<?php
/*
Plugin Name: Colored Categories
Plugin URI: http://brandoncamenisch.com
Description: Adds a custom color to each category
Version: 1.0
Author: Brandon Camenisch
Author URI: http://brandoncamenisch.com/
License: GPLv2 or later
*/

namespace WPSE\coloredcategories;

new ColoredCategories;

class ColoredCategories {

  #Construct
  function __construct() {
    #Actions
      add_action( 'init', array($this, 'loader') );
      add_action( 'admin_enqueue_scripts', array($this, 'scripts'));
      add_action('wp_ajax_update_color_options_array', array($this,'update_color_options_array') );
      add_action('create_category', array($this,'add_single_color_option') );

    #Filters
      add_filter( 'manage_edit-category_columns', array($this, 'add_column_to_categories') );
      add_filter( 'manage_category_custom_column',array($this, 'modify_categories_column_content'), 10, 3 );

    #Hooks
      register_activation_hook( __FILE__, array($this, 'add_color_options_array') );

    #Shortcodes
      add_shortcode('color_categories',array($this,'get_cat_color') );
  }

  public function loader() {
    #define constants
      define('COLORED_CATEGORIES_PATH', plugin_dir_path(__FILE__));
      define('COLORED_CATEGORIES_URL', plugin_dir_url(__FILE__));
  }

  public function add_column_to_categories($column) {
    #ADD NEW COLUMN
      $column['color'] = 'Color';
      return $column;
  }
  public function scripts($hook) {
    #Check is screen is edit-tags || Categories page
    if( 'edit-tags.php' === $hook ){
    #ColorPicker in WP 3.5
    wp_enqueue_script( 'iris' );
    #Register and load it in the footer
    wp_register_script( 'color-category', COLORED_CATEGORIES_URL.'admin.js',NULL,NULL,true);
    wp_enqueue_script( 'color-category' );

    wp_localize_script( 'color-category', 'ajaxObject',
            array( 'ajaxUrl' => admin_url( 'admin-ajax.php'), 'colornonce' => wp_create_nonce( 'colorCategoriesNonce') ) );
    }
  }

  #Converts the opposite of a hex value
  public function opposite_hex_value_color($color) {
    #Get red, green and blue
    $r = substr($color, 0, 2);
    $g = substr($color, 2, 2);
    $b = substr($color, 4, 2);

    #Revert them, they are decimal now
    $r = 0xff-hexdec($r);
    $g = 0xff-hexdec($g);
    $b = 0xff-hexdec($b);

    #Now convert them to hex and return.
    return dechex($r).dechex($g).dechex($b);
    }

  public function modify_categories_column_content($null, $column_name, $color) {
      #Get the color option
    	$catarr =& get_option('colored-categories');
    	#Since there's no foreach let's search the array by matching the color value
    	$key =& array_search($catarr[$color], $catarr);

    	echo "<input type='text' class='color-picker' name='category_color' data-id='$key' value='$catarr[$color]' style='background:$catarr[$color];
                                          	                                                                            padding:5px;
                                          	                                                                            color:#".ColoredCategories::opposite_hex_value_color($catarr[$color]).";
                                          	                                                                            width:100%;
                                          	                                                                            box-sizing:border-box;'>";
  }

  public function add_single_color_option() {
    $highestCategoryId = max(get_all_category_ids());
    $opt =& get_option('colored-categories');
    $opt[$highestCategoryId] = dechex(rand(0x000000, 0xFFFFFF));
    array_push($opt, $opt[$highestCategoryId]);
    update_option('colored-categories', $opt);
  }

  public function add_color_options_array() {
    #Get Categories & define $'s
    $categories = get_all_category_ids();
    $arr = array();

    #Loop through to create array with colors
    foreach($categories as $term_id) {
      #Create random Hex Color
      $color = 	dechex(rand(0x000000, 0xFFFFFF));
      #Add key value to array
      $arr[$term_id] = '#'.$color;
  	 }#END foreach
  	add_option('colored-categories', $arr);
  }

  public function update_color_options_array() {
    if (current_user_can('manage_categories') && is_admin() && isset($_POST['categoryId']) && isset($_POST['categoryColor'])){
      #Get entire array
      $opt =& get_option('colored-categories');
      #Alter the options array appropriately
      $opt[$_POST['categoryId']] = $_POST['categoryColor'];
      #Update entire array
      update_option('colored-categories', $opt);
    }

  }


}#END ColoredCategories CLASS