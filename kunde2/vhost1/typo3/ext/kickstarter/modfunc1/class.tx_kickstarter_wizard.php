<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2001-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * TYPO3 Extension Repository
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

require_once(t3lib_extMgm::extPath("kickstarter")."modfunc1/class.tx_kickstarter_compilefiles.php");

class tx_kickstarter_wizard extends tx_kickstarter_compilefiles {
	var $varPrefix = "kickstarter";		// redundant from "extrep"
	var $siteBackPath = "";
	var $EMmode=1;	// If run from Extension Manager, set to 1.

	var $wizArray=array();

	var $extKey_nusc = "myext";
	var $extKey = "my_ext";
	var $printWOP=0;
	var $outputWOP=0;
	var $saveKey="";
	var $pObj;

	var $afterContent;

	var $options = array(
		"emconf" => array("General info", "Enter general information about the extension here: Title, description, category, author..."),
		"tables" => array("New Database Tables", "Add database tables which can be edited inside the backend. These tables will be added to the global TCA array in TYPO3.","cm.png"),
		"fields" => array("Extend existing Tables", "Add custom fields to existing tables, such as the 'pages', 'tt_content', 'fe_users' or 'be_users' table."),
		"pi" => array("Frontend Plugins", "Create frontend plugins. Plugins are web applications running on the website itself (not in the backend of TYPO3). The default guestbook, message board, shop, rating feature etc. are examples of plugins. Even this very interface (the Extension Kickstarter) is a plugin (extension key 'kickstarter')."),
		"module" => array("Backend Modules", "Create backend modules. A module is normally recognized as the application behind one of the TYPO3 backend menuitems. Examples are the Web>Page, Web>List, User>Setup, Doc module etc. In a more loose sense, all applications integrated with existing module (see below) also belongs to the 'module' category."),
		"moduleFunction" => array("Integrate in existing Modules", "Extend existing modules with new function-menu items. Examples are extensions such as 'User>Task Center, Messaging' which adds internal messaging to TYPO3. Or 'Web>Info, Page TSconfig' which shows the Page TSconfiguration for a page. Or 'Web>Func, Wizards, Sort pages' which is a wizard for re-ordering pages in a folder."),
		"cm" => array("Clickmenu items", "Adds a custom item to the clickmenus of database records. This is a very cool way to integrate small tools of your own in an elegant way!"),
		'sv' => array('Services', 'Create a Services class. With a Services extension you can extend TYPO3 (or an extension which use Services) with functionality, without any changes to the code which use that service.'),
		"ts" => array("Static TypoScript code", "Adds static TypoScript Setup and Constants code - just like a static template would do."),
		"TSconfig" => array("TSconfig", "Adds default Page-TSconfig or User-TSconfig. Can be used to preset options inside TYPO3."),
		"languages" => array("Setup languages", "Start here by entering the number of system languages you want to use in your extension."),
	);

	var $languages = array(
		"dk" => "Danish",
		"de" => "German",
		"no" => "Norwegian",
		"it" => "Italian",
		"fr" => "French",
		"es" => "Spanish",
		"nl" => "Dutch",
		"cz" => "Czech",
		"pl" => "Polish",
		"si" => "Slovenian",
		"fi" => "Finnish",
		"tr" => "Turkish",
		"se" => "Swedish",
		"pt" => "Portuguese",
		"ru" => "Russian",
		"ro" => "Romanian",
		"ch" => "Chinese",
		"sk" => "Slovak",
		"lt" => "Lithuanian",
		'is' => 'Icelandic',
		'hr' => 'Croatian',
		'hu' => 'Hungarian',
		'gl' => 'Greenlandic',
		'th' => 'Thai',
		'gr' => 'Greek',
		'hk' => 'Chinese (Trad)',
		'eu' => 'Basque',
		'bg' => 'Bulgarian',
		'br' => 'Brazilian Portuguese',
		'et' => 'Estonian',
		'ar' => 'Arabic',
		'he' => 'Hebrew',
		'ua' => 'Ukrainian',
		'lv' => 'Latvian',
		'jp' => 'Japanese',
		'vn' => 'Vietnamese',
		'ca' => 'Catalan',
		'ba' => 'Bosnian',
		'kr' => 'Korean',
	);
	var $reservedTypo3Fields="uid,pid,endtime,starttime,sorting,fe_group,hidden,deleted,cruser_id,crdate,tstamp";
	var $mysql_reservedFields="data,table,field,key,desc";

		// Internal:
	var $selectedLanguages = array();
	var $usedNames=array();
	var $fileArray=array();
	var $ext_tables=array();
	var $ext_localconf=array();
	var $ext_locallang=array();

	var $color = array("#C8D0B3","#FEE7B5","#eeeeee");

	var $modData;

	function tx_kickstarter_wizard() {
	  $this->modData = t3lib_div::_POST($this->varPrefix);
	}


	function initWizArray()	{
		$inArray = unserialize(base64_decode($this->modData["wizArray_ser"]));
		$this->wizArray = is_array($inArray) ? $inArray : array();
		if (is_array($this->modData["wizArray_upd"]))	{
			$this->wizArray = t3lib_div::array_merge_recursive_overrule($this->wizArray,$this->modData["wizArray_upd"]);
		}

		$lA = is_array($this->wizArray["languages"]) ? current($this->wizArray["languages"]) : "";
		if (is_array($lA))	{
			reset($lA);
			while(list($k,$v)=each($lA))	{
				if ($v && isset($this->languages[$k]))	{
					$this->selectedLanguages[$k]=$this->languages[$k];
				}
			}
		}
	}

	function mgm_wizard()	{
		$this->initWizArray();

		$saveKey = $this->saveKey = $this->wizArray["save"]["extension_key"] = trim($this->wizArray["save"]["extension_key"]);
		$this->outputWOP = $this->wizArray["save"]["print_wop_comments"] ? 1 : 0;



		if ($saveKey)	{
			$this->extKey=$saveKey;
			$this->extKey_nusc=str_replace("_","",$saveKey);
		}

		if ($this->modData["viewResult"])	{
			$this->modData["wizAction"]="";
			$this->modData["wizSubCmd"]="";
			if ($saveKey)	{
				$content = $this->view_result();
			} else $content = $this->fw("<strong>Error:</strong> Please enter an extension key first!<BR><BR>");
		} elseif ($this->modData["WRITE"])	{
			$this->modData["wizAction"]="";
			$this->modData["wizSubCmd"]="";
			if ($saveKey)	{
				$this->makeFilesArray($this->saveKey);
				$uploadArray = $this->makeUploadArray($this->saveKey,$this->fileArray);
				$this->pObj->importExtFromRep(0,$this->modData["loc"],0,$uploadArray);
			} else $content = $this->fw("<strong>Error:</strong> Please enter an extension key first!<BR><BR>");
		} elseif ($this->modData["totalForm"])	{
			$content = $this->totalForm();
		} elseif ($this->modData["downloadAsFile"])	{
			if ($saveKey)	{
				$this->makeFilesArray($this->saveKey);
				$uploadArray = $this->makeUploadArray($this->saveKey,$this->fileArray);
				$backUpData = $this->makeUploadDataFromArray($uploadArray);
				$filename="T3X_".$saveKey."-".str_replace(".","_","0.0.0").".t3x";
				$mimeType = "application/octet-stream";
				Header("Content-Type: ".$mimeType);
				Header("Content-Disposition: attachment; filename=".$filename);
				echo $backUpData;
				exit;
			} else $content = $this->fw("<strong>Error:</strong> Please enter an extension key first!<BR><BR>");
		} else {
			$action = explode(":",$this->modData["wizAction"]);
			if ((string)$action[0]=="deleteEl")	{
				unset($this->wizArray[$action[1]][$action[2]]);
			}

			$content = $this->getFormContent();
		}
		$wasContent = $content?1:0;
		$content = '
		<script language="javascript" type="text/javascript">
			function setFormAnchorPoint(anchor)	{
				document.'.$this->varPrefix.'_wizard.action = unescape("'.rawurlencode($this->linkThisCmd()).'")+"#"+anchor;
			}
		</script>
		<table border=0 cellpadding=0 cellspacing=0>
			<form action="'.$this->linkThisCmd().'" method="POST" name="'.$this->varPrefix.'_wizard">
			<tr>
				<td valign=top>'.$this->sidemenu().'</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td valign=top>'.$content.'
					<input type="hidden" name="'.$this->piFieldName("wizArray_ser").'" value="'.htmlspecialchars(base64_encode(serialize($this->wizArray))).'" /><BR>';

		if ((string)$this->modData["wizSubCmd"])	{
			if ($wasContent)	$content.='<input name="update2" type="submit" value="Update..."> ';
		}
		$content.='
					<input type="hidden" name="'.$this->piFieldName("wizAction").'" value="'.$this->modData["wizAction"].'">
					<input type="hidden" name="'.$this->piFieldName("wizSubCmd").'" value="'.$this->modData["wizSubCmd"].'">
					'.$this->cmdHiddenField().'
				</td>
			</tr>
			</form>
		</table>'.$this->afterContent;

		return $content;
	}

	/**
	 * Get form content
	 */
	function getFormContent()	{
		switch((string)$this->modData["wizSubCmd"])	{
			case "tables":
				$content.=$this->add_cat_tables();
			break;
			case "fields":
				$content.=$this->add_cat_fields();
			break;
			case "pi":
				$content.=$this->add_cat_pi();
			break;
			case "TSconfig":
				$content.=$this->add_cat_TSconfig();
			break;
			case "ts":
				$content.=$this->add_cat_ts();
			break;
			case "cm":
				$content.=$this->add_cat_cm();
			break;
			case "module":
				$content.=$this->add_cat_module();
			break;
			case "moduleFunction":
				$content.=$this->add_cat_moduleFunction();
			break;
			case "languages":
				$content.=$this->add_cat_languages();
			break;
			case 'sv':
				$content.=$this->add_cat_services();
			break;
			case "emconf":
				$content.=$this->add_cat_emconf();
			break;
			default:
			break;
		}
		return $content;
	}

	/**
	 * Total form
	 */
	function totalForm()	{
		$buf = array($this->printWOP,$this->dontPrintImages);
		$this->printWOP = 1;

		reset($this->options);
		$lines=array();
		while(list($k,$v)=each($this->options))	{
			// Add items:
			$items = $this->wizArray[$k];
			if (is_array($items))	{
				reset($items);
				while(list($k2,$conf)=each($items))	{
					$this->modData["wizSubCmd"]=$k;
					$this->modData["wizAction"]="edit:".$k2;
					$lines[]=$this->getFormContent();
				}
			}
		}

		$this->modData["wizSubCmd"]="";
		$this->modData["wizAction"]="";
		list($this->printWOP,$this->dontPrintImages) = $buf;

		$content = implode("<HR>",$lines);
		return $content;
	}

	/**
	 * Side menu
	 */
	function sidemenu()	{
#debug($this->modData);
		$actionType = $this->modData["wizSubCmd"].":".$this->modData["wizAction"];
		$singles = "emconf,save,ts,TSconfig,languages";
		reset($this->options);
		$lines=array();
		while(list($k,$v)=each($this->options))	{
			// Add items:
			$items = $this->wizArray[$k];
			$c=0;
			$iLines=array();
			if (is_array($items))	{
				reset($items);
				while(list($k2,$conf)=each($items))	{
					$dummyTitle = t3lib_div::inList($singles,$k) ? "[Click to Edit]" : "<em>Item ".$k2."</em>";
					$isActive = !strcmp($k.":edit:".$k2,$actionType);
					$delIcon = $this->linkStr('<img src="'.$this->siteBackPath.'t3lib/gfx/garbage.gif" width="11" height="12" border="0" title="Remove item">',"","deleteEl:".$k.":".$k2);
					$iLines[]='<tr'.($isActive?$this->bgCol(2,-30):$this->bgCol(2)).'><td>'.$this->fw($this->linkStr($this->bwWithFlag($conf["title"]?$conf["title"]:$dummyTitle,$isActive),$k,'edit:'.$k2)).'</td><td>'.$delIcon.'</td></tr>';
					$c=$k2;
				}
			}
			if (!t3lib_div::inList($singles,$k) || !count($iLines))	{
				$c++;
				$addIcon = $this->linkStr('<img src="'.$this->siteBackPath.'t3lib/gfx/add.gif" width="12" height="12" border="0" title="Add item">',$k,'edit:'.$c);
			} else {$addIcon = "";}

			$lines[]='<tr'.$this->bgCol(1).'><td nowrap><strong>'.$this->fw($v[0]).'</strong></td><td>'.$addIcon.'</td></tr>';
			$lines = array_merge($lines,$iLines);
		}

		$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

		$lines[]='<tr><td width=150>
		'.$this->fw("Enter extension key:").'<BR>
		<input type="text" name="'.$this->piFieldName("wizArray_upd").'[save][extension_key]" value="'.$this->wizArray["save"]["extension_key"].'">
		'.($this->wizArray["save"]["extension_key"]?"":'<BR><a href="http://typo3.org/1382.0.html" target="_blank"><font color=red>Make sure to enter the right extension key from the beginning here!</font> You can register one here.</a>').'
		</td><td></td></tr>';
# onClick="setFormAnchorPoint(\'_top\')"
		$lines[]='<tr><td><input type="submit" value="Update..."></td><td></td></tr>';
		$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName("totalForm").'" value="Total form"></td><td></td></tr>';

		if ($this->saveKey)	{
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName("viewResult").'" value="View result"></td><td></td></tr>';
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName("downloadAsFile").'" value="D/L as file"></td><td></td></tr>';
			$lines[]='<tr><td>
			<input type="hidden" name="'.$this->piFieldName("wizArray_upd").'[save][print_wop_comments]" value="0"><input type="checkbox" name="'.$this->piFieldName("wizArray_upd").'[save][print_wop_comments]" value="1" '.($this->wizArray["save"]["print_wop_comments"]?" CHECKED":"").'>'.$this->fw("Print WOP comments").'
			</td><td></td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * View result
	 */
	function view_result()	{
		$this->makeFilesArray($this->saveKey);

		$keyA = array_keys($this->fileArray);
		asort($keyA);

		$filesOverview1=array();
		$filesOverview2=array();
		$filesContent=array();
		reset($keyA);

			$filesOverview1[]= '<tr'.$this->bgCol(1).'>
				<td><strong>'.$this->fw("Filename:").'</strong></td>
				<td><strong>'.$this->fw("Size:").'</strong></td>
				<td><strong>'.$this->fw("&nbsp;").'</strong></td>
			</tr>';

		while(list(,$fileName)=each($keyA))	{
			$data = $this->fileArray[$fileName];

			$fI = pathinfo($fileName);
			if (t3lib_div::inList("php,sql,txt",strtolower($fI["extension"])))	{
				$linkToFile='<strong><a href="#'.md5($fileName).'">'.$this->fw("&nbsp;View&nbsp;").'</a></strong>';
				$filesContent[]='<tr'.$this->bgCol(1).'>
				<td><a name="'.md5($fileName).'"></a><strong>'.$this->fw($fileName).'</strong></td>
				</tr>
				<tr>
					<td>'.$this->preWrap($data["content"]).'</td>
				</tr>';
			} else $linkToFile=$this->fw("&nbsp;");

			$line = '<tr'.$this->bgCol(2).'>
				<td>'.$this->fw($fileName).'</td>
				<td>'.$this->fw(t3lib_div::formatSize($data["size"])).'</td>
				<td>'.$linkToFile.'</td>
			</tr>';
			if (strstr($fileName,"/"))	{
				$filesOverview2[]=$line;
			} else {
				$filesOverview1[]=$line;
			}
		}

		$content = '<table border=0 cellpadding=1 cellspacing=2>'.implode("",$filesOverview1).implode("",$filesOverview2).'</table>';
		$content.= $this->fw("<BR><strong>Author name:</strong> ".$GLOBALS['BE_USER']->user['realName']."
							<BR><strong>Author email:</strong> ".$GLOBALS['BE_USER']->user['email']);


		$content.= '<BR><BR>';
		if (!$this->EMmode)	{
			$content.='<input type="submit" name="'.$this->piFieldName("WRITE").'" value="WRITE to \''.$this->saveKey.'\'">';
		} else {
			$content.='
				<strong>'.$this->fw("Write to location:").'</strong><BR>
				<select name="'.$this->piFieldName("loc").'">'.
					($this->pObj->importAsType("G")?'<option value="G">Global: '.$this->pObj->typePaths["G"].$this->saveKey."/".(@is_dir(PATH_site.$this->pObj->typePaths["G"].$this->saveKey)?" (OVERWRITE)":" (empty)").'</option>':'').
					($this->pObj->importAsType("L")?'<option value="L">Local: '.$this->pObj->typePaths["L"].$this->saveKey."/".(@is_dir(PATH_site.$this->pObj->typePaths["L"].$this->saveKey)?" (OVERWRITE)":" (empty)").'</option>':'').
				'</select>
				<input type="submit" name="'.$this->piFieldName("WRITE").'" value="WRITE" onClick="return confirm(\'If the setting in the selectorbox says OVERWRITE\nthen the current extension in that location WILL be overridden! Totally!\nPlease decide if you want to continue.\n\n(Remember, this is a *kickstarter* - not an editor!)\');">
			';
		}


		$this->afterContent= '<BR><table border=0 cellpadding=1 cellspacing=2>'.implode("",$filesContent).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of Plugins:
	 */
	function add_cat_pi()	{
		$lines=array();

		$catID = "pi";

		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"<strong>Edit Plugin #".$action[1]."</strong>",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';


				// Enter title of the plugin
			$subContent="<strong>Enter a title for the plugin:</strong><BR>".
				$this->renderStringBox_lang("title",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

			$subContent = $this->renderCheckBox($ffPrefix."[plus_user_obj]",$piConf["plus_user_obj"])."USER cObjects are cached. Make it a non-cached USER_INT instead<BR>";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

			$subContent = $this->renderCheckBox($ffPrefix."[plus_not_staticTemplate]",$piConf["plus_not_staticTemplate"])."Enable this option if you want the TypoScript code to be set by default. Otherwise the code will go into a static template file which must be included in the template record (recommended is to <em>not</em> set this option).<BR>";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


				// Position
			if (is_array($this->wizArray["fields"]))	{
				$optValues = array(
					"0" => "",
				);
				reset($this->wizArray["fields"]);
				while(list($kk,$fC)=each($this->wizArray["fields"]))	{
					if ($fC["which_table"]=="tt_content")	{
						$optValues[$kk]=($fC["title"]?$fC["title"]:"Item ".$kk)." (".count($fC["fields"])." fields)";
					}
				}
				if (count($optValues)>1)	{
					$subContent="<strong>Apply a set of extended fields</strong><BR>
						If you have configured a set of extra fields (Extend existing Tables) for the tt_content table, you can have them assigned to this plugin.
						<BR>".
						$this->renderSelectBox($ffPrefix."[apply_extended]",$piConf["apply_extended"],$optValues);
					$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
			}

/*				// Enter title of the plugin
			$subContent="<strong>Enter a 'key'-string for the plugin:</strong><BR>".
				$this->renderStringBox($ffPrefix."[keystring]",$piConf["keystring"]).
				"<BR>(<em>A key string is used as a sub-prefix to the class name, in the database as identification of the plugin etc. If you don't specify any, the wizard will make one based on the title above.<BR>
					Example: If your extension has the extension key 'my_extension' and you enter the key value 'crazymenu', then the class, additional fields etc. will be named 'tx_myextension_crazymenu'<BR>
					Use a-z characters only.</em>)"
				;
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
*/




				// Insert Plugin
			if (is_array($this->wizArray["tables"]))	{
				$optValues = array(
					"0" => "",
				);
				reset($this->wizArray["tables"]);
				while(list($kk,$fC)=each($this->wizArray["tables"]))	{
					$optValues[$kk]=($fC["tablename"]||$fC["title"]?$fC["title"]." (".$this->returnName($this->extKey,"tables").($fC["tablename"]?"_".$fC["tablename"]:"").")":"Item ".$kk)." (".count($fC["fields"])." fields)";
				}
				$incListing="<BR><BR>If you have configured custom tables you can select one of the tables to list by default as an example:
						<BR>".
						$this->renderSelectBox($ffPrefix."[list_default]",$piConf["list_default"],$optValues);
				$incListing.="<BR>".$this->renderCheckBox($ffPrefix."[list_default_listmode]",$piConf["list_default_listmode"]).
					"Listing: Sections instead of table-rows";
				$incListing.="<BR>".$this->renderCheckBox($ffPrefix."[list_default_singlemode]",$piConf["list_default_singlemode"]).
					"Singleview: Sections instead of table-rows";
			} else $incListing="";


			if (!$piConf["addType"])	$piConf["addType"]="list_type";
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"list_type").
				$this->textSetup(
				"Add to 'Insert Plugin' list in Content Elements",
				"Most frontend plugins should be added to the Plugin list of Content Element type 'Insert Plugin'. This is what happens with most other plugins you know of.".
				$this->resImg("pi_pi.png").
				"<BR>".$this->renderCheckBox($ffPrefix."[plus_wiz]",$piConf["plus_wiz"]).
				"Add icon to 'New Content Element' wizard:".
				$this->resImg("pi_cewiz.png").
				"Write a description for the entry (if any):<BR>".
				$this->renderStringBox_lang("plus_wiz_description",$ffPrefix,$piConf).$incListing
				);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Text box
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"textbox").
				$this->textSetup("Add as a 'Textbox' type",
				"The Textbox Content Element is not very common but has a confortable set of fields: Bodytext and image upload.".
				$this->resImg("pi_textbox.png"));
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Menu/Sitemap
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"menu_sitemap").
				$this->textSetup("Add as a 'Menu/Sitemap' item",
					"Adds the plugin to the Menu/Sitemap list. Use this if your plugin is a list of links to pages or elements on the website. An alternative sitemap? Or some special kind of menu in a special design?".
					$this->resImg("pi_menu_sitemap.png"));
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// New content element
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"ce").
				$this->textSetup("Add as a totally new Content Element type",
					"You can also take the plunge into a whole new content element type! Scarry eh?".
					$this->resImg("pi_ce.png").
/*					$this->renderCheckBox($ffPrefix."[plus_rte]",$piConf["plus_rte"])."Enable Rich Text editing for the bodytext field<BR>".
					$this->renderCheckBox($ffPrefix."[plus_images]",$piConf["plus_images"])."Enable images-field<BR>".
					$this->renderCheckBox($ffPrefix."[plus_no_header]",$piConf["plus_images"])."Disable header rendering<BR>".
					$this->renderCheckBox($ffPrefix."[plus_insert_check]",$piConf["plus_insert_check"])."Insert a custom checkbox field<BR>".
					$this->renderCheckBox($ffPrefix."[plus_insert_select]",$piConf["plus_insert_select"])."Insert a custom select field<BR>".
					$this->renderCheckBox($ffPrefix."[plus_insert_string]",$piConf["plus_insert_string"])."Insert a custom text string field<BR>".
					$this->renderCheckBox($ffPrefix."[plus_insert_file]",$piConf["plus_insert_file"])."Insert a custom file field<BR>".
	*/				''
				);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// New header type
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"header").
				$this->textSetup("Add as a new header type",
					"Finally you might insert a new header type here:".
					$this->resImg("pi_header.png"));
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Processing of tags in content.
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"typotags").
				$this->textSetup("Processing of userdefined tag",
						htmlspecialchars("If you wish the plugin to proces content from a userdefined tag in Content Element text-fields, enter the tagname here. Eg. if you wish the tags <mytag>This is the content</mytag> to be your userdefined tags, just enter 'mytag' in this field (lowercase a-z, 0-9 and underscore):")."<BR>".
							$this->renderStringBox($ffPrefix."[tag_name]",$piConf["tag_name"])
					);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Just include library
			$subContent=$this->renderRadioBox($ffPrefix."[addType]",$piConf["addType"],"includeLib").
				$this->textSetup("Just include library",
					"In this case your library is just included when pages are rendered.<BR><BR>".
					$this->renderCheckBox($ffPrefix."[plus_user_ex]",$piConf["plus_user_ex"])."Provide TypoScript example for USER cObject in 'page.1000'<BR>"
					);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_pi'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_pi'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of TypoScript:
	 */
	function add_cat_ts()	{
		$lines=array();

		$catID = "ts";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$action[1]=1;
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Enter constants
			$subContent="<strong>Constants:</strong><BR>".
				$this->renderTextareaBox($ffPrefix."[constants]",$piConf["constants"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Enter setup
			$subContent="<strong>Setup:</strong><BR>".
				$this->renderTextareaBox($ffPrefix."[setup]",$piConf["setup"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_ts'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_ts'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of TSconfig, Page and User
	 */
	function add_cat_TSconfig()	{
		$lines=array();

		$catID = "TSconfig";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$action[1]=1;
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Enter Page TSconfige
			$subContent="<strong>Default Page TSconfig:</strong><BR>".
				$this->renderTextareaBox($ffPrefix."[page_TSconfig]",$piConf["page_TSconfig"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Enter User TSconfig
			$subContent="<strong>Default User TSconfig:</strong><BR>".
				$this->renderTextareaBox($ffPrefix."[user_TSconfig]",$piConf["user_TSconfig"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tsconfig'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tsconfig'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for backend modules
	 */
	function add_cat_module()	{
		$lines=array();

		$catID = "module";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Enter title of the module
			$subContent="<strong>Enter a title for the module:</strong><BR>".
				$this->renderStringBox_lang("title",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent="<strong>Enter a description:</strong><BR>".
				$this->renderStringBox_lang("description",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent="<strong>Enter a tab label (shorter description):</strong><BR>".
				$this->renderStringBox_lang("tablabel",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Position
			$optValues = array(
				"web" => "Sub in Web-module",
				"file" => "Sub in File-module",
				"user" => "Sub in User-module",
				"tools" => "Sub in Tools-module",
				"help" => "Sub in Help-module",
				"_MAIN" => "New main module"
			);
			$subContent="<strong>Sub- or main module?</strong><BR>".
				$this->renderSelectBox($ffPrefix."[position]",$piConf["position"],$optValues).
				$this->resImg("module.png");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Sub-position
			$optValues = array(
				"0" => "Bottom (default)",
				"top" => "Top",
				"web_after_page" => "If in Web-module, after Web>Page",
				"web_before_info" => "If in Web-module, before Web>Info",
			);
			$subContent="<strong>Position in module menu?</strong><BR>".
				$this->renderSelectBox($ffPrefix."[subpos]",$piConf["subpos"],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Admin only
			$subContent = $this->renderCheckBox($ffPrefix."[admin_only]",$piConf["admin_only"])."Admin-only access!<BR>";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Options
			$subContent = $this->renderCheckBox($ffPrefix."[interface]",$piConf["interface"])."Allow other extensions to interface with function menu<BR>";
#			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_module'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_module'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of Backend Module function-menu items
	 */
	function add_cat_moduleFunction()	{
		$lines=array();

		$catID = "moduleFunction";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Enter title of the module function
			$subContent="<strong>Enter the title of function-menu item:</strong><BR>".
				$this->renderStringBox_lang("title",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Position
			$optValues = array(
				"web_func" => "Web>Func",
				"web_func_wizards" => "Web>Func, Wizards",
				"web_info" => "Web>Info",
				"web_ts" => "Web>Template",
				"user_task" => "User>Task Center",
			);
			$subContent="<strong>Sub- or main module?</strong><BR>".
				$this->renderSelectBox($ffPrefix."[position]",$piConf["position"],$optValues).
				"<BR><BR>These images gives you an idea what the options above means:".
				$this->resImg("modulefunc_task.png").
				$this->resImg("modulefunc_func.png");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_moduleFunction'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_moduleFunction'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of Click Menu items
	 */
	function add_cat_cm()	{
		$lines=array();

		$catID = "cm";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Enter title of the module function
			$subContent="<strong>Title of the ClickMenu element:</strong><BR>".
				$this->renderStringBox_lang("title",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Position
			$optValues = array(
				"bottom" => "Insert in bottom",
				"top" => "Insert in top",
				"before_delete" => "Insert before the 'Delete' item",
			);
			$subContent="<strong>Options</strong><BR>".
				$this->renderSelectBox($ffPrefix."[options]",$piConf["options"],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Admin only
			$subContent =$this->resImg("cm.png");
			$subContent.= $this->renderCheckBox($ffPrefix."[second_level]",$piConf["second_level"])."Activate a second-level menu.<BR>";
			$subContent.= $this->renderCheckBox($ffPrefix."[only_page]",$piConf["only_page"])."Add only if the click menu is on a 'Page' (example)<BR>";
			$subContent.= $this->renderCheckBox($ffPrefix."[only_if_edit]",$piConf["only_if_edit"])."Only active if item is editable.<BR>";
			$subContent.= $this->renderCheckBox($ffPrefix."[remove_view]",$piConf["remove_view"])."Remove 'Show' element (example)<BR>";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_cm'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_cm'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}


	/**
	 * Renders form for selection of language
	 */
	function add_cat_languages()	{
		$lines=array();

		$catID = "languages";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$action[1]=1;
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Admin only
			$subContent ="";
			reset($this->languages);
			while(list($k,$v)=each($this->languages))	{
				$subContent.= $this->renderCheckBox($ffPrefix."[".$k."]",$piConf[$k]).$v."<BR>";
			}
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($this->textSetup("Enter which languages to setup:",$subContent)).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_languages'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_languages'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}


	/**
	 * Renders form for services
	 */
	function add_cat_services()	{
		$lines=array();

		$catID = 'sv';
		$action = explode(':',$this->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],'<strong>Edit Service #'.$action[1].'</strong>',$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

			if (!$this->EMmode && $this->saveKey)	{
				$extKeyRec = $this->pObj->getExtKeyRecord($this->saveKey);
			}

				// Title
			$subContent='<strong>Title:</strong><br />'.
				$this->renderStringBox($ffPrefix.'[title]',$piConf['title']?$piConf['title']:$extKeyRec['title']);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent='<strong>Description:</strong><br />'.
				$this->renderStringBox($ffPrefix.'[description]',$piConf['description']?$piConf['description']:$extKeyRec['description']);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

			$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

				// Type
			$subContent='<strong>Service type:</strong><br />'.
				$this->renderStringBox($ffPrefix.'[type]',$piConf['type']?$piConf['type']:$extKeyRec['type']).'<br />'.
				'Enter here the key to define which type of service this should be.<br />Examples: "textExtract", "metaExtract".';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// SubType
			$subContent='<strong>Sub type(s) (comma list):</strong><br />'.
				$this->renderStringBox($ffPrefix.'[subtype]',$piConf['subtype']?$piConf['subtype']:$extKeyRec['subtype']).'<br />'.
				'Possible subtypes are defined by the service type.<br />You have read the service type documentation.<br />Example: using subtypes for file types (doc, txt, pdf, ...) the service might work for.';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

			$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

				// Priority
			$optValues = Array(
				'50' => 'default (50)',
				'10' => 'very low (10)',
				'20' => 'low (20)',
				'40' => 'bit lower (40)',
				'60' => 'bit higher (60)',
				'80' => 'high (80)',
				'100' => 'Very high (100)',
			);
			$subContent='<strong>Priority:</strong><br />'.
				$this->renderSelectBox($ffPrefix.'[priority]',$piConf['priority'],$optValues).'<br />'.
				'50 = medium priority. <br />The priority of services can be changed by admin configuration.';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Quality
			$quality = $piConf['quality']?$piConf['quality']:$extKeyRec['quality'];
			$quality = $quality ? $quality : '50';
			$subContent='<strong>Quality:</strong><br />'.
				$this->renderStringBox($ffPrefix.'[quality]',$quality).'<br />'.
				'The numbering of the quality is defined by the service type.<br />You have read the service type documentation.<br />The default quality range is 0-100.';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


				// OS Dependencies
			$optValues = Array(
				'' => 'no special dependency',
				'unix' => 'Unix only',
				'win' => 'Windows only',
//				'unix,win' => 'Unix or Windows',
			);

			$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

			$subContent='<strong>Operating System dependency:</strong><br />'.
				$this->renderSelectBox($ffPrefix.'[os]',$piConf['os'],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Exec dependencies
			$subContent='<strong>External program(s) (comma list):</strong><br />'.
				$this->renderStringBox($ffPrefix.'[exec]',$piConf['exec']).'<br />'.
				'Program(s) needed to run this service (eg. "perl").';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_services'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_services'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border="0" cellpadding="2" cellspacing="2">'.implode('',$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for general settings
	 */
	function add_cat_emconf()	{
		$lines=array();

		$catID = "emconf";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$action[1]=1;
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

			if (!$this->EMmode && $this->saveKey)	{
				$extKeyRec = $this->pObj->getExtKeyRecord($this->saveKey);
			}

				// Title
			$subContent="<strong>Title:</strong><BR>".
				$this->renderStringBox($ffPrefix."[title]",$piConf["title"]?$piConf["title"]:$extKeyRec["title"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent="<strong>Description:</strong><BR>".
				$this->renderStringBox($ffPrefix."[description]",$piConf["description"]?$piConf["description"]:$extKeyRec["description"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Sub-position
			$optValues = Array(
				'' => '',
				'fe' => 'Frontend',
				'plugin' => 'Frontend Plugins',
				'be' => 'Backend',
				'module' => 'Backend Modules',
				'services' => 'Services',
				'example' => 'Examples',
				'misc' => 'Miscellaneous',
				'templates' => 'Templates',
				'doc' => 'Documentation',
			);
			$subContent="<strong>Category:</strong><BR>".
				$this->renderSelectBox($ffPrefix."[category]",$piConf["category"],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';




				// State
			$optValues = Array(
				"alpha" => "Alpha (Very initial development)",
				"beta" => "Beta (Under current development, should work partly)",
				"stable" => "Stable (Stable and used in production)",
				"experimental" => "Experimental (Nobody knows if this is going anywhere yet...)",
				"test" => "Test (Test extension, demonstrates concepts etc.)",
			);
			$subContent="<strong>State</strong><BR>".
				$this->renderSelectBox($ffPrefix."[state]",$piConf["state"],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Dependencies
			$subContent="<strong>Dependencies (comma list of extkeys):</strong><BR>".
				$this->renderStringBox($ffPrefix."[dependencies]",$piConf["dependencies"]);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';




				// Author
			$subContent="<strong>Author Name:</strong><BR>".
				$this->renderStringBox($ffPrefix."[author]",$piConf["author"]?$piConf["author"]:$GLOBALS['BE_USER']->user['realName']);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Author/Email
			$subContent="<strong>Author email:</strong><BR>".
				$this->renderStringBox($ffPrefix."[author_email]",$piConf["author_email"]?$piConf["author_email"]:$GLOBALS['BE_USER']->user['email']);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_emconf'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_emconf'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of Click Menu items
	 */
	function add_cat_save()	{
		$lines = array();

		$catID = "save";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$action[1] = 1;
			$this->regNewEntry($catID,$action[1]);

			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID];
			$ffPrefix = '['.$catID.']';


			$optValues = array();
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extrep_keytable', 'owner_fe_user='.intval($GLOBALS['TSFE']->fe_user->user['uid']).$GLOBALS['TSFE']->sys_page->enableFields('tx_extrep_keytable'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$subres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', 'tx_extrep_repository', 'extension_uid='.intval($row['uid']).' AND emconf_state IN ("alpha","beta","stable")'.$GLOBALS['TSFE']->sys_page->enableFields('tx_extrep_repository'));
				list($count) = $GLOBALS['TYPO3_DB']->sql_fetch_row($subres);
				if (!$count)	{
					$optValues[$row["extension_key"]]=$row["extension_key"].": ".$row["title"];
				}
			}
			if (count($optValues))	{
				$subContent = $this->renderSelectBox($ffPrefix."[extension_key]",$piConf["extension_key"],$optValues);
			} else {
				$subContent = '<strong>Error:</strong> The Kickstarter allows you only to save to extensions which are not yet upgraded to alpha, beta or stable state. None of these were found, so you should register a new extension key before you can save!';
			}

			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($this->textSetup("Select an extension key to save to:",$subContent)).'</td></tr>';
		}


		$this->makeRepositoryUpdateArray($piConf["extension_key"]);

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_save'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_save'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of fields
	 */
	function add_cat_fields()	{
		$lines=array();

		$catID = "fields";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

		}


				// Header field
			$optValues = array(
				"tt_content" => "Content (tt_content)",
				"fe_users" => "Frontend Users (fe_users)",
				"fe_groups" => "Frontend Groups (fe_groups)",
				"be_users" => "Backend Users (be_users)",
				"be_groups" => "Backend Groups (be_groups)",
				"tt_news" => "News (tt_news)",
				"tt_address" => "Address (tt_address)",
				"pages" => "Pages (pages)",
			);

			foreach($GLOBALS['TCA'] as $tablename => $tableTCA) {
				if(!$optValues[$tablename]) {
					$optValues[$tablename] = $GLOBALS['LANG']->sL($tableTCA['ctrl']['title']).' ('.$tablename.')';
				}
			}

			$subContent = "<strong>Which table:<BR></strong>".
					$this->renderSelectBox($ffPrefix."[which_table]",$piConf["which_table"],$optValues).
					$this->whatIsThis("Select the table which should be extended with these extra fields.");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).
				'<input type="hidden" name="'.$this->piFieldName("wizArray_upd").$ffPrefix.'[title]" value="'.($piConf["which_table"]?$optValues[$piConf["which_table"]]:"").'"></td></tr>';





				// PRESETS:
			$selPresetBox=$this->presetBox($piConf["fields"]);

				// FIelds
			$c=array(0);
			$this->usedNames=array();
			if (is_array($piConf["fields"]))	{
				$piConf["fields"] = $this->cleanFieldsAndDoCommands($piConf["fields"],$catID,$action[1]);

					// Do it for real...
				reset($piConf["fields"]);
				while(list($k,$v)=each($piConf["fields"]))	{
					$c[]=$k;
					$subContent=$this->renderField($ffPrefix."[fields][".$k."]",$v);
					$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw("<strong>FIELD:</strong> <em>".$v["fieldname"]."</em>").'</td></tr>';
					$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
			}


				// New field:
			$k=max($c)+1;
			$v=array();
			$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw("<strong>NEW FIELD:</strong>").'</td></tr>';
			$subContent=$this->renderField($ffPrefix."[fields][".$k."]",$v,1);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw("<BR><BR>Load preset fields: <BR>".$selPresetBox).'</td></tr>';

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_fields'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_fields'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';
		return $content;
	}

	/**
	 * Renders form for addition of fields
	 */
	function add_cat_tables()	{
		$lines=array();

		$catID = "tables";
		$action = explode(":",$this->modData["wizAction"]);
		if ($action[0]=="edit")	{
			$this->regNewEntry($catID,$action[1]);
			$lines = $this->catHeaderLines($lines,$catID,$this->options[$catID],"&nbsp;",$action[1]);
			$piConf = $this->wizArray[$catID][$action[1]];
			$ffPrefix='['.$catID.']['.$action[1].']';

				// Unique table name:
			$table_suffixes=array();
			if (is_array($this->wizArray[$catID]))	{
				reset($this->wizArray[$catID]);
				while(list($kk,$vv)=each($this->wizArray[$catID]))	{
					if (!strcmp($action[1],$kk))	{
						if (count($table_suffixes) && t3lib_div::inList(implode(",",$table_suffixes),$vv["tablename"]."Z"))	{
							$piConf["tablename"].=$kk;
						}
						break;
					}
					$table_suffixes[]=$vv["tablename"]."Z";
				}
			}


				// Enter title of the table
			$subContent="<strong>Tablename:</strong><BR>".
				$this->returnName($this->extKey,"tables")."_".$this->renderStringBox($ffPrefix."[tablename]",$piConf["tablename"]).
				"<BR><strong>Notice:</strong> Use characters a-z0-9 only. Only lowercase, no spaces.<BR>
				This becomes the table name in the database. ";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


				// Enter title of the table
			$subContent="<strong>Title of the table:</strong><BR>".
				$this->renderStringBox_lang("title",$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';



				// Fields - overview
			$c=array(0);
			$this->usedNames=array();
			if (is_array($piConf["fields"]))	{
				$piConf["fields"] = $this->cleanFieldsAndDoCommands($piConf["fields"],$catID,$action[1]);

				// Do it for real...
				reset($piConf["fields"]);
				$lines[]='<tr'.$this->bgCol(1).'><td><strong> Fields Overview </strong></td></tr>';
//				$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw($v[1]).'</td></tr>';
				$lines[]='<tr><td></td></tr>';

				$subContent ='<tr '.$this->bgCol(2).'>
					<td><strong>Name</strong></td>
					<td><strong>Title</strong></td>
					<td><strong>Type</strong></td>
					<td><strong>Exclude?</strong></td>
					<td><strong>Details</strong></td>
				</tr>';
				while(list($k,$v)=each($piConf["fields"]))	{
					$c[]=$k;
					$subContent .=$this->renderFieldOverview($ffPrefix."[fields][".$k."]",$v);
				}
				$lines[]='<tr'.$this->bgCol(3).'><td><table>'.$this->fw($subContent).'</table></td></tr>';
			}

			$lines[]='<tr'.$this->bgCol(1).'><td><strong> Edit Fields </strong></td></tr>';
//			$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw($v[1]).'</td></tr>';
			$lines[]='<tr><td></td></tr>';




				// Admin only
			$subContent = "";
			$subContent.= $this->renderCheckBox($ffPrefix."[add_deleted]",$piConf["add_deleted"],1)."Add 'Deleted' field ".$this->whatIsThis("Whole system: If a table has a deleted column, records are never really deleted, just 'marked deleted'. Thus deleted records can actually be restored by clearing a deleted-flag later.\nNotice that all attached files are also not deleted from the server, so if you expect the table to hold some heavy size uploads, maybe you should not set this...")."<BR>";
			$subContent.= $this->renderCheckBox($ffPrefix."[add_hidden]",$piConf["add_hidden"],1)."Add 'Hidden' flag ".$this->whatIsThis("Frontend: The 'Hidden' flag will prevent the record from being displayed on the frontend.")."<BR>".$this->resImg("t_flag_hidden.png",'hspace=20','','<BR><BR>');
			$subContent.= $this->renderCheckBox($ffPrefix."[add_starttime]",$piConf["add_starttime"])."Add 'Starttime' ".$this->whatIsThis("Frontend: If a 'Starttime' is set, the record will not be visible on the website, before that date arrives.")."<BR>".$this->resImg("t_flag_starttime.png",'hspace=20','','<BR><BR>');
			$subContent.= $this->renderCheckBox($ffPrefix."[add_endtime]",$piConf["add_endtime"])."Add 'Endtime' ".$this->whatIsThis("Frontend: If a 'Endtime' is set, the record will be hidden from that date and into the future.")."<BR>".$this->resImg("t_flag_endtime.png",'hspace=20','','<BR><BR>');
			$subContent.= $this->renderCheckBox($ffPrefix."[add_access]",$piConf["add_access"])."Add 'Access group' ".$this->whatIsThis("Frontend: If a frontend user group is set for a record, only frontend users that are members of that group will be able to see the record.")."<BR>".$this->resImg("t_flag_access.png",'hspace=20','','<BR><BR>');
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Sorting
			$optValues = array(
				"crdate" => "[crdate]",
				"cruser_id" => "[cruser_id]",
				"tstamp" => "[tstamp]",
			);
			$subContent = "";
			$subContent.= $this->renderCheckBox($ffPrefix."[localization]",$piConf["localization"])."Enabled localization features".$this->whatIsThis("If set, the records will have a selector box for language and a reference field which can point back to the original default translation for the record. These features are part of the internal framework for localization.").'<BR>';
			$subContent.= $this->renderCheckBox($ffPrefix."[versioning]",$piConf["versioning"])."Enable versioning ".$this->whatIsThis("If set, you will be able to versionize records from this table. Highly recommended if the records are passed around in a workflow.").'<BR>';
			$subContent.= $this->renderCheckBox($ffPrefix."[sorting]",$piConf["sorting"])."Manual ordering of records ".$this->whatIsThis("If set, the records can be moved up and down relative to each other in the backend. Just like Content Elements. Otherwise they are sorted automatically by any field you specify").'<BR>';
			$subContent.= $this->textSetup("","If 'Manual ordering' is not set, order the table by this field:<BR>".
				$this->renderSelectBox($ffPrefix."[sorting_field]",$piConf["sorting_field"],$this->currentFields($optValues,$piConf["fields"]))."<BR>".
				$this->renderCheckBox($ffPrefix."[sorting_desc]",$piConf["sorting_desc"])." Descending");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Type field
			$optValues = array(
				"0" => "[none]",
			);
			$subContent = "<strong>'Type-field', if any:<BR></strong>".
					$this->renderSelectBox($ffPrefix."[type_field]",$piConf["type_field"],$this->currentFields($optValues,$piConf["fields"])).
					$this->whatIsThis("A 'type-field' is the field in the table which determines how the form is rendered in the backend, eg. which fields are shown under which circumstances.\nFor instance the Content Element table 'tt_content' has a type-field, CType. The value of this field determines if the editing form shows the bodytext field as is the case when the type is 'Text' or if also the image-field should be shown as when the type is 'Text w/Image'");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Header field
			$optValues = array(
				"0" => "[none]",
			);
			$subContent = "<strong>Label-field:<BR></strong>".
					$this->renderSelectBox($ffPrefix."[header_field]",$piConf["header_field"],$this->currentFields($optValues,$piConf["fields"])).
					$this->whatIsThis("A 'label-field' is the field used as record title in the backend.");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Icon
			$optValues = array(
				"default.gif" => "Default (white)",
				"default_black.gif" => "Black",
				"default_gray4.gif" => "Gray",
				"default_blue.gif" => "Blue",
				"default_green.gif" => "Green",
				"default_red.gif" => "Red",
				"default_yellow.gif" => "Yellow",
				"default_purple.gif" => "Purple",
			);

			$subContent= $this->renderSelectBox($ffPrefix."[defIcon]",$piConf["defIcon"],$optValues)." Default icon ".$this->whatIsThis("All tables have at least one associated icon. Select which default icon you wish. You can always substitute the file with another.");
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Allowed on pages
			$subContent = "<strong>Allowed on pages:<BR></strong>".
					$this->renderCheckBox($ffPrefix."[allow_on_pages]",$piConf["allow_on_pages"])." Allow records from this table to be created on regular pages.";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Allowed in "Insert Records"
			$subContent = "<strong>Allowed in 'Insert Records' field in content elements:<BR></strong>".
					$this->renderCheckBox($ffPrefix."[allow_ce_insert_records]",$piConf["allow_ce_insert_records"])." Allow records from this table to be linked to by content elements.";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Add new button
			$subContent = "<strong>Add 'Save and new' button in forms:<BR></strong>".
					$this->renderCheckBox($ffPrefix."[save_and_new]",$piConf["save_and_new"])." Will add an additional save-button to forms by which you can save the item and instantly create the next.";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$subContent = "<strong>Notice on fieldnames:<BR></strong>".
				"Don't use fieldnames from this list of reserved names/words: <BR>
				<blockquote><em>".implode(", ",explode(",",$this->reservedTypo3Fields.",".$this->mysql_reservedFields))."</em></blockquote>";
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';



				// PRESETS:
			$selPresetBox=$this->presetBox($piConf["fields"]);

				// Fields
			$c=array(0);
			$this->usedNames=array();
			if (is_array($piConf["fields"]))	{

				// Do it for real...
				reset($piConf["fields"]);
				while(list($k,$v)=each($piConf["fields"]))	{
					$c[]=$k;
					$subContent=$this->renderField($ffPrefix."[fields][".$k."]",$v);
					$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw("<strong>FIELD:</strong> <em>".$v["fieldname"]."</em>").'</td></tr>';
					$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
			}

				// New field:
			$k=max($c)+1;
			$v=array();
			$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw("<strong>NEW FIELD:</strong>").'</td></tr>';
			$subContent=$this->renderField($ffPrefix."[fields][".$k."]",$v,1);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw("<BR><BR>Load preset fields: <BR>".$selPresetBox).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tables'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tables'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode("",$lines).'</table>';

		return $content;
	}

	/**
	 * @author	Luite van Zelst <luite@aegee.org>
	 */
	function renderFieldOverview($prefix,$fConf,$dontRemove=0)	{
			// Sorting
		$optTypes = array(
			"" => "",
			"input" => "String input",
			"input+" => "String input, advanced",
			"textarea" => "Text area",
			"textarea_rte" => "Text area with RTE",
			"textarea_nowrap" => "Text area, No wrapping",
			"check" => "Checkbox, single",
			"check_4" => "Checkbox, 4 boxes in a row",
			"check_10" => "Checkbox, 10 boxes in two rows (max)",
			"link" => "Link",
			"date" => "Date",
			"datetime" => "Date and time",
			"integer" => "Integer, 10-1000",
			"select" => "Selectorbox",
			"radio" => "Radio buttons",
			"rel" => "Database relation",
			"files" => "Files",
		);
		$optEval = array(
			"" => "",
			"date" => "Date (day-month-year)",
			"time" => "Time (hours, minutes)",
			"timesec" => "Time + seconds",
			"datetime" => "Date + Time",
			"year" => "Year",
			"int" => "Integer",
			"int+" => "Integer 0-1000",
			"double2" => "Floating point, x.xx",
			"alphanum" => "Alphanumeric only",
			"upper" => "Upper case",
			"lower" => "Lower case",
		);
		$optRte = array(
			"tt_content" => "Transform like 'Bodytext'",
			"basic" => "Typical (based on CSS)",
			"moderate" => "Transform images / links",
			"none" => "No transform",
			"custom" => "Custom transform"
		);

		switch($fConf['type']) {
			case 'rel':
				if ($fConf['conf_rel_table'] == '_CUSTOM') {
					$details .= $fConf['conf_custom_table_name'];
				} else {
					$details .= $fConf['conf_rel_table'];
				}
			break;
			case 'input+':
				if($fConf['conf_varchar']) $details[] = 'varchar';
				if($fConf['conf_unique']) $details[] = ($fConf['conf_unique'] == 'L') ?  'unique (page)': 'unique (site)';
				if($fConf['conf_eval']) $details[] = $optEval[$fConf['conf_eval']];
				$details = implode(', ', (array) $details);
			break;
			case 'check_10':
			case 'check_4':
				$details = ($fConf['conf_numberBoxes'] ? $fConf['conf_numberBoxes'] : '4') . ' checkboxes';
			break;
			case 'radio':
				if($fConf['conf_select_items']) $details = $fConf['conf_select_items'] . ' options';
			break;
			case 'select':
				if($fConf['conf_select_items']) $details[] = $fConf['conf_select_items'] . ' options';
				if($fConf['conf_select_pro']) $details[] = 'preprocessing';
				$details = implode(', ', (array) $details);
			break;
			case 'textarea_rte':
				if($fConf['conf_rte']) $details = $optRte[$fConf['conf_rte']];
			break;
			case 'files':
				$details[] = $fConf['conf_files_type'];
				$details[] = $fConf['conf_files'] . ' files';
				$details[] = $fConf['conf_max_filesize'] . ' kB';
				$details = implode(', ', (array) $details);
			break;
		}
		return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
			$fConf['fieldname'],
			$fConf['title'],
			$optTypes[$fConf['type']],
			$fConf['exludeField'] ? 'Yes' : '',
			$details
			);
	}


	function presetBox(&$piConfFields)	{
		$_PRESETS = $this->modData["_PRESET"];

		$optValues = array();

		/* Static Presets from DB-Table are disabled. Just leave the code in here for possible future use */
		//		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'kickstarter_static_presets', '');
		//		while($presetRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
		//			$optValues[] = '<option value="'.htmlspecialchars($presetRow["fieldname"]).'">'.htmlspecialchars($presetRow["title"]." (".$presetRow["fieldname"].", type: ".$presetRow["type"].")").'</option>';
		//			if (is_array($_PRESETS) && in_array($presetRow["fieldname"],$_PRESETS))	{
		//				if (!is_array($piConfFields))	$piConfFields=array();
		//				$piConfFields[] = unserialize($presetRow["appdata"]);
		//			}
		//		}

			// Session presets:
		$ses_optValues=array();
		$sesdat = $GLOBALS["BE_USER"]->getSessionData("kickstarter");
		if (is_array($sesdat["presets"]))	{
			reset($sesdat["presets"]);
			while(list($kk1,$vv1)=each($sesdat["presets"]))	{
				if (is_array($vv1))	{
					reset($vv1);
					while(list($kk2,$vv2)=each($vv1))	{
						$ses_optValues[]='<option value="'.htmlspecialchars($kk1.".".$vv2["fieldname"]).'">'.htmlspecialchars($kk1.": ".$vv2["title"]." (".$vv2["fieldname"].", type: ".$vv2["type"].")").'</option>';
						if (is_array($_PRESETS) && in_array($kk1.".".$vv2["fieldname"],$_PRESETS))	{
							if (!is_array($piConfFields))	$piConfFields=array();
							$piConfFields[] = $vv2;
						}
					}
				}
			}
		}
		if (count($ses_optValues))	{
			$optValues = array_merge($optValues,count($optValues)?array('<option value=""></option>'):array(),array('<option value="">__Fields picked up in this session__:</option>'),$ses_optValues);
		}
		if (count($optValues))		$selPresetBox = '<select name="'.$this->piFieldName("_PRESET").'[]" size='.t3lib_div::intInRange(count($optValues),1,10).' multiple>'.implode("",$optValues).'</select>';
		return $selPresetBox;
	}
	function cleanFieldsAndDoCommands($fConf,$catID,$action)	{
		$newFConf=array();
		$downFlag=0;
		reset($fConf);
		while(list($k,$v)=each($fConf))	{
			if ($v["type"] && trim($v["fieldname"]))	{
				$v["fieldname"] = $this->cleanUpFieldName($v["fieldname"]);

				if (!$v["_DELETE"])	{
					$newFConf[$k]=$v;
					if (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v["fieldname"].'_UP_x') || $downFlag)	{
						if (count($newFConf)>=2)	{
							$lastKeys = array_slice(array_keys($newFConf),-2);

							$buffer = Array();
							$buffer[$lastKeys[1]] = $newFConf[$lastKeys[1]];
							$buffer[$lastKeys[0]] = $newFConf[$lastKeys[0]];

							unset($newFConf[$lastKeys[0]]);
							unset($newFConf[$lastKeys[1]]);

							$newFConf[$lastKeys[1]] = $buffer[$lastKeys[1]];
							$newFConf[$lastKeys[0]] = $buffer[$lastKeys[0]];
						}
						$downFlag=0;
					} elseif (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v["fieldname"].'_DOWN_x'))	{
						$downFlag=1;
					}
				}

					// PRESET:
				//				if (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v["fieldname"].'_SAVE_x'))	{
				//					$datArr=Array(
				//						"fieldname" => $v["fieldname"],
				//						"title" => $v["title"],
// 						"type" => $v["type"],
// 						"appdata" => serialize($v),
// 						"tstamp" => time()
// 					);

// 					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fieldname', 'kickstarter_static_presets', 'fieldname="'.$GLOBALS['TYPO3_DB']->quoteStr($v['fieldname'], 'kickstarter_static_presets').'"');
// 					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) || $v["_DELETE"])	{
// 						if ($v["_DELETE"])	{
// 							$GLOBALS['TYPO3_DB']->exec_DELETEquery('kickstarter_static_presets', 'fieldname="'.$GLOBALS['TYPO3_DB']->quoteStr($v['fieldname'], 'kickstarter_static_presets').'"');
// 						} else {
// 							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('kickstarter_static_presets', 'fieldname="'.$GLOBALS['TYPO3_DB']->quoteStr($v['fieldname'], 'kickstarter_static_presets').'"', $datArr);
// 						}
// 					} else {
// 						$GLOBALS['TYPO3_DB']->exec_INSERTquery("kickstarter_static_presets", $datArr);
// 					}
// 				}
			} else {
			  //				unset($this->wizArray[$catID][$action]["fields"][$k]);
			  //				unset($fConf[$k]);
			}
		}
		//		debug($newFConf);
		$this->wizArray[$catID][$action]["fields"] = $newFConf;
		$sesdat = $GLOBALS["BE_USER"]->getSessionData("kickstarter");
		$sesdat["presets"][$this->extKey."-".$catID."-".$action]=$newFConf;
		$GLOBALS["BE_USER"]->setAndSaveSessionData("kickstarter",$sesdat);

#debug($newFConf);
		return $newFConf;
	}


	function renderField($prefix,$fConf,$dontRemove=0)	{
		$onCP = $this->getOnChangeParts($prefix."[fieldname]");
		$fieldName = $this->renderStringBox($prefix."[fieldname]",$fConf["fieldname"]).
			(!$dontRemove?" (Remove:".$this->renderCheckBox($prefix."[_DELETE]",0).')'.
				'<input type="image" hspace=2 src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/pil2up.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_UP" onClick="'.$onCP[1].'">'.
				'<input type="image" hspace=2 src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/pil2down.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_DOWN" onClick="'.$onCP[1].'">'.
				'<input type="image" hspace=2 src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/savesnapshot.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_SAVE" onClick="'.$onCP[1].'" title="Save this field setting as a preset.">':'');

		$fieldTitle = ((string)$fConf["type"] != 'passthrough') ? $this->renderStringBox_lang("title",$prefix,$fConf) : '';
		$typeCfg = "";

			// Sorting
		$optValues = array(
			"" => "",
			"input" => "String input",
			"input+" => "String input, advanced",
			"textarea" => "Text area",
			"textarea_rte" => "Text area with RTE",
			"textarea_nowrap" => "Text area, No wrapping",
			"check" => "Checkbox, single",
			"check_4" => "Checkbox, 4 boxes in a row",
			"check_10" => "Checkbox, 10 boxes in two rows (max)",
			"link" => "Link",
			"date" => "Date",
			"datetime" => "Date and time",
			"integer" => "Integer, 10-1000",
			"select" => "Selectorbox",
			"radio" => "Radio buttons",
			"rel" => "Database relation",
			"files" => "Files",
			"none" => "Not editable, only displayed",
			"passthrough" => "[Passthrough]",
		);
		$typeCfg.=$this->renderSelectBox($prefix."[type]",$fConf["type"],$optValues);
		$typeCfg.=$this->renderCheckBox($prefix."[excludeField]",isset($fConf["excludeField"])?$fConf["excludeField"]:1)." Is Exclude-field ".$this->whatIsThis("If a field is marked 'Exclude-field', users can edit it ONLY if the field is specifically listed in one of the backend user groups of the user.\nIn other words, if a field is marked 'Exclude-field' you can control which users can edit it and which cannot.")."<BR>";

		$fDetails="";
		switch((string)$fConf["type"])	{
			case "input+":
				$typeCfg.=$this->resImg("t_input.png",'','');

				$fDetails.=$this->renderStringBox($prefix."[conf_size]",$fConf["conf_size"],50)." Field width (5-48 relative, 30 default)<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_max]",$fConf["conf_max"],50)." Max characters<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_required]",$fConf["conf_required"])."Required<BR>";
				$fDetails.=$this->resImg("t_input_required.png",'hspace=20','','<BR><BR>');

				$fDetails.=$this->renderCheckBox($prefix."[conf_varchar]",$fConf["conf_varchar"])."Create VARCHAR, not TINYTEXT field (if not forced INT)<BR>";

				$fDetails.=$this->renderCheckBox($prefix."[conf_check]",$fConf["conf_check"])."Apply checkbox<BR>";
				$fDetails.=$this->resImg("t_input_check.png",'hspace=20','','<BR><BR>');

				$optValues = array(
					"" => "",
					"date" => "Date (day-month-year)",
					"time" => "Time (hours, minutes)",
					"timesec" => "Time + seconds",
					"datetime" => "Date + Time",
					"year" => "Year",
					"int" => "Integer",
					"int+" => "Integer 0-1000",
					"double2" => "Floating point, x.xx",
					"alphanum" => "Alphanumeric only",
					"upper" => "Upper case",
					"lower" => "Lower case",
				);
				$fDetails.="<BR>Evaluate value to:<BR>".$this->renderSelectBox($prefix."[conf_eval]",$fConf["conf_eval"],$optValues)."<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_stripspace]",$fConf["conf_stripspace"])."Strip space<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_pass]",$fConf["conf_pass"])."Is password field<BR>";
				$fDetails.=$this->resImg("t_input_password.png",'hspace=20','','<BR><BR>');

				$fDetails.="<BR>";
				$fDetails.=$this->renderRadioBox($prefix."[conf_unique]",$fConf["conf_unique"],"G")."Unique in whole database<BR>";
				$fDetails.=$this->renderRadioBox($prefix."[conf_unique]",$fConf["conf_unique"],"L")."Unique inside parent page<BR>";
				$fDetails.=$this->renderRadioBox($prefix."[conf_unique]",$fConf["conf_unique"],"")."Not unique (default)<BR>";
				$fDetails.="<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_color]",$fConf["conf_wiz_color"])."Add colorpicker wizard<BR>";
				$fDetails.=$this->resImg("t_input_colorwiz.png",'hspace=20','','<BR><BR>');
				$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_link]",$fConf["conf_wiz_link"])."Add link wizard<BR>";
				$fDetails.=$this->resImg("t_input_link2.png",'hspace=20','','<BR><BR>');
			break;
			case "input":
				$typeCfg.=$this->resImg("t_input.png",'','');

				$fDetails.=$this->renderStringBox($prefix."[conf_size]",$fConf["conf_size"],50)." Field width (5-48 relative, 30 default)<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_max]",$fConf["conf_max"],50)." Max characters<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_required]",$fConf["conf_required"])."Required<BR>";
				$fDetails.=$this->resImg("t_input_required.png",'hspace=20','','<BR><BR>');

				$fDetails.=$this->renderCheckBox($prefix."[conf_varchar]",$fConf["conf_varchar"])."Create VARCHAR, not TINYTEXT field<BR>";
			break;
			case "textarea":
			case "textarea_nowrap":
				$typeCfg.=$this->resImg("t_textarea.png",'','');

				$fDetails.=$this->renderStringBox($prefix."[conf_cols]",$fConf["conf_cols"],50)." Textarea width (5-48 relative, 30 default)<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_rows]",$fConf["conf_rows"],50)." Number of rows (height)<BR>";
				$fDetails.="<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_example]",$fConf["conf_wiz_example"])."Add wizard example<BR>";
				$fDetails.=$this->resImg("t_textarea_wiz.png",'hspace=20','','<BR><BR>');
			break;
			case "textarea_rte":
				$typeCfg.=$this->resImg($fConf["conf_rte"]!="tt_content"?"t_rte.png":"t_rte2.png",'','');

				$optValues = array(
					"tt_content" => "Transform content like the Content Element 'Bodytext' field (default/old)",
					"basic" => "Typical basic setup (new 'Bodytext' field based on CSS stylesheets)",
					"moderate" => "Moderate transform of images and links",
					"none" => "No transformation at all",
					"custom" => "Custom"
				);
				$fDetails.="<BR>Rich Text Editor Mode:<BR>".$this->renderSelectBox($prefix."[conf_rte]",$fConf["conf_rte"],$optValues)."<BR>";
				if ((string)$fConf["conf_rte"]=="custom")	{
					$optValues = array(
						"cut" => array("Cut button"),
						"copy" => array("Copy button"),
						"paste" => array("Paste button"),
						"formatblock" => array("Paragraph formatting","<DIV>, <P>"),
						"class" => array("Character formatting","<SPAN>)"),
						"fontstyle" => array("Font face","<FONT face=>)"),
						"fontsize" => array("Font size","<FONT size=>)"),
						"textcolor" => array("Font color","<FONT color=>"),
						"bold" => array("Bold","<STRONG>, <B>"),
						"italic" => array("italic","<EM>, <I>"),
						"underline" => array("Underline","<U>"),
						"left" => array("Left align","<DIV>, <P>"),
						"center" => array("Center align","<DIV>, <P>"),
						"right" => array("Right align","<DIV>, <P>"),
						"orderedlist" => array("Ordered bulletlist","<OL>, <LI>"),
						"unorderedlist" => array("Unordered bulletlist","<UL>, <LI>"),
						"outdent" => array("Outdent block","<BLOCKQUOTE>"),
						"indent" => array("Indent block","<BLOCKQUOTE>"),
						"link" => array("Link","<A>"),
						"table" => array("Table","<TABLE>, <TR>, <TD>"),
						"image" => array("Image","<IMG>"),
						"line" => array("Ruler","<HR>"),
						"user" => array("User defined",""),
						"chMode" => array("Edit source?","")
					);
					$subLines=array();
					$subLines[]='<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><strong>'.$this->fw("Button name:").'</strong></td>
						<td><strong>'.$this->fw("Tags allowed:").'</strong></td>
					</tr>';
					reset($optValues);
					while(list($kk,$vv)=each($optValues))	{
						$subLines[]='<tr>
							<td>'.$this->renderCheckBox($prefix."[conf_rte_b_".$kk."]",$fConf["conf_rte_b_".$kk]).'</td>
							<td>'.$this->resIcon($kk.".png").'</td>
							<td>'.$this->fw($vv[0]).'</td>
							<td>'.$this->fw(htmlspecialchars($vv[1])).'</td>
						</tr>';
					}
					$fDetails.='<table border=0 cellpadding=2 cellspacing=2>'.implode("",$subLines).'</table><BR>';

					$fDetails.="<BR><strong>Define specific colors:</strong><BR>
						<em>Notice: Use only HEX-values for colors ('blue' should be #0000ff etc.)</em><BR>";
					for($a=1;$a<4;$a++)	{
						$fDetails.="Color #".$a.": ".$this->renderStringBox($prefix."[conf_rte_color".$a."]",$fConf["conf_rte_color".$a],70)."<BR>";
					}
					$fDetails.=$this->resImg("t_rte_color.png",'','','<BR><BR>');

					$fDetails.=$this->renderCheckBox($prefix."[conf_rte_removecolorpicker]",$fConf["conf_rte_removecolorpicker"])."Hide colorpicker<BR>";
					$fDetails.=$this->resImg("t_rte_colorpicker.png",'hspace=20','','<BR><BR>');

					$fDetails.="<BR><strong>Define classes:</strong><BR>";
					for($a=1;$a<7;$a++)	{
						$fDetails.="Class Title:".$this->renderStringBox($prefix."[conf_rte_class".$a."]",$fConf["conf_rte_class".$a],100).
							"<BR>CSS Style: {".$this->renderStringBox($prefix."[conf_rte_class".$a."_style]",$fConf["conf_rte_class".$a."_style"],250)."}".
						"<BR>";
					}
					$fDetails.=$this->resImg("t_rte_class.png",'','','<BR><BR>');

#					$fDetails.=$this->renderCheckBox($prefix."[conf_rte_removePdefaults]",$fConf["conf_rte_removePdefaults"])."<BR>";
					$optValues = array(
						"0" => "",
						"1" => "Hide Hx and PRE from Paragraph selector.",
						"H2H3" => "Hide all, but H2,H3,P,PRE",
					);
					$fDetails.="<BR>Hide Paragraph Items:<BR>".$this->renderSelectBox($prefix."[conf_rte_removePdefaults]",$fConf["conf_rte_removePdefaults"],$optValues)."<BR>";
					$fDetails.=$this->resImg("t_rte_hideHx.png",'hspace=20','','<BR><BR>');

					$fDetails.="<BR><strong>Misc:</strong><BR>";
//					$fDetails.=$this->renderCheckBox($prefix."[conf_rte_custom_php_processing]",$fConf["conf_rte_custom_php_processing"])."Custom PHP processing of content<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_rte_div_to_p]",isset($fConf["conf_rte_div_to_p"])?$fConf["conf_rte_div_to_p"]:1).htmlspecialchars("Convert all <DIV> to <P>")."<BR>";
				}

				$fDetails.="<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_rte_fullscreen]",isset($fConf["conf_rte_fullscreen"])?$fConf["conf_rte_fullscreen"]:1)."Fullscreen link<BR>";
				$fDetails.=$this->resImg("t_rte_fullscreen.png",'hspace=20','','<BR><BR>');

				if (t3lib_div::inList("moderate,basic,custom",$fConf["conf_rte"]))	{
					$fDetails.="<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_rte_separateStorageForImages]",isset($fConf["conf_rte_separateStorageForImages"])?$fConf["conf_rte_separateStorageForImages"]:1)."Storage of images in separate folder (in uploads/[extfolder]/rte/)<BR>";
				}
				if (t3lib_div::inList("moderate,custom",$fConf["conf_rte"]))	{
					$fDetails.="<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_mode_cssOrNot]",isset($fConf["conf_mode_cssOrNot"])?$fConf["conf_mode_cssOrNot"]:1)."Use 'ts_css' transformation instead of 'ts_images-ts-reglinks'<BR>";
				}
			break;
			case "check":
				$typeCfg.=$this->resImg("t_input_link.png",'','');
				$fDetails.=$this->renderCheckBox($prefix."[conf_check_default]",$fConf["conf_check_default"])."Checked by default<BR>";
			break;
			case "select":
			case "radio":
				if ($fConf["type"]=="radio")	{
					$typeCfg.=$this->resImg("t_radio.png",'','');
				} else	{
					$typeCfg.=$this->resImg("t_sel.png",'','');
				}
				$fDetails.="<BR><strong>Define values:</strong><BR>";
				$subLines=array();
					$subLines[]='<tr>
						<td valign=top>'.$this->fw("Item label:").'</td>
						<td valign=top>'.$this->fw("Item value:").'</td>
					</tr>';
				$nItems = $fConf["conf_select_items"] = isset($fConf["conf_select_items"])?t3lib_div::intInRange(intval($fConf["conf_select_items"]),0,20):4;
				for($a=0;$a<$nItems;$a++)	{
					$subLines[]='<tr>
						<td valign=top>'.$this->fw($this->renderStringBox_lang("conf_select_item_".$a,$prefix,$fConf)).'</td>
						<td valign=top>'.$this->fw($this->renderStringBox($prefix."[conf_select_itemvalue_".$a."]",isset($fConf["conf_select_itemvalue_".$a])?$fConf["conf_select_itemvalue_".$a]:$a,50)).'</td>
					</tr>';
				}
				$fDetails.='<table border=0 cellpadding=2 cellspacing=2>'.implode("",$subLines).'</table><BR>';
				$fDetails.=$this->renderStringBox($prefix."[conf_select_items]",$fConf["conf_select_items"],50)." Number of values<BR>";

				if ($fConf["type"]=="select")	{
					$fDetails.=$this->renderCheckBox($prefix."[conf_select_icons]",$fConf["conf_select_icons"])."Add a dummy set of icons<BR>";
					$fDetails.=$this->resImg("t_select_icons.png",'hspace=20','','<BR><BR>');

					$fDetails.=$this->renderStringBox($prefix."[conf_relations]",t3lib_div::intInRange($fConf["conf_relations"],1,1000),50)." Max number of relations<BR>";
					$fDetails.=$this->renderStringBox($prefix."[conf_relations_selsize]",t3lib_div::intInRange($fConf["conf_relations_selsize"],1,50),50)." Size of selector box<BR>";

					$fDetails.=$this->renderCheckBox($prefix."[conf_select_pro]",$fConf["conf_select_pro"])."Add pre-processing with PHP-function<BR>";
				}
			break;
			case "rel":
				if ($fConf["conf_rel_type"]=="group" || !$fConf["conf_rel_type"])	{
					$typeCfg.=$this->resImg("t_rel_group.png",'','');
				} elseif(intval($fConf["conf_relations"])>1)	{
					$typeCfg.=$this->resImg("t_rel_selmulti.png",'','');
				} elseif(intval($fConf["conf_relations_selsize"])>1)	{
					$typeCfg.=$this->resImg("t_rel_selx.png",'','');
				} else {
					$typeCfg.=$this->resImg("t_rel_sel1.png",'','');
				}


				$optValues = array(
					"pages" => "Pages table, (pages)",
					"fe_users" => "Frontend Users, (fe_users)",
					"fe_groups" => "Frontend Usergroups, (fe_groups)",
					"tt_content" => "Content elements, (tt_content)",
					"_CUSTOM" => "Custom table (enter name below)",
					"_ALL" => "All tables allowed!",
				);
				if ($fConf["conf_rel_type"]!="group")	{unset($optValues["_ALL"]);}
				$optValues = $this->addOtherExtensionTables($optValues);
				$fDetails.="<BR>Create relation to table:<BR>".$this->renderSelectBox($prefix."[conf_rel_table]",$fConf["conf_rel_table"],$optValues)."<BR>";
				if ($fConf["conf_rel_table"]=="_CUSTOM")	$fDetails.="Custom table name: ".$this->renderStringBox($prefix."[conf_custom_table_name]",$fConf["conf_custom_table_name"],200)."<BR>";

				$optValues = array(
					"group" => "Field with Element Browser",
					"select" => "Selectorbox, select global",
					"select_cur" => "Selectorbox, select from current page",
					"select_root" => "Selectorbox, select from root page",
					"select_storage" => "Selectorbox, select from storage page",
				);
				$fDetails.="<BR>Type:<BR>".$this->renderSelectBox($prefix."[conf_rel_type]",$fConf["conf_rel_type"]?$fConf["conf_rel_type"]:"group",$optValues)."<BR>";
				if (t3lib_div::intInRange($fConf["conf_relations"],1,1000)==1 && $fConf["conf_rel_type"]!="group")	{
					$fDetails.=$this->renderCheckBox($prefix."[conf_rel_dummyitem]",$fConf["conf_rel_dummyitem"])."Add a blank item to the selector<BR>";
				}

				$fDetails.=$this->renderStringBox($prefix."[conf_relations]",t3lib_div::intInRange($fConf["conf_relations"],1,1000),50)." Max number of relations<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_relations_selsize]",t3lib_div::intInRange($fConf["conf_relations_selsize"],1,50),50)." Size of selector box<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_relations_mm]",$fConf["conf_relations_mm"])."True M-M relations (otherwise commalist of values)<BR>";


				if ($fConf["conf_rel_type"]!="group")	{
					$fDetails.="<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_addrec]",$fConf["conf_wiz_addrec"])."Add 'Add record' link<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_listrec]",$fConf["conf_wiz_listrec"])."Add 'List records' link<BR>";
					$fDetails.=$this->renderCheckBox($prefix."[conf_wiz_editrec]",$fConf["conf_wiz_editrec"])."Add 'Edit record' link<BR>";
					$fDetails.=$this->resImg("t_rel_wizards.png",'hspace=20','','<BR><BR>');
				}
			break;
			case "files":
				if ($fConf["conf_files_type"]=="images")	{
					$typeCfg.=$this->resImg("t_file_img.png",'','');
				} elseif ($fConf["conf_files_type"]=="webimages")	{
					$typeCfg.=$this->resImg("t_file_web.png",'','');
				} else {
					$typeCfg.=$this->resImg("t_file_all.png",'','');
				}

				$optValues = array(
					"images" => "Imagefiles",
					"webimages" => "Web-imagefiles (gif,jpg,png)",
					"all" => "All files, except php/php3 extensions",
				);
				$fDetails.="<BR>Extensions:<BR>".$this->renderSelectBox($prefix."[conf_files_type]",$fConf["conf_files_type"],$optValues)."<BR>";

				$fDetails.=$this->renderStringBox($prefix."[conf_files]",t3lib_div::intInRange($fConf["conf_files"],1,1000),50)." Max number of files<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_max_filesize]",t3lib_div::intInRange($fConf["conf_max_filesize"],1,1000,500),50)." Max filesize allowed (kb)<BR>";
				$fDetails.=$this->renderStringBox($prefix."[conf_files_selsize]",t3lib_div::intInRange($fConf["conf_files_selsize"],1,50),50)." Size of selector box<BR>";
				$fDetails.=$this->resImg("t_file_size.png",'','','<BR><BR>');
//				$fDetails.=$this->renderCheckBox($prefix."[conf_files_mm]",$fConf["conf_files_mm"])."DB relations (very rare choice, normally the commalist is fine enough)<BR>";
				$fDetails.=$this->renderCheckBox($prefix."[conf_files_thumbs]",$fConf["conf_files_thumbs"])."Show thumbnails<BR>";
				$fDetails.=$this->resImg("t_file_thumb.png",'hspace=20','','<BR><BR>');
			break;
			case "integer":
				$typeCfg.=$this->resImg("t_integer.png",'','');
			break;
			case "check_4":
			case "check_10":
				if ((string)$fConf["type"]=="check_4")	{
					$typeCfg.=$this->resImg("t_check4.png",'','');
				} else {
					$typeCfg.=$this->resImg("t_check10.png",'','');
				}
				$nItems= t3lib_div::intInRange($fConf["conf_numberBoxes"],1,10,(string)$fConf["type"]=="check_4"?4:10);
				$fDetails.=$this->renderStringBox($prefix."[conf_numberBoxes]",$nItems,50)." Number of checkboxes<BR>";

				for($a=0;$a<$nItems;$a++)	{
					$fDetails.="<BR>Label ".($a+1).":<BR>".$this->renderStringBox_lang("conf_boxLabel_".$a,$prefix,$fConf);
				}
			break;
			case "date":
				$typeCfg.=$this->resImg("t_date.png",'','');
			break;
			case "datetime":
				$typeCfg.=$this->resImg("t_datetime.png",'','');
			break;
			case "link":
				$typeCfg.=$this->resImg("t_link.png",'','');
			break;
		}

		if ($fConf["type"])	$typeCfg.=$this->textSetup("",$fDetails);

		$content='<table border=0 cellpadding=0 cellspacing=0>
			<tr><td valign=top>'.$this->fw("Field name:").'</td><td valign=top>'.$this->fw($fieldName).'</td></tr>
			<tr><td valign=top>'.$this->fw("Field title:").'</td><td valign=top>'.$this->fw($fieldTitle).'</td></tr>
			<tr><td valign=top>'.$this->fw("Field type:").'</td><td valign=top>'.$this->fw($typeCfg).'</td></tr>
		</table>';
		return $content;
	}


	function currentFields($addFields,$fArr)	{
		if (is_array($fArr))	{
			reset($fArr);
			while(list($k,$v)=each($fArr))	{
				if ($v["type"] && trim($v["fieldname"]))	{
					$addFields[trim($v["fieldname"])]=$v["fieldname"].": ".$v["title"];
				}
			}
		}
		return $addFields;
	}
	function addOtherExtensionTables($optValues)	{
		if (is_array($this->wizArray["tables"]))	{
			reset($this->wizArray["tables"]);
			while(list($k,$info)=each($this->wizArray["tables"]))	{
				if (trim($info["tablename"]))	{
					$tableName = $this->returnName($this->extKey,"tables",trim($info["tablename"]));
					$optValues[$tableName]="Extension table: ".$info["title"]." (".$tableName.")";
				}
			}
		}
		return $optValues;
	}
	function cleanUpFieldName($str)	{
		$fieldName = ereg_replace("[^[:alnum:]_]","",strtolower($str));
		if (!$fieldName || t3lib_div::inList($this->reservedTypo3Fields.",".$this->mysql_reservedFields,$fieldName) || in_array($fieldName,$this->usedNames))	{
			$fieldName.=($fieldName?"_":"").t3lib_div::shortmd5(microtime());
		}
		$this->usedNames[]=$fieldName;
		return $fieldName;
	}
	function whatIsThis($str)	{
		return ' <a href="#" title="'.htmlspecialchars($str).'" style="cursor:help" onClick="alert('.$GLOBALS['LANG']->JScharCode($str).');return false;">(What is this?)</a>';
	}
	function renderStringBox_lang($fieldName,$ffPrefix,$piConf)	{
		$content = $this->renderStringBox($ffPrefix."[".$fieldName."]",$piConf[$fieldName])." [English]";
		if (count($this->selectedLanguages))	{
			$lines=array();
			reset($this->selectedLanguages);
			while(list($k,$v)=each($this->selectedLanguages))	{
				$lines[]=$this->renderStringBox($ffPrefix."[".$fieldName."_".$k."]",$piConf[$fieldName."_".$k])." [".$v."]";
			}
			$content.=$this->textSetup("",implode("<BR>",$lines));
		}
		return $content;
	}

	function textSetup($header,$content)	{
		return ($header?"<strong>".$header."</strong><BR>":"")."<blockquote>".trim($content)."</blockquote>";
	}
	function resImg($name,$p='align="center"',$pre="<BR>",$post="<BR>")	{
		if ($this->dontPrintImages)	return "<BR>";
		$imgRel = $this->path_resources().$name;
		$imgInfo = @getimagesize(PATH_site.$imgRel);
		return $pre.'<img src="'.$this->siteBackPath.$imgRel.'" '.$imgInfo[3].($p?" ".$p:"").' vspace=5 border=1 style="border:solid 1px;">'.$post;
	}
	function resIcon($name,$p="")	{
		if ($this->dontPrintImages)	return "";
		$imgRel = $this->path_resources("icons/").$name;
		if (!@is_file(PATH_site.$imgRel))	return "";
		$imgInfo = @getimagesize(PATH_site.$imgRel);
		return '<img src="'.$this->siteBackPath.$imgRel.'" '.$imgInfo[3].($p?" ".$p:"").'>';
	}
	function path_resources($subdir="res/")	{
		return substr(t3lib_extMgm::extPath("kickstarter"),strlen(PATH_site)).$subdir;
	}
	function getOnChangeParts($prefix)	{
		$md5h=t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$prefix);
		return array('<a name="'.$md5h.'"></a>',"setFormAnchorPoint('".$md5h."');");
	}
	function renderCheckBox($prefix,$value,$defVal=0)	{
		if (!isset($value))	$value=$defVal;
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<input type="hidden" name="'.$this->piFieldName("wizArray_upd").$prefix.'" value="0"><input type="checkbox" name="'.$this->piFieldName("wizArray_upd").$prefix.'" value="1"'.($value?" CHECKED":"").' onClick="'.$onCP[1].'"'.$this->wop($prefix).'>';
	}
	function renderTextareaBox($prefix,$value)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<textarea name="'.$this->piFieldName("wizArray_upd").$prefix.'" style="width:600px;" rows="10" wrap="OFF" onChange="'.$onCP[1].'" title="'.htmlspecialchars("WOP:".$prefix).'"'.$this->wop($prefix).'>'.t3lib_div::formatForTextarea($value).'</textarea>';
	}
	function renderStringBox($prefix,$value,$width=200)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<input type="text" name="'.$this->piFieldName("wizArray_upd").$prefix.'" value="'.htmlspecialchars($value).'" style="width:'.$width.'px;" onChange="'.$onCP[1].'"'.$this->wop($prefix).'>';
	}
	function renderRadioBox($prefix,$value,$thisValue)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<input type="radio" name="'.$this->piFieldName("wizArray_upd").$prefix.'" value="'.$thisValue.'"'.(!strcmp($value,$thisValue)?" CHECKED":"").' onClick="'.$onCP[1].'"'.$this->wop($prefix).'>';
	}
	function renderSelectBox($prefix,$value,$optValues)	{
		$onCP = $this->getOnChangeParts($prefix);
		$opt=array();
		$isSelFlag=0;
		reset($optValues);
		while(list($k,$v)=each($optValues))	{
			$sel = (!strcmp($k,$value)?" SELECTED":"");
			if ($sel)	$isSelFlag++;
			$opt[]='<option value="'.htmlspecialchars($k).'"'.$sel.'>'.htmlspecialchars($v).'</option>';
		}
		if (!$isSelFlag && strcmp("",$value))	$opt[]='<option value="'.$value.'" SELECTED>'.htmlspecialchars("CURRENT VALUE '".$value."' DID NOT EXIST AMONG THE OPTIONS").'</option>';
		return $this->wopText($prefix).$onCP[0].'<select name="'.$this->piFieldName("wizArray_upd").$prefix.'" onChange="'.$onCP[1].'"'.$this->wop($prefix).'>'.implode("",$opt).'</select>';
	}
	function wop($prefix)	{
		return ' title="'.htmlspecialchars("WOP: ".$prefix).'"';
	}
	function wopText($prefix)	{
		return $this->printWOP?'<font face="verdana,arial,sans-serif" size=1 color=#999999>'.htmlspecialchars($prefix).':</font><BR>':'';
	}
	function catHeaderLines($lines,$k,$v,$altHeader="",$index="")	{
					$lines[]='<tr'.$this->bgCol(1).'><td><strong>'.$this->fw($v[0]).'</strong></td></tr>';
					$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw($v[1]).'</td></tr>';
					$lines[]='<tr><td></td></tr>';
		return $lines;
	}
	function linkCurrentItems($cat)	{
		$items = $this->wizArray[$cat];
		$lines=array();
		$c=0;
		if (is_array($items))	{
			reset($items);
			while(list($k,$conf)=each($items))	{
				$lines[]='<strong>'.$this->linkStr($conf["title"]?$conf["title"]:"<em>Item ".$k."</em>",$cat,'edit:'.$k).'</strong>';
				$c=$k;
			}
		}
		if (!t3lib_div::inList("save,ts,TSconfig,languages",$cat) || !count($lines))	{
			$c++;
			if (count($lines))	$lines[]='';
			$lines[]=$this->linkStr('Add new item',$cat,'edit:'.$c);
		}
		return $this->fw(implode("<BR>",$lines));
	}
	function linkStr($str,$wizSubCmd,$wizAction)	{
		return '<a href="#" onClick="
			document.'.$this->varPrefix.'_wizard[\''.$this->piFieldName("wizSubCmd").'\'].value=\''.$wizSubCmd.'\';
			document.'.$this->varPrefix.'_wizard[\''.$this->piFieldName("wizAction").'\'].value=\''.$wizAction.'\';
			document.'.$this->varPrefix.'_wizard.submit();
			return false;">'.$str.'</a>';
	}
	function bgCol($n,$mod=0)	{
		$color = $this->color[$n-1];
		if ($mod)	$color = t3lib_div::modifyHTMLcolor($color,$mod,$mod,$mod);
		return ' bgColor="'.$color.'"';
	}
	function regNewEntry($k,$index)	{
		if (!is_array($this->wizArray[$k][$index]))	{
			$this->wizArray[$k][$index]=array();
		}
	}
	function bwWithFlag($str,$flag)	{
		if ($flag)	$str = '<strong>'.$str.'</strong>';
		return $str;
	}
	/**
	 * Encodes extension upload array
	 */
	function makeUploadDataFromArray($uploadArray)	{
		if (is_array($uploadArray))	{
			$serialized = serialize($uploadArray);
			$md5 = md5($serialized);

			$content=$md5.":";
/*			if ($this->gzcompress)	{
				$content.="gzcompress:";
				$content.=gzcompress($serialized);
			} else {
	*/			$content.=":";
				$content.=$serialized;
//			}
		}
		return $content;
	}
	/**
	 * Make upload array out of extension
	 */
	function makeUploadArray($extKey,$files)	{
		$uploadArray=array();
		$uploadArray["extKey"]=$extKey;
		$uploadArray["EM_CONF"]=Array(
			"title" => "[No title]",
			"description" => "[Enter description of extension]",
			"category" => "example",
			"author" => $this->userfield("name"),
			"author_email" => $this->userfield("email"),

		);

		$uploadArray["EM_CONF"] = array_merge($uploadArray["EM_CONF"],$this->makeEMCONFpreset(""));

		if (is_array($this->_addArray))	{
			$uploadArray["EM_CONF"] = array_merge($uploadArray["EM_CONF"],$this->_addArray);
		}
		$uploadArray["misc"]["codelines"]=0;
		$uploadArray["misc"]["codebytes"]=0;
		$uploadArray["techInfo"] = "";

		$uploadArray["FILES"] = $files;
		return $uploadArray;
	}

	/**
	 * Getting link to this page + extra parameters, we have specified
	 *
	 * @param	array		Additional parameters specified.
	 * @return	string		The URL
	 */
	function linkThisCmd($uPA=array())	{
	  $url = t3lib_div::linkThisScript($uPA);
	  return $url;
	}

	/**
	 * Font wrap function; Wrapping input string in a <span> tag with font family and font size set
	 *
	 * @param	string		Input value
	 * @return	string		Wrapped input value.
	 */
	function fw($str)	{
		return '<span style="font-family:verdana,arial,sans-serif; font-size:10px;">'.$str.'</span>';
	}


	function piFieldName($key)	{
		return $this->varPrefix."[".$key."]";
	}
	function cmdHiddenField()	{
		return '<input type="hidden"  name="'.$this->piFieldName("cmd").'" value="'.htmlspecialchars($this->currentCMD).'">';
	}

	function preWrap($str)	{
		$str = str_replace(chr(9),"&nbsp;&nbsp;&nbsp;&nbsp;",htmlspecialchars($str));
		$str = '<pre>'.$str.'</pre>';
		return $str;
	}
}

// Include extension?
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/kickstarter/modfunc1/class.tx_kickstarter_wizard.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/kickstarter/modfunc1/class.tx_kickstarter_wizard.php"]);
}

?>