<?

class ErrorController extends Zend_Controller_Action {
	
	public function init(){
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
				$this->_helper->layout()->setLayout('layoutnew');
			}
		}
		
	}

	public function errorAction() {
		$isAjaxRequest = $this->getRequest()->isXmlHttpRequest();
		$errors = $this->_getParam('error_handler');
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()
						->setRawHeader('HTTP/1.1 404 Not Found');
				if ($isAjaxRequest) {
					$errorMessage = 'ERROR,404';
				} else {
					$this->view->title = 'Klaida 404 - Puslapis nerastas';
					$this->view->message = 'Jūsų ieškomas puslapis sistemoje nerastas.';
				}
				break;
			default: $this->getResponse()
						->setRawHeader('HTTP/1.1 500 Internal Server Error');
				if ($isAjaxRequest) {
					$errorMessage = 'ERROR,500';
				} else {
					$this->view->title = 'Klaida';
					$this->view->message =
							'Sistema bandydama apdoroti jūsų ieškomą puslapį gražino klaidą.';
				}
				break;
		}


		if ($isAjaxRequest) {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			echo $errorMessage;
		} else {
			$this->view->exception = $errors->exception;
			$this->view->request = $errors->request;
		}
	}

}