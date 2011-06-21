<?php
/*
Plugin Name: List Posts with Pingbacks and Tracks 
Plugin URI: http://christopherross.ca/software/list-posts-with-pingbacks-trackbacks
Description: This function is designed to allow you to add a list of popular posts to your website theme based on which posts have pingback and trackbacks.
Author: Christopher Ross
Author URI: http://christopherross.ca
Version: 1.1.1
*/

/*  Copyright 2008  Christopher Ross  (email : info@christopherross.ca)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'ListPostsWithPingbacksandTrackbacks_menu');

function ListPostsWithPingbacksandTrackbacks_menu() {
  add_options_page('List Pings/Tracks', 'List Pings/Tracks', 10,'ListPostsWithPingbacksandTrackbacks.php', 'ListPostsWithPingbacksandTrackbacks_options');
}

function ListPostsWithPingbacksandTrackbacks_options() {



	/* Page Start */
	echo "
<div class='wrap'>
  <div id='icon-options-general' class='icon32'><br />
  </div>
  <h2>List Pings/Tracks</h2>
  <form name='addlink' id='addlink' method='post' action='http://regentware.com/donate/?5725889'>
    <div id='poststuff' class='metabox-holder has-right-sidebar'>
      <div id='side-info-column' class='inner-sidebar'>
        <div id='side-sortables' class='meta-box-sortables'>
          <div id='linksubmitdiv' class='postbox ' >
            <div class='handlediv' title='Click to toggle'><br />
            </div>
            <h3 class='hndle'><span>Plugin Details</span></h3>
            <div class='inside'>
              <div class='submitbox' id='submitlink'>
                <div id='minor-publishing'>
                  <div style='display:none;'>
                    <input type='submit' name='save' value='Save' />
                  </div>
                  <div id='minor-publishing-actions'>
                    <div id='preview-action'> </div>
                    <div class='clear'></div>
                  </div>
                  <div id='misc-publishing-actions'>
                    <div class='misc-pub-section misc-pub-section-last'>
                          <ul class='options' style='padding-left: 20px;'>
							<style>.options a {text-decoration:none;}</style>
							<li><a href='http://christopherross.ca/software/list-posts-with-pingbacks-trackbacks/'>Plugin Homepage</a></li>
							<li><a href='http://wordpress.org/extend/plugins/list-posts-with-pingbacks-trackbacks/'>Vote for this Plugin</a></li>
							<li><a href='http://forums.christopherross.ca/'>Support Forum</a></li>
							<li><a href='http://support.christopherross.ca/'>Report a Bug</a></li>";
							
			
					echo "		</ul>
                    </div>
                  </div>
                </div>
                <div id='major-publishing-actions'>
                  <div id='delete-action'> </div>
                  <div id='publishing-action'>
                    <input name='save' type='submit' class='button-primary' id='publish' tabindex='4' accesskey='p' value='Donate' />
                  </div>
                  <div class='clear'></div>
                </div>
                <div class='clear'></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id='post-body'>
        <div id='post-body-content'>
          <div id='namediv' class='stuffbox'>
            <h3>
              <label for='link_name'>Settings</label>
            </h3>
            <div class='inside'><span class='hndle'>This plugin has no Administation level settings. To include excerpts in your themes, please follow the readme.txt instructions below.</span></div>
          </div>
          <div id='addressdiv' class='stuffbox'>
            <h3>
              <label for='link_url'>Readme File</label>
            </h3>
            <div class='inside'>
				  <pre>";
				  echo wordwrap(file_get_contents('../wp-content/plugins/list-posts-with-pingbacks-trackbacks/readme.txt'), 80, "\n",true);;
				  echo "</pre>
            </div>
          </div>
          <div id='normal-sortables' class='meta-box-sortables'></div>
          <div id='advanced-sortables' class='meta-box-sortables'> </div>
        </div>
      </div>
    </div>
  </form>
</div>
	";

}









function ListPostsWithPingbacksandTrackbacks($options='') {
	$ns_options = array(
                    "count" => "10",
                    "before"  => "<li>",
                    "after" => "</li>",
					"show" => true,
					"type" => "both",
					"link" => true,
					"order" => "desc",
					"nofollow" => true,
					"minpr" => "0",
					"format" => "#post# - #link#",
                   );

	$options = explode("&",$options);
	
	foreach ($options as $option) {
	
		$parts = explode("=",$option);
		$options[$parts[0]] = $parts[1];
	
	}
	
	if ($options['count']) {$ns_options['count'] = $options['count'];}
	if ($options['before']) {$ns_options['before'] = $options['before'];}
	if ($options['after']) {$ns_options['after'] = $options['after'];}
	if ($options['show']) {$ns_options['show'] = $options['show'];}
	if ($options['type']) {$ns_options['type'] = $options['type'];}
	if ($options['link']) {$ns_options['link'] = $options['link'];}
	if ($options['order']) {$ns_options['order'] = $options['order'];}
	if (isset($options['nofollow'])) {$ns_options['nofollow'] = $options['nofollow'];}
	if (isset($options['minpr'])) {$ns_options['minpr'] = $options['minpr'];}
	if ($options['format']) {$ns_options['format'] = $options['format'];}
	
	

	if(strtolower($ns_options['order']) == "desc") {$sqlorder = "ORDER BY comment_date_gmt DESC";}
	if(strtolower($ns_options['order']) == "asc") {$sqlorder = "ORDER BY comment_date_gmt ASC";}
	if(strtolower($ns_options['order']) == "rand") {$sqlorder = "ORDER BY RAND()";}


	if(strtolower($ns_options['type']) == "pingback") {$type = "`comment_type` LIKE '%ping%'";}
	if(strtolower($ns_options['type']) == "trackback") {$type = "`comment_type` LIKE '%track%'";}
	if(strtolower($ns_options['type']) == "both") {$type = "`comment_type` LIKE '%ping%' OR `comment_type` LIKE '%track%'";}


	$sql = "SELECT *  FROM `wp_comments` WHERE (".$type.") AND `comment_author_url` NOT LIKE '%".$_SERVER['SERVER_NAME']."%' AND `comment_author_url` != '' ".$sqlorder." LIMIT 0,".($ns_options['count']*5);	
	global $wpdb;  
	$comments = $wpdb->get_results($sql);
	

    foreach ($comments as $comment) {  
	
		unset($link);
		unset($url);
		$url = strtolower($comment->comment_author_url);
		$url = str_replace("http:","",$url);
		$url = str_replace("https:","",$url);
		$url = str_replace("www.","",$url);
		$url = str_replace("//","",$url);
		$urlset = explode("/",$url);
		$url = $urlset[0];

		if ( $count < $ns_options['count']) {
			// *************** Check the Pagerank of the base website
			if ($ns_options['minpr'] > 0) {
				$pr = 0;
				for ( $counter = 0; $counter <= 10; $counter += 1) {
					if(substr_count(file_get_contents("cache/pr".$counter.".txt"),$url)>0) {
						$pr = $counter;
					}
				}
				if ($pr == 0) {
					// fetch the pagerank
					//$pr = getpr($url);
					$pr = 10;
					if ($pr == "") {
						$pr = 0;
					} else {
						$fp = fopen("cache/pr".$pr.".txt", "a"); 
						fwrite($fp, $basehref."\n"); 
						fclose($fp); 
					}
					
					// write the pagerank for future reference
					
				}
				// *************** Check the Pagerank of the base website
			} else {
				$pr = 10;
			}
			if ($pr >= $ns_options['minpr']) {
				$final .= $ns_options['before'].$ns_options['format'].$ns_options['after'];
	
				if ($ns_options['link']) 	{$link .= "<a href='".$comment->comment_author_url."' title='".$url."'";}
				if ($ns_options['nofollow'] == true) {$link .= " rel='nofollow' ";}
				if ($ns_options['link']) {$link .= ">";}
				$link .= $url;
				if ($ns_options['link']) {$link .= "</a>\n\n";}
		
				$posts = $wpdb->get_results("SELECT ID, post_title,guid FROM $wpdb->posts WHERE ID=".$comment->comment_post_ID);  
				
				foreach ($posts as $mypost) {  
					$post = "<a href='".$mypost->guid."' title='".$mypost->post_title."'>".$mypost->post_title."</a>";
				}
				
				$final = str_replace("#link#",$link,$final);
				$final = str_replace("#post#",$post,$final);
	
				$count++;
			}
		}
	}
	if ($ns_options['show']==1) {echo $final;} else {return $final;}

}

?>
<?php
//PageRank Lookup v1.1 by HM2K (update: 31/01/07)
//based on an alogoritham found here: http://pagerank.gamesaga.net/
 
//settings - host and user agent
$googlehost='toolbarqueries.google.com';
$googleua='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5';
 
//convert a string to a 32-bit integer
function StrToNum($Str, $Check, $Magic) {
    $Int32Unit = 4294967296;  // 2^32
 
    $length = strlen($Str);
    for ($i = 0; $i < $length; $i++) {
        $Check *= $Magic;      
        //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
        //  the result of converting to integer is undefined
        //  refer to http://www.php.net/manual/en/language.types.integer.php
        if ($Check >= $Int32Unit) {
            $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
            //if the check less than -2^31
            $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
        }
        $Check += ord($Str{$i});
    }
    return $Check;
}
 
//genearate a hash for a url
function HashURL($String) {
    $Check1 = StrToNum($String, 0x1505, 0x21);
    $Check2 = StrToNum($String, 0, 0x1003F);
 
    $Check1 >>= 2;      
    $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
    $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
    $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF); 
       
    $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
    $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
       
    return ($T1 | $T2);
}
 
//genearate a checksum for the hash string
function CheckHash($Hashnum) {
    $CheckByte = 0;
    $Flag = 0;
 
    $HashStr = sprintf('%u', $Hashnum) ;
    $length = strlen($HashStr);
       
    for ($i = $length - 1;  $i >= 0;  $i --) {
        $Re = $HashStr{$i};
        if (1 === ($Flag % 2)) {              
            $Re += $Re;    
            $Re = (int)($Re / 10) + ($Re % 10);
        }
        $CheckByte += $Re;
        $Flag ++;       
    }
 
    $CheckByte %= 10;
    if (0 !== $CheckByte) {
        $CheckByte = 10 - $CheckByte;
        if (1 === ($Flag % 2) ) {
            if (1 === ($CheckByte % 2)) {
                $CheckByte += 9;
            }
            $CheckByte >>= 1;
        }
    }
 
    return '7'.$CheckByte.$HashStr;
}
 
//return the pagerank checksum hash
function getch($url) { return CheckHash(HashURL($url)); }
 
//return the pagerank figure
function getpr($url) {
        global $googlehost,$googleua;
        $ch = getch($url);
        $fp = fsockopen($googlehost, 80, $errno, $errstr, 5);
        if ($fp) {
		
	
           $out = "GET /search?client=navclient-auto&ch=$ch&features=Rank&q=info:$url HTTP/1.1\r\n";
           //echo "<pre>$out</pre>\n"; //debug only
           $out .= "User-Agent: $googleua\r\n";
           $out .= "Host: $googlehost\r\n";
           $out .= "Connection: Close\r\n\r\n";
       
           fwrite($fp, $out);
           
           //$pagerank = substr(fgets($fp, 128), 4); //debug only
           //echo $pagerank; //debug only
           while (!feof($fp)) {
                        $data = fgets($fp, 128);
                        //echo $data;
                        $pos = strpos($data, "Rank_");
                        if($pos === false){} else{
                                $pr=substr($data, $pos + 9);
                                $pr=trim($pr);
                                $pr=str_replace("\n",'',$pr);
                                return $pr;
                        }
           }
           //else { echo "$errstr ($errno)<br />\n"; } //debug only
           fclose($fp);
        }
}
 
//generate the graphical pagerank
function pagerank($url,$width=40,$method='style') {
        if (!preg_match('/^(http:\/\/)?([^\/]+)/i', $url)) { $url='http://'.$url; }
        $pr=getpr($url);
        $pagerank="PageRank: $pr/10";
 
        //The (old) image method
        if ($method == 'image') {
        $prpos=$width*$pr/10;
        $prneg=$width-$prpos;
        $html='<img src="http://www.google.com/images/pos.gif" width='.$prpos.' height=4 border=0 alt="'.$pagerank.'"><img src="http://www.google.com/images/neg.gif" width='.$prneg.' height=4 border=0 alt="'.$pagerank.'">';
        }
        //The pre-styled method
        if ($method == 'style') {
        $prpercent=100*$pr/10;
        $html='<div style="position: relative; width: '.$width.'px; padding: 0; background: #D9D9D9;"><strong style="width: '.$prpercent.'%; display: block; position: relative; background: #5EAA5E; text-align: center; color: #333; height: 4px; line-height: 4px;"><span></span></strong></div>';
        }
       
        $out='<a href="'.$url.'" title="'.$pagerank.'">'.$html.'</a>';
        return $out;
}
 
//if ((!isset($_POST['url'])) && (!isset($_GET['url']))) { echo '<form action="" method="post"><input name="url" type="text"><input type="submit" name="Submit" value="Get Pagerank"></form>'; }
if (isset($_REQUEST['url'])) { echo pagerank($_REQUEST['url']); }
?>