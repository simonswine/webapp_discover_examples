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

class tx_sysnotepad extends mod_user_task {

	/**
	 * Makes the content for the overview frame...
	 */
	function overview_main(&$pObj)	{
		$icon = '<img src="'.$this->backPath.t3lib_extMgm::extRelPath("sys_notepad").'ext_icon.gif" width=18 height=16 class="absmiddle">';
		$noteRow = $this->getQuickNote($this->BE_USER->user["uid"]);
		$noteVal = nl2br(htmlspecialchars(t3lib_div::fixed_lgd_cs(implode(chr(10),t3lib_div::trimExplode(chr(10),$noteRow["note"],1)),50)));
		$content.=$pObj->doc->section(
			$icon."&nbsp;".$this->headLink("tx_sysnotepad",0),
			'<a href="index.php?SET[function]=tx_sysnotepad" target="list_frame" onClick="this.blur();"><img src="'.$this->backPath.'gfx/edit2.gif" width="11" height="12" hspace=2 border="0" align=top>'.$noteVal.'</a>',1,1,0,1);
		return $content;
	}
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		return $this->renderNote();
	}





	function getQuickNote($be_user_id)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_notepad', 'cruser_id='.intval($be_user_id), '', 'crdate', '1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row;
	}



	// ************************
	// NOTES:
	// ***********************
	function renderNote()	{
		global $LANG;

		$out = '';

			// Saving / creating note:
		$incoming = t3lib_div::_GP("data");
		if (is_array($incoming["sys_notepad"]))	{
			$dataArr = array();
			$key = key($incoming["sys_notepad"]);
			if ($key=="NEW")	{
				$dataArr["note"] = $incoming["sys_notepad"]["NEW"]["note"];
				$dataArr["cruser_id"]=$this->BE_USER->user["uid"];
				$dataArr["crdate"]=time();
				$dataArr["tstamp"]=time();
				$this->setQuickNote(0,$dataArr,$this->BE_USER->user["uid"]);
			} else {
				$uid = intval($key);
				$dataArr["note"] = $incoming["sys_notepad"][$uid]["note"];
				$dataArr["tstamp"]=time();
				$this->setQuickNote($uid,$dataArr,$this->BE_USER->user["uid"]);
			}
			$out.=$this->loadLeftFrameJS();
		}

			// Displaying edit form for note:
		$note = $this->getQuickNote($this->BE_USER->user["uid"]);
		if (!is_array($note))	{
			$note["uid"]="NEW";
		}

		$out.= '<textarea rows=30'.$this->pObj->doc->formWidthText().' name="data[sys_notepad]['.$note["uid"].'][note]">'.t3lib_div::formatForTextarea($note["note"]).'</textarea>';
		$out.= '<BR><input type="submit" value="'.$LANG->getLL("lUpdate").'">';

			// Help text:
		if ($this->BE_USER->uc["helpText"])	{
			$out.= '<BR><BR>'.$this->helpBubble().$LANG->getLL("note_helpText");
		}

		return $this->pObj->doc->section("",$out,0,1);
	}
	function setQuickNote($uid,$inRow,$be_user_id)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_notepad', 'uid='.intval($uid));
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if (is_array($row))	{ 	// update...
			if ($be_user_id==$row["cruser_id"])	{
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_notepad", "uid=".intval($uid), $inRow);
			}
		} else {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery("sys_notepad", $inRow);
		}
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_notepad/class.tx_sysnotepad.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_notepad/class.tx_sysnotepad.php"]);
}

?>
