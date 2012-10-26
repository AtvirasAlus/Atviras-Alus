<?php

define( 'WPWEPAY_PATH', IMSTORE_ABSPATH . '/_store/' );
define( 'WPWEPAY_URL', plugin_dir_url(WPWEPAY_PATH) );
define( 'WPWEPAY_BASENAME', plugin_basename( WPWEPAY_PATH ) );

class WePay {

	/**
	 * Version number - sent in user agent string
	 */
	const VERSION = '0.0.9';

	/**
	 * Scope fields
	 * Passed into Wepay::getAuthorizationUri as array
	 */
	const SCOPE_MANAGE_ACCOUNTS  = 'manage_accounts';   // Open and interact with accounts
	const SCOPE_VIEW_BALANCE     = 'view_balance';      // View account balances
	const SCOPE_COLLECT_PAYMENTS = 'collect_payments';  // Create and interact with checkouts
	const SCOPE_REFUND_PAYMENTS  = 'refund_payments';   // Refund checkouts
	const SCOPE_VIEW_USER        = 'view_user';         // Get details about authenticated user

	/**
	 * Application's client ID
	 */
	public $client_id;

	/**
	 * Application's client secret
	 */
	public $client_secret;

	/**
	 * Pass Wepay::$all_scopes into getAuthorizationUri if your application desires full access
	 */
	public static $all_scopes = array(
		self::SCOPE_MANAGE_ACCOUNTS,
		self::SCOPE_VIEW_BALANCE,
		self::SCOPE_COLLECT_PAYMENTS,
		self::SCOPE_REFUND_PAYMENTS,
		self::SCOPE_VIEW_USER,
	);

	/**
	 * Determines whether to use WePay's staing or production servers
	 */
	public $production = null;

	/**
	 * cURL handle
	 */
	private $ch;

	/**
	 * Authenticated user's access token
	 */
	private $token;

	/**
	 * Generate URI used during oAuth authorization
	 * Redirect your user to this URI where they can grant your application
	 * permission to make API calls
	 * @link https://www.wepay.com/developer/reference/oauth2
	 * @param array  $scope             List of scope fields for which your appliation wants access
	 * @param string $redirect_uri      Where user goes after logging in at WePay (domain must match application settings)
	 * @param array  $options optional  user_name,user_email which will be pre-fileld on login form, state to be returned in querystring of redirect_uri
	 * @return string URI to which you must redirect your user to grant access to your application
	 */
	public static function getAuthorizationUri(array $scope, $redirect_uri, array $options = array()) {
		// This does not use WePay::getDomain() because the user authentication
		// domain is different than the API call domain
		if ($this->production === null) {
			throw new RuntimeException('You must initialize the WePay SDK with WePay::useStaging() or WePay::useProduction()');
		}
		$domain = $this->production ? 'https://www.wepay.com' : 'https://stage.wepay.com';
		$uri = $domain . '/v2/oauth2/authorize?';
		$uri .= http_build_query(array(
			'client_id'    => $this->client_id,
			'redirect_uri' => $redirect_uri,
			'scope'        => implode(',', $scope),
			'state'        => empty($options['state'])      ? '' : $options['state'],
			'user_name'    => empty($options['user_name'])  ? '' : $options['user_name'],
			'user_email'   => empty($options['user_email']) ? '' : $options['user_email'],
		));
		return $uri;
	}

	private function getDomain() {
		if ($this->production === true) {
			return 'https://wepayapi.com/v2/';
		}
		elseif ($this->production === false) {
			return 'https://stage.wepayapi.com/v2/';
		}
		else {
			throw new RuntimeException('You must initialize the WePay SDK with WePay::useStaging() or WePay::useProduction()');
		}
	}

	/**
	 * Exchange a temporary access code for a (semi-)permanent access token
	 * @param string $code          'code' field from query string passed to your redirect_uri page
	 * @param string $redirect_uri  Where user went after logging in at WePay (must match value from getAuthorizationUri)
	 * @return StdClass|false
	 *  user_id
	 *  access_token
	 *  token_type
	 */
	public static function getToken($code, $redirect_uri) {
		$uri = $this->getDomain() . 'oauth2/token';
		$params = (array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri'  => $redirect_uri,
			'code'          => $code,
			'state'         => '', // do not hardcode
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, 'WePay v2 PHP SDK v' . self::VERSION);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 5-second timeout, adjust to taste
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_CAINFO, WPWEPAY_PATH.'cacert.pem');  // 5-second timeout, adjust to taste
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$raw = curl_exec($ch);
		if ($errno = curl_errno($ch)) {
			// Set up special handling for request timeouts
			if ($errno == CURLE_OPERATION_TIMEOUTED) {
				throw new WePayServerException;
			}
			throw new Exception('cURL error while making API call to WePay: ' . curl_error($ch), $errno);
		}
		$result = json_decode($raw);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode >= 400) {
			if ($httpCode >= 500) {
				throw new WePayServerException($result->error_description);
			}
			switch ($result->error) {
				case 'invalid_request':
					throw new WePayRequestException($result->error_description, $httpCode);
				case 'access_denied':
				default:
					throw new WePayPermissionException($result->error_description, $httpCode);
			}
		}
		return $result;
	}
	
	/**
	 * Create a new API session
	 * @param string $token - access_token returned from WePay::getToken
	 */
	public function __construct($token, $client_id, $client_secret, $production=false) {
		$this->token = $token;
		$this->client_id  = $client_id;
		$this->production = $production;
		$this->client_secret = $client_secret;
	}

	/**
	 * Clean up cURL handle
	 */
	public function __destruct() {
		if ($this->ch) {
			curl_close($this->ch);
		}
	}

	/**
	 * Make API calls against authenticated user
	 * @param string $endpoint - API call to make (ex. 'user', 'account/find')
	 * @param array  $values   - Associative array of values to send in API call
	 * @return StdClass
	 * @throws WePayException on failure
	 * @throws Exception on catastrophic failure (non-WePay-specific cURL errors)
	 */
	public function request($endpoint, array $values = array()) {
		if (!$this->ch) {
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'WePay v2 PHP SDK v' . self::VERSION);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $this->token", "Content-Type: application/json"));
			curl_setopt($this->ch, CURLOPT_CAINFO, WPWEPAY_PATH.'cacert.pem');  // 5-second timeout, adjust to taste
			curl_setopt($this->ch, CURLOPT_TIMEOUT, 10); // 5-second timeout, adjust to taste
			curl_setopt($this->ch, CURLOPT_POST, !empty($values)); // WePay's API is not strictly RESTful, so all requests are sent as POST unless there are no request values
		}
		$uri = $this->getDomain() . $endpoint;
		curl_setopt($this->ch, CURLOPT_URL, $uri);
		if (!empty($values)) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($values));
		}
		$raw = curl_exec($this->ch);
		if ($errno = curl_errno($this->ch)) {
			// Set up special handling for request timeouts
			if ($errno == CURLE_OPERATION_TIMEOUTED) {
				throw new WePayServerException;
			}
			throw new Exception('cURL error while making API call to WePay: ' . curl_error($this->ch), $errno);
		}
		$result = json_decode($raw);
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($httpCode >= 400) {
			if ($httpCode >= 500) {
				throw new WePayServerException($result->error_description);
			}
			switch ($result->error) {
				case 'invalid_request':
					throw new WePayRequestException($result->error_description, $httpCode);
				case 'access_denied':
				default:
					throw new WePayPermissionException($result->error_description, $httpCode);
			}
		}
		return $result;
	}
}

/**
 * Different problems will have different exception types so you can
 * catch and handle them differently.
 * 
 * WePayServerException indicates some sort of 500-level error code and
 * was unavoidable from your perspective. You may need to re-run the
 * call, or check whether it was received (use a "find" call with your
 * reference_id and make a decision based on the response)
 * 
 * WePayRequestException indicates a development error - invalid endpoint,
 * erroneous parameter, etc.
 * 
 * WePayPermissionException indicates your authorization token has expired,
 * was revoked, or is lacking in scope for the call you made
 */
class WePayException extends Exception {}
class WePayRequestException extends WePayException {}
class WePayPermissionException extends WePayException {}
class WePayServerException extends WePayException {}


$wepay = new WePay(
	$this->opts['wepayaccesstoken'],
	$this->opts['wepayclientid'],
	$this->opts['wepayclientsecret'],
	(( $this->opts['gateway']['wepayprod'] ) ? true : false)
);
