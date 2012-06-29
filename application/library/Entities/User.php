<?
class Entities_User {
    var $user_id;
    var $db;
    var $properties;
   function Entities_User($id=0) {
      $this->user_id=$id;
      $this->db = Zend_Registry::get('db');
    }
    //
    public function getProperties() {
     if ($this->user_id>0) {
        $select = $this->db->select()
        ->from('users')
        ->where('user_id = ?',$this->user_id);
        $this->properties=$this->db->fetchRow($select);
        return $this->properties;
      }
      return false;
    }
    public function getAttributes() {
      if ($this->user_id>0) {
        $select = $this->db->select()
        ->from("users_attributes")
        ->where("user_id = ?", $this->user_id);
        if (!$row = $this->db->fetchRow($select)) {
          $row = array("user_id" => $this->user_id, "user_location" => "", "user_about" => "","user_mail_comments"=>'0', "beta_tester"=>'0');
        }
        $row["user_about_plain"] = $row["user_about"];
        $row["user_about"] = preg_replace('/((?:[^"\'])(?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i', '<a href="$1" rel="nofollow" target="blank">$1</a>', nl2br($row["user_about"]));
        return $row;
      }else{
        return null;
      }
    }
    //
    public function getGroups() {
      if ($this->user_id>0) {
        $select = $this->db->select()
            ->from("users_groups", array("user_id" => "concat('0')"))
            ->joinRight("groups", "groups.group_id=users_groups.group_id")
            ->where("groups.group_public = ?", "1")
            ->where("groups.group_registration = ?", "public")
            ->orWhere("users_groups.user_id = ?", $this->user_id)
            ->group("groups.group_id");
        $aviable_gr = $this->db->fetchAll($select);
        $select = $this->db->select()
            ->from("users_groups")
            ->where("user_id = ?", $this->user_id);
        $subscribed_gr = $this->db->fetchAll($select);
        for ($i = 0; $i < count($aviable_gr); $i++) {
          for ($ii = 0; $ii < count($subscribed_gr); $ii++) {
            if ($aviable_gr[$i]["group_id"] == $subscribed_gr[$ii]["group_id"]) {
              $aviable_gr[$i]["user_id"] = $this->user_id;
            }
          }
        }
        return $aviable_gr;
      }else{
        return  null;
      }
    }
    public function updateGroups($groups) {
     if ($this->user_id>0) {
        $where = array();
        $where[] = $this->db->quoteInto('user_id = ?', $this->user_id);
        $this->db->delete("users_groups", $where);

        for ($i = 0; $i < count($groups); $i++) {
          $this->db->insert("users_groups", array("user_id" => $this->user_id, "group_id" => $groups[$i]));
        }
        return true;
      }
      return false;
    }
    public function updateAttributes($att) {
      if ($this->user_id>0) {
        $where = array();
        $where[] = $this->db->quoteInto('user_id = ?', $this->user_id);
        $this->db->delete("users_attributes", $where);
        $stripTags = new Zend_Filter_StripTags(array('p', 'b', 'br', 'strong'), array());
        $user_about = $stripTags->filter($att["user_about"]);
        return $this->db->insert("users_attributes", array("user_id" => $this->user_id, "user_location" => $att["user_location"], "user_about" => $user_about,'user_mail_comments'=>$att["user_mail_comments"],'beta_tester'=>$att['beta_tester']));
      }else{
        return false;
      }
    }
}
?>