<?php

class CommentsController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
		}
		$this->_helper->layout()->setLayout('layoutnew');
	}

	function indexAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_recipes_comments", array("comment_id", "comment_brewer", "comment_recipe", "comment_date" => "MAX(comment_date)"))
				->join("VIEW_public_recipes", "VIEW_public_recipes.recipe_id=beer_recipes_comments.comment_recipe", array("recipe_name", "recipe_id"))
				->join("users", "beer_recipes_comments.comment_brewer=users.user_id", array("user_name", "user_email", "user_id"))
				->group("comment_id")
				->order("comment_date DESC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(100);
	}
            
	function submitAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get('db');
			if (isset($_POST['recipe_id'])) {
				$db->insert("beer_recipes_comments", array("comment_recipe" => $_POST['recipe_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
				Entities_Events::trigger("new_recipe_comment", array("comment_recipe" => $_POST['recipe_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
				$this->_redirect("/recipes/view/" . $_POST['recipe_id']);
			} else {
				if (isset($_POST['idea_id'])) {
					$db->insert("idea_comments", array("idea_id" => $_POST['idea_id'], "user_id" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
					$this->_redirect("/ideja/" . $_POST['idea_id']);
				} else {
				$db->insert("beer_articles_comments", array("comment_article" => $_POST['article_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
					$this->_redirect("/content/read/1/" . $_POST['article_id']);
				}
			}
		} else {
			$this->_redirect("/");
		}
	}
        function textAction() {
            $db = Zend_Registry::get('db');
            $this->_helper->layout->setLayout('empty');
            $this->_helper->viewRenderer->setNoRender(true);
            if (isset($_GET['comment_id'])) {
                switch($_GET['part']) {
                    case 'recipe':
                        $table = "beer_recipes_comments";
                        break;
                    case 'article':
                         $table = "beer_articles_comments";
                        break;
                    case 'idea':
                         $table = "idea_comments";
                        break;
                }
                if (isset($table)) {
                    $select = $db->select()
                            ->from($table, array("comment_text"))
                            ->where("comment_id=?",$_GET['comment_id']);
                
                    if ($result = $db->fetchRow($select)) {
                        print $result['comment_text'];
                    }
                }
                       
            }
        }
        function updateAction() {
            $this->_helper->layout->setLayout('empty');
            $this->_helper->viewRenderer->setNoRender(true);
            if (isset($_POST)) {
                    $db = Zend_Registry::get('db');
                    if (isset($_POST['recipe'])) {
                        $table = "beer_recipes_comments";
                        }else if (isset($_POST["idea"])) {
                            $table = "idea_comments";
                        }else if (isset($_POST["article"])) {
                            $table = "beer_articles_comments";
                        }
                    
                   if (isset($table)) {
                        if ($db->update($table, array("comment_moddate"=>date('Y-m-d H:i:s', time()),"comment_text" => strip_tags($_POST['comment_text'], '<a>')),"comment_id = " . $_POST['comment_id'])) {
                            $comment_text= preg_replace('@((?:[^"\'])(http|ftp)+(s)?:(//)((\w|\.|\-|_)+)(/)?(\S+)?)@',' <a href="$1" rel="nofollow" target="blank">$1</a>',str_replace(array("\n"), "\n<br />\n", strip_tags($_POST['comment_text'], '<a>')));
                            print Zend_Json::encode(array("status" => 0,"comment_moddate"=>date('Y-m-d H:i:s', time()), "comment_text" => $comment_text));  
                        }else{
                            print Zend_Json::encode(array("status" => 1, "old_comment"=>  nl2br($_POST['old_comment'])));
                        }
                       
                   }
            } else {

            }
        }
	function deleteAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get('db');
			if (isset($_POST["article"])) {
				$db->delete("beer_articles_comments", array("comment_id = " . $_POST['comment_id']));
			} else {
				if (isset($_POST["idea"])) {
					$db->delete("idea_comments", array("comment_id = " . $_POST['comment_id']));
				} else {
                                        
					$db->delete("beer_recipes_comments", array("comment_id = " . $_POST['comment_id']));
                                        Entities_Events::trigger("delete_recipe_comment", array("comment_id" => $_POST['comment_id']));
				}
			}
		} else {
			
		}
		print Zend_Json::encode(array("status" => 0, "data" => array()));
	}

}