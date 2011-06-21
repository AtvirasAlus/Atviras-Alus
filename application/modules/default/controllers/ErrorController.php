<?
class ErrorController extends Zend_Controller_Action
{
  public function errorAction(){
  	  $isAjaxRequest = $this->getRequest()->isXmlHttpRequest();
  	  $errors = $this->_getParam('error_handler');
  	  switch ($errors->type){
		  case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
		  case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
		   $this->getResponse()
                     ->setRawHeader('HTTP/1.1 404 Not Found');
                     	 if ($isAjaxRequest){
		   	   $errorMessage = 'ERROR,404';
			}else {
			  $this->view->title = '~~~~~~~~~~~~~~~ 404 ~~~~~~~~~~~~~~~~~~~';
			  $this->view->message = 
			  '<div  style="width:400"><h2 style="color:red">Čia alaus nėra!</h2></div>';
			}
		    break;
		  default:  $this->getResponse()
                     ->setRawHeader('HTTP/1.1 500 Internal Server Error');
		   if ($isAjaxRequest){
		   	   $errorMessage = 'ERROR,500';
			}else {
			  $this->view->title = 'Oj, oj įvyko klaida...';
			  $this->view->message = 
			    'Puslapis kurį bandote žiūrėti  dar nesukurtas arba sukurtas su klaidom!';
			}
		   break;
	  }
  
		
		  if ($isAjaxRequest){
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender(true);
		  echo $errorMessage;
		}else{
		  $this->view->exception = $errors->exception;
		  $this->view->request   = $errors->request;
		}
  }
}
?>
