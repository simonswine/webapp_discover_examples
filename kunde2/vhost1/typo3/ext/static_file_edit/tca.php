<?php

// ******************************************************************
// Static file edit table
// ******************************************************************
$TCA['sys_staticfile_edit'] = Array (
	'ctrl' => $TCA['sys_staticfile_edit']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => ''
	),
	'columns' => Array (	
		'edit_file' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit.edit_file',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim,required',
				'max' => '256'
			)
		),
		'edit_content' => Array (
			'label' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit.edit_content',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '15',
			),
			'defaultExtras' => '
					richtext [*] :
					rte_transform [ flag=rte_enabled | mode=ts_images-ts_reglinks ] :
					static_write [edit_file|edit_content|edit_subpart_marker|always_reload|update_status]
				'
		),
		'edit_subpart_marker' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit.edit_subpart_marker',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'always_reload' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit.always_reload',
			'config' => Array (
				'type' => 'check'
			)
		),
		'rte_enabled' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disableRTE',
			'config' => Array (
				'type' => 'check',
				'showIfRTE' => 1
			)
		),
		'update_status' => Array (
			'label' => 'LLL:EXT:static_file_edit/locallang_tca.php:sys_staticfile_edit.update_status',
			'config' => Array ('type' => 'none')
		)
	),
	'types' => Array (
		'0' => Array('showitem' => 'edit_file;;1;;2-2-2,
			edit_content;;;nowrap;3-3-3,
			rte_enabled,update_status;;;;5-5-5')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'edit_subpart_marker,always_reload')
	)
);


?>
