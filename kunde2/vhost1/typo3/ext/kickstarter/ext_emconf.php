<?php

########################################################################
# Extension Manager/Repository config file for ext: "kickstarter"
# 
# Auto generated 20-09-2004 12:03
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'Extension Kickstarter',
	'description' => 'Creates a framework for a new extension',
	'category' => 'be',
	'author' => 'Daniel Bruen',
	'author_email' => 'dbruen@saltation.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'private' => '',
	'download_password' => '',
	'version' => '0.0.1',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:109:{s:9:"ChangeLog";s:4:"7aad";s:12:"ext_icon.gif";s:4:"1bf7";s:14:"ext_tables.php";s:4:"b3c3";s:15:"ext_tables.php~";s:4:"bc26";s:16:"locallang_db.php";s:4:"bbca";s:11:"CVS/Entries";s:4:"89b0";s:14:"CVS/Repository";s:4:"496f";s:8:"CVS/Root";s:4:"a141";s:14:"icons/bold.png";s:4:"aff1";s:16:"icons/center.png";s:4:"782e";s:15:"icons/class.png";s:4:"0975";s:14:"icons/copy.png";s:4:"430b";s:13:"icons/cut.png";s:4:"1af3";s:18:"icons/emoticon.png";s:4:"928c";s:18:"icons/fontsize.png";s:4:"8efa";s:19:"icons/fontstyle.png";s:4:"cc24";s:21:"icons/formatblock.png";s:4:"9f63";s:15:"icons/image.png";s:4:"8d35";s:16:"icons/indent.png";s:4:"782f";s:16:"icons/italic.png";s:4:"c91b";s:14:"icons/left.png";s:4:"5b83";s:14:"icons/line.png";s:4:"7885";s:14:"icons/link.png";s:4:"fa8a";s:21:"icons/orderedlist.png";s:4:"f658";s:17:"icons/outdent.png";s:4:"8890";s:15:"icons/paste.png";s:4:"f4cb";s:15:"icons/right.png";s:4:"e998";s:15:"icons/table.png";s:4:"5f9c";s:19:"icons/textcolor.png";s:4:"3988";s:19:"icons/underline.png";s:4:"a0ad";s:23:"icons/unorderedlist.png";s:4:"cdef";s:14:"icons/user.png";s:4:"8ae5";s:17:"icons/CVS/Entries";s:4:"f9c5";s:20:"icons/CVS/Repository";s:4:"745c";s:14:"icons/CVS/Root";s:4:"a141";s:46:"modfunc1/class.tx_kickstarter_compilefiles.php";s:4:"7699";s:47:"modfunc1/class.tx_kickstarter_compilefiles.php~";s:4:"e7fb";s:42:"modfunc1/class.tx_kickstarter_modfunc1.php";s:4:"7abd";s:43:"modfunc1/class.tx_kickstarter_modfunc1.php~";s:4:"af6e";s:40:"modfunc1/class.tx_kickstarter_wizard.php";s:4:"2b5c";s:41:"modfunc1/class.tx_kickstarter_wizard.php~";s:4:"00f2";s:22:"modfunc1/locallang.php";s:4:"1e3d";s:20:"modfunc1/CVS/Entries";s:4:"75b5";s:23:"modfunc1/CVS/Repository";s:4:"0afd";s:17:"modfunc1/CVS/Root";s:4:"a141";s:13:"res/clear.gif";s:4:"cc11";s:10:"res/cm.png";s:4:"df60";s:15:"res/default.gif";s:4:"475a";s:21:"res/default_black.gif";s:4:"355b";s:20:"res/default_blue.gif";s:4:"4ad7";s:21:"res/default_gray4.gif";s:4:"a25c";s:21:"res/default_green.gif";s:4:"1e24";s:22:"res/default_purple.gif";s:4:"78eb";s:19:"res/default_red.gif";s:4:"dc05";s:22:"res/default_yellow.gif";s:4:"401f";s:14:"res/module.png";s:4:"9c10";s:23:"res/modulefunc_func.png";s:4:"af99";s:23:"res/modulefunc_task.png";s:4:"5667";s:16:"res/notfound.gif";s:4:"1bdc";s:23:"res/notfound_module.gif";s:4:"8074";s:13:"res/pi_ce.png";s:4:"6ac3";s:16:"res/pi_cewiz.png";s:4:"57db";s:17:"res/pi_header.png";s:4:"0d49";s:23:"res/pi_menu_sitemap.png";s:4:"fbfc";s:13:"res/pi_pi.png";s:4:"01e2";s:18:"res/pi_textbox.png";s:4:"ed57";s:17:"res/t_check10.png";s:4:"1d11";s:16:"res/t_check4.png";s:4:"d094";s:14:"res/t_date.png";s:4:"0c8b";s:18:"res/t_datetime.png";s:4:"1726";s:18:"res/t_file_all.png";s:4:"9018";s:18:"res/t_file_img.png";s:4:"8eed";s:19:"res/t_file_size.png";s:4:"f082";s:20:"res/t_file_thumb.png";s:4:"56a1";s:18:"res/t_file_web.png";s:4:"e722";s:21:"res/t_flag_access.png";s:4:"23a4";s:22:"res/t_flag_endtime.png";s:4:"2dfc";s:21:"res/t_flag_hidden.png";s:4:"a0ce";s:24:"res/t_flag_starttime.png";s:4:"dd68";s:15:"res/t_input.png";s:4:"c430";s:21:"res/t_input_check.png";s:4:"712e";s:24:"res/t_input_colorwiz.png";s:4:"2a07";s:20:"res/t_input_link.png";s:4:"0ca9";s:21:"res/t_input_link2.png";s:4:"15ad";s:24:"res/t_input_password.png";s:4:"d51a";s:24:"res/t_input_required.png";s:4:"3b9f";s:17:"res/t_integer.png";s:4:"537b";s:14:"res/t_link.png";s:4:"a333";s:15:"res/t_radio.png";s:4:"eb1e";s:19:"res/t_rel_group.png";s:4:"6d4e";s:18:"res/t_rel_sel1.png";s:4:"6a9e";s:22:"res/t_rel_selmulti.png";s:4:"5bdb";s:18:"res/t_rel_selx.png";s:4:"810e";s:21:"res/t_rel_wizards.png";s:4:"9d71";s:13:"res/t_rte.png";s:4:"200b";s:14:"res/t_rte2.png";s:4:"d27c";s:19:"res/t_rte_class.png";s:4:"786e";s:19:"res/t_rte_color.png";s:4:"0d25";s:25:"res/t_rte_colorpicker.png";s:4:"b69e";s:24:"res/t_rte_fullscreen.png";s:4:"f043";s:20:"res/t_rte_hideHx.png";s:4:"c67d";s:13:"res/t_sel.png";s:4:"c49b";s:22:"res/t_select_icons.png";s:4:"24c7";s:18:"res/t_textarea.png";s:4:"1212";s:22:"res/t_textarea_wiz.png";s:4:"cf7b";s:11:"res/wiz.gif";s:4:"02b6";s:15:"res/CVS/Entries";s:4:"6057";s:18:"res/CVS/Repository";s:4:"4e90";s:12:"res/CVS/Root";s:4:"a141";}',
);

?>