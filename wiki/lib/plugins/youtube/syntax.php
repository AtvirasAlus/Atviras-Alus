<?php
/**
 * Plugin YouTube: Create YouTube link and object from ID.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ikuo Obataya <I.Obataya[at]gmail.com
 * @version    2008-04-05
 * @update     2008-04-05
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_youtube extends DokuWiki_Syntax_Plugin {
  var $html;
  var $pattern;
  function syntax_plugin_youtube(){
    $this->html    = @file_get_contents(DOKU_PLUGIN.'youtube/object.htm');
    $this->pattern = '/\{\{(\s?)youtube>(small|large|link):([^} |]+)\|?(.*?)(\s?)\}\}/';
  }
  function getInfo(){
    return array(
    'author' => 'Ikuo Obataya',
    'email'  => 'I.Obataya@gmail.com',
    'date'   => '2008-04-05',
    'name'   => 'YouTube Plugin',
    'desc'   => 'YouTube link and object{{youtube>[small|large|link]:ID}}',
    'url'    => 'http://wiki.symplus.co.jp/computer/en/youtube_plugin',
    );
  }
  function getType(){ return 'substition'; }
  function getSort(){ return 159; }
  function connectTo($mode) { $this->Lexer->addSpecialPattern('\{\{\s?youtube>[^}]*\s?\}\}',$mode,'plugin_youtube'); }
  
  function handle($match, $state, $pos, &$handler){
    $pm = preg_match_all($this->pattern,$match,$result);
    $left  = ($result[1][0]==" ");
    $right = ($result[5][0]==" ");
    $cmd   = $result[2][0];
    $id    = $result[3][0];
    $title = $result[4][0];
    if ($left==true && $right==true){
      $align = 'center';
    }else if($left==true){
      $align = 'right';
    }else if($right==true){
      $align = 'left';
    }
    return array($state, array($cmd,$id,$align,$title));
  } 

  function render($mode, &$renderer, $data){
    if($mode != 'xhtml'){return false;}
    list($state, $match) = $data;
    list($cmd,$id,$align,$title) = $match;
    $id    = urlencode($id);
    $title = urlencode($title);
    $title = str_replace("+"," ",$title);
    switch($cmd){
      case 'link':
        $lnkFormat='<a href="http://www.youtube.com/watch?v=%s" title="%s">';
        $href_start=sprintf($lnkFormat,$id,empty($title)?$id:$title.' ('.$id.')');
        $renderer->doc.=$href_start.'<div class="youtube_icon">'.$title.'</div></a>';
        return true;

      case 'large':
        if ($align=='center'){$renderer->doc.="<center>";}
        $renderer->doc.=sprintf($this->html,425,350,$id,$align,$title,$id);
        if ($align=='center'){$renderer->doc.="</center>";}
        $renderer->doc.=NL;
        return true;

      case 'small':
        if ($align=='center'){$renderer->doc.="<center>";}
        $renderer->doc.=sprintf($this->html,255,210,$id,$align,$title,$id);
        if ($align=='center'){$renderer->doc.="</center>";}
        return true;
    }
    $renderer->doc.=NL;
  }
}
?>