<?
class OrderController extends Zend_Controller_Action {
	private $shops;
	public function init() {
		
		$this->shops['savasalus']['email'] = "info@savasalus.lt";
		
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		if (!isset($this->user->user_id)) {
			$this->_redirect("/");
		}
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
	}

	function indexAction() {
		
	}
	
	function recipeAction(){
		$db = Zend_Registry::get("db");
		$shop = $this->getRequest()->getParam("shop");
		$recipe = $this->getRequest()->getParam("recipe");
		$select = $db->select()
				->from("beer_recipes")
				->where("recipe_id = ?", $recipe);
		if (!$result = $db->fetchRow($select)) exit;
		$this->view->recipe = $result;
		
		$select = $db->select()
				->from("beer_recipes_malt")
				->where("recipe_id = ?", $recipe)
				->order("malt_weight DESC");
		$result = $db->fetchAll($select);
		$malts = array();
		foreach($result as $key=>$val){
			if (isset($malts[$val['malt_name']." (".$val['malt_ebc']." EBC)"])){
				$malts[$val['malt_name']." (".$val['malt_ebc']." EBC)"] += $val['malt_weight']*1000;
			} else {
				$malts[$val['malt_name']." (".$val['malt_ebc']." EBC)"] = $val['malt_weight']*1000;
			}
		}
		$this->view->malts = $malts;
		
		$select = $db->select()
				->from("beer_recipes_hops")
				->where("recipe_id = ?", $recipe)
				->order("hop_weight DESC");
		$result = $db->fetchAll($select);
		$malts = array();
		foreach($result as $key=>$val){
			if (isset($hops[$val['hop_name']." (".$val['hop_alpha']." % AA)"])){
				$hops[$val['hop_name']." (".$val['hop_alpha']." % AA)"] += $val['hop_weight'];
			} else {
				$hops[$val['hop_name']." (".$val['hop_alpha']." % AA)"] = $val['hop_weight'];
			}
		}
		$this->view->hops = $hops;
		
		$select = $db->select()
				->from("beer_recipes_yeast")
				->where("recipe_id = ?", $recipe)
				->order("yeast_weight DESC");
		$result = $db->fetchAll($select);
		$yeasts = array();
		foreach($result as $key=>$val){
			if (isset($yeasts[$val['yeast_name']])){
				$yeasts[$val['yeast_name']] += $val['yeast_weight'];
			} else {
				$yeasts[$val['yeast_name']] = $val['yeast_weight'];
			}
		}
		$this->view->yeasts = $yeasts;
		
		if (isset($_POST['sbmt']) && !empty($_POST['sbmt'])){
			
			$msg  = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
			$msg  = "<h3>atvirasalus.lt sistemoje užregistruotas naujas užsakymas</h3>";
			$msg .= "<table cellspacing=0 cellpadding=4 border=1>";
			$msg .= "<tr><td>Užsakymo data: </td><td style='color: #cc0000;'>".date("Y-m-d H:i:s")."</td></tr>";
			$msg .= "<tr><td>Naudotojas: </td><td>".$this->user->user_name."</td></tr>";
			$msg .= "<tr><td>El. pašto adresas: </td><td><a href='mailto:".$this->user->user_email."'>".$this->user->user_email."</a></td></tr>";
			$msg .= "<tr><td>Rašyti privačią žinutę: </td><td><a href='http://www.atvirasalus.lt/mail/compose?token=".urlencode(base64_encode($this->user->user_name))."' target='_blank'>http://www.atvirasalus.lt/mail/compose?token=".urlencode(base64_encode($this->user->user_name))."</a></td></tr>";
			$msg .= "<tr><td>Receptas: </td><td>".$this->view->recipe['recipe_name']."</td></tr>";
			$msg .= "<tr><td>Recepto nuoroda: </td><td><a href='http://www.atvirasalus.lt/alus/receptas/".$this->view->recipe['recipe_id']."' target='_blank'>http://www.atvirasalus.lt/alus/receptas/".$this->view->recipe['recipe_id']."</a></td></tr>";
			$msg .= "<tr><td colspan='2'><hr /></td></tr>";
			$msg .= "<tr><td>Prekės: </td><td>";
				$msg .= "<table cellspacing=0 cellpadding=4 border=0 width='100%'>";
				foreach ($this->view->malts as $name=>$weight){
					$msg .= "<tr><td>".$name."</td><td style='text-align: right;'>".number_format($weight, 0, ".", " ")." g</td></tr>";
				}
				foreach ($this->view->hops as $name=>$weight){
					$msg .= "<tr><td>".$name."</td><td style='text-align: right;'>".number_format($weight, 0, ".", " ")." g</td></tr>";
				}
				foreach ($this->view->yeasts as $name=>$weight){
					$msg .= "<tr><td>".$name."</td><td style='text-align: right;'>".number_format($weight, 0, ".", " ")." g</td></tr>";
				}
				$msg .= "</table>";
			$msg .= "</td></tr>";
			$msg .= "<tr><td colspan='2'><hr /></td></tr>";
			$msg .= "<tr><td>Pastabos: </td>";
			$msg .= "<td style='color: #cc0000'>".trim($_POST['comments'])."</td></tr>";
			$_POST['discount'] = trim($_POST['discount']);
			if (!empty($_POST['discount'])){
				$msg .= "<tr><td>Nuolaidos kodas: </td><td style='color: #cc0000'>".$_POST['discount']."</td></tr>";
			} else {
				$msg .= "<tr><td>Nuolaidos kodas: </td><td>(nenurodytas)</td></tr>";
			}
			$msg .= "<tr><td>Pristatymo būdas: </td>";
			switch($_POST['delivery']){
				case "1":
					$msg .= '<td>Atsiėmimas "Savas alus" biure'."</td></tr>";
					break;
				case "2":
					$msg .= "<td>Atsiėmimas LPEXPRESS.lt terminale</td></tr>";
					$msg .= "<tr><td>LPEXPRESS.lt terminalas: </td>";
					$msg .= "<td>".trim($_POST['terminal'])."</td></tr>";
					break;
				case "3":
					$msg .= "<td>DPD Lietuva pristatymas pasirinktu adresu</td></tr>";
					$msg .= "<tr><td>Adresas: </td>";
					$msg .= "<td>".trim($_POST['address'])."</td></tr>";
					break;
				default:
					$msg .= "<td>(nenurodytas)</td></tr>";
					break;
			}
			$msg .= "<tr><td>Mokėjimo būdas</td>";
			switch($_POST['payment']){
				case "1":
					$msg .= '<td>Pavedimu į banko sąskaitą'."</td></tr>";
					break;
				case "2":
					$msg .= "<td>Grynaisiais mūsų biure atsiimant prekes</td></tr>";
					break;
				default:
					$msg .= "<td>(nenurodytas)</td></tr>";
					break;
			}
			$msg .= "</table>";
			$msg .= "</body></html>";
			$from_user = "=?UTF-8?B?".base64_encode($this->user->user_name)."?=";
			$subject = "=?UTF-8?B?".base64_encode("Naujas užsakymas atvirasalus.lt sistemoje")."?=";
			$headers = "From: ".$from_user." <".$this->user->user_email.">\r\n".
						"MIME-Version: 1.0" . "\r\n".
						"Content-type: text/html; charset=UTF-8" . "\r\n";
			Entities_Mail::mail(array($this->shops[$shop]['email']), $subject, $msg, $this->user->user_email, $from_user, true);
			//mail($this->shops[$shop]['email'], $subject, $msg, $headers);
			$this->_helper->viewRenderer("sent");
		}
	}
}