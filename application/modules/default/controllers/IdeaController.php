<?php

class IdeaController extends Zend_Controller_Action {

	public function init() {
		
	}

	public function indexAction() {
		
	}

	public function getfileAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$idea_id = $this->getRequest()->getParam('idea');
		$file_id = $this->getRequest()->getParam('file');
		if ($file_id < 1 || $file_id > 3 || !is_numeric($file_id)) {
			exit;
		}
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("idea_items", array("idea_file_" . $file_id . " as file"))
				->where("idea_items.idea_id = ?", $idea_id)
				->limit(1);
		$result = $db->fetchRow($select);

		$path = $_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $idea_id . "/";
		$fullPath = $path . $file_id . "_" . $result['file'];
		if (file_exists($fullPath)) {
			$fsize = filesize($fullPath);
			$path_parts = pathinfo($fullPath);
			switch ($path_parts['extension']) {
				case "pdf": $ctype = "application/pdf";
					break;
				case "exe": $ctype = "application/octet-stream";
					break;
				case "zip": $ctype = "application/zip";
					break;
				case "doc": $ctype = "application/msword";
					break;
				case "xls": $ctype = "application/vnd.ms-excel";
					break;
				case "ppt": $ctype = "application/vnd.ms-powerpoint";
					break;
				case "gif": $ctype = "image/gif";
					break;
				case "png": $ctype = "image/png";
					break;
				case "jpeg":
				case "jpg": $ctype = "image/jpg";
					break;
				default: $ctype = "application/force-download";
			}
			header('Content-Description: File Transfer');
			header("Content-type: " . $ctype);
			header('Content-Disposition: attachment; filename="' . $result['file'] . '"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-length: " . filesize($fullPath));
			ob_end_flush();
			ob_flush();
			flush();
			ob_start();
			readfile($fullPath);
			exit;
		}
	}

	public function voteAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->view->user_info = $user_info;
		$this->_helper->layout->setLayout('empty');
		if (isset($_POST['idea_id'])) {
			$select = $db->select()
					->from("idea_items")
					->where("idea_id = ?", $_POST['idea_id']);
			$idea = $db->fetchRow($select);
			$me = -1;
			if (isset($user_info->user_id) && !empty($user_info->user_id))
				$me = $user_info->user_id;

			if ($me != -1 && $me != $idea['user_id']) {
				$db->delete("idea_votes", array("user_id = '" . $me . "'", "idea_id = '" . $_POST['idea_id'] . "'"));
				switch ($_POST['vote_value']) {
					case "m":
						$v_val = -1;
						break;
					case "p":
						$v_val = 1;
						break;
					default:
						$v_val = 0;
				}
				$db->insert("idea_votes", array(
					"idea_id" => $_POST['idea_id'],
					"user_id" => $me,
					"vote_value" => $v_val,
				));
				$select = $db->select()
						->from("idea_votes", array('SUM(vote_value) AS suma'))
						->where("idea_votes.idea_id = ?", $_POST['idea_id']);
				$result = $db->fetchRow($select);
				$db->update("idea_items", array("idea_vote_sum" => $result['suma']), array("idea_id = '" . $_POST['idea_id'] . "'"));
			}



			$select = $db->select()
					->from("idea_items")
					->join("users", "users.user_id=idea_items.user_id", array("user_name", "user_email"))
					->joinLeft("idea_votes", "idea_votes.idea_id=idea_items.idea_id AND idea_votes.user_id='" . $me . "'", array("vote_value"))
					->where("idea_items.idea_id = ?", $_POST['idea_id'])
					->limit(1);
			$result = $db->fetchRow($select);
			$select = $db->select()
					->from("idea_votes")
					->where("idea_votes.idea_id = ?", $_POST['idea_id']);
			$votes = $db->fetchAll($select);
			$result['total_votes'] = sizeof($votes);
			if ($votes == 0) {
				$result['up_size'] = 0;
				$result['down_size'] = 0;
				$result['neutral_size'] = 0;
			} else {
				$t_up = 0;
				$t_down = 0;
				$t_neutral = 0;
				foreach ($votes as $vote) {
					if ($vote['vote_value'] == "1")
						$t_up++;
					if ($vote['vote_value'] == "0")
						$t_neutral++;
					if ($vote['vote_value'] == "-1")
						$t_down++;
				}
				if ($t_up == 0) {
					$result['up_size'] = 0;
				} else {
					$result['up_size'] = round(100 * $t_up / sizeof($votes));
				}
				if ($t_neutral == 0) {
					$result['neutral_size'] = 0;
				} else {
					$result['neutral_size'] = round(100 * $t_neutral / sizeof($votes));
				}
				if ($t_down == 0) {
					$result['down_size'] = 0;
				} else {
					$result['down_size'] = round(100 * $t_down / sizeof($votes));
				}
				$this->view->idea = $result;
			}
		} else {
			$this->_helper->viewRenderer->setNoRender(true);
			print "error";
		}
	}

	public function listAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		$type = $this->getRequest()->getParam("type");
		if (empty($type))
			$type = "new";
		$this->view->type = $type;
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("idea_items")
				->join("users", "users.user_id=idea_items.user_id", array("user_name", "user_email"))
				->joinLeft("idea_votes", "idea_votes.idea_id=idea_items.idea_id AND idea_votes.user_id='" . $me . "'", array("vote_value"))
				->joinLeft("VIEW_idea_comments_total", "VIEW_idea_comments_total.idea_id=idea_items.idea_id", array("total as comments"));
		if ($type == "finished") {
			$select->where("idea_items.idea_status = ?", "1");
			$select->order("idea_items.idea_finishdate DESC");
		} else {
			if ($type == "rejected"){
				$select->where("idea_items.idea_status = ? OR idea_items.idea_vote_sum <= 0", "2");
				$select->order("idea_items.idea_finishdate DESC");
			} else {
				if ($type != "my") {
					$select->where("idea_items.idea_status != ?", "1");
				}
			}
		}
		if ($type == "top") {
			$select->order("idea_vote_sum DESC");
			$select->where("idea_items.idea_vote_sum > 0");
		} else {
			$select->order("idea_posted DESC");
		}
		if ($type == "my") {
			$select->where("idea_items.user_id = ?", $me);
		}
		if ($type == "unvoted") {
			$select->where("idea_votes.vote_value is NULL AND idea_items.user_id != ?", $me);
		}
		$result = $db->fetchAll($select);
		foreach ($result as $key => $row) {
			$select = $db->select()
					->from("idea_votes")
					->where("idea_votes.idea_id = ?", $row['idea_id']);
			$votes = $db->fetchAll($select);
			$result[$key]['total_votes'] = sizeof($votes);
			if ($votes == 0) {
				$result[$key]['up_size'] = 0;
				$result[$key]['down_size'] = 0;
				$result[$key]['neutral_size'] = 0;
			} else {
				$t_up = 0;
				$t_down = 0;
				$t_neutral = 0;
				foreach ($votes as $vote) {
					if ($vote['vote_value'] == "1")
						$t_up++;
					if ($vote['vote_value'] == "0")
						$t_neutral++;
					if ($vote['vote_value'] == "-1")
						$t_down++;
				}
				if ($t_up == 0) {
					$result[$key]['up_size'] = 0;
				} else {
					$result[$key]['up_size'] = round(100 * $t_up / sizeof($votes));
				}
				if ($t_neutral == 0) {
					$result[$key]['neutral_size'] = 0;
				} else {
					$result[$key]['neutral_size'] = round(100 * $t_neutral / sizeof($votes));
				}
				if ($t_down == 0) {
					$result[$key]['down_size'] = 0;
				} else {
					$result[$key]['down_size'] = round(100 * $t_down / sizeof($votes));
				}
			}
		}
		$adapter = new Zend_Paginator_Adapter_Array($result);
		$page=$this->_getParam('page');
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($page);
		$this->view->content->setItemCountPerPage(10);
	}

	public function listnewAction() {
		$this->_forward("list", null, null, array("type" => "new"));
	}

	public function listmyAction() {
		$this->_forward("list", null, null, array("type" => "my"));
	}
	
	public function listunvotedAction() {
		$this->_forward("list", null, null, array("type" => "unvoted"));
	}

	public function listtopAction() {
		$this->_forward("list", null, null, array("type" => "top"));
	}

	public function listfinishedAction() {
		$this->_forward("list", null, null, array("type" => "finished"));
	}

	public function listrejectedAction() {
		$this->_forward("list", null, null, array("type" => "rejected"));
	}

	public function viewAction() {
		$idea_id = $this->getRequest()->getParam("idea");

		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("idea_items")
				->join("users", "users.user_id=idea_items.user_id", array("user_name", "user_email"))
				->joinLeft("idea_votes", "idea_votes.idea_id=idea_items.idea_id AND idea_votes.user_id='" . $me . "'", array("vote_value"));
		$select->where("idea_items.idea_id = ?", $idea_id);
		$result = $db->fetchRow($select);
		$select = $db->select()
				->from("idea_votes")
				->where("idea_votes.idea_id = ?", $idea_id);
		$votes = $db->fetchAll($select);
		$result['total_votes'] = sizeof($votes);
		if ($votes == 0) {
			$result['up_size'] = 0;
			$result['down_size'] = 0;
			$result['neutral_size'] = 0;
		} else {
			$t_up = 0;
			$t_down = 0;
			$t_neutral = 0;
			foreach ($votes as $vote) {
				if ($vote['vote_value'] == "1")
					$t_up++;
				if ($vote['vote_value'] == "0")
					$t_neutral++;
				if ($vote['vote_value'] == "-1")
					$t_down++;
			}
			if ($t_up == 0) {
				$result['up_size'] = 0;
			} else {
				$result['up_size'] = round(100 * $t_up / sizeof($votes));
			}
			if ($t_neutral == 0) {
				$result['neutral_size'] = 0;
			} else {
				$result['neutral_size'] = round(100 * $t_neutral / sizeof($votes));
			}
			if ($t_down == 0) {
				$result['down_size'] = 0;
			} else {
				$result['down_size'] = round(100 * $t_down / sizeof($votes));
			}
		}
		$this->view->idea = $result;
	}
	
	public function commentsAction(){
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->view->user_info = $user_info;
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("idea_comments")
				->join("users", "users.user_id=idea_comments.user_id", array("user_name", "user_email"))
				->joinLeft("idea_items", "idea_items.idea_id=idea_comments.idea_id", array("idea_title"))
				->order("idea_comments.comment_date DESC")
				->limit(20);
		$result = $db->fetchAll($select);
		$this->view->comments = $result;
	}
	public function createAction(){
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->view->user_info = $user_info;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$this->view->please_login = false;
			$user_id = $user_info->user_id;
			$form = new Form_Idea();
			$this->view->createForm = $form;
			$db = Zend_Registry::get('db');
			if ($this->_request->isPost()) {
				$formData = $this->_request->getPost();
				$db->insert("idea_items", array(
						"idea_title" =>  trim(strip_tags($formData['title'])),
						"idea_description" => strip_tags($formData['description'], "<a><b><i>"),
						"user_id" => $user_id,
						"idea_posted" => date("Y-m-d H:i:s"),
						"idea_full_text" => strip_tags($formData['full_text'], "<a><b><i>"),
					));
				$last_id = $db->lastInsertId();
				$uploadedData = $form->getValues();
				$files_to_upload = array();
				if (!empty($uploadedData['files1'])) $files_to_upload[] = "file1";
				if (!empty($uploadedData['files2'])) $files_to_upload[] = "file2";
				if (!empty($uploadedData['files3'])) $files_to_upload[] = "file3";
				$upload = new Zend_File_Transfer_Adapter_Http();
				try {
					$upload->receive($files_to_upload);
				} catch (Zend_File_Transfer_Exception $e) {
					$e->getMessage();
				}
				$name = $upload->getFileName('files1');
				if (!empty($name)){
					$name_s = str_replace(" ", "-", $upload->getFileName('files1', false));
					@mkdir($_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id);
					$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id . "/1_".$name_s;
					copy($name, $fullFilePath);
					$db->update("idea_items", array("idea_file_1" => $name_s), array("idea_id = '" . $last_id . "'"));
				}
				$name = $upload->getFileName('files2');
				if (!empty($name)){
					$name_s = str_replace(" ", "-", $upload->getFileName('files2', false));
					@mkdir($_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id);
					$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id . "/2_".$name_s;
					copy($name, $fullFilePath);
					$db->update("idea_items", array("idea_file_2" => $name_s), array("idea_id = '" . $last_id . "'"));
				}
				$name = $upload->getFileName('files3');
				if (!empty($name)){
					$name_s = str_replace(" ", "-", $upload->getFileName('files3', false));
					@mkdir($_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id);
					$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/ideas/" . $last_id . "/3_".$name_s;
					copy($name, $fullFilePath);
					$db->update("idea_items", array("idea_file_3" => $name_s), array("idea_id = '" . $last_id . "'"));
				}
				$this->_redirect('/idejos/naujausios');
			}
		} else {
			$this->view->please_login = true;
		}
	}

}