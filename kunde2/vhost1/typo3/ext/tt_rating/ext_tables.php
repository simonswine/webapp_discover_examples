<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tt_rating'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'default_sortby' => 'ORDER BY title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'crdate' => 'crdate',
		'enablecolumns' => Array (
			'disabled' => 'hidden'
		),
		'title' => 'LLL:EXT:tt_rating/locallang_tca.php:tt_rating',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif'
	),
	'interface' => Array (
		'showRecordFieldList' => 'title,description,hidden,rating,votes'
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
		'description' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '5'
			)
		),
		'rating' => Array (
			'label' => 'LLL:EXT:tt_rating/locallang_tca.php:tt_rating.rating',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'trim,double2',
				'default' => '0'
			)
		),
		'votes' => Array (
			'label' => 'LLL:EXT:tt_rating/locallang_tca.php:tt_rating.votes',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '12',
				'eval' => 'int',
				'default' => 0
			)
		),
		'ratingstat' => Array (
			'label' => 'LLL:EXT:tt_rating/locallang_tca.php:tt_rating.ratingstat',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		),
		'recordlink' => Array (
			'label' => 'LLL:EXT:tt_rating/locallang_tca.php:tt_rating.recordlink',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => $TCA['tt_content']['columns']['records']['config']['allowed'],
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
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
		'1' => Array('showitem' => 'hidden, title, description, rating, votes, recordlink')
	)
);

t3lib_extMgm::allowTableOnStandardPages('tt_rating');
t3lib_extMgm::addToInsertRecords('tt_rating');
t3lib_extMgm::addPlugin(Array('LLL:EXT:tt_rating/locallang_tca.php:pi', '8'));
if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_ttrating_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_ttrating_wizicon.php';
?>