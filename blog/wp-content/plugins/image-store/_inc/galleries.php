<?php

/**
 * Image store - admin galleries
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 3.0.0
 */

class ImStoreGallery extends ImStoreAdmin {

	/**
	 * Public variables
	 */
	public $disabled = '';
	public $galpath = '';
	public $error = false;
	public $is_trash = false;
	public $order = array();
	public $meta = array();
	public $gallery = array();
	public $sortby = array();
	public $metaboxes = array();
	public $columns = array();

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function ImStoreGallery() {
		parent::ImStoreAdmin();

		//add columns
		add_filter('ims_async_upload', array(&$this, 'display_image_columns'), 0, 3);
		add_action('admin_print_styles', array(&$this, 'gallery_screen_columns'), 0, 0);

		add_filter('upload_dir', array(&$this, 'change_upload_path'), 80, 1);
		add_filter('ims_image_row_actions_metadata', array(&$this, 'iptc_data'), 100, 3);

		//speed up wordpress load
		if (defined('DOING_AJAX') || defined('DOING_AUTOSAVE' || defined('SHORTINIT')))
			return;

		//save / create post data 
		add_action('save_post', array(&$this, 'save_post'), 10, 5);
		add_action('admin_init', array(&$this, 'gallery_init'), 10, 2);

		//load styles and scripts
		add_action('admin_print_styles', array(&$this, 'gallery_styles'), 1);
		add_action('admin_print_scripts', array(&$this, 'gallery_scripts'), 1);

		//add multydata form attr
		add_action('post_edit_form_tag', array(&$this, 'multidata_form'), 20);

		if (version_compare($this->wp_version, '3.3', '>='))
			add_action('ims_upload_images_tab_content', 'media_upload_form', 1);
		else
			add_action('ims_upload_images_tab_content', array(&$this, 'upload_images_tab'), 1);

		add_action('ims_upload_zip_tab_content', array(&$this, 'upload_zip_tab'), 1);
		add_action('ims_import_folder_tab_content', array(&$this, 'import_folder_tab'), 1);

		add_filter('plupload_init', array(&$this, 'plupload_init'), 15, 2);
		add_filter('screen_settings', array(&$this, 'screen_settings'), 15, 2);
		add_filter('redirect_post_location', array(&$this, 'post_messeges'), 25);
		add_filter('post_updated_messages', array(&$this, 'add_auto_password'), 1);
	}

	/**
	 * Initial actions
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function gallery_init() {

		if ($this->galid) {
			global $post;
			$this->meta = get_post_custom($this->galid);
			$this->gallery = ( isset($post) ) ? $post : get_post($this->galid);
		}

		$this->metaboxes = array(
			'ims_info_box' => __('Gallery Information', 'ims'),
			'ims_import_box' => __('Import Images', 'ims'),
			'ims_images_box' => __('Images', 'ims'),
		);

		$this->order = array(
			'ASC' => __('Ascending', 'ims'),
			'DESC' => __('Descending', 'ims'),
		);
		
		$this->sortby = array(
			'title' => __('Image title', 'ims'),
			'date' => __('Image date', 'ims'),
			'excerpt' => __('Caption', 'ims'),
			'menu_order' => __('Custom order', 'ims'),
		);

		$this->import_tabs = array(
			'upload_images' => __('Upload Images', 'ims'),
			'upload_zip' => __('Upload zip file', 'ims'),
			'import_folder' => __('Scan folder', 'ims'),
		);
		
		$this->exts = (array)apply_filters('ims_allowed_extensions', array('jpg','jpeg','gif','png'));

		foreach ($this->metaboxes as $key => $label)
			add_meta_box($key, $label, array(&$this, $key), "ims_gallery", "normal");
		add_meta_box("ims_customers_box", __('Customers', 'ims'), array(&$this, "customers_metabox"), "ims_gallery", "side", "low");

		do_action('ims_gallery_init', $this);
	}

	/**
	 * Make post edit form multidata
	 *
	 * @since 2.0.0
	 * return void
	 */
	function multidata_form() {
		global $current_screen;
		if ($current_screen->id == 'ims_gallery')
			echo 'enctype="multipart/form-data"';
	}

	/**
	 * Add pic data link
	 *
	 * @since 2.0.0
	 * return void
	 */
	function iptc_data($link, $id, $meta) {
		if (empty($this->ajaxnonce))
			$this->ajaxnonce = wp_create_nonce('ims_ajax');
		return ' | <a href="' . IMSTORE_ADMIN_URL . '/ajax.php?action=imageiptc&id=' . $id . '&_wpnonce=' . $this->ajaxnonce . '" class="thickbox" title="' . __('image metadata') . '">' . __('IPTC', 'ims') . '</a>';
	}

	/**
	 * Load gallery styles
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function gallery_styles() {
		global $post;
		if (isset($post->post_type) && $post->post_type != 'ims_gallery')
			return;
		wp_enqueue_style('thickbox');
		wp_enqueue_style('datepicker', IMSTORE_URL . '/_css/jquery-datepicker.css', false, $this->version, 'all');
	}

	/**
	 * Display message after post 
	 * has been saved
	 *
	 * @param $loc string
	 * @since 2.0.0
	 * return string
	 */
	function post_messeges($loc) {
		if (empty($this->errors))
			return add_query_arg('error', $this->error, $loc);
		return $loc;
	}

	/**
	 * Load admin scripts
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function gallery_scripts() {
		global $post;

		if (isset($post->post_type) && $post->post_type != 'ims_gallery')
			return;

		wp_enqueue_script('thickbox');
		wp_enqueue_script('swfupload-all');
		wp_enqueue_script('swfupload-handlers');

		wp_enqueue_script('ims-gallery', IMSTORE_URL . '/_js/galleries.js', array('jquery'), $this->version, true);
		wp_enqueue_script('datepicker', IMSTORE_URL . '/_js/jquery-ui-datepicker.js', array('jquery'), $this->version);

		//add drag and drop media upload
		if (version_compare($this->wp_version, '3.3', '>='))
			wp_enqueue_script('plupload-handlers', IMSTORE_URL . '/_js/swupload.js');
		else
			wp_enqueue_script('ims-swupload', IMSTORE_URL . '/_js/swupload.js', array('jquery'), $this->version, true);

		wp_localize_script('ims-gallery', 'imsgal', array('adminurl' => rtrim(admin_url(),'/'), 'trash' => __('Trash', 'ims'),
			'deletefile' => $this->opts['deletefiles'], 'imsajax' => IMSTORE_ADMIN_URL . '/ajax.php',
		));
	}

	/**
	 * Hacky way to add auto-genarated
	 * password to new galleries
	 *
	 * @return array|null
	 * @since 2.0.0
	 */
	function add_auto_password($messages) {
		global $post;

		if ($post->post_type != 'ims_gallery' || $this->pagenow != 'post-new.php')
			return $messages;

		$this->gallery = $post;
		$post->post_title = __('Gallery ', 'ims') . $post->ID;

		if (empty($this->opts['securegalleries']))
			return $messages;

		$post->post_password = apply_filters('ims_auto_generate_password', wp_generate_password(8));
		return $messages;
	}

	/**
	 * Create unique gallery ID 
	 *
	 * @param unit $length
	 * @return string
	 * @since 2.0.0
	 */
	function unique_id($length = 12) {
		$pass = '';
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		mt_srand(10000000 * (double) microtime());
		for ($i = 0; $i < $length; $i++)
			$pass .= $salt[mt_rand(0, $len - 1)];
		return $pass;
	}

	/**
	 * Display gallery 
	 * information metabox
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function ims_info_box() {

		$blogpath = ( $this->blog_id ) ? "/blogs.dir/{$this->blog_id}" : '';
		$default = array('_ims_visits' => 0, '_ims_sortby' => '', '_ims_tracking' => '', '_ims_order' => '', 'expire' => '', 'ims_expire' => '',
			'_dis_store' => false, '_ims_price_list' => 0, '_to_attach' => $this->opts['attchlink'], '_ims_gallery_id' => $this->unique_id());

		if ($this->pagenow == 'post-new.php') {
			$this->galpath = $blogpath . $this->opts['galleriespath'] . "/gallery-{$this->gallery->ID}";
			$folderfield = '<input type="text" name="_ims_folder_path" id="_ims_folder_path" value="' . esc_attr($this->galpath) . '" />';

			$this->opts['galleryexpire'];
			extract($default);

			if ($this->opts['galleryexpire']) {
				$time = ( current_time('timestamp') ) + ( $this->opts['galleryexpire'] * 86400 );
				$expire = date_i18n($this->dformat, $time);
				$ims_expire = date_i18n('Y-m-d H:i', $time);
			}
		} else {

			if (empty($this->meta['_ims_folder_path'][0])) {
				$this->galpath = $blogpath . $this->opts['galleriespath'] . "/gallery-{$this->gallery->ID}";
			} else {
				$this->disabled = ' disabled="disabled"';
				$this->galpath = esc_attr($this->meta['_ims_folder_path'][0]);
			}

			$instance = array();
			foreach ($this->meta as $key => $val) {
				if (isset($val[0]))
					$instance[$key] = $val[0];
			}

			extract(wp_parse_args($instance, $default));

			if ($this->gallery->post_expire != '0000-00-00 00:00:00') {
				$expire = date_i18n($this->dformat, strtotime($this->gallery->post_expire));
				$ims_expire = date_i18n('Y-m-d H:i', strtotime($this->gallery->post_expire));
			}

			$folderfield = '<input type="text" name="_ims_folder_path" id="_ims_folder_path" value="' . esc_attr($this->galpath) . '"' . $this->disabled . ' />';
		}
		?>
		<table class="ims-table" >
			<tr>
				<td class="short"><label for="_ims_folder_path"><?php _e('Folder path', 'ims') ?></label></td>
				<td class="long"><?php echo $folderfield ?></td>
				<td><label for="gallery_id"><?php _e('Gallery ID', 'ims') ?></label></td>
				<td><input type="text" name="_ims_gallery_id" id="gallery_id" value="<?php echo esc_attr($_ims_gallery_id) ?>"/></td>
			</tr>
			<?php if (empty($this->opts['disablestore'])) { ?>
				<tr>
					<td><label for="_ims_tracking"><?php _e('Tracking Number', 'ims') ?></label></td>
					<td class="long"><input type="text" name="_ims_tracking" id="_ims_tracking" value="<?php echo esc_attr($_ims_tracking) ?>" /></td>
					<td><label for="_ims_price_list"><?php _e('Price List', 'ims') ?></label></td>
					<td>
						<select name="_ims_price_list" id="_ims_price_list" >
							<?php foreach ($this->get_pricelists() as $list) : ?>
								<option value="<?php echo esc_attr($list->ID) ?>" <?php selected($list->ID, $_ims_price_list) ?> ><?php echo esc_html($list->post_title) ?></option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td><label for="sortby"><?php _e('Sort Order', 'ims') ?></label></td>
				<td colspan="3">
					<select name="_ims_sortby" id="sortby">
						<option value="0"><?php _e('Default', 'ims') ?></option>
						<?php foreach ($this->sortby as $val => $label) : ?>
							<option value="<?php echo esc_attr($val) ?>" <?php selected($val, $_ims_sortby) ?>><?php echo esc_html($label) ?></option> 
						<?php endforeach ?>
					</select>
					<select name="_ims_order">
						<option value="0"><?php _e('Default', 'ims') ?></option> 
						<?php foreach ($this->order as $val => $label) : ?>
							<option value="<?php echo esc_attr($val) ?>" <?php selected($val, $_ims_order) ?>><?php echo $label ?></option> 
						<?php endforeach ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="imsexpire" class="date-icon"><?php _e('Expiration Date', 'ims') ?></label></td>
				<td class="long">
					<input type="text" name="imsexpire" id="imsexpire" value="<?php echo $expire ?>" />
					<input type="hidden" name="_ims_expire" id="_ims_expire" value="<?php echo $ims_expire ?>"/>
				</td>
				<td><label for="_ims_visits"><?php _e('Visits', 'ims') ?></label></td>
				<td><input type="text" name="_ims_visits" id="_ims_visits" value="<?php echo esc_attr($_ims_visits) ?>" /></td>
			</tr>
			<tr>
				<td><label for="_dis_store" ><?php _e('Disable Store', 'ims') ?></label></td>
				<td><input type="checkbox" name="_dis_store" id="_dis_store" <?php checked(true, $_dis_store) ?> value="1" /></td>
				<td><label for="_to_attach"><?php _e('Link to attachment', 'ims') ?></label></td>
				<td><input type="checkbox" name="_to_attach" id="_to_attach" <?php checked(true, $_to_attach) ?> value="1" /></td>
			</tr>
		<?php do_action('ims_info_metabox', $this) ?>
		</table>
		<?php
	}

	/**
	 * Display gallery import box
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function ims_import_box() {
		$class = 'ims-box';
		?>
		<ul class="ims-tabs add-menu-item-tabs">
			<?php foreach ($this->import_tabs as $key => $tab) : ?>
				<li class="tabs"><a href="#<?php echo $key ?>"><?php echo $tab ?></a></li>
			<?php endforeach ?>
		</ul>

		<?php
		if (get_user_setting('uploader'))
			$class .= ' html-uploader';
		?>

		<?php foreach ($this->import_tabs as $key => $tab) : ?>
			<div class="<?php echo $class ?>" id="<?php echo $key ?>">
			<?php do_action("ims_{$key}_tab_content", $key, $tab); ?>
			</div>
		<?php endforeach ?>
		<br class="clear" />
		<?php
	}

	/**
	 * Display images
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function ims_images_box() {
		include_once( IMSTORE_ABSPATH . '/admin/galleries.php' );
	}

	/**
	 * Display image tab content
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function upload_images_tab() {
		include_once( IMSTORE_ABSPATH . '/admin/upload-swf.php' );
	}

	/**
	 * set upload params for media uplaod
	 *
	 * @param $plupload_init array
	 * @return array
	 * @since 3.1.0
	 */
	function plupload_init($plupload_init) {
		if (!$this->in_array($this->pagenow, array('post.php', 'post-new.php')))
			return;

		global $post;
		$post_id = ( empty($this->galid) ) ? $post->ID : $this->galid;

		$plupload_init['multipart_params']['cols'] = 6;
		$plupload_init['multipart_params']['post_id'] = $post_id;
		$plupload_init['multipart_params']['folderpath'] = $this->galpath;
		$plupload_init['url'] = IMSTORE_URL . "/admin/upload-img.php";
		$plupload_init['filters'] = array(array('title' => __('Allowed Files'), 'extensions' =>implode(',',$this->exts)));

		return $plupload_init;
	}

	/**
	 * Display Customers metabox
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function customers_metabox() {
		$customers = $this->get_active_customers();

		$this->meta['_ims_customer'] = ( isset($this->meta['_ims_customer'][0]) ) ?
		maybe_unserialize($this->meta['_ims_customer'][0]) : false;

		echo '<div class="taxonomydiv"><div class="tabs-panel">
			<ul class="categorychecklist form-no-clear">';
		if (is_array($this->meta['_ims_customer'])) {
			foreach ($customers as $customer) {
				$checked = ( $this->in_array($customer->ID, $this->meta['_ims_customer']) ) ? ' checked="checked"' : '';
				echo '<li><label>
				<input type="checkbox" name="_ims_customer[]" value="' . esc_attr($customer->ID) . '"' . $checked . ' /> ' .
				$customer->user_login . '</label></li>';
			}
		} else {
			foreach ($customers as $customer) {
				$checked = ( $customer->ID == $this->meta['_ims_customer'] ) ? ' checked="checked"' : '';
				echo '<li><label>
				<input type="checkbox" name="_ims_customer[]" value="' . esc_attr($customer->ID) . '"' . $checked . ' /> ' .
				$customer->user_login . '</label></li>';
			}
		}
		echo'</ul>
		</div></div>';
	}

	/* Import zip tab content
	 *
	 * @return void
	 * @since 3.0.0
	 */

	function upload_zip_tab() {
		echo '<p><label for="zipfile">' . __('Zip file', 'ims') . '<input type="file" name="zipfile" id="zipfile" /></label></p>';
		echo '<p><label for="zipurl">' . __('Or enter zip file URL', 'ims') . '</label><br />';
		echo '<input type="text" name="zipurl" id="zipurl" class="code"/><br />';
		echo '<small>' . sprintf(__("Import a zip file with images from a url. Your server's maximum file size upload is %s. Publish or update gallery to upload images.", 'ims'), '<strong>' . $this->get_max_file_upload(true) . '</strong>') . '</small></p>';
	}

	/* Import folder tab content
	 *
	 * @return void
	 * @since 3.0.0
	 */

	function import_folder_tab() {
		echo '<p><label for="galleryfolder">' . __('Import From Server Path', 'ims') . '</label></p>';
		echo '<p><input type="text" id="galleryfolder" name="galleryfolder" value="' . esc_attr($this->galpath) . '"' . $this->disabled . '/> ';
		echo '<input type="submit" name="scannfolder" id="scannfolder" value="' . esc_attr__('Scan', 'ims') . '" class="button" />';
		echo '<img src="' . admin_url("images/wpspin_light.gif") . '" id="ajax-loading" class="loading" alt="loading"> <br />';
		echo '<small>' . __("Path relative to the wp-content folder.", 'ims') . '</small></p>';
	}

	/* Modify the image upload path
	 *
	 * @return void
	 * @since 3.0.0
	 */

	function change_upload_path($data) {
		global $pagenow;

		$this->pagenow = $pagenow;
		if ($this->pagenow != "upload-img.php")
			return $data;
		$this->galpath = ( $this->galpath ) ? $this->galpath : "/" . trim($_REQUEST['folderpath'], "/");

		$path['error'] = false;
		$path['subdir'] = $this->galpath;
		$path['baseurl'] = $this->content_url;
		$path['basedir'] = $this->content_dir;
		$path['path'] = $this->content_dir . $this->galpath;
		$path['url'] = trim($this->content_url,'/') . '/' . $this->galpath;

		$path = apply_filters('ims_upload_path', $path, $data);
		return $path;
	}

	/* Register screen columns
	 *
	 * @return void
	 * @since 3.0.0
	 */
	function gallery_screen_columns($register = true) {
		$this->columns = array(
			'cb' => '<input type="checkbox">',
			'imthumb' => __('Thumbnail', 'ims'), 'immetadata' => __('Metadata', 'ims'),
			'imtitle' => __('Title/Caption', 'ims'), 'imauthor' => __('Author', 'ims'),
			'imorder' => __('Order', 'ims'), 'imageid' => __('ID', 'ims'),
		);
		if ($register)
			register_column_headers('ims_gallery', $this->columns);
	}

	/**
	 * Detect the maximum file upload size
	 *
	 * @return string
	 * @since 3.0.0
	 */
	function get_max_file_upload($label = false) {

		$u = -1;
		$sizes = array('KB', 'MB', 'GB');

		if (isset($this->max_upload) && $label == false)
			return $this->max_upload = $upload_size_unit;

		$upload_size_unit = wp_max_upload_size();
		for ($u = -1; $upload_size_unit > 1024 && $u < count($sizes) - 1; $u++)
			$upload_size_unit /= 1024;

		if ($label == false)
			return $this->max_upload = $upload_size_unit;

		if ($u < 0)
			$upload_size_unit = $u = 0;
		else
			$upload_size_unit = (int) $upload_size_unit;

		$this->max_upload = $upload_size_unit;
		return $this->max_upload . $sizes[$u];
	}

	/**
	 * Display image row
	 *
	 * @param unit $id
	 * @param array $data
	 * @param array $attch
	 * @return void
	 * @since 3.0.0
	 */
	function display_image_columns($id, $data, $attch = array()) {
		$disabled = ( $this->is_trash ) ? ' disabled="disabled"' : '';

		if (empty($data))
			return;

		if (empty($this->galpath))
			$this->galpath = $this->opts['galleriespath'] . "/" . dirname($data['file']);

		if (empty($this->hidden))
			$this->hidden = (array) get_user_option('manageims_gallerycolumnshidden');

		if (empty($this->imgnonce))
			$this->imgnonce = '&_wpnonce=' . wp_create_nonce("ims_edit_image") . "&TB_iframe=true&height=570";

		if (empty($this->columns))
			$this->gallery_screen_columns(false);
				
		$r = "";
		foreach ($this->columns as $column_id => $column_name) {
			$hide = ( $this->in_array($column_id, $this->hidden) ) ? ' hidden' : '';
			switch ($column_id) {
				case 'cb':
					$r .= '<th class="column-' . $column_id . ' check-column"><input type="checkbox" name="galleries[]" value="' . esc_attr($id) . '" /></th>';
					break;
				case 'imthumb':
					$mr = '<td class="column-' . $column_id . $hide . '">';
					$mr .= '<a href="' . $this->content_url . $this->galpath . "/" . basename($data['file']) . '?" class="thickbox" >';
					$mr .= '<img src="' . $this->content_url . $this->galpath . "/_resized/" . $data['sizes']['mini']['file'] . '" /></a>';
					$mr .= '</td>';
					$r .= apply_filters('ims_image_row_image', $mr, $id, $data, $attch);
					break;
				case 'immetadata':
					$r .= '<td class="column-' . $column_id . $hide . '">';
					$r .= __('Format: ', 'ims') . $attch['post_mime_type'] . '<br />';
					$r .= $data['width'] . ' x ' . $data['height'] . __(' pixels', 'ims') . '<br />';
					$r .= __('Color: ', 'ims') . ( isset($data['color']) ? $data['color'] : $data['image_meta']['color']) . '<br />';
					$r .= '<div class="row-actions" id="media-head-' . $id . '">';
					if ($this->is_trash) {
						$r .= '<a href="#' . $id . '" class="imsdelete">' . __('Delete', 'ims') . '</a> | ';
						$r .= '<a name="publish" href="#' . $id . '" class="imsrestore">' . __('Restore', 'ims') . '</a>';
					} else {
						$r .= '<a href="' . IMSTORE_ADMIN_URL . '/image-edit.php?editimage=' . $id . $this->imgnonce . '" class="thickbox">' .
								__('Edit', 'ims') . '</a> | ';
						$r .= '<a href="#' . $id . '" class="imsupdate">' . __('Update', 'ims') . '</a> | ';
						$r .= '<a name="trash" href="#' . $id . '" class="imstrash">' . __('Trash', 'ims') . '</a>';
					}
					$r .= apply_filters('ims_image_row_actions_metadata', '', $id, $data, $attch);
					$r .= '</div>';
					$r .= '</td>';
					break;
				case 'imtitle':
					$r .= '<td class="column-' . $column_id . $hide . '">';
					$r .= '<input type="text" name="img_title[' . $id . ']" value="' . esc_attr($attch['post_title']) . '" class="inputxl"' . $disabled . '/>';
					$r .= '<textarea name="img_excerpt[' . $id . ']" rows="3" class="inputxl" ' . $disabled . '>' . esc_textarea($attch['post_excerpt']) . '</textarea>';
					$r .= apply_filters('ims_image_row_actions_title', '', $id, $data, $attch);
					$r .= '</td>';
					break;
				case 'imauthor':
					$author = ( $data['image_meta']['credit'] ) ? $data['image_meta']['credit'] : get_user_meta($attch['post_author'], 'nickname', true);
					$r .= '<td class="column-' . $column_id . $hide . '">' . $author . '</td>';
					break;
				case 'imorder':
					$r .= '<td class="column-' . $column_id . $hide . '">';
					$r .= '<input type="text" name="menu_order[' . $id . ']" value="' . esc_attr($attch['menu_order']) . '"' . $disabled . '/>';
					$r .= '</td>';
					break;
				case 'imageid':
					$r .= '<td class="column-' . $column_id . $hide . '">' . sprintf("%05d", $id) . '</td>';
					break;
				default:
					$r .= '<td class="column-' . $column_id . $hide . '">' .
							apply_filters('ims_image_custom_column', $column_id, $id, $data, $attch) . '</td>';
					break;
			}
		}
		
		echo $r;
	}
	
	
	/**
	 * generate single image metadata
	 *
	 * @param $file array: file, url, type, name
	 * @param $parent_id int
	 * @param $show_errors bool 
	 * @return array | bool
	 * @since 3.1.6
	 */
	function generate_ims_metadata( $file, $parent_id, $show_errors = false ){
		global $current_user;
		
		$file = array_merge($file, pathinfo($file['name']));
		$filepath = apply_filters( 'ims_before_read_image', $file['file'], $file );
		
		if(!file_exists($filepath))
			return false;
				
		// Construct the attachment array
		$attachment = array(
			'menu_order' => '',
			'guid' => $file['url'],
			'post_excerpt' => '',
			'post_status' => 'publish',
			'post_type' => 'ims_image',
			'post_parent' => $parent_id,
			'post_mime_type' => $file['type'],
			'post_title' =>trim( substr($file['name'], 0, -(1 + strlen($file['extension'])) ) ),
		);
		
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		if ( $image_meta = @wp_read_image_metadata($filepath) ){
			if ( trim( $image_meta['title'] ) && 
			! is_numeric( sanitize_title( $image_meta['title'] ) ) )
				$attachment['post_title'] = $image_meta['title'];
			if ( trim( $image_meta['caption'] ) )
				$attachment['post_excerpt'] = $image_meta['caption'];
			if ( !trim( $image_meta['credit'] ) )
				$image_meta['credit'] = $current_user->display_name;
		}
		
		$orininfo = getimagesize( $filepath );
		$image_meta['color'] = __( 'Unknown', 'ims');
		
		if( isset($orininfo['channels']) ){
			switch( $orininfo['channels'] ){ 
				case 1:$image_meta['color'] = 'BW'; break;
				case 3:$image_meta['color'] = 'RGB'; break;
				case 4:$image_meta['color'] = 'CMYK'; break;
			}
		} 
		
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		$attach_id = wp_insert_post( $attachment );
		
		if ( is_wp_error($attach_id) && $show_errors){
			echo '<td colspan="'.$cols.'"><div class="error-div">
			<a class="dismiss" href="#">' . __( 'Dismiss') . '</a>
			<strong>' . sprintf(__( '&#8220;%s&#8221; has failed to upload due to an error' ), esc_html( $name ) ) . '</strong><br />' .
			esc_html( $attach_id->get_error_message( ) ) . '</div></td>';
			return false;
		}
		
		if (!is_wp_error($attach_id)) {
			do_action( 'ims_before_attachment_metadata', $attach_id, $filepath, $file );
			
			$metadata = wp_generate_attachment_metadata($attach_id, $filepath);
			$metadata['image_meta'] = $image_meta;
			
			if( update_post_meta( $attach_id, '_wp_attachment_metadata', $metadata ) && $show_errors){
				echo apply_filters( "ims_async_upload", $attach_id, $metadata, $attachment );
				if( !get_post_meta( $attach_id, '_ims_folder_path' ) )
					update_post_meta( $attach_id, '_ims_folder_path', "/". trim( $_REQUEST['folderpath'] , "/" ) );
			}elseif( $show_errors )  echo 'error';
			
			return $metadata;
		}
		
		return false;
	}

	/**
	 * Save gallery data and images
	 *
	 * @param unit $postid
	 * @param array $post
	 * @since 2.0.0
	 * return unit|string
	 */
	function save_post($postid, $post) {
		if (!current_user_can('ims_add_galleries') || $post->post_type != 'ims_gallery' ||
		$post->post_status == 'auto-draft' || empty($_POST['post_ID']))
			return $postid;

		$scan = false;
		$archive = false;
		$download_file = false;

		//check_admin_referer('update-' . $post->post_type . '_' . $postid);

		$this->galpath = ( empty($_POST['_ims_folder_path']) ) ?
		get_post_meta($postid, '_ims_folder_path', true) : "/" . trim($_POST['_ims_folder_path'], "/");

		if (isset($_POST['scannfolder']) && !empty($_POST['galleryfolder'])) {
			$this->galpath = "/" . trim($_POST['galleryfolder'], "/");
			update_post_meta($postid, '_ims_folder_path', $this->galpath);
		}

		if (empty($this->galpath))
			return $postid;

		global $wpdb;
		$fullpath = $this->content_dir . "/{$this->galpath}/";

		//upload remote zip
		if (!empty($_POST['zipurl'])) {
			
			if (!preg_match('/^http(s)?:\/\//i', $_POST['zipurl']))
				return $this->error = 2;
			if (!preg_match('/(zip)$/i', $_POST['zipurl']))
				return $this->error = 3;

			$filename = basename($_POST['zipurl']);
			$download_file = download_url($_POST['zipurl']);
			
			if (is_wp_error($download_file))
				return $this->error = 1;

		//upload zip
		}elseif (!empty($_FILES['zipfile']['name'])) {
			
			$filename = $_FILES['zipfile']['name'];
			if (!preg_match('/(zip)$/i', $_FILES['zipfile']['name']))
				return $this->error = 3;
			if ($_FILES['zipfile']['error'] != '0' || $_FILES['zipfile']['size'] == 0)
				return $this->error = 5;
			$download_file = $_FILES['zipfile']['tmp_name'];
		}

		//scan folder
		if (!empty($_POST['scannfolder']) && !empty($this->galpath)) {
			$scan = true;
			
			//memory limit
			set_time_limit( 0 );
			ini_set( 'memory_limit', $this->get_memory_limit());
			
			//delete old data
			$wpdb->query(
				"DELETE p,pm FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm 
				ON (p.ID = pm.post_id) WHERE post_parent IN( $postid )"
			);

			if ($dh = @opendir($fullpath)) {
				$x = 0;
				while (false !== ($obj = readdir($dh))) {
					if ($obj{0} == '.' || !preg_match('/('.implode('|',$this->exts).')$/i', $obj))
						continue;
					$archive[$x]['status'] = 'ok';
					$archive[$x]['filename'] = $obj;
					$x++;
				}
				@closedir($dh);
			}
		}

		//single file upload
		if (isset($_POST['html-upload']) && empty($_FILES['async-upload']['error'])) {
			$archive[] = array(
				'status' => 'ok',
				'filename' => $_FILES['async-upload']['name'],
				'content' => file_get_contents($_FILES['async-upload']['tmp_name'])
			);
			@unlink($_FILES['async-upload']['tmp_name']);
		}

		//generate image information
		if ($download_file || $archive) {

			if ($download_file) {
				include_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
				$PclZip = new PclZip($download_file);
				if (false == ( $archive = $PclZip->extract(PCLZIP_OPT_EXTRACT_AS_STRING)))
					return $this->error = 3;
			}

			//delete temp file
			@unlink($download_file);
			
			global $pagenow, $current_user;
			$this->pagenow = $pagenow = 'upload-img.php';

			foreach ($archive as $file) {

				if ('__MACOSX/' === substr($file['filename'], 0, 9) || (isset($file['folder']) && $file['folder'] == true )
				|| !preg_match('/.('.implode('|',$this->exts).')$/i', $file['filename']) || $file['status'] != 'ok')
					continue;

				$filename = basename($file['filename']);
				
				if (preg_match('(^._)', $filename))
					continue;

				if (!file_exists($fullpath))
					@mkdir($fullpath, 0755, true);

				$filepath = $fullpath . $filename;
				if (!$scan) {
					file_put_contents($filepath, $file['content']);
					$filename = wp_unique_filename($fullpath, $filename);
				}
				
				if (file_exists($filepath)) {
					$filetype = wp_check_filetype($filename);
					$filedata = array(
						'file'=> $filepath ,
						'name' =>$filename,
						'type' => $filetype['type'],
						'url'=> str_replace($this->content_dir, $this->content_url, $filepath),
					);
					$this->generate_ims_metadata($filedata, $postid);
				}
			}
		}

		//save gallery settings
		if (empty($_POST['doactions']) && empty($_POST['scannfolder'])) {

			update_post_meta($postid, '_ims_folder_path', $this->galpath);
			$metakeys = array('_ims_order', '_ims_customer', '_ims_sortby', '_ims_visits', '_to_attach',
				'_ims_tracking', '_ims_downloads', '_ims_price_list', '_ims_gallery_id', '_dis_store',);

			foreach ($metakeys as $key) {
				$val = ( empty($_POST[$key]) ) ? '' : $_POST[$key];
				update_post_meta($postid, $key, $val);
			}

			$expire = ( isset($_POST['_ims_expire']) && !empty($_POST['imsexpire']) ) ? $_POST['_ims_expire'] : 0;
			$wpdb->update($wpdb->posts, array('post_expire' => $expire), array('ID' => $postid), array("%s"), array('%d'));

			//update image information
			if (isset($_POST['img_title'])) {
				foreach ((array) $_POST['img_title'] as $key => $val) {
					$img['ID'] = $key;
					$img['post_name'] = $_POST['img_title'][$key];
					$img['post_title'] = $_POST['img_title'][$key];
					$img['menu_order'] = $_POST['menu_order'][$key];
					$img['post_excerpt'] = $_POST['img_excerpt'][$key];
					wp_update_post($img);
				}
			}
		}

		//bulk actions 
		if (isset($_POST['doactions']) && !empty($_POST['galleries'])) {
			if (empty($_POST['actions']))
				return;

			if ($_POST['actions'] == 'delete') {
				foreach ((array) $_POST['galleries'] as $id) {
					if ($this->opts['deletefiles']) {
						$data = get_post_meta($id, '_wp_attachment_metadata', true);
						if ($data && is_array($data['sizes'])) {
							foreach ($data['sizes'] as $size) {
								if (file_exists($fullpath . "_resized/" . $size['file']))
									@unlink($fullpath . "_resized/" . $size['file']);
								else
									@unlink($fullpath . $size['file']);
							}
							@unlink($fullpath . basename($data['file']));
						}
					}
					wp_delete_post($id, true);
				}
			}else {
				$wpdb->query($wpdb->prepare(
					"UPDATE $wpdb->posts SET post_status = %s WHERE ID IN( " .
					$wpdb->escape(implode(', ', $_POST['galleries'])) . " )", $_POST['actions']
				));
			}
		}
	}

}