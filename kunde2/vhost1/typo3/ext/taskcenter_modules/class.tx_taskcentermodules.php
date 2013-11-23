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

class tx_taskcentermodules extends mod_user_task {

	/**
	 * Makes the content for the overview frame...
	 */
	function overview_main(&$pObj)	{
		// Modules might have a link to a totals-page where an overview of the modules content are present.
		if (t3lib_extMgm::isLoaded("plugin_mgm") && $this->accessMod("web_modules"))	{
			$mC = $this->renderModulesList();
			if ($mC)	{
				$icon = '<img src="'.$this->backPath.t3lib_extMgm::extRelPath("taskcenter_modules").'ext_icon.gif" width=18 height=16 class="absmiddle">';
				$content.=$pObj->doc->section($icon."&nbsp;".$this->headLink("tx_taskcentermodules",1),$mC,1,1,0,1);	
			}
		}
		return $content;
	}
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
	}


	// ************************
	// MODULES:
	// ***********************
	function renderModulesList()	{
		global $LANG;
	
		$mList = $this->getModuleList();
		$modList = t3lib_BEfunc::getListOfBackendModules(explode(",",$mList),$this->perms_clause,$this->backPath);

		$lines=array();
		if (is_array($modList["rows"]))	{
			reset($modList["rows"]);
			while(list(,$pageRow)=each($modList["rows"]))	{
				$path = t3lib_BEfunc::getRecordPath ($pageRow["uid"],$this->perms_clause,$this->BE_USER->uc["titleLen"]);
				$lines[]='<nobr>'.t3lib_iconworks::getIconImage("pages",$pageRow,$this->backPath,'hspace="2" align="top" title="'.htmlspecialchars($path)." - ".t3lib_BEfunc::titleAttribForPages($pageRow,"",0).'"').$this->recent_linkModulesModule($this->fixed_lgd($pageRow["title"]),$pageRow["uid"]).'</nobr><BR>';
			}
		}
		$out = implode("",$lines);

		return $out;
	}
	function recent_linkModulesModule($str,$id)	{
		$str='<a href="'.$this->backPath.t3lib_extMgm::extRelPath("plugin_mgm").'modules/index.php?id='.$id.'" target="list_frame" onClick="this.blur();">'.$str.'</a>';
		return $str;
	}
	function getModuleList()	{
		global $TCA;
		$lP=array();
		if (is_array($TCA["pages"]["columns"]["module"]["config"]["items"]))	{
			reset($TCA["pages"]["columns"]["module"]["config"]["items"]);
			while(list(,$p)=each($TCA["pages"]["columns"]["module"]["config"]["items"]))	{
				if (trim($p[1]))	{
					$lP[]=trim($p[1]);
				}
			}
		}
		return implode(",",$lP);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/taskcenter_modules/class.tx_taskcentermodules.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/taskcenter_modules/class.tx_taskcentermodules.php"]);
}

?>