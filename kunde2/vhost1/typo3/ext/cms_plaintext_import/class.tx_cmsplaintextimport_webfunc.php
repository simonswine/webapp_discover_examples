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

require_once(PATH_t3lib."class.t3lib_extobjbase.php");

class tx_cmsplaintextimport_webfunc extends t3lib_extobjbase {
	function modMenu()	{
		return array (
			"import_function" => array(
				0 => "[ SELECT TYPE ]",
				"txt" => "Import Plain Text",
			),
			"import_txt_format" => array(
				0 => "Plain",
				1 => htmlspecialchars("---new_page---/---new_section---")
			),
			"import_source" => array(
				"direct" => "Direct Input"
			),
			"import_txt_trimWhiteSpace" => "",
		);	
	}
	function main() {
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$extension = $this->pObj->MOD_SETTINGS["import_function"];
		$sourceData = t3lib_div::_GP("directInput");
		if (strcmp(trim($sourceData),""))	{
			$theData = Array();	
			switch($this->pObj->MOD_SETTINGS["import_txt_format"])	{
				case 0:
					if ($this->pObj->MOD_SETTINGS["import_txt_trimWhiteSpace"])	{$sourceData = trim($sourceData);}
					$pageKey=$this->pObj->id;
					$theData[$pageKey][uniqid("NEW")]["bodytext"]=$sourceData;
				break;
				case 1:
					$pages = split(sql_regcase("---new_page---"), $sourceData);
					
					while(list($theKey,$theVal)=each($pages))	{
						if (trim($theVal))	{
							if ($theKey)	{	// If $theKey, then it's a new page. Otherwise it's the current page
								$pageKey = uniqid("NEW");
							} else {
								$pageKey=$this->pObj->id;
							}
							$sections = split(sql_regcase("---new_section---"), $theVal);
		//					debug($sections);
							while(list($c,$theContent)=each($sections))	{
								$secId = uniqid("NEW");
								$theContent=$this->cleanEnds($theContent);
								if ($this->pObj->MOD_SETTINGS["import_txt_trimWhiteSpace"])	{$theContent = trim($theContent);}
								if ($theKey && !$c)	{	// A new page-header is created.
									$parts = split(chr(10),$theContent,3);
									$theData[$pageKey]["title"]=trim($parts[0]) ? trim($parts[0]) : "[NO TITLE]";
									$theData[$pageKey]["subtitle"]=trim($parts[1]);
										// If there are more than the two lines, then a new section is created by setting the content-var again
									if (trim($parts[2]))	{
										$theContent=  $this->pObj->MOD_SETTINGS["import_txt_trimWhiteSpace"] ? trim($parts[2]) : $parts[2];
										$theContent= chr(10).$theContent;	// The header is set to "" by adding a single linebreak...
									} else {
										$theContent="";
									}
								}
								if (trim($theContent)) {
									$parts = split(chr(10),$theContent,2);
										// If the first line is longer than (80) chars, then it's regarded a part of the content.
									if (strlen($parts[0])>80)	{
										$parts[1]=$parts[0].chr(10).$parts[1];
										$parts[0]="";
									}
											// Prepends "http://" and "mailto:" to all webadresses starting with www and all email-adresses.
									$parts[1]=" ".$parts[1];	// Add space in the beginning (because of the regexes...
									$parts[1]=ereg_replace("([ ".chr(10).chr(13)."])(".sql_regcase("www")."\.)","\\1http://\\2",$parts[1]);
									$parts[1]=ereg_replace("([ ".chr(10).chr(13)."])([^ ]*@)","\\1mailto:\\2",$parts[1]);
									$parts[1]=substr($parts[1],1);	// Strip the space in the beginning.
											// If a paragraph starts with "-" then it's regarded as a quote and set in italics
									$lines = explode(chr(10),$parts[1]);
									while(list($a,$b)=each($lines))	{
										if (substr(trim($b),0,1)=="-")	{
											$lines[$a]='<i>'.$lines[$a].'</i>';
										}
									}
									$parts[1]=implode($lines,chr(10));
											// putting the content
									$theData[$pageKey][$secId]["header"]=$parts[0];
									$theData[$pageKey][$secId]["bodytext"]=$parts[1];
								}
							}
						}
					}
				break;
			}
		
			$output = "";
			reset($theData);
			while(list($key,$val)=each($theData))	{
				$output.="<HR><b>PAGE: ".$key."</b><BR>";
				if (!t3lib_div::testInt($key))	{
					$output.='<input type="hidden" name="data[pages]['.$key.'][pid]" value="'.$this->pObj->pageinfo["uid"].'">';
					$output.='<input type="hidden" name="data[pages]['.$key.'][hidden]" value="0">';
					$output.='Title:<BR><input type="text" size=50 name="data[pages]['.$key.'][title]" value="'.htmlspecialchars($val["title"]).'"><BR>';
					$output.='Subtitle:<BR><input type="text"'.$GLOBALS["TBE_TEMPLATE"]->formWidth().' name="data[pages]['.$key.'][subtitle]" value="'.htmlspecialchars($val["subtitle"]).'"><BR>';
				}
				while(list($theSectionKey,$theSectionVal)=each($val))	{
					if (strstr($theSectionKey,"NEW"))	{
						$output.='<input type="hidden" name="data[tt_content]['.$theSectionKey.'][pid]" value="'.$key.'">';
						$output.='Header:<BR><input type="text"'.$GLOBALS["TBE_TEMPLATE"]->formWidth().' name="data[tt_content]['.$theSectionKey.'][header]" value="'.htmlspecialchars($theSectionVal["header"]).'"><BR>';
						$output.='Bodytext:<BR><textarea'.$GLOBALS["TBE_TEMPLATE"]->formWidthText().' rows=6 name="data[tt_content]['.$theSectionKey.'][bodytext]">'.t3lib_div::formatForTextarea($theSectionVal["bodytext"]).'</textarea><BR>';
					}
				}
			}
		
			$output='</form><form action="'.$BACK_PATH.'tce_db.php" method="POST" name="editform">'.$output;
			$output.='<input type="Submit" name="submit" value="SAVE"><input type="hidden" name="flags[reverseOrder]" value="1">';
			$output.='<input type="Hidden" name="redirect" value="'.htmlspecialchars(t3lib_div::linkThisUrl(t3lib_div::getIndpEnv("REQUEST_URI"),array("id"=>$this->pObj->id))).'">';
			$output.='&nbsp;&nbsp;<input type="Submit" value="Abort" onClick="jumpToUrl(\'index.php?id='.$this->pObj->id.'\'); return false;">';
		
			$theOutput.=$this->pObj->doc->section("Edit:",$output);	
		} else {
			if (!$extension)	$extension="txt";
			switch($extension)	{
				case "txt":
					$Nmenu=array();
					$Nmenu[]= array(fw("Format:"), t3lib_BEfunc::getFuncMenu($this->pObj->id,"SET[import_txt_format]",$this->pObj->MOD_SETTINGS["import_txt_format"],$this->pObj->MOD_MENU["import_txt_format"]));
		
					$Nmenu[]= array(fw("Trim whitespace:"),t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[import_txt_trimWhiteSpace]",$this->pObj->MOD_SETTINGS["import_txt_trimWhiteSpace"]));
					
					$theOutput.=$this->pObj->doc->section("Select options:",$this->pObj->doc->menuTable($Nmenu));
					$theOutput.=$this->pObj->doc->divider(5);
				break;
				default:
					$extension = "";
				break;
			}
			if ($extension)	{
				$Nmenu= "Source: ".t3lib_BEfunc::getFuncMenu($this->pObj->id,"SET[import_source]",$this->pObj->MOD_SETTINGS["import_source"],$this->pObj->MOD_MENU["import_source"]);
				$theOutput.=$this->pObj->doc->section("Select file source:",'<NOBR>'.$Nmenu.'</NOBR><BR><BR>'.$this->printMenu($this->pObj->MOD_SETTINGS["import_source"],$extension));
			}
		}
		return $theOutput;
	}
	function cleanEnds($string)	{
		$parts = explode(chr(10),$string);
		if (!trim($parts[(count($parts)-1)]))	unset($parts[(count($parts)-1)]);
		if (!trim($parts[0]))	unset($parts[0]);
		return implode($parts,chr(10));
	}
	function printMenu($kind, $ext)	{
		$content = "";		// If set, then this function returns content and not a menu
		
		switch($kind)	{
			case "direct":
					// Direct Input
				$output.= '<table border=0 cellpadding=0 cellspacing=0 width=460>';
				$output.= '<tr><td class="bgColor5"><b>'.fw('Direct Input').'</b></td></tr>';
				$output.= '<tr><td><textarea name="directInput"'.$this->pObj->doc->formWidthText().' rows=20 ></textarea><BR><input type="Submit" name="submit" value="Send Direct Input"></td></tr>';
				$output.= '</table>';			
			break;
		}

		return $output;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cms_plaintext_import/class.tx_cmsplaintextimport_webfunc.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cms_plaintext_import/class.tx_cmsplaintextimport_webfunc.php"]);
}

?>