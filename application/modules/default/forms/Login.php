<?
class Form_Login extends Zend_Form {
	var $__data=array();
	public function __construct($data=array())
	{
		$this->__data=$data;
	    parent::__construct(); 
	 }
	
	public function init() {
		
	$this->setName('login');
	$this->setAction('/auth/login');
	$this->setMethod('post');
	$this->setDescription("signup form | registracija");
	$this->setAttrib('sitename', 'vdaa');
	$this->addElement('Text', 'user_email');
	$this->getElement('user_email')
		->addValidator(new Zend_Validate_EmailAddress())
		->addFilter(new Zend_Filter_StripTags())
		->setLabel('El. paštas:')
		->setRequired(true);
	$this->addElement('Password', 'user_password');
	$this->getElement('user_password')
		->addValidator(new Zend_Validate_Alnum())
		->setLabel('Slaptažodis:')
		->setRequired(true);
	$this->addElement('Button', 'login_action',array("label"=>'Prisijungt'));
	$this->getElement('login_action')
		->setLabel('Prisijungti')
		->setIgnore(true);
 
    	$_elements=$this->getValues();
		foreach($_elements as $key=>$value) {
			if (isset($this->__data[$key])) {
				$this->getElement($key)->setValue($this->__data[$key]);
			}
		}
	}
	public function loadDefaultDecorators()
{		
		foreach($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		//$this->setDecorators ( array (array ('ViewScript', array ('viewScript' => 'auth/forms/login.phtml' ) ) ) );
	}
}
?>
