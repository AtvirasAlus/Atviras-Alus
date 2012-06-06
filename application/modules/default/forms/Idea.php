<?

class Form_Idea extends Zend_Form {

	public function init() {
		$db = Zend_Registry::get('db');

		$this->setAction('/idejos/nauja');
		$this->setMethod('post');
		
		$this->addElement('text', 'title');
		$this->getElement('title')
				->setLabel("Antraštė <span>*</span>");
		
		$this->addElement('textarea', 'description');
		$this->getElement('description')
				->setLabel('Santrauka <span>*</span>');

		$this->addElement('textarea', 'full_text');
		$this->getElement('full_text')
				->setLabel('Detalus aprašymas');
		
		$element = new Zend_Form_Element_File('files1');
		$element->setLabel('Priedas #1');
		$element->addValidator('Extension', false, 'jpg,png,gif');
		$this->addElement($element, 'files1');
		
		$element = new Zend_Form_Element_File('files2');
		$element->setLabel('Priedas #2');
		$element->addValidator('Extension', false, 'jpg,png,gif');
		$this->addElement($element, 'files2');

		$element = new Zend_Form_Element_File('files3');
		$element->setLabel('Priedas #3');
		$element->addValidator('Extension', false, 'jpg,png,gif');
		$this->addElement($element, 'files3');

		$this->addElement('submit', 'attributes_action', array('type' => 'submit', 'class' => 'ui-button'));
		$this->getElement('attributes_action')
				->setLabel('Saugoti')
				->setIgnore(true);
	}

	public function loadDefaultDecorators() {
		foreach ($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => 'idea/forms/create.phtml'))));
	}

}

?>
