<?php
/**
 * Fakturo description of Help Texts Array
 * -------------------------------
 * array('Text for left tab link' => array(
 * 	'field_name' => array( 
 * 		'title' => 'Text showed as bold in right side' , 
 * 		'tip' => 'Text html shown below the title in right side and also can be used for mouse over tips.' , 
 * 		'plustip' => 'Text html added below "tip" in right side in a new paragraph.',
 * )));
 */

$helptexts = array( 
	'PRODUCTS' => array( 
		'tabtitle' =>  __('Product Types', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo' ),
			'tip' => __('Main form to add product types that will be assigned in the product register form. To add a new product type, just click on the <b>"Add New Product Type"</b> button and fill in the fields. Upon saving it, the list will be found on the right side of the form.', 'fakturo' ),
		),	
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo'))
	),
	);

?>