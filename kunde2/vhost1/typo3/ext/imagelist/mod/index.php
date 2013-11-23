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
 * Module: Image listing
 *
 * Lists images in a catalog on the server
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

 
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:imagelist/mod/locallang.php');
require_once (PATH_t3lib.'class.t3lib_stdgraphic.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_recordlist.php');
require_once ($BACK_PATH.'class.file_list.inc');

$BE_USER->modAccess($MCONF,1);





// ***************************
// Script Classes
// ***************************
class fileList_ext extends fileList	{
	function generateList()	{
		$this->files = $this->readDirectory($this->path,'file',$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
		$this->totalItems=count($this->files[sorting]);
		$this->HTMLcode.='<BR>'.$this->getImageList($this->files);
	}
	function addElement($h,$icon,$data,$tdParams="",$lMargin="",$altLine='')	{		
		return '<table border=0 cellpadding=0 cellspacing=0><tr><td>'.$altLine.'</td><td>'.$data[$this->fieldArray[0]].'</td></tr></table>';
	}
	function writeBottom()	{
	}
	function getImageList($files)	{
		$out='';
		if (count($files[files]))	{
			reset($files[files]);
			while (list(,$imgArr) = each($files[files]))	{
				list($flag,$code) = $this->fwd_rwd_nav();
				$out.=$code;
				if ($flag)	{
							// Initialization
					$out.=$this->renderImage($imgArr);
					$this->counter++;
				}
				$this->eCounter++;
			}
		}
		return $out;
	}
	function renderImage($imgArr)	{	
		global $LANG;
		$fI = $imgArr;
		$file = $imgArr['path'].$imgArr[file];
		$imgInfo='';

		$imgObj = t3lib_div::makeInstance('t3lib_stdGraphic');
		$imgObj->init();
		$imgObj->mayScaleUp=0;
		$imgObj->tempPath=PATH_site.$imgObj->tempPath;
		$imgInfo = $imgObj->getImageDimensions($file);
		$ext = $fI['fileext'];

		if ($imgInfo)	{
			$imgInfo_scaled = $imgObj->imageMagickConvert($file,'web',"346",'200m',"",'',"",1);
			$imgInfo_scaled[3] = $this->backPath.'../'.substr($imgInfo_scaled[3],strlen(PATH_site));
			$imageHTML = $imgObj->imgTag($imgInfo_scaled);

			$clipBoard = '';
			$infotext='';
			$infotext.='<b>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:show_item.php.filesize').':</b><BR>'.t3lib_div::formatSize(@filesize($file)).'<BR><BR>';
			$infotext.='<b>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:show_item.php.dimensions').':</b><BR>'.$imgInfo[0].'x'.$imgInfo[1].' pixels<br><br>';
			$infotext.=$clipBoard;
			
			
			$content.='<table border=0 cellpadding=1 cellspacing=0 width=400>
				<tr>
					<td><img src="clear.gif" width=346 height=1></td>
					<td><img src="clear.gif" width=5 height=1></td>
					<td><img src="clear.gif" width=100 height=1></td>
				</tr>
				<tr><td colspan=3 class="bgColor5"><b>'.fw($fI['file']).'</b></td></tr>
				<tr><td valign=top>'.$imageHTML.'</td><td></td><td valign=top class="bgColor4">'.fw($infotext).'</td></tr>
			</table><BR>';
		}
		return $content;
	}
}
class SC_mod_file_images_index {
	var $MCONF=array();
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();

	var $content;
	var $basicFF;
	var $pointer;
	var $imagemode;
	var $table;
	var $id;
	var $doc;	
	
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$this->MCONF = $GLOBALS['MCONF'];
		$this->id = t3lib_div::_GP('id');

		$this->pointer = t3lib_div::_GP('pointer');
		$this->imagemode = t3lib_div::_GP('imagemode');
		$this->table = t3lib_div::_GP('table');
		
		$this->basicFF = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicFF->init($GLOBALS['FILEMOUNTS'],$TYPO3_CONF_VARS['BE']['fileExtensions']);
	}
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		// **************************
		// Initializing
		// **************************
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		
		$this->id = $this->basicFF->is_directory($this->id);
		$access = $this->id && $this->basicFF->checkPathAgainstMounts($this->id.'/');
		
		$filelist = t3lib_div::makeInstance('fileList_ext');
		$filelist->backPath = $BACK_PATH;
		$filelist->thumbs = $this->imagemode;
		$filelist->script = 'index.php';
		$filelist->clickMenus = 0;
		$filelist->iLimit = 5;
		
		
		// **************************
		// Main
		// **************************
		if ($access)	{
			$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
			$filelist->start($this->id,$this->pointer,'','');
			$filelist->writeTop($this->id);
			$filelist->generateList($this->id,$this->table);
			$filelist->writeBottom();
		
				// JavaScript
			$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
				if (top.fsMod) top.fsMod.recentIds["file"] = unescape("'.rawurlencode($this->id).'");
			');
		
				// Draw the header.
			$this->doc->form = '<form action="" method="post">';
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->section('',$filelist->HTMLcode);
		
		
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('pointer,id,target,table','',$this->MCONF['name']));
			}
			$this->content.=$this->doc->endPage();
		} else {
			$this->content='';
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->endPage();
		}
	}
	function printContent()	{
		echo $this->content;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagelist/mod/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagelist/mod/index.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_mod_file_images_index');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>