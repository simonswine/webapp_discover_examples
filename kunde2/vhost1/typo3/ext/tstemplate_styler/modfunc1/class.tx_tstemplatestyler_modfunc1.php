<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Module extension (addition to function menu) 'CSS Styler' for the 'tstemplate_styler' extension.
 * This module is a CSS style editor which reads configuration from the "editorcfg" field of templates and according sets up a hierarchy of classes etc to edit.
 * It can edit both inline style set up in a TS template record as well as external files, both css and html files.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */



require_once(PATH_t3lib."class.t3lib_extobjbase.php");
require_once(PATH_t3lib."class.t3lib_parsehtml.php");

class tx_tstemplatestyler_modfunc1 extends t3lib_extobjbase {
	var $ext_cssMarker = "###CSS_EDITOR STYLE INFORMATION MARKER";
	var $HTMLcolorList = "aqua,black,blue,fuchsia,gray,green,lime,maroon,navy,olive,purple,red,silver,teal,yellow,white";	
	
	var $ext_makeStyleDialogFlag=0;
	var $ext_compiledExamples =array();
	var $ext_compiledStylesheet =array();
	
	var $ext_depthKeysValues=array();
	
	
	var $ext_plusStyleParsed=array();
	var $ext_styleItems = array();
	var $ext_matchSelector = array();
	var $ext_collectionWithContent = array();

	var $ext_allwaysCleanUpWrittenStyles=0;	// If set, then the stylesheets written will be reformatted in a nice style.
	var $ext_oneLineMode=1;
	var $ext_addComments=0;
	
	
	function modMenu()	{
		global $LANG;
		
		return Array (
			"tx_tstemplatestyler_modfunc1_menu" => Array(
				"editor" => "Editor",
				"overview" => "Technical overview"
			),
			"tx_tstemplatestyler_modfunc1_expAll"=>"",
			"tx_tstemplatestyler_modfunc1_showContent"=>"",
			"tx_tstemplatestyler_modfunc1_multipleLines" => "",
			"tx_tstemplatestyler_modfunc1_addComments" => "",
			"tx_tstemplatestyler_modfunc1_keepitLight" => "",
			
			"tx_tstemplatestyler_styleCollection" => ""		// Set to blank - then the value is not forced to any other value...
		);		
	}

	function initialize_editor($pageId,$template_uid=0)	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $tmpl,$tplRow,$theConstants;
		
		$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");	// Defined global here!
		$tmpl->parseEditorCfgField=1;	// This makes sure that editorcfg fields are read as well!
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->resourceCheck=1;
		$tmpl->uplPath = PATH_site.$tmpl->uplPath;
		$tmpl->removeFromGetFilePath = PATH_site;
		$tmpl->init();
		
				// Gets the rootLine
		$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
		$rootLine = $sys_page->getRootLine($pageId);
		$tmpl->runThroughTemplates($rootLine,$template_uid);	// This generates the constants/config + hierarchy info for the template.
	
		$tplRow = $tmpl->ext_getFirstTemplate($pageId,$template_uid);	// Get the row of the first VISIBLE template of the page. whereclause like the frontend.
		if (is_array($tplRow))	{	// IF there was a template...
			return 1;
		}
	}
	function main()	{
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		global $tmpl,$tplRow,$theConstants;

		// **************************
		// Checking for more than one template an if, set a menu...
		// **************************
		$manyTemplatesMenu = $this->pObj->templateMenu();
		$template_uid = 0;
		if ($manyTemplatesMenu)	{
			$template_uid = $this->pObj->MOD_SETTINGS["templatesOnPage"];
		}
		
		$this->ext_oneLineMode = !$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_multipleLines"];
		$this->ext_addComments = $this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_addComments"];
		
		
		// **************************
		// Main
		// **************************
		
		// BUGBUG: Should we check if the user may at all read and write template-records???
		$existTemplate = $this->initialize_editor($this->pObj->id,$template_uid);		// initialize
		if ($existTemplate)	{
			$theOutput.=$this->pObj->doc->divider(5);
			$theOutput.=$this->pObj->doc->section("Current template:",'<img src="'.$BACK_PATH.t3lib_iconWorks::getIcon("sys_template",$tplRow).'" width=18 height=16 align=top><b>'.$this->pObj->linkWrapTemplateTitle($tplRow["title"], "config").'</b>'.htmlspecialchars(trim($tplRow["sitetitle"])?' - ('.$tplRow["sitetitle"].')':''),0,0);
			if ($manyTemplatesMenu)	{
				$theOutput.=$this->pObj->doc->section($manyTemplatesMenu,"");
				$theOutput.=$this->pObj->doc->divider(5);
			}
		}



		// If any plus-signs were clicked, it's registred:
		$updateSettings=0;
		$tsbr = t3lib_div::_GET('tsbr');
		if (is_array($tsbr))	{
			$this->pObj->MOD_SETTINGS["cssbrowser_depthKeys"] = $tmpl->ext_depthKeys($tsbr, $this->pObj->MOD_SETTINGS["cssbrowser_depthKeys"]);
			$updateSettings=1;
		}
		$this->ext_depthKeysValues=$this->pObj->MOD_SETTINGS["cssbrowser_depthKeys"];
		if ($updateSettings){	$GLOBALS["BE_USER"]->pushModuleData($this->pObj->MCONF["name"],$this->pObj->MOD_SETTINGS);}



		
			// Main function:
		$tmpl->generateConfig();	// This parses the TypoScript into the arrays needed. "editorcfg" is parsed as well
			
#debug($tmpl->editorcfg);		
#debug($tmpl->setup_editorcfg);		

			// Style collection menu
		$styleCollections = $this->makeStyleCollectionSelector();
		if (count($styleCollections))	{
			$this->pObj->MOD_MENU["tx_tstemplatestyler_styleCollection"]=array();
			reset($styleCollections);
			while(list($sk,$sv)=each($styleCollections))	{
				$this->pObj->MOD_MENU["tx_tstemplatestyler_styleCollection"][$sk]=$sv["title"];
			}
			$this->pObj->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->pObj->MOD_MENU, t3lib_div::_GP("SET"), $this->pObj->MCONF["name"]);
	
	
			
			
				// *****************
				// Go main:
				// *****************
	
				// Based on the RAW current style-collection, the resources content are now read:
				// Most likely this array should reflect something to base a hash-value upon, after having parsed the content as well in the next section:
			$this->ext_collectionWithContent = $this->readStylesheets($styleCollections[$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_styleCollection"]]);
				// parsing the style-content which was read.
				
			$this->parseStyleContent_begin($this->ext_collectionWithContent["parts"]);
	
				// OK, now $this->ext_styleItems and $this->ext_matchSelector are filled (or fetched from cache) and this means that we can actually write the new information to the right positions if we like
			
	#				debug($this->ext_styleItems);
	#				debug($this->ext_matchSelector);
			
				// If write-buttons are pressed:
			$inData = t3lib_div::_GP("tstemplatestyler");
			if ($inData["write1"] || $inData["write2"])	{
					$this->ext_doWrite=1;
					$this->makeCSSTree();
					$this->ext_doWrite=0;
	
				// preparing for writing back the style-content from $this->ext_styleItems
					$newParts = $this->prepareStyleContent($this->ext_collectionWithContent["parts"]);
	#				debug($newParts);
					$this->writeStyleContent($newParts);
	#debug($GLOBALS["tplRow"]);
						// Re-read template and stylesheets
					$this->ext_styleItems = array();
					$this->ext_matchSelector = array();
					$this->ext_collectionWithContent = array();
					
					$tmpl->generateConfig();
	#debug($tmpl->setup);
					
					$this->ext_collectionWithContent = $this->readStylesheets($styleCollections[$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_styleCollection"]]);
	#debug($this->ext_collectionWithContent);
					$this->parseStyleContent_begin($this->ext_collectionWithContent["parts"]);
			}
	
				// Editor:
			$this->ext_makeStyleDialogFlag = $this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_menu"]=="editor";
			$tree = $this->makeCSSTree($existTemplate);
	
	
	#debug($this->ext_compiledExamples);
	#debug($this->ext_compiledStylesheet);
			
			
			
			
			
			if (t3lib_div::_GP("displayStyle"))	{		// This is display of the examples etc.
				$examplesCode = implode(chr(10),$this->ext_compiledExamples);
				
				$eCArr=explode('src="EXT:',$examplesCode);
				next($eCArr);
				while(list($k,$part)=each($eCArr))	{
					list($extKey)=explode("/",$part,2);
					if (t3lib_extMgm::isLoaded($extKey))	{
						$p=t3lib_extMgm::extRelPath($extKey);
						$eCArr[$k] = $BACK_PATH.(substr($p,0,3)=="../"?"../typo3conf/ext/":"ext/").
										$eCArr[$k];
					}
				}
				$examplesCode=implode('src="', $eCArr);

#				
			
				$output = '
					<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
					
					<html>
					<head>
						<title>Untitled</title>
	
						<style>
						'.$this->ext_plusStyle().
						implode(chr(10),$this->ext_compiledStylesheet).'
						</style>
	
					</head>
					
					<body>
						<table border=0 cellpadding=0><tr><td>
							'.$examplesCode.'
						</td></tr></table>
					</body>
					</html>
				';
				echo $output;
				exit;
			}
	
			
			
	
				// Output:
			$styleColMenu = t3lib_BEfunc::getFuncMenu($this->pObj->id,"SET[tx_tstemplatestyler_styleCollection]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_styleCollection"],$this->pObj->MOD_MENU["tx_tstemplatestyler_styleCollection"]);
			$modeMenu = t3lib_BEfunc::getFuncMenu($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_menu]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_menu"],$this->pObj->MOD_MENU["tx_tstemplatestyler_modfunc1_menu"]);
				
			if (strcmp($tree,""))	{
				$theOutput.=$this->pObj->doc->spacer(5);
				$theOutput.=$this->pObj->doc->section("TypoScript CSS Style tree:",$styleColMenu.$modeMenu,0,1);
				$theOutput.=$this->pObj->doc->spacer(10);
		
				$theOutput.=$this->pObj->doc->sectionEnd();
		
				$theOutput.='<table border=0 cellpadding=1 cellspacing=0>';
				if ($this->ext_editBranch)	{
					$theOutput.='
							<tr>
								<td></td>
								<td><strong>Style preview:</strong><BR>
									<IFRAME src="index.php?id='.$this->pObj->id.'&displayStyle=1&editBranch='.$this->ext_editBranch.'" width=500 height=250 style="border: 1px solid;"></iframe>
									</td>
							</tr>';
				}
				$theOutput.='
						<tr>
							<td><img src=clear.gif width=4 height=1></td>
							<td class="bgColor2">
								<table border=0 cellpadding=0 cellspacing=0 bgcolor="#D9D5C9" width="100%"><tr><td nowrap>'.$tree.'</td></tr></table><img src=clear.gif width=465 height=1></td>
						</tr>
					</table>
				';
				
				$theOutput.= t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_expAll]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_expAll"])."Expand ALL branches (takes time)<BR>";
				$theOutput.= t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_keepitLight]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_keepitLight"])."KISS mode<BR>";
				$theOutput.= t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_multipleLines]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_multipleLines"])."Write CSS attributes in multiple lines<BR>";
				$theOutput.= t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_addComments]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_addComments"])."Write comments before selectors<BR>";
			} else {
				$theOutput.=$this->pObj->doc->section("TypoScript CSS Style tree:","
				The Stylesheet Editor is based on configuration which tells it about available CSS selectors and attributes for them. No such configuration was found in the 'editorcfg' object tree.<BR>Either there are no static templates which adds configuration or you have not added any valid configuration yourself.
				",0,2,2);
			}
	
				// Collection info:
			if (is_array($this->ext_collectionWithContent))	{
				$cInfo = $this->printCollectionInfo($this->ext_collectionWithContent["parts"]);
				$cInfoStr = '<table border=0 cellpadding=2 cellspacing=2>'.implode(chr(10),$cInfo).'</table>';
				$cInfoStr.= t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_tstemplatestyler_modfunc1_showContent]",$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_showContent"])."Show content";
				
				$theOutput.=$this->pObj->doc->section("Style collection info",$cInfoStr,0,1);
				$theOutput.=$this->pObj->doc->spacer(10);
			}
		} else {
			$theOutput.=$this->pObj->doc->section("No stylecollections","
				The stylesheet editor needs to know about one or more stylesheet(s) to edit. None is found at this point.<BR>
				You can...<BR>
				1) either manually set up stylesheets by configuring this in the 'Backend Editor Configuration' field (advanced, see documentation) or <BR>
				2) set stylesheet filenames for your PAGE objects in the TypoScript templates and they will automatically be detected and editable (recommended!)
			",0,1,1);
			$theOutput.=$this->pObj->doc->spacer(10);
		}
		

			// Cache:
		$theOutput.=$this->pObj->doc->spacer(10);
		$theOutput.=$this->pObj->doc->section("Cache",'Click here to <a href="index.php?id='.$this->pObj->id.'&clear_all_cache=1"><strong>clear all cache</strong></a>',0,1);
		
			// Ending section:
		$theOutput.=$this->pObj->doc->sectionEnd();
#debug(strlen($theOutput));
		return $theOutput;
	}


	/**
	 * This initializes the proces of rendering the style-editor tree. Finally it's prints all output rows.
	 */ 
	function makeCSSTree($existTemplate=0,$editBranch="")	{
		global $tmpl;

			// Init
		$tstemplatestyler = t3lib_div::_GP("tstemplatestyler");
		$editBranch = t3lib_div::_GP("editBranch");
		$this->ext_editBranch = $editBranch = $editBranch ? $editBranch : $tstemplatestyler["editBranch"];
#		if ($this->ext_editBranch)	$this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_expAll"]=1;
		$outlines=array();

		if (is_array($this->ext_collectionWithContent))	{
			if (is_array($this->ext_collectionWithContent["CSS_editor."]))	{
				$editorObj = $this->ext_collectionWithContent["CSS_editor."]["ch."];
				$editorTitle = $this->ext_collectionWithContent["CSS_editor"];
			} else {
				$editorObj = $tmpl->setup_editorcfg["CSS_editor."]["ch."];
				$editorTitle = $tmpl->setup_editorcfg["CSS_editor"];
			}
#debug($tmpl->setup_editorcfg);
#debug($tmpl->editorcfg);
				// Starting to make the rows:			
			$lines=array();
			if (is_array($editorObj))	{
				$lines[]=array(
						"html" => '<img src="'.$GLOBALS["BACK_PATH"].'gfx/ol/arrowbullet.gif" width="18" height="16" border="0" class="absmiddle" alt="">'.$editorTitle,
					);

				$lines=$this->makeCSSTree_recursive (
					$editorObj,
					$lines,
					'<img src="'.$GLOBALS["BACK_PATH"].'gfx/ol/blank.gif" width="18" height="16" border="0" class="absmiddle">'
				);
			} else return "";
				
				// Outputting rows:
			reset($lines);
			while(list($k,$v)=each($lines))	{
				$editFieldsRow=0;
				if (strcmp($v["html"],""))	{
					if ($this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_menu"]=="editor")	{
						$datContent="";
						$editIcon="";
						if ($v["datObj"] && !$v["exampleStop"]) $editIcon='<a href="'.t3lib_div::linkThisScript(array("editBranch"=>$v["datObj"])).'"><img src="'.$GLOBALS["BACK_PATH"].'gfx/edit2.gif" width="11" hspace=2 height="12" border="0" align="top" alt="Edit branch"></a>';
						if ($v["itemSel"])	{	// There must be a selector.
							$datContent.='<input type="hidden" name="tstemplatestyler[writeObj]['.$v["datObj"].'.selector]" value="'.htmlspecialchars($v["itemSel"]).'">';
							if ($v["attribs"] && $this->ext_makeStyleDialogFlag)	{
								if ($this->matchEB($editBranch,$v["datObj"]))	{	// $v["datObj"]==$editBranch || substr($v["datObj"],0,strlen($editBranch)+1)==$editBranch."."
									if ($this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_keepitLight"])	{
										$datContent.='<input type="text" name="'.$this->formFieldName($v["datObj"],"ALL").'" value="'.htmlspecialchars(trim($v["CSS_data"])).'" 
											title="'.htmlspecialchars(trim($v["CSS_data_default"])?"DEFAULT: ".trim($v["CSS_data_default"]):"").'"
											style="width:300px;'.(trim($v["CSS_data_default"])?"background-color:#f0f0f0;":"").'"
											'.(trim($v["CSS_data_default"])&&!$v["CSS_data"]?' onfocus="if (!this.value) {this.value=unescape(\''.rawurlencode(trim($v["CSS_data_default"])).'\');}"':'').'
											> '.$v["selector"];
									} else $datContent.=$this->makeStyleDialog($v);
									$editFieldsRow=1;
								} else {
									$datContent.=$v["CSS_data"] ? $v["selector"]." { ".$v["CSS_data"]." }" : "";
								}
							} else {
								$datContent.='<input type="hidden" name="tstemplatestyler[writeObj]['.$v["datObj"].'.style][ALL]" value="'.htmlspecialchars($v["CSS_data"]).'">';
								if ($this->matchEB($editBranch,$v["datObj"]))	{
									$editFieldsRow=1;
								}									
							}
						}
		
						if (!$editBranch || $editFieldsRow)	{
							$outlines[]='<tr class="bgColor">
								<td nowrap>'.$v["html"].'&nbsp;&nbsp;</td>
								<td>'.$editIcon.'</td>
								<td nowrap>'.$datContent.'</td>
							</tr>';
						}
					} else {
						$datContent="";
						if ($v["itemSel"])	{	// There must be a selector.
							$datContent='<input type="hidden" name="tstemplatestyler[writeObj]['.$v["datObj"].'.selector]" value="'.htmlspecialchars($v["itemSel"]).'">';
							$datContent.='<input type="text" name="tstemplatestyler[writeObj]['.$v["datObj"].'.style][ALL]" value="'.htmlspecialchars(trim(ereg_replace("[[:space:]]+"," ",$v["CSS_data"]))).'">';
						}
						$outlines[]='<tr class="bgColor">
							<td nowrap>'.$v["html"].'&nbsp;&nbsp;</td>
							<td nowrap>'.$v["selector"].'&nbsp;&nbsp;</td>
							<td nowrap>'.$datContent.'</td>
							<td nowrap>'.$v["resourcePath"].'</td>
							<td nowrap>'.$v["attribs"].'&nbsp;&nbsp;</td>
							<td nowrap>'.$v["itemSel"].'&nbsp;&nbsp;</td>
							<td nowrap>'.$v["datObj"].'&nbsp;&nbsp;</td>
						</tr>';
					}
				}
			}
		}
				


			// OUTPUT the stuff:		
		$eBCancel='';
		if ($editBranch)	{
			$eBCancel=' <input type="submit" name="_" value="Cancel" onClick="document.location=\''.t3lib_div::linkThisScript(array("editBranch"=>"")).'\';return false;">';
		}
		if ($existTemplate)	{
			$out = '<input type="submit" name="tstemplatestyler[write1]" value="Write changes">'.$eBCancel.'
				<table border=0 cellpadding=0 cellspacing=1>'.implode("",$outlines).'</table>
				<input type="hidden" name="tstemplatestyler[editBranch]" value="'.$editBranch.'">
				<input type="submit" name="tstemplatestyler[write2]" value="Write changes">'.$eBCancel.'
			';
		} else {
			$out = $GLOBALS["TBE_TEMPLATE"]->rfw("You cannot write changes to the stylesheet unless you are on a page with a template record to write to.").'
			<table border=0 cellpadding=0 cellspacing=1>'.implode("",$outlines).'</table>';
		}
		
		return $out;
	}

	/**
	 * Traverses the editorObj hierarchy, generates the basis for the rows in the editor form, renders example code, matches existing selectors etc. Called recursively.
	 */
	function makeCSSTree_recursive($eArr,$lines,$preHTML="",$accSel="",$datObj="",$cc=0,$openBranch=1,$exampleStop=0,$titlePath="")	{
		$oArr=$this->getEarr($eArr);
		$prevPointer="";
		
		reset($oArr);
		while(list($count,$itms)=each($oArr))	{
			list($title,$dat,$k)=$itms;
			if (is_array($dat) && $cc<12)	{
					// Current selector for this branch 
				$thisSel = trim(substr($dat["selector"],0,1)=="+" ? $accSel.trim(substr($dat["selector"],1)) : $accSel." ".$dat["selector"]);

					// references to this position in the hierarchy
				$datObjRef = $datObj ? $datObj.".".$k : $k;
				$thisPath = ($titlePath?$titlePath." -> ":"").$title;

					// Code for making the graphics
				$join = ($this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_expAll"] || $this->ext_editBranch) || !is_array($dat["ch."]);
				$iconN = $join?"join":($this->ext_depthKeysValues[$datObjRef]?"minus":"plus");
				if (($count+1)==count($oArr))	{
					$iconN.="bottom";
				}

					// Getting the current value of the selector from the parsed CSS files:
#				$styleInfo="";
				$styleValue="";
				$matchKey = md5(strtoupper($thisSel));
				$pointer = $this->ext_matchSelector[$matchKey];
				$rPath = $pointer[0];
				if (is_array($pointer))	{
					$this->ext_matchSelector[$matchKey]["configFound"]=1;
#					$styleInfo=$this->ext_styleItems[$pointer[0]][$pointer[1]];
#					$styleValue = $styleInfo["origAttributes"];
					$styleValue = $pointer[2];
				}

				if ($this->ext_plusStyleParsed[1][$matchKey][2])	{
					$styleValue_default=$this->ext_plusStyleParsed[1][$matchKey][2];
				} else $styleValue_default="";
				

					// Incoming data.
				if ($this->ext_doWrite)	{
					$inValueArr = $this->getInputValue($datObjRef);
					if (is_array($inValueArr))	{	// This data WAS incoming, so write it:
						$inValue = $inValueArr[0];
						
						$com = $this->ext_addComments ? chr(10).'/* '.$thisPath.' */' : "";
						
						if (is_array($pointer))	{	// AND this selector was present already
							$this->ext_styleItems[$pointer[0]][$pointer[1]]["changed"]=1;
							$this->ext_styleItems[$pointer[0]][$pointer[1]]["origAttributes"]=$inValue;
							$this->ext_styleItems[$pointer[0]][$pointer[1]]["origComment"] = $com;
							$prevPointer=$pointer[0];
						} elseif (strcmp(trim($inValue),"")) {	// ... but in this case NO such selector existed...
							$this->insertNewSelector($thisSel,$inValue,$prevPointer,$com);
						}
#						debug($thisSel,1);
					}
				}

					// Row info passed back, used to layout:
				$lines[]=array(
						"html" => $openBranch ? $preHTML.($join?'':'<a name="'.substr(md5($datObjRef),0,10).'" href="'.t3lib_div::linkThisScript(array("editBranch"=>$this->ext_editBranch,"tsbr"=>array($datObjRef=>$this->ext_depthKeysValues[$datObjRef]?0:1))).'#'.substr(md5($datObjRef),0,10).'">').'<img src="'.$GLOBALS["BACK_PATH"].'gfx/ol/'.$iconN.'.gif" width="18" height="16" border="0" class="absmiddle" alt="">'.($join?'':'</a>').(!$exampleStop?'<strong>'.$title.'</strong>':$title) : '',
						"selector" => $thisSel,
						"attribs" => $dat["attribs"],
						"CSS_data" => $styleValue,
						"CSS_data_default" => $styleValue_default,
						"itemSel" => $dat["selector"],
						"resourcePath" => $rPath,
						"exampleStop" => $exampleStop,
						"datObj" => $datObjRef
					);

				if ($styleValue && $thisSel)	{
					$this->ext_compiledStylesheet[]=$thisSel.' {'.$styleValue.'}';
				}
				if ($dat["example"] && !$exampleStop && (!$this->ext_editBranch || ($this->matchEB($this->ext_editBranch,$datObjRef)&&$openBranch)))	{
					$this->ext_compiledExamples[]=$this->wrapExample($dat["example"],$title);
				}

					// Children are processed here:
				if (is_array($dat["ch."]))	{
					$exampleWrap = explode("|",$dat["exampleWrap"]);

					if	($dat["exampleWrap"])	{
						$this->ext_compiledExamples[]=$exampleWrap[0];
						$extCE_count = count($this->ext_compiledExamples);
					}

					$thisExampleStop = isset($dat["exampleStop"]) ? $dat["exampleStop"] : $exampleStop;
					$lines = $this->makeCSSTree_recursive(
						$dat["ch."],
						$lines,
						$preHTML.'<img src="'.$GLOBALS["BACK_PATH"].'gfx/ol/'.(($count+1)==count($oArr)?"blank":"line").'.gif" width="18" height="16" border="0" class="absmiddle">',
						$thisSel,
						$datObjRef.".ch",
						$cc+1,
						$openBranch && ($this->ext_depthKeysValues[$datObjRef] || ($this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_expAll"] || $this->ext_editBranch)) ? 1 : 0,
						$thisExampleStop,
						$thisPath
					);

					if	($dat["exampleWrap"])	{
						if (count($this->ext_compiledExamples)==$extCE_count)	{
							array_pop($this->ext_compiledExamples);
						} else {
							$this->ext_compiledExamples[]=$exampleWrap[1];
						}
					}
				}
			}
		}
		return $lines;
	}

	/**
	 * Returns oArr.....
	 */
	function getEarr($eArr)	{
		global $tmpl;
		$oArr=array();
		
		reset($eArr);
		while(list($k,$v)=each($eArr))	{
			$title="";
			$dat="";
			if (is_string($v) && substr($v,0,1)=="<")	{
				$cF = t3lib_div::makeInstance("t3lib_TSparser");
				list($title,$dat)=$cF->getVal(trim(substr($v,1)),$tmpl->setup_editorcfg);

				if (is_array($eArr[$k."."]) && count($eArr[$k."."]))	{
					$dat = $this->joinTSarrays($dat,$eArr[$k."."]);
				}
				$title = $dat["title"]?$dat["title"]:$title;
			} elseif (is_array($v) && substr($eArr[substr($k,0,-1)],0,1)!="<")	{
				$k=substr($k,0,-1);
				$title = $v["title"]?$v["title"]:$eArr[$k];
				$dat = $v;
			}
			if (is_array($dat))		$oArr[]=array($title,$dat,$k);
		}
		return $oArr;
	}

	/**
	 * Joins two TypoScript arrays
	 */
	function joinTSarrays($conf,$old_conf)	{
		if (is_array($old_conf))	{
			reset($old_conf);
			while(list($key,$val)=each($old_conf))	{
				if (is_array($val))	{
					$conf[$key] = $this->joinTSarrays($conf[$key],$val);
				} else {
					$conf[$key] = $val;
				}
			}
		}
		return $conf;
	}

	/**
	 * Returns information of the input value of $objRef
	 */
	function getInputValue($objRef)	{
		$value="";
		$POST = t3lib_div::_POST();
		if (is_array($POST["tstemplatestyler"]["writeObj"][$objRef.".style"]))	{
			reset($POST["tstemplatestyler"]["writeObj"][$objRef.".style"]);
			while(list($k,$v)=each($POST["tstemplatestyler"]["writeObj"][$objRef.".style"]))	{
				if ($k!="ALL" && strcmp("",trim($v)))	{
					if (substr($k,0,7)=="margin-" && t3lib_div::testInt(trim($v)))	{
						$v=trim($v)."px";
					}
					if (substr($k,0,8)=="padding-" && t3lib_div::testInt(trim($v)))	{
						$v=trim($v)."px";
					}
					if ($k=="width" && t3lib_div::testInt(trim($v)))	{
						$v=trim($v)."px";
					}
					$value.=$k.":".trim($v)."; ";
#debug($value,1);
				}
			}
			$value.= $POST["tstemplatestyler"]["writeObj"][$objRef.".style"]["ALL"];
			$value = trim($value);
			return array($value);
		}
	}
	
	/**
	 * Inserts a new selector in a stylesheet.
	 */
	function insertNewSelector($inSelector,$inValue,$prevPointer,$comment="")	{
		if (!$prevPointer || !isset($this->ext_styleItems[$prevPointer]))	{
			end($this->ext_styleItems);
			$prevPointer=key($this->ext_styleItems);
			
				// Here we might also find another stylesheet based on object-string or if a preferred flag was set (which is not specified yet...)
		}
		
		$newEl=array(
			"changed"=>1,
			"origSelector" => $inSelector,
			"selector" => $inSelector,
			"origAttributes" => $inValue,
			"origComment" => $comment
		);
		array_splice ($this->ext_styleItems[$prevPointer], -1, 0, array($newEl));	// MUST be inserted as the second last item, because the last item is always a dummy and all the other items must have proper key-relations with the $this->ext_matchSelector
	}
	
	/**
	 * Wrap example in dotted table
	 */
	function wrapExample($str,$title)	{
		$str = $title.'<br><div style="border: dotted #666666 1px; margin-bottom:10px;">'.$str.'</div>';
		return $str;
	}
	
	/**
	 * Wrap example in dotted table
	 */
	function matchEB($editBranch,$datObjRef)	{
		return $datObjRef==$editBranch || substr($datObjRef,0,strlen($editBranch)+1)==$editBranch.".";
	}
	





	
	
	
	
	
	
	
	
	
	
	
	/********************************

	 	Style editor functions

	 ********************************/



	/**
	 * Style editor boxes
	 */
	function makeStyleDialog($vArray)	{
		$el=array();
			// Splitting style data into an array:
		$styleAttribs=$this->styleAttributes($vArray["CSS_data"]);

			// Make form elements:		
		$vArray["attribs"] = str_replace("ALL","TEXT,TEXT+,BULLET,BORDER,background-color,margin+,padding+",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("HEADER","TEXT,margin-top,margin-bottom",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("TABLE","BORDER,margin+,background-color",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("TD","BORDER,vertical-align,padding+,background-color",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("BORDER","border-color,border-style,border-width",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("BODYTEXT","TEXT,line-height,margin-top,margin-bottom",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("TEXT+","white-space,letter-spacing,word-spacing,text-decoration,text-align,text-transform,text-indent,line-height",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("TEXT","font-family,font-size,color,font-weight,font-style,font-variant",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("LINKS","color,text-decoration",$vArray["attribs"]);
		$vArray["attribs"] = str_replace("BULLETS","list-style-type,list-style-position,margin-left,margin-right,margin-top,margin-bottom",$vArray["attribs"]);
#debug(array($vArray["attribs"]));
		$editorItems = array_unique(t3lib_div::trimExplode(",",$vArray["attribs"],1));
		reset($editorItems);
		while(list(,$v)=each($editorItems))	{
			$notProcessed=0;
			switch((string)$v)	{
				case "font-size":
					$optValues=array(
						"8px"=>"8",
						"9px"=>"9",
						"10px"=>"10",
						"11px"=>"11",
						"12px"=>"12",
						"13px"=>"13",
						"14px"=>"14",
						"15px"=>"15",
						"16px"=>"16",
						"17px"=>"17",
						"18px"=>"18",
						"20px"=>"20",
						"22px"=>"22",
						"24px"=>"24",
						"26px"=>"26",
						"28px"=>"28",
						"30px"=>"30",
						"33px"=>"33",
						"36px"=>"36",
						"40px"=>"40",
						"44px"=>"44",
						"48px"=>"48"
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Size");
				break;
				case "font-family":
					$optValues=array(
						'Verdana, Geneva, Arial, Helvetica, sans-serif' => "Verdana+",
						'Arial, Helvetica, sans-serif' => "Arial+",
						'Times, "Times New Roman", serif;' => "Times+",
						'"Courier New", Courier, monospace;' => "Courier+",
						"Verdana",
						"Arial",
						"Times",
						"Comic Sans MS",
						'Georgia, serif',
						"serif",
						"sans-serif",
						"monospace",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Font");
				break;
				case "font-weight":
					$optValues=array(
						"normal",
						"bold",
						"bolder",
						"lighter",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Weight");
				break;
				case "font-style":
					$optValues=array(
						"normal",
						"italic",
						"oblique",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Style");
				break;
				case "font-variant":
					$optValues=array(
						"normal",
						"small-caps",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Variant");
				break;
				case "white-space":
					$optValues=array(
						"normal",
						"pre",
						"nowrap",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"WhiteSpc");
				break;
				case "letter-spacing":
					$optValues=array(
						"normal",
						"-2px",
						"-1px",
						"1px",
						"2px",
						"3px",
						"4px",
						"5px",
						"6px",
						"7px",
						"8px",
						"9px",
						"10px",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Char Spacing");
				break;
				case "word-spacing":
					$optValues=array(
						"normal",
						"-2px",
						"-1px",
						"1px",
						"2px",
						"3px",
						"4px",
						"5px",
						"6px",
						"7px",
						"8px",
						"9px",
						"10px",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Word Spacing");
				break;
				case "text-decoration":
					$optValues=array(
						"none",
						"underline",
						"overline",
						"line-through",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Decoration");
				break;
				case "text-align":
					$optValues=array(
						"left",
						"right",
						"center",
						"justify",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Align");
				break;
				case "text-transform":
					$optValues=array(
						"capitalize",
						"uppercase",
						"lowercase",
						"none",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Transform");
				break;
				case "text-indent":
					$optValues=array(
						"-30px",
						"-20px",
						"-10px",
						"0px" => "Normal",
						"10px",
						"20px",
						"30px",
						"40px",
						"50px",
						"70px",
						"100px",
						"130px",
						"170px",
						"200px",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Indent");
				break;
				case "line-height":
					$optValues=array(
						"normal",
						"70%"=>"0.7",
						"80%"=>"0.8",
						"90%"=>"0.9",
						"110%"=>"1.1",
						"120%"=>"1.2",
						"130%"=>"1.3",
						"140%"=>"1.4",
						"150%"=>"1.5",
						"160%"=>"1.6",
						"170%"=>"1.7",
						"180%"=>"1.8",
						"200%"=>"2",
						"220%"=>"2.2",
						"250%"=>"2.5",
						"300%"=>"3",
						"350%"=>"3.5",
						"400%"=>"4",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Line Height");
				break;
				case "list-style-type":
					$optValues=array(
						"disc" => "Normal",
						"circle",
						"square",
						"decimal-leading-zero",
						"decimal",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"List type");
				break;
				case "list-style-position":
					$optValues=array(
						"outside" => "Normal",
						"inside" => "Inside",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"List pos:");
				break;
				case "margin+":
					$el[]='Margin T:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"margin-top").'" value="'.htmlspecialchars($styleAttribs["margin-top"]).'"'.$this->styleCode($styleAttribs["margin-top"],"width:35px;").'>'.
							'R:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"margin-right").'" value="'.htmlspecialchars($styleAttribs["margin-right"]).'"'.$this->styleCode($styleAttribs["margin-right"],"width:35px;").'>'.
							'B:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"margin-bottom").'" value="'.htmlspecialchars($styleAttribs["margin-bottom"]).'"'.$this->styleCode($styleAttribs["margin-bottom"],"width:35px;").'>'.
							'L:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"margin-left").'" value="'.htmlspecialchars($styleAttribs["margin-left"]).'"'.$this->styleCode($styleAttribs["margin-left"],"width:35px;").'>'.
							'';
					unset($styleAttribs["margin-top"]);
					unset($styleAttribs["margin-right"]);
					unset($styleAttribs["margin-bottom"]);
					unset($styleAttribs["margin-left"]);
				break;
				case "padding+":
					$el[]='Padding T:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"padding-top").'" value="'.htmlspecialchars($styleAttribs["padding-top"]).'"'.$this->styleCode($styleAttribs["padding-top"],"width:35px;").'>'.
							'R:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"padding-right").'" value="'.htmlspecialchars($styleAttribs["padding-right"]).'"'.$this->styleCode($styleAttribs["padding-right"],"width:35px;").'>'.
							'B:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"padding-bottom").'" value="'.htmlspecialchars($styleAttribs["padding-bottom"]).'"'.$this->styleCode($styleAttribs["padding-bottom"],"width:35px;").'>'.
							'L:<input type="text" name="'.$this->formFieldName($vArray["datObj"],"padding-left").'" value="'.htmlspecialchars($styleAttribs["padding-left"]).'"'.$this->styleCode($styleAttribs["padding-left"],"width:35px;").'>'.
							'';
					unset($styleAttribs["padding-top"]);
					unset($styleAttribs["padding-right"]);
					unset($styleAttribs["padding-bottom"]);
					unset($styleAttribs["padding-left"]);
				break;
				case "padding-top":
				case "padding-bottom":
				case "padding-left":
				case "padding-right":
				case "margin-top":
				case "margin-bottom":
				case "margin-left":
				case "margin-right":
					$el[]='<input type="text" name="'.$this->formFieldName($vArray["datObj"],$v).'" value="'.htmlspecialchars($styleAttribs[$v]).'" title="'.$v.'"'.$this->styleCode($styleAttribs[$v],"width:35px;").'>';
				break;
				case "border-style":
					$optValues=array(
						"none",
						"solid",
						"dotted",
						"dashed",
						"double",
						"groove",
						"ridge",
						"inset",
						"outset",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Border style");
				break;
				case "border-width":
					$optValues=array(
						"0px" => "None",
						"1px",
						"2px",
						"3px",
						"4px",
						"5px",
						"6px",
						"7px",
						"8px",
						"9px",
						"10px",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Width");
				break;
				case "vertical-align":
					$optValues=array(
						"top",
						"middle",
						"bottom",
					);
					$el[]=$this->renderSelector($this->formFieldName($vArray["datObj"],$v),$optValues,$styleAttribs[$v],"Vertical Align");
				break;
				case "border-color":
				case "color":
				case "background-color":
					$optValues=explode(",",$this->HTMLcolorList);
					$onChange="document.editForm['".$this->formFieldName($vArray["datObj"],$v)."'].value=this.options[this.selectedIndex].value; this.selectedIndex=0;";
					$el[]=$this->renderSelector("__".md5($this->formFieldName($vArray["datObj"],$v)),$optValues,"","Color",$onChange).
						'<input type="text" name="'.$this->formFieldName($vArray["datObj"],$v).'" value="'.htmlspecialchars($styleAttribs[$v]).'" title="'.$v.'"'.$this->styleCode($styleAttribs[$v],"width:60px;").'>';
				break;
				case "width":
					$el[]='<input type="text" name="'.$this->formFieldName($vArray["datObj"],$v).'" value="'.htmlspecialchars($styleAttribs[$v]).'" title="'.$v.'"'.$this->styleCode($styleAttribs[$v],"width:60px;").'>';
				break;
				default:
					$notProcessed=1;
#debug($v,1);
				break;
			}
			if (!$notProcessed)	{
				unset($styleAttribs[$v]);
			}
		}
			// If there are more data left:
		if (count($styleAttribs))	{
			$all="";
			reset($styleAttribs);
			while(list($k,$v)=each($styleAttribs))	{
				$all.=$k.':'.$v.';';
			}
			$el[]='<input type="text" name="'.$this->formFieldName($vArray["datObj"],"ALL").'" value="'.htmlspecialchars($all).'">';
		}
		return implode("&nbsp;&nbsp;",$el);
	}

	/**
	 * Returns the formfield name for an object.
	 */
	function formFieldName($datObj,$attrib)	{
		return 'tstemplatestyler[writeObj]['.$datObj.'.style]['.$attrib.']';
	}

	/**
	 * Renders a selector box
	 */	
	function renderSelector($name,$optValues,$value,$title,$onChange="")	{
		$opt=Array();
		$opt[]='<option value="" style="color:#999999;">'.$title.'</option>';
		$notSel=0;
		reset($optValues);
		while(list($k,$v)=each($optValues))	{
			if (t3lib_div::testInt($k))	$k=$v;
			if (!$notSel && !strcmp($value,$k))		$notSel=1;
			$opt[]='<option value="'.htmlspecialchars($k).'"'.(!strcmp($value,$k)?" SELECTED":"").'>'.htmlspecialchars($v).'</option>';
		}
		if (!$notSel && strcmp($value,""))	{
			$opt[]='<option value="">--</option>';
			$opt[]='<option value="'.htmlspecialchars($value).'" SELECTED style="color:red;">'.htmlspecialchars($value).'</option>';
		}
		$out = '<select name="'.$name.'"'.($onChange?' onChange="'.$onChange.'"':'').$this->styleCode($value).'>'.implode("",$opt).'</select>';
		return $out;
	}
	
	/**
	 * Returns the style-attribute for background color of an item.
	 */
	function styleCode($v,$style="")	{
		if (!strcmp($v,""))	{
			return ' style="background-color:'.$this->pObj->doc->bgColor.';'.$style.'"';
		} else {
			return ' style="'.$style.'"';
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/****************************************************************
	
		CSS style sheet parsing + Style collection manipulation
	
	****************************************************************/

	/**
	 * Takes CSS content as input, parses it into an array
	 */
	function parseStyleSheet($input,$fileref,$matchSel=array())	{

		$styleItems=array();
		
		$parts = explode("}",$input);
		while(list($k,$v)=each($parts))	{
			$tmpItem=array();
			$tmpItem["rawContent"]=$v;
			$tmpItem["changed"]=0;
			if ($k+1<count($parts))	{
				$subparts = t3lib_div::revExplode("{",$v,2);
				if (count($subparts)==2)	{	// there is an item here.
						// Attributes:
#					$attribs = $this->styleAttributes($subparts["1"]);

						// Selector and comments:
					$selector = strrev($subparts["0"]);
					$sParts = split(">--|\/\*",$selector,2);
					$tmpItem["origComment"] = strrev(substr($selector,strlen($sParts[0])));
					$tmpItem["origSelector"] = strrev($sParts[0]);
					$tmpItem["origAttributes"] = $subparts["1"];
					
					$tmpItem["selector"] = ereg_replace("[[:space:]]"," ",trim($tmpItem["origSelector"]));
					$tmpItem["matchSelector"] = strtoupper($tmpItem["selector"]);
					$tmpItem["matchSelector_md5"] = md5($tmpItem["matchSelector"]);
#					$tmpItem["attributes"] = $attribs;

					$selStyleAttributes=$tmpItem["origAttributes"];
					if (isset($matchSel[$tmpItem["matchSelector_md5"]]))	{
#debug(array($tmpItem["matchSelector"],$matchSel[$tmpItem["matchSelector_md5"]][2],$selStyleAttributes));
						$selStyleAttributes = $matchSel[$tmpItem["matchSelector_md5"]][2].$selStyleAttributes;
					}
					$matchSel[$tmpItem["matchSelector_md5"]]=array(
						$fileref,
						$k,
						$this->printAttributes($this->styleAttributes($selStyleAttributes),1)
					);
				}
			}
			$styleItems[$k]=$tmpItem;
		}
		return array($styleItems,$matchSel);	
	}
	
	/**
	 * Taking the content of {...} of a CSS stylesheet, explodes the attributes into an array with attributes/values being keys/values
	 */
	function styleAttributes($data)	{
			// Splitting style data into an array:
		$styleAttribs=array();
		$temp_data = t3lib_div::trimExplode(";",$data,1);
		reset($temp_data);
		while(list($k,$v)=each($temp_data))	{
			$p=explode(":",$v,2);
			$styleAttribs[trim($p[0])]=trim($p[1]);
		}
		return $styleAttribs;
	}

	/**
	 *	Returns a "clean" selector with equal distances between parts, element names in uppercase
	 */
	function cleanUpSelector($selector)	{
		$parts = t3lib_div::trimExplode(",",$selector,1);
		while(list($k,$v)=each($parts))	{
			$sParts = explode(" ",$v);
			while(list($kk,$vv)=each($sParts))	{	
				$ssParts = split("[^[:alnum:]]",$vv,2);
				if (strcmp($ssParts[0],""))	{
					$sParts[$kk]=strtoupper($ssParts[0]).substr($vv,strlen($ssParts[0]));
				}
			}
			$parts[$k]=trim(implode(" ",$sParts));
		}
		$selector=implode(", ",$parts);
		return $selector;
	}

	/**
	 * Begin 
	 */
	function parseStyleContent_begin($colParts)	{
			// Set plusStyle 
		$plusStyle=$this->ext_plusStyle();
		if ($plusStyle)	{
			$thisID="_CSS_DEFAULT_STYLE";
			$this->ext_plusStyleParsed = $styleItems = $this->parseStyleSheet($plusStyle,$thisID,$this->ext_matchSelector);
				// If these lines are unset, the default style will automatically be included with the otehr CSS stylesheet parts.
	#		$this->ext_styleItems[$thisID]=$styleItems[0];
	#		$this->ext_matchSelector=$styleItems[1];
		}
		
		$this->parseStyleContent($colParts);
	}

	
	/**
	 * This parses each content section in the collection array:
	 */
	function parseStyleContent($colParts,$path="")	{
		if (is_array($colParts))	{
			reset($colParts);
			while(list($k,$v)=each($colParts))	{
				$thisID = $path.$k."|".$v["file_id"];
				if (is_array($v["content"]))	{
					$this->parseStyleContent($v["content"],$thisID."|");
				} else {
					$styleItems = $this->parseStyleSheet($v["content"],$thisID,$this->ext_matchSelector);
					$this->ext_styleItems[$thisID]=$styleItems[0];
					$this->ext_matchSelector=$styleItems[1];
				}
			}
		}
	}
	
	/**
	 * This parses each content section in the collection array:
	 */
	function prepareStyleContent($colParts,$path="")	{
		if (is_array($colParts))	{
			reset($colParts);
			while(list($k)=each($colParts))	{
				$thisID = $path.$k."|".$colParts[$k]["file_id"];
				if (is_array($colParts[$k]["content"]))	{
					$colParts[$k]["content"] = $this->prepareStyleContent($colParts[$k]["content"],$thisID."|");
				} else {
					$parts=array();
					reset($this->ext_styleItems[$thisID]);
					while(list($kk,$vv)=each($this->ext_styleItems[$thisID]))	{
						if (trim($vv["rawContent"]) || $vv["changed"])	{
							if ($this->ext_allwaysCleanUpWrittenStyles || $vv["changed"])	{
								$parts[]=	chr(10).trim($vv["origComment"]).
											(trim($vv["origComment"])?chr(10):"").
											$this->cleanUpSelector($vv["selector"])." {".
											$this->printAttributes($this->styleAttributes($vv["origAttributes"]));
							} else {
								$parts[]=$vv["origComment"].$vv["origSelector"]."{".$vv["origAttributes"];
							}
						} else $parts[]=$vv["rawContent"];
					}
					$colParts[$k]["content2"]=trim(implode("}",$parts));
					$colParts[$k]["content2_md5"]=md5($colParts[$k]["content2"]);
				}
			}
		}
		return $colParts;
	}

	/**
	 * Writes the stylesheet content back...
	 */
	function writeStyleContent($colParts)	{
		global $tmpl,$tplRow;
		if (is_array($colParts))	{
			reset($colParts);
			while(list($k,$v)=each($colParts))	{
				if (strcmp($v["file_id"],"INLINE"))	{
					$fileRef = $this->getFileName($v["file_id"]);
					if ($fileRef)	{
						$fI=pathinfo($fileRef);
						$ext=strtolower($fI["extension"]);
						if (t3lib_div::inList("html,htm",$ext))	{
							$this->writeHTMLdocument(PATH_site.$fileRef, $v["content"]);
						} elseif (t3lib_div::inList("css",$ext))	{
							$cssFileContent = t3lib_div::getUrl(PATH_site.$fileRef);
							$cssFileContent_md5=md5($cssFileContent);
							if ($cssFileContent_md5==$v["content_md5"])	{
								$this->writeResourceFile(PATH_site.$fileRef,$v["content2"]);
							} else debug("ERROR: The file '".$fileRef."' has changed content...");
						} else {
							$v["ERROR"]="Not valid fileextension (must be html, htm, css)";
						}
					} else {
						$v["ERROR"]="No file found";
					}
				} elseif ($v["pageObject"]) {
					$writeContent=$v["pageObject"]."CSS_inlineStyle ( ".chr(10).$this->prefixLines(trim($v["content2"]),"  ").chr(10).")";
#debug("WRITING TO template record: ".$tplRow["uid"]);
					$this->writeStyle($tplRow["uid"],$tplRow["config"],$writeContent);
				} else {
debug("ERROR: Unrecognized section:");
debug($v);
				}
			}
		}
	}

	/**
	 * Writes <STYLE> section + <LINK>-stylesheets of an HTML-document
	 */
	function writeHTMLdocument($document,$writeArray)	{
#debug($writeArray);
		$content = t3lib_div::getUrl($document);
		$error="";
		
		$parser = t3lib_div::makeInstance("t3lib_parsehtml");
		$headerSection = $parser->splitIntoBlock("head",$content);
		if (count($headerSection)>1)	{
			$internalSections = $parser->splitIntoBlock("style",$headerSection[1]);

			reset($internalSections);
			while(list($k,$v)=each($internalSections))	{
				if ($k%2)	{
					$firstTag=$parser->getFirstTag($v);
					
					$currentContent=$parser->removeFirstAndLastTag($v);
					$currentContent_md5 = md5($currentContent);
					if ($writeArray[$k]["file_id"]=="INLINE" && $writeArray[$k]["content_md5"]==$currentContent_md5)	{
						$internalSections[$k]=$firstTag.chr(10).trim($writeArray[$k]["content2"]).chr(10).'</STYLE>';
					} else $error="Section ".$k." in HTML template was not INLINE or the content was not as expected.";
				} else {
					$nonStyleParts=$parser->getAllParts($parser->splitTags("link",$v));
					reset($nonStyleParts);
					while(list($k2,$v2)=each($nonStyleParts))	{
						list($attrib) = $parser->get_tag_attributes($v2,1);
						$pI = parse_url($attrib["href"]);
						if (!$pI["scheme"] && $pI["path"])	{
							$file = dirname($document)."/".$pI["path"];
							if (@is_file($file))	{
								$currentContent=t3lib_div::getUrl($file);
								$currentContent_md5 = md5($currentContent);
#					debug(array(md5($currentContent),md5($writeArray[$k]["content"][$k2]["content"])));
#					debug($writeArray[$k]["content"][$k2]);
								if ($writeArray[$k]["content"][$k2]["file_id"]==substr($file,strlen(PATH_site)) && $writeArray[$k]["content"][$k2]["content_md5"]==$currentContent_md5)	{
									$theContent = $writeArray[$k]["content"][$k2]["content2"];
									$this->writeResourceFile($file,$theContent);
#debug("WRITING: ".$file);
#debug(array($theContent));
								} else debug("Section ".$k."/".$k2." in HTML template was not INLINE or the content was not as expected.");
							}
						}
					}
				}
			}
			if (!$error)	{
				$headerSection[1] = implode("",$internalSections);
				$this->writeResourceFile($document,implode("",$headerSection));
#debug(array(implode("",$headerSection)));
			} else debug("ERROR: ".$error);
		} else debug("ERROR: No header section?");
	}
	
	/**
	 * Writing resource file.
	 */
	function writeResourceFile($absFileRef,$content)	{
		if (t3lib_div::isFirstPartOfStr($absFileRef,PATH_site))		{
			if (is_file($absFileRef))	{
				$relFile = substr($absFileRef,strlen(PATH_site));
				if (t3lib_div::isFirstPartOfStr($relFile,"uploads/") || t3lib_div::isFirstPartOfStr($relFile,"fileadmin/"))	{
					t3lib_div::writeFile($absFileRef,$content);
					
#					debug("Writing: ".$absFileRef);
#					debug(array($content));
				} else debug("File '".$absFileRef."' not found in either uploads/* or fileadmin/* dirs.");
			} else debug("Not a file! ".$absFileRef);
		} else debug("ERROR: The file '".$absFileRef."' was not in the PATH_site path: '".PATH_site."'");
	}

	/**
	 * Writing $addData into the special CSS_inlineStyle section in the current template record.
	 */
	function writeStyle($uid,$currentData,$addData)	{
		global $tmpl;
#debug(array($addData));
#debug(md5($addData));
		require_once (PATH_t3lib."class.t3lib_tcemain.php");
			// Set the data to be saved
		$recData=array();
		$recData["sys_template"][$uid]["config"] = trim($this->mergeData($currentData,$addData)).chr(10);
#debug($recData);

			// Create new  tce-object
		$tce = t3lib_div::makeInstance("t3lib_TCEmain");
		$tce->stripslashes_values=0;
		
			// Initialize
		$tce->start($recData,Array());
	
			// Saved the stuff
		$tce->process_datamap();
	
			// Clear the cache (note: currently only admin-users can clear the cache in tce_main.php)
		$tce->clear_cacheCmd("all");

			// re-read the template ...
		$this->initialize_editor($this->pObj->id,$uid);
	}
	
	/**
	 * Prefix lines with $indent-string
	 */
	function prefixLines($str,$indent)	{
		$lines = explode(chr(10),$str);
		while(list($k,$v)=each($lines))	{
			$lines[$k]=$indent.$v;
		}
		return implode(chr(10),$lines);
	}

	/**
	 * Merging the inline-content into the TypoScript setup code (with the markers...)
	 */
	function mergeData($currentData,$addData)	{
		$lines = explode(chr(10),$currentData);
		$newContent="";
		
		$inFlag=0;
		$contentInserted=0;
		reset($lines);
		while(list($k,$v)=each($lines))	{
			if (!$inFlag)	{
				$newContent.=$v.chr(10);
			}
			
			if (!strcmp(substr(trim($v),0,strlen($this->ext_cssMarker)),$this->ext_cssMarker))	{
				if (!$inFlag)	{
#					debug("IN: ".$k);
					$inFlag=1;
				} else {
#					debug("OUT: ".$k);
					$newContent.=$addData.chr(10);
					$contentInserted=1;
					$newContent.=$v.chr(10);
					$inFlag=0;
				}
			}
		}
		
		if (!$contentInserted)	{
			$newContent = $currentData.chr(10).
				$this->ext_cssMarker." - begin. CONTENT BETWEEN THESE MARKERS ARE AUTOMATICALLY UPDATED. ###".chr(10).
				$addData.chr(10).
				$this->ext_cssMarker." - end ###".chr(10);
		}
		return $newContent;
	}

	/**
	 *	Prints an array of CSS attributes very nicely
	 */
	function printAttributes($arr,$forceOneLine=0)	{
		$items=array();
		reset($arr);
		while(list($k,$v)=each($arr))	{
			$items[]=$k.":".$v.";";
		}
		
		if ($this->ext_oneLineMode || $forceOneLine)	{
			$out = " ".implode(" ",$items)." ";
		} else {
			$out = chr(10).chr(9).implode(chr(10).chr(9),$items).chr(10);
		}
		
		return $out;
	}

	/**
	 * Based on the parsed template (setup and editorcfg fields) the available style-sheet collections are returned in an array listing all resource-filenames, information etc from the configuration.
	 */
	function makeStyleCollectionSelector()	{
		global $tmpl;

		$outArray=array();
		reset($tmpl->setup);
		while(list($k)=each($tmpl->setup))	{
			if (is_array($tmpl->setup[$k]) && $tmpl->setup[substr($k,0,-1)]=="PAGE")	{
				$tArray=array();
					// Stylesheet:
				if ($tmpl->setup[$k]["stylesheet"])	{
					$tArray[]=array(
						"title"=>"stylesheet",
						"file_id"=>$tmpl->setup[$k]["stylesheet"]
					);
				}
					// Include CSS:
				if (is_array($tmpl->setup[$k]["includeCSS."]))	{
					reset($tmpl->setup[$k]["includeCSS."]);
					while(list($k2,$iCSSfile)=each($tmpl->setup[$k]["includeCSS."]))	{
						if (!is_array($iCSSfile))	{
							$tArray[]=array(
								"title"=>"includeCSS.".$k2,
								"file_id"=>$iCSSfile
							);
						}
					}
				}
				
					// Inline:
				if ($tmpl->setup[$k]["CSS_inlineStyle"])	{
					$tArray[]=array(
						"title"=>"CSS_inlineStyle",
						"file_id"=>"INLINE",
						"pageObject" => $k
					);
				}

				if (count($tArray))	{
					$outArray[] = array(
						"title"=>"PAGE Obj: '".substr($k,0,-1)."'",
						"parts"=>$tArray,
						"CSS_editor" => $tmpl->setup_editorcfg[$k]["CSS_editor"],
						"CSS_editor." => $tmpl->setup_editorcfg[$k]["CSS_editor."]
					);
				}
			}
		}

		reset($tmpl->setup_editorcfg);
		while(list($k)=each($tmpl->setup_editorcfg))	{
			if (is_array($tmpl->setup_editorcfg[$k]) && is_array($tmpl->setup_editorcfg[$k]["CSS_docs."]))	{
				reset($tmpl->setup_editorcfg[$k]["CSS_docs."]);
				while(list($k2,$v2)=each($tmpl->setup_editorcfg[$k]["CSS_docs."]))	{
					if (is_array($v2) && $tmpl->setup_editorcfg[$k]["CSS_docs."][substr($k2,0,-1)]=="EXTERNAL")	{
						$tArray=array();
						if (is_array($v2["docs."]))	{
							reset($v2["docs."]);
							while(list($k3,$v3)=each($v2["docs."]))	{
								if (!is_array($v3))	{
									$tArray[]=array(
										"title"=>$k."CSS_docs.".$k2."docs.".$k3,
										"file_id"=>$v3,
										"conf" => $v2["docs."][$k3."."]
									);
								}
							}
						}
						
						$ID = "BEC: ".$k."CSS_docs.".$k2;
						$outArray[] = array("title"=>$v2["title"]?$v2["title"]:$ID,
							"ID"=>$ID,
							"parts"=>$tArray,
							"CSS_editor" => $v2["CSS_editor"],
							"CSS_editor." => $v2["CSS_editor."]
						);
					}
				}
			}
		}
#debug($outArray);
		return $outArray;
	}

	/**
	 * Takes a raw style-collection array as input, resolves all resource filenames, reads their content.
	 * Does not parse the stylesheets, just reads the content.
	 */
	function readStylesheets($collection)	{
		global $tmpl;
		if (is_array($collection["parts"]))	{
			reset($collection["parts"]);
			while(list($k,$v)=each($collection["parts"]))	{
				if (strcmp($v["file_id"],"INLINE"))	{
					$fileRef = $this->getFileName($v["file_id"]);
					if ($fileRef)	{
						$fI=pathinfo($fileRef);
						$ext=strtolower($fI["extension"]);
						if (t3lib_div::inList("html,htm",$ext))	{
							$v["content"] = $this->parseHTMLdocument(PATH_site.$fileRef);
						} elseif (t3lib_div::inList("css",$ext))	{
							$v["content"] = t3lib_div::getUrl(PATH_site.$fileRef);
							$v["content_md5"]=md5($v["content"]);
						} else {
							$v["ERROR"]="Not valid fileextension (must be html, htm, css)";
						}
					} else {
						$v["ERROR"]="No file found";
					}
				} elseif ($v["pageObject"]) {
					$v["content"] = $tmpl->setup[$v["pageObject"]]["CSS_inlineStyle"];
				} else {
debug("ERROR...");
				}
				$collection["parts"][$k]=$v;
			}
		}
		return $collection;
	}
	
	/**
	 * Reads <STYLE> section + <LINK>-stylesheets of an HTML-document
	 */
	function parseHTMLdocument($document)	{
		$content = t3lib_div::getUrl($document);
		$contentArr=array();
		
		$parser = t3lib_div::makeInstance("t3lib_parsehtml");
		$headerSection = $parser->getAllParts($parser->splitIntoBlock("head",$content),1,0);
		if (count($headerSection))	{
			$internalSections = $parser->splitIntoBlock("style",$headerSection[0]);

			reset($internalSections);
			while(list($k,$v)=each($internalSections))	{
				if ($k%2)	{
					$contentArr[$k]["file_id"]="INLINE";
					$contentArr[$k]["content"]=$parser->removeFirstAndLastTag($v);
					$contentArr[$k]["content_md5"]=md5($contentArr[$k]["content"]);
				} else {
					$nonStyleParts=$parser->getAllParts($parser->splitTags("link",$v));
					reset($nonStyleParts);
					while(list($k2,$v2)=each($nonStyleParts))	{
						list($attrib) = $parser->get_tag_attributes($v2,1);
						$pI = parse_url($attrib["href"]);
						if (!$pI["scheme"] && $pI["path"])	{
							$file = dirname($document)."/".$pI["path"];
							if (@is_file($file))	{
								$contentArr[$k]["content"][$k2]["file_id"]=substr($file,strlen(PATH_site));
								$contentArr[$k]["content"][$k2]["content"]=t3lib_div::getUrl($file);
								$contentArr[$k]["content"][$k2]["content_md5"]=md5($contentArr[$k]["content"][$k2]["content"]);
							}
						}
					}
				}
			}
		}
		return $contentArr;
	}

	/**
	 * Prints information about a style collection
	 */
	function printCollectionInfo($colParts,$lines=array(),$pre="")	{
		$showContent = $this->pObj->MOD_SETTINGS["tx_tstemplatestyler_modfunc1_showContent"];
		if (is_array($colParts))	{
			reset($colParts);
			while(list($k,$v)=each($colParts))	{
				if ($v["file_id"])	{
					$lines[]='<tr class="bgColor4">
							<td nowrap valign=top>'.$pre.$v["file_id"].'</td>
							<td nowrap valign=top>'.
								($v["ERROR"]?"<strong>".$GLOBALS["TBE_TEMPLATE"]->rfw("ERROR: ".$v["ERROR"])."</strong>":"").
								(!is_array($v["content"])&&htmlspecialchars($showContent)?nl2br(str_replace(" ","&nbsp;",str_replace(chr(9),"&nbsp;&nbsp;&nbsp;",$v["content"]))):"&nbsp;").'</td>
						</tr>';
				}
				if (is_array($v["content"]))	{
					$lines = $this->printCollectionInfo($v["content"],$lines,$pre.($v["file_id"]?"&nbsp; &nbsp; &nbsp; ":""));
				}
			}
		}
#debug($colParts);

		return $lines;
	}
	
	/**
	 * Returns the resource relative to PATH_site
	 */
	function getFileName($resource)	{
		global $tmpl;

		$tmpl->getFileName_backPath=PATH_site;		// Setting absolute prefixed path for relative resources.

		$fileRef = $tmpl->getFileName($resource);
		if ($tmpl->removeFromGetFilePath && t3lib_div::isFirstPartOfStr($fileRef,$tmpl->removeFromGetFilePath))	{
			$fileRef = substr($fileRef,strlen($tmpl->removeFromGetFilePath));
		}
		return $fileRef;
	}
	
	
	/**
	 * This returns the CSS codes which are set by default from plugins.
	 */
	function ext_plusStyle()	{
		global $tmpl;

		if (is_array($tmpl->setup["plugin."])) {
			$temp_styleLines=array();
			reset($tmpl->setup["plugin."]);
			while(list($k2,$iCSScode)=each($tmpl->setup["plugin."]))	{
				if (is_array($iCSScode) && $iCSScode["_CSS_DEFAULT_STYLE"])	{
					$temp_styleLines[]='/* default styles for extension "'.substr($k2,0,-1).'" */'.chr(10).$iCSScode["_CSS_DEFAULT_STYLE"];
				}
			}
			if (count($temp_styleLines))	{
				$plusStyle=implode(chr(10),$temp_styleLines).chr(10).chr(10).chr(10).chr(10);
			}
		}
		return $plusStyle;
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tstemplate_styler/modfunc1/class.tx_tstemplatestyler_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tstemplate_styler/modfunc1/class.tx_tstemplatestyler_modfunc1.php"]);
}

?>
