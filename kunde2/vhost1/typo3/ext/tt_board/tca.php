<?php


// ******************************************************************
// This is the standard TypoScript Board table, tt_board
// ******************************************************************
$TCA['tt_board'] = Array (
	'ctrl' => $TCA['tt_board']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'subject,author,email,message'
	),
	'columns' => Array (	
		'subject' => Array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.subject',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		),
		'message' => Array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.message',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',	
				'rows' => '5'
			)
		),
		'author' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.author',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'email' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'hidden' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check'
			)
		),
		'parent' => Array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.parent',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'tt_board',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'notify_me' => Array (
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.notify_me',
			'config' => Array (
				'type' => 'check'
			)
		),
		'crdate' => Array (		// This field is by default filled with creation date. See tt_board 'ctrl' section
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_board/locallang_tca.php:tt_board.crdate',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime'
			)
		)
	),
	'types' => Array (	
		'0' => Array('showitem' => 'hidden;;;;1-1-1, crdate, subject;;;;3-3-3, message, author, email, parent;;;;5-5-5, notify_me')
	)
);
?>