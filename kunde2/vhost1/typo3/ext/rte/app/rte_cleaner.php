<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license 
*  from the author is found in LICENSE.txt distributed with these scripts.
*
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * EXAMPLE SCRIPT! Simply strips HTML of content from RTE
 * 
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   66: class SC_rte_cleaner 
 *   74:     function init()	
 *  102:     function main()	
 *  122:     function printContent()	
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');




/**
 * Script Class
 * 
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_rte
 */
class SC_rte_cleaner {
	var $content;
	var $siteURL;
	var $doc;	
	
	/**
	 * Initialize.
	 * 
	 * @return	void
	 */
	function init()	{
		global $BACK_PATH;

		$this->siteURL = substr(t3lib_div::getIndpEnv('TYPO3_SITE_URL'),0,-1);

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form = '';
		$this->doc->JScode=$this->doc->wrapScriptTags('
			var RTEobj = self.parent.parent;
		
			function setSelectedTextContent(content)	{	//
				var oSel = RTEobj.GLOBAL_SEL;
				var sType = oSel.type;
				if (sType=="Text")	{
					oSel.pasteHTML(content);
				}
			}
		');
	}

	/**
	 * Create main content (JavaScript section).
	 * 
	 * @return	void
	 */
	function main()	{
		$this->content='';
		$this->content.=$this->doc->startPage('RTE cleaner');

		$this->content.=$this->doc->wrapScriptTags('
			setSelectedTextContent(unescape("'.rawurlencode(strip_tags(t3lib_div::_GP("processContent"))).'"));
			RTEobj.edHidePopup();
		');
	}

	/**
	 * Print content
	 * 
	 * @return	void
	 */
	function printContent()	{
		$this->content.= $this->doc->endPage();
		echo $this->content;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/app/rte_cleaner.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/app/rte_cleaner.php']);
}





// Make instance:
$SOBE = t3lib_div::makeInstance('SC_rte_cleaner');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>