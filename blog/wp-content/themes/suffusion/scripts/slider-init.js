/**
 * slider-init.js - Starts the JQuery slider
 */

$j = jQuery.noConflict();

$j(document).ready(function() {
    $j('#sliderContent').cycle({
		fx: suf_featured_fx,
		timeout: suf_featured_interval,
		speed: suf_featured_transition_speed,
		pause: 1,
		sync: suf_featured_sync,
		pager: '#sliderPager',
		prev: 'a.sliderPrev',
		next: 'a.sliderNext'
    });
    $j('a.sliderPause').click(
    	function() {
    		if ($j(this).text() == suf_featured_pause) {
    			$j('#sliderContent').cycle('pause');
    			$j('a.sliderPause').addClass('activeSlide');
    			$j(this).text(suf_featured_resume);
    		}
    		else {
    			$j('#sliderContent').cycle('resume');
    			$j('a.sliderPause').removeClass('activeSlide');
    			$j(this).text(suf_featured_pause);
    		}
    		return false;
    	}
    );
});
