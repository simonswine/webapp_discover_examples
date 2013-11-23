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

require_once (PATH_t3lib."class.t3lib_tceforms.php");
require_once (PATH_t3lib."class.t3lib_tcemain.php");

class tx_systodos extends mod_user_task {
	var $todoTypesCache = array();
	var $insCounter=0;

	function overview_main(&$pObj)	{
		$icon = '<img src="'.$this->backPath.t3lib_extMgm::extRelPath("sys_todos").'ext_icon.gif" width=18 height=16 class="absmiddle">';
		$content.=$pObj->doc->section($icon."&nbsp;".$this->headLink("tx_systodos",1),$this->renderTaskList(),1,1,0,1);
		return $content;
	}
	function main() {
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		return $this->renderTasks();
	}



	// ************************
	// TODO-tasks
	// ***********************
	function renderTaskList()	{
		global $LANG;

		$res = $this->exec_todos_getQueryForTodoRels(" AND sys_todos_users_mm.finished_instance=0");
		$lines=array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$lines[]='<nobr>'.$this->todos_link('<img src="'.$this->backPath.'gfx/todoicon_'.($row["cruser_id"]==$this->BE_USER->user["uid"]?"user":"group").'.gif" width="18" hspace=6 height="10" align=top border=0><strong>'.$this->fixed_lgd($row["title"]),-$row["mm_uid"]).'</strong></nobr><BR>';
		}

		$res = $this->exec_todos_getQueryForTodoRels("", "count(*)", 1);
		list($mc) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$lines[]='<nobr>'.$this->todos_link(sprintf($LANG->getLL("todos_index_msgs"),$mc),"0").'</nobr><BR>';

		$out = implode("",$lines);
		return $out;
	}
	function todos_link($str,$id)	{
		$str='<a href="index.php?SET[function]=tx_systodos&sys_todos_uid='.$id.'#todo" target="list_frame" onClick="this.blur();">'.$str.'</a>';
		return $str;
	}
	function tasks_makeTargetSelector($be_user_Array,$be_group_Array,$type,$returnOptsOnly=0)	{
		global $LANG;
		// Plain todo
		$opt=array();
		reset($be_user_Array);
		$opt[]='<option value="'.$this->BE_USER->user["uid"].'">'.$this->BE_USER->user["username"].' ['.$LANG->getLL("lSelf").']</option>';
		while(list($uid,$dat)=each($be_user_Array))	{
			if ($uid!=$this->BE_USER->user["uid"])	{
				$opt[]='<option value="'.$uid.'"'.($dat["uid"]==$this->BE_USER->user["uid"]?" selected":"").'>'.htmlspecialchars($dat["username"].($dat["uid"]==$this->BE_USER->user["uid"]?' ['.$LANG->getLL("lSelf").']':' ('.$dat["realName"].')')).'</option>';
			}
		}
		if (count($be_group_Array))	{
			$opt[]='<option value="0">'.$LANG->getLL("listSeparator_Groups").'</option>';
			reset($be_group_Array);
			while(list($uid,$dat)=each($be_group_Array))	{
				$opt[]='<option value="-'.$uid.'">'.htmlspecialchars($dat["title"]).'</option>';
			}
		}
		if ($returnOptsOnly)	return $opt;
		return array($LANG->getLL("todos_target").":&nbsp;", '<select name="data[sys_todos]['.$type.'][target_user]">'.implode("",$opt).'</select>');
	}
	function renderTasks()	{
		global $LANG;
		$theCode="";

			// Setting up general things for the function:
		$tUid = intval(t3lib_div::_GP("sys_todos_uid"));
		$this->pObj->doc->tableLayout = Array (
			"defRow" => Array (
				"0" => Array('<TD valign=top nowrap>','</td>'),
				"defCol" => Array('<td valign="top" width=99%>','</td>')
			)
		);
		$this->pObj->doc->table_TR = '<TR class="bgColor-10">';
		$this->pObj->doc->table_TABLE = '<TABLE border=0 cellspacing=1 cellpadding=2 width=100%>';

		$this->todos_processIncoming($tUid);

			// Get groupnames for todo-tasks
		$this->getUserAndGroupArrays();	// Users and groups blinded due to permissions, plus (third) the original user array with all users
//		debug($this->userGroupArray);

			// Create Todo types array (workflows):
		$todoTypes=array();
		$todoTypes["plain"]='['.$LANG->getLL("todos_plain").']';
		if (t3lib_extMgm::isLoaded("sys_workflows"))	{
			if ($this->BE_USER->isAdmin())	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_workflows', 'sys_workflows.pid=0', '', 'sys_workflows.title');
			} else {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
							"sys_workflows.*",
							"sys_workflows",
							"sys_workflows_algr_mm",
							"be_groups",
							"AND be_groups.uid IN (".($this->BE_USER->groupList?$this->BE_USER->groupList:0).")
								 AND sys_workflows.pid=0
								 AND sys_workflows.hidden=0",
							'sys_workflows.uid',
							'sys_workflows.title'
						);
			}
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$todoTypes["wf_".$row["uid"]]=$row["title"];
			}
		}

			// Printing the todo list of a user:
		$theCode.= $this->todos_displayLists($todoTypes,$tUid);

			// Display todo:
		if ($tUid)	{
			$theCode.= $this->todos_displayTodo($todoTypes,$tUid);
		}

			// New todo:
		$theCode.= $this->todos_createForm($todoTypes);

		return $theCode;
	}
	function todos_finalizeWorkflow($workflowRecord,$relRecord)	{
		global $TCA;
		list($table,$uid)=explode(":",$relRecord["rec_reference"]);

//		debug($workflowRecord);
//		debug($relRecord);

		if ($workflowRecord["tablename"]==$table && $TCA[$table])	{
			$itemRecord = t3lib_BEfunc::getRecord($table,$uid);

			if (is_array($itemRecord))	{
				$dataArr=array();
				$cmdArr=array();

					// Changing permissions for the page:
				if ($table=="pages" && $workflowRecord["final_set_perms"])	{
					$dataArr[$table][$uid]["perms_userid"] = $workflowRecord["final_perms_userid"];
					$dataArr[$table][$uid]["perms_groupid"] = $workflowRecord["final_perms_groupid"];
					$dataArr[$table][$uid]["perms_user"] = $workflowRecord["final_perms_user"];
					$dataArr[$table][$uid]["perms_group"] = $workflowRecord["final_perms_group"];
					$dataArr[$table][$uid]["perms_everybody"] = $workflowRecord["final_perms_everybody"];
				}

					// Unhide:
				if ($workflowRecord["final_unhide"])	{
					$hCol = is_array($TCA[$table]["ctrl"]["enablecolumns"]) ? $TCA[$table]["ctrl"]["enablecolumns"]["disabled"] : "";
					if ($hCol)	{
						$dataArr[$table][$uid][$hCol]=0;
					}
				}

					// Target:
				list($target_pid) = explode(",",$workflowRecord["final_target"]);
				$targetPage = t3lib_BEfunc::getRecord("pages",$target_pid);
				if (is_array($targetPage))	{
					$cmdArr[$table][$uid]["move"]=$targetPage["uid"];
				}
//debug($workflowRecord["final_target"]);
//debug($cmdArr);


					// Perform it (as ADMIN)
				$tce = t3lib_div::makeInstance("t3lib_TCEmain");
				$tce->stripslashes_values=0;
				$tce->start($dataArr,$cmdArr,$this->BE_USER);
				$tce->admin=1;	// Set ADMIN permission for this operation.
				$tce->process_datamap();
				$tce->process_cmdmap();
				unset($tce);

				return true;
			} else {
				debug("ERROR: The reference record was not there!");
			}
		} else {
			debug("ERROR: Strange thing, the table name was not valid!");
		}
	}
	function todos_beginWorkflow($workflowRecord)	{
		global $TCA;
		list($working_area_pid) = explode(",",$workflowRecord["working_area"]);
		$table=$workflowRecord["tablename"];
		$workingPage = t3lib_BEfunc::getRecord("pages",$working_area_pid);
		if (is_array($workingPage) && $TCA[$table])	{
			$data[$table]["NEW"]=array();
			$data[$table]["NEW"]["pid"]=$workingPage["uid"];

			$tce = t3lib_div::makeInstance("t3lib_TCEmain");
			$tce->stripslashes_values=0;
			$TCAdefaultOverride = $this->BE_USER->getTSConfigProp("TCAdefaults");
			if (is_array($TCAdefaultOverride))	{
				$tce->setDefaultsFromUserTS($TCAdefaultOverride);
			}
			$tce->start($data,array(),$this->BE_USER);
			$tce->admin=1;	// Set ADMIN permission for this operation.
			$tce->process_datamap();

			if ($tce->substNEWwithIDs["NEW"])	{
				return $table.":".$tce->substNEWwithIDs["NEW"];
			}
		} else {
			debug("ERROR: No working area page for workflow is defined!");
		}
	}
	function todos_processIncoming($tUid)	{
		global $LANG;

			// PROCESSING:
		$data = t3lib_div::_GP("data");
//debug($data);
//debug(t3lib_div::_GP("create_todo"));

		// ********************************
		// Instance updated
		// ********************************
		if (t3lib_div::_GP("newStatus") && is_array($data["sys_todos_users_mm"]))	{	// A status is updated
			$RD_URL="";
			reset($data["sys_todos_users_mm"]);
			while(list($key)=each($data["sys_todos_users_mm"]))	{
				$key = intval($key);
				$res = $this->exec_todos_getQueryForTodoRels(" AND sys_todos_users_mm.mm_uid=".$key, "sys_todos_users_mm.*,sys_todos.cruser_id,sys_todos.type", 1);
				if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					if ($data["sys_todos_users_mm"][$key]["status"]["code"]>0)	{
						$wF = $row["type"];
						if (substr($wF,0,3)=="wf_" && t3lib_extMgm::isLoaded("sys_workflows"))	{
							$workflowRecord=t3lib_BEfunc::getRecord("sys_workflows",substr($wF,3));
						} else {unset($workflowRecord);}

						$noUpdate=0;
	//	debug($row);

						$status_log = unserialize($row["status_log"]);
						if (!is_array($status_log))	$status_log=array();
						$statLogDat = array(
							"code" => $data["sys_todos_users_mm"][$key]["status"]["code"],
							"issuer" => $GLOBALS["BE_USER"]->user["uid"],
							"tstamp" => time(),
							"uid_foreign_before" => $row["uid_foreign"],
							"comment" => $data["sys_todos_users_mm"][$key]["status"]["comment"]
						);

						$field_values=array();
						$field_values["status"]=$data["sys_todos_users_mm"][$key]["status"]["code"];
						$field_values["tstamp"]=time();
						$field_values["is_read"]=0;	// Not used yet, but the point is that this flag is set when a target users looks at the item for the first time. This may later be used to inform the owner of the item whether it has been looked at or not. Here it's reset for every change to the log. Maybe it should be changed for each new target only.?

						// target:
						switch($field_values["status"])	{
							case 1:		// todos_status_comment, no change of target
							break;
							case 2:		// todos_status_begin, no change of target (at this point)
								if (is_array($workflowRecord) && $workflowRecord["tablename"] && !$row["rec_reference"])	{
									$recId = $this->todos_beginWorkflow($workflowRecord);
									if ($recId)	{
										$field_values["rec_reference"]=$recId;
										$RD_URL = $this->getEditRedirectUrlForReference($recId);
									} else {
										debug("ERROR: The record was not created, so either the workflow is not properly configured or the user did not have permissions to create the record (check the system log for details if this is the case)");
									}
								} else {
									debug("ERROR: No workflow record found OR no tablename defined OR there was already a record reference in the record!");
								}
							break;
							case 3:		// todos_status_end, pass on to reviewer if found (may select reviewer in form), else back to admin
								$first=0;
									// Trying to find a review user if any and apply this user instead of the owner.
								if (t3lib_extMgm::isLoaded("sys_workflows") && is_array($workflowRecord) && $workflowRecord["tablename"])	{
									$revUsers = $this->todos_getReviewUsers($workflowRecord["uid"]);
									reset($revUsers);
									while(list($u_id)=each($revUsers))	{
											// CHECK IF the submittet target user matches one of the reviewers
										if (!$first)	$first=$u_id;
										if ($u_id == $data["sys_todos_users_mm"][$key]["status"]["newTarget"])	{
											$field_values["uid_foreign"]=$u_id;
											break;
										}
									}

								}
								if (!$field_values["uid_foreign"])	{	// IF the target is NOT found yet (may have been between the submitted targets.)
									$field_values["uid_foreign"]= $first ? $first : $row["cruser_id"];	// ... select the first review user and if that is not set, select the owner
								}
							break;
							case 4:		// todos_status_passOn, just pass on to selected target
								if (intval($data["sys_todos_users_mm"][$key]["status"]["newTarget"]))	{
									$field_values["uid_foreign"]=$data["sys_todos_users_mm"][$key]["status"]["newTarget"];
								}
							break;
							case 5:		// todos_status_reject, target = sender user
								$field_values["uid_foreign"]=$row["cruser_id"];
							break;


							case 100:		// Reset status-log
								if ($this->BE_USER->user["uid"]==$row["cruser_id"])	{	// Must own
									$statLogDat["status_log_clear"]=1;
								} else {$noUpdate=1;}
							break;
							case 103:		// Finalize
								if ($this->BE_USER->user["uid"]==$row["cruser_id"])	{	// Must own
									$field_values["uid_foreign"]=$row["cruser_id"];
									$field_values["finalized"] = $this->todos_finalizeWorkflow($workflowRecord,$row) ? 1 : 0;
								} else {$noUpdate=1;}
							break;
	/*							case 101:
								if ($this->BE_USER->user["uid"]==$row["cruser_id"])	{	// Must own
									$field_values["deleted"]=1;
	//								debug("DELETE");
								}
							break;
	*/						case 102:
								if ($this->BE_USER->user["uid"]==$row["cruser_id"])	{	// Must own
									$statLogDat["uid_foreign"] = $data["sys_todos_users_mm"][$key]["status"]["newTarget"];

									$field_values = array(
										'uid_local' => $row['uid_local'],
										'uid_foreign' => $statLogDat['uid_foreign'],
										'status' => 102,
										'tstamp' => time(),
										'status_log' => serialize(array($statLogDat))
									);

									$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_todos_users_mm', $field_values);

									$noUpdate = 1;
								}
							break;
						}

						if (!$noUpdate)	{
							if (isset($field_values["uid_foreign"]))	$statLogDat["uid_foreign"] = $field_values["uid_foreign"];
							$status_log[]=$statLogDat;
							$field_values["status_log"]=serialize($status_log);

							$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_todos_users_mm", "mm_uid=".intval($key), $field_values);
						}
					}

						// Finished?
					if (isset($data["sys_todos_users_mm"][$key]["finished_instance"]) && $this->BE_USER->user["uid"]==$row["cruser_id"])	{

						$field_values = array(
							'finished_instance' => $data['sys_todos_users_mm'][$key]['finished_instance']?1:0
						);
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_todos_users_mm', 'mm_uid='.intval($key), $field_values);
					}
				}
			}


				// If redirect, do that.
			if ($RD_URL)	{
				header("Location: ".t3lib_div::locationHeaderUrl($RD_URL));
			}
		}

		// ***********************************
		// sys_todos are created/updated
		// ***********************************
		if (t3lib_div::_GP("create_todo") && is_array($data["sys_todos"]))	{	// A todo is created
			reset($data["sys_todos"]);
			$key = key($data["sys_todos"]);
			if ($key=="NEW")	{
				if ($data["sys_todos"][$key]["target_user"] && $data["sys_todos"][$key]["type"] && $data["sys_todos"][$key]["title"])	{

					$fields_values = array(
						'title' => $data['sys_todos'][$key]['title'],
						'type' => $data['sys_todos'][$key]['type'],
						'deadline' => $data['sys_todos'][$key]['deadline'],
						'description' => $data['sys_todos'][$key]['description'],
						'deleted' => 0,
						'finished' => 0,
						'tstamp' => time(),
						'crdate' => time(),
						'cruser_id' => $this->BE_USER->user['uid']
					);
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_todos', $fields_values);

						// Relation:
					if (!$GLOBALS['TYPO3_DB']->sql_error())	{
						$fields_values = array(
							'uid_local' => $GLOBALS['TYPO3_DB']->sql_insert_id(),
							'uid_foreign' => $data['sys_todos'][$key]['target_user'],
							'tstamp' => time()
						);
						$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_todos_users_mm', $fields_values);
					}

						// SEnding email notification and filling the emRec array:
					$tempQ = FALSE;
					$emRec = array();
					if ($data["sys_todos"][$key]["target_user"]>0)		{	// Ordinary user
						$tempQ = TRUE;
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,realName,email', 'be_users', 'uid='.intval($data['sys_todos'][$key]['target_user']).t3lib_BEfunc::deleteClause('be_users'));
					}
					if ($data['sys_todos'][$key]['target_user']<0)		{	// Users in group
						$tempQ = TRUE;
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,realName,email', 'be_users', $GLOBALS['TYPO3_DB']->listQuery('usergroup_cached_list', abs($data['sys_todos'][$key]['target_user']), 'be_users').t3lib_BEfunc::deleteClause('be_users'));
					}
					if ($tempQ)	{
						$sAE = t3lib_div::_GP('sendAsEmail');	// This flag must be set in order for the email to get sent
						while($brow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							$sendM=0;
							if ($sAE && strstr($brow["email"],"@") && $brow["uid"]!=$this->BE_USER->user["uid"])	{	// Send-flag must be set, the user must have an email address and finally mails are not sent to the creating user, should he be in the group.
								$this->sendEmail($brow["email"],$data["sys_todos"][$key]["title"],$data["sys_todos"][$key]["description"]);
								$sendM=1;
							}
							$emRec[] = $brow["username"].($sendM ? " (".$brow["email"].")" : "");
						}
					}
					if (count($emRec))	{	// $emRec just stores the users which is in the target group/target-user and here the list is displayed for convenience.
						$emailList=implode("<BR>&nbsp;&nbsp;",$emRec);
						$theCode.= $this->pObj->doc->section($LANG->getLL("todos_created"),$LANG->getLL("todos_created_msg")."<BR>&nbsp;&nbsp;".$emailList,0,1,1);
					}
				} else {
//					$theCode.= $this->pObj->doc->section($LANG->getLL("todos_created"),$this->errorIcon().$GLOBALS["TBE_TEMPLATE"]->rfw($LANG->getLL("todos_createdError_msg")),0,1);
				}
			} else {	// Edit todo:
				$editRow = t3lib_BEfunc::getRecordRaw("sys_todos","uid=".$key);
				if (is_array($editRow) && $editRow["cruser_id"]==$this->BE_USER->user["uid"] && $data["sys_todos"][$key]["title"])	{
					$fields_values = array(
						'title' => $data['sys_todos'][$key]['title'],
						'deadline' => $data['sys_todos'][$key]['deadline'],
						'description' => $data['sys_todos'][$key]['description'],
						'tstamp' => time()
					);
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_todos', 'uid='.intval($key), $fields_values);
				}
			}
		}


		// *************************************************
		// sys_todos / instances are updated regarding DONE
		// *************************************************
		if (t3lib_div::_GP("marked_todos"))	{
			$action = t3lib_div::_GP("marked_todos_action");
			$done = t3lib_div::_GP("DONE");
			if (is_array($done))	{
				while(list($uidKey,$value)=each($done))	{
					$uidKey = intval($uidKey);
					if ($uidKey<0)	{
						$uidKey = abs($uidKey);
						$sys_todos_users_mm_row = t3lib_BEfunc::getRecordRaw("sys_todos_users_mm","mm_uid=".$uidKey);
						if (is_array($sys_todos_users_mm_row))	{
							$sys_todos_row = t3lib_BEfunc::getRecordRaw("sys_todos","uid=".intval($sys_todos_users_mm_row["uid_local"]));
							if (is_array($sys_todos_row) && $sys_todos_row["cruser_id"]==$this->BE_USER->user["uid"])	{

								$fields_values = array(
									"finished_instance" => $value?1:0
								);
								if ($action==127 && $value)	$fields_values["deleted"]=1;

								$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_todos_users_mm", "mm_uid=".intval($uidKey), $fields_values);

									// Check if there are any sys_todos_users_mm left, which are not deleted. If there are not, delete the sys_todos item
								$isNotDeleted = t3lib_BEfunc::getRecordRaw("sys_todos_users_mm","uid_local=".intval($sys_todos_row["uid"])." AND deleted=0");
								if (!is_array($isNotDeleted))	{
										// Delete sys_todos
									$fields_values = array(
										"finished" => 1,
										"deleted" => 1
									);
									$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_todos", "uid=".intval($sys_todos_row["uid"]), $fields_values);
								}
							}
						}

					} else {
						$sys_todos_row = t3lib_BEfunc::getRecordRaw("sys_todos","uid=".intval($uidKey));
						if (is_array($sys_todos_row) && $sys_todos_row["cruser_id"]==$this->BE_USER->user["uid"])	{
							$fields_values = array("finished" => $value?1:0);
							if ($action==127 && $value)	$fields_values["deleted"] = 1;

							$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_todos", "uid=".$uidKey, $fields_values);

								// Also set status for instances, if they are checked for main item:
							if ($fields_values["deleted"])	{
								$inst_fields_values = array("deleted" => 1);

									// Update all relations to the sys_todos
								$GLOBALS['TYPO3_DB']->exec_UPDATEquery("sys_todos_users_mm", "uid_local=".intval($uidKey), $inst_fields_values);
							}
						}
					}
				}
			}
		}
		return $theCode;
	}
	function todos_workflowTitle($todoTypes,$type)	{
		if (!isset($this->todoTypesCache[$type]))	{
			if (isset($todoTypes[$type]))	{
				$this->todoTypesCache[$type] = $todoTypes[$type];
			} elseif (substr($type,0,3)=="wf_" && t3lib_extMgm::isLoaded("sys_workflows")) {
				$workflowRecord=t3lib_BEfunc::getRecord("sys_workflows",substr($type,3));
				$this->todoTypesCache[$type] = $workflowRecord["title"];
			}
		}
		return $this->todoTypesCache[$type];
	}
	function todos_displayTodo($todoTypes,$tUid)	{
		global $LANG;

		if ($tUid>0)	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_todos', 'uid='.intval($tUid).' AND cruser_id='.intval($this->BE_USER->user['uid']).' AND NOT deleted');
		} else {
			$res = $this->exec_todos_getQueryForTodoRels(" AND sys_todos_users_mm.mm_uid=".abs($tUid));
		}

		$msg = array();
		$workflowRecord = "";
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$editIcon = $row["cruser_id"]==$this->BE_USER->user["uid"] ? '<a href="index.php?sys_todos_uid='.$tUid.'&editTodo=1#todo"><img src="'.$this->backPath.'gfx/edit2.gif" width="11" height="12" vspace=2 border="0" align=top></a>' : '';
			$iconName = 'tc_todos'.($row["cruser_id"]==$this->BE_USER->user["uid"]?'':'_foreign').'.gif';
			$header = '<nobr><img src="'.$this->backPath.'gfx/i/'.$iconName.'" width="18" height="16" hspace=2 border=0 align=top title="'.$LANG->getLL("todos_item").' #'.$row["uid"].'">'.$editIcon.' <strong>'.htmlspecialchars($row["title"]).'</strong></nobr><BR>';
			$formA = array();
			$formA[] = array($LANG->getLL("todos_createdBy").":&nbsp;", $this->userGroupArray[2][$row["cruser_id"]]["username"]." (".$this->userGroupArray[2][$row["cruser_id"]]["realName"]."), ".$this->dateTimeAge($row["crdate"],-1));
			$dLine = $this->dateTimeAge($row["deadline"],-1)."&nbsp;";
			if ($row["deadline"]<time())	$dLine = '<span class="typo3-red">'.$dLine.'</span>';
			$formA[] = array($LANG->getLL("todos_deadline").":&nbsp;", $dLine);
			$formA[] = array($LANG->getLL("todos_description").":&nbsp;", nl2br($row["description"])."&nbsp;");

			if ($row["type"] && $row["type"]!="plain")	{
				$formA[]=array($LANG->getLL("todos_type").":&nbsp;", $this->todos_workflowTitle($todoTypes,$row["type"]));
				$wF = $row["type"];
				if (substr($wF,0,3)=="wf_" && t3lib_extMgm::isLoaded("sys_workflows"))	{
					$workflowRecord=t3lib_BEfunc::getRecord("sys_workflows",substr($wF,3));
					if (is_array($workflowRecord) && $workflowRecord["tablename"])	{
						$formA[]=array($LANG->getLL("todos_workflowDescr").":&nbsp;", $workflowRecord["description"]);
	//					debug($workflowRecord);
					}
				}
			}

			$msg[] = $header.$this->pObj->doc->table($formA);

			if (!t3lib_div::_GP("editTodo"))	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
								"sys_todos_users_mm.*",
								"sys_todos",
								"sys_todos_users_mm",
								"",
								"AND NOT sys_todos_users_mm.deleted AND NOT sys_todos.deleted".($tUid<0?" AND sys_todos_users_mm.mm_uid=":" AND sys_todos.uid=").abs($tUid)
							);

				// Display todo log:
				$this->insCounter=0;
				while ($rel_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					$msg[] = "<HR>".$this->todos_printStatus($rel_row,$row,$workflowRecord,$tUid,$GLOBALS['TYPO3_DB']->sql_num_rows($res));
				}
				$msg[]='<BR><input type="submit" name="newStatus" value="'.$LANG->getLL("todos_newStatus").'"> <input type="submit" name="cancel" value="'.$LANG->getLL("lCancel").'" onClick="document.editform.sys_todos_uid.value=0;">
				<input type="hidden" name="sys_todos_uid" value="'.$tUid.'"><BR>';
			}

			if (count($msg))	{
				$theCode.= $this->pObj->doc->spacer(20);
				$theCode.= $this->pObj->doc->section('<a name="todo"></a>'.$LANG->getLL("todo_details",1),implode("",$msg),0,1,0,1);
			}

				// Edit form:
			if (t3lib_div::_GP("editTodo") && $row["cruser_id"]==$this->BE_USER->user["uid"])	{
				$theCode.=$this->todos_createForm($todoTypes,$row);
			}
		}
		return $theCode;
	}
	function todos_printStatus($rel_row,$todo_row,$workflow_row,$tUid,$countOfInstances=0)	{
		global $LANG, $TCA;

		if (t3lib_extMgm::isLoaded("sys_workflows") && is_array($workflow_row))	{
			$revUsers = $this->todos_getReviewUsers($workflow_row["uid"]);
		} else $revUsers=array();

		$noExtraFields=0;

		$theCode="";
		$statusLabels = array();
		$statusLabels[1]=htmlspecialchars($LANG->getLL("todos_status_comment"));
		$statusLabels[2]=htmlspecialchars($LANG->getLL("todos_status_begin"));
		$statusLabels[3]=htmlspecialchars($LANG->getLL("todos_status_end"));
		$statusLabels[4]=htmlspecialchars($LANG->getLL("todos_status_passOn"));
		$statusLabels[5]=htmlspecialchars($LANG->getLL("todos_status_reject"));

		$statusLabels[100]=htmlspecialchars($LANG->getLL("todos_status_resetStatus"));
		$statusLabels[103]=htmlspecialchars($LANG->getLL("todos_status_finalize"));
//		$statusLabels[101]=htmlspecialchars($LANG->getLL("todos_status_delete"));
		$statusLabels[102]=htmlspecialchars($LANG->getLL("todos_status_newInstance"));

		$allowedTargetCodes = "2,3,4,102";

		$this->insCounter++;
		//$this->insCounter.' /
		$iSt = ' ('.$LANG->getLL("todos_instance").' #'.$rel_row["mm_uid"].')';

		$theCode.='<BR><strong>'.$LANG->getLL("todos_logEntries").$iSt.':</strong><BR>';
		$log = unserialize($rel_row["status_log"]);
		$prevUsersGroups=array();
		if (is_array($log))	{
			$lines=array();

			reset($log);
			$c=0;
			while(list(,$logDat)=each($log))	{
				$prevUsersGroups[] = $logDat["uid_foreign_before"];
		//		debug($logDat);
				if ($logDat["status_log_clear"])	{
					$c=0;
					$lines=array();
				}

				$c++;
				$bgColorClass=($c+1)%2 ? 'bgColor' : 'bgColor-10';
				$lines[]='<tr class="'.$bgColorClass.'">
					<td valign=top nowrap width=10%>'.
						'<strong>'.$statusLabels[$logDat["code"]].'</strong><BR>'.
						$this->printUserGroupName($logDat["issuer"]).'<BR>'.
						$this->dateTimeAge($logDat["tstamp"],1).'<BR>'.
						(isset($logDat["uid_foreign"]) ? "<em>".$LANG->getLL("todos_logEntries_lTarget")."</em>:<BR>".$this->printUserGroupName($logDat["uid_foreign"]).'<BR>' : '').
					'<BR></td>
					<td valign=top>&nbsp;&nbsp;&nbsp;</td>
					<td valign=top width=80%>'.nl2br(htmlspecialchars($logDat["comment"])).'</td>
				</tr>';
			}

			array_unshift($lines,'<tr class="bgColor5">
				<td nowrap><strong>'.$LANG->getLL("todos_logEntries_lStatusInfo").':</strong></td>
				<td>&nbsp;</td>
				<td nowrap><strong>'.$LANG->getLL("todos_logEntries_lDetails").':</strong></td>
			</tr>');

			$theCode.= '<table border=0 cellpadding=0 cellspacing=0 width="100%">'.implode("",$lines).'</table>';
		} else {
			$theCode.=$LANG->getLL("todos_logEntries_msg1").'<br>';
		}
		if ($rel_row["uid_foreign"])	{
			$theCode.='<BR>'.$LANG->getLL("todos_logEntries_lTargetedAt").': <strong>'.$this->printUserGroupName($rel_row["uid_foreign"],1).'</strong><BR>';
		} else {
			$theCode.='<BR><strong>'.$LANG->getLL("todos_logEntries_lnoTarget").'</strong>';
		}

		if ($rel_row["rec_reference"])	{
			$theCode.="<BR>".$LANG->getLL("todos_logEntries_lRecordRelated").':<br>';
			$recRelParts = explode(":",$rel_row["rec_reference"]);
			$recordRelated=t3lib_BEfunc::getRecord($recRelParts[0],$recRelParts[1]);
			if ($recordRelated)	{
				$theCode.='<a href="'.$this->getEditRedirectUrlForReference($rel_row["rec_reference"]).'"><img src="'.$this->backPath.'gfx/edit2.gif" width="11" height="12" hspace=3 vspace=2 border="0" align=top></a>'.
							($recRelParts[0]=="pages" ? $this->pObj->doc->viewPageIcon($recRelParts[1],$this->backPath,'align=top hspace=2') : "").
							t3lib_iconWorks::getIconImage($recRelParts[0],$recordRelated,$this->backPath,' align="top" hspace="2"').
							htmlspecialchars($recordRelated[$TCA[$recRelParts[0]]["ctrl"]["label"]]);
			} else {
				$theCode.='<span class="typo3-red"><strong>'.$LANG->getLL("todos_errorNoRecord").'</strong></span>';
			}

		}
		$theCode.="<BR><BR>";

		// ****************************
		// Status selector
		// ****************************
		$opt=Array();
		$statusLabels_copy = $statusLabels;

			// Unset certain items which we do not present in the menu under certain circumstances.
		if (!is_array($workflow_row) || !$workflow_row["tablename"] || $rel_row["rec_reference"])		unset($statusLabels_copy[2]);
		if (!is_array($workflow_row) || !$workflow_row["tablename"] || !$rel_row["rec_reference"])		unset($statusLabels_copy[103]);
		if (!count($revUsers) || (!$rel_row["rec_reference"] && is_array($workflow_row)))	unset($statusLabels_copy[3]);
		if ($this->BE_USER->user["uid"]!=$todo_row["cruser_id"])	{
			unset($statusLabels_copy[100]);
//			unset($statusLabels_copy[101]);
			unset($statusLabels_copy[102]);
			unset($statusLabels_copy[103]);
		} else {
			unset($statusLabels_copy[5]);
		}

		if ($tUid<0)	unset($statusLabels_copy[102]);		// Don't allow new instance creation if we're not looking at the TODO item but an instance.

			// If finalized:
		if ($rel_row["finalized"])	{
			$statusLabels_copy = array();
			if ($this->BE_USER->user["uid"]==$todo_row["cruser_id"])	{
//				$statusLabels_copy[101] = $statusLabels[101];
				$statusLabels_copy[102] = $statusLabels[102];
				$noExtraFields=1;
			}
		}

		$formCodeAccu="";
		if (count($statusLabels_copy))	{
			reset($statusLabels_copy);
			if ($countOfInstances>1 || $this->BE_USER->user["uid"]==$todo_row["cruser_id"])	$opt[]='<option value="0"></option>';
			while(list($kk,$vv)=each($statusLabels_copy))	{
				$opt[]='<option value="'.$kk.'">'.$vv.'</option>';
			}
				$onChange="var allowedCodes=',".$allowedTargetCodes.",';
					if (allowedCodes.indexOf(','+this.options[this.selectedIndex].value+',')==-1) {
						document.editform['data[sys_todos_users_mm][".$rel_row["mm_uid"]."][status][newTarget]'].selectedIndex=0;
					}";
			$formCodeAccu.=htmlspecialchars($LANG->getLL("todos_status_addStatus")).':<BR><select name="data[sys_todos_users_mm]['.$rel_row["mm_uid"].'][status][code]" onChange="'.$onChange.'">'.implode("",$opt).'</select><BR>';

			if (!$noExtraFields)	{
				$opt=Array();
				$opt[]='<option value="0"></option>';
//				$opt[]='<option value="0">[ '.htmlspecialchars($LANG->getLL("todos_selectTargetUG")).' ]</option>';

					// Sender
				$revUserRec = t3lib_BEfunc::getRecord("be_users",$todo_row["cruser_id"]);
				$opt[]='<option value="'.$todo_row["cruser_id"].'">'.htmlspecialchars($LANG->getLL("todos_sender").': '.$revUserRec["username"].($revUserRec["realName"]?" (".$revUserRec["realName"].")":"")).'</option>';

					// Review users:
				reset($revUsers);
				while(list($u_id,$revUserRec)=each($revUsers))	{
						// CHECK IF they
					$opt[]='<option value="'.$u_id.'">'.htmlspecialchars($LANG->getLL("todos_reviewer").': '.$revUserRec["username"].($revUserRec["realName"]?" (".$revUserRec["realName"].")":"")).'</option>';
				}

				// Users through time:
				$prevUsersGroups[]=$this->BE_USER->user["uid"];
				$prevUsersGroups[]=$rel_row["uid_foreign"];
				if (is_array($prevUsersGroups) && count($prevUsersGroups))	{
					$opt[]='<option value="0"></option>';
					$opt[]='<option value="0">'.htmlspecialchars($LANG->getLL("todos_pastUG")).'</option>';
					$prevUsersGroups = array_unique($prevUsersGroups);
					reset($prevUsersGroups);
					while(list(,$UGuid)=each($prevUsersGroups))	{
						if ($UGuid)		$opt[]='<option value="'.$UGuid.'">'.htmlspecialchars(($UGuid>0?$LANG->getLL("todos_user"):$LANG->getLL("todos_group")).": ".$this->printUserGroupName($UGuid)).'</option>';
					}
				}

				if ($this->BE_USER->user["uid"]==$todo_row["cruser_id"])	{
					$opt[]='<option value="0"></option>';
					$opt[]='<option value="0">'.htmlspecialchars($LANG->getLL("todos_allUG")).'</option>';

					if ($todo_row["type"]=="plain")	{
						$opt = array_merge($opt,$this->tasks_makeTargetSelector($this->userGroupArray[0],$this->userGroupArray[1],0,1));
					} elseif (is_array($workflow_row))	{
						$grL = implode(",",t3lib_div::intExplode(",",$workflow_row["target_groups"]));
						$wf_groupArray=t3lib_BEfunc::getGroupNames("title,uid","AND uid IN (".($grL?$grL:0).")");
						$wf_userArray = t3lib_BEfunc::blindUserNames($this->userGroupArray[2],array_keys($wf_groupArray),1);
						$opt = array_merge($opt,$this->tasks_makeTargetSelector($wf_userArray,$wf_groupArray,0,1));
					}
				}

				$onChange="var allowedCodes=',".$allowedTargetCodes.",';
					if (allowedCodes.indexOf(
						','
						+ document.editform['data[sys_todos_users_mm][".$rel_row["mm_uid"]."][status][code]'].options[document.editform['data[sys_todos_users_mm][".$rel_row["mm_uid"]."][status][code]'].selectedIndex].value
						+',')==-1 || this.options[this.selectedIndex].value==0) {
							this.selectedIndex=0;
						}";
				$formCodeAccu.=htmlspecialchars($LANG->getLL("todos_status_selectTarget")).':<BR><select name="data[sys_todos_users_mm]['.$rel_row["mm_uid"].'][status][newTarget]" onChange="'.$onChange.'">'.implode("",$opt).'</select><BR>';

				$formCodeAccu.=htmlspecialchars($LANG->getLL("todos_statusNote")).':<BR><textarea rows="10" name="data[sys_todos_users_mm]['.$rel_row["mm_uid"].'][status][comment]"'.$this->pObj->doc->formWidthText(40,"","").'></textarea><BR>';
			}
		}
		if ($this->BE_USER->user["uid"]==$todo_row["cruser_id"])	{
			$formCodeAccu.='<input type="hidden" name="data[sys_todos_users_mm]['.$rel_row["mm_uid"].'][finished_instance]" value="0"><input type="checkbox" name="data[sys_todos_users_mm]['.$rel_row["mm_uid"].'][finished_instance]" value="1"'.($rel_row["finished_instance"]?" checked":"").'>'.
				htmlspecialchars($LANG->getLL("todos_finished"));
		}


		return $theCode.$formCodeAccu;
	}
	function negateList($list)	{
		$listArr = explode(",",$list);
		while(list($k,$v)=each($listArr))	{
			$listArr[$k]=$v*-1;
		}
//		debug(implode(",",$listArr));
		return implode(",",$listArr);
	}
	function exec_todos_getQueryForTodoRels($extraWhere="",$selectFields="",$allowOwn=0)	{
		$groupQ = $this->BE_USER->user["usergroup_cached_list"] ? " OR sys_todos_users_mm.uid_foreign IN (".$this->negateList($this->BE_USER->user["usergroup_cached_list"]).")" : "";
		if ($allowOwn)	$groupQ.=" OR sys_todos.cruser_id=".intval($this->BE_USER->user["uid"]);

		return $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
					$selectFields?$selectFields:"sys_todos.*,sys_todos_users_mm.mm_uid,sys_todos_users_mm.uid_foreign,sys_todos_users_mm.finished_instance",
					"sys_todos",
					"sys_todos_users_mm",
					"",
					" AND (sys_todos_users_mm.uid_foreign=".intval($this->BE_USER->user["uid"]).$groupQ.")".	// UID foreign must match the current users id OR be within the group-list of the user
						" AND sys_todos.deleted=0 AND sys_todos_users_mm.deleted=0".		// Todo AND it's relation must not be deleted
						" AND ((sys_todos.finished=0 AND sys_todos_users_mm.finished_instance=0) OR sys_todos.cruser_id=".intval($this->BE_USER->user["uid"]).")".	// Either the user must own his todo-item (in which case finished items are displayed) OR the item must NOT be finished (which will remove it from others lists.)
						$extraWhere,
					'',
					'sys_todos.deadline'
				);	// Sort by deadline
	}
	function todos_getReviewUsers($wfUid)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						"be_users.uid,be_users.username,be_users.realName",
						"sys_workflows",
						"sys_workflows_rvuser_mm",
						"be_users",
						t3lib_BEfunc::deleteClause("be_users").t3lib_BEfunc::deleteClause("sys_workflows")." AND sys_workflows.uid=".intval($wfUid),
						'',
						'be_users.username'
					);
		$outARr=array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$outARr[$row["uid"]]=$row;
		}
		return $outARr;
	}
	function todos_displayLists($todoTypes,$tUid)	{
		global $LANG;

		$lines=array();
		$ownCount=0;

			$lines[]='<tr>
				<td class="bgColor5">&nbsp;</td>
				<td class="bgColor5" width=50%><strong>'.$LANG->getLL("todos_title").':</strong></td>
				<td class="bgColor5" width=25%><strong>'.$LANG->getLL("todos_type").':</strong></td>
				<td class="bgColor5" width=25%><strong>'.$LANG->getLL("todos_deadline").':</strong></td>
				<td class="bgColor5"><strong>'.$LANG->getLL("todos_finished").':</strong></td>
			</tr>';

			// SELECT Incoming todos (incl. own todos):
			// Incoming todos are those set for a user which he must carry out. Those are the relations in sys_todos_users_mm where uid_foreign is either the users uid or the negative uid of a group his a member of
		$res = $this->exec_todos_getQueryForTodoRels();
		$out = "";
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{

			$c=0;
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$c++;
				if ($tUid==-$row["mm_uid"])	{
					$bTb="<B>";
					$bTe="</B>";
					$active='<img src="'.$this->backPath.'gfx/content_client.gif" width="7" height="10" border="0" align=top>';
				} else {
					$bTb=$bTe="";
					$active="";
				}
				$t_dL=$this->dateTimeAge($row["deadline"],-1);
				$t_dL = ($row["deadline"]>time()) ? $t_dL : '<span class="typo3-red">'.$t_dL.'</span>';
				$iconName = 'tc_todos'.($row["cruser_id"]==$this->BE_USER->user["uid"]?'':'_foreign').($row["uid_foreign"]>=0?'':'_group').'.gif';
				$bgColorClass=($c+1)%2 ? 'bgColor' : 'bgColor-10';
				$lines[]='<tr>
					<td class="'.$bgColorClass.'">'.$this->linkTodos('<img src="'.$this->backPath.'gfx/i/'.$iconName.'" width="18" height="16" hspace=2 border=0 title="'.$LANG->getLL("todos_instance").' #'.$row["mm_uid"].',  '.htmlspecialchars($LANG->getLL("todos_createdBy").": ".$this->userGroupArray[2][$row["cruser_id"]]["username"]." (".$this->userGroupArray[2][$row["cruser_id"]]["realName"].")").'">',-$row["mm_uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>'.$this->linkTodos($active.$bTb."&nbsp;".$this->fixed_lgd($row["title"])."&nbsp;".$bTb,-$row["mm_uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>&nbsp;'.t3lib_div::fixed_lgd_cs($this->todos_workflowTitle($todoTypes,$row["type"]),15).'&nbsp;</td>
					<td class="'.$bgColorClass.'" nowrap>'.$t_dL.'&nbsp;</td>
					<td class="'.$bgColorClass.'" align=right>'.($row["cruser_id"]==$this->BE_USER->user["uid"]?'<input type="hidden" name="DONE['.-$row["mm_uid"].']" value=0><input type="checkbox" name="DONE['.-$row["mm_uid"].']" value="1"'.($row["finished_instance"]?" checked":"").'>':'&nbsp;').'</td>
				</tr>';

				if ($row["cruser_id"]==$this->BE_USER->user["uid"])	$ownCount++;
			}
		}



			// SELECT Master todos for list:
			// A master todo is an OUTGOING todo you have created, in this case for other users.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
					"sys_todos.*,sys_todos_users_mm.uid_foreign",
					"sys_todos",
					"sys_todos_users_mm",
					"",
					" AND sys_todos_users_mm.uid_foreign!=".intval($this->BE_USER->user["uid"]).
							" AND sys_todos.cruser_id=".intval($this->BE_USER->user["uid"]).
							" AND sys_todos.deleted=0",
					'sys_todos.uid',
					'sys_todos.deadline'
				);
		$out = "";
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{

			$lines[]='<tr><td colspan=5>&nbsp;</td></tr>';
			$lines[]='<tr>
				<td class="bgColor5">&nbsp;</td>
				<td class="bgColor5" colspan="4"><strong>'.$LANG->getLL("todos_list_master").':</strong></td>
			</tr>';

			$c=0;
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$c++;
				$bgColorClass=($c+1)%2 ? 'bgColor' : 'bgColor-10';
				if ($tUid==$row["uid"])	{
					$bTb="<B>";
					$bTe="</B>";
					$active='<img src="'.$this->backPath.'gfx/content_client.gif" width="7" height="10" border="0" align=top>';
				} else {
					$bTb=$bTe="";
					$active="";
				}
				$t_dL=$this->dateTimeAge($row["deadline"],-1);
				$t_dL = ($row["deadline"]>time()) ? $t_dL : '<span class="typo3-red">'.$t_dL.'</span>';
				$iconName = 'tc_todos'.($row["uid_foreign"]>=0?'':'_group').'.gif';
				$bgColorClass=($c+1)%2 ? 'bgColor' : 'bgColor-10';
				$lines[]='<tr>
					<td class="'.$bgColorClass.'">'.$this->linkTodos('<img src="'.$this->backPath.'gfx/i/'.$iconName.'" width="18" height="16" hspace=2 border=0 title="'.$LANG->getLL("todos_item").' #'.$row["uid"].', '.htmlspecialchars($LANG->getLL("todos_createdBy").": ".$this->userGroupArray[2][$row["cruser_id"]]["username"]." (".$this->userGroupArray[2][$row["cruser_id"]]["realName"].")").'">',$row["uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>'.$this->linkTodos($active.$bTb."&nbsp;".$this->fixed_lgd($row["title"])."&nbsp;".$bTb,$row["uid"]).'</td>
					<td class="'.$bgColorClass.'" nowrap>&nbsp;'.t3lib_div::fixed_lgd_cs($this->todos_workflowTitle($todoTypes,$row["type"]),15).'&nbsp;</td>
					<td class="'.$bgColorClass.'" nowrap>'.$t_dL.'&nbsp;</td>
					<td class="'.$bgColorClass.'" align=right>'.($row["cruser_id"]==$this->BE_USER->user["uid"]?'<input type="hidden" name="DONE['.$row["uid"].']" value=0><input type="checkbox" name="DONE['.$row["uid"].']" value="1"'.($row["finished"]?" checked":"").'>':'&nbsp;').'</td>
				</tr>';

				if ($row["cruser_id"]==$this->BE_USER->user["uid"])	$ownCount++;
			}

//			$out = '<table border=0 cellpadding=0 cellspacing=0>'.implode("",$lines).'</table>';
//			$theCode.= $this->pObj->doc->spacer(10);
//			$theCode.= $this->pObj->doc->section($LANG->getLL("todos_list_master"),$out,1,0);
		}

		if (count($lines)>1)	{
			$out = '<table border=0 cellpadding=0 cellspacing=0>'.implode("",$lines).'</table>';

			if ($ownCount)	{
				$bMenu = '<BR><div align=right><select name="marked_todos_action">
				<option value=-1></option>
				<option value=127>'.$LANG->getLL("todos_purge").'</option>
				</select><input type="submit" name="marked_todos" value="'.$LANG->getLL("todos_updateMarked").'"></div>';
			} else $bMenu = "";


			$theCode.= $this->pObj->doc->section($LANG->getLL("todos_list"),$out.$bMenu,0,1);
		}
		return $theCode;
	}
	function todos_createForm($todoTypes,$editRec="")	{
		global $LANG;

			// CREATE/EDIT/VIEW TODO:
		$wF = is_array($editRec) ? $editRec["type"] : t3lib_div::_GP("workflow_type");
		if ($wF && isset($todoTypes[$wF]))	{
			$type = is_array($editRec) ? $editRec["uid"] : "NEW";
			$formA=array();

			if (!is_array($editRec))	{
					// Making the target_user/group selector:
				if ($wF=="plain")	{		// If the type is plain, the todo-item may be assigned to all users accessible for the current user.
						// Title selector:
					$formA[]=array($LANG->getLL("todos_type").":&nbsp;", $LANG->getLL("todos_plain"));
					$formA[] = $this->tasks_makeTargetSelector($this->userGroupArray[0],$this->userGroupArray[1],$type);

				} elseif (substr($wF,0,3)=="wf_" && t3lib_extMgm::isLoaded("sys_workflows"))	{	// If it's a workflow from sys_workflows table, the list of target groups and users is re-fetched, according the the target_groups definition.
					$workflowRecord=t3lib_BEfunc::getRecord("sys_workflows",substr($wF,3));
					if (is_array($workflowRecord) && $workflowRecord["tablename"])	{
						$formA[]=array($LANG->getLL("todos_type").":&nbsp;", $workflowRecord["title"]);
							// Get groupnames for todo-tasks

						$grL = implode(",",t3lib_div::intExplode(",",$workflowRecord["target_groups"]));
						$wf_groupArray=t3lib_BEfunc::getGroupNames("title,uid","AND uid IN (".($grL?$grL:0).")");
						$wf_groupArrayUids=array_keys($wf_groupArray);
						$wf_userArray = t3lib_BEfunc::blindUserNames($this->userGroupArray[2],$wf_groupArrayUids,1);
						$formA[] = $this->tasks_makeTargetSelector($wf_userArray,$wf_groupArray,$type);
					}
				}
			}

				// Title selector:
			$formA[]=array($LANG->getLL("todos_title").":&nbsp;", '<input type="text" name="data[sys_todos]['.$type.'][title]" value="'.htmlspecialchars(is_array($editRec)?$editRec["title"]:$todoTypes[$wF]).'" max=255'.$this->pObj->doc->formWidth(40).'>');

				// Deadline selector:
			$curTodoTime=time();
			$formA[]=array($LANG->getLL("todos_deadline").":&nbsp;", '<input type="text" name="data[sys_todos]['.$type.'][deadline]_hr'.'" onChange="typo3FormFieldGet(\'data[sys_todos]['.$type.'][deadline]\', \'datetime\', \'\', 0,0);"'.$this->pObj->doc->formWidth(20).'>
			<input type="hidden" value="'.intval($editRec["deadline"]).'" name="data[sys_todos]['.$type.'][deadline]">
			<select name="_time_selector" onChange="
				document.forms[0][\'data[sys_todos]['.$type.'][deadline]\'].value=(this.options[this.selectedIndex].value>0?this.options[this.selectedIndex].value:(document.forms[0][\'data[sys_todos]['.$type.'][deadline]\'].value!=\'0\'?document.forms[0][\'data[sys_todos]['.$type.'][deadline]\'].value:'.time().')-this.options[this.selectedIndex].value);
				this.selectedIndex=0;
				typo3FormFieldSet(\'data[sys_todos]['.$type.'][deadline]\', \'datetime\', \'\', 0,0);
			">
				<option value="0"></option>
				<option value="'.(mktime(0,0,0)+3600*12).'">'.$LANG->getLL("todos_DL_today").'</option>
				<option value="'.(mktime(0,0,0)+3600*24+3600*12).'">'.$LANG->getLL("todos_DL_tomorrow").'</option>
				<option value="'.(mktime(0,0,0)+3600*24*7+3600*12).'">'.$LANG->getLL("todos_DL_weekLater").'</option>
				<option value="'.(mktime(0,0,0)+3600*24*31+3600*12).'">'.$LANG->getLL("todos_DL_monthLater").'</option>
				<option value="'.(-3600*24*1).'">+1 '.$LANG->getLL("todos_DL_day").'</option>
				<option value="'.(-3600*24*2).'">+2 '.$LANG->getLL("todos_DL_days").'</option>
				<option value="'.(-3600*24*4).'">+4 '.$LANG->getLL("todos_DL_days").'</option>
				<option value="'.(-3600*24*7).'">+7 '.$LANG->getLL("todos_DL_days").'</option>
				<option value="'.(-3600*24*14).'">+14 '.$LANG->getLL("todos_DL_days").'</option>
				<option value="'.(-3600*24*31).'">+31 '.$LANG->getLL("todos_DL_days").'</option>
			</select>
			');

			$t3lib_TCEforms = t3lib_div::makeInstance("t3lib_TCEforms");
			$t3lib_TCEforms->backPath = $this->backPath;

			$t3lib_TCEforms->extJSCODE.='typo3FormFieldSet("data[sys_todos]['.$type.'][deadline]", "datetime", "", 0,0);';

				// Description:
			$formA[]=array($LANG->getLL("todos_description").":&nbsp;", '<textarea rows="10" name="data[sys_todos]['.$type.'][description]"'.$this->pObj->doc->formWidthText(40,"","").'>'.t3lib_div::formatForTextarea(is_array($editRec)?$editRec["description"]:"").'</textarea>');

				// Notify email:
			if (!is_array($editRec) && $this->BE_USER->user["email"])	{
				$formA[]=array("&nbsp;", '<input type="checkbox" name="sendAsEmail" value=1>'.$LANG->getLL("todo_email").'<BR>('.$LANG->getLL("lReplyAddress").': '.$this->BE_USER->user["email"].')');
			}

			$formA[]=array("&nbsp;","&nbsp;");
			$onClick = "if (document.forms[0]['data[sys_todos][".$type."][title]'].value=='') {alert(".$GLOBALS['LANG']->JScharCode($LANG->getLL("todos_mustFillIn")).");return false;}";
			$hidden = '<input type=hidden name="data[sys_todos]['.$type.'][type]" value="'.htmlspecialchars($wF).'">';
			if ($type=="NEW")	{
				$formA[]=array("&nbsp;", '<input type="submit" name="create_todo" value="'.$LANG->getLL("lCreate").'" onClick="'.$onClick.'"> <input type="submit" value="'.$LANG->getLL("lCancel").'">');
			} else {
				$formA[]=array("&nbsp;", '<input type="submit" name="create_todo" value="'.$LANG->getLL("lUpdate").'"><input type="hidden" name="sys_todos_uid" value="'.$editRec["uid"].'">');
			}

			$theCode.= $this->pObj->doc->spacer(20);
			$theCode.= $this->pObj->doc->section('<a name="new"></a>'.$LANG->getLL(is_array($editRec)?"todos_update":"todos_new",1),$this->pObj->doc->table($formA).$hidden.$t3lib_TCEforms->JSbottom(),0,!is_array($editRec),0,1);
		} else {
			// Todo type:
			$opt_type=array();
			reset($todoTypes);
			$opt_type[]='<option value="0"></option>';
			while(list($uid,$title)=each($todoTypes))	{
				$opt_type[]='<option value="'.$uid.'">'.htmlspecialchars($title).'</option>';
			}
//				$type_onChange="if (document.forms[0]['data[sys_todos][".$type."][title]'].value=='') document.forms[0]['data[sys_todos][".$type."][title]'].value=document.forms[0]['data[sys_todos][".$type."][type]'].options[document.forms[0]['data[sys_todos][".$type."][type]'].selectedIndex].text;";
			$formA=array();
			$formA[]=array($LANG->getLL("todos_type").":&nbsp;", '<select name="workflow_type" onChange="document.location=\'index.php?workflow_type=\'+this.options[this.selectedIndex].value+\'#new\';">'.implode("",$opt_type).'</select>');
			$theCode.= $this->pObj->doc->spacer(20);
			$theCode.= $this->pObj->doc->section($LANG->getLL("todos_new"),$this->pObj->doc->table($formA),0,1);
		}
		return $theCode;
	}
	function linkTodos($str,$id)	{
		$str='<a href="index.php?sys_todos_uid='.$id.'#todo">'.$str.'</a>';
		return $str;
	}
	function getEditRedirectUrlForReference($recRef)	{
		$parts = explode(":",$recRef);
		if ($parts[0]=="pages")	{
			$outUrl = $this->backPath."sysext/cms/layout/db_layout.php?id=".$parts[1]."&SET[function]=0&edit_record=".$parts[0].":".$parts[1]."&returnUrl=".rawurlencode(t3lib_div::getIndpEnv("REQUEST_URI"));
		} else {
			$outUrl = $this->backPath."alt_doc.php?returnUrl=".rawurlencode(t3lib_div::getIndpEnv("REQUEST_URI"))."&edit[".$parts[0]."][".$parts[1]."]=edit";
		}
		return $outUrl;
	}
	function printUserGroupName($uid,$icon=0)	{
		if ($uid>0)	{
			return ($icon?t3lib_iconWorks::getIconImage("be_users",t3lib_BEfunc::getRecord("be_users",$uid),$this->backPath,$params=" align=top"):"").
				$this->userGroupArray[2][$uid]["username"].($this->userGroupArray[2][$uid]["realName"]?" (".$this->userGroupArray[2][$uid]["realName"].")":"");
		} else {
			$grRec = t3lib_BEfunc::getRecord("be_groups",abs($uid));
			return ($icon?t3lib_iconWorks::getIconImage("be_groups",$grRec,$this->backPath,' align="top"'):'').
				$grRec["title"];
		}
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_todos/class.tx_systodos.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sys_todos/class.tx_systodos.php"]);
}

?>