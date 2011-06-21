<?
class Form_Profile extends Zend_Form {
	public function init() {
		
		$this->setAction('/brewer/profile');
		$this->setMethod('post');
		
		$this->addElement('Password', 'user_password_old');
		$this->getElement('user_password_old')
			->setLabel('Senas slaptažodis:')
			->addValidator(new Zend_Validate_Alnum())
			->setRequired(true);
		$this->addElement('Password', 'user_password');
		$this->getElement('user_password')
			->addValidator(new Zend_Validate_Alnum())
			->setLabel('Naujas slaptažodis:')
			->setRequired(true);
		$this->addElement('Password', 'user_password_repeat');
		$this->getElement('user_password_repeat')
			->addValidator(new Zend_Validate_Alnum())
			->setLabel('Naujas slaptažodis (pakartoti):')
			->setRequired(true);
		$this->addElement('Button', 'profile_action',array('type'=>'submit'));
		$this->getElement('profile_action')
			->setLabel('Keisti')
			->setIgnore(true);
		
	}
	public function loadDefaultDecorators()
{		
		foreach($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		$this->setDecorators ( array (array ('ViewScript', array ('viewScript' => 'brewer/forms/profile.phtml' ) ) ) );
	}
}
?>
