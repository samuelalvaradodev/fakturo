<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_currency') ) :
class fktr_tax_currency {
	
	function __construct() {
		add_action( 'init', array('fktr_tax_currency', 'init'), 1, 99 );
		add_action('fktr_currencies_edit_form_fields', array('fktr_tax_currency', 'edit_form_fields'));
		add_action('fktr_currencies_add_form_fields',  array('fktr_tax_currency', 'add_form_fields'));
		add_action('edited_fktr_currencies', array('fktr_tax_currency', 'save_fields'), 10, 2);
		add_action('created_fktr_currencies', array('fktr_tax_currency','save_fields'), 10, 2);
		
		add_filter('manage_edit-fktr_currencies_columns', array('fktr_tax_currency', 'columns'), 10, 3);
		add_filter('manage_fktr_currencies_custom_column',  array('fktr_tax_currency', 'theme_columns'), 10, 3);
		add_action('admin_enqueue_scripts', array('fktr_tax_currency', 'scripts'), 10, 1);
		
	}
	public static function init() {
		
		$labels = array(
			'name'                       => _x( 'Currencies', 'Currencies', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Currency', 'Currency', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Currencies', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Currencies', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Currencies', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Currency', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Currency', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Currency', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Currency Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Currency with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Currencies', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Currencies', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Currencies found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Currencies', FAKTURO_TEXT_DOMAIN ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-currencies' ),
		);
		register_taxonomy(
			'fktr_currencies',
			'',
			$args
		);
		
	}
	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'fktr_currencies') {
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-currencies', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-currencies.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);
			wp_localize_script('taxonomy-currencies', 'setting_system',
				array(
					'thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers']

				) );
		
		}
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		<div class="form-field" id="plural_div">
			<label for="term_meta[plural]">'.__( 'Plural', FAKTURO_TEXT_DOMAIN ).'</label>
			<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="">
			<p class="description">'.__( 'Enter name plural of the currency', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="symbol_div">
			<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: center; padding-right: 0px; " type="text" name="term_meta[symbol]" id="term_meta[symbol]" value="">
			<p class="description">'.__( 'Enter a symbol like $', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="rate_div">
			<label for="term_meta[rate]">'.__( 'Rate', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " type="text" name="term_meta[rate]" id="term_meta_rate" value="0">
			<p class="description">'.__( 'Enter a rate', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="reference_div">
			<label for="term_meta[reference]">'.__( 'Reference', FAKTURO_TEXT_DOMAIN ).'</label>
			<input type="text" name="term_meta[reference]" id="term_meta[reference]" value="">
			<p class="description">'.__( 'Enter a reference website to find the conversion rate', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, 'fktr_currencies');
		$setting_system = get_option('fakturo_system_options_group', false);
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[plural]">'.__( 'Plural', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="'.$term_meta->plural.'">
				<p class="description">'.__( 'Enter name plural of the currency', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" style="width: 60px;text-align: center; padding-right: 0px; " name="term_meta[symbol]" id="term_meta[symbol]" value="'.$term_meta->symbol.'">
				<p class="description">'.__( 'Enter a symbol like $', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[rate]">'.__( 'Rate', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " type="text" name="term_meta[rate]" id="term_meta_rate" value="'.number_format($term_meta->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).'">
				<p class="description">'.__( 'Enter a rate', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[reference]">'.__( 'Reference', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[reference]" id="term_meta[reference]" value="'.$term_meta->reference.'">
				<p class="description">'.__( 'Enter a reference website to find the conversion rate', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
			'symbol' => __('Symbol', FAKTURO_TEXT_DOMAIN),
			'rate' => __('Rate', FAKTURO_TEXT_DOMAIN)
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		
		$term = get_fakturo_term($term_id, 'fktr_currencies');
		$setting_system = get_option('fakturo_system_options_group', false);
		switch ($column_name) {
			case 'symbol': 
				$out = esc_attr( $term->symbol);
				break;

			case 'rate': 
				$out = esc_attr(number_format($term->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']));
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function save_fields($term_id, $tt_id) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (isset( $_POST['term_meta'])) {
			
			if (strpos($_POST['term_meta']['rate'], $setting_system['decimal']) !== false) {
				$pieceNumber = explode($setting_system['decimal'], $_POST['term_meta']['rate']);
				$pieceNumber[0] = str_replace($setting_system['thousand'], '', $pieceNumber[0]);
				$_POST['term_meta']['rate'] = implode('.', $pieceNumber);
				$_POST['term_meta']['rate'] = filter_var($_POST['term_meta']['rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			}
			
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_currency = new fktr_tax_currency();

?>