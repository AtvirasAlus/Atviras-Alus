<?
class Entities_FormErrors extends Zend_Db_Table {
	
	public function __construct() {
	
		 parent::__construct();
		 $this->ERROR_CODES=array();
		 $this->ERROR_CODES["emailAddressInvalidFormat"] = "Netinkamas e. pašto adreso formatas";
		 $this->ERROR_CODES["emailAddressInvalidHostname"] = "Nurodytas e. pašto adresas neegzistuoja";
		 $this->ERROR_CODES["badCaptcha"] = "Simboliai nesutampa";
		 $this->ERROR_CODES["isEmpty"] = "Neužpildytas laukas";
		 $this->ERROR_CODES["notAlpha"] = "Leidžiami simboliai raidės"; 
	 	$this->ERROR_CODES["REGISTRATION_NOT_STARTED"] = "Registracija neprasidėjo"; 
		$this->ERROR_CODES["REGISTRATION_ENDED"] = "Registracija pasibaigė"; 
		$this->ERROR_CODES["TO_MANY_USERS"] = "Nėra laisvų vietų"; 
		$this->ERROR_CODES["ALLREADY_REGISTERED"] = ""; 
		
}
	
	public function getError($code) {
		if (isset($this->ERROR_CODES[$code])) {
			return $this->ERROR_CODES[$code];
		}else{
			return $code;
		}
	}
	
}
