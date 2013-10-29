<?php

class Zend_View_Helper_UsePlato extends Zend_View_Helper_Abstract {
	function usePlato($sg, $show_symbol = false) {
		$use = $this->view->use_plato;
		if ($use === true){
			$plato = -668.962 + 1262.45 * $sg - 776.43 * $sg * $sg + 182.94 * $sg * $sg * $sg;
			$plato = round($plato, 1);
			if ($show_symbol === true){
				return number_format($plato, 1, ".", "")." °P";
			} else {
				return number_format($plato, 1, ".", "");
			}
		} else {
			return number_format($sg, 3, ".", "");
		}
	}

}