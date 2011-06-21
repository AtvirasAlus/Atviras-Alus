<?
class Entities_BrewCalc { 
public static function sg_plato($sg) {
	return (-463.37) + (668.72 * $sg) - (205.35 * $sg * $sg);
}
public static function real_extract($og_plato,$fg_plato) {
	return (0.1808 * $og_plato) + (0.8192 * $fg_plato);
}
public static function abv($og,$fg) {
	return ($og - $fg)  / 0.75;
}
public static function calories($og,$fg) {
   
	$og_plato = Entities_BrewCalc::sg_plato($og);
	$fg_plato =Entities_BrewCalc::sg_plato($fg);
	$re = Entities_BrewCalc::real_extract($og_plato, $fg_plato);
	$abv = Entities_BrewCalc::abv($og, $fg);
	$abw = (0.79 * $abv)  /  $fg;
	$calories = (6.9 * ($abw*100) + 4*($re-0.1)) * $fg * 10;
	$carb = (($re - 0.1) * $fg * 3.55);
	return round($calories,1);

} 
}
?>
