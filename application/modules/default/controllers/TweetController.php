<?php
  class TweetController extends Zend_Controller_Action
  {
  	  public function formAction() {
  	  $this->_helper->layout->setLayout('empty');
          $storage = new Zend_Auth_Storage_Session(); 
         	$storage_data=$storage->read();
		$this->view->can_post=isset($storage_data->user_id);
          
  	  } 
	public function allAction() {
		 $db = Zend_Registry::get('db');
			$select=$db->select()	
			->from("beer_tweets")
			->joinLeft("users","beer_tweets.tweet_owner=users.user_id",array("user_id","user_name","user_email"))
		//	->where("user_active = 1")
			->order("tweet_date DESC");
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(15);
		
	}
 public function itemtweetAction() {
		$this->_helper->layout->setLayout('empty');
		$db = Zend_Registry::get('db');
		$select=$db->select()
			->from("beer_tweets")
			->joinLeft("users","beer_tweets.tweet_owner=users.user_id",array("user_id","user_name","user_email"))
			->order("tweet_date DESC")
			->limit(1,$_REQUEST['sid']);
			$this->view->twitterItem=$db->fetchRow($select);
		
}
private function removeCache() {
$db = Zend_Registry::get("db");
    	    $frontendOptions = array(
              'lifetime' => 7200, // cache lifetime of 2 hours
              'automatic_serialization' => true);
             $backendOptions = array(
                  'cache_dir' => './cache/' // Directory where to put the cache files
            );
 
    $cache = Zend_Cache::factory('Core', 'File',$frontendOptions,$backendOptions);	
    $cache->remove('tweet_latest');
}
public function removetweetAction() {
    
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session();
		$storage_data=$storage->read();
		if (isset($storage_data->user_type)) {
			$db = Zend_Registry::get('db');
			if ($db->delete("beer_tweets","tweet_id = ".$_POST['id'])) { @$this->removeCache('tweet_latest');print 1;}else{print 0;}
		}
}
  	  public function addtweetAction() {
		$this->_helper->layout->setLayout('empty');
		$storage = new Zend_Auth_Storage_Session();
		$storage_data=$storage->read();
		if (isset($storage_data->user_id)) {
			$db = Zend_Registry::get('db');
			$fields=  array("tweet_link_url"=>"link_url","tweet_link_title"=>"link_title","tweet_link_description"=>"link_description","tweet_link_image"=>"link_image","tweet_message"=>"link_message");
			$inserts=array();
			foreach ($fields as $key => $value) {
			  if (isset($_POST[$value]) ){
			    $inserts[$key]=strip_tags($_POST[$value]);
			  }
			}
			$inserts["tweet_owner"]=$storage_data->user_id;
			$db->insert("beer_tweets", $inserts);
			@$this->removeCache('tweet_latest');
			$select=$db->select()
			->from("beer_tweets")
			->joinLeft("users","beer_tweets.tweet_owner=users.user_id",array("user_id","user_name","user_email"))
			->where("beer_tweets.tweet_id = ?", $db->lastInsertId());
			$this->view->twitterItem=$db->fetchRow($select);
		}
  	  }
  	  public function imageparserAction() {
		$this->_helper->layout->setLayout('empty');
		$images_array=explode(",",$_POST['images']);
		$approved_images=array();
		for ($i=0;$i<count($images_array);$i++) {
		  if(list($width, $height, $type, $attr) = @getimagesize(@$images_array[$i])) {
		       if(($width >= 50 && $height >= 50) && ($width <= 600 && $height <= 600)){
		          $approved_images[]=array("url"=>$images_array[$i],"width"=>$width,"height"=>$height);
		          
		      }
		  }
		}
		$this->view->images=$approved_images;

  	  }

  	  public function linkparserAction() {
  	  $this->view->http_status=200;
  	 
  	  $this->_helper->layout->setLayout('empty');
      $images_array=array();
      $__url=$_REQUEST['url'];

//preg_match_all("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $__url, $matches);
        preg_match_all('!https?://[\S]+!', $__url, $matches);
         if (count($matches)>0) {
            $url=$matches[0][0];
         }else{ 
          $url = $__url;
        } 
        $url_title=$url;
        $url_description="";
        //$url = $this->checkURL($url);
        $client = new Zend_Http_Client($url);
        if ($response = @$client->request()) {
          $ctype=$response->getHeader('Content-type');
          $string=$response->getBody();
          if (is_array($ctype)) $ctype = $ctype[0];
          $parsed_url=parse_url($url);
          if (stripos($ctype,"text/html")!==false) {
          $doc = new DOMDocument();
          @$doc->loadHTML('<?xml encoding="UTF-8">' .$string);
          $title=$doc->getElementsByTagName('title');
          if ($title->length>0) {
            $url_title=$title->item(0)->nodeValue;
          } 
          
        if (stripos($parsed_url['host'],"youtube.com")!==false) {
          $q=$this->parse_query($url);
          if (isset($q['v'])) {
            $images_array[]="http://img.youtube.com/vi/".$q['v']."/1.jpg";
          }
        }	
        if (count($images_array)==0) {
            $images=$doc->getElementsByTagName('img');
            for ($i=0;$i<$images->length;$i++) {
                if ($attr_src=$images->item($i)->getAttributeNode ('src')) {
                    if ($abs_url=$this->relative2absolute($url, $attr_src->nodeValue)){
                      $images_array[]=$abs_url;
                    }
                }
            }
        }
          $url_tags=array();
          $tags = $doc->getElementsByTagName('meta');
          for ($i=0;$i<$tags->length;$i++) {
            if ($attr_name=$tags->item($i)->getAttributeNode ('name')) {
              if( $attr_content=$tags->item($i)->getAttributeNode ('content')) {
                  $url_tags[$attr_name->nodeValue] = $attr_content->nodeValue;
              }
            }
          }
          if (!isset($url_tags['description'])) {
           
		$tagStripper=new Zend_Filter_StripTags();
               $url_description=mb_substr($tagStripper->filter(preg_replace('#<script[^>]*>.*?</script>#is', "",$string)),0,255);
            
          }else{
              $url_description= $url_tags['description'];
          }
            
            }
          }else{
            $this->view->http_status=404;
          }
            $this->view->images=$images_array;
            $this->view->description =   $url_description;
            $this->view->title =$url_title;
            $this->view->url=$url;

  	  }
  	   
private  function parse_query($var)
 {
  /**
   *  Use this function to parse out the query array element from
   *  the output of parse_url().
   */
  $var  = parse_url($var, PHP_URL_QUERY);
  $var  = html_entity_decode($var);
  $var  = explode('&', $var);
  $arr  = array();

  foreach($var as $val)
   {
    $x          = explode('=', $val);
    $arr[$x[0]] = $x[1];
   }
  unset($val, $x, $var);
  return $arr;
 }
  	  private function checkURL($value) {
        $value = trim($value);
        if (get_magic_quotes_gpc()) 
        {
          $value = stripslashes($value);
        }
        $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
        $value = strip_tags($value);
        $value = htmlspecialchars($value);
        return $value;
      }	
  	  
  	  
  
function relative2absolute($absolute, $relative) {
        $p = @parse_url($relative);
        if(!$p) {
	        //$relative is a seriously malformed URL
	        return false;
        }
        if(isset($p["scheme"])) return $relative;
 
        $parts=(parse_url($absolute));
 
        if(substr($relative,0,1)=='/') {
            $cparts = (explode("/", $relative));
            array_shift($cparts);
        } else {
            if(isset($parts['path'])){
                 $aparts=explode('/',$parts['path']);
                 array_pop($aparts);
                 $aparts=array_filter($aparts);
            } else {
                 $aparts=array();
            }
           $rparts = (explode("/", $relative));
           $cparts = array_merge($aparts, $rparts);
           foreach($cparts as $i => $part) {
                if($part == '.') {
                    unset($cparts[$i]);
                } else if($part == '..') {
                    unset($cparts[$i]);
                    unset($cparts[$i-1]);
                }
            }
        }
        $path = implode("/", $cparts);
 
        $url = '';
        if($parts['scheme']) {
            $url = "$parts[scheme]://";
        }
        if(isset($parts['user'])) {
            $url .= $parts['user'];
            if(isset($parts['pass'])) {
                $url .= ":".$parts['pass'];
            }
            $url .= "@";
        }
        if(isset($parts['host'])) {
            $url .= $parts['host']."/";
        }
        $url .= $path;
 
        return $url;
}}
  ?>
