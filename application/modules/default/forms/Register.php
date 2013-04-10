<?

class Form_Register extends Zend_Form {

	var $__data = array();

	public function __construct($data = array()) {
		$this->__data = $data;
		parent::__construct();
	}

	public function init() {

		$this->setName('login');
		$this->setAction('/auth/register');
		$this->setMethod('post');
		$this->setDescription("signup form | registracija");
		$this->addElement('text', 'user_name', array("required" => true));
		$this->getElement('user_name')
				->addFilter(new Zend_Filter_StripTags())
				->setLabel('Naudotojo vardas:')
				->setRequired(true);
		$this->addElement('text', 'user_email', array("required" => true));
		$this->getElement('user_email')
				->addValidator(new Zend_Validate_EmailAddress())
				->addFilter(new Zend_Filter_StripTags())
				->setLabel('El. paštas:')
				->setRequired(true);
		$captcha = new Zend_Form_Element_Captcha(
						'captcha',
						array('label' => 'Saugos kodas:',
							'captcha' => array(
								'captcha' => 'Image',
								'wordLen' => 6,
								'timeout' => 3000,
								'dotNoiseLevel' => 2,
								'font' => './public/fonts/verdanab.ttf',
								'imgDir' => './public/captcha/',
								'imgUrl' => '/public/captcha/'
						)));
		$this->addElement($captcha);

		$this->addElement('submit', 'register_action', array("label" => 'Registruotis', "class" => "ui-button"));
		$this->getElement('register_action')
				->setLabel('Registruotis')
				->setIgnore(true);

		$_elements = $this->getValues();
		foreach ($_elements as $key => $value) {
			if (isset($this->__data[$key])) {
				$this->getElement($key)->setValue($this->__data[$key]);
			}
		}
	}

	public function loadDefaultDecorators() {
		foreach ($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => 'auth/forms/register.phtml'))));
	}

}

?>
