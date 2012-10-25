<?php

require_once 'class.base.php';

/**
 * Fields - Base class
 * Core properties/methods for Content Type derivative classes
 * @package Simple Lightbox
 * @subpackage Fields
 * @author SM
 */
class SLB_Field_Base extends SLB_Base {
	/**
	 * @var string Unique name
	 */
	var $id = '';
	
	/**
	 * ID formatting options
	 * Merged with defaults during initialization
	 * @see $id_formats_default
	 * @var array
	 */
	var $id_formats = null;
	
	/**
	 * Default ID Formatting options
	 * Structure:
	 * > Key (string): Format name
	 * > Val (array): Options
	 * @var array
	 */
	var $id_formats_default = array(
		'attr_id' => array(
			'wrap'			=> array('open' => '_', 'segment_open' => '_'),
			'prefix'		=> array('get_container', 'get_id', 'add_prefix'),
			'recursive'		=> true
		),
		'attr_name' => array(
			'wrap'		=> array('open' => '[', 'close' => ']', 'segment_open' => '[', 'segment_close' => ']'),
			'recursive'	=> true,
			'prefix'	=> array('get_container', 'get_id', 'add_prefix')
		)
	);

	/**
	 * Reference to parent object that current instance inherits from
	 * @var object
	 */
	var $parent = null;

	/**
	 * Title
	 * @var string
	 */
	var $title = '';

	/**
	 * @var string Short description
	 */
	var $description = '';

	/**
	 * @var array Object Properties
	 */
	var $properties = array();
	
	/**
	 * Initialization properties
	 * @var array
	 */
	var $properties_init = null;
	
	/**
	 * Structure: Property names stored as keys in group
	 * Root
	 *  -> Group Name
	 *    -> Property Name => Null
	 * Reason: Faster searching over large arrays
	 * @var array Groupings of Properties
	 */
	var $property_groups = array();
	
	/**
	 * Keys to filter out of properties array before setting properties
	 * @var array
	 */
	var $property_filter = array('group');
	
	/**
	 * Data for object
	 * May also contain data for nested objects
	 * @var mixed
	 */
	var $data = null;
	
	/**
	 * Whether data has been fetched or not
	 * @var bool
	 */
	var $data_fetched = false;
	
	/**
	 * @var array Script resources to include for object
	 */
	var $scripts = array();

	/**
	 * @var array CSS style resources to include for object
	 */
	var $styles = array();

	/**
	 * Hooks (Filters/Actions) for object
	 * @var array
	 */
	var $hooks = array();
	
	/**
	 * Mapping of child properties to parent members
	 * Allows more flexibility when creating new instances of child objects using property arrays
	 * Associative array structure:
	 *  > Key: Child property to map FROM
	 *  > Val: Parent property to map TO
	 * @var array
	 */
	var $map = null;
	
	/**
	 * Legacy Constructor
	 */
	function SLB_Field_Base($id = '', $parent = null) {
		$args = func_get_args();
		call_user_func_array(array($this, '__construct'), $args);
	}

	/**
	 * Constructor
	 */
	function __construct($id = '', $parent = null) {
		parent::__construct();
		//Normalize Properties
		$args = func_get_args();
		if ( func_num_args() > 1 && empty($parent) ) {
			unset($args[1]);
			$args = array_values($args);
		}
		$properties = $this->make_properties($this->util->func_get_options($args), array('id' => $id, 'parent' => $parent));
		//Remove empty variables
		if ( empty($properties['parent']) )
			unset($properties['parent']);
		//Save init properties
		$this->properties_init = $properties;
		//Set Properties
		$this->set_properties($properties);
	}

	/* Getters/Setters */
	
	/**
	 * Checks if the specified path exists in the object
	 * @param array $path Path to check for
	 * @return bool TRUE if path exists in object, FALSE otherwise
	 */
	function path_isset($path = '') {
		//Stop execution if no path is supplied
		if ( empty($path) )
			return false;
		$args = func_get_args();
		$path = $this->util->build_path($args);
		$item =& $this;
		//Iterate over path and check if each level exists before moving on to the next
		for ($x = 0; $x < count($path); $x++) {
			if ( $this->util->property_exists($item, $path[$x]) ) {
				//Set $item as reference to next level in path for next iteration
				$item =& $this->util->get_property($item, $path[$x]);
				//$item =& $item[ $path[$x] ];
			} else {
				return false;
			}
		}
		return true; 
	}

	/**
	 * Retrieves a value from object using a specified path
	 * Checks to make sure path exists in object before retrieving value
	 * @param array $path Path to retrieve value from. Each item in array is a deeper dimension
	 * @return mixed Value at specified path
	 */
	function &get_path_value($path = '') {
		$ret = '';
		$path = $this->util->build_path(func_get_args());
		if ( $this->path_isset($path) ) {
			$ret =& $this;
			for ($x = 0; $x < count($path); $x++) {
				if ( 0 == $x )
					$ret =& $ret->{ $path[$x] };
				else
					$ret =& $ret[ $path[$x] ];
			}
		}
		return $ret;
	}

	/**
	 * Search for specified member value in field type ancestors
	 * @param string $member Name of object member to search (e.g. properties, layout, etc.)
	 * @param string $name Value to retrieve from member
	 * @return mixed Member value if found (Default: empty string)
	 */
	function get_parent_value($member, $name = '', $default = '') {
		$parent =& $this->get_parent();
		return $this->get_object_value($parent, $member, $name, $default, 'parent');
	}

	/**
	 * Retrieves specified member value
	 * Handles inherited values
	 * Merging corresponding parents if value is an array (e.g. for property groups)
	 * @param string|array $member Member to search.  May also contain a path to the desired member
	 * @param string $name Value to retrieve from member
	 * @param mixed $default Default value if no value found (Default: empty string)
	 * @param string $dir Direction to move through hierarchy to find value
	 * Possible Values:
	 *  parent (default) 	- Search through field parents
	 *  current				- Do not search through connected objects
	 *  container			- Search through field containers
	 *  caller				- Search through field callers
	 * @return mixed Specified member value
	 * @todo Return reference
	 */
	function &get_member_value($member, $name = '', $default = '', $dir = 'parent') {
		//Check if path to member is supplied
		$path = array();
		if ( is_array($member) && isset($member['tag']) ) {
			if ( isset($member['attributes']['ref_base']) ) {
				if ( 'root' != $member['attributes']['ref_base'] )
					$path[] = $member['attributes']['ref_base'];
			} else {
				$path[] = 'properties';
			}

			$path[] = $member['tag'];
		} else {
			$path = $member;
		}

		$path = $this->util->build_path($path, $name);
		//Set defaults and prepare data
		$val = $default;
		$inherit = false;
		$inherit_tag = '{inherit}';

		/* Determine whether the value must be retrieved from a parent/container object
		 * Conditions:
		 * > Path does not exist in current field
		 * > Path exists and is not an object, but at least one of the following is true:
		 *   > Value at path is an array (e.g. properties, elements, etc. array)
		 *     > Parent/container values should be merged with retrieved array
		 *   > Value at path is a string that inherits from another field
		 *     > Value from other field will be retrieved and will replace inheritance placeholder in retrieved value
		 */

		$deeper = false;

		if ( !$this->path_isset($path) )
			$deeper = true;
		else {
			$val = $this->get_path_value($path);
			if ( !is_object($val) && ( is_array($val) || ($inherit = strpos($val, $inherit_tag)) !== false ) )
				$deeper = true;
			else
				$deeper = false;
		}
		if ( $deeper && 'current' != $dir ) {
			//Get Parent value (recursive)
			$ex_val = ( 'parent' != $dir ) ? $this->get_container_value($member, $name, $default) : $this->get_parent_value($member, $name, $default);
			//Handle inheritance
			if ( is_array($val) ) {
				//Combine Arrays
				if ( is_array($ex_val) )
					$val = array_merge($ex_val, $val);
			} elseif ( $inherit !== false ) {
				//Replace placeholder with inherited string
				$val = str_replace($inherit_tag, $ex_val, $val);
			} else {
				//Default: Set parent value as value
				$val = $ex_val;
			}
		}

		return $val;
	}

	/**
	 * Search for specified member value in an object
	 * @param object $object Reference to object to retrieve value from
	 * @param string $member Name of object member to search (e.g. properties, layout, etc.)
	 * @param string $name (optional) Value to retrieve from member
	 * @param mixed $default (optional) Default value to use if no value found (Default: empty string)
	 * @param string $dir Direction to move through hierarchy to find value @see SLB_Field_Type::get_member_value() for possible values
	 * @return mixed Member value if found (Default: $default)
	 */
	function get_object_value(&$object, $member, $name = '', $default = '', $dir = 'parent') {
		$ret = $default;
		if ( is_object($object) && method_exists($object, 'get_member_value') )
			$ret = $object->get_member_value($member, $name, $default, $dir);
		return $ret;
	}
	
	/**
	 * Set item ID
	 * @param string $id Unique item ID
	 */
	function set_id($id) {
		if ( empty($id) || !is_string($id) )
			return false;
		$this->id = trim($id);
	}
	
	/**
	 * Retrieves field ID
	 * @param array|string $options (optional) Options or ID of format to use
	 * @return string item ID
	 */
	function get_id($options = array()) {
		$item_id = trim($this->id);
		
		$formats = $this->get_id_formats();
		
		//Setup options
		$options_default = array(
			'format'			=> null,
			'wrap'				=> array('open' => '', 'close' => '', 'segment_open' => '', 'segment_close' => ''),
			'segments_pre'		=> null,
			'prefix'			=> '',
			'recursive'			=> false
		);
		
		//Load options based on format
		if ( !is_array($options) )
			$options = array('format' => $options);
		if ( is_string($options['format']) && isset($formats[$options['format']]) )
			$options_default = wp_parse_args($formats[$options['format']], $options_default);
		else
			unset($options['format']);
		$options = wp_parse_args($options, $options_default);
		extract($options);

		//Validate options
		$wrap = wp_parse_args($wrap, $options_default['wrap']);
		
		if ( !is_array($segments_pre) )
			$segments_pre = array($segments_pre);
		$segments_pre = array_reverse($segments_pre);
		
		//Format ID based on options

		$item_id = array($item_id);

		//Add parent objects to ID 
		if ( !!$recursive ) {
			//Create array of ID components
			$c = $this->get_caller();
			while ( !!$c ) {
				//Add ID of current caller to array
				if ( method_exists($c, 'get_id') && ( $itemp = $c->get_id() ) && !empty($itemp) )
					$item_id = $itemp;
				//Get parent object
				$c = ( method_exists($c, 'get_caller') ) ? $c->get_caller() : null;
				$itemp = '';
			}
			unset($c);
		}
		
		//Additional segments (Pre)
		foreach ( $segments_pre as $seg ) {
			if ( is_null($seg) )
				continue;
			if ( is_object($seg) )
				$seg = (array)$seg;
			if ( is_array($seg) )
				$item_id = array_merge($item_id, array_reverse($seg));
			elseif ( '' != strval($seg) )
				$item_id[] = strval($seg);
		}
		
		//Prefix
		if ( is_array($prefix) ) {
			//Array is sequence of instance methods to call on object
			//Last array member can be an array of parameters to pass to methods
			$count = count($prefix);
			$args = ( $count > 1 && is_array($prefix[$count - 1]) ) ? array_pop($prefix) : array();
			$p = $this;
			$val = '';
			//Iterate through methods
			foreach ( $prefix as $m ) {
				//Build callback
				$m = $this->util->m($p, $m);
				//Call callback 
				if ( is_callable($m) )
					$val = call_user_func_array($m, $args);
				//Process returned value
				if ( is_object($val) )
					$p = $val; //Use returned object in next round
				else
					array_unshift($args, $val); //Pass returned value as parameter to next method on using current object
			}
			$prefix = $val;
			unset($p, $val);
		}
		if ( is_numeric($prefix) )
			$prefix = strval($prefix);
		if ( empty($prefix) || !is_string($prefix) )
			$prefix = ''; 

		//Convert array to string
		$item_id = $prefix . $wrap['open'] . implode($wrap['segment_close'] . $wrap['segment_open'], array_reverse($item_id)) . $wrap['close'];
		return $item_id;
	}
	
	/**
	 * Retrieve ID formatting options for class
	 * Format options arrays are merged together and saved to $id_formats
	 * @uses $id_formats
	 * @uses $id_formats_default
	 * @return array ID Formatting options
	 */
	function &get_id_formats() {
		if ( is_null($this->id_formats) ) {
			$this->id_formats = wp_parse_args($this->id_formats, $this->id_formats_default);
		}
		return $this->id_formats;
	}

	/**
	 * Retrieve value from data member
	 * @param string $context Context to format data for
	 * @param bool $top (optional) Whether to traverse through the field hierarchy to get data for field (Default: TRUE)
	 * @return mixed Value at specified path
	 */
	function get_data($context = '', $top = true) {
		$opt_d = array('context' => '', 'top' => true);
		$args = func_get_args();
		$a = false;
		if ( count($args) == 1 && is_array($args[0]) && !empty($args[0]) ) {
			$a = true;
			$args = wp_parse_args($args[0], $opt_d);
			extract($args);
		}
		
		if ( is_string($top) ) {
			if ( 'false' == $top )
				$top = false;
			elseif ( 'true' == $top )
				$top = true;
			elseif ( is_numeric($top) )
				$top = intval($top);
		}
		$top = !!$top;
		$obj =& $this;
		$obj_path = array($this);
		$path = array();
		if ( $top ) {
			//Iterate through hiearchy to get top-most object
			while ( !empty($obj) ) {
				$new = null;
				//Try to get caller first
				if ( method_exists($obj, 'get_caller') ) {
					$checked = true;
					$new =& $obj->get_caller();
				}
				//Try to get container if no caller found
				if ( empty($new) && method_exists($obj, 'get_container') ) {
					$checked = true;
					$new =& $obj->get_container();
					//Load data
					if ( method_exists($new, 'load_data') ) {
						$new->load_data();
					}
				}
	
				$obj =& $new;
				unset($new);
				//Stop iteration
				if ( !empty($obj) ) {
					//Add object to path if it is valid
					$obj_path[] =& $obj;
				}
			}
			unset($obj);
		}

		//Check each object (starting with top-most) for matching data for current field

		//Reverse array
		$obj_path = array_reverse($obj_path);
		//Build path for data location
		foreach ( $obj_path as $obj ) {
			if ( method_exists($obj, 'get_id') )
				$path[] = $obj->get_id();
		}
		//Iterate through objects
		while ( !empty($obj_path) ) {
			//Get next object
			$obj =& array_shift($obj_path);
			//Shorten path
			array_shift($path);
			//Check for value in object and stop iteration if matching data found
			$val = $this->get_object_value($obj, 'data', $path, null, 'current');
			if ( !is_null($val) ) {
				break;
			}
		}
		return $this->format($val, $context);
	}

	/**
	 * Sets value in data member
	 * Sets value to data member itself by default
	 * @param mixed $value Value to set
	 * @param string|array $name Name of value to set (Can also be path to value)
	 */
	function set_data($value, $name = '') {
		$ref =& $this->get_path_value('data', $name);
		$ref = $value;
	}
	
	/**
	 * Sets parent object of current instance
	 * Parent objects must be the same object type as current instance
	 * @uses SLB to get field type definition
	 * @uses SLB_Fields::has() to check if field type exists
	 * @uses SLB_Fields::get() to retrieve field type object reference
	 * @param string|object $parent Parent ID or reference
	 */
	function set_parent($parent = null) {
		//Stop processing if parent empty
		if ( empty($parent) && !is_string($this->parent) )
			return false;
		//Parent passed as object reference wrapped in array
		if ( is_array($parent) && is_object($parent[0]) )
			$parent =& $parent[0];
		
		//No parent set but parent ID (previously) set in object
		if ( empty($parent) && is_string($this->parent) )
			$parent = $this->parent;
		
		//Retrieve reference object if ID was supplied
		if ( is_string($parent) ) {
			$parent = trim($parent);
			//Get parent object reference
			/**
			 * @var SLB
			 */
			$b =& $this->get_base();
			if ( $b && $b->fields->has($parent) ) {
				$parent =& $b->fields->get($parent);
			}
		}
		
		//Set parent value on object
		if ( is_string($parent) || is_object($parent) )
			$this->parent =& $parent;
	}

	/**
	 * Retrieve field type parent
	 * @return SLB_Field_Type Reference to parent field
	 */
	function &get_parent() {
		return $this->parent;
	}

	/**
	 * Set object title
	 * @param string $title Title for object
	 * @param string $plural Plural form of title
	 */
	function set_title($title = '') {
		$this->title = strip_tags(trim($title));
	}

	/**
	 * Retrieve object title
	 */
	function get_title() {
		return $this->get_member_value('title', '','', 'current');
	}

	/**
	 * Set object description
	 * @param string $description Description for object
	 */
	function set_description($description = '') {
		$this->description = strip_tags(trim($description));
	}

	/**
	 * Retrieve object description
	 * @return string Object description
	 */
	function get_description() {
		$dir = 'current';
		return $this->get_member_value('description', '','', $dir);
		return $desc;
	}
	
	/**
	 * Sets multiple properties on field type at once
	 * @param array $properties Properties. Each element is an array containing the arguments to set a new property
	 * @return boolean TRUE if successful, FALSE otherwise
	 */
	function set_properties($properties) {
		if ( !is_array($properties) )
			return false;
		
		//Set Member properties
		foreach ( $properties as $prop => $val ) {
			if ( ( $m = 'set_' . $prop ) && method_exists($this, $m) ) {
				$this->{$m}($val);
				//Remove member property from array
				unset($properties[$prop]);
			}
		}
		
		//Filter properties
		$properties = $this->filter_properties($properties);
		
		//Set additional instance properties
		foreach ( $properties as $name => $val) {
			$this->set_property($name, $val);
		}
	}
	
	/**
	 * Remap properties based on $map
	 * @uses $map For determine how child properties should map to parent properties
	 * @uses SLB_Utlities::array_remap() to perform array remapping
	 * @param array $properties Associative array of properties
	 * @return array Remapped properties
	 */
	function remap_properties($properties) {
		//Return remapped properties
		return $this->util->array_remap($properties, $this->map);
	}
	
	/**
	 * Build properties array
	 * Accepts a variable number of additional arrays of default properties
	 * that will be merged in order from last to first
	 * (e.g. first array overwrites duplicate members in last)
	 * @uses SLB_Field_Base::remap() to remap properties members if necessary
	 * @param array $props Instance properties
	 * @param array $defaults Default properties
	 * @return array Normalized properties
	 */
	function make_properties($props, $defaults = array()) {
		$args = func_get_args();
		$args = array_reverse($args);
		$props = array();
		foreach ( $args as $arg ) {
			$props = wp_parse_args($arg, $props);
		}
		return $this->remap_properties($props);
	}
	
	/**
	 * Filter property members
	 * @uses $property_filter to remove define members to remove from $properties
	 * @param array $props Properties
	 * @return array Filtered properties
	 */
	function filter_properties($props = array()) {
		return $this->util->array_filter_keys($props, $this->property_filter);
	}
	
	/**
	 * Add/Set a property on the field definition
	 * @param string $name Name of property
	 * @param mixed $value Default value for property
	 * @param string|array $group Group(s) property belongs to
	 * @return boolean TRUE if property is successfully added to field type, FALSE otherwise
	 */
	function set_property($name, $value = '', $group = null) {
		//Do not add if property name is not a string
		if ( !is_string($name) )
			return false;
		//Create property array
		$prop_arr = array();
		$prop_arr['value'] = $value;
		//Add to properties array
		$this->properties[$name] = $value;
		//Add property to specified groups
		if ( !empty($group) ) {
			$this->set_group_property($group, $name);
		}
		return true;
	}

	/**
	 * Retreives property from field type
	 * @param string $name Name of property to retrieve
	 * @return mixed Specified Property if exists (Default: Empty string)
	 */
	function get_property($name) {
		$val = $this->get_member_value('properties', $name);
		return $val;
	}
	
	/**
	 * Removes a property from item
	 * @param string $name Property ID
	 */
	function remove_property($name) {
		//Remove property
		if ( isset($this->properties[$name]) )
			unset($this->properties[$name]);
		//Remove from group
		foreach ( array_keys($this->property_groups) as $g ) {
			if ( isset($this->property_groups[$g][$name]) ) {
				unset($this->property_groups[$g][$name]);
				break;
			}
		}
	}

	/**
	 * Adds Specified Property to a Group
	 * @param string|array $group Group(s) to add property to
	 * @param string $property Property to add to group
	 */
	function set_group_property($group, $property) {
		if ( is_string($group) && isset($this->property_groups[$group][$property]) )
			return;
		if ( !is_array($group) ) {
			$group = array($group);
		}

		foreach ($group as $g) {
			$g = trim($g);
			//Initialize group if it doesn't already exist
			if ( !isset($this->property_groups[$g]) )
				$this->property_groups[$g] = array();

			//Add property to group
			$this->property_groups[$g][$property] = null;
		}
	}

	/**
	 * Retrieve property group
	 * @param string $group Group to retrieve
	 * @return array Array of properties in specified group
	 */
	function get_group($group) {
		return $this->get_member_value('property_groups', $group, array());
	}
	
	/**
	 * Save field data
	 * Child classes will define their own
	 * functionality for this method
	 * @return bool TRUE if save was successful (FALSE otherwise)
	 */
	function save() {
		return true;
	}
	
	/*-** Hooks **-*/
	
	/**
	 * Retrieve hooks added to object
	 * @return array Hooks
	 */
	function get_hooks() {
		return $this->get_member_value('hooks', '', array());
	}
	
	/**
	 * Add hook for object
	 * @see add_filter() for parameter defaults
	 * @param $tag
	 * @param $function_to_add
	 * @param $priority
	 * @param $accepted_args
	 */
	function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		//Create new array for tag (if not already set)
		if ( !isset($this->hooks[$tag]) )
			$this->hooks[$tag] = array();
		//Build Unique ID
		if ( is_string($function_to_add) )
			$id = $function_to_add;
		elseif ( is_array($function_to_add) && !empty($function_to_add) )
			$id = strval($function_to_add[count($function_to_add) - 1]);
		else
			$id = 'function_' . ( count($this->hooks[$tag]) + 1 ); 
		//Add hook
		$this->hooks[$tag][$id] = func_get_args();
	}
	
	/**
	 * Convenience method for adding an action for object
	 * @see add_filter() for parameter defaults
	 * @param $tag
	 * @param $function_to_add
	 * @param $priority
	 * @param $accepted_args
	 */
	function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		$this->add_hook($tag, $function_to_add, $priority, $accepted_args);
	}
	
	/**
	 * Convenience method for adding a filter for object
	 * @see add_filter() for parameter defaults
	 * @param $tag
	 * @param $function_to_add
	 * @param $priority
	 * @param $accepted_args
	 */
	function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
		$this->add_hook($tag, $function_to_add, $priority, $accepted_args);
	}
	
	/*-** Dependencies **-*/
	
	/**
	 * Adds dependency to object
	 * @param string $type Type of dependency to add (script, style)
	 * @param array|string $context When dependency will be added (@see SLB_Utilities::get_action() for possible contexts)
	 * @see wp_enqueue_script for the following of the parameters
	 * @param $handle
	 * @param $src
	 * @param $deps
	 * @param $ver
	 * @param $ex
	 */
	function add_dependency($type, $context, $handle, $src = false, $deps = array(), $ver = false, $ex = false) {
		$args = func_get_args();
		//Remove type/context from arguments
		$args = array_slice($args, 2);

		//Set context
		if ( !is_array($context) ) {
			//Wrap single contexts in an array
			if ( is_string($context) )
				$context = array($context);
			else 
				$context = array();
		}
		//Add file to instance property
		if ( isset($this->{$type}) && is_array($this->{$type}) )
			$this->{$type}[$handle] = array('context' => $context, 'params' => $args);
	}
	
	/**
	 * Add script to object to be added in specified contexts
	 * @param array|string $context Array of contexts to add script to page
	 * @see wp_enqueue_script for the following of the parameters
	 * @param $handle
	 * @param $src
	 * @param $deps
	 * @param $ver
	 * @param $in_footer
	 */
	function add_script( $context, $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
		$args = func_get_args();
		//Add file type to front of arguments array
		array_unshift($args, 'scripts');
		call_user_func_array($this->m('add_dependency'), $args);
	}

	/**
	 * Retrieve script dependencies for object
	 * @return array Script dependencies
	 */
	function get_scripts() {
		return $this->get_member_value('scripts', '', array());
	}
	
	/**
	 * Add style to object to be added in specified contexts
	 * @param array|string $context Array of contexts to add style to page
	 * @see wp_enqueue_style for the following of the parameters
	 * @param $handle
	 * @param $src
	 * @param $deps
	 * @param $ver
	 * @param $in_footer
	 */
	function add_style( $handle, $src = false, $deps = array(), $ver = false, $media = false ) {
		$args = func_get_args();
		array_unshift($args, 'styles');
		call_user_func_array($this->m('add_dependency'), $args);
	}

	/**
	 * Retrieve Style dependencies for object
	 * @return array Style dependencies
	 */
	function get_styles() {
		return $this->get_member_value('styles', '', array());
	}
	
	/* Helpers */
	
	/**
	 * Format value based on specified context
	 * @param mixed $value Value to format
	 * @param string $context Current context
	 * @return mixed Formatted value
	 */
	function format($value, $context = '') {
		$handler = 'format_' . trim(strval($context));
		//Only process if context is valid and has a handler
		if ( !empty($context) && method_exists($this, $handler) ) {
			//Pass value to handler
			$value = $this->{$handler}($value, $context);
		}
		//Return formatted value
		return $value;
	}
	
	/**
	 * Format value for output in form field
	 * @param mixed $value Value to format
	 * @return mixed Formatted value
	 */
	function format_form($value) {
		if ( is_string($value) )
			$value = htmlentities($value);
		return $value;
	}
}

/**
 * Field Types
 * Stores properties for a specific field
 * @package Simple Lightbox
 * @subpackage Fields
 * @author SM
 */
class SLB_Field_Type extends SLB_Field_Base {
	/* Properties */

	/**
	 * @var array Array of Field types that make up current Field type
	 */
	var $elements = array();

	/**
	 * @var array Field type layouts
	 */
	var $layout = array();

	/**
	 * @var SLB_Field_Type Parent field type (reference)
	 */
	var $parent = null;

	/**
	 * Object that field is in
	 * @var SLB_Field|SLB_Field_Type|SLB_Field_Collection
	 */
	var $container = null;

	/**
	 * Object that called field
	 * Used to determine field hierarchy/nesting
	 * @var SLB_Field|SLB_Field_Type|SLB_Field_Collection
	 */
	var $caller = null;

	/**
	 * Legacy Constructor
	 */
	function SLB_Field_Type($id = '', $parent = null) {
		$args = func_get_args();
		call_user_func_array(array($this, '__construct'), $args);
	}
	
	function __construct($id = '', $parent = null) {
		parent::__construct($id, $parent);
	}

	/* Getters/Setters */

	/**
	 * Search for specified member value in field's container object (if exists)
	 * @param string $member Name of object member to search (e.g. properties, layout, etc.)
	 * @param string $name Value to retrieve from member
	 * @return mixed Member value if found (Default: empty string)
	 */
	function get_container_value($member, $name = '', $default = '') {
		$container =& $this->get_container();
		return $this->get_object_value($container, $member, $name, $default, 'container');
	}

	/**
	 * Search for specified member value in field's container object (if exists)
	 * @param string $member Name of object member to search (e.g. properties, layout, etc.)
	 * @param string $name Value to retrieve from member
	 * @return mixed Member value if found (Default: empty string)
	 */
	function get_caller_value($member, $name = '', $default = '') {
		$caller =& $this->get_caller();
		return $this->get_object_value($caller, $member, $name, $default, 'caller');
	}

	/**
	 * Sets reference to container object of current field
	 * Reference is cleared if no valid object is passed to method
	 * @param object $container
	 */
	function set_container(&$container) {
		if ( !empty($container) && is_object($container) ) {
			//Set as param as container for current field
			$this->container =& $container;
		} else {
			//Clear container member if argument is invalid
			$this->clear_container();
		}
	}

	/**
	 * Clears reference to container object of current field
	 */
	function clear_container() {
		$this->container = null;
	}

	/**
	 * Retrieves reference to container object of current field
	 * @return object Reference to container object
	 */
	function &get_container() {
		$ret = null;
		if ( $this->has_container() )
			$ret =& $this->container;
		return $ret;
	}

	/**
	 * Checks if field has a container reference
	 * @return bool TRUE if field is contained, FALSE otherwise
	 */
	function has_container() {
		return !empty($this->container);
	}

	/**
	 * Sets reference to calling object of current field
	 * Any existing reference is cleared if no valid object is passed to method
	 * @param object $caller Calling object
	 */
	function set_caller(&$caller) {
		if ( !empty($caller) && is_object($caller) )
			$this->caller =& $caller;
		else
			$this->clear_caller();
	}

	/**
	 * Clears reference to calling object of current field
	 */
	function clear_caller() {
		unset($this->caller);
	}

	/**
	 * Retrieves reference to caller object of current field
	 * @return object Reference to caller object
	 */
	function &get_caller() {
		$ret = null;
		if ( $this->has_caller() )
			$ret =& $this->caller;
		return $ret;
	}

	/**
	 * Checks if field has a caller reference
	 * @return bool TRUE if field is called by another field, FALSE otherwise
	 */
	function has_caller() {
		return !empty($this->caller);
	}

	

	/**
	 * Sets an element for the field type
	 * @param string $name Name of element
	 * @param SLB_Field_Type $type Reference of field type to use for element
	 * @param array $properties Properties for element (passed as keyed associative array)
	 * @param string $id_prop Name of property to set $name to (e.g. ID, etc.)
	 */
	function set_element($name, $type, $properties = array(), $id_prop = 'id') {
		$name = trim(strval($name));
		if ( empty($name) )
			return false;
		//Create new field for element
		$el = new SLB_Field($name, $type);
		//Set container to current field instance
		$el->set_container($this);
		//Add properties to element
		$el->set_properties($properties);
		//Save element to current instance
		$this->elements[$name] =& $el;
	}

	/**
	 * Add a layout to the field
	 * @param string $name Name of layout
	 * @param string $value Layout text
	 */
	function set_layout($name, $value = '') {
		if ( !is_string($name) )
			return false;
		$name = trim($name);
		$this->layout[$name] = $value;
		return true;
	}

	/**
	 * Retrieve specified layout
	 * @param string $name Layout name
	 * @param bool $parse_nested (optional) Whether nested layouts should be expanded in retreived layout or not (Default: TRUE)
	 * @return string Specified layout text
	 */
	function get_layout($name = 'form', $parse_nested = true) {
		//Retrieve specified layout (use $name value if no layout by that name exists)
		$layout = $this->get_member_value('layout', $name, $name);

		//Find all nested layouts in current layout
		if ( !empty($layout) && !!$parse_nested ) {
			$ph = $this->get_placeholder_defaults();

			while ($ph->match = $this->parse_layout($layout, $ph->pattern_layout)) {
				//Iterate through the different types of layout placeholders
				foreach ($ph->match as $tag => $instances) {
					//Iterate through instances of a specific type of layout placeholder
					foreach ($instances as $instance) {
						//Get nested layout
						$nested_layout = $this->get_member_value($instance);

						//Replace layout placeholder with retrieved item data
						if ( !empty($nested_layout) )
							$layout = str_replace($ph->start . $instance['match'] . $ph->end, $nested_layout, $layout);
					}
				}
			}
		}

		return $layout;
	}

	/**
	 * Checks if specified layout exists
	 * Finds layout if it exists in current object or any of its parents
	 * @param string $layout Name of layout to check for
	 * @return bool TRUE if layout exists, FALSE otherwise
	 */
	function has_layout($layout) {
		$ret = false;
		if ( is_string($layout) && ($layout = trim($layout)) && !empty($layout) ) {
			$layout = $this->get_member_value('layout', $layout, false);
			if ( $layout !== false )
				$ret = true;
		}

		return $ret;
	}

	/**
	 * Checks if layout content is valid
	 * Layouts need to have placeholders to be valid
	 * @param string $layout_content Layout content (markup)
	 * @return bool TRUE if layout is valid, FALSE otherwise
	 */
	function is_valid_layout($layout_content) {
		$ph = $this->get_placeholder_defaults();
		return preg_match($ph->pattern_general, $layout_content);
	}

	/**
	 * Parse field layout with a regular expression
	 * @param string $layout Layout data
	 * @param string $search Regular expression pattern to search layout for
	 * @return array Associative array containing all of the regular expression matches in the layout data
	 * 	Array Structure:
	 *		root => placeholder tags
	 *				=> Tag instances (array)
	 *					'tag'			=> (string) tag name
	 *					'match' 		=> (string) placeholder match
	 *					'attributes' 	=> (array) attributes
	 */
	function parse_layout($layout, $search) {
		$ph_xml = '';
		$parse_match = '';
		$ph_root_tag = 'ph_root_element';
		$ph_start_xml = '<';
		$ph_end_xml = ' />';
		$ph_wrap_start = '<' . $ph_root_tag . '>';
		$ph_wrap_end = '</' . $ph_root_tag . '>';
		$parse_result = false;

		//Find all nested layouts in layout
		$match_value = preg_match_all($search, $layout, $parse_match, PREG_PATTERN_ORDER);

		if ($match_value !== false && $match_value > 0) {
			$parse_result = array();
			//Get all matched elements
			$parse_match = $parse_match[1];

			//Build XML string from placeholders
			foreach ($parse_match as $ph) {
				$ph_xml .= $ph_start_xml . $ph . $ph_end_xml . ' ';
			}
			$ph_xml = $ph_wrap_start . $ph_xml . $ph_wrap_end;
			//Parse XML data
			$ph_prs = xml_parser_create();
			xml_parser_set_option($ph_prs, XML_OPTION_SKIP_WHITE, 1);
			xml_parser_set_option($ph_prs, XML_OPTION_CASE_FOLDING, 0);
			$ret = xml_parse_into_struct($ph_prs, $ph_xml, $parse_result['values'], $parse_result['index']);
			xml_parser_free($ph_prs);

			//Build structured array with all parsed data

			unset($parse_result['index'][$ph_root_tag]);

			//Build structured array
			$result = array();
			foreach ($parse_result['index'] as $tag => $instances) {
				$result[$tag] = array();
				//Instances
				foreach ($instances as $instance) {
					//Skip instance if it doesn't exist in parse results
					if (!isset($parse_result['values'][$instance]))
						continue;

					//Stop processing instance if a previously-saved instance with the same options already exists
					foreach ($result[$tag] as $tag_match) {
						if ($tag_match['match'] == $parse_match[$instance - 1])
							continue 2;
					}

					//Init instance data array
					$inst_data = array();

					//Add Tag to array
					$inst_data['tag'] = $parse_result['values'][$instance]['tag'];

					//Add instance data to array
					$inst_data['attributes'] = (isset($parse_result['values'][$instance]['attributes'])) ? $inst_data['attributes'] = $parse_result['values'][$instance]['attributes'] : '';

					//Add match to array
					$inst_data['match'] = $parse_match[$instance - 1];

					//Add to result array
					$result[$tag][] = $inst_data;
				}
			}
			$parse_result = $result;
		}

		return $parse_result;
	}

	/**
	 * Retrieves default properties to use when evaluating layout placeholders
	 * @return object Object with properties for evaluating layout placeholders
	 */
	function get_placeholder_defaults() {
		$ph = new stdClass();
		$ph->start = '{';
		$ph->end = '}';
		$ph->reserved = array('ref' => 'ref_base');
		$ph->pattern_general = '/' . $ph->start . '([a-zA-Z0-9_].*?)' . $ph->end . '/i';
		$ph->pattern_layout = '/' . $ph->start . '([a-zA-Z0-9].*?\s+' . $ph->reserved['ref'] . '="layout.*?".*?)' . $ph->end . '/i';
		return $ph;
	}
	
	/**
	 * Build item output
	 * @param string $layout (optional) Layout to build
	 * @param string $data Data to pass to layout
	 * @return string Generated output
	 */
	function build($layout = 'form', $data = null) {
		$out = array(
			$this->build_pre($layout, $data),
			$this->build_layout($layout,$data),
			$this->build_post($layout, $data)
		);
		return implode('', $out);
	}
	
	/**
	 * Content to add before layout output
	 * @return string
	 */
	function build_pre($layout = 'form', $data = null) {
		return '';
	}
	
	/**
	 * Content to add after layout output
	 * @return string
	 */
	function build_post($layout = 'form', $data = null) {
		return '';
	}
	
	/**
	 * Builds HTML for a field based on its properties
	 * @param string $layout (optional) Name of layout to build
	 * @param array $data Additional data for current item
	 */
	function build_layout($layout = 'form', $data = null) {
		$out_default = '';
		//Get base layout
		$out = $this->get_layout($layout);
		//Only parse valid layouts
		if ( $this->is_valid_layout($out) ) {
			//Parse Layout
			$ph = $this->get_placeholder_defaults();

			//Search layout for placeholders
			while ( $ph->match = $this->parse_layout($out, $ph->pattern_general) ) {
				//Iterate through placeholders (tag, id, etc.)
				foreach ( $ph->match as $tag => $instances ) {
					//Iterate through instances of current placeholder
					foreach ( $instances as $instance ) {
						//Process value based on placeholder name
						$target_property = apply_filters($this->add_prefix('process_placeholder_' . $tag), '', $this, $instance, $layout, $data);
						//Process value using default processors (if necessary)
						if ( '' == $target_property ) {
							$target_property = apply_filters($this->add_prefix('process_placeholder'), $target_property, $this, $instance, $layout, $data);
						}

						//Clear value if value not a string
						if ( !is_scalar($target_property) ) {
							$target_property = '';
						}
						//Replace layout placeholder with retrieved item data
						$out = str_replace($ph->start . $instance['match'] . $ph->end, $target_property, $out);
					}
				}
			}
		} else {
			$out = $out_default;
		}
		/* Return generated value */
		return implode('', array($this->build_pre(), $out, $this->build_post()));
	}
}

class SLB_Field extends SLB_Field_Type {}

/**
 * Managed collection of fields
 * @package Simple Lightbox
 * @subpackage Fields
 * @author SM
 */
class SLB_Field_Collection extends SLB_Field_Base {
	
	/**
	 * Indexed array of items in collection
	 * @var array
	 */
	var $items = array();

	/**
	 * Associative array of groups in content type
	 * Key: Group name
	 * Value: object of group properties
	 *  > title
	 *  > description string Group description
	 *  > items array Items in group
	 * @var array
	 */
	var $groups = array();
	
	/**
	 * Item type
	 * @var string
	 */
	var $item_type = 'SLB_Field';
	
	/* Constructors */

	/**
	 * Legacy constructor
	 * @uses __construct() to init instance
	 * @param string $id Content type ID
	 */
	function SLB_Field_Collection($id, $title = '', $properties = null) {
		$args = func_get_args();
		call_user_func_array(array($this, '__construct'), $args);
	}

	/**
	 * Class constructor
	 * @param string $id Content type ID
	 * @param array $properties (optional) Properties to set for content type (Default: none)
	 */
	function __construct($id, $properties = null) {
		//Parent constructor
		parent::__construct($id, $properties);
		
		//Init
		$this->init();
		
		//Setup object based on properties
		if ( is_array($properties) && !empty($properties) ) {
			//Groups
			if ( isset($properties['groups']) )
				$this->add_groups($properties['groups']);
			//Items
			if ( isset($properties['items']) )
				$this->add_items($properties['items']);
		}
	}

	/*-** Getters/Setters **-*/
	
	/* Data */
	
	/**
	 * Retrieve external data for items in collection
	 * Retrieved data is saved to the collection's $data property
	 * Uses class properties to determine how data is retrieved
	 * Examples:
	 *  > DB
	 *  > XML
	 *  > JSON
	 * @return void
	 */
	function load_data() {
		$this->data_fetched = true;
	}
	
	/**
	 * Set data for an item
	 * @param string|object $item Reference or ID of Field to set data for
	 * @param mixed $value Data to set
	 */
	function set_data($item, $value = '', $save = true) {
		//Set data for entire collection
		if ( 1 == func_num_args() && is_array($item) )
			$this->data = wp_parse_args($item, $this->data);
		//Get $item's ID
		elseif ( is_object($item) && method_exists($item, 'get_id') )
			$item = $item->get_id();
		//Set data
		if ( is_string($item) && !empty($item) && isset($this->items[$item]) )
			$this->data[$item] = $value;
		if ( $save )
			$this->save();
	}

	/* Item */
	
	/**
	 * Adds item to collection
	 * @param string $id Unique name for item
	 * @param object|string $parent Field type that this item is based on
	 * @param array $properties (optional) Item properties
	 * @param string $group (optional) Group ID to add item to
	 * @return object Reference to new item
	 */
	function &add($id, $parent = null, $properties = array(), $group = null) {
		$args = func_get_args();
		$properties = $this->make_properties($this->util->func_get_options($args), $properties, array('group' => $group));
		$it = ( is_object($id) ) ? 'O:' . $id->get_id() . '(' . get_class($id) . ')' : $id;
		//Check if previously created item is being added
		if ( is_object($id) && strtolower(get_class($id)) == strtolower($this->item_type) ) {
			$item =& $id;
		} else {
			//Create item
			if ( !class_exists($this->item_type) )
				return false;
			$type = $this->item_type;
			/**
			 * @var SLB_Field
			 */
			$item =& new $type($id, $properties);
		}
		if ( strlen($item->get_id()) == 0 ) {
			return false;
		}
		
		$item->set_container($this);

		//Add item to collection
		$this->items[$item->get_id()] =& $item;
		
		//Add item to group
		if ( empty($group) ) {
			//Check properties array for group
			if ( isset($properties['group']) ) {
				$group = $properties['group'];
				//Remove group property from array
				unset($properties['group']);
			}
		}
		$this->add_to_group($group, $item->id);
		return $item;
	}

	/**
	 * Removes item from collection
	 * @param string|object $item Object or item ID to remove
	 */
	function remove($item) {
		if ( $this->has($item) ) {
			$item = $this->get($item);
			$item = $item->get_id();
			//Remove from items array
			unset($this->items[$item]);
			//Remove item from groups
			$this->remove_from_group($item);
		}
	}

	/**
	 * Checks if item exists in the collection
	 * @param string $item Item ID
	 * @return bool TRUE if item exists, FALSE otherwise
	 */
	function has($item) {
		return ( !is_string($item) || empty($item) || is_null($this->get_member_value('items', $item, null)) ) ? false : true;
	}
	
	/**
	 * Retrieve specified item in collection
	 * @param string|object $item Item object or ID to retrieve
	 * @return object Specified item
	 */
	function &get($item) {
		if ( $this->has($item) ) {
			if ( !is_object($item) || !is_a($item, $this->item_type) ) {
				if ( is_string($item) ) {
					$item = trim($item);
					$item =& $this->items[$item];
				}
				else {
					$item = false;
				}
			}
		}
		
		if ( empty($item) ) {
			//Return empty item if no item exists
			$item =& new $this->item_type;
		}
		return $item;
	}
	
	/**
	 * Retrieve item data
	 * @param $item
	 * @param $context
	 * @param $top
	 */
	function get_data($item = null, $context = '', $top = true) {
		$this->load_data();
		$ret = null;
		if ( $this->has($item) ) {
			$item =& $this->get($item);
			$ret = $item->get_data($context, $top);
		} elseif ( is_null($item) ) {
			$ret = parent::get_data($context, $top);
		}
		return $ret;
	}

	/* Items (Collection) */
	
	/**
	 * Add multiple items to collection
	 * @param array $items Items to add to collection
	 * Array Structure:
	 *  > Key (string): Item ID
	 *  > Val (array): Item properties
	 * @return void
	 */
	function add_items($items = array()) {
		//Validate
		if ( !is_array($items) || empty($items) )
			return false;
		//Iterate
		foreach ( $items as $id => $props ) {
			$this->add($id, $props);
		}
	}
	
	/**
	 * Retrieve reference to items in collection
	 * @return array Collection items (reference)
	 */
	function &get_items($group = null) {
		if ( $this->group_exists($group) ) {
			return $this->get_group_items($group);
		}
		return $this->items; 
	}
	
	/**
	 * Default bulk item building method
	 * Children classes should implement their own functionality
	 * If no group specified, all items in collection are built
	 * @param string|object $group (optional) Group to build items for (ID or instance object)
	 * @return void
	 */
	function build_items($group = null) {
		$items =& $this->get_items($group);
		$out = array();
		foreach ( $items as $item ) {
			$out[] = $item->build();
		}
		return implode('', $out);
	}
	
	/* Group */
	
	/**
	 * Add groups to collection
	 * @param array $groups Associative array of group properties
	 * Array structure:
	 *  > Key (string): group ID
	 *  > Val (string): Group Title
	 */
	function add_groups($groups = array()) {
		//Validate
		if ( !is_array($groups) || empty($groups) )
			return false;
		//Iterate
		foreach ( $groups as $id => $props ) {
			$this->add_group($id, $props);
		}
	}
	
	/**
	 * Adds group to content type
	 * Groups are used to display related items in the UI 
	 * @param string $id Unique name for group
	 * @param string $title Group title
	 * @param string $description Short description of group's purpose
	 * @param array $items (optional) ID's of existing items to add to group
	 * @return object Group object
	 */
	function &add_group($id, $title = '', $description = '', $items = array()) {
		//Create new group and set properties
		$id = trim($id);
		$this->groups[$id] =& $this->create_group($title, $description);
		//Add items to group (if supplied)
		if ( !empty($items) && is_array($items) )
			$this->add_to_group($id, $items);
		return $this->groups[$id];
	}

	/**
	 * Remove specified group from content type
	 * @param string $id Group ID to remove
	 */
	function remove_group($id) {
		$id = trim($id);
		if ( $this->group_exists($id) ) {
			unset($this->groups[$id]);
		}
	}

	/**
	 * Standardized method to create a new item group
	 * @param string $title Group title (used in meta boxes, etc.)
	 * @param string $description Short description of group's purpose
	 * @return object Group object
	 */
	function &create_group($title = '', $description = '') {
		//Create new group object
		$group = new stdClass();
		/* Set group properties */
		
		//Set Title
		$title = ( is_scalar($title) ) ? trim($title) : '';
		$group->title = $title;
		//Set Description
		$description = ( is_scalar($description) ) ? trim($description) : '';
		$group->description = $description;
		//Create array to hold items
		$group->items = array();
		return $group;
	}
	
	/**
	 * Checks if group exists in collection
	 * @param string $id Group name
	 * @return bool TRUE if group exists, FALSE otherwise
	 */
	function group_exists($group) {
		$ret = false;
		if ( is_object($group) )
			$ret = true;
		elseif ( is_string($group) && ($group = trim($group)) && strlen($group) > 0 ) {
			$group = trim($group);
			//Check if group exists
			$ret = !is_null($this->get_member_value('groups', $group, null));
		}
		return $ret;
	}
	
	/**
	 * Adds item to a group in the collection
	 * Group is created if it does not already exist
	 * @param string|array $group ID of group (or group parameters if new group) to add item to
	 * @param string|array $items Name or array of item(s) to add to group
	 */
	function add_to_group($group, $items) {
		//Validate parameters
		$group_id = '';
		if ( !empty($group) ) {
			if ( !is_array($group) ) {
				$group = array($group, $group);
			}
			
			$group[0] = $group_id = trim(sanitize_title_with_dashes($group[0]));
		}
		if ( empty($group_id) || empty($items) )
			return false;
		//Create group if it doesn't exist
		if ( !$this->group_exists($group_id) ) {
			call_user_func_array($this->m('add_group'), $group);
		}
		if ( ! is_array($items) )
			$items = array($items);
		foreach ( $items as $item ) {
			if ( ! $this->has($item) )
				continue;
			$iref =& $this->get($item);
			//Remove item from any other group it's in (items can only be in one group)
			foreach ( array_keys($this->groups) as $group_name ) {
				if ( isset($this->groups[$group_name]->items[$iref->id]) )
					unset($this->groups[$group_name]->items[$iref->id]);
			}
			//Add reference to item in group
			$this->groups[$group_id]->items[$iref->id] =& $iref;
			unset($iref);
		}
	}

	/**
	 * Remove item from a group
	 * If no group is specified, then item is removed from all groups
	 * @param string|object $item Object or ID of item to remove from group
	 * @param string $group (optional) Group ID to remove item from
	 */
	function remove_from_group($item, $group = '') {
		//Get ID of item to remove or stop execution if item invalid
		$item = $this->get($item);
		$item = $item->get_id();
		if ( !$item )
			return false;

		//Remove item from group
		if ( !empty($group) ) {
			//Remove item from single group
			if ( ($group =& $this->get_group($group)) && isset($group->items[$item]) ) {
				unset($group->items[$item]);
			}
		} else {
			//Remove item from all groups
			foreach ( array_keys($this->groups) as $group ) {
				if ( ($group =& $this->get_group($group)) && isset($group->items[$item]) ) {
					unset($group->items[$item]);
				}
			}
		}
	}

	/**
	 * Retrieve specified group
	 * @param string $group ID of group to retrieve
	 * @return object Reference to specified group
	 */
	function &get_group($group) {
		if ( is_object($group) )
			return $group;
		if ( is_string($group) )
			$group = trim($group);
		//Create group if it doesn't already exist
		if ( ! $this->group_exists($group) )
			$this->add_group($group);
		return $this->get_member_value('groups', $group);
	}
	
	/**
	 * Retrieve a group's items
	 * @uses SLB_Field_Collection::get_group() to retrieve group object
	 * @param object|string $group Group object or group ID
	 * @return array Group's items
	 */
	function &get_group_items($group) {
		$group =& $this->get_group($group);
		return $group->items;
	}

	/**
	 * Retrieve all groups in content type
	 * @return array Reference to group objects
	 */
	function &get_groups() {
		return $this->get_member_value('groups');
	}

	/**
	 * Output items in a group
	 * @param string $group ID of Group to output
	 * @return string Group output
	 * @todo Refactor to be general builder
	 */
	function build_group($group) {
		$out = array();
		$classnames = (object) array(
			'multi'		=> 'multi_field',
			'single'	=> 'single_field',
			'elements'	=> 'has_elements'
		);

		//Stop execution if group does not exist
		if ( $this->group_exists($group) && $group =& $this->get_group($group) ) {
			$group_items = ( count($group->items) > 1 ) ? $classnames->multi : $classnames->single . ( ( ( $fs = array_keys($group->items) ) && ( $f =& $group->items[$fs[0]] ) && ( $els = $f->get_member_value('elements', '', null) ) && !empty($els) ) ? '_' . $classnames->elements : '' );
			$classname = array($this->add_prefix('attributes_wrap'), $group_items);
			$out[] = '<div class="' . implode(' ', $classname) . '">'; //Wrap all items in group

			//Build layout for each item in group
			foreach ( array_keys($group->items) as $item_id ) {
				$item =& $group->items[$item_id];
				$item->set_caller($this);
				//Start item output
				$id = $this->add_prefix('field_' . $item->get_id());
				$out[] = '<div id="' . $id . '_wrap" class=' . $this->add_prefix('attribute_wrap') . '>';
				//Build item layout
				$out[] = $item->build_layout();
				//end item output
				$out[] = '</div>';
				$item->clear_caller();
			}
			$out[] = '</div>'; //Close items container
			//Add description if exists
			if ( !empty($group->description) )
				$out[] = '<p class=' . $this->add_prefix('group_description') . '>' . $group->description . '</p>';
		}

		//Return group output
		return implode($out);
	}
	
	/* Collection */
	
	/**
	 * Build entire collection of items
	 */
	function build() {
		//Get Groups
		$groups = array_keys($this->get_groups());
		//Build groups
		foreach ( $groups as $group ) {
			$this->build_group($group);
		}
	}
}

/**
 * Collection of default system-wide fields
 * @package Simple Lightbox
 * @subpackage Fields
 * @author SM
 *
 */
class SLB_Fields extends SLB_Field_Collection {
	
	var $item_type = 'SLB_Field_Type';
	
	/**
	 * Placeholder handlers
	 * @var array
	 */
	var $placholders = null;
	
	/* Constructor */
	
	function SLB_Fields() {
		$this->__construct();
	}
	
	function __construct() {
		parent::__construct('fields');
	}
	
	function register_hooks() {
		parent::register_hooks();
		
		//Init fields
		add_action('init', $this->m('register_types'));
		//Init placeholders
		add_action('init', $this->m('register_placeholders'));
	}
	
	/* Field Types */
	
	/**
	 * Initialize fields and content types
	 */
	function register_types() {
		/* Field Types */

		//Base
		$base =& new SLB_Field_Type('base');
		$base->set_description('Default Element');
		$base->set_property('tag', 'span');
		$base->set_property('class', '', 'attr');
		$base->set_layout('form_attr', '{tag} name="{field_name}" id="{field_id}" {properties ref_base="root" group="attr"}');
		$base->set_layout('form', '<{form_attr ref_base="layout"} />');
		$base->set_layout('label', '<label for="{field_id}">{label}</label>');
		$base->set_layout('display', '{data context="display"}');
		$this->add($base);

		//Base closed
		$base_closed =& new SLB_Field_Type('base_closed');
		$base_closed->set_parent('base');
		$base_closed->set_description('Default Element (Closed Tag)');
		$base_closed->set_layout('form_start', '<{tag} id="{field_id}" name="{field_name}" {properties ref_base="root" group="attr"}>');
		$base_closed->set_layout('form_end', '</{tag}>');
		$base_closed->set_layout('form', '{form_start ref_base="layout"}{data}{form_end ref_base="layout"}');
		$this->add($base_closed);

		//Input
		$input =& new SLB_Field_Type('input', 'base');
		$input->set_description('Default Input Element');
		$input->set_property('tag', 'input');
		$input->set_property('type', 'text', 'attr');
		$input->set_property('value', '{data}', 'attr');
		$this->add($input);

		//Text input
		$text =& new SLB_Field_Type('text', 'input');
		$text->set_description('Text Box');
		$text->set_property('size', 15, 'attr');
		$text->set_property('label');
		$text->set_layout('form', '{label ref_base="layout"} {inherit}');
		$this->add($text);
		
		//Checkbox
		$cb =& new SLB_Field_Type('checkbox', 'input');
		$cb->set_property('type', 'checkbox');
		$cb->set_property('value', null);
		$cb->set_layout('form_attr', '{inherit} {checked}');
		$cb->set_layout('form', '{label ref_base="layout"} <{form_attr ref_base="layout"} />');
		$this->add($cb);

		//Textarea
		$ta =& new SLB_Field_Type('textarea', 'base_closed');
		$ta->set_property('tag', 'textarea');
		$ta->set_property('cols', 40, 'attr');
		$ta->set_property('rows', 3, 'attr');
		$this->add($ta);
		
		//Rich Text
		$rt =& new SLB_Field_Type('richtext', 'textarea');
		$rt->set_property('class', 'theEditor {inherit}');
		$rt->set_layout('form', '<div class="rt_container">{inherit}</div>');
		$rt->add_action('admin_print_footer_scripts', 'wp_tiny_mce', 25);
		$this->add($rt);

		//Hidden
		$hidden =& new SLB_Field_Type('hidden');
		$hidden->set_parent('input');
		$hidden->set_description('Hidden Field');
		$hidden->set_property('type', 'hidden');
		$this->add($hidden);

		//Select
		$select =& new SLB_Field_Type('select', 'base_closed');
		$select->set_description('Select tag');
		$select->set_property('tag', 'select');
		$select->set_property('tag_option', 'option');
		$select->set_property('options', array());
		$select->set_layout('form', '{label ref_base="layout"} {form_start ref_base="layout"}{option_loop ref_base="layout"}{form_end ref_base="layout"}');
		$select->set_layout('option_loop', '{loop data="properties.options" layout="option" layout_data="option_data"}');
		$select->set_layout('option', '<{tag_option} value="{data_ext id="option_value"}">{data_ext id="option_text"}</{tag_option}>');
		$select->set_layout('option_data', '<{tag_option} value="{data_ext id="option_value"}" selected="selected">{data_ext id="option_text"}</{tag_option}>');		
		$this->add($select);
		
		//Span
		$span =& new SLB_Field_Type('span', 'base_closed');
		$span->set_description('Inline wrapper');
		$span->set_property('tag', 'span');
		$span->set_property('value', 'Hello there!');
		$this->add($span);
		
		//Enable plugins to modify (add, remove, etc.) field types
		do_action_ref_array($this->add_prefix('register_fields'), array($this));
		
		//Signal completion of field registration
		do_action_ref_array($this->add_prefix('fields_registered'), array($this));
	}
	
	/* Placeholder handlers */
	
	function register_placeholders() {
		//Default placeholder handlers
		$this->register_placeholder('all', $this->m('process_placeholder_default'), 11);
		$this->register_placeholder('field_id', $this->m('process_placeholder_id'));
		$this->register_placeholder('field_name', $this->m('process_placeholder_name'));
		$this->register_placeholder('data', $this->m('process_placeholder_data'));
		$this->register_placeholder('data_ext',$this->m('process_placeholder_data_ext'));
		$this->register_placeholder('loop', $this->m('process_placeholder_loop'));
		$this->register_placeholder('label', $this->m('process_placeholder_label'));
		$this->register_placeholder('checked', $this->m('process_placeholder_checked'));
		
		//Allow other code to register placeholders
		do_action_ref_array($this->add_prefix('register_field_placeholders'), array($this));
		
		//Signal completion of field placeholder registration
		do_action_ref_array($this->add_prefix('field_placeholders_registered'), array($this));
	}
	
	/**
	 * Register a function to handle a placeholder
	 * Multiple handlers may be registered for a single placeholder
	 * Adds filter hook to WP for handling specified placeholder
	 * Placeholders are in layouts and are replaced with data at runtime
	 * @uses add_filter()
	 * @param string $placeholder Name of placeholder to add handler for (Using 'all' will set the function as a handler for all placeholders
	 * @param callback $callback Function to set as a handler
	 * @param int $priority (optional) Priority of handler
	 * @return void
	 */
	function register_placeholder($placeholder, $callback, $priority = 10) {
		if ( 'all' == $placeholder )
			$placeholder = '';
		else
			$placeholder = '_' . $placeholder;
		$hook = $this->add_prefix('process_placeholder' . $placeholder);
		add_filter($hook, $callback, $priority, 5);
	}
	
	/**
	 * Default placeholder processing
	 * To be executed when current placeholder has not been handled by another handler
	 * @param string $output Value to be used in place of placeholder
	 * @param SLB_Field $item Field containing placeholder
	 * @param array $placeholder Current placeholder
	 * @see SLB_Field::parse_layout for structure of $placeholder array
	 * @param string $layout Layout to build
	 * @param array $data Extended data for item
	 * @return string Value to use in place of current placeholder
	 */
	function process_placeholder_default($output, $item, $placeholder, $layout, $data) {
		//Validate parameters before processing
		if ( empty($output) && is_a($item, 'SLB_Field_Type') && is_array($placeholder) ) {
			//Build path to replacement data
			$output = $item->get_member_value($placeholder);

			//Check if value is group (properties, etc.)
			//All groups must have additional attributes (beyond reserved attributes) that define how items in group are used
			if (is_array($output)
				&& !empty($placeholder['attributes'])
				&& is_array($placeholder['attributes'])
				&& ($ph = $item->get_placeholder_defaults())
				&& $attribs = array_diff(array_keys($placeholder['attributes']), array_values($ph->reserved))
			) {
				/* Targeted property is an array, but the placeholder contains additional options on how property is to be used */

				//Find items matching criteria in $output
				//Check for group criteria
				if ( 'properties' == $placeholder['tag'] && ($prop_group = $item->get_group($placeholder['attributes']['group'])) && !empty($prop_group) ) {
					/* Process group */
					$group_out = array();
					//Iterate through properties in group and build string
					foreach ( array_keys($prop_group) as $prop_key ) {
						$prop_val = $item->get_property($prop_key);
						if ( !is_null($prop_val) )
							$group_out[] = $prop_key . '="' . $prop_val . '"';
					}
					$output = implode(' ', $group_out);
				}
			} elseif ( is_object($output) && is_a($output, $item->base_class) ) {
				/* Targeted property is actually a nested item */
				//Set caller to current item
				$output->set_caller($item);
				//Build layout for nested element
				$output = $output->build_layout($layout);
			}
		}

		return $output;
	}

	/**
	 * Build Field ID attribute
	 * @see SLB_Field_Type::process_placeholder_default for parameter descriptions
	 * @return string Placeholder output
	 */
	function process_placeholder_id($output, $item, $placeholder, $layout, $data) {
		//Get attributes
		$args = wp_parse_args($placeholder['attributes'], array('format' => 'attr_id')); 
		return $item->get_id($args);
	}
	
	/**
	 * Build Field name attribute
	 * Name is formatted as an associative array for processing by PHP after submission
	 * @see SLB_Field_Type::process_placeholder_default for parameter descriptions
	 * @return string Placeholder output
	 */
	function process_placeholder_name($output, $item, $placeholder, $layout, $data) {
		//Get attributes
		$args = wp_parse_args($placeholder['attributes'], array('format' => 'attr_name')); 
		return $item->get_id($args);
	}
	
	/**
	 * Build item label
	 * @see SLB_Fields::process_placeholder_default for parameter descriptions
	 * @return string Field label
	 */
	function process_placeholder_label($output, $item, $placeholder, $layout, $data) {
		//Check if item has label property (e.g. sub-elements)
		$out = $item->get_property('label');
		//If property not set, use item title
		if ( empty($out) )
			$out = $item->get_title();
		return $out;
	}
	
	/**
	 * Retrieve data for item
	 * @see SLB_Field_Type::process_placeholder_default for parameter descriptions
	 * @return string Placeholder output
	 */
	function process_placeholder_data($output, $item, $placeholder, $layout) {
		$attr_default = array (
			'context'	=> '',
		);
		$opts = wp_parse_args($placeholder['attributes'], $attr_default);
		//Save context to separate variable
		$context = $opts['context'];
		unset($opts['context']);
		//Get data
		$out = $item->get_data($opts);
		if ( !is_null($out) ) {
			//Get specific member in value (e.g. value from a specific item element)
			if ( isset($opts['element']) && is_array($out) && ( $el = $opts['element'] ) && isset($out[$el]) )
				$out = $out[$el];
		}
		
		//Format data based on context
		$out = $item->format($out, $context);
		//Return data
		return $out;
	}
	
	/**
	 * Set checked attribute on item
	 * Evaluates item's data to see if item should be checked or not
	 * @see SLB_Fields::process_placeholder_default for parameter descriptions
	 * @return string Appropriate checkbox attribute
	 */
	function process_placeholder_checked($output, $item, $placeholder, $layout, $data) {
		$out = '';
		$c = $item->get_container();
		$d = ( isset($c->data[$item->get_id()]) ) ? $c->data[$item->get_id()] : null;
		$item->set_property('d', true);
		if ( $item->get_data() )
			$out = 'checked="checked"';
		$item->set_property('d', false);
		return $out;
	}

	/**
	 * Loops over data to build item output
	 * Options:
	 *  data		- Dot-delimited path in item that contains data to loop through
	 *  layout		- Name of layout to use for each data item in loop
	 *  layout_data	- Name of layout to use for data item that matches previously-saved item data
	 * @see SLB_Field_Type::process_placeholder_default for parameter descriptions
	 * @return string Placeholder output
	 */
	function process_placeholder_loop($output, $item, $placeholder, $layout, $data) {
		//Setup loop options
		$attr_defaults = array (
								'layout'		=> '',
								'layout_data'	=> null,
								'data'			=> ''
								);
		$attr = wp_parse_args($placeholder['attributes'], $attr_defaults);
		if ( is_null($attr['layout_data']) )
			$attr['layout_data'] =& $attr['layout'];
		//Get data for loop
		$path = explode('.', $attr['data']);
		$loop_data = $item->get_member_value($path);
		
		//Check if data is callback
		if ( is_callable($loop_data) )
			$loop_data = call_user_func($loop_data);
		
		//Get item data
		$data = $item->get_data();

		//Iterate over data and build output
		$out = array();
		if ( is_array($loop_data) && !empty($loop_data) ) {
			foreach ( $loop_data as $value => $label ) {
				//Load appropriate layout based on item value
				$layout = ( ($data === 0 && $value === $data) xor $data == $value ) ? $attr['layout_data'] : $attr['layout'];
				//Stop processing if no valid layout is returned
				if ( empty($layout) )
					continue;
				//Prep extended item data
				$data_ext = array('option_value' => $value, 'option_text' => $label);
				$out[] = $item->build_layout($layout, $data_ext);
			}
		}

		//Return output
		return implode($out);
	}
	
	/**
	 * Returns specified value from extended data array for item
	 * @see SLB_Field_Type::process_placeholder_default for parameter descriptions
	 * @return string Placeholder output
	 */
	function process_placeholder_data_ext($output, $item, $placeholder, $layout, $data) {
		if ( isset($placeholder['attributes']['id']) && ($key = $placeholder['attributes']['id']) && isset($data[$key]) ) {
			$output = strval($data[$key]);
		}

		return $output;
	}
}