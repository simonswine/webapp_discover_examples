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

 
class tx_sysmessages extends mod_user_task {
	var $charWidth=60;

	function modMenu()	{
		global $LANG;
		return Array(
			"tx_sysmessages_box" => array(
				"inbox" => $LANG->getLL("messages_inbox"),
				"archive" => $LANG->getLL("messages_archive"),
				"sent" => $LANG->getLL("messages_sent")
			)
		);
	}
	function overview_main(&$pObj)	{
		$icon = '<img src="'.$this->backPath.t3lib_extMgm::extRelPath("sys_messages").'ext_icon.gif" width=18 height=16 class="absmiddle">';
		$content.=$pObj->doc->section($icon."&nbsp;".$this->headLink("tx_sysmessages",0,"&SET[tx_sysmessages_box]=inbox"),$this->renderMessagesList(),1,1,0,1);
		return $content;
	}
	function main() {
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		return $this->renderMessages();
		
	}




	// ************************
	// MESSAGES
	// ***********************
	function messages_insertRel($mes_id,$mRow,$brow,$sAE,&$emRec,$status=0)	{
		$fields_values = array(
			"uid_local" => $mes_id,
			"uid_foreign" => $brow["uid"],
			"tstamp" => time(),
			"status" => $status
		);
		if ($status==126)	{	// If the relation is for the user himself (sent) set the is_read flag.
			$fields_values["is_read"] = 1;
		}
		
		$GLOBALS['TYPO3_DB']->exec_INSERTquery("sys_messages_users_mm", $fields_values);

		if ($sAE && strstr($brow["email"],"@"))	{
			$this->sendEmail($brow["email"],$mRow["title"],$mRow["note"]);
			$emRec[] = $brow["username"]." (".$brow["email"].")";
		} else {
			$emRec[] = $brow["username"];
		}
	}
	function exec_messages_selectFromStatus($ss,$fields="sys_messages.*,sys_messages_users_mm.*",$where="")	{
		return $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						$fields,
						"sys_messages",
						"sys_messages_users_mm",
						"be_users",
						" AND sys_messages_users_mm.uid_foreign=".intval($this->BE_USER->user["uid"])." AND sys_messages_users_mm.status=".$ss.$where,
						'',
						"sys_messages.crdate DESC"
					);
	}
	function renderMessagesList()	{
		global $LANG;

		$res = $this->exec_messages_selectFromStatus(0,"sys_messages.*,sys_messages_users_mm.*"," AND sys_messages_users_mm.is_read=0");
		$lines = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$lines[]='<nobr>'.$this->messages_link('<img src="'.$this->backPath.'gfx/mailicon.gif" width="18" hspace=6 height="10" align=top border=0><b>'.$this->fixed_lgd($row["title"]),$row["mm_uid"]).'</b></nobr><BR>';
		}

		$res = $this->exec_messages_selectFromStatus(0,"count(*)");
		list($mc) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$lines[]='<nobr>'.$this->messages_link(sprintf($LANG->getLL("messages_index_msgs"),$mc),"0").'</nobr><BR>';

		$out = implode("",$lines);
		return $out;
	}
	function messages_link($str,$id,$box="inbox")	{
		$str='<a href="index.php?SET[function]=tx_sysmessages&SET[tx_sysmessages_box]='.$box.'&sys_messages_MM_uid='.$id.'#msg" target="list_frame" onClick="this.blur();">'.$str.'</a>';
		return $str;
	}
	function renderMessages()	{
		global $LANG;
		$theCode="";
			
			// Setting up general things for the function:
		$this->pObj->doc->tableLayout = Array (
			"defRow" => Array (
				"0" => Array('<TD valign=top nowrap>','</td>'),
				"defCol" => Array('<td valign="top" width=99%>','</td>')
			)
		);
		$this->pObj->doc->table_TR = '<TR class="bgColor-10">';
		$this->pObj->doc->table_TABLE = '<TABLE border=0 cellspacing=1 cellpadding=2 width=100%>';
		
			// This is the id of the relation record which connects the mails with the user
		$mUid_MM = t3lib_div::intInRange(t3lib_div::_GP("sys_messages_MM_uid"),0);

			// PROCESSING:
		$data = t3lib_div::_GP("data");
			
			// Deleted and marked mails
		if (t3lib_div::_GP("marked_mails") || t3lib_div::_GP("marked_mails"))	{
			$marked_mails = @array_keys(t3lib_div::_GP("MARKED"));
			if (is_array($marked_mails))	{
				$ss = t3lib_div::_GP("marked_mails_action");
				if (t3lib_div::inList("0,1,127",$ss))	{
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_messages_users_mm', 'mm_uid IN ('.implode(',',$GLOBALS['TYPO3_DB']->cleanIntArray($marked_mails)).') AND uid_foreign='.intval($this->BE_USER->user["uid"]), array('status' => $ss));
				}
			}
			// Create mails (new and replys)
		} elseif (is_array($data["sys_messages"]))	{
			reset($data["sys_messages"]);
			$key = key($data["sys_messages"]);

			// If "key" is an integer it's a reply
			$rres = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						"sys_messages.*",
						"sys_messages",
						"sys_messages_users_mm",
						"be_users",
						" AND sys_messages_users_mm.mm_uid=".intval($key)." AND be_users.uid=".intval($this->BE_USER->user["uid"])
					);
			if ($r_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rres))	{
			} else {
				$key=="NEW";
			}

			if ($data["sys_messages"][$key]["recipient"] && $data["sys_messages"][$key]["title"] && $data["sys_messages"][$key]["note"])	{
				$fields_values = array(
					"title" => $data["sys_messages"][$key]["title"],
					"note" => $data["sys_messages"][$key]["note"],
					"parent" => is_array($r_row)?($r_row["parent"]?$r_row["parent"]:$r_row["uid"]):0,
					"orig_recipient" => $data["sys_messages"][$key]["recipient"],
					"crdate" => time(),
					"cruser_id" => $this->BE_USER->user["uid"]
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery("sys_messages", $fields_values);

				$mes_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
				
					// Relation:
				$tempQ = FALSE;
				$emRec = array();
				if ($data["sys_messages"][$key]["recipient"]>0)		{	// Ordinary user
					$tempQ = TRUE;
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,realName,email', 'be_users', 'uid='.intval($data['sys_messages'][$key]['recipient']).t3lib_BEfunc::deleteClause('be_users'));
				} 
				if ($data["sys_messages"][$key]["recipient"]<0)		{	// Users in group
					$tempQ = TRUE;
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,realName,email', 'be_users', $GLOBALS['TYPO3_DB']->listQuery('usergroup_cached_list', abs($data['sys_messages'][$key]['recipient']), 'be_users').t3lib_BEfunc::deleteClause('be_users'));
				}
				if ($tempQ)	{
					$sAE = t3lib_div::_GP("sendAsEmail");
					while($brow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
						if ($brow["uid"]!=$this->BE_USER->user["uid"] || $data["sys_messages"][$key]["recipient"]>0)	{	// Send mail to all in group except user himself (unless the mail is targeted directly to the user!)
							$this->messages_insertRel($mes_id,$fields_values,$brow,$sAE,$emRec);
						}
					}	
						// Sending to whole group (REPLYs only)
					if (is_array($r_row) && t3lib_div::_GP("replyAll"))	{
						$rres = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
								"be_users.username,be_users.realName,be_users.uid,be_users.email",
								"sys_messages",
								"sys_messages_users_mm",
								"be_users",
								" AND sys_messages_users_mm.uid_local=".intval($r_row["uid"])." AND sys_messages_users_mm.status!=126"
							);
						while($brow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rres))	{
							if ($brow["uid"]!=$this->BE_USER->user["uid"])	{	// Send mail to all in group except user himself (unless the mail is targeted directly to the user!)
								$this->messages_insertRel($mes_id,$fields_values,$brow,$sAE,$emRec);
							}
						}
					}

						// Setting the sent mail at the sending user
					$temp=array();
					$this->messages_insertRel($mes_id,$fields_values,$this->BE_USER->user,0,$temp,126);
				}
				if (count($emRec))	{
					$emailList=implode("<BR>&nbsp;&nbsp;",$emRec);
					$theCode.= $this->pObj->doc->section($LANG->getLL("messages_sent"),$LANG->getLL("messages_sent_msg")."<BR>&nbsp;&nbsp;".$emailList,0,1,1);
				}
			} else {
				$theCode.= $this->pObj->doc->section($LANG->getLL("messages_sent"),$this->errorIcon().'<span class="typo3-red">'.$LANG->getLL("messages_sentError_msg").'</span>',0,1);
			}
			$mUid_MM=0;	// No display of messages if one is just created og replied to.
		}
		
			// Get groupnames for todo-tasks
		list($be_user_Array,$be_group_Array,$be_user_Array_o)=$this->getUserAndGroupArrays();

			// Display message:
		$msg_buffer="";
		$currentSender="";
		$doUpdate=0;
		if ($mUid_MM)	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						'sys_messages.*,sys_messages_users_mm.*',
						'sys_messages',
						'sys_messages_users_mm',
						'be_users',
						' AND sys_messages_users_mm.uid_foreign='.intval($this->BE_USER->user['uid']).
							' AND mm_uid='.intval($mUid_MM)
					);
			$msg = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					// Update 'is_read' flag:
				if (!$row["is_read"])	{
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_messages_users_mm', 'mm_uid='.intval($row["mm_uid"]), array('is_read' => 1));
					$row["is_read"]=1;
					$doUpdate=1;
				}

				$currentSender = $row["cruser_id"];
				$currentSubject = $row["title"];
				$currentUid = $row["uid"];
				$currentSenderName = $be_user_Array_o[$row["cruser_id"]]["username"]." (".$be_user_Array_o[$row["cruser_id"]]["realName"].")";
				$replySep=" \n\n".$LANG->getLL("messages_replySeparator").chr(10);
				$currentContent = $replySep.$row["note"];
				$currentContent_formatted = $replySep."> ".implode(chr(10)."> ",explode(chr(10),$this->breakLines($row["note"])));
				
					// Getting recipient list (all relations which are not 126 (sent)):
				$recipArray = array();
				$rres = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						"be_users.username,be_users.realName,be_users.uid,sys_messages_users_mm.is_read",
						"sys_messages",
						"sys_messages_users_mm",
						"be_users",
						" AND sys_messages_users_mm.uid_local=".intval($row["uid"])." AND sys_messages_users_mm.status!=126"
					);
				while($r_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rres))	{
					$recipArray[$r_row["uid"]]=$r_row["username"]." (".$r_row["realName"].") ";
					if (!$r_row["is_read"])	{$recipArray[$r_row["uid"]]=$GLOBALS["TBE_TEMPLATE"]->dfw($recipArray[$r_row["uid"]]);}
				}

				// Getting thread:
				$pUid = $row["parent"] ? $row["parent"] : $row["uid"];
				$rres = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						"sys_messages.*,sys_messages_users_mm.*",
						"sys_messages",
						"sys_messages_users_mm",
						"be_users",
						" AND (sys_messages.uid=".intval($pUid)." OR sys_messages.parent=".intval($pUid).") AND sys_messages_users_mm.uid_foreign=".intval($this->BE_USER->user["uid"]),
						'',
						'sys_messages.crdate'
					);
				while($r_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rres))	{
					if ($mUid_MM==$r_row["mm_uid"])	{
						$active='<img src="'.$this->backPath.'gfx/content_client.gif" width="7" height="10" border="0" align=top>';
					} else {
						$active='';
					}
					$thSender = $be_user_Array_o[$r_row["cruser_id"]]["username"]." (".$be_user_Array_o[$r_row["cruser_id"]]["realName"]."), ".$this->dateTimeAge($r_row["crdate"]);
					$thread_lines[]=$this->linkMessage('<img src="'.$this->backPath.'gfx/i/'.($r_row["orig_recipient"]<0?'tc_mails_group.gif':'tc_mails.gif').'" width="18" height="16" hspace=2 border=0 align=top>'.$active."&nbsp;".$thSender,$r_row["mm_uid"]);
					//debug($r_row);
				}
//				debug($recipArray);
				

				$header=fw('<img src="'.$this->backPath.'gfx/i/'.($row["orig_recipient"]<0?'tc_mails_group.gif':'tc_mails.gif').'" width="18" height="16" hspace=2 border=0 align=top> <strong>'.$row["title"].'</strong><BR>');
				$formA=array();
				$formA[]=array($LANG->getLL("messages_lSender").":&nbsp;", $currentSenderName);
//				if (count($recipArray)>1)		{
					$formA[]=array($LANG->getLL("messages_lRecipients").":&nbsp;", implode("<BR>",$recipArray));
//				}
				$formA[]=array($LANG->getLL("messages_lDateTime").":&nbsp;", $this->dateTimeAge($row["crdate"]));
				$formA[]=array($LANG->getLL("messages_message").":&nbsp;", nl2br($row["note"]));
				if (count($thread_lines)>1)	{
					$formA[]=array($LANG->getLL("messages_thread").":&nbsp;", implode("<BR>",$thread_lines));
				}

				$msg[] = $header.$this->pObj->doc->table($formA);
			}
			$msg_buffer.= $this->pObj->doc->spacer(20);
			$msg_buffer.= $this->pObj->doc->section('<a name="msg"></a>'.$LANG->getLL("messages_content"),implode("",$msg),0,1,0,1);
		}


			// SELECT messages for list:
		switch($this->pObj->MOD_SETTINGS["tx_sysmessages_box"])	{
			case "archive":
				$ss = 1;
			break;
			case "deleted":
				$ss = 127;
			break;
			case "sent":
				$ss = 126;
			break;
			default:
				$ss = 0;
			break;
		}
		
		
		$res = $this->exec_messages_selectFromStatus($ss);
		$out = "";
		$marked_code = $this->pObj->MOD_SETTINGS["tx_sysmessages_box"]=="sent" ? "" : '<strong>'.fw($LANG->getLL("messages_lMark").":").'</strong>';
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
			$lines=array();
			$lines[]='<tr>
				<td class="bgColor5">'.fw("&nbsp;").'</td>
				<td class="bgColor5" width=33%><strong>'.fw($LANG->getLL("messages_lSubject").":").'</strong></td>
				<td class="bgColor5" width=33%><strong>'.fw($LANG->getLL("messages_lSender").":").'</strong></td>
				<td class="bgColor5" width=33%><strong>'.fw($LANG->getLL("messages_lDateTime").":").'</strong></td>
				<td class="bgColor5">'.$marked_code.'</td>
			</tr>';

			$c=0;
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
//			debug($row);
				$c++;
				$bgColorClass=($c+1)%2 ? 'bgColor' : 'bgColor-10';
				if ($mUid_MM==$row["mm_uid"])	{
					$active='<img src="'.$this->backPath.'gfx/content_client.gif" width="7" height="10" border="0" align=top>';
				} else {
					$bTb=$bTe="";
					$active='';
				}
				if (!$row["is_read"])	{
					$bTb="<B>";
					$bTe="</B>";
				} else {
					$bTb=$bTe="";
				}
				$marked_code = $this->pObj->MOD_SETTINGS["tx_sysmessages_box"]=="sent" ? "" : '<input type="checkbox" name="MARKED['.$row["mm_uid"].']" value="1">';
				$lines[]='<tr>
					<td class="'.$bgColorClass.'">'.$this->linkMessage('<img src="'.$this->backPath.'gfx/i/'.($row["orig_recipient"]<0?'tc_mails_group.gif':'tc_mails.gif').'" width="18" height="16" hspace=2 border=0>',$row["mm_uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>'.$this->linkMessage($active.$bTb."&nbsp;".$this->fixed_lgd($row["title"])."&nbsp;".$bTe,$row["mm_uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>'.$this->linkMessage($bTb."&nbsp;".$this->fixed_lgd($be_user_Array_o[$row["cruser_id"]]["username"]." (".$be_user_Array_o[$row["cruser_id"]]["realName"].")")."&nbsp;".$bTb,$row["mm_uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>'.$this->dateTimeAge($row["crdate"]).'</td>
					<td class="'.$bgColorClass.'" align=right>'.$marked_code.'</td>
				</tr>';
			}
			$out = '<table border=0 cellpadding=0 cellspacing=0>'.implode("",$lines).'</table>';
		}
		$labelKey = $LANG->getLL("messages_".$this->pObj->MOD_SETTINGS["tx_sysmessages_box"]);
		$lMenu = t3lib_BEfunc::getFuncMenu("","SET[tx_sysmessages_box]",$this->pObj->MOD_SETTINGS["tx_sysmessages_box"],$this->pObj->MOD_MENU["tx_sysmessages_box"]);
		if ($c && $this->pObj->MOD_SETTINGS["tx_sysmessages_box"]!="sent")	{
			$bMenu = '<BR><div align=right><select name="marked_mails_action">
			<option value=-1></option>
			<option value=0>'.$LANG->getLL("messages_moveTo").' '.$LANG->getLL("messages_inbox").'</option>
			<option value=1>'.$LANG->getLL("messages_moveTo").' '.$LANG->getLL("messages_archive").'</option>
			<option value=127>'.$LANG->getLL("messages_moveDelete").'</option>
			</select><input type="submit" name="marked_mails" value="'.$LANG->getLL("messages_moveMarked").'"></div>';
		} else $bMenu = "";
		$theCode.= $this->pObj->doc->section($labelKey,$lMenu.$out.$bMenu,0,1);
		$theCode.=$msg_buffer;

			
			// CREATE MAIL:
		// New mail/reply form:
		$opt=array();
		$opt[]='<option value="0"></option>';
		reset($be_user_Array);
		while(list($uid,$dat)=each($be_user_Array))	{
			$opt[]='<option value="'.$uid.'">'.htmlspecialchars($dat["username"].($dat["uid"]==$this->BE_USER->user["uid"]?' ['.$LANG->getLL("lSelf").']':' ('.$dat["realName"].')')).'</option>';
		}
		if (count($be_group_Array))	{
			$opt[]='<option value="0">'.$LANG->getLL("listSeparator_Groups").'</option>';
			reset($be_group_Array);
			while(list($uid,$dat)=each($be_group_Array))	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', 'be_users', $GLOBALS['TYPO3_DB']->listQuery('usergroup_cached_list', $uid, 'be_users').t3lib_BEfunc::deleteClause('be_users'));
				list($grCount) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
				$opt[] = '<option value="-'.$uid.'">'.htmlspecialchars($dat["title"]." (".$grCount." recipients)").'</option>';
			}
		}

		$type = ($currentSender && $this->pObj->MOD_SETTINGS["tx_sysmessages_box"]!="sent") ? $mUid_MM : "NEW";
		$replyContent="";
		$formA=array();
		if ($type=="NEW")	{
			$formA[]=array($LANG->getLL("messages_recipient").":&nbsp;", '<select name="data[sys_messages]['.$type.'][recipient]">'.implode("",$opt).'</select>');
			$formA[]=array($LANG->getLL("messages_subject").":&nbsp;", '<input type="text" name="data[sys_messages]['.$type.'][title]" max=255'.$this->pObj->doc->formWidth(40).'>');
		} else {
			$formA[]=array($LANG->getLL("messages_recipient").":&nbsp;", '<input type="hidden" name="data[sys_messages]['.$type.'][recipient]" value="'.$currentSender.'">'.$currentSenderName);
			$currentSubject = $LANG->getLL("messages_RE").": ".ereg_replace("[[:alnum:]]:[ ]*","",$currentSubject);
			$formA[]=array($LANG->getLL("messages_subject").":&nbsp;", '<input type="hidden" name="data[sys_messages]['.$type.'][title]" value="'.htmlspecialchars($currentSubject).'">'.$this->fixed_lgd($currentSubject,50));
		}
		$insert = $type=="NEW" ? '' : '<BR><input type=hidden name="insertContentStore" value="'.htmlspecialchars($currentContent).'"><input type=hidden name="insertContentStore_formatted" value="'.htmlspecialchars($currentContent_formatted).'"><strong><a href="#" onClick="document.forms[0][\'data[sys_messages]['.$type.'][note]\'].value+=document.forms[0].insertContentStore.value; return false;">Insert original</a> - <a href="#"  onClick="document.forms[0][\'data[sys_messages]['.$type.'][note]\'].value+=document.forms[0].insertContentStore_formatted.value; return false;">Insert formatted</a></strong>';
		$formA[]=array($LANG->getLL("messages_message").":&nbsp;", '<textarea wrap="virtual" rows="10" name="data[sys_messages]['.$type.'][note]"'.$this->pObj->doc->formWidthText(40,"","virtual").'>'.t3lib_div::formatForTextarea($replyContent).'</textarea>'.$insert);
		if ($this->BE_USER->user["email"])	{
			$formA[]=array("&nbsp;", '<input type="checkbox" name="sendAsEmail" value=1>'.$LANG->getLL("messages_email").'<BR>('.$LANG->getLL("lReplyAddress").': '.$this->BE_USER->user["email"].')');
		}
		if ($type!="NEW" && count($recipArray)>1)	{
			$formA[]=array("&nbsp;", '<input type="checkbox" name="replyAll" value=1>'.sprintf($LANG->getLL("messages_replyAll"),count($recipArray)));
		}

		$formA[]=array("&nbsp;","&nbsp;");
		$onClick = "if (document.forms[0]['data[sys_messages][".$type."][title]'].value=='' || document.forms[0]['data[sys_messages][".$type."][note]'].value=='' || document.forms[0]['data[sys_messages][".$type."][recipient]'].options[document.forms[0]['data[sys_messages][".$type."][recipient]'].selectedIndex].value==0) {alert(".$GLOBALS['LANG']->JScharCode($LANG->getLL("messages_mustFillIn")).");return false;}";
		if ($type=="NEW")	{
			$formA[]=array("&nbsp;", '<input type="submit" name="submit" value="'.$LANG->getLL("lSendNew").'" onClick="'.$onClick.'">');
		} else {
			$formA[]=array("&nbsp;", '<input type="submit" name="submit" value="'.$LANG->getLL("lSendReply").'">');
		}
			
		$update = $doUpdate ? '<script language="javascript" type="text/javascript">if (parent.list_frame) {parent.nav_frame.document.location = "overview.php";}</script>' : '';
		$theCode.= $this->pObj->doc->spacer(20);
		$theCode.= $this->pObj->doc->section($LANG->getLL($type=="NEW"?"messages_new":"messages_reply"),$this->pObj->doc->table($formA).$update,0,1);
		return $theCode;
	}
	function linkMessage($str,$id)	{
		$str='<a href="index.php?sys_messages_MM_uid='.$id.'#msg">'.$str.'</a>';
		return $str;
	}
	function breakLines($str,$implChar="\n",$charWidth=0)	{
		return t3lib_div::breakLinesForEmail($str,$implChar,$charWidth?$charWidth:$this->charWidth);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_messages/class.tx_sysmessages.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_messages/class.tx_sysmessages.php"]);
}

?>