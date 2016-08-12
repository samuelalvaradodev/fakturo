<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_packaging') ) :
class fktr_tax_packaging {
	
	public static $tax_name = 'fktr_packaging';
	function __construct() {
		add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
		
		add_action(self::$tax_name.'_edit_form_fields', array(__CLASS__, 'edit_form_fields'));
		add_action(self::$tax_name.'_add_form_fields',  array(__CLASS__, 'add_form_fields'));
		
		
		add_action('edited_'.self::$tax_name, array(__CLASS__, 'save_fields'), 10, 2);
		add_action('created_'.self::$tax_name, array(__CLASS__,'save_fields'), 10, 2);
		
		add_filter('manage_edit-'.self::$tax_name.'_columns', array(__CLASS__, 'columns'), 10, 3);
		add_filter('manage_'.self::$tax_name.'_custom_column',  array(__CLASS__, 'theme_columns'), 10, 3);
		
		add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts'), 10, 1);
		
		
	}
	public static function init() {
		
	
		$labels = array(
			'name'                       => _x( 'Packagings', 'Packagings' ),
			'singular_name'              => _x( 'Packaging', 'Packaging' ),
			'search_items'               => __( 'Search Packagings' ),
			'popular_items'              => __( 'Popular Packagings' ),
			'all_items'                  => __( 'All Packagings' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Packaging' ),
			'update_item'                => __( 'Update Packaging' ),
			'add_new_item'               => __( 'Add New Packaging' ),
			'new_item_name'              => __( 'New Packaging Name' ),
			'separate_items_with_commas' => __( 'Separate Packagings with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Packagings' ),
			'choose_from_most_used'      => __( 'Choose from the most used Packagings' ),
			'not_found'                  => __( 'No Packagings found.' ),
			'menu_name'                  => __( 'Packagings' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-packaging' ),
		);

		register_taxonomy(
			self::$tax_name,
			'',
			$args
		);
		
		
		
	}
	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			
			
		}
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>

		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
	
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
	
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		
		switch ($column_name) {
			default:
				break;
		}
		return $out;    
	}
	public static function save_fields($term_id, $tt_id) {
		if (isset( $_POST['term_meta'])) {
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_packaging = new fktr_tax_packaging();

?>