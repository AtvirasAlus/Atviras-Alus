<?
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
private  $fc;
private $config;
private $post_init_actions;
function _initAutoLoad() {
	$this->post_init_actions=array();
	$this->initConfig();
	$this->fc = Zend_Controller_Front::getInstance(); 
	$this->fc->setControllerDirectory(array('default' => APPLICATION_PATH . '/modules/default/controllers'));
	$this->initRoutes();
	$this->initDatabase(); 
	for ($i=0;$i< count($this->post_init_actions);$i++) {
		switch($this->post_init_actions[$i]["action"]) {
		case "doautologin":
			if (strlen($this->post_init_actions[$i]["params"]['user_email'])>0) {
			 Entities_AUTH::dologin($this->post_init_actions[$i]["params"]['user_email'],$this->post_init_actions[$i]["params"]['user_password'],$this->post_init_actions[$i]["params"]['remember']);
			}
		break;
		}
	}
    }
    function initConfig() {
    	     $this->config = new Zend_Config($this->getOptions(APPLICATION_ENV)); 
    	     if (isset($_COOKIE["user_email"])) {
    	     	$isin_email = $_COOKIE["user_email"];
    	     	$isin_password=$_COOKIE["user_password"];
    	     	$isin_remember=false;
    	     	if (isset($_COOKIE["remember"])){
    	     		$isin_remember=($_COOKIE["remember"]=='1') ? true : false;
    	     	}
    	     }
    	     $storage = new Zend_Auth_Storage_Session();
    	     if (isset($isin_email)) {
    	     	 $u=$storage->read();
    	    
    	     if (!isset($u->user_id)) {
    	     	    
    	     	     	     $this->post_init_actions[0]=array("action"=>"doautologin","params"=>array("user_email"=>$isin_email,"user_password"=>$isin_password,"remember"=>$isin_remember));
    	     	    } 
    	     }
    	    $defaultNamespace->isin=true;
    	    Zend_Registry::set('config',$this->config);
    }
    
    function initDatabase() {
    	 $db = Zend_Db::factory($this->config->resources->db->adapter, $this->config->resources->db->params); 
    	  if ($db->getConnection()) {
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		Zend_Registry::set('db', $db);
	  }
    }
    function  initRoutes() {
		$router = $this->fc->getRouter();
		$router->addRoute("beta_enable",new Zend_Controller_Router_Route("/beta_on",array("module"=>"default","controller" => "index","action" => "enablebeta")));
		$router->addRoute("beta_disable",new Zend_Controller_Router_Route("/beta_off",array("module"=>"default","controller" => "index","action" => "disablebeta")));
		$router->addRoute("activate",new Zend_Controller_Router_Route("auth/activate/:emailhash",array("module"=>"default","controller" => "auth","action" => "activate","emailhash"=>0)));
		
		$router->addRoute("recipes",new Zend_Controller_Router_Route("brewer/recipes/:page",array("module"=>"default","controller" => "brewer","action" => "recipes","page"=>0)));
		$router->addRoute("recipes_favorites",new Zend_Controller_Router_Route("recipes/favorites/:page",array("module"=>"default","controller" => "recipes","action" => "favorites","page"=>0)));
		$router->addRoute("recipes_view_0",new Zend_Controller_Router_Route("recipes/view/:recipe",array("module"=>"default","controller" => "recipes","action" => "view","recipe"=>0)));
		$router->addRoute("recipes_view",new Zend_Controller_Router_Route("alus/receptas/:recipe",array("module"=>"default","controller" => "recipes","action" => "view","recipe"=>0)));
		$router->addRoute("recipes_del_image",new Zend_Controller_Router_Route("/alus/trinti_nuotrauka/:image_id",array("module"=>"default","controller" => "recipes","action" => "delete_image","image_id"=>0)));
	
		$router->addRoute("rate",new Zend_Controller_Router_Route("vertinimas/:page",array("module"=>"default","controller" => "rate","action" => "index","page"=>0)));
		$router->addRoute("rate_brewery",new Zend_Controller_Router_Route("vertinimas/bravoras/:bid/:page",array("module"=>"default","controller" => "rate","action" => "brewery","bid"=>0, "page"=>0)));
		$router->addRoute("rate_style",new Zend_Controller_Router_Route("vertinimas/stilius/:sid/:page",array("module"=>"default","controller" => "rate","action" => "style","sid"=>0, "page"=>0)));
		$router->addRoute("rate_beer",new Zend_Controller_Router_Route("vertinimas/alus/:bid",array("module"=>"default","controller" => "rate","action" => "beer","bid"=>0)));

		$router->addRoute("calculus_recipe",new Zend_Controller_Router_Route("index/calculus/:recipe",array("module"=>"default","controller" => "index","action" => "calculus","recipe"=>0)));
		//$router->addRoute("calculus",new Zend_Controller_Router_Route("/calculus/:recipe",array("module"=>"default","controller" => "index","action" => "calculus","recipe"=>0)));
		$router->addRoute("group",new Zend_Controller_Router_Route("/groups/view/:group_id",array("module"=>"default","controller" => "groups","action" => "view","group_id"=>0)));
		$router->addRoute("index_filter",new Zend_Controller_Router_Route("/filter/:type",array("module"=>"default","controller" => "index","action" => "index", "type"=>'all')));
		$router->addRoute("gallery",new Zend_Controller_Router_Route("/gallery/:page",array("module"=>"default","controller" => "recipes","action" => "gallery", "page"=>0)));
		$router->addRoute("food_list",new Zend_Controller_Router_Route("/maistas/:category",array("module"=>"default","controller" => "food","action" => "list", "category"=>0)));
		$router->addRoute("food_item",new Zend_Controller_Router_Route("/patiekalas/:item",array("module"=>"default","controller" => "food","action" => "item", "item"=>0)));
		$router->addRoute("food_my",new Zend_Controller_Router_Route("/maistas/mano",array("module"=>"default","controller" => "food","action" => "my")));
		$router->addRoute("food_new",new Zend_Controller_Router_Route("/maistas/naujas",array("module"=>"default","controller" => "food","action" => "new")));
		$router->addRoute("food_edit",new Zend_Controller_Router_Route("/maistas/redaguoti/:item",array("module"=>"default","controller" => "food","action" => "edit", "item"=>0)));
		$router->addRoute("food_delete",new Zend_Controller_Router_Route("/maistas/trinti/:item",array("module"=>"default","controller" => "food","action" => "delete", "item"=>0)));
		$router->addRoute("food1",new Zend_Controller_Router_Route("/maistas/receptai-su-alumi",array("module"=>"default","controller" => "food","action" => "list", "category"=>3)));
		$router->addRoute("food2",new Zend_Controller_Router_Route("/maistas/pagrindiniai-patiekalai",array("module"=>"default","controller" => "food","action" => "list", "category"=>2)));
		$router->addRoute("food3",new Zend_Controller_Router_Route("/maistas/uzkandziai-prie-alaus",array("module"=>"default","controller" => "food","action" => "list", "category"=>1)));
		$router->addRoute("idejos",new Zend_Controller_Router_Route("/idejos/:page",array("module"=>"default","controller" => "idea","action" => "list","page"=>0)));
		$router->addRoute("idejos_my",new Zend_Controller_Router_Route("/idejos/mano/:page",array("module"=>"default","controller" => "idea","action" => "list_my","page"=>0)));
		$router->addRoute("idejos_unvoted",new Zend_Controller_Router_Route("/idejos/balsavimas/:page",array("module"=>"default","controller" => "idea","action" => "list_unvoted","page"=>0)));
		$router->addRoute("idejos_new",new Zend_Controller_Router_Route("/idejos/naujausios/:page",array("module"=>"default","controller" => "idea","action" => "list_new","page"=>0)));
		$router->addRoute("idejos_top",new Zend_Controller_Router_Route("/idejos/populiariausios/:page",array("module"=>"default","controller" => "idea","action" => "list_top","page"=>0)));
		$router->addRoute("idejos_finished",new Zend_Controller_Router_Route("/idejos/igyvendintos/:page",array("module"=>"default","controller" => "idea","action" => "list_finished","page"=>0)));
		$router->addRoute("idejos_rejected",new Zend_Controller_Router_Route("/idejos/atmestos/:page",array("module"=>"default","controller" => "idea","action" => "list_rejected","page"=>0)));
		$router->addRoute("idejos_view",new Zend_Controller_Router_Route("/ideja/:idea",array("module"=>"default","controller" => "idea","action" => "view", "idea" => 0)));
		$router->addRoute("idejos_create",new Zend_Controller_Router_Route("/idejos/nauja",array("module"=>"default","controller" => "idea","action" => "create")));
		$router->addRoute("idejos_comments",new Zend_Controller_Router_Route("/idejos/komentarai",array("module"=>"default","controller" => "idea","action" => "comments")));
		$router->addRoute("brewer",new Zend_Controller_Router_Route("/brewers/:brewer",array("module"=>"default","controller" => "brewer","action" => "info","brewer"=>0)));
		$router->addRoute("brewer_recipes",new Zend_Controller_Router_Route("/brewer/recipes/:brewer/:page",array("module"=>"default","controller" => "brewer","action" => "recipes","brewer"=>0,"page"=>0)));
		$router->addRoute("brewer_sessions",new Zend_Controller_Router_Route("/brewer/sessions/:brewer",array("module"=>"default","controller" => "brewer","action" => "sessions","brewer"=>0)));
		$router->addRoute("brewer_list",new Zend_Controller_Router_Route("/brewer/list/:page",array("module"=>"default","controller" => "brewer","action" => "list","page"=>0)));
		$router->addRoute("style",new Zend_Controller_Router_Route("/style/:style/:page",array("module"=>"default","controller" => "recipes","action" => "styles","style"=>0,"page"=>0)));
		$router->addRoute("pagalba",new Zend_Controller_Router_Route("/pagalba",array("module"=>"default","controller" => "content","action" => "read","category"=>"help","page"=>0)));
		$router->addRoute("parama",new Zend_Controller_Router_Route("/parama",array("module"=>"default","controller" => "content","action" => "parama","category"=>"help","page"=>0)));
		$router->addRoute("recipes_brew_session",new Zend_Controller_Router_Route("/brew-session/recipe/:recipe",array("module"=>"default","controller" => "brew-session","action" => "recipe","recipe"=>0)));
		$router->addRoute("brewer_brew_session",new Zend_Controller_Router_Route("/brew-session/brewer/:brewer",array("module"=>"default","controller" => "brew-session","action" => "brewer","brewer"=>0)));
		$router->addRoute("edit_brew_session",new Zend_Controller_Router_Route("/brew-session/edit/:session",array("module"=>"default","controller" => "brew-session","action" => "edit","session"=>0)));
		$router->addRoute("new_brew_session",new Zend_Controller_Router_Route("/brew-session/new/:recipe",array("module"=>"default","controller" => "brew-session","action" => "new","recipe"=>0)));
		$router->addRoute("history_brew_session",new Zend_Controller_Router_Route("/brew-session/history/:page",array("module"=>"default","controller" => "brew-session","action" => "history","page"=>0)));
		$router->addRoute("detail_brew_session",new Zend_Controller_Router_Route("/brew-session/detail/:session",array("module"=>"default","controller" => "brew-session","action" => "detail","session"=>0)));
		$router->addRoute("articles_list",new Zend_Controller_Router_Route("/content/list/:cat_page",array("module"=>"default","controller" => "content","action" => "list","cat_page"=>'0-0')));
		$router->addRoute("articles_read",new Zend_Controller_Router_Route("/content/read/:cat/:article",array("module"=>"default","controller" => "content","action" => "read","cat"=>0,'article'=>0)));
		$router->addRoute("skaitykla",new Zend_Controller_Router_Route("/skaitykla",array("module"=>"default","controller" => "content","action" => "list","cat_page"=>1)));
		$router->addRoute("stilius",new Zend_Controller_Router_Route("/stilius/:style/:page",array("module"=>"default","controller" => "styles","action" => "styles","style"=>0,"page"=>0)));
		$router->addRoute("search",new Zend_Controller_Router_Route("/search/:params/:page",array("module"=>"default","controller" => "recipes","action" => "search","params"=>0,"page"=>0)));
		$router->addRoute("mail_in",new Zend_Controller_Router_Route("/mail/inbox/:page",array("module"=>"default","controller" => "mail","action" => "inbox","page"=>0)));
		$router->addRoute("mail_out",new Zend_Controller_Router_Route("/mail/outbox/:page",array("module"=>"default","controller" => "mail","action" => "outbox","page"=>0)));
		$router->addRoute("events",new Zend_Controller_Router_Route("/ivykiai/:page",array("module"=>"default","controller" => "events","action" => "index","page"=>0)));
		$router->addRoute("event",new Zend_Controller_Router_Route("/ivykis/:event",array("module"=>"default","controller" => "events","action" => "view","event"=>0)));
		$router->addRoute("sitemap",new Zend_Controller_Router_Route("/sitemap",array("module"=>"default","controller" => "index","action" => "sitemap")));
                $router->addRoute("brewer_twitter",new Zend_Controller_Router_Route("/tweet/all/:page",array("module"=>"default","controller" => "tweet","action" => "all","page"=>0)));
    }
}
?>
