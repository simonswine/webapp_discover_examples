<?php
/**
* Default  TCA_DESCR for "sys_staticfile_edit"
*/

$LOCAL_LANG = Array (
	'default' => Array (
		'.description' => 'Represents actual static HTML files on the server which has editable parts in them. Through these records you edit those parts of content.',
		'.details' => 'When you create a record in this table, you must enter a filename from a certain path relative to the path of the site root. By default the files must be located in the fileadmin/static/ folder.
The main content field of this record is used to edit the content of a certain section in the HTML file defined for the record. The section is delimited by two markers (default markers are \'###TYPO3_STATICFILE_EDIT###\') and then the record content is saved to the database, it\'s also written to the file, substituting the original content between the markers.
The fact that the content is also saved to the database plays no role. The important thing is that the file is updated.',
		'_.seeAlso' => 'sys_staticfile_edit:edit_file
sys_staticfile_edit:edit_subpart_marker
sys_staticfile_edit:edit_content
',
		'edit_file.description' => 'Filename of the static file being edited.',
		'edit_file.details' => 'The filename should be relative to the path configured in $TYPO3_CONF_VARS[BE][staticFileEditPath]. This path is by default \'fileadmin/static/\'.
You may enter a subpath, eg. "mainfiles/scheme.html" which will in turn be a request for the file "fileadmin/static/mainfiles/scheme.html".',
		'_edit_file.seeAlso' => 'sys_staticfile_edit:edit_content
sys_staticfile_edit:edit_subpart_marker',
		'edit_content.description' => 'This is the content from the section in the static file. Edit, save, and the static file is updated instantly.',
		'_edit_content.seeAlso' => 'sys_staticfile_edit:edit_file
sys_staticfile_edit:always_reload
sys_staticfile_edit:update_status
',
		'edit_subpart_marker.description' => 'Alternative subpart marker.',
		'edit_subpart_marker.details' => 'By default the content edited in the HTML-file is encapsulated in so called markers. You may specify a marker within an HTML comment tag which is definitely the most smart thing to do, because your marker will not be visible then.
The default marker is \'###TYPO3_STATICFILE_EDIT###\' which means that your HTML-file should have at least one section that looks in principle like this:

&lt;!-- ###TYPO3_STATICFILE_EDIT### begin --&gt;

&lt;strong&gt;Hello World&lt;/strong&gt;

&lt;!-- ###TYPO3_STATICFILE_EDIT### end --&gt;

The content \'Hello world\' with tags is the content being edited by this record and it\'s the markers that are responsible for defining this area!

So if you specify the value BUTTERFLY as the value of this field, your subpart markers should look like:

&lt;!-- BUTTERFLY begin --&gt;

&lt;strong&gt;Hello World&lt;/strong&gt;

&lt;!-- BUTTERFLY end --&gt;

<b>Notice:</b> As you might have realized, it\'s important to pick a subpart marker which does NOT by any chance appear in the bodytext field. BUTTERFLY is not a good example. As string like ###NONSENSE_kdo8u3ksi### is far better.

The reason why you can specify this alternative marker is if you for instance wants to define more than one editable section in an HTML-file. Then you\'ll have to define it with separate markers.
',
		'always_reload.description' => 'If checked, the content being edited will always be reloaded from the actual file and not the record.',
		'always_reload.details' => 'By this you are guarantee that if somebody else has edited the file externally, you\'ll not override those changes but rather continue working on them.',
		'_always_reload.seeAlso' => 'sys_staticfile_edit:edit_content',
		'update_status.description' => 'Contains the latest status of the file update.',
		'update_status.details' => 'This field is not editable. It just contains an update message which lets you know how well the operation went.',
		'rte_enabled.description' => 'If checked, the Rich Text Editor will be disabled in this record.',
		'_rte_enabled.seeAlso' => 'tt_content:rte_enabled',
	),
);
?>