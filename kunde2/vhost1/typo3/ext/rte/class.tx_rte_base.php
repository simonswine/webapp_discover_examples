<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * RTE base class (Traditional RTE for MSIE 5+ on windows only!)
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_rte_base extends t3lib_rteapi 
 *   74:     function isAvailable()	
 *  104:     function drawRTE(&$pObj,$table,$field,$row,$PA,$specConf,$thisConfig,$RTEtypeVal,$RTErelPath,$thePidValue)	
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */





require_once(PATH_t3lib.'class.t3lib_rteapi.php');
/**
 * RTE base class (Traditional RTE for MSIE 5+ on windows only!)
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_rte
 */
class tx_rte_base extends t3lib_rteapi {

		// External:
	var $RTEdivStyle;				// Alternative style for RTE <div> tag.

		// Internal, static:
	var $ID = 'rte';				// Identifies the RTE as being the one from the "rte" extension if any external code needs to know...
	var $debugMode = FALSE;			// If set, the content goes into a regular TEXT area field - for developing testing of transformations. (Also any browser will load the field!)


	/**
	 * Returns true if the RTE is available. Here you check if the browser requirements are met.
	 * If there are reasons why the RTE cannot be displayed you simply enter them as text in ->errorLog
	 *
	 * @return	boolean		TRUE if this RTE object offers an RTE in the current browser environment
	 */
	function isAvailable()	{
		global $CLIENT;

		if (TYPO3_DLOG)	t3lib_div::devLog('Checking for availability...','rte');

		$this->errorLog = array();
		if (!$this->debugMode)	{	// If debug-mode, let any browser through
			if ($CLIENT['BROWSER']!='msie') 	$this->errorLog[] = '"rte": Browser is not MSIE';
			if ($CLIENT['SYSTEM']!='win') 		$this->errorLog[] = '"rte": Client system is not Windows';
			if ($CLIENT['VERSION']<5) 			$this->errorLog[] = '"rte": Browser version below 5';
		}
		if (!count($this->errorLog))	return TRUE;
	}

	/**
	 * Draws the RTE as an iframe for MSIE 5+
	 *
	 * @param	object		Reference to parent object, which is an instance of the TCEforms.
	 * @param	string		The table name
	 * @param	string		The field name
	 * @param	array		The current row from which field is being rendered
	 * @param	array		Array of standard content for rendering form fields from TCEforms. See TCEforms for details on this. Includes for instance the value and the form field name, java script actions and more.
	 * @param	array		"special" configuration - what is found at position 4 in the types configuration of a field from record, parsed into an array.
	 * @param	array		Configuration for RTEs; A mix between TSconfig and otherwise. Contains configuration for display, which buttons are enabled, additional transformation information etc.
	 * @param	string		Record "type" field value.
	 * @param	string		Relative path for images/links in RTE; this is used when the RTE edits content from static files where the path of such media has to be transformed forth and back!
	 * @param	integer		PID value of record (true parent page id)
	 * @return	string		HTML code for RTE!
	 */
	function drawRTE(&$pObj,$table,$field,$row,$PA,$specConf,$thisConfig,$RTEtypeVal,$RTErelPath,$thePidValue)	{

			// Draw form element:
		if ($this->debugMode)	{	// Draws regular text area (debug mode)
			$item = parent::drawRTE($pObj,$table,$field,$row,$PA,$specConf,$thisConfig,$RTEtypeVal,$RTErelPath,$thePidValue);
		} else {	// Draw real RTE (MSIE 5+ only)

				// Adding needed code in top:
			$pObj->additionalJS_pre['rte_loader_function'] = $this->loaderFunc($pObj->formName);
			$pObj->additionalJS_submit[] = "
							if(TBE_RTE_WINDOWS['".$PA['itemFormElName']."'])	{ document.".$pObj->formName."['".$PA['itemFormElName']."'].value = TBE_RTE_WINDOWS['".$PA['itemFormElName']."'].getHTML(); } else { OK=0; }";

				// Setting style:
			$RTEWidth = 460+($pObj->docLarge ? 150 : 0);
			$RTEdivStyle = $this->RTEdivStyle ? $this->RTEdivStyle : 'position:relative; left:0px; top:0px; height:380px; width:'.$RTEWidth.'px; border:solid 0px;';

				// RTE url:
			$rteURL = $pObj->backPath.'ext/rte/app/rte.php?'.
							'elementId='.rawurlencode($PA['itemFormElName']).	// Form element name
							'&pid='.$thePidValue.								// PID for record being edited.
							'&typeVal='.rawurlencode($RTEtypeVal).				// TCA "types" value for record
							'&bgColor='.rawurlencode($pObj->colorScheme[0]).	// Background color
							'&sC='.rawurlencode($PA['extra']).					// Extra options; This is index 3 (part #4) of the TCA "types" configuration of the field. Can be parsed by
							'&defaultExtras='.rawurlencode($PA['fieldConf']['defaultExtras']).	// Default Extra options
							'&formName='.rawurlencode($pObj->formName);			// Form name

				// Transform value:
			$value = $this->transformContent('rte',$PA['itemFormElValue'],$table,$field,$row,$specConf,$thisConfig,$RTErelPath,$thePidValue);

				// Register RTE windows:
			$pObj->RTEwindows[] = $PA['itemFormElName'];
			$item = '
				'.$this->triggerField($PA['itemFormElName']).'
				<input type="hidden" name="'.htmlspecialchars($PA['itemFormElName']).'" value="'.htmlspecialchars($value).'" />
				<div id="cdiv'.count($pObj->RTEwindows).'" style="'.htmlspecialchars($RTEdivStyle).'">
				<iframe
					src="'.htmlspecialchars($rteURL).'"
					id="'.$PA['itemFormElName'].'_RTE"
					style="visibility:visible; position:absolute; left:0px; top:0px; height:100%; width:100%;"></iframe>
				</div>';
		}

			// Return form item:
		return $item;
	}

	/**
	 * Return the function for loading the RTEs
	 *
	 * @param	string		Formname
	 * @return string		Loader function
	 */
	function loaderFunc($formname)	{
		return '
				var TBE_RTE_WINDOWS = new Array();

				function TBE_EDITOR_setRTEref(RTEobj,theField,loadContent)	{	//
					TBE_RTE_WINDOWS[theField] = RTEobj;
					if (loadContent)	{
						RTEobj.setHTML(document.'.$formname.'[theField].value);
					}
				}
		';
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/class.tx_rte_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rte/class.tx_rte_base.php']);
}
?>
