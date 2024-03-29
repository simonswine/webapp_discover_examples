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
* Contains class with time tracking functions
*
* $Id: class.t3lib_timetrack.php,v 1.11 2004/09/13 22:57:19 typo3 Exp $
* Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
* XHTML compliant
*
* @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   88: class t3lib_timeTrack
 *
 *              SECTION: Logging parsing times in the scripts
 *  144:     function start()
 *  157:     function push($tslabel, $value='')
 *  182:     function pull($content='')
 *  200:     function setTSlogMessage($content,$num=0)
 *  214:     function setTSselectQuery($query,$msg)
 *  227:     function incStackPointer()
 *  238:     function decStackPointer()
 *  248:     function mtime()
 *  258:     function convertMicrotime($microtime)
 *
 *              SECTION: Printing the parsing time information (for Admin Panel)
 *  291:     function printTSlog()
 *  436:     function fixContent(&$arr, $content, $depthData='', $first=0, $vKey='')
 *  500:     function fixCLen($c,$v)
 *  516:     function fw($str)
 *  530:     function createHierarchyArray(&$arr,$pointer,$uniqueId)
 *  550:     function debug_typo3PrintError($header,$text,$js,$baseUrl='')
 *
 * TOTAL FUNCTIONS: 15
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */











/**
 * Frontend Timetracking functions
 *
 * Is used to register how much time is used with operations in TypoScript
 * Used by index_ts
 *
 * @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 * @see t3lib_tsfeBeUserAuth, tslib_fe, tslib_cObj, TSpagegen
 */
class t3lib_timeTrack {
	var $starttime = 0;             // Is loaded with the millisecond time when this object is created

	var $LR = 1;                    // Log Rendering flag. If set, ->push() and ->pull() is called from the cObj->cObjGetSingle(). This determines whether or not the TypoScript parsing activity is logged. But it also slows down the rendering
	var $printConf=array(
		'showParentKeys' => 1,
		'contentLength' => 10000,       // Determines max lenght of displayed content before it gets cropped.
		'contentLength_FILE' => 400,    // Determines max lenght of displayed content FROM FILE cObjects before it gets cropped. Reason is that most FILE cObjects are huge and often used as template-code.
		'flag_tree' => 1,
		'flag_messages' => 1,
		'flag_queries' => 0,
		'flag_content' => 0,
		'allTime' => 0,
		'keyLgd' => 40,
		'factor' => 10,
		'col' => '#D9D5C9'
	);

	var $wrapError =array(
		0 => array('',''),
		1 => array('<b>','</b>'),
		2 => array('<b><font color="#ff6600">','</font></b>'),
		3 => array('<b><font color="#ff0000">','</font></b>')
	);
	var $wrapIcon =array(
		0 => '',
		1 => '<img src="t3lib/gfx/icon_note.gif" width="18" height="16" align="absmiddle" alt="" />',
		2 => '<img src="t3lib/gfx/icon_warning.gif" width="18" height="16" align="absmiddle" alt="" />',
		3 => '<img src="t3lib/gfx/icon_fatalerror.gif" width="18" height="16" align="absmiddle" alt="" />'
	);

	var $uniqueCounter=0;
	var $tsStack = array(array());
	var $tsStackLevel = 0;
	var $tsStackLevelMax=array();
	var $tsStackLog = array();
	var $tsStackPointer=0;
	var $currentHashPointer=array();






	/*******************************************
	*
	* Logging parsing times in the scripts
	*
	*******************************************/

	/**
	 * Constructor
	 * Sets the starting time
	 *
	 * @return	void
	 */
	function start()    {
		$this->starttime=0;
		$this->starttime=$this->mtime();
	}

	/**
	 * Pushes an element to the TypoScript tracking array
	 *
	 * @param	string		Label string for the entry, eg. TypoScript property name
	 * @param	string		Additional value(?)
	 * @return	void
	 * @see tslib_cObj::cObjGetSingle(), pull()
	 */
	function push($tslabel, $value='')  {
		array_push($this->tsStack[$this->tsStackPointer], $tslabel);
		array_push($this->currentHashPointer, 'timetracker_'.$this->uniqueCounter++);

		$this->tsStackLevel++;
		$this->tsStackLevelMax[] = $this->tsStackLevel;

			// setTSlog
		$k = end($this->currentHashPointer);
		$this->tsStackLog[$k] = array(
			'level' => $this->tsStackLevel,
			'tsStack' => $this->tsStack,
			'value' => $value,
			'starttime' => microtime(),
			'stackPointer' => $this->tsStackPointer
		);
	}

	/**
	 * Pulls an element from the TypoScript tracking array
	 *
	 * @param	string		The content string generated within the push/pull part.
	 * @return	void
	 * @see tslib_cObj::cObjGetSingle(), push()
	 */
	function pull($content='')  {
		$k = end($this->currentHashPointer);
		$this->tsStackLog[$k]['endtime'] =  microtime();
		$this->tsStackLog[$k]['content'] = $content;

		$this->tsStackLevel--;
		array_pop($this->tsStack[$this->tsStackPointer]);
		array_pop($this->currentHashPointer);
	}

	/**
	 * Logs the TypoScript entry
	 *
	 * @param	string		The message string
	 * @param	integer		Message type: 0: information, 1: message, 2: warning, 3: error
	 * @return	void
	 * @see tslib_cObj::CONTENT()
	 */
	function setTSlogMessage($content,$num=0)   {
		end($this->currentHashPointer);
		$k = current($this->currentHashPointer);

		$this->tsStackLog[$k]['message'][] = $this->wrapIcon[$num].$this->wrapError[$num][0].htmlspecialchars($content).$this->wrapError[$num][1];
	}

	/**
	 * Set TSselectQuery - for messages in TypoScript debugger.
	 *
	 * @param	string		Query string
	 * @param	string		Message/Label to attach
	 * @return	void
	 */
	function setTSselectQuery($query,$msg)  {
		end($this->currentHashPointer);
		$k = current($this->currentHashPointer);

		$this->tsStackLog[$k]['selectQuery'][] = array('query'=>$query,'msg'=>$msg);
	}

	/**
	 * Increases the stack pointer
	 *
	 * @return	void
	 * @see decStackPointer(), TSpagegen::renderContent(), tslib_cObj::cObjGetSingle()
	 */
	function incStackPointer()  {
		$this->tsStackPointer++;
		$this->tsStack[$this->tsStackPointer]=array();
	}

	/**
	 * Decreases the stack pointer
	 *
	 * @return	void
	 * @see incStackPointer(), TSpagegen::renderContent(), tslib_cObj::cObjGetSingle()
	 */
	function decStackPointer()  {
		unset($this->tsStack[$this->tsStackPointer]);
		$this->tsStackPointer--;
	}

	/**
	 * Returns the current time in milliseconds
	 *
	 * @return	integer
	 */
	function mtime()    {
		return $this->convertMicrotime(microtime())-$this->starttime;
	}

	/**
	 * Returns microtime input to milliseconds
	 *
	 * @param	string		PHP microtime string
	 * @return	integer
	 */
	function convertMicrotime($microtime)   {
		$parts = explode(' ',$microtime);
		return round(($parts[0]+$parts[1])*1000);
	}

















	/*******************************************
	*
	* Printing the parsing time information (for Admin Panel)
	*
	*******************************************/

	/**
	 * Print TypoScript parsing log
	 *
	 * @return	string		HTML table with the information about parsing times.
	 * @see t3lib_tsfeBeUserAuth::extGetCategory_tsdebug()
	 */
	function printTSlog()   {
			// Calculate times and keys for the tsStackLog
		reset($this->tsStackLog);
		$preEndtime=0;
		while(list($uniqueId,$data)=each($this->tsStackLog))    {
			$this->tsStackLog[$uniqueId]['endtime'] = $this->convertMicrotime($this->tsStackLog[$uniqueId]['endtime'])-$this->starttime;
			$this->tsStackLog[$uniqueId]['starttime'] = $this->convertMicrotime($this->tsStackLog[$uniqueId]['starttime'])-$this->starttime;
			$this->tsStackLog[$uniqueId]['deltatime'] = $this->tsStackLog[$uniqueId]['endtime']-$this->tsStackLog[$uniqueId]['starttime'];
			$this->tsStackLog[$uniqueId]['key'] = implode(end($data['tsStack']),$this->tsStackLog[$uniqueId]['stackPointer']?'.':'/');
			$preEndtime = $this->tsStackLog[$uniqueId]['endtime'];
		}

			// Create hierarchical array of keys pointing to the stack
		$arr = array();
		reset($this->tsStackLog);
		while(list($uniqueId,$data)=each($this->tsStackLog))    {
			$this->createHierarchyArray($arr,$data['level'], $uniqueId);
		}
			// Parsing the registeret content and create icon-html for the tree
		$this->tsStackLog[$arr['0.'][0]]['content'] = $this->fixContent($arr['0.']['0.'], $this->tsStackLog[$arr['0.'][0]]['content'], '', 0, $arr['0.'][0]);

			// Displaying the tree:
		reset($this->tsStackLog);
		$out='<tr>
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('TypoScript Key').'</b></td>
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Value').'</b></td>';
		if ($this->printConf['allTime'])    {
			$out.='
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Time').'</b></td>
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Own').'</b></td>
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Sub').'</b></td>
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Total').'</b></td>';
		} else {
			$out.='
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Own').'</b></td>';
		}

		$out.='
			<td bgcolor="#ABBBB4" align="center"><b>'.$this->fw('Details').'</b></td>
			</tr>';



		$flag_tree=$this->printConf['flag_tree'];
		$flag_messages=$this->printConf['flag_messages'];
		$flag_content=$this->printConf['flag_content'];
		$flag_queries=$this->printConf['flag_queries'];
		$keyLgd=$this->printConf['keyLgd'];
		$factor=$this->printConf['factor'];
		$col=$this->printConf['col'];

		$c=0;
		while(list($uniqueId,$data)=each($this->tsStackLog))    {
			$bgColor = ' bgcolor="'.($c%2 ? t3lib_div::modifyHTMLColor($col,$factor,$factor,$factor) : $col).'"';
			$item='';
			if (!$c)    {   // If first...
				$data['icons']='';
				$data['key']= 'Script Start';
				$data['value'] = '';
			}


				// key label:
			$keyLabel='';
			if (!$flag_tree && $data['stackPointer'])   {
				$temp=array();
				reset($data['tsStack']);
				while(list($k,$v)=each($data['tsStack']))   {
					$temp[]=t3lib_div::fixed_lgd_pre(implode($v,$k?'.':'/'),$keyLgd);
				}
				array_pop($temp);
				$temp = array_reverse($temp);
				array_pop($temp);
				if (count($temp))   {
					$keyLabel='<br /><font color="#999999">'.implode($temp,'<br />').'</font>';
				}
			}
			$theLabel = $flag_tree ? end(t3lib_div::trimExplode('.',$data['key'],1)) : $data['key'];
			$theLabel = t3lib_div::fixed_lgd_pre($theLabel, $keyLgd);
			$theLabel = $data['stackPointer'] ? '<font color="maroon">'.$theLabel.'</font>' : $theLabel;
			$keyLabel=$theLabel.$keyLabel;
			$item.='<td valign="top" nowrap="nowrap"'.$bgColor.'>'.($flag_tree?$data['icons']:'').$this->fw($keyLabel).'</td>';

				// key value:
			$keyValue=$data['value'];
			$item.='<td valign="top" nowrap="nowrap"'.$bgColor.'>'.$this->fw(htmlspecialchars($keyValue)).'</td>';

			if ($this->printConf['allTime'])    {
					// deltatime:
				$item.='<td valign="top" align="right" nowrap="nowrap"'.$bgColor.'>'.$this->fw($data['starttime']).'</td>';
				$item.='<td valign="top" align="right" nowrap="nowrap"'.$bgColor.'>'.$this->fw($data['owntime']).'</td>';
				$item.='<td valign="top" align="right" nowrap="nowrap"'.$bgColor.'>'.$this->fw($data['subtime'] ? '+'.$data['subtime'] : '').'</td>';
				$item.='<td valign="top" align="right" nowrap="nowrap"'.$bgColor.'>'.$this->fw($data['subtime'] ? '='.$data['deltatime'] : '').'</td>';
			} else {
					// deltatime:
				$item.='<td valign="top" align="right" nowrap="nowrap"'.$bgColor.'>'.$this->fw($data['owntime']).'</td>';
			}


				// messages:
			$msgArr=array();
			$msg='';
			if ($flag_messages && is_array($data['message']))   {
				reset($data['message']);
				while(list(,$v)=each($data['message'])) {
					$msgArr[]=nl2br($v);
				}
			}
			if ($flag_queries && is_array($data['selectQuery']))    {
				reset($data['selectQuery']);
				while(list(,$v)=each($data['selectQuery'])) {
					$res = $GLOBALS['TYPO3_DB']->sql_query('EXPLAIN '.$v['query']);
					$v['mysql_error'] = $GLOBALS['TYPO3_DB']->sql_error();
					if (!$GLOBALS['TYPO3_DB']->sql_error()) {
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))   {
							$v['explain'][]=$row;
						}
					}
					$msgArr[]=t3lib_div::view_array($v);
				}
			}
			if ($flag_content && strcmp($data['content'],''))   {
				$msgArr[]='<font color="#000066">'.nl2br($data['content']).'</font>';
			}
			if (count($msgArr)) {
				$msg=implode($msgArr,'<hr />');
			}
			$item.='<td valign="top"'.$bgColor.'>'.$this->fw($msg).'</td>';
			$out.='<tr>'.$item.'</tr>';
			$c++;
		}
		$out='<table border="0" cellpadding="0" cellspacing="0">'.$out.'</table>';
		return $out;
	}

	/**
	 * Recursively generates the content to display
	 *
	 * @param	array		Array which is modified with content. Reference
	 * @param	string		Current content string for the level
	 * @param	string		Prefixed icons for new PM icons
	 * @param	boolean		Set this for the first call from outside.
	 * @param	string		Seems to be the previous tsStackLog key
	 * @return	string		Returns the $content string generated/modified. Also the $arr array is modified!
	 */
	function fixContent(&$arr, $content, $depthData='', $first=0, $vKey='') {
		$ac=0;
		$c=0;
			// First, find number of entries
		reset($arr);
		while(list($k,$v)=each($arr))   {
			if (t3lib_div::testInt($k)) {
				$ac++;
			}
		}
			// Traverse through entries
		$subtime=0;
		reset($arr);
		while(list($k,$v)=each($arr))   {
			if (t3lib_div::testInt($k)) {
				$c++;

				$deeper = is_array($arr[$k.'.']) ? 1 : 0;
				$PM = 'join';
				$LN = ($ac==$c)?'blank':'line';
				$BTM = ($ac==$c)?'bottom':'';
				$PM = is_array($arr[$k.'.']) ? ($deeper ? 'minus':'plus') : 'join';
				$this->tsStackLog[$v]['icons']=$depthData.($first?'':'<img src="t3lib/gfx/ol/'.$PM.$BTM.'.gif" width="18" height="16" align="top" border="0" alt="" />');

				if (strlen($this->tsStackLog[$v]['content']))   {
					$content = str_replace($this->tsStackLog[$v]['content'],$v, $content);
				}
				if (is_array($arr[$k.'.'])) {
					$this->tsStackLog[$v]['content'] = $this->fixContent($arr[$k.'.'], $this->tsStackLog[$v]['content'], $depthData.($first?'':'<img src="t3lib/gfx/ol/'.$LN.'.gif" width="18" height="16" align="top" border="0" alt="" />'), 0, $v);
				} else {
					$this->tsStackLog[$v]['content'] = $this->fixCLen($this->tsStackLog[$v]['content'], $this->tsStackLog[$v]['value']);
					$this->tsStackLog[$v]['subtime']='';
					$this->tsStackLog[$v]['owntime']=$this->tsStackLog[$v]['deltatime'];
				}
				$subtime+=$this->tsStackLog[$v]['deltatime'];
			}
		}
			// Set content with special chars
		if (isset($this->tsStackLog[$vKey]))    {
			$this->tsStackLog[$vKey]['subtime']=$subtime;
			$this->tsStackLog[$vKey]['owntime']=$this->tsStackLog[$vKey]['deltatime']-$subtime;
		}
		$content=$this->fixCLen($content, $this->tsStackLog[$vKey]['value']);

			// Traverse array again, this time substitute the unique hash with the red key
		reset($arr);
		while(list($k,$v)=each($arr))   {
			if (t3lib_div::testInt($k)) {
				if (strlen($this->tsStackLog[$v]['content']))   {
					$content = str_replace($v, '<font color="red"><b>['.$this->tsStackLog[$v]['key'].']</b></font>', $content);
				}
			}
		}
			// return the content
		return $content;
	}

	/**
	 * Wraps the input content string in green colored font-tags IF the length o fthe input string exceeds $this->printConf['contentLength'] (or $this->printConf['contentLength_FILE'] if $v == "FILE"
	 *
	 * @param	string		The content string
	 * @param	string		Command: If "FILE" then $this->printConf['contentLength_FILE'] is used for content length comparison, otherwise $this->printConf['contentLength']
	 * @return	string
	 */
	function fixCLen($c,$v) {
		$len = $v=='FILE'?$this->printConf['contentLength_FILE']:$this->printConf['contentLength'];
		if (strlen($c)>$len)    {
			$c='<font color="green">'.htmlspecialchars(t3lib_div::fixed_lgd($c,$len)).'</font>';
		} else {
			$c=htmlspecialchars($c);
		}
		return $c;
	}

	/**
	 * Wraps input string in a <font> tag with verdana, black and size 1
	 *
	 * @param	string		The string to be wrapped
	 * @return	string
	 */
	function fw($str)   {
		return '<font face="verdana" color="black" size="1" style="color:black;">'.$str.'&nbsp;</font>';
	}

	/**
	 * Helper function for internal data manipulation
	 *
	 * @param	array		Array (passed by reference) and modified
	 * @param	integer		Pointer value
	 * @param	string		Unique ID string
	 * @return	void
	 * @access private
	 * @see printTSlog()
	 */
	function createHierarchyArray(&$arr,$pointer,$uniqueId) {
		if (!is_array($arr))    $arr=array();
		if ($pointer>0) {
			end($arr);
			$k=key($arr);
			$this->createHierarchyArray($arr[intval($k).'.'],$pointer-1,$uniqueId);
		} else {
			$arr[] = $uniqueId;
		}
	}

	/**
	 * This prints out a TYPO3 error message.
	 *
	 * @param	string		Header string
	 * @param	string		Message string
	 * @param	boolean		If set, then this will produce a alert() line for inclusion in JavaScript.
	 * @param	string		URL for the <base> tag (if you want it)
	 * @return	string
	 */
	function debug_typo3PrintError($header,$text,$js,$baseUrl='')   {
		if ($js)    {
			echo"alert('".t3lib_div::slashJS($header."\n".$text)."');";
		} else {
			echo '
				<html>
					<head>
						'.($baseUrl ? '<base href="'.htmlspecialchars($baseUrl).'" />' : '').'
						<title>Error!</title>
					</head>
					<body bgcolor="white">
						<div align="center">
							<table border="0" cellspacing="0" cellpadding="0" width="333" bgcolor="#ffffff">
								<tr>
									<td><img src="t3lib/gfx/typo3logo.gif" width="333" height="43" vspace="10" border="0" alt="" /></td>
								</tr>
								<tr>
									<td bgcolor="black">
										<table width="100%" border="0" cellspacing="1" cellpadding="10">
											<tr>
												<td bgcolor="#F4F0E8">
													<font face="verdana,arial,helvetica" size="2">';
			echo '<b><center><font size="+1">'.$header.'</font></center></b><br />'.$text;
			echo '                                  </font>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</div>
					</body>
				</html>';
		}
	}
}
?>