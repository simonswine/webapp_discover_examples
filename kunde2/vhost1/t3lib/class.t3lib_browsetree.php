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
 * Generate a page-tree, browsable.
 *
 * $Id: class.t3lib_browsetree.php,v 1.12 2004/09/13 22:57:17 typo3 Exp $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @coauthor	Ren� Fritz <r.fritz@colorcube.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   74: class t3lib_browseTree extends t3lib_treeView
 *   83:     function init($clause='')
 *  107:     function getTitleAttrib($row)
 *  119:     function wrapIcon($icon,$row)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once (PATH_t3lib.'class.t3lib_treeview.php');













/**
 * Extension class for the t3lib_treeView class, specially made for browsing pages
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @coauthor	Ren� Fritz <r.fritz@colorcube.de>
 * @see t3lib_treeView, t3lib_pageTree
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_browseTree extends t3lib_treeView {

	/**
	 * Initialize, setting what is necessary for browsing pages.
	 * Using the current user.
	 *
	 * @param	string		Additional clause for selecting pages.
	 * @return	void
	 */
	function init($clause='')	{
			// This is very important for making trees of pages: Filtering out deleted pages, pages with no access to and sorting them correctly:
		parent::init(' AND '.$GLOBALS['BE_USER']->getPagePermsClause(1).' '.$clause, 'sorting');

		$this->table = 'pages';
		$this->setTreeName('browsePages');
		$this->domIdPrefix = 'pages';
		$this->iconName = '';
		$this->title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		$this->MOUNTS = $GLOBALS['WEBMOUNTS'];

		$this->fieldArray = array_merge($this->fieldArray,array('doktype','php_tree_stop','t3ver_id'));
		if (t3lib_extMgm::isLoaded('cms'))	{
			$this->fieldArray = array_merge($this->fieldArray,array('hidden','starttime','endtime','fe_group','module','extendToSubpages'));
		}
	}

	/**
	 * Creates title attribute content for pages.
	 * Uses API function in t3lib_BEfunc which will retrieve lots of useful information for pages.
	 *
	 * @param	array		The table row.
	 * @return	string
	 */
	function getTitleAttrib($row) {
		return t3lib_BEfunc::titleAttribForPages($row,'1=1 '.$this->clause,0);
	}

	/**
	 * Wrapping the image tag, $icon, for the row, $row (except for mount points)
	 *
	 * @param	string		The image tag for the icon
	 * @param	array		The row for the current element
	 * @return	string		The processed icon input value.
	 * @access private
	 */
	function wrapIcon($icon,$row)	{
			// Add title attribute to input icon tag
		$theIcon = $this->addTagAttributes($icon,($this->titleAttrib ? $this->titleAttrib.'="'.$this->getTitleAttrib($row).'"' : ''));

			// Wrap icon in click-menu link.
		if (!$this->ext_IconMode)	{
			$theIcon = $GLOBALS['TBE_TEMPLATE']->wrapClickMenuOnIcon($theIcon,$this->treeName,$this->getId($row),0);
		} elseif (!strcmp($this->ext_IconMode,'titlelink'))	{
			$aOnClick = 'return jumpTo(\''.$this->getJumpToParam($row).'\',this,\''.$this->domIdPrefix.$this->getId($row).'_'.$this->bank.'\');';
			$theIcon='<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.$theIcon.'</a>';
		}
		return $theIcon;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_browsetree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_browsetree.php']);
}
?>
