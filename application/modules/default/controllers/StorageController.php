<?php
class StorageController extends Zend_Controller_Action {
	public function init() {
		$this->storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $this->user_info = $this->storage->read();
		$this->db = Zend_Registry::get("db");
	}
	
	public function indexAction(){
		$db = $this->db;
		if (!isset($this->user_info->user_id) || $this->user_info->user_id == 0){
			$this->_redirect("/");
		}
		$user_id = $this->user_info->user_id;
		$select = $db->select()
				->from("users_groups", array("group_id"))
				->join("groups", "groups.group_id = users_groups.group_id", array("group_name", "group_description"))
				->where("users_groups.user_id = ?", $user_id)
				->where("groups.group_brewcrew = ?", '1');
		$result = $db->fetchAll($select);
		$this->view->in_bc = false;
		if (sizeof($result)>0) $this->view->in_bc = true;

		$this->view->data = array();
		$select = $db->select();
		$select = $db->select();
		$select->from("storage_malt")
				->where("user_id = ?", $user_id)
				->order("malt_name ASC");
		$this->view->data["malt"] = $db->fetchAll($select);
		$select = $db->select();
		$select->from("storage_hops")
				->where("user_id = ?", $user_id)
				->order("hop_name ASC");
		$this->view->data["hops"] = $db->fetchAll($select);
		$select = $db->select();
		$select->from("storage_yeast")
				->where("user_id = ?", $user_id)
				->order("yeast_name ASC");
		$this->view->data["yeast"] = $db->fetchAll($select);
		$select = $db->select();
		$select->from("storage_other")
				->where("user_id = ?", $user_id)
				->order("other_name ASC");
		$this->view->data["other"] = $db->fetchAll($select);
		
		$select = $db->select()
				->from("users_groups", array("group_id"))
				->join("groups", "groups.group_id = users_groups.group_id", array("group_name", "group_description"))
				->where("users_groups.user_id = ?", $user_id)
				->where("groups.group_brewcrew = ?", '1');
		$result = $db->fetchAll($select);
		if (sizeof($result) > 0){
			foreach($result as $key=>$val){
				$select = $db->select()
						->from("users_groups", array("user_id"))
						->join("users", "users.user_id = users_groups.user_id", array("user_name", "user_email"))
						->where("users_groups.group_id = ?", $val['group_id'])
						->where("users_groups.user_id != ?", $user_id);
				$result[$key]['users'] = $db->fetchAll($select);
				if (sizeof($result[$key]['users']) > 0){
					foreach($result[$key]['users'] as $k=>$v){
						$select = $db->select()
								->from("storage_malt")
								->where("user_id = ?", $v['user_id'])
								->where("malt_brewcrew_public = 1")
								->order("malt_name ASC");
						$result[$key]['users'][$k]["malt"] = $db->fetchAll($select);
						$select = $db->select()
								->from("storage_hops")
								->where("user_id = ?", $v['user_id'])
								->where("hop_brewcrew_public = 1")
								->order("hop_name ASC");
						$result[$key]['users'][$k]["hops"] = $db->fetchAll($select);
						$select = $db->select()
								->from("storage_yeast")
								->where("user_id = ?", $v['user_id'])
								->where("yeast_brewcrew_public = 1")
								->order("yeast_name ASC");
						$result[$key]['users'][$k]["yeast"] = $db->fetchAll($select);
						$select = $db->select()
								->from("storage_other")
								->where("user_id = ?", $v['user_id'])
								->where("other_brewcrew_public = 1")
								->order("other_name ASC");
						$result[$key]['users'][$k]["other"] = $db->fetchAll($select);
					}
				}
			}
			$this->view->bc = $result;
		}
	}

	public function editAction() {
		$db = $this->db;
		if (!isset($this->user_info->user_id) || $this->user_info->user_id == 0){
			$this->_redirect("/");
		}
		$user_id = $this->user_info->user_id;
		
		$select = $db->select()
				->from("users_groups", array("group_id"))
				->join("groups", "groups.group_id = users_groups.group_id", array("group_name", "group_description"))
				->where("users_groups.user_id = ?", $user_id)
				->where("groups.group_brewcrew = ?", '1');
		$result = $db->fetchAll($select);
		$this->view->in_bc = false;
		if (sizeof($result)>0) $this->view->in_bc = true;

		if (isset($_POST) && !empty($_POST)){
			$data['malt'] = array();
			$data['hops'] = array();
			$data['yeast'] = array();
			$malt_size = sizeof($_POST['malt_list']);
			foreach($_POST['malt_list'] as $key=>$val){
				$temp = array();
				$temp['name'] = $val;
				$temp['weight'] = $_POST['malt_weight'][$key];
				$temp['ebc'] = $_POST['malt_color'][$key];
				$temp['public'] = $_POST['malt_bc'][$key];
				if ($malt_size-1 > $key)
					$data['malt'][] = $temp;
			}
			$hops_size = sizeof($_POST['hop_list']);
			foreach($_POST['hop_list'] as $key=>$val){
				$temp = array();
				$temp['name'] = $val;
				$temp['weight'] = $_POST['hop_weight'][$key];
				$temp['alpha'] = $_POST['hop_alpha'][$key];
				$temp['public'] = $_POST['hop_bc'][$key];
				if ($hops_size-1 > $key)
					$data['hops'][] = $temp;
			}
			$yeast_size = sizeof($_POST['yeast_list']);
			foreach($_POST['yeast_list'] as $key=>$val){
				$temp = array();
				$temp['name'] = $val;
				$temp['weight'] = $_POST['yeast_weight'][$key];
				$temp['public'] = $_POST['yeast_bc'][$key];
				if ($yeast_size-1 > $key)
					$data['yeast'][] = $temp;
			}
			$other_size = sizeof($_POST['other_list']);
			foreach($_POST['other_list'] as $key=>$val){
				$temp = array();
				$temp['name'] = $val;
				$temp['weight'] = $_POST['other_weight'][$key];
				$temp['public'] = $_POST['other_bc'][$key];
				if ($other_size-1 > $key)
					$data['other'][] = $temp;
			}
			$delete = $db->delete("storage_hops", "user_id = '".$user_id."'");
			$delete = $db->delete("storage_malt", "user_id = '".$user_id."'");
			$delete = $db->delete("storage_yeast", "user_id = '".$user_id."'");
			$delete = $db->delete("storage_other", "user_id = '".$user_id."'");
			foreach($data['malt'] as $key=>$val){
				$insert = $db->insert("storage_malt", array(
					"user_id" => $user_id,
					"malt_name" => $val['name'],
					"malt_ebc" => $val['ebc'],
					"malt_brewcrew_public" => $val['public'],
					"malt_weight" => $val['weight']
				));
			}
			foreach($data['hops'] as $key=>$val){
				$insert = $db->insert("storage_hops", array(
					"user_id" => $user_id,
					"hop_name" => $val['name'],
					"hop_alpha" => $val['alpha'],
					"hop_brewcrew_public" => $val['public'],
					"hop_weight" => $val['weight']
				));
			}
			foreach($data['yeast'] as $key=>$val){
				$insert = $db->insert("storage_yeast", array(
					"user_id" => $user_id,
					"yeast_name" => $val['name'],
					"yeast_brewcrew_public" => $val['public'],
					"yeast_weight" => $val['weight']
				));
			}
			foreach($data['other'] as $key=>$val){
				$insert = $db->insert("storage_other", array(
					"user_id" => $user_id,
					"other_name" => $val['name'],
					"other_brewcrew_public" => $val['public'],
					"other_weight" => $val['weight']
				));
			}
			$this->_redirect("/storage");
		}
		
		$this->view->data = array();
		$select = $db->select();
		$select = $db->select();
		$select->from("storage_malt")
				->where("user_id = ?", $user_id)
				->order("malt_name ASC");
		$mlts = $db->fetchAll($select);
		foreach($mlts as $key=>$val){
			$mlts[$key]['malt_name'] = html_entity_decode($val['malt_name']);
		}
		$this->view->data["malt"] = $mlts;
		$select = $db->select();
		$select->from("storage_hops")
				->where("user_id = ?", $user_id)
				->order("hop_name ASC");
		$this->view->data["hops"] = $db->fetchAll($select);
		$select = $db->select();
		$select->from("storage_yeast")
				->where("user_id = ?", $user_id)
				->order("yeast_name ASC");
		$this->view->data["yeast"] = $db->fetchAll($select);
		$select = $db->select();
		$select->from("storage_other")
				->where("user_id = ?", $user_id)
				->order("other_name ASC");
		$this->view->data["other"] = $db->fetchAll($select);
		
		$select = $db->select()
			->from("beer_malt")
			->order("malt_name");
		$this->view->malts = $db->fetchAll($select);
		$select = $db->select()
			->from("beer_styles")
			->order("style_name");
		$this->view->styles = $db->fetchAll($select);
		$select = $db->select()
			->from("beer_hops")
			->order("hop_name");
		$this->view->hops = $db->fetchAll($select);
		$select = $db->select()
			->from("beer_yeasts")
			->order("yeast_name");
		$this->view->yeasts = $db->fetchAll($select);
		$select = $db->select()
			->from("beer_others")
			->order("other_name");
		$this->view->others = $db->fetchAll($select);
	}
}