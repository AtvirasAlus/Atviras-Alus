<?
class Entities_Settings extends Zend_Db_Table {
	protected $_name="settings";
	protected $_primary = 'setting_id';

	public function __construct() {
	
		 parent::__construct(); 
	}
	public static function get($key="") {
		$db=new Entities_Settings();
		if (strlen($key)) {
			if ($setting=$db->fetchRow($db->select(array("setting_key"))->where("setting_key = ?",$key))) {
				if (isset($setting["setting_value"])) {
						return trim($setting["setting_value"]);
				}
			};
				
		}else{
			$rsettings=array();
			if ($settings=$db->fetchAll($db->select(array("setting_key","setting_value")))) {
				for($i=0;$i<count($settings);$i++) {
					$rsettings[$settings[$i]["setting_key"]]=trim($settings[$i]["setting_value"]);
				}
			}
			return $rsettings;
		}
	}
	public function getKeys() {
		return $this->fetchAll($this->select(array("setting_key")));
	}
	public static function set($values) {
		$db=new Entities_Settings();
		$keys=$db->getKeys();
		for ($i=0;$i<count($keys);$i++) {
			if (isset($values[$keys[$i]['setting_key']])){
				$db->update(array("setting_value"=>$values[$keys[$i]['setting_key']]),"setting_key = '".$keys[$i]['setting_key']."'");
			}
		}
		
	}
	public static function getTemplate($key) {
		$db = Zend_Registry::get("db");
		$select=$db->select()
		->from("templates")
		->where("template_code = ?",$key);
		return $db->fetchRow($select);
		
		
	}
	
	
}
