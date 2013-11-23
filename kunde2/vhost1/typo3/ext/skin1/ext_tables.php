<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


if (TYPO3_MODE=='BE')	{
	/**
	 * Setting up backend styles and colors
	 */
	$TBE_STYLES = array(
		'colorschemes' => Array (
			'0' => '#F7F7F3,#E3E3DF,#EDEDE9',  	// Default. Always used on main-palettes in the bottom of the forms
			'1' => '#94A19A,#7C8D84,#7C8D84',	// Typically hidden, type and other primary 'meta' fields
			'2' => '#E4D69E,#E7DBA8,#E9DEAF',	// For headers
			'3' => '#C2BFC0,#C7C5C5,#C7C5C5',	// For main content
			'4' => '#B2B5C3,#C4C6D1,#D5D7DE',	// For extra content, like images, files etc.
			'5' => '#C3B2B5,#D1C4C6,#DED5D7'	// For special content
		),
		'styleschemes' => Array (
			'0' => array('all'=>'background-color: #F7F7F3;border:#7C8D84 solid 1px;', 'check'=>''),
			'1' => array('all'=>'background-color: #94A19A;border:#7C8D84 solid 1px;', 'check'=>''),
			'2' => array('all'=>'background-color: #E4D69E;border:#7C8D84 solid 1px;', 'check'=>''),
			'3' => array('all'=>'background-color: #C2BFC0;border:#7C8D84 solid 1px;', 'check'=>''),
			'4' => array('all'=>'background-color: #B2B5C3;border:#7C8D84 solid 1px;', 'check'=>''),
			'5' => array('all'=>'background-color: #C3B2B5;border:#7C8D84 solid 1px;', 'check'=>''),
		),
		'borderschemes' => Array (
			'0' => array('border:solid 1px black;',5),
			'1' => array('border:solid 1px black;',5),
			'2' => array('border:solid 1px black;',5),
			'3' => array('border:solid 1px black;',5),
			'4' => array('border:solid 1px black;',5),
			'5' => array('border:solid 1px black;',5)
		),
		'mainColors' => Array (	// Always use #xxxxxx color definitions!
			'bgColor' => '#F7F7F3',		// Light background color
			'bgColor2' => '#7F9080',		// Steel-blue
			'bgColor3' => '#F0EDDE',		// dok.color
			'bgColor4' => '#E5E5DB',		// light tablerow background, brownish
			'bgColor5' => '#CBCAC3',		// light tablerow background, greenish
			'bgColor6' => '#E1D5B3',		// light tablerow background, yellowish, for section headers. Light.
			'hoverColor' => '#800000'
		),
		'background' => t3lib_extMgm::extRelPath($_EXTKEY).'background.gif',	// Background image generally in the backend
		'logo' => t3lib_extMgm::extRelPath($_EXTKEY).'the_logo_image.gif',	// Logo in alternative backend, top left: 129x32 pixels
		'logo_login' => t3lib_extMgm::extRelPath($_EXTKEY).'login_logo_image.gif'	// Login-logo: 333x63 pixels
	);
}
?>