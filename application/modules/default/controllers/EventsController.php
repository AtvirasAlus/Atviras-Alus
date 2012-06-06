<?

class EventsController extends Zend_Controller_Action {

	private $userInfo;

	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
	}

	function indexAction() {

		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_events")
				->where("beer_events.event_published = ?", '1')
				->order("beer_events.event_start DESC");

		//$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(21);
	}

	function viewAction() {
		$storage = new Zend_Auth_Storage_Session();

		$euid = explode("-", $this->_getParam('event'));
		$event_id = $euid[0];

		if ($event_id > 0) {
			$db = Zend_Registry::get("db");
			$select = $db->select();
			$select->from("beer_events")
					->where("event_id = ?", $event_id);
			$this->view->event = $db->fetchRow($select);
			$select = $db->select();
			$select->from("beer_events_users", array())
					->join("users", "beer_events_users.user_id=users.user_id", array("user_id" => "group_concat(users.user_id)"))
					->where("event_id=?", $event_id);
			$this->view->registered_users = array();
			$r_users = $db->fetchRow($select);
			if (isset($r_users["user_id"])) {
				$select = $db->select();
				$select->from("users")
						->where("user_id in (" . $r_users["user_id"] . ")");
				$this->view->registered_users = $db->fetchAll($select);
			}
		}
		if (isset($this->user->user_id)) {

			$select = $db->select();
			$select->from("beer_events_users")
					->where("event_id = ?", $event_id)
					->where("user_id = ?", $this->user->user_id);

			$t = $db->fetchAll($select);
			if (count($t) > 0) {
				$this->view->registration_status = 2;
			} else {
				$this->view->registration_status = 1;
			}

			$this->view->user_id = $this->user->user_id;
			//visi aludariu receptai 
			$select = $db->select();
			$select->from("beer_recipes")
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
					->joinLeft("beer_competition_entries", "beer_recipes.recipe_id=beer_competition_entries.recipe_id AND beer_competition_entries.event_id='" . $event_id . "'", "event_id")
					->where("brewer_id = ?", $this->user->user_id)
					->order('beer_recipes.recipe_name');
			$beer_recipes = $db->fetchAll($select);
			$this->view->beer_recipes = $beer_recipes;
		} else {
			$this->view->registration_status = 0;
		}
	}

	function registerAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->_helper->layout->setLayout('empty');
		$u = $storage->read();
		if (isset($u->user_name)) {
			if (isset($_POST)) {
				$db->delete("beer_events_users", "event_id = " . $_POST['id'] . ' and user_id = ' . $u->user_id);
				switch ($_POST['action']) {
					case "in":
						$db->insert("beer_events_users", array("event_id" => $_POST['id'], "user_id" => $u->user_id));
						break;
				}
				$select = $db->select();
				$select->from("beer_events_users", array())
						->join("users", "beer_events_users.user_id=users.user_id", array("user_id" => "group_concat(users.user_id)"))
						->where("event_id=?", $_POST['id']);
				$this->view->registered_users = array();
				$r_users = $db->fetchRow($select);
				if (isset($r_users["user_id"])) {
					$select = $db->select();
					$select->from("users")
							->where("user_id in (" . $r_users["user_id"] . ")");
					$this->view->registered_users = $db->fetchAll($select);
				}
			}
		} else {

			$this->_helper->viewRenderer->setNoRender(true);
			print "Neregistruotas nautotojas";
		}
	}

	function myEntriesAction() {

		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$storage = new Zend_Auth_Storage_Session();

		$this->view->event_id = $this->_getParam("event_id");
		$this->view->user_id = $this->user->user_id;

		//Uzregistruoti receptai
		$select = $db->select();
		$select->from("beer_competition_entries")
				->where("event_id = ?", $this->view->event_id)
				->where("event_user_id = ?", $this->user->user_id);
		$beer_competition_entries = $db->fetchAll($select);
		$this->view->beer_competition_entries = $beer_competition_entries;

		$this->view->beer_competition_entries_recipes = array();
		foreach ($beer_competition_entries as $entry) {
			$this->view->beer_competition_entries_recipes[] = $entry['recipe_id'];
		}

		$beer_recipes = array();
		if (!empty($this->view->beer_competition_entries_recipes)) {
			$select = $db->select();
			$select->from("beer_recipes")
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
					->where("brewer_id = ?", $this->user->user_id)
					->where("recipe_id IN(?)", $this->view->beer_competition_entries_recipes)
					->order('beer_recipes.recipe_name');
			$beer_recipes = $db->fetchAll($select);
		}

		$this->view->beer_recipes = $beer_recipes;
	}

	function myAllRecipesAction() {
		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$storage = new Zend_Auth_Storage_Session();

		$this->view->event_id = $this->_getParam("event_id");
		$this->view->user_id = $this->user->user_id;

		$select = $db->select();
		$select->from("beer_recipes")
				->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id")
				->where("brewer_id = ?", $this->user->user_id)
				->order('beer_recipes.recipe_name');
		$beer_recipes = $db->fetchAll($select);
		$this->view->beer_recipes = $beer_recipes;
	}

	function entryRegistrationAction() {
		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		if (isset($u->user_name)) {
			if (isset($_POST)) {
				switch ($_POST['action']) {

					case "add":



						$db->insert("beer_competition_entries", array("event_id" => $_POST['event_id'], "event_user_id" => $_POST['event_user_id'], "recipe_id" => $_POST['recipe_id'], "style_id" => $_POST['style_id'], 'created_at' => date('Y:m:d H:i:s')));
						break;

					case "remove":
						$db->delete("beer_competition_entries", 'event_id = ' . (int) $_POST['event_id'] . ' AND event_user_id = ' . (int) $_POST['event_user_id'] . ' AND recipe_id = ' . (int) $_POST['recipe_id']);
						break;
				}
			}
		} else {
			print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas nautotojas", "type" => "authentication"))));
		}
	}

}