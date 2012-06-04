<?
class Zend_View_Helper_Twitter extends Zend_View_Helper_Abstract{
	public $view; 
	public function twitter() {
		$storage = new Zend_Auth_Storage_Session(); 
      		$user_info=$storage->read();
		$this->viewer=0;
		if (isset($user_info->user_id)) {
			if ($user_info->user_type=="admin"){
				$this->viewer=-999;
			}else{
				$this->viewer=$user_info->user_id;
			}
		}
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		return $this;
	}
	
	public function tweetList($rows=10,$page=0) {
    $db = Zend_Registry::get("db");
    	    $frontendOptions = array(
              'lifetime' => 7200, // cache lifetime of 2 hours
              'automatic_serialization' => true
              );
     
           $backendOptions = array(
                'cache_dir' => './cache/' // Directory where to put the cache files
          );
         
     
    // getting a Zend_Cache_Core object
    $cache = Zend_Cache::factory('Core',
                                 'File',
                                 $frontendOptions,
                                 $backendOptions);	
    if (!$rendered=$cache->load('tweet_latest')) {
      $db = Zend_Registry::get("db");
      $select=$db->select()
        ->from("beer_tweets")
        ->joinLeft("users","beer_tweets.tweet_owner=users.user_id",array("user_id","user_name","user_email"))
        ->where("users.user_active = ?", '1')
        ->order("tweet_date DESC")
        ->limit($rows);
      $this->view->twitter=$this;
      $this->rowsCount=$rows;
      $this->view->twitterItems=$db->fetchAll($select);
      $rendered=$this->view->render("twitterList.phtml");
      $cache->save($rendered, 'tweet_latest');
       
		}
    $this->view->headLink()->appendStylesheet("/public/ui/external/jquery.qtip.min.css");
    $this->view->headScript()->appendFile("/public/ui/external/jquery.qtip.min.js");
      return $rendered;
		
		 
	}
	public function tweetItem($data) {
		$this->view->twitter=$this;
		$this->view->tweet=$data;
		return $this->view->render("twitterItem.phtml");
	}
	public function setView(Zend_View_Interface $view) 
	{ 

	$this->view = $view;
	 
	} 
}
?>
