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
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

class tx_ttboard_wizicon {
	function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems["plugins_ttboard_2"] = array(
			"icon"=>t3lib_extMgm::extRelPath("tt_board")."message_board.gif",
			"title"=>$LANG->getLLL("plugins_2_title",$LL),
			"description"=>$LANG->getLLL("plugins_2_description",$LL),
			"params"=>"&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=4&defVals[tt_content][select_key]=".rawurlencode("FORUM, POSTFORM")
		);
		$wizardItems["plugins_ttboard_3"] = array(
			"icon"=>t3lib_extMgm::extRelPath("tt_board")."forum.gif",
			"title"=>$LANG->getLLL("plugins_3_title",$LL),
			"description"=>$LANG->getLLL("plugins_3_description",$LL),
			"params"=>"&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=2&defVals[tt_content][select_key]=".rawurlencode("FORUM, POSTFORM")
		);
		
		return $wizardItems;
	}
	function includeLocalLang()	{
		include(t3lib_extMgm::extPath("tt_board")."locallang.php");
		return $LOCAL_LANG;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tt_board/class.tx_ttboard_wizicon.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tt_board/class.tx_ttboard_wizicon.php"]);
}

?>