<?php


// ******************************************************************
// This is the standard TypoScript calendar table, tt_calender
// ******************************************************************
$TCA['tt_calender'] = Array (
	'ctrl' => $TCA['tt_calender']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'type,date,title,note,category,responsible,workgroup,time,week,hidden,starttime,endtime'
	),
	'columns' => Array (	
		'starttime' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'hidden' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
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
		'type' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.type',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender.type.I.0', 0),
					Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender.type.I.1', 1)
				),
				'default' => 0
			)
		),
		'date' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.date',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'date',
				'default' => '0'
			)
		),
		'time' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.time',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'time',
				'default' => '0'
			)
		),
		'week' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.week',
			'config' => Array (
				'type' => 'input',
				'size' => '6',
				'max' => '6',
				'eval' => 'int',
				'range' => Array (
					'upper' => 52,
					'lower' => 1
				),
				'default' => '0'
			)
		),
		'datetext' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.datetext',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'complete' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.complete',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'workgroup' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.workgroup',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'priority' => Array (
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.priority',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender.priority.I.0', 1),
					Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender.priority.I.1', 3),
					Array('LLL:EXT:tt_calender/locallang_tca.php:tt_calender.priority.I.2', 5)
				),
				'default' => 3
			)
		),
		'responsible' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.responsible',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'tt_address',
				'size' => '3',
				'eval' => 'trim',
				'maxitems' => '6',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'category' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.category',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0)
				),
				'foreign_table' => 'tt_calender_cat'
			)
		),
		'link' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:tt_calender/locallang_tca.php:tt_calender.link',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '5',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		)
	),
	'types' => Array (	
		'0' => Array('showitem' => 'type;;;;1-1-1, hidden, date;;2;;3-3-3, title, note, category, --div--, datetext;;;;5-5-5, link'),
		'1' => Array('showitem' => 'type;;;;1-1-1, hidden, date;;2;;3-3-3, title, note, category, --div--, complete;;;;5-5-5, priority, workgroup, responsible, link')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, endtime'),
		'2' => Array('showitem' => 'week,time')	//, month
	)
);




// ******************************************************************
// This is the standard TypoScript calendar category table, tt_calender_cat
// ******************************************************************
$TCA['tt_calender_cat'] = Array (
	'ctrl' => $TCA['tt_calender_cat']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title'
	),
	'columns' => Array (	
		'title' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		)
	),
	'types' => Array (	
		'0' => Array('showitem' => 'title;;;;3-3-3')
	)
);





?>