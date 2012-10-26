<?php

/**
 * ImStorePaypalIPN - Paypal Notification
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0 
 */
class ImStorePaypalIPN {

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function ImStorePaypalIPN() {
		global $ImStore;

		$postdata = '';
		$this->opts = $ImStore->opts;
		
		if( $this->opts['gateway']['paypalsand'] )
		$url =  $ImStore->gateways['paypalsand']['url'] ;
		else  $url = $ImStore->gateways['paypalprod']['url'] ;
		
		$log = array('REQUEST_TIME', 'REMOTE_ADDR', 'REQUEST_METHOD', 'HTTP_USER_AGENT', 'REMOTE_PORT');

		foreach ($_POST as $i => $v)
			$postdata .= $i . '=' . urlencode($v) . '&';
		$postdata .= 'cmd=_notify-validate';

		$web = parse_url($url);
		if ($web['scheme'] == 'https' ||
				strpos($url, 'sandbox') !== false) {
			$web['port'] = 443;
			$ssl = 'ssl://';
		} else {
			$web['port'] = 80;
			$ssl = '';
		}
		$fp = fsockopen($ssl . $web['host'], $web['port'], $errnum, $errstr, 30);

		if (!$fp) {
			
			return;
			
		} else {
			fputs($fp, "POST " . $web['path'] . " HTTP/1.1\r\n");
			fputs($fp, "Host: " . $web['host'] . "\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($postdata) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $postdata . "\r\n\r\n");

			while (!feof($fp))
				$info[] = @fgets($fp, 1024);

			fclose($fp);
			$info = implode(',', $info);

			if (eregi('VERIFIED', $info)) {

				do_action('ims_before_paypal_ipn', $postdata);
				
				global $ImStore;
				$ImStore->checkout( (int)$_POST['custom'], $_POST);
				
				do_action('ims_after_paypal_ipn', $_POST['custom'], $_POST);
				
				return;
				
			} else {
				
				$logtext = '';
				$file = IMSTORE_ABSPATH . "/ipn_log.txt";
				$hd = fopen($file, 'a');
				
				foreach($_POST as $i => $v)
					$logtext .= $i.'='.$v."\n";
			
				foreach ($log as $key)
					$logtext .= $key . '=' . $_SERVER[$key] . ',';
					
				$logtext .= "\n$url\n_________________\n";
				
				fwrite($hd, $web['host'] . "," . $logtext);
				fclose($hd);
				return;
			}
		}
	}

}

new ImStorePaypalIPN( );