<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 3.0.0
*/

if( !current_user_can( 'ims_change_settings')) 
	die( );
	

$templates = 
( function_exists( 'get_page_templates') ) 
? get_page_templates( ) : '';


//general settings
$settings['general'] = array(
	'deletefiles' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Delete image files', $this->domain ),
		'desc'	=> __( 'Delete files from server,when deleting a gallery/images', $this->domain ),
	 ),
	'mediarss' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Media RSS feed', $this->domain ),
		'desc'	=> __( 'Add RSS feed the blog header for unsecured galleries. Useful for CoolIris/PicLens', $this->domain ),
	 ),
	'stylesheet' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Use CSS', $this->domain ),
		'desc'	=> __( 'Use the default Image Store look', $this->domain ),
	 ),
	'imswidget' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Widget', $this->domain ),
		'desc'	=> __( 'Enable the use of the Image Store Widget', $this->domain ),
	 ),
	'disablestore' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Disable store features', $this->domain ),
		'desc'	=> __( 'Use as a gallery manager only, not a store.', $this->domain ),
	 ),
	'hidephoto' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Hide "Photo" link', $this->domain ),
		'desc'	=> __( 'Hide Photo link from the store navigation.', $this->domain ),
	 ),
	'hideslideshow' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Hide "Slideshow" link', $this->domain ),
		'desc'	=> __( 'Hide Slideshow link from the store navigation.', $this->domain ),
	 ),
	'hidefavorites' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Hide "Favorites" link', $this->domain ),
		'desc'	=> __( 'Hide Favorites link from the store navigation.', $this->domain ),
	 ),
	'ims_searchable' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Searchable Galleries', $this->domain ),
		'desc'	=> __( 'Allow galleries to show in search results.', $this->domain ),
	 ),
	'album_template' => array( 
		'type' 	=> 'select',
		'label' => __( 'Album Template', $this->domain ),
		'desc'	=> __( ' Select the template that should be used to display albums.', $this->domain ),
		'opts'	=> array( 
			'0' => __( 'Default template', $this->domain ),
			'page.php' => __( 'Page template', $this->domain ),
		) + array_flip( (array) $templates ),
	 ),
	'album_per_page' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Albums per page', $this->domain ),
		'desc'	=> __( 'How many albums to display per page on the front-end.', $this->domain ),
	 ),
);

//gallery
$settings['gallery'] = array(
	'galleriespath' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Gallery folder path', $this->domain ),
		'desc'	=> __( 'Default folder path for all the galleries images', $this->domain ),
	 ),
	'securegalleries' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Secure galleries', $this->domain ),
		'desc'	=> __( 'Secure all new galleries with a password by default.', $this->domain ),
	 ),
	'colorbox' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Colorbox', $this->domain ),
		'desc'	=> __( 'Use the default ligthbox feature.', $this->domain ),
	 ),
	'wplightbox' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Ligthbox for WP galleries', $this->domain ),
		'desc'	=> __( 'Use lightbox on WordPress Galleries.', $this->domain ),
	 ),
	'disablebw' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Disable B and W', $this->domain ),
		'desc'	=> __( 'Disable black and white color option.', $this->domain ),
	 ),
	'disablesepia' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Disable sepia', $this->domain ),
		'desc'	=> __( 'Disable sepia color option.', $this->domain ),
	 ),
	'downloadorig' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Download Original', $this->domain ),
		'desc'	=> __( 'Allow users to download original image if image size selected is not available.', $this->domain ),
	 ),
	'attchlink' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Link image to attachment', $this->domain ),
		'desc'	=> __( 'Link image to image page instead of image file.', $this->domain ),
	 ),
	'ims_page_secure' => array( 
		'type' 	=> 'select',
		'label' => __( 'Secure galleries page', $this->domain ),
		'desc'	=> __( ' Page used to display the gallery login form', $this->domain ),
	 ),
	'gallery_template' => array( 
		'type' 	=> 'select',
		'label' => __( 'Gallery template', $this->domain ),
		'desc'	=> __( ' Select the template that should be used to display the galleries.', $this->domain ),
		'opts'	=> array( '0' => __( 'Default template', $this->domain ) ) + array_flip( (array) $templates ),
	 ),
	'imgs_per_page' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Images per page', $this->domain ),
		'desc'	=> __( 'How many images to display per page on the front-end.', $this->domain ),
	 ),
	'galleryexpire' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __('Galleries expire after', $this->domain ),
		'desc'	=> __( 'In days, set to 0 to remove expiration default.', $this->domain ),
	 ),
	'imgsortorder' => array( 
		'type' 	=> 'select',
		'label' => __( 'Sort images', $this->domain ),
		'opts'	=> array(
			'menu_order' => __( 'Custom order', $this->domain ),
			'post_excerpt' => __( 'Caption', $this->domain ),
			'post_title' => __( 'Image title', $this->domain ),
			'post_date' => __( 'Image date', $this->domain ),
		 ),
	 ),
	'imgsortdirect' => array( 
		'type' 	=> 'select',
		'label' => __( 'Sort direction', $this->domain ),
		'opts'	=> array(
			'ASC' => __( 'Ascending', $this->domain ),
			'DESC' => __( 'Descending', $this->domain ),
		 ),
	 ),
);

//page option
$pages = get_pages( );
foreach( (array)$pages as $page )
	$settings['gallery']['ims_page_secure']['opts'][$page->ID] = $page->post_title;

//image
$settings['image'] = array(
	'preview_size_' => array(
		'multi' => true,
		'label' => __( 'Image preview size(pixels)', $this->domain ),
		'opts'	=> array(
			'w' => array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Max Width', $this->domain ),
			 ),
			'h' => array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Max Height', $this->domain ),
			 ),
			'q' => array( 
				'val'	=> '',
				'label' => __( 'Quality', $this->domain ), 
				'type' 	=> 'text',
				'desc'	=> '(1-100)',
			 ),
		 ),
	 ), 
	'watermark' => array( 
		'type' 	=> 'radio',
		'label' => __( 'Watermark', $this->domain ),
		'opts'	=> array(
			'0' => __( 'No watermark', $this->domain ),
			'1' => __( 'Use text as watermark', $this->domain ),
			'2' => __( 'Use image as watermark', $this->domain ),
		 ),
	 ),
	'watermark_' => array( 
		'multi' => true,			 
		'type' 	=> 'text',
		'label' => __( 'Watermark options', $this->domain ),
		'opts'	=> array(
			'text' 	=> array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Text', $this->domain ),
			 ),
			'color' => array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Color', $this->domain ), 
				'desc'	=> ' #Hex' 
			 ),
			'size'	=> array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Font size', $this->domain ) 
			 ),
			'trans' => array( 
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Transparency', $this->domain ), 
				'desc'	=> ' (0-127)'
			 ),
		 ),
	 ),
	'watermarkurl' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' =>  __( 'Watermark URL', $this->domain ) ,
		'desc'	=> __( 'Full URL to image, PNG with transparency recommended', $this->domain ),
	 ),
);

//slideshow
$settings['slideshow'] = array(
	array(
		'col'	=> true,
		'opts' 	=> array(
			'numThumbs' => array(
				'type' 	=> 'text',
				'label' => __( 'Number of thumbnails to show', $this->domain ),
			 ),
			'maxPagesToShow' => array(
				'type' 	=> 'text',
				'label' => __( 'Maximun number of pages', $this->domain ),
			)
		 ),
	 ),
	array(
		'col'	=> true,
		'opts' 	=> array(
			'transitionTime' => array(
				'type' 	=> 'text',
				'label' => __( 'Transition time', $this->domain ),
				'desc'	=> __( '1000 = 1 second', $this->domain ),
			 ),
			'slideshowSpeed' => array(
				'type' 	=> 'text',
				'label' => __( 'Slideshow speed', $this->domain ),
				'desc'	=> __( '1000 = 1 second', $this->domain ),
			)
		 ),
	 ),
	array(
		'col'	=> true,
		'opts' 	=> array(
			'playLinkText' => array(
				'type' 	=> 'text',
				'label' => __( 'Play link text', $this->domain ),
			 ),
			'pauseLinkTex' => array(
				'type' 	=> 'text',
				'label' => __( 'Pause link text', $this->domain ),
			)
		 ),
	 ),
	array(
		'col'	=> true,
		'opts' 	=> array(
			'nextLinkText' => array(
				'type' 	=> 'text',
				'label' => __( 'Next link text', $this->domain ),
			 ),
			'prevLinkText' => array(
				'type' 	=> 'text',
				'label' => __( 'Previous link text', $this->domain ),
			)
		 ),
	 ),
	array(
		'col'	=> true,
		'opts' 	=> array(
			'nextPageLinkText' => array(
				'type' 	=> 'text',
				'label' => __( 'Next page link text', $this->domain ),
			 ),
			'prevPageLinkText' => array(
				'val'	=> '',
				'type' 	=> 'text',
				'label' => __( 'Previous page link text', $this->domain ),
			)
		 ),
	 ),
	array(
		'col'	=> true,
		'opts' 	=> array(
			'closeLinkText' => array(
				'type' 	=> 'text',
				'label' => __( 'Close link text', $this->domain ),
			 ),
			'autoStart' => array(
				'val'	=> 1,
				'type' 	=> 'checkbox',
				'label' => __( 'Auto start?', $this->domain ),
			)
		 ),
	 ),
	
);

//payment
$settings['payment'] = array(
	'symbol' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Currency Symbol', $this->domain ),
	 ),
	 'disable_decimal' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Disable decimal point', $this->domain ),
		'desc'	=> __( 'Disable auto format prices with a decimal points.', $this->domain ),
	 ),
	'clocal' => array( 
		'type' 	=> 'radio',
		'label' => __( 'Currency Symbol Location', $this->domain ),
		'opts'	=> array(
			'1' => __( '&#036;100', $this->domain ),
			'2' => __( '&#036; 100', $this->domain ),
			'3' => __( '100&#036;', $this->domain ),
			'4' => __( '100 &#036;', $this->domain ),
		 ),
	 ),
	'currency' 	=> array( 
		'type' 	=> 'select',
		'label' => __( 'Default Currency', $this->domain ),
		'opts'	=> array( 
			'0' => __( 'Please Choose Default Currency', $this->domain ),
			'AUD' =>__( 'Australian Dollar', $this->domain ),
			'BRL' =>__( 'Brazilian Real', $this->domain ),
			'CAD' =>__( 'Canadian Dollar', $this->domain ),
			'CZK' =>__( 'Czech Koruna', $this->domain ),
			'DKK' =>__( 'Danish Krone', $this->domain ),
			'EUR' =>__( 'Euro', $this->domain ),
			'HKD' =>__( 'Hong Kong Dollar', $this->domain ),
			'HUF' =>__( 'Hungarian Forint', $this->domain ),
			'ILS' =>__( 'Israeli New Sheqel', $this->domain ),
			'JPY' =>__( 'Japanese Yen', $this->domain ),
			'MYR' =>__( 'Malaysian Ringgit', $this->domain ),
			'MXN' =>__( 'Mexican Peso', $this->domain ),
			'NOK' =>__( 'Norwegian Krone', $this->domain ),
			'NZD' =>__( 'New Zealand Dollar', $this->domain ),
			'PHP' =>__( 'Philippine Peso', $this->domain ),
			'PLN' =>__( 'Polish Zloty', $this->domain ),
			'GBP' =>__( 'Pound Sterling', $this->domain ),
			'SGD' =>__( 'Singapore Dollar', $this->domain ),
			'ZAR' =>__( 'South African Rands', $this->domain ),
			'SEK' =>__( 'Swedish Krona', $this->domain ),
			'CHF' =>__( 'Swiss Franc', $this->domain ),
			'TWD' =>__( 'Taiwan New Dollar', $this->domain ),
			'THB' =>__( 'Thai Baht', $this->domain ),
			'TRY' =>__( 'Turkish Lira', $this->domain ),
			'USD' =>__( 'U.S.Dollar', $this->domain ),
		 ),
	 ),
	'gateway' => array( 
		'type' 	=> 'select',
		'label' => __( 'Payment gateway', $this->domain ),
		'opts'	=> array( 
			'notification' => __( 'Email notification only', $this->domain ),
			'paypalsand' => __( 'Paypal Cart Sanbox (test)', $this->domain ),
			'paypalprod' => __( 'Paypal Cart Production (live)', $this->domain ),
			'googlesand' => __( 'Google Checkout Sandbox (test)', $this->domain ),
			'googleprod' => __( 'Google Checkout Production (live)', $this->domain ),
			'custom' 		 => __( 'Other', $this->domain ),
		)
	 ),
);


//settings base on payment option
if( $this->in_array( $this->opts['gateway'], array( 'googlesand', 'googleprod') ) ){
	$settings['payment']['taxcountry'] = array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => '<a href="http://goes.gsfc.nasa.gov/text/web_country_codes.html" target="_blank">'.__( 'Country Code', $this->domain ).'</a>',
	);
	$settings['payment']['googleid'] = array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Google merchant ID', $this->domain ),
	);
	$settings['payment']['googlekey'] = array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Merchant key', $this->domain ),
	);
	
} elseif( $this->in_array( $this->opts['gateway'], array( 'paypalsand', 'paypalprod') ) ){
	$settings['payment']['paypalname'] = array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'PayPal Account E-mail', $this->domain ),
	);

}elseif( $this->opts['gateway'] == 'custom' ){
	
	$settings['payment']['gateway_method'] = array( 
		'type' 	=> 'radio',
		'label' => __( 'Method', $this->domain ),
		'opts'	=> array(
			'get' => __( 'Get', $this->domain ),
			'post' => __( 'Post', $this->domain ),
		 ),
	);
	$settings['payment']['gateway_url'] = array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'URL', $this->domain ),
	);
	$settings['payment']['data_pair'] = array( 
		'val'	=> '',
		'type' 	=> 'textarea',
		'label' => __( 'Data pair', $this->domain ),
		'desc'	=> __( 'Enter key|value should be separated by a pipe, and each data pair by a comma. 
		 ex: key|value,Key|value. <br />
		<strong>Note:</strong> you will have to setup your own notification script record sales.<br />
		<strong>Tags:</strong> ', $this->domain ) . str_replace( '/', '',implode( ', ', $this->opts['carttags']) ),
	);

}else{
	$settings['payment']['shippingmessage'] = array( 
		'val'	=> '',
		'type' 	=> 'textarea',
		'label' => __( 'Shipping Message', $this->domain ),
	);
	$settings['payment']['required_'] = array( 
		'multi'	=> true,
		'label' => __( 'Required Fields', $this->domain ),
	);
	
	foreach( (array) $this->opts['checkoutfields'] as $key => $label )
		$settings['payment']['required_']['opts'][$key] = array( 'val' => 1, 'label' => $label, 'type' => 'checkbox' );
}

//checkout
$settings['checkout'] = array(
	'taxamount' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Tax', $this->domain ),
		'desc'	=> __( 'Set tax to zero (0) to remove tax calculation.', $this->domain ),
	 ),
	'taxtype' => array( 
		'type' 	=> 'select',
		'label' => __( 'Tax calculation type', $this->domain ),
		'opts' => array(
			'percent' => __( 'Percent', $this->domain ),
			'amount' => __( 'Amount', $this->domain ),
		 ),
 	 ),
	'notifyemail' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Order Notification email(s)', $this->domain ),
	 ), 
	'notifysubj' => array( 
		'val'	=> '',
		'type' 	=> 'text',
		'label' => __( 'Order Notification subject', $this->domain ),
	 ), 
	'notifymssg' => array( 
		'type' 	=> 'textarea',
		'label' => __( 'Order Notification message', $this->domain ),
		'desc'	=> __( 'Tags: ', $this->domain ) . str_replace( '/', '', implode( ', ', (array)$this->opts['tags'] )),
	 ), 
	'emailreceipt' => array( 
		'val'	=> 1,
		'type' 	=> 'checkbox',
		'label' => __( 'Email receipt', $this->domain ),
		'desc'	=> __( 'Email purchase reciept to customers if they provide an email.', $this->domain ),
	 ),
	'thankyoureceipt' => array( 
		'type' 	=> 'textarea',
		'label' => __( 'Purchase Receipt', $this->domain ),
		'desc'	=> __( 'Thank you message and receipt information', $this->domain ),
	 ), 
	'termsconds' => array( 
		'type' 	=> 'textarea',
		'label' => __( 'Terms and Conditions', $this->domain ),
		'desc'	=> __( 'Shown below the shopping cart', $this->domain ),
	 ),
);

//permissions

$settings['permissions'] = array(
	'userid' => array( 
		'type' 	=> 'select',
		'label' => __( 'Select User', $this->domain ),
		'opts' => $this->get_users( ),
	 ),
);

if( !empty($_GET['userid']) ){
	$userid = (int)$_GET['userid'];
	$settings['permissions']['ims_'] = array( 
		'multi'	=> true,	
		'type' 	=> 'checkbox',
		'label' => __( 'Permissions', $this->domain ),
	);
	foreach( $this->uopts['caplist'] as $cap => $capname )
		$settings['permissions']['ims_']['opts'][$cap] = array( 'val' => 1, 'label' => $capname, 'type' => 'checkbox', 'user' => $userid );
	$this->opts['userid'] = $userid;
}

//reset
$settings['reset'] = array(
	'empty1' => array( 'type' => 'empty'	 ),
	'resetsettings' => array(
		'type' 	=> 'submit',
		'label' => __( 'Reset', $this->domain ),
		'val'	=> __( 'Reset all settings to defaults', $this->domain ),
	 ),
	'empty2' => array( 'type' => 'empty'	 ),
	'uninstall' => array(
		'type' 	=> 'uninstall',
		'label' => __( 'Uninstall', $this->domain ),
		'val'	=> __( 'Uninstall Image Store', $this->domain ),
		'desc'	=> __( '<p><strong>UNINSTALL IMAGE STORE WARNING.</strong></p>
					 <p>Once uninstalled,this cannot be undone.<strong> You should backup your database </strong> and image files before doing this, Just in case.
					 If you are not sure what are your doing,please don not do anything</p>', $this->domain ),
	 ),
);

$settings = apply_filters( 'ims_setting_fields', $settings );

?>