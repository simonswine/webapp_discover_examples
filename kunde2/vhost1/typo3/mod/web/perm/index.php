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
 * Module: Permission setting
 *
 * $Id: index.php,v 1.19 2004/09/13 22:57:23 typo3 Exp $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   89: class SC_mod_web_perm_index
 *  123:     function init()
 *  183:     function menuConfig()
 *  214:     function main()
 *  291:     function printContent()
 *
 *              SECTION: Listing and Form rendering
 *  317:     function doEdit()
 *  454:     function notEdit()
 *
 *              SECTION: Helper functions
 *  647:     function printCheckBox($checkName,$num)
 *  658:     function printPerms($int)
 *  676:     function groupPerms($row,$firstGroup)
 *  693:     function getRecursiveSelect($id,$perms_clause)
 *
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

unset($MCONF);
require('conf.php');
require($BACK_PATH.'init.php');
require($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:lang/locallang_mod_web_perm.xml');
require_once (PATH_t3lib.'class.t3lib_pagetree.php');
require_once (PATH_t3lib.'class.t3lib_page.php');

$BE_USER->modAccess($MCONF,1);






/**
 * Script Class for the Web > Access module
 * This module lets you view and change permissions for pages.
 *
 * variables:
 * $this->depth 	: 	intval 1-3: decides the depth of the list
 * $this->mode		:	'perms' / '': decides if we view a user-overview or the permissions.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_mod_web_perm_index {

		// External, static:
	var $getLevels = 10;			// Number of levels to enable recursive settings for

		// Internal, static:
	var $MCONF=array();			// Module config
	var $doc;					// Document template object
	var $content;				// Content accumulation

	var $MOD_MENU=array();		// Module menu
	var $MOD_SETTINGS=array();	// Module settings, cleansed.

	var $perms_clause;			// Page select permissions
	var $pageinfo;				// Current page record

	var $color;					// Background color 1
	var $color2;				// Background color 2
	var $color3;				// Background color 3

	var $editingAllowed;		// Set internally if the current user either OWNS the page OR is admin user!

		// Internal, static: GPvars:
	var $id;					// Page id.
	var $edit;					// If set, editing of the page permissions will occur (showing the editing screen). Notice: This value is evaluated against permissions and so it will change internally!
	var $return_id;				// ID to return to after editing.
	var $lastEdited;			// Id of the page which was just edited.


	/**
	 * Initialization of the class
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$BACK_PATH;

			// Setting GPvars:
		$this->id = intval(t3lib_div::_GP('id'));
		$this->edit = t3lib_div::_GP('edit');
		$this->return_id = t3lib_div::_GP('return_id');
		$this->lastEdited = t3lib_div::_GP('lastEdited');

			// Module name;
		$this->MCONF = $GLOBALS['MCONF'];

			// Page select clause:
		$this->perms_clause = $BE_USER->getPagePermsClause(1);

			// Initializing document template object:
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_trans';
		$this->doc->form='<form action="'.$BACK_PATH.'tce_db.php" method="post" name="editform">';
		$this->doc->JScode = '<script type="text/javascript" src="'.$BACK_PATH.'t3lib/jsfunc.updateform.js"></script>';
		$this->doc->JScode.= $this->doc->wrapScriptTags('
			function checkChange(checknames, varname)	{	//
				var res = 0;
				for (var a=1; a<=5; a++)	{
					if (document.editform[checknames+"["+a+"]"].checked)	{
						res|=Math.pow(2,a-1);
					}
				}
				document.editform[varname].value = res | (checknames=="check[perms_user]"?1:0) ;
				setCheck (checknames,varname);
			}
			function setCheck(checknames, varname)	{ 	//
				if (document.editform[varname])	{
					var res = document.editform[varname].value;
					for (var a=1; a<=5; a++)	{
						document.editform[checknames+"["+a+"]"].checked = (res & Math.pow(2,a-1));
					}
				}
			}
			function jumpToUrl(URL)	{	//
				document.location = URL;
			}
		');

			// Setting up the context sensitive menu:
		$CMparts=$this->doc->getContextMenuCode();
		$this->doc->bodyTagAdditions = $CMparts[1];
		$this->doc->JScode.=$CMparts[0];
		$this->doc->postCode.= $CMparts[2];

			// Set up menus:
		$this->menuConfig();
	}

	/**
	 * Configuration of the menu and initialization of ->MOD_SETTINGS
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;

			// MENU-ITEMS:
			// If array, then it's a selector box menu
			// If empty string it's just a variable, that'll be saved.
			// Values NOT in this array will not be saved in the settings-array for the module.
		$temp = $LANG->getLL('levels');
		$this->MOD_MENU = array(
			'depth' => array(
				1 => '1 '.$temp,
				2 => '2 '.$temp,
				3 => '3 '.$temp,
				4 => '4 '.$temp,
				10 => '10 '.$temp
			),
			'mode' => array(
				0 => $LANG->getLL('user_overview'),
				'perms' => $LANG->getLL('permissions')
			)
		);

			// Clean up settings:
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}

	/**
	 * Main function, creating the content for the access editing forms/listings
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG;

			// Access check...
			// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

			// Checking access:
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

				// This decides if the editform can and will be drawn:
			$this->editingAllowed = ($this->pageinfo['perms_userid']==$BE_USER->user['uid'] || $BE_USER->isAdmin());
			$this->edit = $this->edit && $this->editingAllowed;

				// If $this->edit then these functions are called in the end of the page...
			if ($this->edit)	{
				$this->doc->postCode.= $this->doc->wrapScriptTags('
					setCheck("check[perms_user]","data[pages]['.$this->id.'][perms_user]");
					setCheck("check[perms_group]","data[pages]['.$this->id.'][perms_group]");
					setCheck("check[perms_everybody]","data[pages]['.$this->id.'][perms_everybody]");
				');
			}

				// Draw the HTML page header.
			$this->content.=$this->doc->startPage($LANG->getLL('permissions'));
			$this->content.=$this->doc->header($LANG->getLL('permissions').($this->edit?': '.$LANG->getLL('Edit'):''));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',
				$this->doc->funcMenu(
					$this->doc->getHeader('pages',$this->pageinfo,htmlspecialchars($this->pageinfo['_thePath'])).'<br />'.
						$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path',1).': '.
						'<span title="'.htmlspecialchars($this->pageinfo['_thePathFull']).'">'.htmlspecialchars(t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],-50)).'</span>',
					t3lib_BEfunc::getFuncMenu($this->id,'SET[mode]',$this->MOD_SETTINGS['mode'],$this->MOD_MENU['mode'])
				));
			$this->content.=$this->doc->divider(5);



			$vContent = $this->doc->getVersionSelector($this->id,1);
			if ($vContent)	{
				$this->content.=$this->doc->section('',$vContent);
			}



				// Main function, branching out:
			if (!$this->edit)	{
				$this->notEdit();
			} else {
				$this->doEdit();
			}

				// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=
					$this->doc->spacer(20).
					$this->doc->section('',$this->doc->makeShortcutIcon('id,edit,return_id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		} else {
				// If no access or if ID == zero
			$this->content.=$this->doc->startPage($LANG->getLL('permissions'));
			$this->content.=$this->doc->header($LANG->getLL('permissions'));
		}

			// Ending page:
		$this->content.=$this->doc->endPage();
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{

		echo $this->content;
	}










	/*****************************
	 *
	 * Listing and Form rendering
	 *
	 *****************************/

	/**
	 * Creating form for editing the permissions	($this->edit = true)
	 * (Adding content to internal content variable)
	 *
	 * @return	void
	 */
	function doEdit()	{
		global $BE_USER,$LANG;

			// Get usernames and groupnames
		$be_group_Array=t3lib_BEfunc::getListGroupNames('title,uid');
		$groupArray=array_keys($be_group_Array);

		$be_user_Array = t3lib_BEfunc::getUserNames();
		if (!$GLOBALS['BE_USER']->isAdmin())		$be_user_Array = t3lib_BEfunc::blindUserNames($be_user_Array,$groupArray,1);
		$be_group_Array_o = $be_group_Array = t3lib_BEfunc::getGroupNames();
		if (!$GLOBALS['BE_USER']->isAdmin())		$be_group_Array = t3lib_BEfunc::blindGroupNames($be_group_Array_o,$groupArray,1);
		$firstGroup = $groupArray[0] ? $be_group_Array[$groupArray[0]] : '';	// data of the first group, the user is member of


			// Owner selector:
		$options='';
		$userset=0;	// flag: is set if the page-userid equals one from the user-list
		foreach($be_user_Array as $uid => $row)	{
			if ($uid==$this->pageinfo['perms_userid'])	{
				$userset = 1;
				$selected=' selected="selected"';
			} else {$selected='';}
			$options.='
				<option value="'.$uid.'"'.$selected.'>'.htmlspecialchars($row['username']).'</option>';
		}
		$options='
				<option value="0"></option>'.$options;
		$selector='
			<select name="data[pages]['.$this->id.'][perms_userid]">
				'.$options.'
			</select>';

		$this->content.=$this->doc->section($LANG->getLL('Owner').':',$selector);


			// Group selector:
		$options='';
		$userset=0;
		foreach($be_group_Array as $uid => $row)	{
			if ($uid==$this->pageinfo['perms_groupid'])	{
				$userset = 1;
				$selected=' selected="selected"';
			} else {$selected='';}
			$options.='
				<option value="'.$uid.'"'.$selected.'>'.htmlspecialchars($row['title']).'</option>';
		}
		if (!$userset && $this->pageinfo['perms_groupid'])	{	// If the group was not set AND there is a group for the page
			$options='
				<option value="'.$this->pageinfo['perms_groupid'].'" selected="selected">'.
						htmlspecialchars($be_group_Array_o[$this->pageinfo['perms_groupid']]['title']).
						'</option>'.
						$options;
		}
		$options='
				<option value="0"></option>'.$options;
		$selector='
			<select name="data[pages]['.$this->id.'][perms_groupid]">
				'.$options.'
			</select>';

		$this->content.=$this->doc->divider(5);
		$this->content.=$this->doc->section($LANG->getLL('Group').':',$selector);

			// Permissions checkbox matrix:
		$code='
			<table border="0" cellspacing="2" cellpadding="0" id="typo3-permissionMatrix">
				<tr>
					<td></td>
					<td class="bgColor2">'.str_replace(' ','<br />',$LANG->getLL('1',1)).'</td>
					<td class="bgColor2">'.str_replace(' ','<br />',$LANG->getLL('16',1)).'</td>
					<td class="bgColor2">'.str_replace(' ','<br />',$LANG->getLL('2',1)).'</td>
					<td class="bgColor2">'.str_replace(' ','<br />',$LANG->getLL('4',1)).'</td>
					<td class="bgColor2">'.str_replace(' ','<br />',$LANG->getLL('8',1)).'</td>
				</tr>
				<tr>
					<td align="right" class="bgColor2">'.$LANG->getLL('Owner',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_user',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_user',5).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_user',2).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_user',3).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_user',4).'</td>
				</tr>
				<tr>
					<td align="right" class="bgColor2">'.$LANG->getLL('Group',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_group',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_group',5).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_group',2).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_group',3).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_group',4).'</td>
				</tr>
				<tr>
					<td align="right" class="bgColor2">'.$LANG->getLL('Everybody',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_everybody',1).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_everybody',5).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_everybody',2).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_everybody',3).'</td>
					<td class="bgColor-20">'.$this->printCheckBox('perms_everybody',4).'</td>
				</tr>
			</table>
			<br />

			<input type="hidden" name="data[pages]['.$this->id.'][perms_user]" value="'.$this->pageinfo['perms_user'].'" />
			<input type="hidden" name="data[pages]['.$this->id.'][perms_group]" value="'.$this->pageinfo['perms_group'].'" />
			<input type="hidden" name="data[pages]['.$this->id.'][perms_everybody]" value="'.$this->pageinfo['perms_everybody'].'" />
			'.$this->getRecursiveSelect($this->id,$this->perms_clause).'
			<input type="submit" name="submit" value="'.$LANG->getLL('Save',1).'" />'.
			'<input type="submit" value="'.$LANG->getLL('Abort',1).'" onclick="'.htmlspecialchars('jumpToUrl(\'index.php?id='.$this->id.'\'); return false;').'" />
			<input type="hidden" name="redirect" value="'.htmlspecialchars(TYPO3_MOD_PATH.'index.php?mode='.$this->MOD_SETTINGS['mode'].'&depth='.$this->MOD_SETTINGS['depth'].'&id='.intval($this->return_id).'&lastEdited='.$this->id).'" />
		';

			// Adding section with the permission setting matrix:
		$this->content.=$this->doc->divider(5);
		$this->content.=$this->doc->section($LANG->getLL('permissions').':',$code);

			// CSH for permissions setting
		$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'perm_module_setting', $GLOBALS['BACK_PATH'],'<br/><br/>');

			// Adding help text:
		if ($BE_USER->uc['helpText'])	{
			$this->content.=$this->doc->divider(20);
			$legendText = '<b>'.$LANG->getLL('1',1).'</b>: '.$LANG->getLL('1_t',1);
			$legendText.= '<br /><b>'.$LANG->getLL('16',1).'</b>: '.$LANG->getLL('16_t',1);
			$legendText.= '<br /><b>'.$LANG->getLL('2',1).'</b>: '.$LANG->getLL('2_t',1);
			$legendText.= '<br /><b>'.$LANG->getLL('4',1).'</b>: '.$LANG->getLL('4_t',1);
			$legendText.= '<br /><b>'.$LANG->getLL('8',1).'</b>: '.$LANG->getLL('8_t',1);

			$code=$legendText.'<br /><br />'.$LANG->getLL('def',1);
			$this->content.=$this->doc->section($LANG->getLL('Legend',1).':',$code);
		}
	}

	/**
	 * Showing the permissions in a tree ($this->edit = false)
	 * (Adding content to internal content variable)
	 *
	 * @return	void
	 */
	function notEdit()	{
		global $BE_USER,$LANG,$BACK_PATH;

			// Get usernames and groupnames: The arrays we get in return contains only 1) users which are members of the groups of the current user, 2) groups that the current user is member of
		$groupArray = $BE_USER->userGroupsUID;
		$be_user_Array = t3lib_BEfunc::getUserNames();
		if (!$GLOBALS['BE_USER']->isAdmin())		$be_user_Array = t3lib_BEfunc::blindUserNames($be_user_Array,$groupArray,0);
		$be_group_Array = t3lib_BEfunc::getGroupNames();
		if (!$GLOBALS['BE_USER']->isAdmin())		$be_group_Array = t3lib_BEfunc::blindGroupNames($be_group_Array,$groupArray,0);

			// Length of strings:
		$tLen= ($this->MOD_SETTINGS['mode']=='perms' ? 20 : 30);


			// Selector for depth:
		$code.=$LANG->getLL('Depth').': ';
		$code.=t3lib_BEfunc::getFuncMenu($this->id,'SET[depth]',$this->MOD_SETTINGS['depth'],$this->MOD_MENU['depth']);
		$this->content.=$this->doc->section('',$code);
		$this->content.=$this->doc->spacer(5);

			// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$this->perms_clause);

		$tree->addField('perms_user',1);
		$tree->addField('perms_group',1);
		$tree->addField('perms_everybody',1);
		$tree->addField('perms_userid',1);
		$tree->addField('perms_groupid',1);
		$tree->addField('hidden');
		$tree->addField('fe_group');
		$tree->addField('starttime');
		$tree->addField('endtime');
		$tree->addField('editlock');

			// Creating top icon; the current page
		$HTML=t3lib_iconWorks::getIconImage('pages',$this->pageinfo,$BACK_PATH,'align="top"');
		$tree->tree[]=Array('row'=>$this->pageinfo,'HTML'=>$HTML);

			// Create the tree from $this->id:
		$tree->getTree($this->id,$this->MOD_SETTINGS['depth'],'');

			// Make header of table:
		$code='';
		if ($this->MOD_SETTINGS['mode']=='perms')	{
			$code.='
				<tr>
					<td class="bgColor2" colspan="2">&nbsp;</td>
					<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center"><b>'.$LANG->getLL('Owner',1).'</b></td>
					<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center"><b>'.$LANG->getLL('Group',1).'</b></td>
					<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center"><b>'.$LANG->getLL('Everybody',1).'</b></td>
					<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center"><b>'.$LANG->getLL('EditLock',1).'</b></td>
				</tr>
			';
		} else {
			$code.='
				<tr>
					<td class="bgColor2" colspan="2">&nbsp;</td>
					<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center" nowrap="nowrap"><b>'.$LANG->getLL('User',1).':</b> '.$BE_USER->user['username'].'</td>
					'.(!$BE_USER->isAdmin()?'<td class="bgColor2"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td class="bgColor2" align="center"><b>'.$LANG->getLL('EditLock',1).'</b></td>':'').'
				</tr>';
		}

			// Traverse tree:
		foreach($tree->tree as $data)	{
			$cells = array();

				// Background colors:
			if ($this->lastEdited==$data['row']['uid'])	{$bgCol = ' class="bgColor-20"';} else {$bgCol = '';}
			$lE_bgCol = $bgCol;

				// User/Group names:
			$userN = $be_user_Array[$data['row']['perms_userid']] ? $be_user_Array[$data['row']['perms_userid']]['username'] : ($data['row']['perms_userid'] ? '<i>['.$data['row']['perms_userid'].']!</i>' : '');
			$groupN = $be_group_Array[$data['row']['perms_groupid']] ? $be_group_Array[$data['row']['perms_groupid']]['title']  : ($data['row']['perms_groupid'] ? '<i>['.$data['row']['perms_groupid'].']!</i>' : '');
			$groupN = t3lib_div::fixed_lgd_cs($groupN,20);

				// Seeing if editing of permissions are allowed for that page:
			$editPermsAllowed=($data['row']['perms_userid']==$BE_USER->user['uid'] || $BE_USER->isAdmin());

				// First column:
			$cells[]='
					<td align="left" nowrap="nowrap"'.$bgCol.'>'.$data['HTML'].htmlspecialchars(t3lib_div::fixed_lgd($data['row']['title'],$tLen)).'&nbsp;</td>';

				// "Edit permissions" -icon
			if ($editPermsAllowed && $data['row']['uid'])	{
				$aHref = 'index.php?mode='.$this->MOD_SETTINGS['mode'].'&depth='.$this->MOD_SETTINGS['depth'].'&id='.$data['row']['uid'].'&return_id='.$this->id.'&edit=1';
				$cells[]='
					<td'.$bgCol.'><a href="'.htmlspecialchars($aHref).'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('ch_permissions',1).'" align="top" alt="" /></a></td>';
			} else {
				$cells[]='
					<td'.$bgCol.'></td>';
			}

				// Rest of columns (depending on mode)
			if ($this->MOD_SETTINGS['mode']=='perms')	{
				$cells[]='
					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td'.$bgCol.' nowrap="nowrap">'.($data['row']['uid']?$this->printPerms($data['row']['perms_user']).' '.$userN:'').'</td>

					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td'.$bgCol.' nowrap="nowrap">'.($data['row']['uid']?$this->printPerms($data['row']['perms_group']).' '.$groupN:'').'</td>

					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td'.$bgCol.' nowrap="nowrap">'.($data['row']['uid']?' '.$this->printPerms($data['row']['perms_everybody']):'').'</td>

					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td'.$bgCol.' nowrap="nowrap">'.($data['row']['editlock']?'<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/recordlock_warning2.gif','width="22" height="16"').' title="'.$LANG->getLL('EditLock_descr',1).'" alt="" />':'').'</td>
				';
			} else {
				$cells[]='
					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>';

				if ($BE_USER->user['uid']==$data['row']['perms_userid'])	{$bgCol = ' class="bgColor-20"';} else {$bgCol = $lE_bgCol;}
				$cells[]='
					<td'.$bgCol.' nowrap="nowrap" align="center">'.($data['row']['uid']?$owner.$this->printPerms($BE_USER->calcPerms($data['row'])):'').'</td>
					'.(!$BE_USER->isAdmin()?'
					<td'.$bgCol.'><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					<td'.$bgCol.' nowrap="nowrap">'.($data['row']['editlock']?'<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/recordlock_warning2.gif','width="22" height="16"').' title="'.$LANG->getLL('EditLock_descr',1).'" alt="" />':'').'</td>
					':'');
				$bgCol = $lE_bgCol;
			}

				// Compile table row:
			$code.='
				<tr>
					'.implode('
					',$cells).'
				</tr>';
		}

			// Wrap rows in table tags:
		$code='<table border="0" cellspacing="0" cellpadding="0" id="typo3-permissionList">'.$code.'</table>';

			// Adding the content as a section:
		$this->content.=$this->doc->section('',$code);

			// CSH for permissions setting
		$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'perm_module', $GLOBALS['BACK_PATH'],'<br/>|');

			// Creating legend table:
		$legendText = '<b>'.$LANG->getLL('1',1).'</b>: '.$LANG->getLL('1_t',1);
		$legendText.= '<br /><b>'.$LANG->getLL('16',1).'</b>: '.$LANG->getLL('16_t',1);
		$legendText.= '<br /><b>'.$LANG->getLL('2',1).'</b>: '.$LANG->getLL('2_t',1);
		$legendText.= '<br /><b>'.$LANG->getLL('4',1).'</b>: '.$LANG->getLL('4_t',1);
		$legendText.= '<br /><b>'.$LANG->getLL('8',1).'</b>: '.$LANG->getLL('8_t',1);

		$code='<table border="0" id="typo3-legendTable">
			<tr>
				<td valign="top"><img src="legend.gif" width="86" height="75" alt="" /></td>
				<td valign="top" nowrap="nowrap">'.$legendText.'</td>
			</tr>
		</table>';
		$code.='<br />'.$LANG->getLL('def',1);
		$code.='<br /><br /><span class="perm-allowed">*</span>: '.$LANG->getLL('A_Granted',1);
		$code.='<br /><span class="perm-denied">x</span>: '.$LANG->getLL('A_Denied',1);

			// Adding section with legend code:
		$this->content.=$this->doc->spacer(20);
		$this->content.=$this->doc->section($LANG->getLL('Legend').':',$code,0,1);
	}














	/*****************************
	 *
	 * Helper functions
	 *
	 *****************************/

	/**
	 * Print a checkbox for the edit-permission form
	 *
	 * @param	string		Checkbox name key
	 * @param	integer		Checkbox number index
	 * @return	string		HTML checkbox
	 */
	function printCheckBox($checkName,$num)	{
		$onClick = 'checkChange(\'check['.$checkName.']\', \'data[pages]['.$GLOBALS['SOBE']->id.']['.$checkName.']\')';
		return '<input type="checkbox" name="check['.$checkName.']['.$num.']" onclick="'.htmlspecialchars($onClick).'" /><br />';
	}

	/**
	 * Print a set of permissions
	 *
	 * @param	integer		Permission integer (bits)
	 * @return	string		HTML marked up x/* indications.
	 */
	function printPerms($int)	{
		$str='';
		$str.= (($int&1)?'*':'<span class="perm-denied">x</span>');
		$str.= (($int&16)?'*':'<span class="perm-denied">x</span>');
		$str.= (($int&2)?'*':'<span class="perm-denied">x</span>');
		$str.= (($int&4)?'*':'<span class="perm-denied">x</span>');
		$str.= (($int&8)?'*':'<span class="perm-denied">x</span>');

		return '<span class="perm-allowed">'.$str.'</span>';
	}

	/**
	 * Returns the permissions for a group based of the perms_groupid of $row. If the $row[perms_groupid] equals the $firstGroup[uid] then the function returns perms_everybody OR'ed with perms_group, else just perms_everybody
	 *
	 * @param	array		Row array (from pages table)
	 * @param	array		First group data
	 * @return	integer		Integer: Combined permissions.
	 */
	function groupPerms($row,$firstGroup)	{
		if (is_array($row))	{
			$out=intval($row['perms_everybody']);
			if ($row['perms_groupid'] && $firstGroup['uid']==$row['perms_groupid'])	{
				$out|= intval($row['perms_group']);
			}
			return $out;
		}
	}

	/**
	 * Finding tree and offer setting of values recursively.
	 *
	 * @param	integer		Page id.
	 * @param	string		Select clause
	 * @return	string		Select form element for recursive levels (if any levels are found)
	 */
	function getRecursiveSelect($id,$perms_clause)	{

			// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$perms_clause);
		$tree->addField('perms_userid',1);
		$tree->makeHTML=0;
		$tree->setRecs = 1;

			// Make tree:
		$tree->getTree($id,$this->getLevels,'');

			// If there are a hierarchy of page ids, then...
		if ($GLOBALS['BE_USER']->user['uid'] && count($tree->ids_hierarchy))	{

				// Init:
			$label_recur = $GLOBALS['LANG']->getLL('recursive');
			$label_levels = $GLOBALS['LANG']->getLL('levels');
			$label_pA = $GLOBALS['LANG']->getLL('pages_affected');
			$theIdListArr=array();
			$opts='
						<option value=""></option>';

				// Traverse the number of levels we want to allow recursive setting of permissions for:
			for ($a=$this->getLevels;$a>0;$a--)	{
				if (is_array($tree->ids_hierarchy[$a]))	{
					foreach($tree->ids_hierarchy[$a] as $theId)	{
						if ($GLOBALS['BE_USER']->isAdmin() || $GLOBALS['BE_USER']->user['uid']==$tree->recs[$theId]['perms_userid'])	{
							$theIdListArr[]=$theId;
						}
					}
					$lKey = $this->getLevels-$a+1;
					$opts.='
						<option value="'.htmlspecialchars(implode(',',$theIdListArr)).'">'.
							t3lib_div::deHSCentities(htmlspecialchars($label_recur.' '.$lKey.' '.$label_levels)).' ('.count($theIdListArr).' '.$label_pA.')'.
							'</option>';
				}
			}

				// Put the selector box together:
			$theRecursiveSelect = '<br />
					<select name="mirror[pages]['.$id.']">
						'.$opts.'
					</select>

				<br /><br />';
		} else {
			$theRecursiveSelect = '';
		}

			// Return selector box element:
		return $theRecursiveSelect;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/mod/web/perm/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/mod/web/perm/index.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_mod_web_perm_index');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

if ($TYPO3_CONF_VARS['BE']['compressionLevel'])	{
	new gzip_encode($TYPO3_CONF_VARS['BE']['compressionLevel']);
}
?>
