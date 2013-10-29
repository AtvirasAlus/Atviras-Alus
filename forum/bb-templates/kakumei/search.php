<?php bb_get_header(); ?>
<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Search')?>
	</div>
	<div class="forum_search">
		<?php search_form(); ?>
	</div>
	<div class="clear"></div>
</div>


<div class="inner_container forum_search_f">
	<?php bb_topic_search_form(); ?>
</div>

<div class="inner_container">
	<?php if ( !empty ( $q ) ) : ?>
	<div class="inner_header"><?php _e('Ieškoma frazė')?> &#8220;<?php echo esc_html($q); ?>&#8221;</div>
	<?php endif; ?>

	<?php if ( $recent ) : ?>
		<div id="results-recent" class="search-results">
			<div style="font-weight: bold;"><?php _e('Naujausios žinutės')?></div>
			<ol>
				<?php foreach ( $recent as $bb_post ) : ?>
					<li<?php alt_class( 'recent' ); ?>>
						<a href="<?php post_link(); ?>"><?php topic_title($bb_post->topic_id); ?></a>
						<span class="freshness"><?php printf( __('(%s)'), bb_datetime_format_i18n( bb_get_post_time( array( 'format' => 'timestamp' ) ) ) ); ?></span>
						<p><?php echo bb_show_context($q, $bb_post->post_text); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>
	
	<?php if ( $relevant ) : ?>
		<div id="results-relevant" class="search-results">
			<div style="font-weight: bold;"><?php _e('Susijusios žinutės')?></div>
			<ol>
				<?php foreach ( $relevant as $bb_post ) : ?>
					<li<?php alt_class( 'relevant' ); ?>>
						<a href="<?php post_link(); ?>"><?php topic_title($bb_post->topic_id); ?></a>
						<span class="freshness"><?php printf( __('(%s)'), bb_datetime_format_i18n( bb_get_post_time( array( 'format' => 'timestamp' ) ) ) ); ?></span>
						<p><?php post_text(); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<?php if ( $q && !$recent && !$relevant ) : ?>
		<div><?php _e('Paieškos rezultatų nėra') ?></div>
	<?php endif; ?>
</div>

<?php bb_get_footer(); ?>
