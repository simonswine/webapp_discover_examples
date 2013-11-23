<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * Adding the 'table' button to the RTE by default 
 * and configure the transformation engine to NOT dissolve the tables.
 *
 * If at any point the RTE.default object is cleared (eg 'RTE.default > ') in the Page TSconfig
 * this will not work!
 */

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:

if ($_EXTCONF['en'] && is_array($_EXTCONF['en.']))	{
	t3lib_extMgm::addPageTSConfig('
	
	RTE.default {
	'.($_EXTCONF['en.']['tables']?'  proc.preserveTables = 1':'').'
	  showButtons = '.($_EXTCONF['en.']['extended']?'cut,copy,paste,fontstyle,fontsize,textcolor':'').','.($_EXTCONF['en.']['tables']?'table':'').'
	  hideButtons = '.($_EXTCONF['en.']['extended']?'class,user,chMode':'').'
	}
	');
}
?>