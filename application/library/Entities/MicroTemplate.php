<?
class Entities_MicroTemplate {
	public static function render($tpl,$vars) {
		foreach($vars as $tag=>$data){
                	$tpl=preg_replace('/{'.$tag.'}/',$data,$tpl);
		}
		return $tpl;
	}
	public static function get($tpl) {
    return Entities_Settings::getTemplate($tpl);
	}
}
?>
