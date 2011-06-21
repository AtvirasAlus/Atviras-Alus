<?
class Zend_View_Helper_ColorHex extends Zend_View_Helper_Abstract{
  public $view; 
public function colorHex($srm,$ebc=true) {
	if ($ebc) {
	$srm=$srm*0.375-0.46;
	}
	$srm_rgb =array();
	$srm_rgb[] = '#FFFFFF';
	$srm_rgb[] = '#FFE699';
	$srm_rgb[] = '#FFD878';
	$srm_rgb[] = '#FFCA5A';
	$srm_rgb[] = '#FFBF42';
	$srm_rgb[] = '#FBB123';
	$srm_rgb[] = '#F8A600';
	$srm_rgb[] = '#F39C00';
	$srm_rgb[] = '#EA8F00';
	$srm_rgb[] = '#E58500';
	$srm_rgb[] = '#DE7C00';
	$srm_rgb[] = '#D77200';
	$srm_rgb[] = '#CF6900';
	$srm_rgb[] = '#CB6200';
	$srm_rgb[] = '#C35900';
	$srm_rgb[] = '#BB5100';
	$srm_rgb[] = '#B54C00';
	$srm_rgb[] = '#B04500';
	$srm_rgb[] = '#A63E00';
	$srm_rgb[] = '#A13700';
	$srm_rgb[] = '#9B3200';
	$srm_rgb[] = '#952D00';
	$srm_rgb[] = '#8E2900';
	$srm_rgb[] = '#882300';
	$srm_rgb[] = '#821E00';
	$srm_rgb[] = '#7B1A00';
	$srm_rgb[] = '#771900';
	$srm_rgb[] = '#701400';
	$srm_rgb[] = '#6A0E00';
	$srm_rgb[] = '#660D00';
	$srm_rgb[] = '#5E0B00';
	$srm_rgb[] = '#5A0A02';
	$srm_rgb[] = '#600903';
	$srm_rgb[] = '#520907';
	$srm_rgb[] = '#4C0505';
	$srm_rgb[] = '#470606';
	$srm_rgb[] = '#440607';
	$srm_rgb[] = '#3F0708';
	$srm_rgb[] = '#3B0607';
	$srm_rgb[] = '#3A070B';
	$srm_rgb[] = '#36080A';
	if (isset($srm_rgb[round($srm,0)])) {
		return $srm_rgb[round($srm,0)];
	} else {
		return "#000000";
	}
	}
	public function setView(Zend_View_Interface $view) 
    { 
        $this->view = $view; 
    } 
	
}?>
