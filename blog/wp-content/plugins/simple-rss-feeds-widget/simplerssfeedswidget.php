<?php
/*
Plugin Name: Simple RSS Feeds Widget
Plugin URI: http://petitnuage.fr/developpement/simple-rss-feeds-widget-wordpress/
Description: A widget to display third party feeds on your blog.
Version: 1.0.2
Author: bourse, petitnuage
Author URI: http://petitnuage.fr/
License: GPL2
*/

/*  Copyright 2010 Petit Nuage (email: contact@petitnuage.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( ABSPATH . WPINC . '/feed.php' );

/**
 * SimpleRssFeedsWidget_Widget Class
 */
class SimpleRssFeedsWidget_Widget extends WP_Widget
{
	/** Constructor */
	function SimpleRssFeedsWidget_Widget()
	{
		parent::WP_Widget(
			'simplerssfeedswidget',
			__( 'Simple RSS Feeds Widget', 'simplerssfeedswidget' ),
			array(	'classname' => 'simplerssfeedswidget', 'description' => __( 'Display third party feeds', 'simplerssfeedswidget' ) )
		);
	}

	/** @see WP_Widget::form */
	function form( $instance )
	{
		$title = esc_attr( $instance[ 'title' ] );
		if( empty( $title ) )
			$title = __( 'Business and Stock Market News', 'simplerssfeedswidget' );

		$rss = esc_attr( $instance[ 'rss' ] );
		if( empty( $rss ) )
		{
			$rss = __( 'DEFAULT_RSS_FEEDS', 'simplerssfeedswidget' );
			if( $rss == 'DEFAULT_RSS_FEEDS' )
			{
				// Default RSS feeds list if none has been provided for the blog locale
				/*
				$rss =	 "http://www.labourseenaction.fr/feed/!"					// La bourse en ligne
						."http://www.capital.fr/rss2/feed/fil-bourse.xml!"			// Capital.fr - Fil Bourse
						."http://www.moneyweek.fr/feed/!"							// MoneyWeek
						."http://rss.feedsportal.com/c/268/f/3632/index.rss!"		// Boursier.com - actualité de la Bourse
						."http://www.latribune.fr/rss/rss-bourse.html!"				// La Tribune.fr - RSS Bourse
						."http://rss.feedsportal.com/c/499/f/413866/index.rss!"		// Les Echos - actualité sur les sociétés du cac 40
						."http://rss.feedsportal.com/c/499/f/413862/index.rss!"		// Les Echos - recommandations, conseils et analyses
						."http://www.lefigaro.fr/rss/figaro_bourse.xml!"			// LE FIGARO - Le Figaro – Bourse de Paris : Actualité financière, cotations et cours en direct
						."http://www.lemonde.fr/rss/tag/economie.xml!"				// Economie - LeMonde.fr
						."http://rss.feedsportal.com/c/32268/f/438247/index.rss!"	// Libération - Économie
						."http://rss.feedsportal.com/c/568/f/9917/index.rss!"		// LEXPRESS.fr - Economie
						."http://rss.nouvelobs.com/c/32262/fe.ed/tempsreel.nouvelobs.com/actualite/economie/rss.xml!"	// Nouvelobs.com en temps réel : Economie
						."http://rss.challenges.fr/c/32261/f/437855/index.rss!"		// Challenges.fr en temps réel - La Une
						."http://rss.challenges.fr/c/32261/f/437870/index.rss!"		// Bourse (blog Challenges.fr)
						."http://www.lepoint.fr/content/system/rss/economie/economie_doc.xml"	// Le Point.fr : Economie
						;
				*/
				$rss =   "http://feeds.nytimes.com/nyt/rss/Business!"										// Business
						."http://www.washingtonpost.com/wp-dyn/rss/business/index.xml!"						// Business
						."http://www.washingtonpost.com/wp-dyn/rss/business/economy/index.xml!"				// Economy
						."http://www.washingtonpost.com/wp-dyn/rss/linkset/2005/04/18/LI2005041800997.xml!"	// Financial Industry News
						."http://www.washingtonpost.com/wp-dyn/rss/linkset/2005/03/28/LI2005032800718.xml!"	// Investing
						."http://www.washingtonpost.com/wp-dyn/rss/business/special/3/index.xml!"			// Oil and Gas Prices
						."http://www.washingtonpost.com/wp-dyn/rss/business/personalfinance/index.xml!"		// Personal Finance
						."http://www.washingtonpost.com/wp-dyn/rss/linkset/2005/03/28/LI2005032800573.xml!"	// Week in Stocks
						."http://www.efinancialnews.com/assetmanagement/rss!"								// Asset Management
						."http://www.efinancialnews.com/investmentbanking/rss!"								// Investment Banking
						."http://www.efinancialnews.com/privateequity/rss!"									// Private Equity
						."http://rss.cnn.com/rss/money_topstories.rss!"										// Money Top Stories
						."http://rss.cnn.com/rss/money_markets.rss!"										// Money Markets
						."http://rss.cnn.com/rss/magazines_fortuneintl.rss!"								// Money Fortune International
						."http://www.cnbc.com/id/20746031/device/rss/rss.html!"								// Economy Headlines
						."http://www.cnbc.com/id/20761872/device/rss/rss.html!"								// Stock Market Headlines
						."http://www.ft.com/rss/home/uk!"													// UK Homepage
						."http://www.ft.com/rss/home/us!"													// US Homepage
						."http://www.ft.com/rss/home/europe!"												// Europe Homepage
						."http://newsrss.bbc.co.uk/rss/newsonline_world_edition/front_page/rss.xml!"		// News Front Page
						."http://www.marketwatch.com/rss/topstories!"										// Top Stories
						."http://www.forbes.com/business/index.xml!"										// Business
						."http://online.wsj.com/xml/rss/3_7085.xml!"										// World News
						."http://feeds2.feedburner.com/time/business"										// Times' Biz & Tech
						;
			}
		}
		$rss = str_replace( '!', "\n", $rss );

		// Introduction text
		$intro = esc_attr( $instance[ 'intro' ] );
		if( empty( $intro ) )
			$intro = __( 'Here are some economic and stock market news:', 'simplerssfeedswidget' );
		
		// Get parameters
		$maxDisplayedItemsPerSource = $instance[ 'maxDisplayedItemsPerSource' ];
		if( !isset( $maxDisplayedItemsPerSource ) || $maxDisplayedItemsPerSource <= 0 || $maxDisplayedItemsPerSource > 100 )
			$maxDisplayedItemsPerSource = 2;

		$maxDisplayedItemsInTotal = $instance[ 'maxDisplayedItemsInTotal' ];
		if( !isset( $maxDisplayedItemsInTotal ) || $maxDisplayedItemsInTotal <= 0 || $maxDisplayedItemsInTotal > 100 )
			$maxDisplayedItemsInTotal = 5;

		// Source
		$source = $instance[ 'source' ];
		if( $source != 'displayed' && $source != 'hidden' )
			$source = 'displayed';

		// Signature
		$signature = $instance[ 'signature' ];
		if( $signature != 'full' && $signature != 'short' && $signature != 'hidden' )
			$signature = 'hidden';

		?>
		<p>
			<label for="<?php echo( $this->get_field_id( 'title' ) ); ?>">
				<?php _e( 'Title:', 'simplerssfeedswidget' ); ?>
				<input class="widefat" id="<?php echo( $this->get_field_id( 'title' ) ); ?>" name="<?php echo( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo( $title ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'intro' ) ); ?>">
				<?php _e( 'Introduction text:', 'simplerssfeedswidget' ); ?>
				<textarea cols="40" rows="5" class="widefat" id="<?php echo( $this->get_field_id( 'intro' ) ); ?>" name="<?php echo( $this->get_field_name( 'intro' ) ); ?>"><?php echo( $intro ); ?></textarea>
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'rss' ) ); ?>">
				<?php _e( 'RSS feeds (one per line):', 'simplerssfeedswidget' ); ?>
				<textarea cols="40" rows="5" class="widefat" id="<?php echo( $this->get_field_id( 'rss' ) ); ?>" name="<?php echo( $this->get_field_name( 'rss' ) ); ?>"><?php echo( $rss ); ?></textarea>
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'maxDisplayedItemsPerSource' ) ); ?>">
				<?php _e( 'Maximum displayed items from same source:', 'simplerssfeedswidget' ); ?>
				<input class="widefat" id="<?php echo( $this->get_field_id( 'maxDisplayedItemsPerSource' ) ); ?>" name="<?php echo( $this->get_field_name( 'maxDisplayedItemsPerSource' ) ); ?>" type="text" value="<?php echo( $maxDisplayedItemsPerSource ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'maxDisplayedItemsInTotal' ) ); ?>">
				<?php _e( 'Maximum displayed items in total:', 'simplerssfeedswidget' ); ?>
				<input class="widefat" id="<?php echo( $this->get_field_id( 'maxDisplayedItemsInTotal' ) ); ?>" name="<?php echo( $this->get_field_name( 'maxDisplayedItemsInTotal' ) ); ?>" type="text" value="<?php echo( $maxDisplayedItemsInTotal ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'source' ) ); ?>">
				<?php _e( 'RSS Feed Source:', 'simplerssfeedswidget' ); ?>
				<select name="<?php echo $this->get_field_name('source'); ?>" id="<?php echo $this->get_field_id('source'); ?>" class="widefat">
					<option value="displayed"<?php selected( $source, 'displayed' ); ?>><?php _e( 'Display source', 'simplerssfeedswidget' ); ?></option>
					<option value="hidden"<?php selected( $source, 'hidden' ); ?>><?php _e( 'Hide source', 'simplerssfeedswidget' ); ?></option>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'signature' ) ); ?>">
				<?php _e( 'Widget Signature:', 'simplerssfeedswidget' ); ?>
				<select name="<?php echo $this->get_field_name('signature'); ?>" id="<?php echo $this->get_field_id('signature'); ?>" class="widefat">
					<option value="full"<?php selected( $signature, 'full' ); ?>><?php _e( 'Display full signature', 'simplerssfeedswidget' ); ?></option>
					<option value="short"<?php selected( $signature, 'short' ); ?>><?php _e( 'Display short signature', 'simplerssfeedswidget' ); ?></option>
					<option value="hidden"<?php selected( $signature, 'hidden' ); ?>><?php _e( 'Hide signature', 'simplerssfeedswidget' ); ?></option>
				</select>
			</label>
		</p>

		<?php
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )
	{
		// processes widget options to be saved
		$instance = $old_instance;

		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'intro' ] = strip_tags( $new_instance[ 'intro' ] );
		$instance[ 'rss' ] = strip_tags( $new_instance[ 'rss' ] );
		
		$instance[ 'maxDisplayedItemsPerSource' ] = $new_instance[ 'maxDisplayedItemsPerSource' ];
		if(    !is_numeric( $instance[ 'maxDisplayedItemsPerSource' ] )
			|| $instance[ 'maxDisplayedItemsPerSource' ] <= 0
			|| $instance[ 'maxDisplayedItemsPerSource' ] > 100 )
		{
			$instance[ 'maxDisplayedItemsPerSource' ] = 2;
		}
		
		$instance[ 'maxDisplayedItemsInTotal' ] = $new_instance[ 'maxDisplayedItemsInTotal' ];
		if(    !is_numeric( $instance[ 'maxDisplayedItemsInTotal' ] )
			|| $instance[ 'maxDisplayedItemsInTotal' ] <= 0
			|| $instance[ 'maxDisplayedItemsInTotal' ] > 100 )
		{
			$instance[ 'maxDisplayedItemsInTotal' ] = 5;
		}

		$instance[ 'signature' ] = $new_instance[ 'signature' ];
		if( $instance[ 'signature' ] != 'full' && $instance[ 'signature' ] != 'short' && $instance[ 'signature' ] != 'hidden' )
			$instance[ 'signature' ] = 'hidden';

		$instance[ 'source' ] = $new_instance[ 'source' ];
		if( $instance[ 'source' ] != 'displayed' && $instance[ 'source' ] != 'hidden' )
			$instance[ 'source' ] = 'displayed';

		return $instance;

	}

	static function sort( $a, $b )
	{
		if( $a->get_date( 'U' ) < $b->get_date( 'U' ) )
		{
			return 1;
		}

		if( $a->get_date( 'U' ) > $b->get_date( 'U' ) )
		{
			return -1;
		}
		
		return 0;
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance )
	{
		// outputs the content of the widget
		extract( $args );

		echo( $before_widget );

		// Get title
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		echo( $before_title . $title . $after_title );

		// Get intro
		$title = apply_filters( 'widget_title', $instance[ 'intro' ] );
		echo( '<p>' . $title . '</p>' );

		// Get parameters
		$maxDisplayedItemsPerSource = $instance[ 'maxDisplayedItemsPerSource' ];
		if( !isset( $maxDisplayedItemsPerSource ) || $maxDisplayedItemsPerSource <= 0 || $maxDisplayedItemsPerSource > 100 )
			$maxDisplayedItemsPerSource = 2;

		$maxDisplayedItemsInTotal = $instance[ 'maxDisplayedItemsInTotal' ];
		if( !isset( $maxDisplayedItemsInTotal ) || $maxDisplayedItemsInTotal <= 0 || $maxDisplayedItemsInTotal > 100 )
			$maxDisplayedItemsInTotal = 5;

		// Get source
		$source = $instance[ 'source' ];
		if( $source != 'displayed' &&  $source != 'hidden' )
			$source = 'displayed';

		// Get signature
		$signature = $instance[ 'signature' ];
		if( $signature != 'full' && $signature != 'short' && $signature != 'hidden' )
			$instance[ 'signature' ] = 'hidden';

		// Get and display RSS feeds
		$rss = $instance[ 'rss' ];
		$rssUris  = explode( "\n", $rss );
		// parse feeds URIs
		$displayedItems = array();
		foreach( $rssUris as $rssUri )
		{
			// Is the URI fine?
			if( !empty( $rssUri ) )
			{
				// OK, fetch feed
				$feed = fetch_feed( $rssUri );
				if( !is_wp_error( $feed ) )
				{
					// OK
					$maxItems = $feed->get_item_quantity( $maxDisplayedItemsPerSource );
					if( $maxItems > 0 )
					{
						// OK, found items in the fetched feed
						$feedItems = $feed->get_items( 0, $maxItems );
						foreach( $feedItems as $item )
						{
							$displayedItems[] = $item;
						}
					}
				}
			}
		}

		// Do we have at least 1 item to display?
		if( !empty( $displayedItems ) )
		{
			// Yes, we do
			usort( $displayedItems, 'SimpleRssFeedsWidget_Widget::sort' );

			echo( '<ul>' );
			$displayedItemsCount = 0;
			foreach( $displayedItems as $item )
			{
				echo( '<li><a title="'.date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ).'" href="'.$item->get_permalink().'">'.$item->get_title().'</a>' );
				if( $source != 'hidden' )
					echo( ' <small>(<cite>'.$item->get_feed()->get_title().'</cite>)</small>' );
				echo( '</li>');
				
				$displayedItemsCount++;
				if( $displayedItemsCount > $maxDisplayedItemsInTotal )
				{
					break;
				}
			}
			echo( '</ul>' );
		}

		// Display signature
		if( $signature != 'hidden' )
		{
			echo( '<p><small>' );
			if( $signature == 'full' )
			{
				printf(
					__( 'Powered by %s supplied by %s', 'simplerssfeedswidget' ),
					'<a title="'.__( 'WordPress RSS Widget', 'simplerssfeedswidget' ).'" href="http://wordpress.org/extend/plugins/simple-rss-feeds-widget/">'.__( 'Simple RSS Feeds Widget', 'simplerssfeedswidget' ).'</a>',
					'<a title="bourse" href="http://www.labourseenaction.fr/">Bourse en ligne</a>' );
			}
			else
			{
				printf(
					__( 'Powered by %s', 'simplerssfeedswidget' ),
					'<a title="'.__( 'WordPress RSS Widget', 'simplerssfeedswidget' ).'" href="http://wordpress.org/extend/plugins/simple-rss-feeds-widget/">'.__( 'Simple RSS Feeds Widget', 'simplerssfeedswidget' ).'</a>' );
			}
			echo( '</small></p>' );
		}

		echo( $after_widget );
	}

} // SimpleRssFeedsWidget_Widget

function SimpleRssFeedsWidget_Widget_Register()
{
	load_plugin_textdomain( 'simplerssfeedswidget', false, dirname( plugin_basename( __FILE__ ) ) );
	return register_widget( "SimpleRssFeedsWidget_Widget" );
}

// register widget
add_action( 'widgets_init', 'SimpleRssFeedsWidget_Widget_Register' );

