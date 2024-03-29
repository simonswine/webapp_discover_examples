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
 * Contains class for TYPO3 clipboard for records and files
 *
 * $Id: class.t3lib_clipboard.php,v 1.12 2004/09/13 22:57:17 typo3 Exp $
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   90: class t3lib_clipboard
 *  126:     function initializeClipboard()
 *  155:     function lockToNormal()
 *  172:     function setCmd($cmd)
 *  219:     function setCurrentPad($padIdent)
 *  234:     function endClipboard()
 *  247:     function cleanUpCBC($CBarr,$table,$removeDeselected=0)
 *  265:     function isElements()
 *  274:     function printClipboard()
 *  363:     function printContentFromTab($pad)
 *  437:     function padTitleWrap($str,$pad)
 *  454:     function linkItemText($str,$rec,$table='')
 *  479:     function isSelected($table,$uid)
 *  493:     function getSelectedRecord($table='',$uid='')
 *  516:     function selUrlDB($table,$uid,$copy=0,$deselect=0,$baseArray=array())
 *  532:     function selUrlFile($path,$copy=0,$deselect=0,$baseArray=array())
 *  549:     function pasteUrl($table,$uid,$setRedirect=1)
 *  566:     function deleteUrl($setRedirect=1,$file=0)
 *  583:     function editUrl()
 *  604:     function removeUrl($table,$uid)
 *  614:     function cleanCurrent()
 *  641:     function elFromTable($matchTable='',$pad='')
 *  673:     function confirmMsg($table,$rec,$type,$clElements)
 *  716:     function removeElement($el)
 *  728:     function saveClipboard()
 *  738:     function currentMode()
 *  749:     function clLabel($key,$Akey='labels')
 *
 *              SECTION: FOR USE IN tce_db.php:
 *  790:     function makePasteCmdArray($ref,$CMD)
 *  819:     function makeDeleteCmdArray($CMD)
 *
 *              SECTION: FOR USE IN tce_file.php:
 *  862:     function makePasteCmdArray_file($ref,$FILE)
 *  884:     function makeDeleteCmdArray_file($FILE)
 *
 * TOTAL FUNCTIONS: 30
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * TYPO3 clipboard for records and files
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_clipboard {
	var $numberTabs = 3;

	/**
	 * Clipboard data kept here
	 *
	 * Keys:
	 * 			'normal'
	 * 			'tab_[x]' where x is >=1 and denotes the pad-number
	 * 				\	'mode'	:	'copy' means copy-mode, default = moving ('cut')
	 * 				\	'el'	:	Array of elements:
	 * 								DB: keys = '[tablename]|[uid]'	eg. 'tt_content:123'
	 * 								DB: values = 1 (basically insignificant)
	 * 								FILE: keys = '_FILE|[shortmd5 of path]'	eg. '_FILE|9ebc7e5c74'
	 * 								FILE: values = The full filepath, eg. '/www/htdocs/typo3/32/dummy/fileadmin/sem1_3_examples/alternative_index.php' or 'C:/www/htdocs/typo3/32/dummy/fileadmin/sem1_3_examples/alternative_index.php'
	 *
	 * 			'current' pointer to current tab (among the above...)
	 * 			'_setThumb'	boolean: If set, file thumbnails are shown.
	 *
	 * 		The virtual tablename '_FILE' will always indicate files/folders. When checking for elements from eg. 'all tables' (by using an empty string) '_FILE' entries are excluded (so in effect only DB elements are counted)
	 *
	 */
	var $clipData=array();

	var $changed=0;
	var $current='';
	var $backPath='';
	var $lockToNormal=0;
	var $fileMode=0;		// If set, clipboard is displaying files.


	/**
	 * Initialize the clipboard from the be_user session
	 *
	 * @return	void
	 */
	function initializeClipboard()	{
		global $BE_USER;

			// Get data
		$clipData = $BE_USER->getModuleData('clipboard',$BE_USER->getTSConfigVal('options.saveClipboard')?'':'ses');

			// NumberTabs
		$clNP = $BE_USER->getTSConfigVal('options.clipboardNumberPads');
		if (t3lib_div::testInt($clNP) && $clNP>=0)	{
			$this->numberTabs = t3lib_div::intInRange($clNP,0,20);
		}

			// Resets/reinstates the clipboard pads
		$this->clipData['normal'] = is_array($clipData['normal']) ? $clipData['normal'] : array();
		for ($a=1;$a<=$this->numberTabs;$a++)	{
			$this->clipData['tab_'.$a] = is_array($clipData['tab_'.$a]) ? $clipData['tab_'.$a] : array();
		}

			// Setting the current pad pointer ($this->current) and _setThumb (which determines whether or not do show file thumbnails)
		$this->clipData['current'] = $this->current = isset($this->clipData[$clipData['current']]) ? $clipData['current'] : 'normal';
		$this->clipData['_setThumb'] = $clipData['_setThumb'];
	}

	/**
	 * Call this method after initialization if you want to lock the clipboard to operate on the normal pad only. Trying to switch pad through ->setCmd will not work
	 * This is used by the clickmenu since it only allows operation on single elements at a time (that is the "normal" pad)
	 *
	 * @return	void
	 */
	function lockToNormal()	{
		$this->lockToNormal=1;
		$this->current='normal';
	}

	/**
	 * The array $cmd may hold various keys which notes some action to take.
	 * Normally perform only one action at a time.
	 * In scripts like db_list.php / file_list.php the GET-var CB is used to control the clipboard.
	 *
	 * 		Selecting / Deselecting elements
	 * 		Array $cmd['el'] has keys = element-ident, value = element value (see description of clipData array in header)
	 * 		Selecting elements for 'copy' should be done by simultaneously setting setCopyMode.
	 *
	 * @param	array		Array of actions, see function description
	 * @return	void
	 */
	function setCmd($cmd)	{
		if (is_array($cmd['el']))	{
			reset($cmd['el']);
			while(list($k,$v)=each($cmd['el']))	{
				if ($this->current=='normal')	{
					unset($this->clipData['normal']);
				}
				if ($v)	{
					$this->clipData[$this->current]['el'][$k]=$v;
				} else {
					$this->removeElement($k);
				}
				$this->changed=1;
			}
		}
			// Change clipboard pad (if not locked to normal)
		if ($cmd['setP'])	{
			$this->setCurrentPad($cmd['setP']);
		}
			// Remove element	(value = item ident: DB; '[tablename]|[uid]'    FILE: '_FILE|[shortmd5 hash of path]'
		if ($cmd['remove'])	{
			$this->removeElement($cmd['remove']);
			$this->changed=1;
		}
			// Remove all on current pad (value = pad-ident)
		if ($cmd['removeAll'])	{
			$this->clipData[$cmd['removeAll']]=array();
			$this->changed=1;
		}
			// Set copy mode of the tab
		if (isset($cmd['setCopyMode']))	{
			$this->clipData[$this->current]['mode']=$this->isElements()?($cmd['setCopyMode']?'copy':''):'';
			$this->changed=1;
		}
			// Toggle thumbnail display for files on/off
		if (isset($cmd['setThumb']))	{
			$this->clipData['_setThumb']=$cmd['setThumb'];
			$this->changed=1;
		}
	}

	/**
	 * Setting the current pad on clipboard
	 *
	 * @param	string		Key in the array $this->clipData
	 * @return	void
	 */
	function setCurrentPad($padIdent)	{
			// Change clipboard pad (if not locked to normal)
		if (!$this->lockToNormal && $this->current!=$padIdent)	{
			if (isset($this->clipData[$padIdent]))	$this->clipData['current'] = $this->current = $padIdent;
			if ($this->current!='normal' || !$this->isElements())	$this->clipData[$this->current]['mode']='';	// Setting mode to default (move) if no items on it or if not 'normal'
			$this->changed=1;
		}
	}

	/**
	 * Call this after initialization and setCmd in order to save the clipboard to the user session.
	 * The function will check if the internal flag ->changed has been set and if so, save the clipboard. Else not.
	 *
	 * @return	void
	 */
	function endClipboard()	{
		if ($this->changed)	$this->saveClipboard();
		$this->changed=0;
	}

	/**
	 * Cleans up an incoming element array $CBarr (Array selecting/deselecting elements)
	 *
	 * @param	array		Element array from outside ("key" => "selected/deselected")
	 * @param	string		$table is the 'table which is allowed'. Must be set.
	 * @param	boolean		$removeDeselected can be set in order to remove entries which are marked for deselection.
	 * @return	array		Processed input $CBarr
	 */
	function cleanUpCBC($CBarr,$table,$removeDeselected=0)	{
		if (is_array($CBarr))	{
			reset($CBarr);
			while(list($k,$v)=each($CBarr))	{
				$p=explode('|',$k);
				if ((string)$p[0]!=(string)$table || ($removeDeselected && !$v))	{
					unset($CBarr[$k]);
				}
			}
		}
		return $CBarr;
	}

	/**
	 * Reports if the current pad has elements (does not check file/DB type OR if file/DBrecord exists or not. Only counting array)
	 *
	 * @return	boolean		True if elements exist.
	 */
	function isElements()	{
		return is_array($this->clipData[$this->current]['el']) && count($this->clipData[$this->current]['el']);
	}

	/**
	 * Prints the clipboard
	 *
	 * @return	string		HTML output
	 */
	function printClipboard()	{
		global $TBE_TEMPLATE,$LANG;

		$out=array();
		$elCount = count($this->elFromTable($this->fileMode?'_FILE':''));

			// Upper header
		$out[]='
			<tr class="bgColor2">
				<td colspan="3" nowrap="nowrap" align="center"><span class="uppercase"><strong>'.$this->clLabel('clipboard','buttons').'</strong></span></td>
			</tr>';

			// Button/menu header:
		$thumb_url = t3lib_div::linkThisScript(array('CB'=>array('setThumb'=>$this->clipData['_setThumb']?0:1)));
		$copymode_url = t3lib_div::linkThisScript(array('CB'=>array('setCopyMode'=>($this->currentMode()=='copy'?'':'copy'))));
		$rmall_url = t3lib_div::linkThisScript(array('CB'=>array('removeAll'=>$this->current)));

			// Selector menu + clear button
		$opt=array();
		$opt[]='<option value="" selected="selected">'.$this->clLabel('menu','rm').'</option>';
		if (!$this->fileMode && $elCount)	$opt[]='<option value="'.htmlspecialchars("document.location='".$this->editUrl()."&returnUrl='+top.rawurlencode(document.location);").'">'.$this->clLabel('edit','rm').'</option>';
		if ($elCount)	$opt[]='<option value="'.htmlspecialchars("
			if(confirm(".$GLOBALS['LANG']->JScharCode(sprintf($LANG->sL('LLL:EXT:lang/locallang_core.php:mess.deleteClip'),$elCount)).")){
				document.location='".$this->deleteUrl(0,$this->fileMode?1:0)."&redirect='+top.rawurlencode(document.location);
			}
			").'">'.$this->clLabel('delete','rm').'</option>';
		$selector_menu = '<select name="_clipMenu" onchange="eval(this.options[this.selectedIndex].value);this.selectedIndex=0;">'.implode('',$opt).'</select>';

		$out[]='
			<tr class="typo3-clipboard-head">
				<td>'.
				'<a href="'.htmlspecialchars($thumb_url).'#clip_head">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/thumb_'.($this->clipData['_setThumb']?'s':'n').'.gif','width="21" height="16"').' vspace="2" border="0" title="'.$this->clLabel('thumbmode_clip').'" alt="" />'.
					'</a>'.
				'<a href="'.htmlspecialchars($copymode_url).'#clip_head">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/copymode_'.($this->currentMode()=='copy'?'s':'n').'.gif','width="21" height="16"').' vspace="2" border="0" title="'.$this->clLabel('copymode').'" alt="" />'.
					'</a>'.
				'</td>
				<td width="95%">'.$selector_menu.'</td>
				<td><a href="'.htmlspecialchars($rmall_url).'#clip_head">'.$LANG->sL('LLL:EXT:lang/locallang_core.php:buttons.clear',1).'</a></td>
			</tr>';


			// Print header and content for the NORMAL tab:
		$out[]='
			<tr class="bgColor5">
				<td colspan="3"><a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('CB'=>array('setP'=>'normal')))).'#clip_head">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($this->current=='normal'?'minus':'plus').'bullet.gif','width="18" height="16"').' border="0" align="top" alt="" />'.
					$this->padTitleWrap('Normal','normal').
					'</a></td>
			</tr>';
		if ($this->current=='normal')	$out=array_merge($out,$this->printContentFromTab('normal'));

			// Print header and content for the NUMERIC tabs:
		for ($a=1;$a<=$this->numberTabs;$a++)	{
			$out[]='
				<tr class="bgColor5">
					<td colspan="3"><a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('CB'=>array('setP'=>'tab_'.$a)))).'#clip_head">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($this->current=='tab_'.$a?'minus':'plus').'bullet.gif','width="18" height="16"').' border="0" align="top" alt="" />'.
						$this->padTitleWrap($this->clLabel('cliptabs').$a,'tab_'.$a).
						'</a></td>
				</tr>';
			if ($this->current=='tab_'.$a)	$out=array_merge($out,$this->printContentFromTab('tab_'.$a));
		}

			// Wrap accumulated rows in a table:
		$output = '<a name="clip_head"></a>

			<!--
				TYPO3 Clipboard:
			-->
			<table cellpadding="0" cellspacing="1" border="0" width="290" id="typo3-clipboard">
				'.implode('',$out).'
			</table>';

			// Wrap in form tag:
		$output = '<form action="">'.$output.'</form>';

			// Return the accumulated content:
		return $output;
	}

	/**
	 * Print the content on a pad. Called from ->printClipboard()
	 *
	 * @param	string		Pad reference
	 * @return	array		Array with table rows for the clipboard.
	 * @access private
	 */
	function printContentFromTab($pad)	{
		global $TBE_TEMPLATE;

		$lines=array();
		if (is_array($this->clipData[$pad]['el']))	{
			reset($this->clipData[$pad]['el']);
			while(list($k,$v)=each($this->clipData[$pad]['el']))	{
				if ($v)	{
					list($table,$uid) = explode('|',$k);
					$bgColClass = ($table=='_FILE'&&$this->fileMode)||($table!='_FILE'&&!$this->fileMode) ? 'bgColor4-20' : 'bgColor4';

					if ($table=='_FILE')	{	// Rendering files/directories on the clipboard:
						if (@file_exists($v) && t3lib_div::isAllowedAbsPath($v))	{
							$fI = pathinfo($v);
							$icon = is_dir($v) ? 'folder.gif' : t3lib_BEfunc::getFileIcon(strtolower($fI['extension']));
							$size = ' ('.t3lib_div::formatSize(filesize($v)).'bytes)';
							$icon = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/fileicons/'.$icon,'width="18" height="16"').' border="0" hspace="20" class="absmiddle" title="'.htmlspecialchars($fI['basename'].$size).'" alt="" />';
							$thumb = $this->clipData['_setThumb'] ? (t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],$fI['extension']) ? t3lib_BEfunc::getThumbNail($this->backPath.'thumbs.php',$v,' vspace="4"') : '') :'';

							$lines[]='
								<tr>
									<td class="'.$bgColClass.'">'.$icon.'</td>
									<td class="'.$bgColClass.'" nowrap="nowrap" width="95%">&nbsp;'.$this->linkItemText(htmlspecialchars(t3lib_div::fixed_lgd_cs(basename($v),$GLOBALS['BE_USER']->uc['titleLen'])),$v).
										($pad=='normal'?(' <strong>('.($this->clipData['normal']['mode']=='copy'?$this->clLabel('copy','cm'):$this->clLabel('cut','cm')).')</strong>'):'').'&nbsp;'.($thumb?'<br />'.$thumb:'').'</td>
									<td class="'.$bgColClass.'" align="center">'.
									'<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$v.'\', \'\'); return false;').'"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' hspace="2" border="0" title="'.$this->clLabel('info','cm').'" alt="" /></a>'.
									'<a href="'.htmlspecialchars($this->removeUrl('_FILE',t3lib_div::shortmd5($v))).'#clip_head"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/close_12h.gif','width="11" height="12"').' border="0" title="'.$this->clLabel('removeItem').'" alt="" /></a>'.
									'</td>
								</tr>';
						} else {
								// If the file did not exist (or is illegal) then it is removed from the clipboard immediately:
							unset($this->clipData[$pad]['el'][$k]);
							$this->changed=1;
						}
					} else {	// Rendering records:
						$rec=t3lib_BEfunc::getRecord($table,$uid);
						if (is_array($rec))	{
							$lines[]='
								<tr>
									<td class="'.$bgColClass.'">'.$this->linkItemText(t3lib_iconWorks::getIconImage($table,$rec,$this->backPath,'hspace="20" title="'.htmlspecialchars(t3lib_BEfunc::getRecordIconAltText($rec,$table)).'"'),$rec,$table).'</td>
									<td class="'.$bgColClass.'" nowrap="nowrap" width="95%">&nbsp;'.$this->linkItemText(htmlspecialchars(t3lib_div::fixed_lgd_cs(t3lib_BEfunc::getRecordTitle($table,$rec),$GLOBALS['BE_USER']->uc['titleLen'])),$rec,$table).
										($pad=='normal'?(' <strong>('.($this->clipData['normal']['mode']=='copy'?$this->clLabel('copy','cm'):$this->clLabel('cut','cm')).')</strong>'):'').'&nbsp;</td>
									<td class="'.$bgColClass.'" align="center">'.
									'<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.intval($uid).'\'); return false;').'"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' hspace="2" border="0" title="'.$this->clLabel('info','cm').'" alt="" /></a>'.
									'<a href="'.htmlspecialchars($this->removeUrl($table,$uid)).'#clip_head"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/close_12h.gif','width="11" height="12"').' border="0" title="'.$this->clLabel('removeItem').'" alt="" /></a>'.
									'</td>
								</tr>';
						} else {
							unset($this->clipData[$pad]['el'][$k]);
							$this->changed=1;
						}
					}
				}
			}
		}
		if (!count($lines))	{
			$lines[]='
								<tr>
									<td class="bgColor4"><img src="clear.gif" width="56" height="1" alt="" /></td>
									<td colspan="2" class="bgColor4" nowrap="nowrap" width="95%">&nbsp;<em>('.$this->clLabel('clipNoEl').')</em>&nbsp;</td>
								</tr>';
		}

		$this->endClipboard();
		return $lines;
	}

	/**
	 * Wraps title of pad in bold-tags and maybe the number of elements if any.
	 *
	 * @param	string		String (already htmlspecialchars()'ed)
	 * @param	string		Pad reference
	 * @return	string		HTML output (htmlspecialchar'ed content inside of tags.)
	 */
	function padTitleWrap($str,$pad)	{
		$el = count($this->elFromTable($this->fileMode?'_FILE':'',$pad));
		if ($el)	{
			return '<strong>'.$str.'</strong> ('.($pad=='normal'?($this->clipData['normal']['mode']=='copy'?$this->clLabel('copy','cm'):$this->clLabel('cut','cm')):htmlspecialchars($el)).')';
		} else {
			return $GLOBALS['TBE_TEMPLATE']->dfw($str);
		}
	}

	/**
	 * Wraps the title of the items listed in link-tags. The items will link to the page/folder where they originate from
	 *
	 * @param	string		Title of element - must be htmlspecialchar'ed on beforehand.
	 * @param	mixed		If array, a record is expected. If string, its a path
	 * @param	string		Table name
	 * @return	string
	 */
	function linkItemText($str,$rec,$table='')	{
		if (is_array($rec) && $table)	{
			if ($this->fileMode)	{
				$str=$GLOBALS['TBE_TEMPLATE']->dfw($str);
			} else {
				$str='<a href="'.htmlspecialchars($this->backPath.'db_list.php?id='.$rec['pid']).'">'.$str.'</a>';
			}
		} elseif (@file_exists($rec))	{
			if (!$this->fileMode)	{
				$str=$GLOBALS['TBE_TEMPLATE']->dfw($str);
			} else {
				$str='<a href="'.htmlspecialchars($this->backPath.'file_list.php?id='.dirname($rec)).'">'.$str.'</a>';
			}
		}
		return $str;
	}

	/**
	 * Verifies if the item $table/$uid is on the current pad.
	 * If the pad is "normal", the mode value is returned if the element existed. Thus you'll know if the item was copy or cut moded...
	 *
	 * @param	string		Table name, (_FILE for files...)
	 * @param	integer		Element uid (path for files)
	 * @return	string
	 */
	function isSelected($table,$uid)	{
		$k=$table.'|'.$uid;
		return $this->clipData[$this->current]['el'][$k] ? ($this->current=='normal'?$this->currentMode():1) : '';
	}

	/**
	 * Returns item record $table,$uid if selected on current clipboard
	 * If table and uid is blank, the first element is returned.
	 * Makes sense only for DB records - not files!
	 *
	 * @param	string		Table name
	 * @param	integer		Element uid
	 * @return	array		Element record with extra field _RECORD_TITLE set to the title of the record...
	 */
	function getSelectedRecord($table='',$uid='')	{
		if (!$table && !$uid)	{
			$elArr = $this->elFromTable('');
			reset($elArr);
			list($table,$uid) = explode('|',key($elArr));
		}
		if ($this->isSelected($table,$uid))	{
			$selRec = t3lib_BEfunc::getRecord($table,$uid);
			$selRec['_RECORD_TITLE'] = t3lib_BEfunc::getRecordTitle($table,$selRec);
			return $selRec;
		}
	}

	/**
	 * Returns the select-url for database elements
	 *
	 * @param	string		Table name
	 * @param	integer		Uid of record
	 * @param	boolean		If set, copymode will be enabled
	 * @param	boolean		If set, the link will deselect, otherwise select.
	 * @param	array		The base array of GET vars to be sent in addition. Notice that current GET vars WILL automatically be included.
	 * @return	string		URL linking to the current script but with the CB array set to select the element with table/uid
	 */
	function selUrlDB($table,$uid,$copy=0,$deselect=0,$baseArray=array())	{
		$CB = array('el'=>array(rawurlencode($table.'|'.$uid)=>$deselect?0:1));
		if ($copy)	$CB['setCopyMode'] = 1;
		$baseArray['CB'] = $CB;
		return t3lib_div::linkThisScript($baseArray);
	}

	/**
	 * Returns the select-url for files
	 *
	 * @param	string		Filepath
	 * @param	boolean		If set, copymode will be enabled
	 * @param	boolean		If set, the link will deselect, otherwise select.
	 * @param	array		The base array of GET vars to be sent in addition. Notice that current GET vars WILL automatically be included.
	 * @return	string		URL linking to the current script but with the CB array set to select the path
	 */
	function selUrlFile($path,$copy=0,$deselect=0,$baseArray=array())	{
		$CB=array('el'=>array(rawurlencode('_FILE|'.t3lib_div::shortmd5($path))=>$deselect?'':$path));
		if ($copy)	$CB['setCopyMode']=1;
		$baseArray['CB']=$CB;
		return t3lib_div::linkThisScript($baseArray);
	}

	/**
	 * pasteUrl of the element (database and file)
	 * For the meaning of $table and $uid, please read from ->makePasteCmdArray!!!
	 * The URL will point to tce_file or tce_db depending in $table
	 *
	 * @param	string		Tablename (_FILE for files)
	 * @param	mixed		"destination": can be positive or negative indicating how the paste is done (paste into / paste after)
	 * @param	boolean		If set, then the redirect URL will point back to the current script, but with CB reset.
	 * @return	string
	 */
	function pasteUrl($table,$uid,$setRedirect=1)	{
		$rU = $this->backPath.($table=='_FILE'?'tce_file.php':'tce_db.php').'?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&CB[paste]='.rawurlencode($table.'|'.$uid).
			'&CB[pad]='.$this->current;
		return $rU;
	}

	/**
	 * deleteUrl for current pad
	 *
	 * @param	boolean		If set, then the redirect URL will point back to the current script, but with CB reset.
	 * @param	boolean		If set, then the URL will link to the tce_file.php script in the typo3/ dir.
	 * @return	string
	 */
	function deleteUrl($setRedirect=1,$file=0)	{
		$rU = $this->backPath.($file?'tce_file.php':'tce_db.php').'?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&CB[delete]=1'.
			'&CB[pad]='.$this->current;
		return $rU;
	}

	/**
	 * editUrl of all current elements
	 * ONLY database
	 * Links to alt_doc.php
	 *
	 * @return	string		The URL to alt_doc.php with parameters.
	 */
	function editUrl()	{
		$elements = $this->elFromTable('');	// all records
		reset($elements);
		$editCMDArray=array();
		while(list($tP)=each($elements))	{
			list($table,$uid) = explode('|',$tP);
			$editCMDArray[] = '&edit['.$table.']['.$uid.']=edit';
		}

		$rU = $this->backPath.'alt_doc.php?'.implode('',$editCMDArray);
		return $rU;
	}

	/**
	 * Returns the remove-url (file and db)
	 * for file $table='_FILE' and $uid = shortmd5 hash of path
	 *
	 * @param	string		Tablename
	 * @param	string		uid integer/shortmd5 hash
	 * @return	string		URL
	 */
	function removeUrl($table,$uid)	{
		return t3lib_div::linkThisScript(array('CB'=>array('remove'=>$table.'|'.$uid)));
	}

	/**
	 * This traverses the elements on the current clipboard pane
	 * and unsets elements which does not exist anymore or are disabled.
	 *
	 * @return	void
	 */
	function cleanCurrent()	{
		if (is_array($this->clipData[$this->current]['el']))	{
			reset($this->clipData[$this->current]['el']);
			while(list($k,$v)=each($this->clipData[$this->current]['el']))	{
				list($table,$uid) = explode('|',$k);
				if ($table!='_FILE')	{
					if (!$v || !is_array(t3lib_BEfunc::getRecord($table,$uid,'uid')))	{
						unset($this->clipData[$this->current]['el'][$k]);
						$this->changed=1;
					}
				} else {
					if (!$v || !@file_exists($v))	{
						unset($this->clipData[$this->current]['el'][$k]);
						$this->changed=1;
					}
				}
			}
		}
	}

	/**
	 * Counts the number of elements from the table $matchTable. If $matchTable is blank, all tables (except '_FILE' of course) is counted.
	 *
	 * @param	string		Table to match/count for.
	 * @param	string		$pad can optionally be used to set another pad than the current.
	 * @return	array		Array with keys from the CB.
	 */
	function elFromTable($matchTable='',$pad='')	{
		$pad = $pad ? $pad : $this->current;
		$list=array();
		if (is_array($this->clipData[$pad]['el']))	{
			reset($this->clipData[$pad]['el']);
			while(list($k,$v)=each($this->clipData[$pad]['el']))	{
				if ($v)	{
					list($table,$uid) = explode('|',$k);
					if ($table!='_FILE')	{
						if ((!$matchTable || (string)$table==(string)$matchTable) && $GLOBALS['TCA'][$table])	{
							$list[$k]= ($pad=='normal'?$v:$uid);
						}
					} else {
						if ((string)$table==(string)$matchTable)	{
							$list[$k]=$v;
						}
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Returns confirm JavaScript message
	 *
	 * @param	string		Table name
	 * @param	mixed		For records its an array, for files its a string (path)
	 * @param	string		Type-code
	 * @param	array		Array of selected elements
	 * @return	string		JavaScript "confirm" message
	 */
	function confirmMsg($table,$rec,$type,$clElements)	{
		$labelKey = 'LLL:EXT:lang/locallang_core.php:mess.'.($this->currentMode()=='copy'?'copy':'move').($this->current=='normal'?'':'cb').'_'.$type;
		$msg = $GLOBALS['LANG']->sL($labelKey);

		if ($table=='_FILE')	{
			$thisRecTitle = basename($rec);
			if ($this->current=='normal')	{
				reset($clElements);
				$selItem = current($clElements);
				$selRecTitle = basename($selItem);
			} else {
				$selRecTitle=count($clElements);
			}
		} else {
			$thisRecTitle = (
				$table=='pages' && !is_array($rec) ?
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] :
				t3lib_BEfunc::getRecordTitle($table,$rec)
			);

			if ($this->current=='normal')	{
				$selItem = $this->getSelectedRecord();
				$selRecTitle=$selItem['_RECORD_TITLE'];
			} else {
				$selRecTitle=count($clElements);
			}
		}

			// Message:
		$conf='confirm('.$GLOBALS['LANG']->JScharCode(sprintf(
			$msg,
			t3lib_div::fixed_lgd_cs($selRecTitle,30),
			t3lib_div::fixed_lgd_cs($thisRecTitle,30)
			)).')';
		return $conf;
	}

	/**
	 * Removes element on clipboard
	 *
	 * @param	string		Key of element in ->clipData array
	 * @return	void
	 */
	function removeElement($el)	{
		unset($this->clipData[$this->current]['el'][$el]);
		$this->changed=1;
	}

	/**
	 * Saves the clipboard, no questions asked.
	 * Use ->endClipboard normally (as it checks if changes has been done so saving is necessary)
	 *
	 * @return	void
	 * @access private
	 */
	function saveClipboard()	{
		global $BE_USER;
		$BE_USER->pushModuleData('clipboard',$this->clipData);
	}

	/**
	 * Returns the current mode, 'copy' or 'cut'
	 *
	 * @return	string		"copy" or "cut"
	 */
	function currentMode()	{
		return $this->clipData[$this->current]['mode']=='copy' ? 'copy' : 'cut';
	}

	/**
	 * Clipboard label - getting from "EXT:lang/locallang_core.php:"
	 *
	 * @param	string		Label Key
	 * @param	string		Alternative key to "labels"
	 * @return	string
	 */
	function clLabel($key,$Akey='labels')	{
		return htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:'.$Akey.'.'.$key));
	}















	/*****************************************
	 *
	 * FOR USE IN tce_db.php:
	 *
	 ****************************************/

	/**
	 * Applies the proper paste configuration in the $cmd array send to tce_db.php.
	 * $ref is the target, see description below.
	 * The current pad is pasted
	 *
	 * 		$ref: [tablename]:[paste-uid].
	 * 		tablename is the name of the table from which elements *on the current clipboard* is pasted with the 'pid' paste-uid.
	 * 		No tablename means that all items on the clipboard (non-files) are pasted. This requires paste-uid to be positive though.
	 * 		so 'tt_content:-3'	means 'paste tt_content elements on the clipboard to AFTER tt_content:3 record
	 * 		'tt_content:30'	means 'paste tt_content elements on the clipboard into page with id 30
	 * 		':30'	means 'paste ALL database elements on the clipboard into page with id 30
	 * 		':-30'	not valid.
	 *
	 * @param	string		[tablename]:[paste-uid], see description
	 * @param	array		Command-array
	 * @return	array		Modified Command-array
	 */
	function makePasteCmdArray($ref,$CMD)	{
		list($pTable,$pUid) = explode('|',$ref);
		$pUid = intval($pUid);

		if ($pTable || $pUid>=0)	{	// pUid must be set and if pTable is not set (that means paste ALL elements) the uid MUST be positive/zero (pointing to page id)
			$elements = $this->elFromTable($pTable);

			$elements = array_reverse($elements);	// So the order is preserved.
			$mode = $this->currentMode()=='copy' ? 'copy' : 'move';

				// Traverse elements and make CMD array
			reset($elements);
			while(list($tP)=each($elements))	{
				list($table,$uid) = explode('|',$tP);
				if (!is_array($CMD[$table]))	$CMD[$table]=array();
				$CMD[$table][$uid][$mode]=$pUid;
				if ($mode=='move')	$this->removeElement($tP);
			}
			$this->endClipboard();
		}
		return $CMD;
	}

	/**
	 * Delete record entries in CMD array
	 *
	 * @param	array		Command-array
	 * @return	array		Modified Command-array
	 */
	function makeDeleteCmdArray($CMD)	{
		$elements = $this->elFromTable('');	// all records
		reset($elements);
		while(list($tP)=each($elements))	{
			list($table,$uid) = explode('|',$tP);
			if (!is_array($CMD[$table]))	$CMD[$table]=array();
			$CMD[$table][$uid]['delete']=1;
			$this->removeElement($tP);
		}
		$this->endClipboard();
		return $CMD;
	}

















	/*****************************************
	 *
	 * FOR USE IN tce_file.php:
	 *
	 ****************************************/

	/**
	 * Applies the proper paste configuration in the $file array send to tce_file.php.
	 * The current pad is pasted
	 *
	 * @param	string		Reference to element (splitted by "|")
	 * @param	array		Command-array
	 * @return	array		Modified Command-array
	 */
	function makePasteCmdArray_file($ref,$FILE)	{
		list($pTable,$pUid) = explode('|',$ref);
		$elements = $this->elFromTable('_FILE');
		$mode = $this->currentMode()=='copy' ? 'copy' : 'move';

			// Traverse elements and make CMD array
		reset($elements);
		while(list($tP,$path)=each($elements))	{
			$FILE[$mode][]=array('data'=>$path,'target'=>$pUid);
			if ($mode=='move')	$this->removeElement($tP);
		}
		$this->endClipboard();

		return $FILE;
	}

	/**
	 * Delete files in CMD array
	 *
	 * @param	array		Command-array
	 * @return	array		Modified Command-array
	 */
	function makeDeleteCmdArray_file($FILE)	{
		$elements = $this->elFromTable('_FILE');
			// Traverse elements and make CMD array
		reset($elements);
		while(list($tP,$path)=each($elements))	{
			$FILE['delete'][]=array('data'=>$path);
			$this->removeElement($tP);
		}
		$this->endClipboard();

		return $FILE;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_clipboard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_clipboard.php']);
}
?>
