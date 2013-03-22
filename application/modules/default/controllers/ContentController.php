<?php

class ContentController extends Zend_Controller_Action {

	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs = $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
		}
	}

	public function policyAction() {
		
	}

	public function termsAction() {
		define("DOKU_INC", "wiki/");
		define('NOSESSION', 1);
		require_once(DOKU_INC . 'inc/init.php');
		require_once(DOKU_INC . 'inc/common.php');
		require_once(DOKU_INC . 'inc/parserutils.php');
		$source = file_get_contents("wiki/data/pages/terminologija.txt");
		$info = array();
		$data = p_render('xhtml', p_get_instructions($source), $info);
		$this->view->data = $data;
	}

	public function readAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$cat = $this->_getParam('cat');
		if ($cat != 0) {
			$article = explode("-", $this->_getParam('article'));
			switch ($article[0]) {
				case "18":
					$this->_redirect("/wiki/straipsniai:mokomasis_klipas_alaus_gamyba_is_salyklo_salinimas_misos_tekinimas");
					break;
				case "17":
					$this->_redirect("/wiki/straipsniai:mokomasis_klipas_pradedantiems_aludariams");
					break;
				case "16":
					$this->_redirect("/wiki/straipsniai:salutiniai_skoniai_aluje");
					break;
				case "15":
					$this->_redirect("/wiki/straipsniai:sausas_apyniavimas");
					break;
				case "13":
					$this->_redirect("/wiki/straipsniai:salinimo_moksliniai_pagrindai_ii_dalis_krakmolo_skaidymas");
					break;
				case "12":
					$this->_redirect("/wiki/straipsniai:salinimo_moksliniai_pagrindai_i_dalis");
					break;
				case "8":
					$this->_redirect("/wiki/straipsniai:paprastai_apie_temperaturos_itaka_alaus_gamybai");
					break;
				case "9":
					$this->_redirect("/wiki/straipsniai:apie_vandeni");
					break;
				case "11":
					$this->_redirect("/wiki/straipsniai:ar_zinote_kuo_skiriasi_kristalinis_ir_karamelinis_salyklai");
					break;
				case "5":
					$this->_redirect("/wiki/straipsniai:vieno_alaus_istorija_arba_kaip_pasigaminti_salyklini_alu_bute");
					break;
				case "6":
					$this->_redirect("/wiki/straipsniai:kaip_isrukyti_salykla");
					break;
				case "4":
					$this->_redirect("/wiki/straipsniai:salyklo_gamyba");
					break;
				case "3":
					$this->_redirect("/wiki/straipsniai:alaus_darymas_is_misos_ekstrakto");
					break;
				case "1":
					$this->_redirect("/wiki/straipsniai:misos_tekinimo_filtras_naudojant_cpvc_vamzdeliu_sistema");
					break;
			}
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->where("article_id =?", $article[0]);

			$this->_helper->viewRenderer("read" . $cat);
			$this->view->article = $db->fetchRow($select);
		}
	}

	public function aboutAction() {
		
	}

	public function helpAction() {
		
	}

	public function faqAction() {
		
	}

	public function paramaAction() {
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("bonuses", array("SUM(bo_amount) as kiekis"))
				->where("bo_amount > 0");
		$result = $db->fetchRow($select);
		$total = $result['kiekis'];
		$this->view->total = number_format($total, 2);
		$select = $db->select()
				->from("bonuses", array("SUM(bo_amount) as kiekis"));
		$result = $db->fetchRow($select);
		$kiekis = $result['kiekis'];
		$this->view->kiekis = number_format($kiekis, 2);
		$select = $db->select()
				->from("bonuses")
				->order("bo_created DESC");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$result[$key]['bo_leftover'] = $kiekis;
			if (strpos($val['bo_description'], "Serverio nuoma") !== false){
				$result[$key]['bo_description'] = "Serverio nuomos paslaugos pratęsimas";
			} else {
				if (strpos($val['bo_description'], " - ") !== false){
					$temp = explode(" - ", $val['bo_description']);
					$result[$key]['bo_description'] = $temp[1];
				} else {
					$temp = $val['bo_description'];
					$temp = str_replace(array("SMS ", " (styxas)"), array("",""), $temp);
					//$temp = substr($temp, 0, 4);
					$temp = substr($temp, 0, 4)."6****".substr($temp, -3);
					$result[$key]['bo_description'] = $temp;
				}
			}
			$kiekis = $kiekis + (-1 * $val['bo_amount']);
			$kiekis = number_format($kiekis, 2);
		}
		$adapter = new Zend_Paginator_Adapter_Array($result);
		$page = $this->_getParam('page');
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($page);
		$this->view->content->setItemCountPerPage(50);
	}

	public function articleAction() {
		
	}

	public function listAction() {
		$cat_page = explode("-", $this->_getParam('cat_page'));
		$this->view->articles = array();
		if ($cat_page[0]) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->joinLeft("beer_articles_comments", "beer_articles_comments.comment_article=beer_articles.article_id", array("COUNT(comment_id) AS total"))
					->group("beer_articles.article_id")
					->where("article_cat =?", $cat_page[0])
					->where("article_publish =?", '1')
					->order("article_modified DESC");
			$page = isset($cat_page[1]) ? $cat_page[0] : 1;
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($page);
			$this->view->content->setItemCountPerPage(100);
		}
	}

}