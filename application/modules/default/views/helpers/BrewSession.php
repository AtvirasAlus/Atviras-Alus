<?
class Zend_View_Helper_BrewSession extends Zend_View_Helper_Abstract{
	function brewSession() {
		return $this;
	}
	 function editableRow($session=array(),$add=true) {
	 	 $fields=array("session_name"=>"Virimo pavadinimas","user_name"=>"Aludaris","recipe_name"=>"Receptas","session_size"=>"Alaus kiekis (l.)","session_og"=>"OG","session_fg"=>"FG","session_primarydate"=>"Pirminė fermentacija","session_secondarydate"=>"Antrinė fermentacija","session_caskingdate"=>"Išpilstyta","session_comments"=>"Pastabos");
	 	 if (!isset($session["redirect"])) {
	 	 	 $session["redirect"]="brew-session/brewer";
	 	 }
		if ($add) {
			$session=array_merge($session,array("session_name"=>"","session_size"=>"","session_og"=>"","session_fg"=>"","session_comments"=>"","session_caskingdate"=>"","session_secondarydate"=>"","session_primarydate"=>""));
			$line2='<tr><td colspan="2" align="right"><input type="hidden" name="redirect" value="'.$session['redirect'].'"> <input type="hidden" name="session_recipe" value="'.$session['recipe_id'].'"><input type="hidden" name="session_brewer" value="'.$session['user_id'].'"><input type="submit" value="Pridėti"></td></tr>';
			$action="/brew-session/add";
		}else{
			$line2='<tr><td colspan="1" align="right"><input type="hidden" name="redirect" value="'.$session['redirect'].'"><input type="hidden" name="session_recipe" value="'.$session['recipe_id'].'"><input type="hidden" name="session_id" value="'.$session['session_id'].'"></td><td align="right"><div style="display:inline"><input type="button" value="Trinti" onClick="deleteSession('.$session["session_id"].')"/>&nbsp;<input type="submit" value="Saugoti"/></div></td></tr>';
			$action="/brew-session/update";
		}
		return '<form method="post" action="'.$action.'"><tr><td class="bs_title">'.$fields['session_name'].'</td><td class="bs_data"><input type="text" name="session_name"  style="width:200" value="'.$session["session_name"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['user_name'].'</td><td class="bs_data">'. $session['user_name'].'</td></tr>
		<tr><td class="bs_title">'.$fields['recipe_name'].'</td><td class="bs_data">'.$session["recipe_name"].'</td></tr>
		<tr><td class="bs_title">'.$fields['session_size'].'</td><td class="bs_data"><input type="text" name="session_size" style="width:55" value="'.$session["session_size"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_og'].'</td><td class="bs_data"><input type="text" name="session_og" style="width:55" value="'.$session["session_og"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_fg'].'</td><td class="bs_data"><input type="text" name="session_fg" style="width:55" value="'.$session["session_fg"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_primarydate'].'</td><td class="bs_data"><input type="text" name="session_primarydate" style="width:120" id="session_primarydate" value="'.$session["session_primarydate"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_secondarydate'].'</td><td class="bs_data"><input type="text" name="session_secondarydate" style="width:120" id="session_secondarydate" value="'.$session["session_secondarydate"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_caskingdate'].'</td><td class="bs_data"><input type="text" name="session_caskingdate" style="width:120" id="session_caskingdate" value="'.$session["session_caskingdate"].'"/></td></tr>
		<tr><td class="bs_title">'.$fields['session_comments'].'</td><td class="bs_data"><textarea style="width:450;height:250" name="session_comments">'.($session["session_comments"]).'</textarea></td></tr>'.$line2.'</form>';
		}
	
	function infoRow($session,$edit=false,$id=0) {
	$icons="";
	if ($edit) {
		$icons='<div style="display:inline"><a href="/brew-session/edit/'.$session['session_id'].'" alt="Redaguoti" title="Redaguoti" rel="nofollow""><span class="ui-icon ui-icon-wrench">Redaguoti</span></a></div>';
	}
	return '<tr class="bs-tr-'.($id%2).'"><td>'.$icons.'</td><td>'.$session['session_name'].'</td><td><a href="/brewers/'.$session['user_id'].'">'.$session['user_name'].'</a></td><td><a href="/recipes/view/'.$session['recipe_id'].'">'.$session['recipe_name'].'</a></td><td>'.$session['session_size'].'</td><td>'.$session['session_og'].'</td><td>'.$session['session_fg'].'</td><td>'.$session['session_primarydate'].'</td><td>'.$session['session_secondarydate'].'</td><td>'.$session['session_caskingdate'].'</td><td>'.nl2br($session['session_comments']).'<a href="/brew-session/detail/'.$session['session_id'].'"> [detaliau]</a></td></tr></form>';
	}
	function infoColumn($session,$owner=false) {
		$fields=array("session_name"=>"Virimo pavadinimas","user_name"=>"Aludaris","recipe_name"=>"Receptas","session_size"=>"Alaus kiekis (l.)","session_og"=>"OG","session_fg"=>"FG","session_primarydate"=>"Pirminė fermentacija","session_secondarydate"=>"Antrinė fermentacija","session_caskingdate"=>"Išpilstyta","session_comments"=>"Pastabos");
		$str="";
		foreach ($fields as $key => $value) {
			$str.='<tr><td class="bs_title" width="20%">'.$value.'</td><td class="bs_data">'.$session[$key].'</td></tr>';
		}

		return $str;
	}
}
?>
