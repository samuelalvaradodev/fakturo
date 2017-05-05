<?php
	// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_wizzard {

	public static $steps = array();
	public static $current_request = array();
	/**
	* Static function hooks
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function hooks() {
		add_action('admin_post_fktr_wizzard', array(__CLASS__, 'page'));
		add_action('fktr_wizzard_output_print_scripts', array(__CLASS__, 'scripts'));
		add_action('fktr_wizzard_output_print_styles', array(__CLASS__, 'styles'));
		
		add_action('admin_post_fktr_wizzard_post', array(__CLASS__, 'post_actions'));
		add_action('fktr_wizzard_install_step_1', array(__CLASS__, 'page_step_one'));
		add_action('fktr_wizzard_action_1', array(__CLASS__, 'action_one'));
		add_action('wp_ajax_fktr_load_countries_states', array(__CLASS__, 'load_countries_ajax'));

		add_action('fktr_wizzard_install_step_2', array(__CLASS__, 'page_step_two'));
		add_action('fktr_wizzard_action_2', array(__CLASS__, 'action_two'));

		add_action('fktr_wizzard_install_step_3', array(__CLASS__, 'page_step_three'));
		add_action('fktr_wizzard_install_step_4', array(__CLASS__, 'page_step_four'));
		
	}
	/**
	* Static function get_steps
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function get_steps() {
		if (empty($steps)) {
			$steps = array('Load Countries', 'Company Info', 'Load Currencies', 'Other 4', 'Other 5');
			$steps = apply_filters('fktr_steps_setup_array', $steps);
		}
		return $steps;
	}
	/**
	* Static function default_request_values
	* @access public
	* @return Array of default values on GET requests.
	* @since 0.7
	*/
	public static function default_request_values() {
		$default_values = array(
							'action' => 'fktr_wizzard',
							'step' => 1
						);
		return $default_values;
	}
	/**
	* Static function post_actions
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function post_actions() {
		if (!wp_verify_nonce($_POST['_wpnonce'], 'fktr_wizzard_nonce' ) ) {
		    wp_die(__( 'Security check', FAKTURO_TEXT_DOMAIN )); 
		}
		
		if (!empty($_POST['step_action']) && is_numeric($_POST['step_action'])) {
			do_action('fktr_wizzard_action_'.$_POST['step_action']);
		} else {
			$output = ''.__('A problem please try again.').'<br/><br/> <a href="'.admin_url('admin-post.php?action=fktr_wizzard').'" class="button">'.__('Back.').'</a>';
			wp_die($output); 
		}
		$all_steps = self::get_steps();
		if (count($all_steps) > $_POST['step_action']) {
			//redirect to next steep
			wp_redirect(admin_url('admin-post.php?action=fktr_wizzard&step='.($_POST['step_action']+1)));
		} else {
			// redirect to welcome
		}
	}
	/**
	* Static function scripts
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function scripts() {
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-wizzard-main', FAKTURO_PLUGIN_URL . 'assets/js/wizzard_main.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-fktr-new-terms-popup', FAKTURO_PLUGIN_URL . 'assets/js/new-terms-popup.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		$steps = self::get_steps();
		$porcent_per_steep = ((100-count($steps))/count($steps));
		wp_localize_script('jquery-wizzard-main', 'backend_object',
			array('ajax_url' => admin_url( 'admin-ajax.php' ),
				'loading_states_text' => __('Loading countries and states...', FAKTURO_TEXT_DOMAIN ),
				'porcent_per_steep' => $porcent_per_steep,
				'loading_image' => admin_url('images/spinner.gif'), 
				'loading_states_text' => __('Loading states...', FAKTURO_TEXT_DOMAIN ),
				'ajax_nonce' => wp_create_nonce('fktr_wizzard_ajax_nonce'),
			)
		);

		if (self::$current_request['step'] == 1) {
			wp_enqueue_script( 'jquery-wizzard-countries-states', FAKTURO_PLUGIN_URL . 'assets/js/wizzard_countries_states.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		} 

		if (self::$current_request['step'] == 2) {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_script( 'jquery-settings', FAKTURO_PLUGIN_URL . 'assets/js/wizzard_company_info.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		} 
		
	}
	/**
	* Static function styles
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function styles() {
		wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
		wp_enqueue_style('fktr-new-terms-popup',FAKTURO_PLUGIN_URL .'assets/css/new-terms-popup.css');	
		if (self::$current_request['step'] == 2) {
			wp_enqueue_style('thickbox');
		}
		
	}
	/**
	* Static function page
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page() {
		self::$current_request = wp_parse_args($_GET, self::default_request_values());
		do_action('fktr_wizzard_install_step_'.self::$current_request['step']);
	}
	/**
	* Static function get_form
	* @access public
	* @return HTML of form and actions to work.
	* @since 0.7
	*/
	public static function get_form() {
		$ret = '<form method="post" action="'.admin_url('admin-post.php').'">
					'.wp_nonce_field('fktr_wizzard_nonce').'
					<input type="hidden" name="action" value="fktr_wizzard_post"/>
					<input type="hidden" name="step_action" value="'.self::$current_request['step'].'"/>';
		return $ret;
	}
	/**
	* Static function page_step_one
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_one() {
		
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
		$html_select_countries = '<select name="selected_country" id="selected_country">';
		foreach ($countries as $kc => $country) {
			$html_select_countries .= '<option value="' . $country[0] . '">' . esc_html($country[2]) . '</option>';
		}
		$html_select_countries .= '</select>';

		$print_html = '<h1>'.__('Load Countries and States', FAKTURO_TEXT_DOMAIN).'</h1>
					'.self::get_form().'
					<div id="content_step">
					<p>'.__('Do you want load all countries and states by default?', FAKTURO_TEXT_DOMAIN).'</p>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes" checked/></th>
							<td>
								'. __( 'Yes', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes_only_a_country"/></th>
							<td>
								'. __( 'Yes, but only a country.', FAKTURO_TEXT_DOMAIN ) .'
								<div id="container_select_countries" style="display:none;"> 
									'.$html_select_countries.'
								</div>
	                        </td>
	                    </tr>
	                    <tr valign="top">
	                        <th scope="row"><input type="radio" name="load_contries_states" value="no"/></th>
							<td>
								'. __( 'No', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                </table>
	                </div>
					<div id="buttons_container">
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Next', FAKTURO_TEXT_DOMAIN ) .'"/>
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=2').'" class="button button-large">'. __( 'Skip', FAKTURO_TEXT_DOMAIN ) .'</a>
					</div>
					</form>
					';
		self::ouput($print_html, __('Load Countries and States', FAKTURO_TEXT_DOMAIN));
		
	}
	/**
	* Static function load_countries_ajax
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function load_countries_ajax() {
		check_ajax_referer('fktr_wizzard_ajax_nonce', 'nonce');
		$country_id = 0;
		if (!empty($_REQUEST['country_id']) && is_numeric($_REQUEST['country_id'])) {
			$country_id = $_REQUEST['country_id'];
		}
		if (empty($country_id)) {
			wp_die('error');
		}
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
		$count_insert = 0;
		foreach ($countries as $kc => $country) {
			if ($country[0] != $country_id) {
				continue; 
			}
			$count_insert++;
			$termc = term_exists($country[2], 'fktr_countries');
			if ($termc !== 0 && $termc !== null) {
				// exist this term
			} else {
				// don't exist term
				$termc = wp_insert_term($country[2], 'fktr_countries');
				if (is_wp_error($termc)){
										
				}
				
				$country_term = $termc['term_id'];
				foreach ($states as $ks => $state) {
						
					if ($state[2] == $country[0]) {
						$count_insert++;
						if ($count_insert >= 100 ) {
							$count_insert = 0;
							wp_cache_flush();
						}
						$term_s = term_exists($state[1], 'fktr_countries', $country_term);
						if ($term_s !== 0 && $term_s !== null) {
								// exist state
						} else {
							$term_s = wp_insert_term($state[1], 'fktr_countries', array('parent' => $country_term));
							if (is_wp_error($term_s)){
							}
							$state_term = $term_s['term_id'];
						}
					}	
				}
			}
		}
		if ($country_id == count($countries)) {
			die('last_country');
		} else {
			die('not_last_country');
		}
	}
	/**
	* Static function action_one
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_one() {
		$load_contries_states = 'no';
		if (!empty($_POST['load_contries_states'])) {
			$load_contries_states = $_POST['load_contries_states'];
		}
		/*
		Activate only on ajax loading countries fail.
		if ($load_contries_states == 'yes') {
			require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
			$count_insert = 0;
			foreach ($countries as $kc => $country) {
				$count_insert++;
				$termc = term_exists($country[2], 'fktr_countries');
				if ($termc !== 0 && $termc !== null) {
					// exist this term
				} else {
					// don't exist term
					$termc = wp_insert_term($country[2], 'fktr_countries');
					if (is_wp_error($termc)){
										
					}
					error_log('insert country:'.$country[2]);
					$country_term = $termc['term_id'];
					foreach ($states as $ks => $state) {
						
						if ($state[2] == $country[0]) {
							$count_insert++;
							if ($count_insert >= 100 ) {
								$count_insert = 0;
							    wp_cache_flush();
							    error_log('wp_cache_flush');
							}
							$term_s = term_exists($state[1], 'fktr_countries', $country_term);
							if ($term_s !== 0 && $term_s !== null) {
								// exist state
							} else {
								$term_s = wp_insert_term($state[1], 'fktr_countries', array('parent' => $country_term));
								if (is_wp_error($term_s)){
									error_log($term->get_error_message());
								}
								error_log('insert:'.$state[1]);
								$state_term = $term_s['term_id'];
							}
						}

						
					}
				}
			}
		}
		*/
		
		
	}
	/**
	* Static function page_step_two
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_two() {

		$options = get_option('fakturo_info_options_group');
		if (empty($options['url'])) {
			$options['url'] = FAKTURO_PLUGIN_URL . 'assets/images/etruel-logo.png';
		}
		update_option('fakturo_info_options_group', $options);
		$selectCountry = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a country', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['country'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[country]',
			'id' 				 => 'fakturo_info_options_group_country',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a state', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $options['country'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['state'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[state]',
			'id' 				 => 'fakturo_info_options_group_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));

		$selectTaxCondition = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Tax Condition', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[tax_condition]',
			'class'              => '',
			'id'				 => 'fakturo_info_options_group_tax_condition',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		$print_html = '<h1>'.__('Company Info', FAKTURO_TEXT_DOMAIN).'</h1>
					'.self::get_form().'
					<table class="form-table">
						<tr valign="top">
							<th scope="row">'. __( 'Name', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[name]" value="'.$options['name'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Taxpayer ID', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" id="fakturo_info_options_group_taxpayer" name="fakturo_info_options_group[taxpayer]" value="'.$options['taxpayer'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Gross income tax ID', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[tax]" value="'.$options['tax'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Start of activities', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[start]" id="start" value="'.$options['start'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Address', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<textarea name="fakturo_info_options_group[address]" cols="36" rows="4">'.$options['address'].'</textarea>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Telephone', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[telephone]" value="'.$options['telephone'].'"/>
							</td>
	                    </tr>
						
						<tr valign="top">
							<th scope="row">'. __( 'Country', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								'.$selectCountry.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_countries',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_info_options_group_country',
																	)
																).'
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'State', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<table style="border-spacing: 0px;">
									<tr>
										<td id="td_select_state">
											'.$selectState.'  
										</td>
										<td>
											 '.fktr_popup_taxonomy::button(
																array(
																	'taxonomy' => 'fktr_countries',
																	'echo' => 0,
																	'class' => 'button',
																	'opcional_add_new_item'	=> __( 'Add New State', FAKTURO_TEXT_DOMAIN ),
																	'selector_parent_select' => '#fakturo_info_options_group_country',
																	'selector' => '#fakturo_info_options_group_state',
																)
															).'
									    </td>
									</tr>
								</table>
							</td>
	                    </tr>

	                    <tr valign="top">
							<th scope="row">'. __( 'City', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[city]" value="'.$options['city'].'"/>
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'Postcode', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[postcode]" value="'.$options['postcode'].'"/>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Website', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[website]" value="'.$options['website'].'"/>
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'Tax condition', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								'.$selectTaxCondition.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_tax_conditions',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_info_options_group_tax_condition',
																	)
																).'
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Company Logo', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<label for="upload_image">
									<input id="url" type="text" size="36" value="'.$options['url'].'" name="fakturo_info_options_group[url]" />
									<input id="upload_logo_button" class="button button-orange" type="button" value="Upload Image" />
									<br />'.__( 'Enter an URL or upload an image for the company logo.', FAKTURO_TEXT_DOMAIN ).'
								</label>
								
								<p style="padding-top: 5px;">'. __( 'This is your current logo', FAKTURO_TEXT_DOMAIN ) .'</p><img id="setting_img_log" src="'. $options['url'] .'" style="padding:5px;" />
							</td>
	                    </tr>
					</table>
					<div id="buttons_container">
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=1').'" class="button button-large">'. __( 'Previous', FAKTURO_TEXT_DOMAIN ) .'</a>	
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Next', FAKTURO_TEXT_DOMAIN ) .'"/>
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=3').'" class="button button-large">'. __( 'Skip', FAKTURO_TEXT_DOMAIN ) .'</a>
					</div>
					</form>
					';
		self::ouput($print_html, __('Company Info', FAKTURO_TEXT_DOMAIN));
		
	}
	/**
	* Static function action_two
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_two() {
		update_option('fakturo_info_options_group', $_POST['fakturo_info_options_group']);
	}
	/**
	* Static function page_step_three
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_three() {
		
		$print_html = '<h1>'.__('Load Currencies', FAKTURO_TEXT_DOMAIN).'</h1>
					'.self::get_form().'
					<p>Do you want load all currencies by default?</p>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes" checked/></th>
							<td>
								'. __( 'Yes', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes_only_a_country"/></th>
							<td>
								'. __( 'Yes, but only a currency.', FAKTURO_TEXT_DOMAIN ) .'
								<div id="container_select_countries" style="display:none;"> 
									
								</div>
	                        </td>
	                    </tr>
	                    <tr valign="top">
	                        <th scope="row"><input type="radio" name="load_contries_states" value="no"/></th>
							<td>
								'. __( 'No', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                </table>
					<div id="buttons_container">
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=2').'" class="button button-large">'. __( 'Previous', FAKTURO_TEXT_DOMAIN ) .'</a>	
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Next', FAKTURO_TEXT_DOMAIN ) .'"/>
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=4').'" class="button button-large">'. __( 'Skip', FAKTURO_TEXT_DOMAIN ) .'</a>
					</div>
					</form>
					';
		self::ouput($print_html, __('Load Currencies', FAKTURO_TEXT_DOMAIN));
	
	}
	/**
	* Static function page_step_four
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_four() {
		
		$print_html = '<h1>'.__('Step Four', FAKTURO_TEXT_DOMAIN).'</h1>
					'.self::get_form().'
					<p>Install content</p>
					<div id="buttons_container">
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=3').'" class="button button-large">'. __( 'Previous', FAKTURO_TEXT_DOMAIN ) .'</a>	
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Next', FAKTURO_TEXT_DOMAIN ) .'"/>
						<a href="'.admin_url('admin-post.php?action=fktr_wizzard&step=5').'" class="button button-large">'. __( 'Skip', FAKTURO_TEXT_DOMAIN ) .'</a>
					</div>
					</form>
					';
		self::ouput($print_html, __('Company Info', FAKTURO_TEXT_DOMAIN));
	
	}
	/**
	* Static function ouput
	* @access public
	* @param string $message Ouput message or WP_Error object.
	* @param string          $title   Optional. Output title. Default empty.
	* @param string|array    $args    Optional. Arguments to control behavior. Default empty array.
	* @return void
	* @since 0.7
	*/
	public static function ouput($message, $title = '', $args = array()) {
		$defaults = array( 'response' => 200 );
		$r = wp_parse_args($args, $defaults);

		$have_gettext = function_exists('__');

		$steps = self::get_steps();
		$porcent_per_steep = ((100-count($steps))/count($steps));
		if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
			if ( empty( $title ) ) {
				$error_data = $message->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['title'] ) )
					$title = $error_data['title'];
			}
			$errors = $message->get_error_messages();
			switch ( count( $errors ) ) {
			case 0 :
				$message = '';
				break;
			case 1 :
				$message = "<p>{$errors[0]}</p>";
				break;
			default :
				$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
				break;
			}
		} elseif ( is_string( $message ) ) {
			$message = "<p>$message</p>";
		}

		if ( isset( $r['back_link'] ) && $r['back_link'] ) {
			$back_text = $have_gettext? __('&laquo; Back') : '&laquo; Back';
			$message .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
		}

		if ( ! did_action( 'admin_head' ) ) :
			if ( !headers_sent() ) {
				status_header( $r['response'] );
				nocache_headers();
				header( 'Content-Type: text/html; charset=utf-8' );
			}

			if ( empty($title) )
				$title = $have_gettext ? __('WordPress &rsaquo; Error') : 'WordPress &rsaquo; Error';

			$text_direction = 'ltr';
			if ( isset($r['text_direction']) && 'rtl' == $r['text_direction'] )
				$text_direction = 'rtl';
			elseif ( function_exists( 'is_rtl' ) && is_rtl() )
				$text_direction = 'rtl';
	?>
	<!DOCTYPE html>
	<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
	-->
	<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width">
		<?php
		if ( function_exists( 'wp_no_robots' ) ) {
			wp_no_robots();
		}
		?>
		<title><?php echo $title ?></title>
		<style type="text/css">
			html {
				background: #f1f1f1;
			}
			input[type=text], input[type=search], input[type=radio], input[type=tel], input[type=time], input[type=url], input[type=week], input[type=password], input[type=checkbox], input[type=color], input[type=date], input[type=datetime], input[type=datetime-local], input[type=email], input[type=month], input[type=number], select, textarea {
			    border: 1px solid #ddd;
			    -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
			    box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
			    background-color: #fff;
			    color: #32373c;
			    outline: 0;
			    -webkit-transition: 50ms border-color ease-in-out;
			    transition: 50ms border-color ease-in-out;
			}
			input, select {
			    margin: 1px;
			    padding: 3px 5px;
			}
			input, select, textarea {
			    font-size: 14px;
			    -webkit-border-radius: 0;
			    border-radius: 0;
			}
			input, textarea {
			    box-sizing: border-box;
			   
			}
			input[type=text], textarea, select {
				width: 300px;
			}
			
			#error-page {
				background: #fff;
				color: #444;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 2em auto;
				padding: 1em 2em;
				min-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				margin-top: 20px;
				margin-left: 60px;
				margin-right: 60px;
			}
			
			#error-page p {
				font-size: 14px;
				line-height: 1.5;
				margin: 25px 0 20px;
			}
			#error-page code {
				font-family: Consolas, Monaco, monospace;
			}
			#icon-header {
				margin: 0px auto;
				width: 100px;
				height: 100px;
				display: block;
			}
			.stepwizard-row {
				background: #fff;
				height: 4px;
				margin: 0px auto;
				margin-top: 20px;
				min-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				background-image: -webkit-linear-gradient(top,#626161 0,#b9b8b7 100%);
				background-image: linear-gradient(to bottom,#626161 0,#b9b8b7 100%);
				margin-left: 60px;
				margin-right: 60px;
			}
			.stepwizard-row-bar {
				background-color: #f7b63e;
				width: 40%;
				height: 100%;
				background-image: -webkit-linear-gradient(top,#f7b63e 0,#816414 100%);
				background-image: linear-gradient(to bottom,#f7b63e 0,#816414 100%);
			}
			.wizard-steps-circles {
				height: auto;
				margin: 0px auto;
				min-width: 700px;
				margin-left: 60px;
				margin-right: 60px;
			}
			.wizard-steps-circles div {
				width: 20px;
    			height: 20px;
                border-radius: 10px;
                float: left;
                text-align: center;
                color: #333;
    			background-color: #fff;
    			border-color: #ccc;
    			line-height: 1.428571429;
    			margin-top: -12px;
    			opacity: 0.7;
    			margin-right: <?php echo $porcent_per_steep; ?>%;;
    			margin-left: -12px;
    			border: 1px solid #4d5154;
			}
			.wizard-steps-circles div.active {
				width: 20px;
    			height: 20px;
                border-radius: 10px;
                float: left;
                text-align: center;
                color: #fff;
    			background-color: #f7b63e;
    		    background-image: -webkit-linear-gradient(top,#f7b63e 0,#816414 100%);
    			background-image: linear-gradient(to bottom,#f7b63e 0,#816414 100%);
    			line-height: 1.428571429;
    			margin-top: -12px;
    			margin-right: <?php echo $porcent_per_steep; ?>%;
    			opacity: 1;
    			border: 1px solid #4d5154;
			}
			.wizard-steps-circles div:last-child {
				margin-right: 0px;
			}
			.wizard-steps-circles div:first-child {
				margin-left: 0px;
			}

			.wizard-steps {
				height: auto;
				margin: 0px auto;
				min-width: 700px;
				margin-left: 60px;
				margin-right: 60px;
				clear: both;
			}
			.wizard-steps div {
				display: inline-block;
				text-align: left;
				width: 19%;
				visibility: hidden;
			}
			.wizard-steps div.active {
				display: inline-block;
				text-align: left;
				width: 19%;
				visibility:visible;
			}
			
			h1 {
				border-bottom: 1px solid #dadada;
				clear: both;
				color: #666;
				font-size: 24px;
				margin: 30px 0 0 0;
				padding: 0;
				padding-bottom: 7px;
			}
			
			ul li {
				margin-bottom: 10px;
				font-size: 14px ;
			}
			a {
				color: #0073aa;
			}
			a:hover,
			a:active {
				color: #00a0d2;
			}
			a:focus {
				color: #124964;
			    -webkit-box-shadow:
			    	0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
			    box-shadow:
			    	0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
				outline: none;
			}
			#buttons_container {
				text-align: right;
			}
			.button-large {
				line-height: 30px;
    			height: 32px;
    			padding: 0 20px 1px;
			}
			.button {
				background: #f7f7f7;
				border: 1px solid #ccc;
				color: #555;
				display: inline-block;
				text-decoration: none;
				font-size: 13px;
				line-height: 26px;
				height: 28px;
				margin: 0;
				padding: 0 10px 1px;
				cursor: pointer;
				-webkit-border-radius: 3px;
				-webkit-appearance: none;
				border-radius: 3px;
				white-space: nowrap;
				-webkit-box-sizing: border-box;
				-moz-box-sizing:    border-box;
				box-sizing:         border-box;

				-webkit-box-shadow: 0 1px 0 #ccc;
				box-shadow: 0 1px 0 #ccc;
			 	vertical-align: top;
			}

			.button-orange {
				background: #f2b340;
    			border: 1px solid #bd8827;
    			color: #fffcfc;
			}
			.button-orange:hover {
				background: #f2db40 !important;
				color: #fffcfc !important;
			}
			.button-orange:focus {
				background: #bd8827 !important;
				color: #fffcfc !important;
			}
			

			.button-large {
				height: 32px;
    			line-height: 30px;
    			padding: 0 20px 2px;
			}

			.button:hover,
			.button:focus {
				background: #fafafa;
				border-color: #999;
				color: #23282d;
			}

			.button:focus  {
				border-color: #5b9dd9;
				-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				outline: none;
			}

			.button:active {
				background: #eee;
				border-color: #999;
			 	-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 	-webkit-transform: translateY(1px);
			 	-ms-transform: translateY(1px);
			 	transform: translateY(1px);
			}

			<?php
			if ( 'rtl' == $text_direction ) {
				echo 'body { font-family: Tahoma, Arial; }';
			}
			?>
		</style>
		<?php
			do_action('fktr_wizzard_output_print_styles');
			wp_print_styles();
			do_action('fktr_wizzard_output_print_scripts');
			wp_print_scripts();
		?>
	</head>
	<body>

	<?php endif; // ! did_action( 'admin_head' ) ?>
		<img id="icon-header" src="<?php echo FAKTURO_PLUGIN_URL.'assets/images/icon-256x256.png'; ?>"/>
		<div class="stepwizard-row">
			<div class="stepwizard-row-bar" style="width:<?php echo (self::$current_request['step']-1)*($porcent_per_steep+1); ?>%">
			</div>
		</div>
		<div class="wizard-steps-circles">
			<?php 
				foreach (self::get_steps() as $k => $st) {
					$key_step = $k+1;
					$class = '';
					if ($key_step <= self::$current_request['step']) {
						$class = ' class="active"';
					}
					echo '<div'.$class.'>'.$key_step.'</div>';
				}
			?>
      	</div>
		<div class="wizard-steps">
			<?php 
				foreach (self::get_steps() as $k => $st) {
					$key_step = $k+1;
					$class = '';
					if ($key_step == self::$current_request['step']) {
						$class = ' class="active"';
					}
					echo '<div'.$class.' style="width:'.$porcent_per_steep.'%">'.$st.'</div>';
				}
			?>
        	
      	</div>
		<div id="error-page">
			
			
			<?php echo $message; ?>
		</div>
		
	</body>
	</html>
	<?php
	die();

	}

}
fktr_wizzard::hooks();
?>