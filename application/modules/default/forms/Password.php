<?
class Form_Password extends Zend_Form {

	public function init() {
		$this->setAction('/auth/remember');
		$this->setMethod('post');
		$this->setAttrib('sitename', 'vdaa');
		$this->addElement('Text', 'user_email');
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
		$this->addElement('submit', 'password_action', array("type" => "submit", "class" => "ui-button"));
		$this->getElement('password_action')
				->setLabel('Siųsti slaptažodį')
				->setIgnore(true);
	}

	public function loadDefaultDecorators() {
		foreach ($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => 'auth/forms/password.phtml'))));
	}

}
?>