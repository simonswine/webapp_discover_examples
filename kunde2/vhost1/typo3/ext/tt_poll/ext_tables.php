<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tt_poll'] = Array (
	'ctrl' => Array (
		'label' => 'title',
		'default_sortby' => 'ORDER BY title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'crdate' => 'crdate',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime'
		),
		'title' => 'LLL:EXT:tt_poll/locallang_tca.php:tt_poll',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif'
	),
	'interface' => Array (
		'showRecordFieldList' => 'title,question,hidden,votes'
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
		'question' => Array (
			'label' => 'LLL:EXT:tt_poll/locallang_tca.php:tt_poll.question',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '2'
			)
		),
		'answers' => Array (
			'label' => 'LLL:EXT:tt_poll/locallang_tca.php:tt_poll.answers',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '5'
			)
		),
		'votes' => Array (
			'label' => 'LLL:EXT:tt_poll/locallang_tca.php:tt_poll.votes',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '12',
				'eval' => 'int',
				'default' => 0
			)
		),
		'recordlink' => Array (
			'label' => 'LLL:EXT:tt_poll/locallang_tca.php:tt_poll.recordlink',
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
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
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
				'size' => '7',
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
		)
	),
	'types' => Array (
		'1' => Array('showitem' => 'hidden, title, question, answers, votes, starttime, endtime, recordlink')
	)
);

t3lib_extMgm::allowTableOnStandardPages('tt_poll');
t3lib_extMgm::addToInsertRecords('tt_poll');
?>