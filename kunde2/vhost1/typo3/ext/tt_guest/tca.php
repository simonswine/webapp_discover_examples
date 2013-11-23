<?php


// ******************************************************************
// This is the standard TypoScript guestbook
// ******************************************************************
$TCA['tt_guest'] = Array (
	'ctrl' => $TCA['tt_guest']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,cr_name,cr_email,note,www,hidden'
	),
	'columns' => Array (	
		'title' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		),
		'note' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.note',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',	
				'rows' => '5'
			)
		),
		'cr_name' => Array (
			'label' => 'LLL:EXT:tt_guest/locallang_tca.php:tt_guest.cr_name',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'cr_email' => Array (
			'label' => 'LLL:EXT:tt_guest/locallang_tca.php:tt_guest.cr_email',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'www' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.www',
			'config' => Array (
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '256'
			)
		),
		'hidden' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		)
	),
	'types' => Array (	
		'0' => Array('showitem' => 'hidden;;;;1-1-1, title;;;;3-3-3, note, cr_name, cr_email, www')
	)
);
?>