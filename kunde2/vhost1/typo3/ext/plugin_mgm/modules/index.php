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
 * Module: Direct Mail module for TYPO3
 *
 * This module is used to create and send Direct Mails in TYPO3
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */


unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:plugin_mgm/modules/locallang.php");
require_once (PATH_t3lib."class.t3lib_page.php");
$BE_USER->modAccess($MCONF,1);



// ***************************
// Script Classes
// ***************************
class SC_mod_web_modules_index {
	var $MCONF=array();
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();
	var $doc;	

	var $include_once=array();
	var $content;
	
	var $perms_clause;
	var $modTSconfig;
	var $pageinfo;
	var $access;
	var $CMD;
	var $module;
	var $sys_dmail_uid;
	var $pages_uid;
	var $id;

	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		global $LOCAL_LANG;
		$this->MCONF = $GLOBALS["MCONF"];
		$this->id = intval(t3lib_div::_GP("id"));

		$this->CMD = t3lib_div::_GP("CMD");
		$this->sys_dmail_uid = t3lib_div::_GP("sys_dmail_uid");
		$this->pages_uid = t3lib_div::_GP("pages_uid");

		$this->perms_clause = $BE_USER->getPagePermsClause(1);

		$this->menuConfig();

		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$this->access = is_array($this->pageinfo) ? 1 : 0;

		if (!$this->id)	{
			$this->pageinfo=array();
			$this->CMD="";
			$this->access=1;
		}
		if ($this->access)	{
			$this->module = $this->pageinfo["module"];
			if (!$this->module)	{
				$pidrec=t3lib_BEfunc::getRecord("pages",intval($this->pageinfo["pid"]));
				$this->module=$pidrec["module"];
			}
		}

		switch((string)$this->module)	{
			case "dmail":
				if (t3lib_extMgm::isLoaded("direct_mail"))	{
					$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_pagetree.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_htmlmail.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_dmailer.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_readmail.php";
					$this->include_once[]=PATH_t3lib."class.t3lib_querygenerator.php";
					$this->include_once[]=t3lib_extMgm::extPath("direct_mail")."mod/class.mod_web_dmail.php";
					$this->include_once[]=t3lib_extMgm::extPath("direct_mail")."mod/class.mailselect.php";
				}
			break;
		}
	}
	function menuConfig()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// MENU-ITEMS:
			// If array, then it's a selector box menu
			// If empty string it's just a variable, that'll be saved. 
			// Values NOT in this array will not be saved in the settings-array for the module.
		$this->MOD_MENU = array(
			"dmail_mode" => array(
				"news" => $GLOBALS["LANG"]->getLL("dmail_newsletters"),
				"direct" => "Direct Mails",
				"quick" => "QuickMail",
				"recip" => "Recipient list",
				"conf" => "Module configuration",
				"mailerengine" => "Mailer Engine Status",
				"help" => "Instructions"
			),
	
			"dmail_test_email"=>""
		);
	
	
			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,"mod.".$this->MCONF["name"]);
		$this->MOD_MENU["function"] = t3lib_BEfunc::unsetMenuItems($this->modTSconfig["properties"],$this->MOD_MENU["function"],"menu.function");
		
			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP("SET"), $this->MCONF["name"]);
	}
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check...
		// The page will show only if there is a valid page and if this page may be viewed by the user
		
		if ($this->access)	{
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->JScode = '
<script language="javascript" type="text/javascript">
	script_ended = 0;
	function jumpToUrl(URL)	{
		document.location = URL;
	}
	function jumpToUrlD(URL)	{
		document.location = URL+"&sys_dmail_uid='.$this->sys_dmail_uid.'";
	}
</script>
				';
			$this->doc->postCode='
<script language="javascript" type="text/javascript">
	script_ended = 1;
	if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
</script>
		';
			$this->doc->form = '<form action="index.php?id='.$this->id.'" method="POST">';
		
			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"]).'<br>'.$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").': '.t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);
		
				// *******************
				// Draw the header.
				// *******************
			$this->content.=$this->doc->startPage($LANG->getLL(($this->module?$this->module."_":"")."title"));
			$this->content.=$this->doc->header($LANG->getLL(($this->module?$this->module."_":"")."title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$headerSection);
		
			switch((string)$this->module)	{
				case "shop":
					$this->content.=$this->doc->section("TODO: FE_USERS-module",nl2br('
					Here you can:
					- See the list of orders and modify order-status
					- Create/edit products on the page and subpages
					- Setup simple shop-parameters (eg. email/name of ...)
					'));
				break;
				case "board":
					$this->content.=$this->doc->section("TODO: FE_USERS-module",nl2br('
					Here you can:
					- Browse the forums on the page/subpage
					- Hide/unhide topics
					- List a thread
					- Manage forums/categories
					- Setup simple parameters (eg. admin email/name)
					'));
				break;
				case "dmail":
							// Module configuration.
					$modTSconfig_submod = t3lib_BEfunc::getModTSconfig($this->id,"mod.".$this->MCONF["name"].".dmail");

					$SET = t3lib_div::_GP("SET");
					if (isset($SET["test_email"]))	{
						$email = $SET["test_email"];
					}
					
						// Create module object for the web_dmail module
					$className=t3lib_div::makeInstanceClassName("mod_web_dmail");
					$modObj = new $className($this->id,$this->pageinfo,$this->perms_clause,$this->CMD,$this->sys_dmail_uid,$this->pages_uid,$modTSconfig_submod);
					$modObj->createDMail();
					$modObj->updatePageTS();
					$this->content.=$modObj->main();	
				break;
				case "news":
					$this->content.=$this->doc->section("TODO: NEWS-module",nl2br('
					Here you can:
					- Create new news items on the page or subpages
					- See list of / search the news items
					'));
				break;
				case "fe_users":
					$this->content.=$this->doc->section("TODO: FE_USERS-module",nl2br('
					Here you can:
					- Create new front-end users in a snap
					- Create new front-end usergroups easily
					- See the list of users, when they logged in last time.
					'));
				break;
				case "approve":
					$this->content.=$this->doc->section("TODO: APPROVAL-module",'This module is basically just meant to list records like users signing up for something and being hidden initially. Then there will be a little button to press if you want to unhide/hide the record. Not too complicated.');
				break;
				default:
					$modList = t3lib_BEfunc::getListOfBackendModules(explode(",","dmail,board,fe_users,approve,news,shop"),$this->perms_clause,$BACK_PATH);		
					$this->content.=$this->doc->section($LANG->getLL("modules"),$modList["list"],0,1);
				break;
			}
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance("smallDoc");
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	// ***************************
	// OTHER FUNCTIONS:	
	// ***************************
	function getRecursiveSelect($id,$perms_clause)	{
		// Finding tree and offer setting of values recursively.
		$tree = t3lib_div::makeInstance("t3lib_pageTree");
		$tree->init("AND ".$perms_clause);
		$tree->makeHTML=0;
		$tree->setRecs = 0;
		$getLevels=10000;
		$tree->getTree($id,$getLevels,"");
		return $tree->ids;
	}
}

// Include extension?
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/plugin_mgm/modules/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/plugin_mgm/modules/index.php"]);
}












// Make instance:
$SOBE = t3lib_div::makeInstance("SC_mod_web_modules_index");
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();
?>