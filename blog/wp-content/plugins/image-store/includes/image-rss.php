<?php
/**
*RSS Feeds
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 0.5.3 
*/

class ImStoreFeeds{
	
	/**
	*Constructor
	*
	*@return void
	*@since 0.5.3 
	*/
	function __construct(){
		add_action('wp_head',array($this,'print_rss_link'));
		add_action('parse_query',array($this,'dis_feed'));
	}
	
	/**
	 *Display rss link 
	 *
	 *@return void
	 *@since 0.5.3 
	 */
	function print_rss_link(){
		global $ImStore,$post;
		if($ImStore->store->opts['mediarss'] && $post->post_type == "ims_gallery")
			echo '<link rel="alternate" type="application/rss+xml" title="gallery feed" href="'.$this->get_feed_url().'" />'."\n";
	}
	
	/**
	*Get feed urls 
	*
	*@since 0.5.0 
	*return void
	*/
	function get_feed_url(){
		global $ImStore;
		if($ImStore->permalinks) $link = get_permalink()."/feed/imstore";
		else $link = get_permalink()."&amp;feed=imstore";
		return $link;
	}
	
	/**
	*Display feed
	*
	*@return void
	*@since 0.5.3 
	*/
	 function dis_feed(){
		if(get_query_var('feed') !== 'imstore') return;
		$this->get_images();
		if(empty($this->attachments)){
			header('content-type:text/plain;charset='. $this->charset);
			echo sprintf(__("No images have been added yet.",ImStore::domain),$gid);
			exit;
		}else{ $this->display_rss(); }
	 }
	 
	/**
	*Get feed images
	*
	*@return array
	*@since 0.5.3 
	*/
	function get_images(){
		global $wpdb;
		$this->attachments = $wpdb->get_results($wpdb->prepare(
			"SELECT ID,post_title,guid,
			meta_value,post_excerpt
			FROM $wpdb->posts AS p 
			LEFT JOIN $wpdb->postmeta AS pm
			ON p.ID = pm.post_id
			WHERE post_type = 'ims_image'
			AND meta_key = '_wp_attachment_metadata'
			AND post_status = 'publish'
			AND post_parent = (
				SELECT ID FROM $wpdb->posts
				WHERE post_name = '%s'
			) ORDER BY post_date DESC LIMIT ".get_option('posts_per_rss')
		,get_query_var('ims_gallery')));
		if(empty($this->attachments)) return;
		foreach($this->attachments as $post){
			$post->meta_value = unserialize($post->meta_value);
			$images[] = $post;
		} $this->attachments = $images;
	}
	
	/**
	*Display rss feed
	*
	*@return void
	*@since 0.5.3 
	*/
	 function display_rss(){
		global $ImStore; 
	 	header('Content-Type:'.feed_content_type('rss-http').'; charset='.$this->charset,true);
		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'" standalone="yes"?>';?>
		<rss version="2.0"
			xmlns:atom="http://www.w3.org/2005/Atom"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:media="http://search.yahoo.com/mrss/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		>
		<channel>
			<title><![CDATA[<?php bloginfo_rss('name'); _e('image RSS',ImStore::domain);?>]]></title>
			<atom:link href="<?php self_link();?>" rel="self" type="application/rss+xml" />
			<link><?php bloginfo_rss('url')?></link>
			<description><![CDATA[<?php bloginfo_rss("description")?>]]></description>
			<lastBuildDate><?php echo mysql2date('D,d M Y H:i:s +0000',get_lastpostmodified('GMT'),false);?></lastBuildDate>
			<language><?php echo get_option('rss_language');?></language>
			<sy:updatePeriod><?php echo apply_filters('rss_update_period','hourly');?></sy:updatePeriod>
			<sy:updateFrequency><?php echo apply_filters('rss_update_frequency','1');?></sy:updateFrequency>
			<?php foreach($this->attachments as $image){ $encr = $ImStore->store->encrypt_id($image->ID)?>
			<?php $filetype = wp_check_filetype(basename($image->post_title));?>
			<item>
				<title><![CDATA[<?php echo $image->post_title?>]]></title>
				<link><?php echo IMSTORE_URL."image.php?img={$encr}";?></link>
				<media:thumbnail url="<?php echo IMSTORE_URL."image.php?img={$encr}&amp;thumb=1";?>"/>
				<media:content type="<?php echo $filetype['type']?>" url="<?php echo IMSTORE_URL."image.php?img={$encr}";?>"/>
			</item>
			<?php }?>
		</channel>
		</rss>
	<?php }
	 
}
$this->feeds = new ImStoreFeeds();
?>