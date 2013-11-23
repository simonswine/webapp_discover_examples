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
 * RTE initialization
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * additions by: Martin van Es <m.vanes@drecomm.nl>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   78: class SC_rte_rte
 *   98:     function init()
 *  163:     function makeToolBarHide()
 *  215:     function getLabels()
 *  233:     function makeHeader()
 *  337:     function setButtons()
 *  354:     function JSout()
 *  472:     function main()
 *  497:     function printContent()
 *
 *              SECTION: OTHER FUNCTIONS:
 *  519:     function RTEtsConfigParams()
 *  531:     function detectUselessBar($hide,$all)
 *  564:     function cleanList($str)
 *  580:     function filterStyleEl($elValue,$matchList)
 *
 * TOTAL FUNCTIONS: 12
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');

require_once (PATH_t3lib.'class.t3lib_page.php');






/**
 * Script Class
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_rte
 */
class SC_rte_rte {
	var $content;

	var $elementId;
	var $elementParts;
	var $tscPID;
	var $typeVal;
	var $thePid;
	var $RTEsetup;
	var $thisConfig;
	var $hide;
	var $toggleHTML;
	var $confValues;
	var $siteUrl;
	var $language;
	var $specConf;

	/**
	 * @return	[type]		...
	 */
	function init()	{
		global $BE_USER,$LANG,$HTTP_GET_VARS,$TBE_TEMPLATE,$TCA;
#debug(t3lib_div::_GET());

			// Element ID + pid
		$this->elementId = t3lib_div::_GP('elementId');
		$this->elementParts = explode('][',ereg_replace('\]$','',ereg_replace('^(TSFE_EDIT\[data\]\[|data\[)','',$this->elementId)));

			// Find the page PIDs:
		list($this->tscPID,$this->thePid) = t3lib_BEfunc::getTSCpid(trim($this->elementParts[0]),trim($this->elementParts[1]),t3lib_div::_GP('pid'));

			// Record "types" field value:
		$this->typeVal = t3lib_div::_GP('typeVal');
		if (!isset($HTTP_GET_VARS['typeVal']))	{
			die ('System Error: No typeVal was sent!');
		}

			// Find "thisConfig" for record/editor:
		unset($this->RTEsetup);
		$this->thisConfig = array();
		if ($this->thePid >= 0)	{
			$this->RTEsetup = $BE_USER->getTSConfig('RTE',t3lib_BEfunc::getPagesTSconfig($this->tscPID));
			$this->thisConfig = t3lib_BEfunc::RTEsetup($this->RTEsetup['properties'],$this->elementParts[0],$this->elementParts[2],$this->typeVal);
		} else {
			die ('System Error: Could not fetch configuration based on the parameters sent to the script. ($this->thePid, $this->elementId)');		// This will prevent the RTE from being used with record who has a pid of zero, so maybe this is not so smart...?
		}

		if ($this->thisConfig['disabled'])	{
			die ('System Error: Apparently the RTE is disabled and this script should not have been loaded anyway.');
		}


			// Special configuration (line) and default extras:
		$en = t3lib_div::_GP('sC');
		$defaultExtras = t3lib_div::_GP('defaultExtras');
		$this->specConf = t3lib_BEfunc::getSpecConfParts($en,$defaultExtras);

			// Tool bar stuff:
		$this->makeToolBarHide();

			// bgColor
		$this->confValues['backgroundColor'] = t3lib_div::_GP('bgColor');
		if (!$this->confValues['backgroundColor'])	{
			$this->confValues['backgroundColor'] = $TBE_TEMPLATE->bgColor3;
		}
		$this->confValues['steelBlue'] = $TBE_TEMPLATE->bgColor2;

			// ***************************
			// Language
			// ***************************
		$this->language = $LANG->lang;
		if ($this->language=='default')	{
			$this->language='';
		}

		$this->siteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function makeToolBarHide()	{
		global $BE_USER;

			// ***************************
			// Find list of elements active
			// ***************************
				// All elements:
		$all = array('cut','copy','paste','bar1','formatblock','class','fontstyle','fontsize','textcolor','bar2','bold','italic','underline','bar3','left','center','right','bar4','orderedlist','unorderedlist','outdent','indent','bar5','link','table','bgcolor','image','bar6','emoticon','line','user');
		$bars = array('bar1','bar2','bar3','bar4','bar5','bar6');

			// These can never be displayed
		$this->hide=array();	// elements to hide
		//	$this->hide[]='emoticon';
		//	$this->hide[]='bgcolor';

			// specConf for field from backend
		$pList = is_array($this->specConf['richtext']['parameters']) ? implode(',',$this->specConf['richtext']['parameters']) : '';
		if ($pList!='*')	{	// If not all
			$show = array_merge($this->specConf['richtext']['parameters'],$bars);			// Merging listed and obligatory elements

			if ($this->thisConfig['showButtons'])	{
				$show = array_unique(array_merge($show,t3lib_div::trimExplode(',',$this->thisConfig['showButtons'],1)));
			}
			$this->hide = array_diff($all,$show);
		}

			// RTEkeyList for backend user
		$RTEkeyList = isset($BE_USER->userTS['options.']['RTEkeyList']) ? $BE_USER->userTS['options.']['RTEkeyList'] : '*';
		if ($RTEkeyList!='*')	{
			$show = t3lib_div::trimExplode(',',$RTEkeyList,1);
			$show = array_merge($show,$bars);			// Merging listed and obligatory elements
			$this->hide = array_diff($all,$show);
				// Toggle if 'Source code' button should be displayed.
			$this->toggleHTML = in_array('chMode',$show) ? 1 : 0;
		} else {$this->toggleHTML=1;}

			// Add removed fields from RTE config
		if ($this->thisConfig['hideButtons'])	{
			$this->hide = array_unique(array_merge($this->hide,t3lib_div::trimExplode(',',$this->thisConfig['hideButtons'],1)));
		}

			// Making sure that no illegal keys are in the array
		$this->hide=array_intersect($all,$this->hide);
			// Trying to find out if any bars useless
		$this->hide=$this->detectUselessBar($this->hide,$all);
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getLabels()	{
		global $LANG,$LOCAL_LANG;

		include (t3lib_extMgm::extPath('rte').'app/locallang.php');

		$labels='';
		foreach($LOCAL_LANG['default'] as $key => $label)	{
			$labels.= 'var '.$key.' = '.$LANG->JScharCode($LANG->getLL($key)).';'.chr(10);
		}

		return $labels;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function makeHeader()	{
		$this->content='';
		$this->content.='
<HTML>
<HEAD>
	<script language="javascript" type="text/javascript">

/* Labels: */
'.$this->getLabels().'

	</script>
	<script language="javascript" type="text/javascript" src="rte.js"></script>
	<STYLE>
		body {margin:0pt;border:none;padding:0pt}
		#tbDBSelect {display:none;text-align:left;width: 100;margin-right: 1pt;margin-bottom: 0pt;margin-top: 0pt;padding: 0pt}
		#DBSelect, #idMode, .userButton {font:8pt arial}
		#DBSelect {width:100}
		#idMode {margin-top:0pt}
		.tbButton {text-align:left;margin:0pt 1pt 0pt 0pt;padding:0pt}
		#EditBox {position: relative}
		select  {  font-family: Verdana, Arial, Helvetica; font-size: 10px }
		textarea  {  font-family: Verdana, Arial, Helvetica; font-size: 10px}
		input   {  font-family: Verdana, Arial, Helvetica; font-size: 10px }
	</STYLE>
	<STYLE ID=skin>
		#EditBox {margin: 0px 0px 0px 0px}
		#tbUpRight, #tbUpLeft {width:110px}
		#idMode {background: '.$this->confValues['backgroundColor'].';margin-left:0px;padding:0pt}
		#idMode LABEL {color: black; font:bold 10px verdana; text-decoration: none}
		#tbTopBar {height:19px}
		#tbButtons, #tbContents {background: '.$this->confValues['backgroundColor'].';vertical-align: top}
		#tbContents {padding:0px 0px}
		#tbBottomBar {height:0px}
	</STYLE>
	<STYLE ID=defPopupSkin>
		#popup BODY {margin:0px;border-top:none}
		#popup .colorTable TR {height:6px}
		#popup .colorTable TD {width:6px;cursor:hand}
		#popup .colorTable, .CLASSES {font: 10px verdana;}
		#popup #header {width:100%}
		#popup #close {cursor:default;font:bold 8pt system;width:16px;text-align: center}
		#popup #content {padding:10pt}
		#popup TABLE {vertical-align:top}
		#popup .tabBody {border:1px black solid;border-top: none}
		#popup .tabItem, #popup .tabSpace {border-bottom:1px black solid;border-left:1px black solid}
		#popup .tabItem {border-top:1px black solid;font:10pt arial,geneva,sans-serif;}
		#popup .currentColor {width:20px;height:20px; margin: 0pt;margin-right:15pt;border:1px black solid}
		#popup .tabItem DIV {margin:3px;padding:0px;cursor: hand}
		#popup .tabItem DIV.disabled {color: gray;cursor: default}
		#popup .selected {font-weight:bold}
		#popup .emoticon {cursor:hand}
		#popup .specialColors {color:black; font:10px verdana;}
';

		if (is_array($this->RTEsetup['properties']['classes.']))	{
			reset($this->RTEsetup['properties']['classes.']);
			while(list($className,$conf)=each($this->RTEsetup['properties']['classes.']))	{
				$this->content.='#popup .CLASSES'.substr($className,0,-1).' {'.$conf['value']."} \n";
			}
		}
		$this->content.='
	</STYLE>
	<STYLE ID=popupSkin>
		#popup BODY {border: 1px black solid; background: '.$this->confValues['backgroundColor'].'}
		#popup #header {background:'.$this->confValues['steelBlue'].'; color: black}
		#popup #caption {text-align: left;font: bold 10px verdana}
		#popup .ColorTable, #popup #idList #idListTR TD#current {border: 1px black solid}
		#popup #idList TD{cursor: hand;border: 0px #F1F1F1 solid; background:'.$this->confValues['backgroundColor'].';}
		#idListTR TR{cursor: hand;background:'.$this->confValues['backgroundColor'].';}
		#popup #close {border: 1px black solid;cursor:hand;color: black;font: bold 10px verdana; margin-right: 3px;padding:0px 4px 2px}
		#popup #tableProps .tablePropsTitle {color:#006699;text-align:left;margin:0pt;border-bottom: 1px black solid;margin-bottom:5pt}
		#tableButtons, #tableProps {padding:5px}
		#popup #tableContents {height:175px}
		#popup #tableProps .tablePropsTitle, #popup #tableProps, #popup #tableProps TABLE {font: 10px verdana;}
		#popup #tableOptions  {color: black; font:bold 10px verdana; padding:15pt 5pt; }
		#popup #puDivider {background:black;width:1px}
		#popup #content {margin: 0pt;padding:0pt 0pt 5pt 0pt}
		#popup #ColorPopup {width: 250px}
		#popup .block P,#popup .block H1,#popup .block H2,#popup .block H3,
		#popup .block H4, #popup .block H5,#popup .block H6,#popup .block PRE {margin:0pt;padding:0pt}
		#popup #customFont {font: 10px verdana;text-decoration:italic}
		#popup select  {  font-family: Verdana, Arial, Helvetica; font-size: 10px }
		#popup textarea  {  font-family: Verdana, Arial, Helvetica; font-size: 10px}
		#popup input   {  font-family: Verdana, Arial, Helvetica; font-size: 10px }
	</STYLE>
	<script language="javascript" type="text/javascript">
		var L_EMOTICONPATH_TEXT = "'.$this->siteUrl.'t3lib/gfx/emoticons/";
		var theEditor;
		window.onload = initEditor;
		var theBackGroundColor = "'.$this->confValues['backgroundColor'].'";
		';

		$this->content.='
		var BACK_PATH = "'.$GLOBALS['BACK_PATH'].'";
		function spitItOut()	{	//
	//		self.parent.TBE_EDITOR_setHiddenContent(getHTML(),"'.$this->elementId.'");
		}
		function spitItIn()	{	//
			self.parent.TBE_EDITOR_setRTEref(self,"'.$this->elementId.'",1);
//			setHTML(self.parent.'.t3lib_div::_GP('formName').'["'.$this->elementId.'"].value);
		}
		';

		$this->content.='
		function setButtons()	{
		';
		reset($this->hide);
		while(list(,$value)=each($this->hide))	{
			$this->content.='
			setToolbar("tb'.$value.'",0);';
		}
		$this->content.='
		}
		';
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function JSout()	{
		global $LANG;

		// **********************************************
		// Setting up the available classes for the RTE
		// **********************************************
		$JSout='';

		$JSout.="var classes_style = new Array(); \n";
		$JSout.="var classes_label = new Array(); \n";
		$JSout.="var classes_noShow = new Array(); \n";
		if (is_array($this->RTEsetup['properties']['classes.']))	{
			reset($this->RTEsetup['properties']['classes.']);
			$cc=0;
			while(list($className,$conf)=each($this->RTEsetup['properties']['classes.']))	{
				$className = substr($className,0,-1);
				$JSout.= 'classes_style['.$cc."]=' .".$className." {'+unescape('".t3lib_div::rawUrlEncodeJS($conf['value'])."')+'}'; \n";
				$JSout.= "classes_label['".$className."']=".$LANG->JScharCode($LANG->sL($conf['name']))."; \n";
				$JSout.= "classes_noShow['".$className."']=".($conf['noShow']?1:0)."; \n";
				$cc++;
			}
		}

		$JSout.="var colors_value = new Array(); \n";
		$JSout.="var colors_label = new Array(); \n";
		if (is_array($this->RTEsetup['properties']['colors.']))	{
			reset($this->RTEsetup['properties']['colors.']);
			while(list($cName,$conf)=each($this->RTEsetup['properties']['colors.']))	{
				$cName=substr($cName,0,-1);
				$JSout.= "colors_value['".$cName."']='".$conf['value']."'; \n";
				$JSout.= "colors_label['".$cName."']=".$LANG->JScharCode($LANG->sL($conf['name']))."; \n";
			}
		}

		$JSout.="var fonts_value = new Array(); \n";
		$JSout.="var fonts_label = new Array(); \n";
		if (is_array($this->RTEsetup['properties']['fonts.']))	{
			reset($this->RTEsetup['properties']['fonts.']);
			while(list($fontName,$conf)=each($this->RTEsetup['properties']['fonts.']))	{
				$fontName=substr($fontName,0,-1);
				$JSout.= "fonts_value['".$fontName."']='".$conf['value']."'; \n";
				$JSout.= "fonts_label['".$fontName."']=".$LANG->JScharCode($LANG->sL($conf['name']))."; \n";
			}
		}
		$mainStyle_font=($this->thisConfig['mainStyle_font']?$this->thisConfig['mainStyle_font']:'Verdana');

		$mainElements=array();
		$mainElements['P'] = 'margin-top:0px; margin-bottom:5px;'.$this->thisConfig['mainStyleOverride_add.']['P'];
		$elList=explode(',','H1,H2,H3,H4,H5,H6,PRE');
		while(list(,$elListName)=each($elList))	{
			if ($this->thisConfig['mainStyleOverride_add.'][$elListName])	$mainElements[$elListName]=$this->thisConfig['mainStyleOverride_add.'][$elListName];
		}

		$JSout.="var main_elements_style = new Array(); \n";
		$addElementCode='';
		reset($mainElements);
		while(list($elListName,$elValue)=each($mainElements))	{
			$addElementCode.=$elListName.' {'.$elValue."}\n";
			$JSout.="main_elements_style['".$elListName."']=unescape('".rawurlencode($this->filterStyleEl($elValue,'color,font*'))."');\n";
		}

		$styleCode = $this->thisConfig['mainStyleOverride'] ? $this->thisConfig['mainStyleOverride'] : '
		BODY {border: 1px black solid; border-top: none; margin : 2 2 2 2'.
			'; font-family:'.$mainStyle_font.
			'; font-size:'.($this->thisConfig['mainStyle_size']?$this->thisConfig['mainStyle_size']:'10px').
			'; color:'.($this->thisConfig['mainStyle_color']?$this->thisConfig['mainStyle_color']:'black').
			'; background-color:'.($this->thisConfig['mainStyle_bgcolor']?$this->thisConfig['mainStyle_bgcolor']:'white').
			';'.$this->thisConfig['mainStyleOverride_add.']['BODY'].'}
		TD {font-family:Verdana; font-size:10px;'.$this->thisConfig['mainStyleOverride_add.']['TD'].'}
		DIV {margin-top:0px; margin-bottom:5px;'.$this->thisConfig['mainStyleOverride_add.']['DIV'].'}
		PRE {margin-top:0px; margin-bottom:5px;'.$this->thisConfig['mainStyleOverride_add.']['PRE'].'}
		OL {margin: 5px 10px 5px 30px;'.$this->thisConfig['mainStyleOverride_add.']['OL'].'}
		UL {margin: 5px 10px 5px 30px;'.$this->thisConfig['mainStyleOverride_add.']['UL'].'}
		BLOCKQUOTE {margin-top:0px; margin-bottom:0px;'.$this->thisConfig['mainStyleOverride_add.']['BLOCKQUOTE'].'}
		'.$addElementCode;

		if (is_array($this->thisConfig['inlineStyle.']))	{
			$styleCode.=chr(10).implode(chr(10),$this->thisConfig['inlineStyle.']).chr(10);
		}

		$JSout.='var inlineStyle = unescape("'.t3lib_div::rawUrlEncodeJS($styleCode).'");'.chr(10);

		$JSout.="var conf_classesParagraph = '".$this->cleanList($this->thisConfig['classesParagraph'])."'; \n";
		$JSout.="var conf_classesCharacter = '".$this->cleanList($this->thisConfig['classesCharacter'])."'; \n";
		$JSout.="var conf_classesImage = '".$this->cleanList($this->thisConfig['classesImage'])."'; \n";
		$JSout.="var conf_classesTable = '".$this->cleanList($this->thisConfig['classesTable'])."'; \n";
		$JSout.="var conf_classesLinks = '".$this->cleanList($this->thisConfig['classesLinks'])."'; \n";
		$JSout.="var conf_classesTD = '".$this->cleanList($this->thisConfig['classesTD'])."'; \n";
		$JSout.="var conf_colors = '".$this->cleanList($this->thisConfig['colors'])."'; \n";
		$JSout.="var conf_fontFace = '".$this->cleanList($this->thisConfig['fontFace'])."'; \n";
		$JSout.="var conf_hidePStyleItems = '".$this->cleanList(strtoupper($this->thisConfig['hidePStyleItems']))."'; \n";
		$JSout.="var conf_hideFontFaces = '".$this->cleanList($this->thisConfig['hideFontFaces'])."'; \n";
		$JSout.="var conf_hideFontSizes = '".$this->cleanList($this->thisConfig['hideFontSizes'])."'; \n";
		$JSout.='var conf_disableColorPicker = '.($this->thisConfig['disableColorPicker']?1:0)."; \n";
		$JSout.="var conf_RTEtsConfigParams = '&RTEtsConfigParams=".rawurlencode($this->RTEtsConfigParams())."'; \n";
		$JSout.='var conf_enableRightClick = '.($this->thisConfig['disableRightClick']?0:1).";\n";
		$JSout.="var conf_fontSizeStyle = unescape('font-family:".rawurlencode($mainStyle_font)."');\n";
		$JSout.="var conf_NeutralStyle = 'font-family:Verdana,Arial; font-size:10; font-weight:normal; color:black;';\n";
		$JSout.='var conf_showExampleInPopups = '.($this->thisConfig['disablePCexamples']?0:1).";\n";


		/*
		$JSout.='alert(conf_classesParagraph);';
		$JSout.='alert(conf_classesCharacter);';
		$JSout.='alert(conf_colors);';
		$JSout.='alert(conf_fontFace);';
		$JSout.='alert(conf_hidePStyleItems);';
		$JSout.='alert(conf_hideFontFaces);';
		*/

		$this->content.=$JSout;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function main()	{
		$this->content.='
</script>
</head>
<body tabindex="-1" scroll="no" oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;" onscroll="return false;" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
	<div id="idEditor" style="visibility: hidden;">
		<table id="idToolbar" border="0" width="100%" cellspacing="0" cellpadding="0" onclick="edHidePopup();">
			<tr>
				<td id="tbContents"><script language="javascript" type="text/javascript">drawToolbar();</script></td>
			</tr>
			<tr><td bgcolor="black"><img src="clear.gif" width="1" height="1" alt="" /></td></tr>
		</table>
		<iframe src="" name="idPopup" style="height: 200px; left: 25px; margin-top: 8px; position: absolute; visibility: hidden; width: 200px; z-index: -1"></iframe>
		<iframe src="" id="EditBox" name="idEditbox" width="100%" height="100%" onfocus="edHidePopup();" onblur="spitItOut();"></iframe>
		<div id="tbmode"><script language="javascript" type="text/javascript">drawModeSelect('.$this->toggleHTML.');</script></div>
	</div>
</body>
</html>';
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function printContent()	{
		echo $this->content;
	}










	/***************************
	 *
	 * OTHER FUNCTIONS:
	 *
	 ***************************/

	/**
	 * @return	[type]		...
	 */
	function RTEtsConfigParams()	{
		$p = t3lib_BEfunc::getSpecConfParametersFromArray($this->specConf['rte_transform']['parameters']);
		return $this->elementParts[0].':'.$this->elementParts[1].':'.$this->elementParts[2].':'.$this->thePid.':'.$this->typeVal.':'.$this->tscPID.':'.$p['imgpath'];
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$hide: ...
	 * @param	[type]		$all: ...
	 * @return	[type]		...
	 */
	function detectUselessBar($hide,$all)	{
		$show = array_diff($all,$hide);
		$items=array();
			// Register all
		$c=0;
		reset($show);
		while(list(,$v)=each($show))	{
			$items[$c]['cur']=substr($v,0,3);
			$items[$c]['prev'] = $items[$c-1]['cur'];
			$items[$c-1]['next'] = $items[$c]['cur'];
			$c++;
		}
			// remove
		$c=0;
		$elAdded=0;
		reset($show);
		while(list(,$v)=each($show))	{
	//			t3lib_div::debug($items[$c]);
			if ($items[$c]['cur']!='bar')	$elAdded=1;
			if ($items[$c]['cur']=='bar' && ($items[$c]['next']=='bar' || $items[$c]['next']=='' || $items[$c]['prev']=='' || !$elAdded))	{
				$hide[]=$v;
			}
			$c++;
		}
		return $hide;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$str: ...
	 * @return	[type]		...
	 */
	function cleanList($str)	{
		if (strstr($str,'*'))	{
			$str = '*';
		} else {
			$str = implode(',',array_unique(t3lib_div::trimExplode(',',$str,1)));
		}
		return $str;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$elValue: ...
	 * @param	[type]		$matchList: ...
	 * @return	[type]		...
	 */
	function filterStyleEl($elValue,$matchList)	{
		$matchParts = t3lib_div::trimExplode(',',$matchList,1);
		$styleParts = explode(';',$elValue);
		$nStyle=array();
		while(list($k,$p)=each($styleParts))	{
			$pp = t3lib_div::trimExplode(':',$p);
			if ($pp[0]&&$pp[1])	{
				reset($matchParts);
				while(list(,$el)=each($matchParts))	{
					$star=substr($el,-1)=='*';
					if (!strcmp($pp[0],$el) || ($star && t3lib_div::isFirstPartOfStr($pp[0],substr($el,0,-1)) ))	{
						$nStyle[]=$pp[0].':'.$pp[1];
					} else 	unset($styleParts[$k]);
				}
			} else {
				unset($styleParts[$k]);
			}
		}
		return implode('; ',$nStyle);
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/app/rte.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/app/rte.php']);
}







// Make instance:
$SOBE = t3lib_div::makeInstance('SC_rte_rte');
$SOBE->init();
$SOBE->makeHeader();
$SOBE->JSout();
$SOBE->main();
$SOBE->printContent();
?>
