<?
class Zend_View_Helper_TweetComments extends Zend_View_Helper_Abstract{
public function  tweetComments($tweet_id) {
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("beer_tweets_comments")
			->joinLeft("users","users.user_id = beer_tweets_comments.comment_brewer",array("user_id","user_name","user_email"))
			->where("comment_tweet =?",$tweet_id)
			->order("comment_date ASC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->tweet_id=$tweet_id;
			$out = $this->view->render("tweetcomments.phtml");
		return $out;
	}
}
?>
