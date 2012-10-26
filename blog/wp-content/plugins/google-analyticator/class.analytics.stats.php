<?php

# Include SimplePie if it doesn't exist
if ( !class_exists('SimplePie') ) {
	require_once (ABSPATH . WPINC . '/class-feed.php');
}

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_AnalyticsService.php';

/**
 * Handles interactions with Google Analytics' Stat API
 *
 * @author http://www.codebyjeff.com
 **/
class GoogleAnalyticsStats
{

	var $client = false;
	var $accountId;
	var $baseFeed = 'https://www.googleapis.com/analytics/v3';
	var $token = false;


	/**
	 * Constructor
	 *
	 * @param token - a one-time use token to be exchanged for a real token
	 **/
	function GoogleAnalyticsStats()
	{

		$this->client = new Google_Client();

		$this->client->setClientId(GOOGLE_ANALYTICATOR_CLIENTID);
		$this->client->setClientSecret(GOOGLE_ANALYTICATOR_CLIENTSECRET);
		$this->client->setRedirectUri(GOOGLE_ANALYTICATOR_REDIRECT);
		$this->client->setScopes(array(GOOGLE_ANALYTICATOR_SCOPE));

		// Magic. Returns objects from the Analytics Service instead of associative arrays.
		$this->client->setUseObjects(true);

		try {
                        $this->analytics = new Google_AnalyticsService($this->client);
                    }
                    catch (Google_ServiceException $e)
                    {
                              print 'There was an Analytics API service error ' . $e->getCode() . ':' . $e->getMessage();

                    }

	}

	function checkLogin()
	{
		$ga_google_authtoken  = get_option('ga_google_authtoken');

		if (!empty($ga_google_authtoken))
		{
			$this->client->setAccessToken($ga_google_authtoken);
		}
		else
		{
			$authCode = get_option('ga_google_token');

			if (empty($authCode)) return false;

                        
			$accessToken = $this->client->authenticate($authCode);

                        if($accessToken)
                        {
                            $this->client->setAccessToken($accessToken);
                            update_option('ga_google_authtoken', $accessToken);
                        }
                        else
                        {
                            return false;
                        }
		}

		$this->token =  $this->client->getAccessToken();
		return true;
	}

	function deauthorize()
	{
		update_option('ga_google_token', '');
		update_option('ga_google_authtoken', '');
	}

	function getSingleProfile()
	{

		$webproperty_id = get_option('ga_uid');
		list($pre, $account_id, $post) = explode('-',$webproperty_id);

		if (empty($webproperty_id)) return false;

                try {
                    $profiles = $this->analytics->management_profiles->listManagementProfiles($account_id, $webproperty_id);
                }
                catch (Google_ServiceException $e)
                {
                    print 'There was an Analytics API service error ' . $e->getCode() . ': ' . $e->getMessage();
                    return false;
                }

		$profile_id = $profiles->items[0]->id;
		if (empty($profile_id)) return false;

		$account_array = array();
		array_push($account_array, array('id'=>$profile_id, 'ga:webPropertyId'=>$webproperty_id));
		return $account_array;

	}

        function getAllProfiles()
        {
            $profile_array = array();
            
            try {
                    $profiles = $this->analytics->management_webproperties->listManagementWebproperties('~all');
                }
                catch (Google_ServiceException $e)
                {
                    print 'There was an Analytics API service error ' . $e->getCode() . ': ' . $e->getMessage();
                }


            if( !empty( $profiles->items ) )
            {
                foreach( $profiles->items as $profile )
                {
                    $profile_array[ $profile->id ] = str_replace('http://','',$profile->name );
                }
            }

            return $profile_array;
        }

	function getAnalyticsAccounts()
	{
		$analytics = new Google_AnalyticsService($this->client);
		$accounts = $analytics->management_accounts->listManagementAccounts();
		$account_array = array();

		$items = $accounts->getItems();

		if (count($items) > 0) {
			foreach ($items as $key => $item)
			{
				$account_id = $item->getId();

				$webproperties = $analytics->management_webproperties->listManagementWebproperties($account_id);

				if (!empty($webproperties))
				{
					foreach ($webproperties->getItems() as $webp_key => $webp_item) {
						$profiles = $analytics->management_profiles->listManagementProfiles($account_id, $webp_item->id);

						$profile_id = $profiles->items[0]->id;
						array_push($account_array, array('id'=>$profile_id, 'ga:webPropertyId'=>$webp_item->id));
					}
				}
			}

			return $account_array;
		}
		return false;

	}



	/**
	 * Sets the account id to use for queries
	 *
	 * @param id - the account id
	 **/
	function setAccount($id)
	{
		$this->accountId = $id;
	}


	/**
	 * Get a specific data metrics
	 *
	 * @param metrics - the metrics to get
	 * @param startDate - the start date to get
	 * @param endDate - the end date to get
	 * @param dimensions - the dimensions to grab
	 * @param sort - the properties to sort on
	 * @param filter - the property to filter on
	 * @param limit - the number of items to get
	 * @return the specific metrics in array form
	 **/
	function getMetrics($metric, $startDate, $endDate, $dimensions = false, $sort = false, $filter = false, $limit = false)
	{
		$analytics = new Google_AnalyticsService($this->client);

		$params = array();

		if ($dimensions)
		{
			$params['dimensions'] = $dimensions;
		}
		if ($sort)
		{
			$params['sort'] = $sort;
		}
		if ($filter)
		{
			$params['filters'] = $filter;
		}
		if ($limit)
		{
			$params['max-results'] = $limit;
		}

	   return $analytics->data_ga->get(
	       'ga:' . $this->accountId,
	       $startDate,
	       $endDate,
	       $metric,
	       $params
	       );
	}





	/**
	 * Checks the date against Jan. 1 2005 because GA API only works until that date
	 *
	 * @param date - the date to compare
	 * @return the correct date
	 **/
	function verifyStartDate($date)
	{
		if ( strtotime($date) > strtotime('2005-01-01') )
			return $date;
		else
			return '2005-01-01';
	}

} // END class	