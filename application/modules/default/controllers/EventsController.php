<?

class EventsController extends Zend_Controller_Action {

    private $userInfo;

    public function init() {
        $this->storage = new Zend_Auth_Storage_Session();
        $this->db = Zend_Registry::get('db');
        $this->user = $this->storage->read();
		$user_info = $this->storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$select = $this->db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $this->db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
		}
		$this->_helper->layout()->setLayout('layoutnew');
    }

    function indexAction() {


        $select = $this->db->select()
                ->from("beer_events", array("*", "DATE_FORMAT(event_start, '%Y-%m-%d %H:%i') as event_start"))
                ->where("beer_events.event_published = ?", '1')
                ->order("beer_events.event_start DESC");

        //$this->view->recipes=$this->db->fetchAll($select);
         $this->view->editable = $this->canEdit(-1);
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);
        $this->view->content = new Zend_Paginator($adapter);
        $this->view->content->setCurrentPageNumber($this->_getParam('page'));
        $this->view->content->setItemCountPerPage(21);
    }

    function viewAction() {
        $storage = new Zend_Auth_Storage_Session();

        $euid = explode("-", $this->_getParam('event'));
        $event_id = $euid[0];
        
        $this->view->editable = false;
        if ($event_id > 0) {
            $this->db = Zend_Registry::get("db");
            $select = $this->db->select();
            $select->from("beer_events", array("*", "DATE_FORMAT(event_start, '%Y-%m-%d %H:%i') as event_start"))
                    ->where("event_id = ?", $event_id);
            $this->view->event = $this->db->fetchRow($select);
            $select = $this->db->select();
            $select->from("beer_events_users", array())
                    ->join("users", "beer_events_users.user_id=users.user_id", array("user_id" => "group_concat(users.user_id)"))
                    ->where("event_id=?", $event_id);
            $this->view->registered_users = array();
            $r_users = $this->db->fetchRow($select);
            if (isset($r_users["user_id"])) {
                $select = $this->db->select();
                $select->from("users")
                        ->where("user_id in (" . $r_users["user_id"] . ")");
                $this->view->registered_users = $this->db->fetchAll($select);
            }
             $select = $this->db->select();
                        $select->from("beer_events")
                                ->join("beer_events_groups","beer_events.event_id=beer_events_groups.event_id")
                                ->join("groups","beer_events_groups.group_id=groups.group_id")
                                ->where("beer_events.event_id = ?", $event_id);
             $group_id=0;           
             if ($row=$this->db->fetchRow($select)) {
                 $this->view->event_group=$row;
                 $group_id=$row['group_id'];
             }
        
        if (isset($this->user->user_id)) {

            $select = $this->db->select();
            $select->from("beer_events_users")
                    ->where("event_id = ?", $event_id)
                    ->where("user_id = ?", $this->user->user_id);

            $t = $this->db->fetchAll($select);
            if (count($t) > 0) {
                $this->view->registration_status = 2;
            } else {
                $this->view->registration_status = 1;
            }

            $this->view->user_id = $this->user->user_id;
            //visi aludariu receptai 
            if ($this->view->event['event_type'] == 'competition') {
                $select = $this->db->select();
                $select->from("beer_recipes")
                        ->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
                        ->joinLeft("beer_competition_entries", "beer_recipes.recipe_id=beer_competition_entries.recipe_id AND beer_competition_entries.event_id='" . $event_id . "'", "event_id")
                        ->where("brewer_id = ?", $this->user->user_id)
                        ->order('beer_recipes.recipe_name');
                $beer_recipes = $this->db->fetchAll($select);
                $this->view->beer_recipes = $beer_recipes;
            }
        } else {
            $this->view->registration_status = 0;
        }
        $this->view->editable = $this->canEdit($group_id);
        }
    }

    function registerAction() {
        $this->_helper->layout->setLayout('empty');
        $u = $this->user;
        if (isset($u->user_name)) {
            if (isset($_POST)) {
                $this->db->delete("beer_events_users", "event_id = " . $_POST['id'] . ' and user_id = ' . $u->user_id);
                switch ($_POST['action']) {
                    case "in":
                        $this->db->insert("beer_events_users", array("event_id" => $_POST['id'], "user_id" => $u->user_id));
                        break;
                }
                $select = $this->db->select();
                $select->from("beer_events_users", array())
                        ->join("users", "beer_events_users.user_id=users.user_id", array("user_id" => "group_concat(users.user_id)"))
                        ->where("event_id=?", $_POST['id']);
                $this->view->registered_users = array();
                $r_users = $this->db->fetchRow($select);
                if (isset($r_users["user_id"])) {
                    $select = $this->db->select();
                    $select->from("users")
                            ->where("user_id in (" . $r_users["user_id"] . ")");
                    $this->view->registered_users = $this->db->fetchAll($select);
                }
            }
        } else {

            $this->_helper->viewRenderer->setNoRender(true);
            print "Neregistruotas nautotojas";
        }
    }

    private function getAviableGroups() {
        $groups = array();
        $select = $this->db->select();
        $select->from("groups");
        if ($this->user->user_type != "admin") {
            $select->joinLeft("users_groups", "users_groups.group_id=groups.group_id", array())
                    ->where("users_groups.user_id = ?", $this->user->user_id)
                    ->where("users_groups.user_status= ?", "admin");
        } else {
            $groups[] = array("group_name" => "", "group_description" => "Renginys nepriskiriamas grupėms", "group_id" => 0);
        }
        if ($rows = $this->db->fetchAll($select)) {
            $groups = array_merge($groups, $rows);
        }
        return $groups;
    }

    private function canEdit($group_id = -1) {
        $editable = false;

        if (isset($this->user->user_id)) {
            $editable = ($this->user->user_type == "admin" || $this->user->user_type == "moderator") ? true : false;
            if (!$editable) {
                $select = $this->db->select();
                $select->from("users_groups", array("group_id"))
                        ->where("users_groups.user_id = ?", $this->user->user_id);
                if ($group_id > -1) {
                    $select->where("users_groups.group_id = ?", $group_id);
                }
                $select->where("users_groups.user_status= ?", "admin");

                if ($row = $this->db->fetchRow($select)) {
                    return true;
                }
            }
        }

        return $editable;
    }

    function myEntriesAction() {
        $this->_helper->layout->setLayout('empty');


        $this->view->event_id = $this->_getParam("event_id");
        $this->view->user_id = $this->user->user_id;

        //Uzregistruoti receptai
        $select = $this->db->select();
        $select->from("beer_competition_entries")
                ->where("event_id = ?", $this->view->event_id)
                ->where("event_user_id = ?", $this->user->user_id);
        $beer_competition_entries = $this->db->fetchAll($select);
        $this->view->beer_competition_entries = $beer_competition_entries;

        $this->view->beer_competition_entries_recipes = array();
        foreach ($beer_competition_entries as $entry) {
            $this->view->beer_competition_entries_recipes[] = $entry['recipe_id'];
        }
        $beer_recipes = array();
        if (!empty($this->view->beer_competition_entries_recipes)) {
            $select = $this->db->select();
            $select->from("beer_recipes")
                    ->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
                    ->where("brewer_id = ?", $this->user->user_id)
                    ->where("recipe_id IN(?)", $this->view->beer_competition_entries_recipes)
                    ->order('beer_recipes.recipe_name');
            $beer_recipes = $this->db->fetchAll($select);
        }

        $this->view->beer_recipes = $beer_recipes;
    }

    function myAllRecipesAction() {
        $this->_helper->layout->setLayout('empty');


        $this->view->event_id = $this->_getParam("event_id");
        $this->view->user_id = $this->user->user_id;

        $select = $this->db->select();
        $select->from("beer_recipes")
                ->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
                ->where("brewer_id = ?", $this->user->user_id)
                ->order('beer_recipes.recipe_name');
        $beer_recipes = $this->db->fetchAll($select);
        $this->view->beer_recipes = $beer_recipes;
    }

    function entryRegistrationAction() {
        $this->_helper->layout->setLayout('empty');
        $this->_helper->viewRenderer->setNoRender(true);
        $u = $this->user;
        if (isset($u->user_name)) {
            if (isset($_POST)) {
                switch ($_POST['action']) {
                    case "add":
                        $this->db->insert("beer_competition_entries", array("event_id" => $_POST['event_id'], "event_user_id" => $_POST['event_user_id'], "recipe_id" => $_POST['recipe_id'], "style_id" => $_POST['style_id'], 'created_at' => date('Y:m:d H:i:s')));
                        break;
                    case "remove":
                        $this->db->delete("beer_competition_entries", 'event_id = ' . (int) $_POST['event_id'] . ' AND event_user_id = ' . (int) $_POST['event_user_id'] . ' AND recipe_id = ' . (int) $_POST['recipe_id']);
                        break;
                }
            }
        } else {
            print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas nautotojas", "type" => "authentication"))));
        }
    }

    public function modEventAction() {

        if (isset($_GET['event_id'])) {
            if (is_numeric($_GET['event_id'])) {
                $event_id = intval($_GET['event_id']);
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'MOD':
                            if (isset($this->user->user_id)) {

                                $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : 0;
                                $where = array();
                                $where[] = $this->db->quoteInto('event_id = ?', $event_id);
                                if ($this->canEdit($group_id)) {
                                    $this->db->update("beer_events", array(
                                        "event_name" => strip_tags($_POST['event_name']),
                                        "event_resume" => trim(strip_tags($_POST['event_resume'])),
                                        "event_description" => $_POST['event_description'],
                                        "event_start" => $_POST['event_start'],
                                        "event_published" => isset($_POST['event_published']) ? 1 : 0,
                                        "event_registration_end" => $_POST['event_registration_end'],
                                        "event_posted" => date("Y-m-d H:i:s")
                                            ), $where);

                                    $where = array();
                                    $where[] = $this->db->quoteInto('event_id = ?', $event_id);
                                    $this->db->delete('beer_events_groups', $where);
                                    if (is_numeric($group_id) && $group_id > 0) {

                                        $this->db->insert('beer_events_groups', array('group_id' => $group_id, 'event_id' => $event_id));
                                    }

                                    $this->_redirect('/events/mod-event/?event_id=' . $event_id);
                                }
                            }
                            break;
                    }
                } else {
                    $group_id = 0;
                    $select = $this->db->select()
                            ->from('beer_events_groups')
                            ->where('event_id = ?', $event_id);
                    if ($row = $this->db->fetchRow($select)) {
                        $group_id = $row['group_id'];
                    }
                    if ($this->canEdit($group_id)) {
                        $select = $this->db->select();
                        $select->from("beer_events")
                                ->where("event_id = ?", $event_id);
                        if ($row = $this->db->fetchRow($select)) {
                            $this->view->event = $row;
                            $this->view->event['group_id'] = $group_id;
                        };
                        $this->view->groups = $groups = $this->getAviableGroups();
                        $this->view->editable = count($groups) > 0;
                    }
                }
            }
        }
    }

    public function createEventAction() {
        if (isset($this->user->user_id)) {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'ADD':
                        if (isset($this->user->user_id)) {
                            $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : 0;
                            if ($this->canEdit($group_id)) {
                                $this->db->insert("beer_events", array(
                                    "event_name" => strip_tags($_POST['event_name']),
                                    "event_resume" => trim(strip_tags($_POST['event_resume'])),
                                    "event_description" => $_POST['event_description'],
                                    "event_start" => $_POST['event_start'],
                                    "event_published" => isset($_POST['event_published']) ? 1 : 0,
                                    "event_registration_end" => $_POST['event_registration_end'],
                                    "event_posted" => date("Y-m-d H:i:s")
                                ));
                                $ev_id = $this->db->lastInsertId();

                                if (is_numeric($_POST['group_id']) && $group_id > 0) {

                                    $this->db->insert('beer_events_groups', array('group_id' => $group_id, 'event_id' => $ev_id));
                                }
                                $this->_redirect('/events/mod-event/?event_id=' . $ev_id);
                            }
                        }
                        break;
                }
            } else {

                $this->view->groups = $groups = $this->getAviableGroups();
                $this->view->editable = count($groups) > 0;
            }
        }
    }

}