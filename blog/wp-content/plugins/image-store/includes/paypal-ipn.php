<?php 

/**
 * ImStorePaypalIPN - Paypal Notification
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2011
 * @since 0.5.0 
*/

class ImStorePaypalIPN {
	
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function __construct(){
		global $ImStore;
		
		$postdata = '';
		$this->opts = $ImStore->store->opts;
		$this->subtitutions = $ImStore->store->subtitutions;
		$url = $ImStore->store->gateway[$this->opts['gateway']];
		$log = array('REQUEST_TIME','REMOTE_ADDR','REQUEST_METHOD','HTTP_USER_AGENT','REMOTE_PORT');

		foreach($_POST as $i => $v) $postdata .= $i.'='.urlencode($v).'&';
		$postdata .= 'cmd=_notify-validate';
		
		$web = parse_url($url);
		if($web['scheme'] == 'https') { 
			$web['port'] = 443; 
			$ssl = 'ssl://'; 
		} else { 
			$web['port'] = 80;
			$ssl = ''; 
		}
		$fp = @fsockopen($ssl.$web['host'],$web['port'],$errnum,$errstr,30);
		
		if(!$fp) { 
			die();
		} else {
			fputs($fp,"POST ".$web['path']." HTTP/1.1\r\n");
			fputs($fp,"Host: ".$web['host']."\r\n");
			fputs($fp,"Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp,"Content-length: ".strlen($postdata)."\r\n");
			fputs($fp,"Connection: close\r\n\r\n");
			fputs($fp,$postdata."\r\n\r\n");
		
			while(!feof($fp)) { 
				$info[] = @fgets($fp,1024); 
			}
			fclose($fp);
			$info = implode(',',$info);
							
			if(eregi('VERIFIED',$info)) {
				// information was verified
				$this->process_paypal_IPN();
		 		die();
			} else {
				$file = IMSTORE_ABSPATH."/ipn_log.txt"; 
				$hd = fopen($file,'a');
				foreach($log as $key) $logtext .= $key.'='.$_SERVER[$key].',';
				fwrite($hd,$logtext."\n"); 
				fclose($hd);
				die();
			}
		}
	}
	
	
	/**
	 * Process Paypal IPN
	 *
	 * @return boolean
	 * @since 0.5.0 
	 */
	function process_paypal_IPN(){
		
		if($_POST['business'] != $this->opts['paypalname'])
			return false;
			
		if($_POST['mc_currency'] != $this->opts['currency'])
			return false;
		
		$data = get_post_meta($_POST['custom'],'_ims_order_data',true);
		$total = ($data['discounted'])?$data['discounted']:$data['total'];
		
		if($_POST['mc_gross'] != number_format($total,2))
			return false;
		
		wp_update_post(array(
			'post_expire' => '0',
			'ID' => $_POST['custom'],
			'post_status' => 'pending',
			'post_date' => current_time('timestamp') 
		));
		update_post_meta($_POST['custom'],'_response_data',$_POST);
		$this->subtitutions[] = $data['instructions'];
		
		$to 		= $this->opts['notifyemail'];
		$subject 	= $this->opts['notifysubj'];
		$message 	= preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['notifymssg']);
		$headers 	= 'From: "Image Store" <imstore@'.$_SERVER['HTTP_HOST'].">\r\n";
		
		wp_mail($to,$subject,$message,$headers);
		setcookie('ims_orderid_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
		
		/*foreach($_POST as $i => $v)
			$postdata .= $i.'='.$v."\n";
			
		$file = "mytext.txt"; 
		$hd = fopen($file,'w');
		fwrite($hd,$postdata."\n"); 
		fclose($hd);*/
		die();
	}
	
}

$paypalIPN = new ImStorePaypalIPN
?>