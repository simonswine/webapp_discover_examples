<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2001-2010 Christian Jul Jensen (christian@typo3.com)
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
 * Class for generating front end for building queries
 *
 * $Id: class.t3lib_querygenerator.php 7905 2010-06-13 14:42:33Z ohader $
 *
 * @author	Christian Jul Jensen <christian@typo3.com>
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @coauthor	Jo Hasenau <info@cybercraft.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   98: class t3lib_queryGenerator
 *  245:     function makeFieldList()
 *  273:     function init($name,$table,$fieldList='')
 *  410:     function setAndCleanUpExternalLists($name,$list,$force='')
 *  426:     function procesData($qC='')
 *  529:     function cleanUpQueryConfig($queryConfig)
 *  586:     function getFormElements($subLevel=0,$queryConfig='',$parent='')
 *  744:     function makeOptionList($fN, $conf, $table)
 *  953:     function printCodeArray($codeArr,$l=0)
 *  976:     function formatQ($str)
 *  989:     function mkOperatorSelect($name,$op,$draw,$submit)
 * 1011:     function mkTypeSelect($name,$fieldName,$prepend='FIELD_')
 * 1032:     function verifyType($fieldName)
 * 1049:     function verifyComparison($comparison,$neg)
 * 1068:     function mkFieldToInputSelect($name,$fieldName)
 * 1091:     function mkTableSelect($name,$cur)
 * 1113:     function mkCompSelect($name,$comparison,$neg)
 * 1131:     function getSubscript($arr)
 * 1146:     function initUserDef()
 * 1155:     function userDef()
 * 1164:     function userDefCleanUp($queryConfig)
 * 1175:     function getQuery ($queryConfig,$pad='')
 * 1205:     function getQuerySingle($conf,$first)
 * 1245:     function cleanInputVal($conf,$suffix='')
 * 1270:     function getUserDefQuery ($qcArr)
 * 1278:     function updateIcon()
 * 1287:     function getLabelCol()
 * 1299:     function makeSelectorTable($modSettings,$enableList='table,fields,query,group,order,limit')
 * 1431:     function getTreeList($id, $depth, $begin=0, $perms_clause)
 * 1465:     function getSelectQuery($qString = '', $fN = '')
 * 1504:     function JSbottom($formname='forms[0]')
 * 1510:     function typo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue)
 * 1528:     function typo3FormFieldGet(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off)
 *
 * TOTAL FUNCTIONS: 32
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */











/**
 * Class for generating front end for building queries
 *
 * @author	Christian Jul Jensen <christian@typo3.com>
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_queryGenerator	{
	var $lang = array(
		'OR' => 'or',
		'AND' => 'and',
		'comparison' => array(
				 // Type = text	offset = 0
			'0_' => 'contains',
			'1_' => 'does not contain',
			'2_' => 'starts with',
			'3_' => 'does not start with',
			'4_' => 'ends with',
			'5_' => 'does not end with',
			'6_' => 'equals',
			'7_' => 'does not equal',
				 // Type = number , offset = 32
			'32_' => 'equals',
			'33_' => 'does not equal',
			'34_' => 'is greater than',
			'35_' => 'is less than',
			'36_' => 'is between',
			'37_' => 'is not between',
			'38_' => 'is in list',
			'39_' => 'is not in list',
			'40_' => 'binary AND equals',
			'41_' => 'binary AND does not equal',
			'42_' => 'binary OR equals',
			'43_' => 'binary OR does not equal',
				// Type = multiple, relation, files , offset = 64
			'64_' => 'equals',
			'65_' => 'does not equal',
			'66_' => 'contains',
			'67_' => 'does not contain',
			'68_' => 'is in list',
			'69_' => 'is not in list',
			'70_' => 'binary AND equals',
			'71_' => 'binary AND does not equal',
			'72_' => 'binary OR equals',
			'73_' => 'binary OR does not equal',
				// Type = date,time  offset = 96
			'96_' => 'equals',
			'97_' => 'does not equal',
			'98_' => 'is greater than',
			'99_' => 'is less than',
			'100_' => 'is between',
			'101_' => 'is not between',
			'102_' => 'binary AND equals',
			'103_' => 'binary AND does not equal',
			'104_' => 'binary OR equals',
			'105_' => 'binary OR does not equal',
				// Type = boolean,  offset = 128
			'128_' => 'is True',
			'129_' => 'is False',
				// Type = binary , offset = 160
			'160_' => 'equals',
			'161_' => 'does not equal',
			'162_' => 'contains',
			'163_' => 'does not contain'
		)
	);

	var $compSQL = array(
			// Type = text	offset = 0
		'0' => "#FIELD# LIKE '%#VALUE#%'",
		'1' => "#FIELD# NOT LIKE '%#VALUE#%'",
		'2' => "#FIELD# LIKE '#VALUE#%'",
		'3' => "#FIELD# NOT LIKE '#VALUE#%'",
		'4' => "#FIELD# LIKE '%#VALUE#'",
		'5' => "#FIELD# NOT LIKE '%#VALUE#'",
		'6' => "#FIELD# = '#VALUE#'",
		'7' => "#FIELD# != '#VALUE#'",
			// Type = number, offset = 32
		'32' => "#FIELD# = '#VALUE#'",
		'33' => "#FIELD# != '#VALUE#'",
		'34' => '#FIELD# > #VALUE#',
		'35' => '#FIELD# < #VALUE#',
		'36' => '#FIELD# >= #VALUE# AND #FIELD# <= #VALUE1#',
		'37' => 'NOT (#FIELD# >= #VALUE# AND #FIELD# <= #VALUE1#)',
		'38' => '#FIELD# IN (#VALUE#)',
		'39' => '#FIELD# NOT IN (#VALUE#)',
		'40' => '(#FIELD# & #VALUE#)=#VALUE#',
		'41' => '(#FIELD# & #VALUE#)!=#VALUE#',
		'42' => '(#FIELD# | #VALUE#)=#VALUE#',
		'43' => '(#FIELD# | #VALUE#)!=#VALUE#',
			// Type = multiple, relation, files , offset = 64
		'64' => "#FIELD# = '#VALUE#'",
		'65' => "#FIELD# != '#VALUE#'",
		'66' => "#FIELD# LIKE '%#VALUE#%' AND #FIELD# LIKE '%#VALUE1#%'",
		'67' => "(#FIELD# NOT LIKE '%#VALUE#%' OR #FIELD# NOT LIKE '%#VALUE1#%')",
		'68' => '#FIELD# IN (#VALUE#)',
		'69' => '#FIELD# NOT IN (#VALUE#)',
		'70' => '(#FIELD# & #VALUE#)=#VALUE#',
		'71' => '(#FIELD# & #VALUE#)!=#VALUE#',
		'72' => '(#FIELD# | #VALUE#)=#VALUE#',
		'73' => '(#FIELD# | #VALUE#)!=#VALUE#',
			// Type = date, offset = 32
		'96' => "#FIELD# = '#VALUE#'",
		'97' => "#FIELD# != '#VALUE#'",
		'98' => '#FIELD# > #VALUE#',
		'99' => '#FIELD# < #VALUE#',
		'100' => '#FIELD# >= #VALUE# AND #FIELD# <= #VALUE1#',
		'101' => 'NOT (#FIELD# >= #VALUE# AND #FIELD# <= #VALUE1#)',
		'102' => '(#FIELD# & #VALUE#)=#VALUE#',
		'103' => '(#FIELD# & #VALUE#)!=#VALUE#',
		'104' => '(#FIELD# | #VALUE#)=#VALUE#',
		'105' => '(#FIELD# | #VALUE#)!=#VALUE#',
			// Type = boolean, offset = 128
		'128' => "#FIELD# = '1'",
		'129' => "#FIELD# != '1'",
			// Type = binary = 160
		'160' => "#FIELD# = '#VALUE#'",
		'161' => "#FIELD# != '#VALUE#'",
		'162' => '(#FIELD# & #VALUE#)=#VALUE#',
		'163' => '(#FIELD# & #VALUE#)=0'
	);

	var $comp_offsets = array(
		'text' => 0,
		'number' => 1,
		'multiple' => 2,
		'relation' => 2,
		'files' => 2,
		'date' => 3,
		'time' => 3,
		'boolean' => 4,
		'binary' => 5
	);
	var $noWrap=' nowrap';

	var $name;			// Form data name prefix
	var $table;			// table for the query
	var $fieldList;		// field list
	var $fields = array();	// Array of the fields possible
	var $extFieldLists = array();
	var $queryConfig=array(); // The query config
	var $enablePrefix=0;
	var $enableQueryParts = 0;
	var $extJSCODE='';

	protected $formName = '';







	/**
	 * @return	[type]		...
	 */
	function makeFieldList()	{
		global $TCA;
		$fieldListArr = array();
		if (is_array($TCA[$this->table]))	{
			t3lib_div::loadTCA($this->table);
			foreach ($TCA[$this->table]['columns'] as $fN => $value) {
				$fieldListArr[]=$fN;
			}
			$fieldListArr[]='uid';
			$fieldListArr[]='pid';
			$fieldListArr[]='deleted';
			if ($TCA[$this->table]['ctrl']['tstamp'])	$fieldListArr[]=$TCA[$this->table]['ctrl']['tstamp'];
			if ($TCA[$this->table]['ctrl']['crdate'])	$fieldListArr[]=$TCA[$this->table]['ctrl']['crdate'];
			if ($TCA[$this->table]['ctrl']['cruser_id'])	$fieldListArr[]=$TCA[$this->table]['ctrl']['cruser_id'];
			if ($TCA[$this->table]['ctrl']['sortby'])	$fieldListArr[]=$TCA[$this->table]['ctrl']['sortby'];
		}
		return implode(',',$fieldListArr);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$table: ...
	 * @param	[type]		$fieldList: ...
	 * @return	[type]		...
	 */
	function init($name,$table,$fieldList='')	{
		global $TCA;

			// Analysing the fields in the table.
		if (is_array($TCA[$table]))	{
			t3lib_div::loadTCA($table);
			$this->name = $name;
			$this->table = $table;
			$this->fieldList = $fieldList ? $fieldList : $this->makeFieldList();

			$fieldArr = t3lib_div::trimExplode(',',$this->fieldList,1);
			foreach ($fieldArr as $fN) {
				$fC = $TCA[$this->table]['columns'][$fN];
				$this->fields[$fN] = $fC['config'];
				$this->fields[$fN]['exclude'] = $fC['exclude'];
				if (is_array($fC) && $fC['label'])	{
					$this->fields[$fN]['label'] = rtrim(trim($GLOBALS['LANG']->sL($fC['label'])), ':');
					switch ($this->fields[$fN]['type'])	{
						case 'input':
							if (preg_match('/int|year/i', $this->fields[$fN]['eval']))	{
								$this->fields[$fN]['type']='number';
							} elseif (preg_match('/time/i', $this->fields[$fN]['eval']))	{
								$this->fields[$fN]['type'] = 'time';
							} elseif (preg_match('/date/i', $this->fields[$fN]['eval']))	{
								$this->fields[$fN]['type']='date';
							} else {
								$this->fields[$fN]['type']='text';
							}
						break;
						case 'check':
							if (!$this->fields[$fN]['items'])	{
								$this->fields[$fN]['type'] = 'boolean';
							} else {
								$this->fields[$fN]['type'] = 'binary';
							}
						break;
						case 'radio':
							$this->fields[$fN]['type'] = 'multiple';
						break;
						case 'select':
							$this->fields[$fN]['type'] = 'multiple';
							if ($this->fields[$fN]['foreign_table'])	{
								$this->fields[$fN]['type'] = 'relation';
							}
							if ($this->fields[$fN]['special'])	{
								$this->fields[$fN]['type'] = 'text';
							}
						break;
						case 'group':
							$this->fields[$fN]['type'] = 'files';
							if ($this->fields[$fN]['internal_type'] == 'db')	{
								$this->fields[$fN]['type'] = 'relation';
							}
						break;
						case 'user':
						case 'flex':
						case 'passthrough':
						case 'none':
						case 'text':
						default:
							$this->fields[$fN]['type']='text';
						break;
					}

				} else {
					$this->fields[$fN]['label']='[FIELD: '.$fN.']';
					switch ($fN)	{
						case 'pid':
							$this->fields[$fN]['type'] = 'relation';
							$this->fields[$fN]['allowed'] = 'pages';
						break;
						case 'cruser_id':
							$this->fields[$fN]['type'] = 'relation';
							$this->fields[$fN]['allowed'] = 'be_users';
						break;
						case 'tstamp':
						case 'crdate':
							$this->fields[$fN]['type'] = 'time';
						break;
						case 'deleted':
							$this->fields[$fN]['type'] = 'boolean';
						break;
						default:
							$this->fields[$fN]['type'] = 'number';
						break;
					}
				}
			}
		}

		/*	// EXAMPLE:
		$this->queryConfig = array(
			array(
				'operator' => 'AND',
				'type' => 'FIELD_spaceBefore',
			),
			array(
				'operator' => 'AND',
				'type' => 'FIELD_records',
				'negate' => 1,
				'inputValue' => 'foo foo'
			),
			array(
				'type' => 'newlevel',
				'nl' => array(
					array(
						'operator' => 'AND',
						'type' => 'FIELD_spaceBefore',
						'negate' => 1,
						'inputValue' => 'foo foo'
					),
					array(
						'operator' => 'AND',
						'type' => 'FIELD_records',
						'negate' => 1,
						'inputValue' => 'foo foo'
					)
				)
			),
			array(
				'operator' => 'OR',
				'type' => 'FIELD_maillist',
			)
		);
		*/
		$this->initUserDef();
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$list: ...
	 * @param	[type]		$force: ...
	 * @return	[type]		...
	 */
	function setAndCleanUpExternalLists($name,$list,$force='')	{
		$fields = array_unique(t3lib_div::trimExplode(',',$list.','.$force,1));
		$reList=array();
		foreach ($fields as $fN) {
			if ($this->fields[$fN])		$reList[]=$fN;
		}
		$this->extFieldLists[$name]=implode(',',$reList);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$qC: ...
	 * @return	[type]		...
	 */
	function procesData($qC='')	{
		$this->queryConfig = $qC;

		$POST = t3lib_div::_POST();

		// if delete...
		if($POST['qG_del']) {
			//initialize array to work on, save special parameters
			$ssArr = $this->getSubscript($POST['qG_del']);
			$workArr =& $this->queryConfig;
			for($i=0;$i<sizeof($ssArr)-1;$i++) {
				$workArr =& $workArr[$ssArr[$i]];
			}
			// delete the entry and move the other entries
			unset($workArr[$ssArr[$i]]);
			for($j=$ssArr[$i];$j<sizeof($workArr);$j++) {
				$workArr[$j] = $workArr[$j+1];
				unset($workArr[$j+1]);
			}
		}

		// if insert...
		if($POST['qG_ins']) {
			//initialize array to work on, save special parameters
			$ssArr = $this->getSubscript($POST['qG_ins']);
			$workArr =& $this->queryConfig;
			for($i=0;$i<sizeof($ssArr)-1;$i++) {
				$workArr =& $workArr[$ssArr[$i]];
			}
			// move all entries above position where new entry is to be inserted
			for($j=sizeof($workArr);$j>$ssArr[$i];$j--) {
				$workArr[$j] = $workArr[$j-1];
			}
			//clear new entry position
			unset($workArr[$ssArr[$i]+1]);
			$workArr[$ssArr[$i]+1]['type'] = 'FIELD_';
		}

		// if move up...
		if($POST['qG_up']) {
			//initialize array to work on
			$ssArr = $this->getSubscript($POST['qG_up']);
			$workArr =& $this->queryConfig;
			for($i=0;$i<sizeof($ssArr)-1;$i++) {
				$workArr =& $workArr[$ssArr[$i]];
			}
			//swap entries
			$qG_tmp = $workArr[$ssArr[$i]];
			$workArr[$ssArr[$i]] = $workArr[$ssArr[$i]-1];
			$workArr[$ssArr[$i]-1] = $qG_tmp;
		}

		// if new level...
		if($POST['qG_nl']) {
			//initialize array to work on
			$ssArr = $this->getSubscript($POST['qG_nl']);
			$workArr =& $this->queryConfig;
			for($i=0;$i<sizeof($ssArr)-1;$i++) {
				$workArr =& $workArr[$ssArr[$i]];
			}
			// Do stuff:
			$tempEl = $workArr[$ssArr[$i]];
			if (is_array($tempEl))	{
				if ($tempEl['type']!='newlevel')	{
					$workArr[$ssArr[$i]]=array(
						'type' => 'newlevel',
						'operator' => $tempEl['operator'],
						'nl' => array($tempEl)
					);
				}
			}
		}

		// if collapse level...
		if($POST['qG_remnl']) {
			//initialize array to work on
			$ssArr = $this->getSubscript($POST['qG_remnl']);
			$workArr =& $this->queryConfig;
			for($i=0;$i<sizeof($ssArr)-1;$i++) {
				$workArr =& $workArr[$ssArr[$i]];
			}

			// Do stuff:
			$tempEl = $workArr[$ssArr[$i]];
			if (is_array($tempEl))	{
				if ($tempEl['type']=='newlevel')	{
					$a1 = array_slice($workArr,0,$ssArr[$i]);
					$a2 = array_slice($workArr,$ssArr[$i]);
					array_shift($a2);
					$a3 = $tempEl['nl'];
					$a3[0]['operator'] = $tempEl['operator'];
					$workArr=array_merge($a1,$a3,$a2);
				}
			}
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$queryConfig: ...
	 * @return	[type]		...
	 */
	function cleanUpQueryConfig($queryConfig)	{
		//since we dont traverse the array using numeric keys in the upcoming whileloop make sure it's fresh and clean before displaying
		if (is_array($queryConfig))	{
			ksort($queryConfig);
		} else {
			//queryConfig should never be empty!
			if(!$queryConfig[0] || !$queryConfig[0]['type']) $queryConfig[0] = array('type'=>'FIELD_');
		}
			// Traverse:
		$c=0;
		$arrCount=0;
		foreach ($queryConfig as $key => $conf) {
			if(substr($conf['type'],0,6)=='FIELD_') {
				$fName = substr($conf['type'],6);
				$fType = $this->fields[$fName]['type'];
			} elseif($conf['type']=='newlevel') {
				$fType = $conf['type'];
			} else {
				$fType = 'ignore';
			}
//			debug($fType);
			switch($fType)	{
				case 'newlevel':
					if(!$queryConfig[$key]['nl']) $queryConfig[$key]['nl'][0]['type'] = 'FIELD_';
					$queryConfig[$key]['nl']=$this->cleanUpQueryConfig($queryConfig[$key]['nl']);
				break;
				case 'userdef':
					$queryConfig[$key]=$this->userDefCleanUp($queryConfig[$key]);
				break;
				case 'ignore':
				default:
//					debug($queryConfig[$key]);
					$verifiedName=$this->verifyType($fName);
					$queryConfig[$key]['type']='FIELD_'.$this->verifyType($verifiedName);

					if($conf['comparison'] >> 5 != $this->comp_offsets[$fType]) $conf['comparison'] = $this->comp_offsets[$fType] << 5;
					$queryConfig[$key]['comparison']=$this->verifyComparison($conf['comparison'],$conf['negate']?1:0);

					$queryConfig[$key]['inputValue']=$this->cleanInputVal($queryConfig[$key]);
					$queryConfig[$key]['inputValue1']=$this->cleanInputVal($queryConfig[$key],1);

//					debug($queryConfig[$key]);
			break;
			}
		}
		return $queryConfig;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$subLevel: ...
	 * @param	[type]		$queryConfig: ...
	 * @param	[type]		$parent: ...
	 * @return	[type]		...
	 */
	function getFormElements($subLevel=0,$queryConfig='',$parent='')	{
		$codeArr=array();
		if (!is_array($queryConfig))	$queryConfig=$this->queryConfig;

		$c=0;
		$arrCount=0;
		foreach ($queryConfig as $key => $conf) {
			$subscript = $parent.'['.$key.']';
			$lineHTML = '';
			$lineHTML.=$this->mkOperatorSelect($this->name.$subscript,$conf['operator'],$c,($conf['type']!='FIELD_'));
			if(substr($conf['type'],0,6)=='FIELD_') {
				$fName = substr($conf['type'],6);
				$this->fieldName = $fName;
				$fType = $this->fields[$fName]['type'];
				if($conf['comparison'] >> 5 != $this->comp_offsets[$fType]) $conf['comparison'] = $this->comp_offsets[$fType] << 5;

				//nasty nasty...
				//make sure queryConfig contains _actual_ comparevalue.
				//mkCompSelect don't care, but getQuery does.
				$queryConfig[$key]['comparison'] += (isset($conf['negate'])-($conf['comparison']%2));

			} elseif($conf['type']=='newlevel') {
				$fType = $conf['type'];
			} else {
				$fType = 'ignore';
			}
			switch($fType)	{
				case 'ignore':
				break;
				case 'newlevel':
					if(!$queryConfig[$key]['nl']) $queryConfig[$key]['nl'][0]['type'] = 'FIELD_';
					$lineHTML.='<input type="hidden" name="'.$this->name.$subscript.'[type]" value="newlevel">';
					$codeArr[$arrCount]['sub'] = $this->getFormElements($subLevel+1,$queryConfig[$key]['nl'],$subscript.'[nl]');
				break;
				case 'userdef':
					 $lineHTML.=$this->userDef($this->name.$subscript,$conf,$fName,$fType);
				break;
				case 'date':
					$lineHTML.=$this->mkTypeSelect($this->name.$subscript.'[type]',$fName);
					$lineHTML.=$this->mkCompSelect($this->name.$subscript.'[comparison]',$conf['comparison'],$conf['negate']?1:0);
					$lineHTML.='<input type="checkbox" class="checkbox"' . ($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';

					if ($conf['comparison']==100 || $conf['comparison']==101)	{	// between
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue]_hr'.'" value="'.strftime('%e-%m-%Y', $conf['inputValue']).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue]\', \'date\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'">';
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue1]_hr'.'" value="'.strftime('%e-%m-%Y', $conf['inputValue1']).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue1]\', \'date\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue1']).'" name="'.$this->name.$subscript.'[inputValue1]'.'">';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue]", "date", "", 0,0);';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue1]", "date", "", 0,0);';
					} else {
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue]_hr'.'" value="'.strftime('%e-%m-%Y', $conf['inputValue']).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue]\', \'date\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'">';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue]", "date", "", 0,0);';
					}
				break;
				case 'time':
					$lineHTML.=$this->mkTypeSelect($this->name.$subscript.'[type]', $fName);
					$lineHTML.=$this->mkCompSelect($this->name.$subscript.'[comparison]', $conf['comparison'], $conf['negate']?1:0);

					$lineHTML.='<input type="checkbox" class="checkbox"'.($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';
					if ($conf['comparison']==100 || $conf['comparison']==101)	{	// between:
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue]_hr'.'" value="'.strftime('%H:%M %e-%m-%Y', $conf['inputValue']).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue]\', \'datetime\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'">';
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue1]_hr'.'" value="'.strftime('%H:%M %e-%m-%Y', $conf['inputValue1']).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue1]\', \'datetime\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue1']).'" name="'.$this->name.$subscript.'[inputValue1]'.'">';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue]", "datetime", "", 0,0);';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue1]", "datetime", "", 0,0);';
					} else {
						$lineHTML.='<input type="text" name="'.$this->name.$subscript.'[inputValue]_hr'.'" value="'.strftime('%H:%M %e-%m-%Y', intval($conf['inputValue'])).'" '.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' onChange="typo3form.fieldGet(\''.$this->name.$subscript.'[inputValue]\', \'datetime\', \'\', 0,0);"><input type="hidden" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'">';
						$this->extJSCODE.='typo3form.fieldSet("'.$this->name.$subscript.'[inputValue]", "datetime", "", 0,0);';
					}
				break;
				case 'multiple':
				case 'binary':
				case 'relation':
					$lineHTML.=$this->mkTypeSelect($this->name.$subscript.'[type]', $fName);
					$lineHTML.=$this->mkCompSelect($this->name.$subscript.'[comparison]', $conf['comparison'], $conf['negate']?1:0);
					$lineHTML.='<input type="checkbox" class="checkbox"'.($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';
					if ($conf['comparison']==68 || $conf['comparison']==69 || $conf['comparison']==162 || $conf['comparison']==163)	{
						$lineHTML.='<select name="'.$this->name.$subscript.'[inputValue]'.'[]" style="vertical-align:top;" size="5" multiple>';
					} elseif ($conf['comparison']==66 || $conf['comparison']==67)	{
						if (is_array($conf['inputValue']))	{
							$conf['inputValue'] = implode(',', $conf['inputValue']);
						}
						$lineHTML.= '<input type="text" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).'>';
					} else {
						$lineHTML.= '<select name="'.$this->name.$subscript.'[inputValue]'.'" style="vertical-align:top;" onChange="submit();">';
					}
					if ($conf['comparison']!=66 && $conf['comparison']!=67)	{
						$lineHTML.= $this->makeOptionList($fName, $conf, $this->table);
						$lineHTML.= '</select>';
					}
				break;
				case 'files':
					$lineHTML.= $this->mkTypeSelect($this->name.$subscript.'[type]', $fName);
					$lineHTML.= $this->mkCompSelect($this->name.$subscript.'[comparison]', $conf['comparison'], $conf['negate']?1:0);
					$lineHTML.= '<input type="checkbox" class="checkbox"'.($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';
					if ($conf['comparison']==68 || $conf['comparison']==69)	{
						$lineHTML .= '<select name="'.$this->name.$subscript.'[inputValue]'.'[]" style="vertical-align:top;" size="5" multiple>';
					} else {
						$lineHTML .= '<select name="'.$this->name.$subscript.'[inputValue]'.'" style="vertical-align:top;" onChange="submit();">';
					}
					$lineHTML .= '<option value=""></option>'.$this->makeOptionList($fName, $conf, $this->table);
					$lineHTML .= '</select>';
					if ($conf['comparison']==66 || $conf['comparison']==67)	{
						$lineHTML .= ' + <input type="text" value="'.htmlspecialchars($conf['inputValue1']).'" name="'.$this->name.$subscript.'[inputValue1]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).'>';
					}
				break;
				case 'boolean':
					$lineHTML .= $this->mkTypeSelect($this->name.$subscript.'[type]', $fName);
					$lineHTML .= $this->mkCompSelect($this->name.$subscript.'[comparison]', $conf['comparison'], $conf['negate']?1:0);
					$lineHTML .= '<input type="checkbox" class="checkbox"'.($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';
					$lineHTML .= '<input type="hidden" value="1" name="'.$this->name.$subscript.'[inputValue]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).'>';
				break;
				default:
					$lineHTML .= $this->mkTypeSelect($this->name.$subscript.'[type]', $fName);
					$lineHTML .= $this->mkCompSelect($this->name.$subscript.'[comparison]', $conf['comparison'], $conf['negate']?1:0);
					$lineHTML .= '<input type="checkbox" class="checkbox"'.($conf['negate']?' checked':'').' name="'.$this->name.$subscript.'[negate]'.'" onClick="submit();">';
					if ($conf['comparison']==37 || $conf['comparison']==36)	{	// between:
						$lineHTML.='<input type="text" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(5).'>
						<input type="text" value="'.htmlspecialchars($conf['inputValue1']).'" name="'.$this->name.$subscript.'[inputValue1]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(5).'>';	// onChange='submit();'
					} else {
						$lineHTML.='<input type="text" value="'.htmlspecialchars($conf['inputValue']).'" name="'.$this->name.$subscript.'[inputValue]'.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).'>';	// onChange="submit();"
					}
				break;
			}
			if($fType != 'ignore') {
				$lineHTML .= $this->updateIcon();
				if ($loopcount)	{
					$lineHTML .= '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/garbage.gif', 'width="11" height="12"') . 'title="Remove condition" name="qG_del'.$subscript.'">';
				}
				$lineHTML .= '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/add.gif', 'width="12" height="12"') . ' title="Add condition" name="qG_ins'.$subscript.'">';
				if($c!=0) $lineHTML.= '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/pil2up.gif', 'width="12" height="7"') . ' title="Move up" name="qG_up'.$subscript.'">';

				if($c!=0 && $fType!='newlevel') {
					$lineHTML.= '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/pil2right.gif', 'height="12" width="7"') . ' title="New level" name="qG_nl'.$subscript.'">';
				}
				if($fType=='newlevel') {
					$lineHTML.= '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/pil2left.gif',  'height="12" width="7"') . ' title="Collapse new level" name="qG_remnl'.$subscript.'">';
				}

				$codeArr[$arrCount]['html'] = $lineHTML;
				$codeArr[$arrCount]['query'] = $this->getQuerySingle($conf,$c>0?0:1);
				$arrCount++;
				$c++;
			}
			$loopcount = 1;
		}
//		$codeArr[$arrCount] .='<input type="hidden" name="CMD" value="displayQuery">';
		$this->queryConfig = $queryConfig;
//modifyHTMLColor($color,$R,$G,$B)
		return $codeArr;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$codeArr: ...
	 * @param	[type]		$l: ...
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function makeOptionList($fN, $conf, $table)	{
		$fieldSetup = $this->fields[$fN];
		if ($fieldSetup['type']=='files')	{
			if ($conf['comparison']==66 || $conf['comparison']==67)	{
				$fileExtArray = explode(',', $fieldSetup['allowed']);
				natcasesort($fileExtArray);
				foreach ($fileExtArray as $fileExt)	{
					if (t3lib_div::inList($conf['inputValue'], $fileExt))	{
						$out .= '<option value="'.$fileExt.'" selected>.'.$fileExt.'</option>';
					} else {
						$out .= '<option value="'.$fileExt.'">.'.$fileExt.'</option>';
					}
				}
			}
			$d = dir(t3lib_div::getIndpEnv(TYPO3_DOCUMENT_ROOT).'/'.$fieldSetup['uploadfolder']);
			while (false !== ($entry=$d->read()))	{
				if ($entry=='.' || $entry=='..')	{
					continue;
				}
				$fileArray[] = $entry;
			}
			$d->close();
			natcasesort($fileArray);
			foreach ($fileArray as $fileName)	{
				if (t3lib_div::inList($conf['inputValue'], $fileName))	{
					$out .= '<option value="'.$fileName.'" selected>'.$fileName.'</option>';
				} else {
					$out .= '<option value="'.$fileName.'">'.$fileName.'</option>';
				}
			}
		}
		if ($fieldSetup['type']=='multiple')	{
			foreach ($fieldSetup['items'] as $key=>$val)	{
				if (substr($val[0], 0, 4) == 'LLL:')	{
					$value = $GLOBALS['LANG']->sL($val[0]);
				} else {
					$value = $val[0];
				}
				if (t3lib_div::inList($conf['inputValue'], $val[1]))	{
					$out .= '<option value="'.$val[1].'" selected>'.$value.'</option>';
				} else {
					$out .= '<option value="'.$val[1].'">'.$value.'</option>';
				}
			}
		}
		if ($fieldSetup['type']=='binary')	{
			foreach ($fieldSetup['items'] as $key=>$val)	{
				if (substr($val[0], 0, 4)=='LLL:')	{
					$value = $GLOBALS['LANG']->sL($val[0]);
				} else {
					$value = $val[0];
				}
				if (t3lib_div::inList($conf['inputValue'], pow(2, $key)))	{
					$out .= '<option value="'.pow(2, $key).'" selected>'.$value.'</option>';
				} else {
					$out .= '<option value="'.pow(2, $key).'">'.$value.'</option>';
				}
			}
		}
		if ($fieldSetup['type']=='relation')	{
			if ($fieldSetup['items'])	{
				foreach ($fieldSetup['items'] as $key=>$val)	{
					if (substr($val[0], 0, 4) == 'LLL:')	{
						$value = $GLOBALS['LANG']->sL($val[0]);
					} else {
						$value = $val[0];
					}
					if (t3lib_div::inList($conf['inputValue'], $val[1]))	{
						$out .= '<option value="'.$val[1].'" selected>'.$value.'</option>';
					} else {
						$out .= '<option value="'.$val[1].'">'.$value.'</option>';
					}
				}
			}
			global $TCA;
			if (stristr($fieldSetup['allowed'], ','))	{
				$from_table_Arr = explode(',', $fieldSetup['allowed']);
				$useTablePrefix = 1;
				if (!$fieldSetup['prepend_tname'])	{
					$checkres = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fN, $table, t3lib_BEfunc::deleteClause($table), $groupBy = '', $orderBy = '', $limit = '');
					if ($checkres)	{
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($checkres))	{
							if (stristr($row[$fN], ','))	{
								$checkContent = explode(',', $row[$fN]);
								foreach ($checkContent as $singleValue)	{
									if (!stristr($singleValue, '_'))	{
										$dontPrefixFirstTable = 1;
									}
								}
							} else {
								$singleValue = $row[$fN];
								if (strlen($singleValue) && !stristr($singleValue, '_'))	{
									$dontPrefixFirstTable = 1;
								}
							}
						}
					}
				}
			} else {
				$from_table_Arr[0] = $fieldSetup['allowed'];
			}
			if ($fieldSetup['prepend_tname'])	{
				$useTablePrefix = 1;
			}
			if ($fieldSetup['foreign_table'])	{
				$from_table_Arr[0] = $fieldSetup['foreign_table'];
			}
			$counter = 0;
			while (list(, $from_table) = each($from_table_Arr))	{
				if (($useTablePrefix && !$dontPrefixFirstTable && $counter!=1) || $counter==1)	{
					$tablePrefix = $from_table.'_';
				}
				$counter = 1;
				if (is_array($TCA[$from_table]))	{
					t3lib_div::loadTCA($from_table);
					$labelField = $TCA[$from_table]['ctrl']['label'];
					$altLabelField = $TCA[$from_table]['ctrl']['label_alt'];
					if ($TCA[$from_table]['columns'][$labelField]['config']['items'])	{
						foreach ($TCA[$from_table]['columns'][$labelField]['config']['items'] as $labelArray)	{
							if (substr($labelArray[0], 0, 4) == 'LLL:')	{
								$labelFieldSelect[$labelArray[1]] = $GLOBALS['LANG']->sL($labelArray[0]);
							} else {
								$labelFieldSelect[$labelArray[1]] = $labelArray[0];
							}
						}
						$useSelectLabels = 1;
					}
					if ($TCA[$from_table]['columns'][$altLabelField]['config']['items'])	{
						foreach ($TCA[$from_table]['columns'][$altLabelField]['config']['items'] as $altLabelArray)	{
							if (substr($altLabelArray[0], 0, 4) == 'LLL:')	{
								$altLabelFieldSelect[$altLabelArray[1]] = $GLOBALS['LANG']->sL($altLabelArray[0]);
							} else {
								$altLabelFieldSelect[$altLabelArray[1]] = $altLabelArray[0];
							}
						}
						$useAltSelectLabels = 1;
					}
					$altLabelFieldSelect = $altLabelField ? ','.$altLabelField : '';
					$select_fields = 'uid,'.$labelField.$altLabelFieldSelect;
					if (!$GLOBALS['BE_USER']->isAdmin() && $GLOBALS['TYPO3_CONF_VARS']['BE']['lockBeUserToDBmounts'])	{
						$webMounts = $GLOBALS['BE_USER']->returnWebmounts();
						$perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
						foreach ($webMounts as $key => $val)	{
							if ($webMountPageTree)	{
								$webMountPageTreePrefix = ',';
							}
							$webMountPageTree .= $webMountPageTreePrefix.$this->getTreeList($val, 999, $begin = 0, $perms_clause);
						}
						if ($from_table=='pages')	{
							$where_clause = 'uid IN ('.$webMountPageTree.') ';
							if (!$GLOBALS['SOBE']->MOD_SETTINGS['show_deleted'])	{
								$where_clause .= t3lib_BEfunc::deleteClause($from_table).' AND'.$perms_clause;
							}
						} else {
							$where_clause = 'pid IN ('.$webMountPageTree.') ';
							if (!$GLOBALS['SOBE']->MOD_SETTINGS['show_deleted'])	{
								$where_clause .= t3lib_BEfunc::deleteClause($from_table);
							}
						}
					} else {
						$where_clause = 'uid';
						if (!$GLOBALS['SOBE']->MOD_SETTINGS['show_deleted'])	{
							$where_clause .= t3lib_BEfunc::deleteClause($from_table);
						}
					}
					$orderBy = 'uid';
					if (!$this->tableArray[$from_table])	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy, $limit = '');
					}
					if ($res)	{
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							$this->tableArray[$from_table][] = $row;
						}
					}
					foreach ($this->tableArray[$from_table] as $key=>$val)	{
						if ($useSelectLabels)	{
							$outArray[$tablePrefix.$val['uid']] = htmlspecialchars($labelFieldSelect[$val[$labelField]]);
						} elseif ($val[$labelField])	{
							$outArray[$tablePrefix.$val['uid']] = htmlspecialchars($val[$labelField]);
						} elseif ($useAltSelectLabels)	{
							$outArray[$tablePrefix.$val['uid']] = htmlspecialchars($altLabelFieldSelect[$val[$altLabelField]]);
						} else {
							$outArray[$tablePrefix.$val['uid']] = htmlspecialchars($val[$altLabelField]);
						}
					}
					if ($GLOBALS['SOBE']->MOD_SETTINGS['options_sortlabel'] && is_array($outArray))	{
						natcasesort($outArray);
					}
				}
			}
			foreach ($outArray as $key2 => $val2)	{
				if (t3lib_div::inList($conf['inputValue'], $key2))	{
					$out .= '<option value="'.$key2.'" selected>['.$key2.'] '.$val2.'</option>';
				} else {
					$out .= '<option value="'.$key2.'">['.$key2.'] '.$val2.'</option>';
				}
			}
		}
		return $out;
	}


	 /**
 * [Describe function...]
 *
 * @param	[type]		$codeArr: ...
 * @param	[type]		$l: ...
 * @return	[type]		...
 */
	function printCodeArray($codeArr,$l=0)	{
		$line='';
		if ($l)		$indent='<td style="vertical-align:top;"><img height="1" width="50"></td>';
		$lf=$l*30;
		$bgColor = t3lib_div::modifyHTMLColor($GLOBALS['TBE_TEMPLATE']->bgColor2,$lf,$lf,$lf);
		foreach ($codeArr as $k => $v) {
			$line.= '<tr>'.$indent.'<td bgcolor="'.$bgColor.'"'.$this->noWrap.'>'.$v['html'].'</td></tr>';
			if ($this->enableQueryParts)	{$line.= '<tr>'.$indent.'<td>'.$this->formatQ($v['query']).'</td></tr>';}
			if (is_array($v['sub']))	{
				$line.= '<tr>'.$indent.'<td'.$this->noWrap.'>'.$this->printCodeArray($v['sub'],$l+1).'</td></tr>';
			}
		}
		$out='<table border="0" cellpadding="0" cellspacing="1">'.$line.'</table>';
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$str: ...
	 * @return	[type]		...
	 */
	function formatQ($str)	{
		return '<font size="1" face="verdana" color="maroon"><i>' . htmlspecialchars($str) . '</i></font>';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$op: ...
	 * @param	[type]		$draw: ...
	 * @param	[type]		$submit: ...
	 * @return	[type]		...
	 */
	function mkOperatorSelect($name,$op,$draw,$submit)	{
		if ($draw)	{
			$out='<select name="'.$name.'[operator]"'.($submit?' onChange="submit();"':'').'>';	//
			$out.='<option value="AND"'.(!$op||$op=="AND" ? ' selected':'').'>'.$this->lang["AND"].'</option>';
			$out.='<option value="OR"'.($op=='OR' ? ' selected':'').'>'.$this->lang['OR'].'</option>';
			$out.='</select>';
		} else {
			$out.='<input type="hidden" value="'.$op.'" name="'.$name.'[operator]">';
			$out.='<img src="clear.gif" height="1" width="47">';

		}
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$fieldName: ...
	 * @param	[type]		$prepend: ...
	 * @return	[type]		...
	 */
	function mkTypeSelect($name,$fieldName,$prepend='FIELD_')	{
		$out='<select name="'.$name.'" onChange="submit();">';
		$out.='<option value=""></option>';
		foreach ($this->fields as $key => $value) {
			if (!$fieldValue['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields', $this->table.':'.$key)) {
				$label = $this->fields[$key]['label'];
				$label_alt = $this->fields[$key]['label_alt'];
				$out .= '<option value="'.$prepend.$key.'"'.($key==$fieldName ? ' selected' : '').'>'.$label.'</option>';
			}
		}
		$out.='</select>';
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$fieldName: ...
	 * @return	[type]		...
	 */
	function verifyType($fieldName)	{
		$first = '';
		foreach ($this->fields as $key => $value) {
			if (!$first)	$first = $key;
			if ($key==$fieldName) return $key;
		}
		return $first;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$comparison: ...
	 * @param	[type]		$neg: ...
	 * @return	[type]		...
	 */
	function verifyComparison($comparison,$neg)	{
		$compOffSet = $comparison >> 5;
		$first=-1;
		for($i=32*$compOffSet+$neg;$i<32*($compOffSet+1);$i+=2) {
			if ($first==-1)	$first = $i;
			if (($i >> 1)==($comparison >> 1))	{
				return $i;
			}
		}
		return $first;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$fieldName: ...
	 * @return	[type]		...
	 */
	function mkFieldToInputSelect($name,$fieldName)	{
		$out='<input type="Text" value="'.htmlspecialchars($fieldName).'" name="'.$name.'"'.$GLOBALS['TBE_TEMPLATE']->formWidth().'>'.$this->updateIcon();
		$out.='<a href="#" onClick="document.forms[0][\''.$name.'\'].value=\'\';return false;">' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete', array('title' => 'Clear list')) . '</a>';
		$out.='<BR><select name="_fieldListDummy" size="5" onChange="document.forms[0][\''.$name.'\'].value+=\',\'+this.value">';
		foreach ($this->fields as $key => $value) {
			if (!$fieldValue['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields', $this->table.':'.$key))	{
				$label = $this->fields[$key]['label'];
				$label_alt = $this->fields[$key]['label_alt'];
				$out .= '<option value="'.$key.'"'.($key==$fieldName ? ' selected':'').'>'.$label.'</option>';
			}
		}
		$out.='</select>';
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$cur: ...
	 * @return	[type]		...
	 */
	function mkTableSelect($name,$cur)	{
		global $TCA;
		$out='<select name="'.$name.'" onChange="submit();">';
		$out.='<option value=""></option>';
		foreach ($TCA as $tN => $value) {
			if ($GLOBALS['BE_USER']->check('tables_select',$tN))	{
				$out.='<option value="'.$tN.'"'.($tN==$cur ? ' selected':'').'>'.$GLOBALS['LANG']->sl($TCA[$tN]['ctrl']['title']).'</option>';
			}
		}
		$out.='</select>';
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$name: ...
	 * @param	[type]		$comparison: ...
	 * @param	[type]		$neg: ...
	 * @return	[type]		...
	 */
	function mkCompSelect($name,$comparison,$neg)	{
		$compOffSet = $comparison >> 5;
		$out='<select name="'.$name.'" onChange="submit();">';
		for($i=32*$compOffSet+$neg;$i<32*($compOffSet+1);$i+=2) {
			if($this->lang['comparison'][$i.'_']) {
				$out.='<option value="'.$i.'"'.(($i >> 1)==($comparison >> 1) ? ' selected':'').'>'.$this->lang['comparison'][$i.'_'].'</option>';
			}
		}
		$out.='</select>';
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$arr: ...
	 * @return	[type]		...
	 */
	function getSubscript($arr) {
		while(is_array($arr)) {
			reset($arr);
			list($key,)=each($arr);
			$retArr[] = $key;
			$arr = $arr[$key];
		}
		return $retArr;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function initUserDef()	{

	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function userDef()	{
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$queryConfig: ...
	 * @return	[type]		...
	 */
	function userDefCleanUp($queryConfig)	{
		return $queryConfig;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$queryConfig: ...
	 * @param	[type]		$pad: ...
	 * @return	[type]		...
	 */
	function getQuery ($queryConfig,$pad='') {
		$qs = '';
			// Since we don't traverse the array using numeric keys in the upcoming whileloop make sure it's fresh and clean
		ksort($queryConfig);
		$first=1;
		foreach ($queryConfig as $key => $conf) {
			switch($conf['type']) {
				case 'newlevel':
					$qs.=LF.$pad.trim($conf['operator']).' ('.$this->getQuery($queryConfig[$key]['nl'],$pad.'   ').LF.$pad.')';
				break;
				case 'userdef':
					$qs.=LF.$pad.getUserDefQuery($conf,$first);
				break;
				default:
					$qs.=LF.$pad.$this->getQuerySingle($conf,$first);
				break;
			}
			$first=0;
		}
		return $qs;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$conf: ...
	 * @param	[type]		$first: ...
	 * @return	[type]		...
	 */
	function getQuerySingle($conf,$first)	{
		$prefix = $this->enablePrefix ? $this->table.'.' : '';
		if (!$first)	{
				// Is it OK to insert the AND operator if none is set?
			$qs .= trim(($conf['operator'] ? $conf['operator'] : 'AND')).' ';
		}
		$qsTmp = str_replace('#FIELD#', $prefix.trim(substr($conf['type'],6)), $this->compSQL[$conf['comparison']]);
		$inputVal = $this->cleanInputVal($conf);
		if ($conf['comparison']==68 || $conf['comparison']==69)	{
			$inputVal = explode(',', $inputVal);
			foreach ($inputVal as $key => $fileName)	{
				$inputVal[$key] = "'".$fileName."'";
			}
			$inputVal = implode(',', $inputVal);
			$qsTmp = str_replace('#VALUE#', $inputVal, $qsTmp);
		} elseif ($conf['comparison']==162 || $conf['comparison']==163)	{
			$inputValArray = explode(',', $inputVal);
			$inputVal = 0;
			foreach ($inputValArray as $key=>$fileName)	{
				$inputVal += intval($fileName);
			}
			$qsTmp = str_replace('#VALUE#', $inputVal, $qsTmp);
		} else {
		$qsTmp = str_replace('#VALUE#', $GLOBALS['TYPO3_DB']->quoteStr($inputVal,$this->table), $qsTmp);
		}
		if ($conf['comparison']==37 || $conf['comparison']==36 || $conf['comparison']==66 || $conf['comparison']==67 || $conf['comparison']==100 || $conf['comparison']==101)	{	// between:
			$inputVal = $this->cleanInputVal($conf,'1');
			$qsTmp = str_replace('#VALUE1#', $GLOBALS['TYPO3_DB']->quoteStr($inputVal,$this->table), $qsTmp);
		}
		$qs .= trim($qsTmp);
		return $qs;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$conf: ...
	 * @param	[type]		$suffix: ...
	 * @return	[type]		...
	 */
	function cleanInputVal($conf,$suffix='')	{
		if(($conf['comparison'] >> 5==0) || ($conf['comparison']==32 || $conf['comparison']==33 || $conf['comparison']==64 || $conf['comparison']==65 || $conf['comparison']==66 || $conf['comparison']==67 || $conf['comparison']==96 || $conf['comparison']==97))	{
			$inputVal = $conf['inputValue'.$suffix];
		} elseif ($conf['comparison']==39 || $conf['comparison']==38) {	// in list:
			$inputVal = implode(',',t3lib_div::intExplode(',',$conf['inputValue'.$suffix]));
		} elseif ($conf['comparison']==68 || $conf['comparison']==69 || $conf['comparison']==162 || $conf['comparison']==163) {	// in list:
			if (is_array($conf['inputValue'.$suffix])) {
				$inputVal = implode(',', $conf['inputValue'.$suffix]);
			} elseif ($conf['inputValue'.$suffix]) {
				$inputVal = $conf['inputValue'.$suffix];
			} else {
				$inputVal = 0;
			}
		} else {
			$inputVal = doubleval($conf['inputValue'.$suffix]);
		}
		return $inputVal;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$qcArr: ...
	 * @return	[type]		...
	 */
	function getUserDefQuery ($qcArr) {
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function updateIcon()	{
		return '<input type="image" border="0" ' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/refresh_n.gif',  'width="14" height="14"') . ' title="Update" name="just_update">';
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getLabelCol()	{
		global $TCA;
		return $TCA[$this->table]['ctrl']['label'];
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$modSettings: ...
	 * @param	[type]		$enableList: ...
	 * @return	[type]		...
	 */
	function makeSelectorTable($modSettings,$enableList='table,fields,query,group,order,limit')	{
		$enableArr=explode(',',$enableList);
			// Make output
		$TDparams = ' class="bgColor5" nowrap';

		if (in_array('table',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableSelectATable'])	{
			$out='
			<tr>
				<td'.$TDparams.'><strong>Select a table:</strong></td>
				<td'.$TDparams.'>'.$this->mkTableSelect('SET[queryTable]',$this->table).'</td>
			</tr>';
		}
		if ($this->table)	{

				// Init fields:
			$this->setAndCleanUpExternalLists('queryFields',$modSettings['queryFields'],'uid,'.$this->getLabelCol());
			$this->setAndCleanUpExternalLists('queryGroup',$modSettings['queryGroup']);
			$this->setAndCleanUpExternalLists('queryOrder',$modSettings['queryOrder'].','.$modSettings['queryOrder2']);

				// Limit:
			$this->extFieldLists['queryLimit']=$modSettings['queryLimit'];
			if (!$this->extFieldLists['queryLimit'])	$this->extFieldLists['queryLimit']=100;
			$parts = t3lib_div::intExplode(',',$this->extFieldLists['queryLimit']);
			if ($parts[1])	{
				$this->limitBegin = $parts[0];
				$this->limitLength = $parts[1];
			} else {
				$this->limitLength = $this->extFieldLists['queryLimit'];
			}
			$this->extFieldLists['queryLimit'] = implode(',',array_slice($parts,0,2));

				// Insert Descending parts
			if ($this->extFieldLists['queryOrder'])	{
				$descParts = explode(',',$modSettings['queryOrderDesc'].','.$modSettings['queryOrder2Desc']);
				$orderParts = explode(',',$this->extFieldLists['queryOrder']);
				$reList=array();
				foreach ($orderParts as $kk => $vv) {
					$reList[]=$vv.($descParts[$kk]?' DESC':'');
				}
				$this->extFieldLists['queryOrder_SQL'] = implode(',',$reList);
			}

				// Query Generator:
			$this->procesData($modSettings['queryConfig'] ? unserialize($modSettings['queryConfig']) : '');
	//		debug($this->queryConfig);
			$this->queryConfig = $this->cleanUpQueryConfig($this->queryConfig);
	//		debug($this->queryConfig);
			$this->enableQueryParts = $modSettings['search_query_smallparts'];

			$codeArr=$this->getFormElements();
			$queryCode=$this->printCodeArray($codeArr);

			if (in_array('fields',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableSelectFields'])	{
				$out.='
				<tr>
					<td'.$TDparams.'><strong>Select fields:</strong></td>
					<td'.$TDparams.'>'.$this->mkFieldToInputSelect('SET[queryFields]',$this->extFieldLists['queryFields']).'</td>
				</tr>';
			}
			if (in_array('query',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableMakeQuery'])	{
				$out.='<tr>
					<td colspan="2"'.$TDparams.'><strong>Make Query:</strong></td>
				</tr>
				<tr>
					<td colspan="2">'.$queryCode.'</td>
				</tr>
				';
			}
			if (in_array('group',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableGroupBy'])	{
				$out.='<tr>
					<td'.$TDparams.'><strong>Group By:</strong></td>
					<td'.$TDparams.'>'.$this->mkTypeSelect('SET[queryGroup]',$this->extFieldLists['queryGroup'],'').'</td>
				</tr>';
			}
			if (in_array('order',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableOrderBy'])	{
				$orderByArr = explode(',',$this->extFieldLists['queryOrder']);
		//		debug($orderByArr);
				$orderBy='';
				$orderBy.=$this->mkTypeSelect('SET[queryOrder]',$orderByArr[0],'').
				'&nbsp;'.t3lib_BEfunc::getFuncCheck($GLOBALS['SOBE']->id,'SET[queryOrderDesc]',$modSettings['queryOrderDesc'],'','','id="checkQueryOrderDesc"').'&nbsp;<label for="checkQueryOrderDesc">Descending</label>';
				if ($orderByArr[0])	{
					$orderBy.= '<BR>'.$this->mkTypeSelect('SET[queryOrder2]',$orderByArr[1],'').
					'&nbsp;'.t3lib_BEfunc::getFuncCheck($GLOBALS['SOBE']->id,'SET[queryOrder2Desc]',$modSettings['queryOrder2Desc'],'','','id="checkQueryOrder2Desc"').'&nbsp;<label for="checkQueryOrder2Desc">Descending</label>';
				}
				$out.='<tr>
					<td'.$TDparams.'><strong>Order By:</strong></td>
					<td'.$TDparams.'>'.$orderBy.'</td>
				</tr>';
			}
			if (in_array('limit',$enableArr) && !$GLOBALS['BE_USER']->userTS['mod.']['dbint.']['disableLimit'])	{
				$limit = '<input type="Text" value="'.htmlspecialchars($this->extFieldLists['queryLimit']).'" name="SET[queryLimit]" id="queryLimit"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).'>'.$this->updateIcon();

				$prevLimit = ($this->limitBegin-$this->limitLength) < 0 ? 0 :
				$this->limitBegin-$this->limitLength;
				if ($this->limitBegin)	{
					$prevButton = '<input type="button" value="previous '.$this->limitLength.'" onclick=\'document.getElementById("queryLimit").value="'.$prevLimit.','.$this->limitLength.'";document.forms[0].submit();\'>';
				}
				if (!$this->limitLength)	{
					$this->limitLength = 100;
				}
				$nextLimit = $this->limitBegin + $this->limitLength;
				if ($nextLimit < 0) $nextLimit = 0;
				if ($nextLimit)	{
					$nextButton = '<input type="button" value="next '.$this->limitLength.'" onclick=\'document.getElementById("queryLimit").value="'.$nextLimit.','.$this->limitLength.'";document.forms[0].submit();\'>';
				}

				$numberButtons = '<input type="button" value="10" onclick=\'document.getElementById("queryLimit").value="10";document.forms[0].submit();\'>';
				$numberButtons .= '<input type="button" value="20" onclick=\'document.getElementById("queryLimit").value="20";document.forms[0].submit();\'>';
				$numberButtons .= '<input type="button" value="50" onclick=\'document.getElementById("queryLimit").value="50";document.forms[0].submit();\'>';
				$numberButtons .= '<input type="button" value="100" onclick=\'document.getElementById("queryLimit").value="100";document.forms[0].submit();\'>';
				$out.='<tr>
					<td'.$TDparams.'><strong>Limit:</strong></td>
					<td'.$TDparams.'>'.$limit.$prevButton.$nextButton.'&nbsp;'.$numberButtons.'</td>
				</tr>
				';
			}
		}
		$out='<table border="0" cellpadding="3" cellspacing="1" class="qg-make-query">'.$out.'</table>';
		$out.=$this->JSbottom($this->formName);
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$qString: ...
	 * @param	[type]		$depth: ...
	 * @param	[type]		$begin: ...
	 * @param	[type]		$perms_clause: ...
	 * @return	[type]		...
	 */
	function getTreeList($id, $depth, $begin=0, $perms_clause)	{
		$depth = intval($depth);
		$begin = intval($begin);
		$id = intval($id);
		if ($begin==0)	{
			$theList = $id;
		} else {
			$theList = '';
		}
		if ($id && $depth > 0)	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'pages',
				'pid='.$id.' '.t3lib_BEfunc::deleteClause('pages').' AND '.$perms_clause
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if ($begin <= 0)	{
					$theList .= ','.$row['uid'];
				}
				if ($depth > 1)	{
					$theList .= $this->getTreeList($row['uid'], $depth-1, $begin-1, $perms_clause);
				}
			}
		}
		return $theList;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$qString: ...
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getSelectQuery($qString = '', $fN = '')	{
		if (!$qString) $qString = $this->getQuery($this->queryConfig);
			$qString = '('.$qString.')';
		if (!$GLOBALS['BE_USER']->isAdmin() && $GLOBALS['TYPO3_CONF_VARS']['BE']['lockBeUserToDBmounts']) {
			$webMounts = $GLOBALS['BE_USER']->returnWebmounts();
			$perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			foreach($webMounts as $key => $val) {
				if ($webMountPageTree) {
					$webMountPageTreePrefix = ',';
					}
				$webMountPageTree .= $webMountPageTreePrefix.$this->getTreeList($val, 999, $begin = 0, $perms_clause);
			}
			if ($this->table == 'pages') {
				$qString .= ' AND uid IN ('.$webMountPageTree.')';
			} else {
				$qString .= ' AND pid IN ('.$webMountPageTree.')';
			}
		}
		$fieldlist = $this->extFieldLists['queryFields'] .
			',pid' .
			($GLOBALS['TCA'][$this->table]['ctrl']['delete'] ? ',' . $GLOBALS['TCA'][$this->table]['ctrl']['delete'] : '');
		if (!$GLOBALS['SOBE']->MOD_SETTINGS['show_deleted'])	{
			$qString .= t3lib_BEfunc::deleteClause($this->table);
		}
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
						$fieldlist,
						$this->table,
						$qString,
						trim($this->extFieldLists['queryGroup']),
						$this->extFieldLists['queryOrder'] ? trim($this->extFieldLists['queryOrder_SQL']) : '',
						$this->extFieldLists['queryLimit']
					);
		return $query;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$formname: ...
	 * @return	[type]		...
	 */
	function JSbottom($formname)	{
		if ($this->extJSCODE)	{
			$out.='
			<script language="javascript" type="text/javascript" src="'.$GLOBALS['BACK_PATH'].'../t3lib/jsfunc.evalfield.js"></script>
			<script language="javascript" type="text/javascript" src="'.$GLOBALS['BACK_PATH'].'jsfunc.tbe_editor.js"></script>
			<script language="javascript" type="text/javascript">
				TBE_EDITOR.formname = "'.$formname.'";
				TBE_EDITOR.formnameUENC = "'.rawurlencode($formname).'";
				TBE_EDITOR.backend_interface = "'.$GLOBALS['BE_USER']->uc['interfaceSetup'].'";
				'.$this->extJSCODE.'
			</script>';
			return $out;
		}
	}

	/**
	 * Sets the current name of the input form.
	 *
	 * @param	string		$formName: The name of the form.
	 * @return	void
	 */
	public function setFormName($formName) {
		$this->formName = trim($formName);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_querygenerator.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_querygenerator.php']);
}

?>
