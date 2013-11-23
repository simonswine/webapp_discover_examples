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
 * Class: Freesite
 * 
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

class freesite_admin {

		// internal
	var $data = array();	// Incoming data
	var $check = array();	// Tells which fields are still not OK.
	var $fieldsArray=array();
	var $HIDDEN_FIELDS="";	// Used to accumulate hidden-fields
	var $ALL_OK="";			// This flag is set IF data has been input and they verify OK!
	var $verificationOK="";	// If this flag is set, then we're going to create the user!!!
	var $verificationCode="";	
	var $noDomainURL="";	// noDomainUrl is now the url (without http://) to the site. It's prepended with a slash.

		// Internal - create
	var $groupTemplate = "";		// The users own group will be modelled after this group (be a copy)
	var $groupGeneral = "";		// The user becomes a member of this group, which should be about general configuration for all users.
	var $newsite_page = "";
	var $groupData=array();
	var $userData=array();
	var $user_uid = "";
	var $userGroup_uid = "";
	
		// ***********************
		// Default Configuration:
		// ***********************
	var $sysConfig = Array (
		"backendOnly" => 1,					// Boolean: If set, then the module will check for a backend login. If false, everyone can create a new site with the module at typo3conf/freesite/
		"emailHeader" => "From: TYPO3 Freesite Creator <>",		// Header data for the emails send from the Freesite module
			// **********************************
			// ENTERING DATA:
			// **********************************
			// Configuration of features and how data is processed.
		"pid_templateArchive" => 0,			// PID: The PID (pages-uid) of the template archive. If 0 (zero), no template is created for the new site. If archive has no templates in it, the site gets an empty template. The archive folder should be a "sysFolder" -> In the template archive you should put template-records that you want the new websites to be "based on". Templates in subpages in the archive are also selectable. 
		"pid_dummyPagesArchive" => 0,		// PID: The PID of the dummy page archive. If 0 (zero), no dummy pages are created for the new site. REMEMBER that the user MUST have read access to the dummy pages. Any pages, the user cannot read are (of course) not copied! The archive folder should be a "sysFolder" -> In the dummy page archive you should create pages with subpages that represent initial page structures for new sites created. Every page in the root of the dummy page archive represents a page-structure, and only sub- and subsub-pages are copied.
		"exclude_templateSelect" => 0,		// Boolean: If set, you will NOT be able to select a template with this module. The default template however will be the first template found in the archive.
		"exclude_dummyPages" => 0,			// Boolean: If set, you will NOT be able to select a dummy page set with this module. The default dummy page set will be the first set found in the archive.
		"previewData" => 0,					// Boolean: If set, the data entered in the module will be displayed for preview first! Previewing the entered data is a state between creation and modification of data, where the data is not editable and presented as ordinary text on the screen before final approval. Preview ENABLES the use of emailVerification... + the label "label_Preview"
		"emailVerification" => 1,			// Boolean: If set AND if previewData is enabled, the preview state of the module will send an email to the entered email-address with a verification-code. This code must be copy/pasted into a form-field and if the code is correct, the site will be created. This feature provides a way to verify the email-address of the user that is creating a website.
		"emailVerificationSubject" => "Freesite Verification Code",	// String: Subject of the verification code email
		"emailVerificationMessage" => "This is the verification code from the automatic site-creation tool. Just enter this code into the code-field on the website.",	// String: Body of the verification code email
		"virtualDirRequired" => 0,			// Boolean: If set, virtual directory MUST be entered!
		"defaultLanguage" => "",			// Default value for the language if not sent with form. Values must be from the current selection of TYPO3 language keys.
	
		"defaultFormField_sitetitle" => "TITLE",			// Name of the form-field (eg. "TITLE" for the default field), which content should be made the site-title of the template in case it's not set.
	
			// Template, HTML-formatting, messages and labels
		"templateFile" => "",				// Filereference: (relative to sitePath) reference to a custom html-file to use as template. Default is "template.html". Look in this file to see how it works... (###.....### marks are substituted)
		"templateFile_selectTemplate" => "",	// Filereference: (relative to sitePath) reference to a custom html-file to use as template for display of template options. Default is "template.html". Look in this file to see how it works... (###.....### marks are substituted)
		"templateFile_selectPages" => "",		// Filereference: (relative to sitePath) reference to a custom html-file to use as template for display of page options. Default is "template.html". Look in this file to see how it works... (###.....### marks are substituted)
		"inputFieldParams" => 'size=55',	// String: HTML <input>-tag parameters for the form. Primarily used to set the size/max of all the form-fields.
		"notCorrectHTML" => '<BR><img src="../../../t3lib/gfx/icon_fatalerror.gif" width=18 height=16 align=top> ',	// HTML-code that displays a warning that this field has not been correctly filled
		"msgBadUsername" => 'The username is too short or it exists allready. Please enter another one.',
		"msgBadPassword" => 'The password is too short.',
		"msgBadEmail" => 'This is not a valid email address.',
		"msgBadPath" => 'This directory is in use allready (or not allowed).',
		"msgBadDomain" => 'This domain-name is not valid.',
		"msgBadDomain_used" => 'The domain is allready used in TYPO3',
		"msgBadValue" => 'This field is required!',
		"msgEmailVerificationCode" => '<B>EMAIL Verification:</b><BR>A verification code has been send to <i>%s</i>. Read your mail and enter the code in this field in order to continue!<BR>',		// String: The content of this variable is displayed as a message before the field in which the code should be entered.
		"label_Update" => 'Update information',
		"label_Return" => 'Make changes',
		"label_Preview" => 'Freeze information',		
		"label_Create" => 'Create site!',
	
		"templateFile_selectTemplate" => "",	// Filereference: (relative to sitePath) reference to a custom html-file to use as template for display of template options. Default is "template.html". Look in this file to see how it works... (###.....### marks are substituted)
		"templateFile_selectPages" => "",		// Filereference: (relative to sitePath) reference to a custom html-file to use as template for display of page options. Default is "template.html". Look in this file to see how it works... (###.....### marks are substituted)
		"templateSelect_noInfoLink" => 0,	// Boolean: If set, there will be NO info-link at each of the templates in the list.
		
			// **********************************
			// CREATE SITE:
			// **********************************
			// ID's
		"testingOnly" => 0,						// Boolean. If set, NOTHING is put in the database and no directories are created. Just testing the submitted values (which are printed with the debug()-function)
		"pid_newsite" => 0,						// PID: The pages-uid under which new sites are created! If this is NOT set and valid, users will not get a new site!! (So this affects everything). Users will just be created with their group and any optional user-/groupHomeDir
		"uid_templateGroup" => 0,				// be_groups-UID, Optional: The user-group to use as a model for the created usergroups! What this means is that certain fields from this groups is directly copied to the created groups! The fields are: (inc_access_lists,non_exclude_fields,pagetypes_select,tables_select,tables_modify,groupMods,db_mountpoints,db_mountpoints,file_mountpoints). See tables.php for further information.
		"uid_generalGroup" => 0,				// be_groups-UID, Optional: The user-group to use as a secondary group for all created users. New users will be a member of their own group + this group (if it exists). Good trick is to include this general group as a subgroup of the template group. This way new users to the site needs only being members of the template group and not both...
			// Create features:
		"create_lockToDomain" => 1,				// Boolean: If set, the users and groups are locked to their domains, if domain is entered. LockToDomain is a feature that lets users login (and groups be enabled) only from that domain!
		"create_fileoper_perms_default" => 7,	// Integer/Bits: Default value for the be_users.fileoper_perms field. See the item in tables.php for further details. "7" enables the user to do most basic fileoperations.
		"createDomains" => 1,					// Boolean. If set, a sys_domain record will be created for the site IF either a domain-name or virtual-directory is given.
		"createVirtualDirs" => 0,				// Boolean. If set, a virtual-directory in the root of the typo3-site is created. This REQUIRES a domain-record and therefore "createDomains" must be enabled for this feature to work. Does not work on Windows. -> A virtual directory is a directory that consists of softlinks to the majority of files/folders in the root of the typo3-site. This feature provides a way to let "http://www.typo3.com/man_uk/" go to a specific page (site) in the page-structure.
		"createNewGroupDir" => 0,				// Boolean. If set and $TYPO3_CONF_VARS["BE"]["groupHomePath"] is set and valid, a new directory for the group is created here! (that is a directory with the number "be_groups.uid" of the usergroup)
		"createNewUserDir" => 0,				// Boolean. If set and $TYPO3_CONF_VARS["BE"]["userHomePath"] is set, a new directory for the user is created here! NORMALLY just create a group-dir. Should be sufficient for most cases. (that is a directory with the number "be_users.uid" of the new user)
			// Messages, template
		"templateFile_created" => "",			// Filereference: (relative to PATH_site) reference to a custom html-file to use as template after the site has been created. Default is "template_created.html". Look in this file to see how it works... (###.....### marks are substituted)
		"notify_email" => "",					// Email-addr: Enter email-adress to be notified when a site is created. (admin notification)
		"notifyUser_email" => 1,					// Boolean. Sends username and password to the user when site is created!
		"notifyUser_emailSubject" => 'TYPO3 Freesite Created',
		"notifyUser_emailMessage" => '
The new TYPO3 site has been created for you!

The website is found at: ###WEBSITE_URL###

Username: ###USERNAME###
Password: ###PASSWORD###

You enter the administration site at :
###LINK_URL###

Kind regards.
				'
	);

	var $backPath = "";
	var $genTree_HTML = "";



	/**
	 * Constructor. Checks if the module is allowed to be users without backend login.
	 */
	function freesite_admin () {
		$this->backPath = $GLOBALS["BACK_PATH"];
		
		$extConf = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["freesite"]);
		if (!is_array($extConf))	{
				t3lib_BEfunc::typo3PrintError ("Configuration Error","No configuration found written to localconf.php. Please go to the Extension Manager, click the title of the Freesite extension and configure this module with the form in the bottom of the page!",0);
				exit;
		}

		$this->sysConfig = array_merge($this->sysConfig,$extConf);		// Candidate for t3lib_div::array_merge() if integer-keys will some day make trouble...
		if (!$this->sysConfig["notify_email"])	$this->sysConfig["notify_email"] = $TYPO3_CONF_VARS["BE"]["warning_email_addr"];
		

		// If windows, createVirtualDirs is disabled. Filesystem specific.
		if (TYPO3_OS=="WIN")	{
			$this->sysConfig["createVirtualDirs"]==0;		// Virtual dirs does not work on windows as we're using softlinks (UNIX-thing)
		}
		
		
		// ******************************
		// Checking backend user login?
		// ******************************
		if ($this->sysConfig["backendOnly"])	{
			if (!$GLOBALS["BE_USER"]->user["uid"])	{
				t3lib_BEfunc::typo3PrintError ("Login-error","No user logged in! Sorry, I can't proceed then!",0);
				exit;
			}
			$GLOBALS["BE_USER"]->modAccess($GLOBALS["MCONF"],1);
		}
	}


	/**
	 * Generating tree.
	 */
	function genTree($theID, $depthData)	{
		// Generates a list of Page-uid's that corresponds to the tables in the tree. This list should ideally include all records in the pages-table.
		$a=0;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,doktype,hidden,deleted', 'pages', 'pid='.intval($theID).' AND NOT deleted', '', 'sorting');
		$c = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$a++;
			$newID =$row[uid];
	
			$this->genTree_HTML.="\n<DIV><NOBR>";
			$PM = "join";
			$LN = ($a==$c)?"blank":"line";
			$BTM = ($a==$c)?"bottom":"";
			$this->genTree_HTML.= $depthData.'<IMG src="'.$this->backPath.'t3lib/gfx/ol/'.$PM.$BTM.'.gif" width="18" height="16" align="top"><IMG src="'.$this->backPath.'gfx/i/pages.gif" width="18" height="16" align="top">'.t3lib_div::fixed_lgd(strip_tags($row[title]),50).'</NOBR></DIV>';
			$this->genTree($newID,$this->genTree_HTML ? $depthData.'<IMG src="'.$this->backPath.'t3lib/gfx/ol/'.$LN.'.gif" width="18" height="16" align="top">'  : '');
		}
	}

	/**
	 * Checks if record exists.
	 */
	function doesExist($table,$field,$value)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $field.'="'.$GLOBALS['TYPO3_DB']->quoteStr($value, $table).'"');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			return $row;
		}
	}

	/**
	 * 
	 */
	function getInputData($fieldName,$hsc=0)	{
		$out = $this->data[$fieldName];
		if ($hsc)	{
			$out=htmlspecialchars($out);
		}
		return $out;
	}

	/**
	 * Returns 'not correct' html-code
	 */
	function isCorrect($fieldName)	{
		if (isset($this->check[$fieldName]))	{
			return $this->sysConfig["notCorrectHTML"].$this->check[$fieldName].'<BR>';
		}
	}

	/**
	 * Cleans up a filename
	 */
	function cleanFileName($fileName)	{
		// Returns a string where any character not matching [a-zA-Z0-9_-] is substituted by "_"
		$theNewName = ereg_replace("[^\.[:alnum:]_-]","_",trim($fileName));
		return substr($theNewName,0,50);
	}

	/**
	 * Removes single slash in the end of string
	 */
	function rmSlash($string)	{
		return ereg_replace("\/$","",$string);
	}

	/**
	 * Removes double slash
	 */
	function rmDoubleSlash($string)	{
		return str_replace("//","/",$string);
	}
	

	/**
	 * Get subpart (template)
	 */
	function getSubpart($content, $marker)	{
		if ($marker && strstr($content,$marker))	{
			$start = strpos($content, $marker)+strlen($marker);
			$stop = @strpos($content, $marker, $start+1);
			$sub = substr($content, $start, $stop-$start);
	
			$reg=Array();		
			ereg("^[^<]*-->",$sub,$reg);
			$start+=strlen($reg[0]);
			
			$reg=Array();		
			ereg("<!--[^>]*$",$sub,$reg);
			$stop-=strlen($reg[0]);
			
			return substr($content, $start, $stop-$start);
		}
	}

	/**
	 * Substitute subpart (template)
	 */
	function substituteSubpart($content,$marker,$subpartContent,$recursive=1)	{
			// This function substitutes a subpart in $content with the content of $subpartContent.
			// If $recursive is set, the function calls itself with the content set to the remaining part of the content after the second marker. This means that proceding subparts are ALSO substituted!
			// If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the content of the subpart (fetched by $this->getSubpart())
		$start = strpos($content, $marker);
		$stop = @strpos($content, $marker, $start+1)+strlen($marker);
		if ($start && $stop>$start)	{
				// code before
			$before = substr($content, 0, $start);
			$reg=Array();		
			ereg("<!--[^>]*$",$before,$reg);
			$start-=strlen($reg[0]);
			$before = substr($content, 0, $start);
				// code after
			$after = substr($content, $stop);
			$reg=Array();		
			ereg("^[^<]*-->",$after,$reg);
			$stop+=strlen($reg[0]);
			$after = substr($content, $stop);
				// replace?
			if (is_array($subpartContent))	{
				$substContent=$subpartContent[0].$this->getSubpart($content,$marker).$subpartContent[1];
			} else {
				$substContent=$subpartContent;
			}
	
			if ($recursive && strpos($after, $marker))	{
				return $before.$substContent.$this->substituteSubpart($after,$marker,$subpartContent);
			} else {
				return $before.$substContent.$after;
			}
		} else {
			return $content;
		}
	}

	/**
	 * Inserts into database (not with TCE!) and updates if uid is set.
	 */
	function insertInDatabase($table,$data,$uid=0)	{
		if ($table && is_array($data))	{
			unset($data["uid"]);

			if (count($data))	{
				if ($this->sysConfig["testingOnly"])	{
					debug($table);
					debug($data);

					if ($uid)	{
						$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, 'uid='.intval($uid), $data);
					} else {
						$query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $data);
					}
					debug($query,1);
	
					return "99999";
				} else {
					if ($uid)	{
						$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.intval($uid), $data);
					} else {
						$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $data);
					}
					$err = $GLOBALS['TYPO3_DB']->sql_error();
					if ($err)	{
						debug($err,1);
						debug($table,1);
						debug($query,1);
						
						exit;
					}
					return $GLOBALS['TYPO3_DB']->sql_insert_id();
				}
			}
		}
		die("The record could not be inserted or updated! ".$table);
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * MAIN FUNCTION of the freesite module.
	 */
	function main()	{
		
		$this->data = t3lib_div::_POST("data");
		$this->verifyData();
		$this->isReady();
		
		// ***********************
		// Enter/Check data:
		// ***********************
		if (!$this->verificationOK)	{			// That is, we're still entering data... opposed to creating the site and records.
			$templateCode = $this->enteringData();
		} else {	
			$templateCode = $this->creatingData();
		}
		
		echo $templateCode;
		
		if ($this->sysConfig["testingOnly"])	{
			echo "<HR>";
			debug("DATA:");
			debug($this->data);
		}
	}

	/**
	 * Verifies incoming data
	 */
	function verifyData()	{
			// *********************************************
			// Processing Incoming data:
			// 
			// If there has been some incoming data:
			// Stripslashes + check the content!
			// **********************************************
		if (is_array($this->data))	{
			reset($this->data);
			while(list($k,$v)=each($this->data))	{
				$v=trim($v);
				$ok=0;  // flag that if set allows a field to be blank.
				switch($k)	{
					case "FIELD_username":
						$v=strtolower($v);			// Lowercase
						$v=$this->cleanFileName($v);	// No space
						$v=substr($v,0,20);
						if (strlen($v)<5 || $this->doesExist("be_users","username",$v))	{
							$this->check[$k]=$this->sysConfig["msgBadUsername"];
						}
					break;
					case "FIELD_email":
						if (!ereg("^[A-Za-z0-9\._-]*[@][A-Za-z0-9\._-]*[\.].[A-Za-z0-9]*$",$v))	{
							$this->check[$k]=$this->sysConfig["msgBadEmail"];
						}
					break;
					case "FIELD_password":
						if (strlen($v)<5)	{
							$this->check[$k]=$this->sysConfig["msgBadPassword"];
						}
					break;
					case "FIELD_lang":
						if (!$v)	{
							$v=$this->sysConfig["defaultLanguage"];
						}
					break;
					case "TEMPLATE":
					case "DUMMY_PAGES":
						$v=intval($v);
						$ok=1;
					break;	
					case "FOLDER":
						$v=str_replace("å","aa",$v);
						$v=str_replace("Å","AA",$v);
						$v=strtr($v, "áéúíâêûôîæøÁÉÚÍÄËÜÖÏÆØ", "aeuiaeuoieoAEUIAEUOIEO");
						
						$v=$this->cleanFileName($v);
						if ($v && (@file_exists(PATH_site.$v) || ereg("^typo|file|upload|media|t3lib|tslib",$v)))	{
							$this->check[$k]=$this->sysConfig["msgBadPath"];
						}
						if (!$this->sysConfig["virtualDirRequired"])	{
							$ok=1;
						}
					break;		
					case "DOMAIN":
						$v=strtolower($v);
						if ($v)	{
							if (ereg("[^\.a-z0-9_-]",$v) || !strstr($v,"."))	{
								$this->check[$k]=$this->sysConfig["msgBadDomain"];
							} else {
								if ($this->doesExist("sys_domain","domainName",$v))	{
									$this->check[$k]=$this->sysConfig["msgBadDomain_used"];
								}
							}
						}
						$ok=1;
					break;	
					CASE "TITLE":
						$ok=1;
					break;				
					default:
					break;
				}
				$this->data[$k]=$v;
				if (!$this->data[$k] && !$ok)	{
					$this->check[$k]=$this->sysConfig["msgBadValue"];
				}
			}
		}
	}	

	/**
	 * Checking if all data is verified and if the verification code was properly entered
	 */
	function isReady()	{
		// *******************************************************
		// Verifying if data entering is done
		// If $this->verificationOK is set, then the site is created
		// *******************************************************
		$this->ALL_OK= (is_array($this->data) && !count($this->check));		// This flag is set IF data has been input and they verify OK!
			// Verification code is calculated
		$this->verificationOK="";		// If this flag is set, then we're going to create the user!!!
		if ($this->ALL_OK)	{
			ksort($this->data);
			$this->verificationCode = strtoupper(substr(md5(serialize($this->data)),0,10));

			$VERIFICATION_CODE = strtoupper(trim(t3lib_div::_POST("VERIFICATION_CODE")));		// The sent ver-code
			if (t3lib_div::_POST("createButton"))		{	// Must press the createButton!
				$this->verificationOK = ($VERIFICATION_CODE && !strcmp($VERIFICATION_CODE,$this->verificationCode));
			}
		}
	}
	
	
	
	
	
	
	
	
	// **************************
	// 
	// ENTERING DATA
	// 
	// **************************
	

	/**
	 * Entering data
	 */
	function enteringData()	{
		// ****************
		// Get the template file
		// ****************
		$templateFile= $this->sysConfig["templateFile"] ? PATH_site.$this->sysConfig["templateFile"] : "template.html";
		$templateCode = t3lib_div::getURL($templateFile);
		if (!$templateCode)	die("No template file!! (".$templateFile.")");
		
		
		$this->fieldsArray["BACK_PATH"]=$this->backPath."../";

		// ************************************
		// Preview of template and page set
		// ************************************
		$this->fieldsArray["TEMPLATE_VIEW"]="";
		if ($this->data["TEMPLATE"] && !$this->check["TEMPLATE"])	{
			$fN="tmplimages/".$this->data["TEMPLATE"];
			$file="";
			if (@is_file($fN.".gif"))	{
				$file=$fN.".gif";
			} elseif (@is_file($fN.".jpg"))	{
				$file=$fN.".jpg";
			}
			if (@is_file($file))	{
				$fI = @getimagesize($file);
				if (is_array($fI))	{
					$this->fieldsArray["TEMPLATE_VIEW"]='<BR><img src="'.$file.'" '.$fI[3].'><BR>';
				}
			}
		}
		$this->fieldsArray["DUMMY_PAGES_VIEW"]="";
		if ($this->data["DUMMY_PAGES"] && !$this->check["DUMMY_PAGES"])	{
			$this->genTree($this->data["DUMMY_PAGES"],"");
			$this->fieldsArray["DUMMY_PAGES_VIEW"]="<BR><BR>".$this->genTree_HTML."<BR>";
		}



		if (!$this->ALL_OK || t3lib_div::_POST("updateButton"))	{
				// If everything is NOT OK or if the updateButton is still pushed:
			$this->displayDataForms();
		} else {
				// If everything is OK and createButton is pushed:
			$this->previewData();
		}
		
		
		
		// *****************************
		// Substitute template markers
		// *****************************
		$this->fieldsArray["UPDATE_CREATE"].=$this->HIDDEN_FIELDS;
		reset($this->fieldsArray);
		while(list($key,$content)=each($this->fieldsArray))	{
			$templateCode = str_replace("###".$key."###",$content,$templateCode);
		}
		return $templateCode;
	}	

	/**
	 * Display data
	 */
	function displayDataForms()	{
		global $TCA;
			// *****************************
			// Getting language array
			// *****************************
		$languages=array();
		t3lib_div::loadTCA("be_users");
		if ($TCA["be_users"]["columns"]["lang"])		{
			reset($TCA["be_users"]["columns"]["lang"]["config"]["items"]);
			while(list(,$k)=each($TCA["be_users"]["columns"]["lang"]["config"]["items"]))	{
				$theK = $k[1] ? $k[1] : "default";
				$languages[$theK]=$GLOBALS["LANG"]->sL($k[0]);
			}
		}
	
			// *****************************
			// Setting form-field extras	
			// *****************************
		$params=($this->sysConfig["inputFieldParams"] ? " ".$this->sysConfig["inputFieldParams"] : "") ;
	
			// *****************************
			// Personal information
			// *****************************
		$this->fieldsArray["FIELD_realName"]='<input type="Text" name="data[FIELD_realName]" value="'.$this->getInputData("FIELD_realName",1).'"'.$params.'>'.$this->isCorrect("FIELD_realName");
		$this->fieldsArray["FIELD_email"]='<input type="Text" name="data[FIELD_email]" value="'.$this->getInputData("FIELD_email",1).'"'.$params.'>'.$this->isCorrect("FIELD_email");
		$this->fieldsArray["FIELD_username"]='<input type="Text" name="data[FIELD_username]" value="'.$this->getInputData("FIELD_username",1).'"'.$params.'>'.$this->isCorrect("FIELD_username");
		$this->fieldsArray["FIELD_password"]='<input type="Text" name="data[FIELD_password]" value="'.$this->getInputData("FIELD_password",1).'"'.$params.'>'.$this->isCorrect("FIELD_password");
		
		$oTags="";
		reset($languages);
		$cmpValue = $this->getInputData("FIELD_lang") ? $this->getInputData("FIELD_lang") : $this->sysConfig["defaultLanguage"];
		while(list($key,$val)=each($languages))	{
			$oTags.='<option value="'.$key.'"'.($key==$cmpValue?' selected':'').'>'.$val.'</option>';
		}
		$this->fieldsArray["FIELD_lang"]='<select name="data[FIELD_lang]">'.$oTags.'</select>'.$this->isCorrect("FIELD_lang");
		
			// *****************************
			// Website specific:
			// *****************************
		$this->fieldsArray["TITLE"]='<input type="Text" name="data[TITLE]" value="'.$this->getInputData("TITLE",1).'"'.$params.'>'.$this->isCorrect("TITLE");
		$this->fieldsArray["FOLDER"]='<input type="Text" name="data[FOLDER]" value="'.$this->getInputData("FOLDER",1).'"'.$params.'>'.$this->isCorrect("FOLDER");
		$this->fieldsArray["DOMAIN"]='<input type="Text" name="data[DOMAIN]" value="'.$this->getInputData("DOMAIN",1).'"'.$params.'>'.$this->isCorrect("DOMAIN");
		
	
			// *****************************
			// Template
			// *****************************
		$pid = intval($this->sysConfig["pid_templateArchive"]);
		$oTags="";
		$firstUID=0;
		if ($pid)	{
				// Select templates in root
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_template', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime', '', 'sorting');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if (!$firstUID) $firstUID=$row[uid];
				$key=$row["uid"];
				$val=$row["title"];
				$oTags.='<option value="'.$key.'"'.($key==$this->getInputData("TEMPLATE")?' selected':'').'>'.$val.'</option>';
			}
				// Select subcategories of template folder.
			$page_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime AND NOT fe_group', '', 'sorting');
			while($page_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($page_res))	{
					// Subcategory templates
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_template', 'pid='.intval($page_row['uid']).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime', '', 'sorting');
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					if (!$firstUID) $firstUID=$row[uid];
					$key=$row["uid"];
					$val=$page_row["title"]." / ".$row["title"];
					$oTags.='<option value="'.$key.'"'.($key==$this->getInputData("TEMPLATE")?' selected':'').'>'.$val.'</option>';
				}
			}
			$this->fieldsArray["TEMPLATE"]='<select name="data[TEMPLATE]">'.$oTags.'</select>'.$this->isCorrect("TEMPLATE");
			if ($this->sysConfig["exclude_templateSelect"])	{
				$this->fieldsArray["TEMPLATE"]="";
				$this->HIDDEN_FIELDS.='<input type="hidden" name="data[TEMPLATE]" value="'.$firstUID.'">';
			}
		} else {
			$this->fieldsArray["TEMPLATE"]="";
		}
	
			// *****************************
			// Dummy Pages
			// *****************************
		$pid = intval($this->sysConfig["pid_dummyPagesArchive"]);
		$oTags="";
		$firstUID=0;
		if ($pid)	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime AND NOT fe_group', '', 'sorting');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if (!$firstUID) $firstUID=$row[uid];
				$key=$row["uid"];
				$val=$row["title"];
				$oTags.='<option value="'.$key.'"'.($key==$this->getInputData("DUMMY_PAGES")?' selected':'').'>'.$val.'</option>';
			}
			$this->fieldsArray["DUMMY_PAGES"]='<select name="data[DUMMY_PAGES]">'.$oTags.'</select>'.$this->isCorrect("DUMMY_PAGES");
			if ($this->sysConfig["exclude_dummyPages"])	{
				$this->fieldsArray["DUMMY_PAGES"]='';
				$this->HIDDEN_FIELDS.='<input type="Hidden" name="data[DUMMY_PAGES]" value="'.$firstUID.'">';
			}
		} else {
			$this->fieldsArray["DUMMY_PAGES"]='';
		}
	
			// *****************************
			// UPDATE_CREATE
			// *****************************
		$this->fieldsArray["UPDATE_CREATE"]='<input type="Submit" name="updateButton" value="'.$this->sysConfig["label_Update"].'">';
		if ($this->ALL_OK)	{		// If every thing  is OK!
			if ($this->sysConfig["previewData"])	{
				$this->fieldsArray["UPDATE_CREATE"].=' <input type="Submit" name="previewButton" value="'.$this->sysConfig["label_Preview"].'">';
			} else {
				$this->fieldsArray["UPDATE_CREATE"].=' <input type="Submit" name="createButton" value="'.$this->sysConfig["label_Create"].'">';
				$this->HIDDEN_FIELDS.='<input type="Hidden" name="VERIFICATION_CODE" value="'.$this->verificationCode.'">'.chr(10);
			}
		}
	}

	/**
	 * Preview data
	 */
	function previewData()	{
		$fields=explode(",","FIELD_realName,FIELD_email,FIELD_username,FIELD_password,FIELD_lang,TITLE,FOLDER,DOMAIN,DUMMY_PAGES,TEMPLATE");
		while(list(,$v)=each($fields))	{
			switch($v)	{
				case "DOMAIN":
					if ($this->data["FOLDER"] && !$this->data["DOMAIN"])	{		// If the folder is given, but the domain is not...
						$p=parse_url(t3lib_div::getIndpEnv("TYPO3_REQUEST_SCRIPT"));
						$dir=dirname($p["path"]);	// Strip file
						$path=dirname(dirname($dir));	// strip the ../../ dirs
						$this->fieldsArray[$v]="<b>".$path."</b>";
					} else {
						$this->fieldsArray[$v]="<b>".$this->data[$v]."</b>";
					}
				break;			
				default:
					$this->fieldsArray[$v]="<b>".$this->data[$v]."</b>";
				break;
			}
			$this->HIDDEN_FIELDS.='<input type="Hidden" name="data['.$v.']" value="'.htmlspecialchars($this->data[$v]).'">'.chr(10);
		}
	
			// **************************	
			// UPDATE_CREATE
			// **************************	
		if ($this->sysConfig["emailVerification"] && $this->sysConfig["previewData"])	{
			$this->fieldsArray["UPDATE_CREATE"]=sprintf($this->sysConfig["msgEmailVerificationCode"],$this->data["FIELD_email"]);
			$msg=$this->sysConfig["emailVerificationMessage"];
	
//			if (!$this->sysConfig["testingOnly"])	{
				mail ($this->data["FIELD_email"], 
					$this->sysConfig["emailVerificationSubject"],
					$msg.chr(10).chr(10).$this->verificationCode,
					$this->sysConfig["emailHeader"]);
//			}
			$this->fieldsArray["UPDATE_CREATE"].='<input type="Text" name="VERIFICATION_CODE" value=""><BR><BR>'.chr(10);
		} else {
			$this->HIDDEN_FIELDS.='<input type="Hidden" name="VERIFICATION_CODE" value="'.$this->verificationCode.'">'.chr(10);
		}
	
		$this->fieldsArray["UPDATE_CREATE"].='<input type="Submit" name="updateButton" value="'.$this->sysConfig["label_Return"].'">';
		$this->fieldsArray["UPDATE_CREATE"].=' <input type="Submit" name="createButton" value="'.$this->sysConfig["label_Create"].'">';
	}
	
	
	

	
	// ********************************
	//
	// Creating the template
	//
	// ********************************
	function creatingData ()	{

		// *********************************************************
		// Get template, general group, newsite page.
		// *********************************************************
		$this->groupTemplate = $this->doesExist("be_groups","uid",$this->sysConfig["uid_templateGroup"]);		// The users own group will be modelled after this group (be a copy)
		$this->groupGeneral = $this->doesExist("be_groups","uid",$this->sysConfig["uid_generalGroup"]);		// The user becomes a member of this group, which should be about general configuration for all users.
		$this->newsite_page = $this->doesExist("pages","uid",$this->sysConfig["pid_newsite"]);
		
		
		// ***************************
		// Setting new group defaults
		// ***************************
			// Standard data.
		$this->groupData=array();
		$this->groupData["pid"]=0;
		$this->groupData["tstamp"]=time();
		$this->groupData["hidden"]=0;
		$this->groupData["crdate"]=time();
		$this->groupData["cruser_id"]=0;
		$this->groupData["deleted"]=0;
		//	$this->groupData["starttime"]=0;
		//	$this->groupData["endtime"]=0;
		
			// User specific
		$this->groupData["title"]=$this->data["FIELD_username"];
		$this->groupData["description"]=
			"Sitetitle: ".$this->data["TITLE"].chr(10).
			"Name: ".$this->data["FIELD_realName"].chr(10).
			"Domain: ".$this->data["DOMAIN"].chr(10).
			"Folder: ".$this->data["FOLDER"].chr(10);
		
			// From template group:		
		$this->groupData["inc_access_lists"]=		$this->groupTemplate["inc_access_lists"];
		$this->groupData["non_exclude_fields"]=	$this->groupTemplate["non_exclude_fields"];
		$this->groupData["pagetypes_select"]=		$this->groupTemplate["pagetypes_select"];
		$this->groupData["tables_select"]=		$this->groupTemplate["tables_select"];
		$this->groupData["tables_modify"]=		$this->groupTemplate["tables_modify"];
		$this->groupData["groupMods"]=			$this->groupTemplate["groupMods"];
		$this->groupData["db_mountpoints"]=		$this->groupTemplate["db_mountpoints"];		// The newsite pid is added to this!
		$this->groupData["file_mountpoints"]=		$this->groupTemplate["file_mountpoints"];
		$this->groupData["subgroup"]=		$this->groupTemplate["subgroup"];
		
			// Lock To Domain?	
		if ($this->sysConfig["create_lockToDomain"])	{
			$this->groupData["lockToDomain"]=$this->data["DOMAIN"];
		} else {
			$this->groupData["lockToDomain"]="";
		}
		
		
		
		
		// ***************************
		// Setting new user defaults
		// ***************************
			// standard data.
		$this->userData=array();
		$this->userData["pid"]=0;
		$this->userData["tstamp"]=time();
		$this->userData["admin"]=0;
		$this->userData["disable"]=0;
		$this->userData["starttime"]=0;
		$this->userData["endtime"]=0;
		$this->userData["crdate"]=time();
		$this->userData["cruser_id"]=0;
		$this->userData["deleted"]=0;
			
			// user specific
		$this->userData["username"]=$this->data["FIELD_username"];
		$this->userData["password"]=md5($this->data["FIELD_password"]);
		$this->userData["email"]=$this->data["FIELD_email"];
		$this->userData["realName"]=$this->data["FIELD_realName"];
		
		$this->data["FIELD_lang"] = $this->data["FIELD_lang"] ? $this->data["FIELD_lang"] : $this->sysConfig["defaultLanguage"];
		$this->userData["lang"]=$this->data["FIELD_lang"]=="default" ? "" : $this->data["FIELD_lang"];
		
			// This sets the mountpoints of the user to empty. Also it's configure (options) that the user should get his mountpoints from the groups.
		$this->userData["db_mountpoints"]="";
		$this->userData["file_mountpoints"]="";
		$this->userData["options"]=3;
		
			// Lock To Domain?
		if ($this->sysConfig["create_lockToDomain"])	{
			$this->userData["lockToDomain"]=$this->data["DOMAIN"];
		} else {
			$this->userData["lockToDomain"]="";
		}
		
			// Other options		
		$this->userData["uc"]="";			// No preconfiguration for the user.
		$this->userData["userMods"]="";	// No special modules for the user.
		$this->userData["fileoper_perms"]=$this->sysConfig["create_fileoper_perms_default"];

		// ************************************************************************
		// Checking (again) if the username is valid (that is, not used already)
		// ************************************************************************
		if (!$this->doesExist("be_users","username",$this->userData["username"]))	{
			return $this->startCreate();
		} else {
			// SOMETHING WENT WRONG, probably the user did suddenly exist.
			die("Some error occurred! (the user did exist for some reason!)");
		}

	}		

	/**
	 * Start creating the new site.
	 */	
	function startCreate()	{
		$this->setNoDomainUrl();		// Setting the no domain url
		$this->notifyAdmin();			// Notify admin of this creation
		$this->addUserAndGroup();
		$this->createDirs();	

			// ***************************************
			// Create a user-object with the new user
			// ***************************************
		$new_BE_USER = t3lib_div::makeInstance("t3lib_beUserAuth");	// New backend user object
		$new_BE_USER->OS = TYPO3_OS;
		if (!$this->sysConfig["testingOnly"])	{
			$new_BE_USER->setBeUserByUid($this->user_uid);
			$new_BE_USER->fetchGroupData();
		}
	
			// ********************************************************************************
			// If the newsite_pid existed, the whole process of setting up the site is begun.
			// ********************************************************************************
		if (($new_BE_USER->user["uid"] || $this->sysConfig["testingOnly"]) && is_array($this->newsite_page))		{
				// copy pages...
			$dummyPage_row=$this->doesExist("pages","uid",$this->data["DUMMY_PAGES"]);
			if ($this->sysConfig["pid_dummyPagesArchive"] && is_array($dummyPage_row) && $this->sysConfig["pid_dummyPagesArchive"]==$dummyPage_row["pid"])	{
				$root_page_pid = $this->copyDummyPages($dummyPage_row["uid"],$new_BE_USER);
				if ($root_page_pid)	{
					$this->setGroupMountPoint($root_page_pid);
					$this->setRootPageProperties($root_page_pid);
					$virtualDir = $this->createVirtualDir();
					$domainData = $this->createDomainRecord($root_page_pid,$virtualDir);
					$this->createTemplateRecord($root_page_pid,$this->getRootPageTitle());
				} else die ("No pages copied");
			} else die("no dummy page archive.");
		} else die ("new_BE_USER did not exist OR newsite_page didn't exist");
	
		$out = $this->createdOutput($domainData);	
		$this->sendNotifyEmailToUser();
		return $out;
	}
	
	/**
	 * Finding default url (prepended with slash)
	 */
	function setNoDomainUrl()	{
		$p=parse_url(t3lib_div::getIndpEnv("TYPO3_REQUEST_SCRIPT"));		// Url of this script
		$dir=dirname($p["path"]);	// Strip file
		$sitedirname = dirname(dirname(dirname(dirname($dir))));	
		if (substr($sitedirname,-1)!="/")	{
			$sitedirname.="/";
		}
		$this->noDomainURL=$this->rmDoubleSlash($p["host"].$sitedirname);	// strip the dir of this tool
	}
	
	/**
	 * Notification email to administrator:
	 */
	function notifyAdmin() {
		$subject=$this->sysConfig["notifyUser_emailSubject"];
		if ($this->sysConfig["notify_email"])	{
			mail ($this->sysConfig["notify_email"], 
				$subject." (admin-note)",
				"New site is created by ".$this->data["FIELD_realName"]." (".$this->data["FIELD_email"].") at http://".$this->noDomainURL.chr(10).chr(10).
				"Username: ".$this->userData["username"].chr(10).
				"Password: ".$this->data["FIELD_password"].chr(10),
				$this->sysConfig["emailHeader"]);
		}
	}
	
	/**
	 * Creating a be_user and group based on ->groupData and ->userData
	 */
	function addUserAndGroup()	{
			// Save the new usergroup to database
		$this->testHeader("Add group");
		$this->userGroup_uid=$this->insertInDatabase("be_groups",$this->groupData);
		
			// Save the new user to database
			// If there is a generalGroup, put it in the list
		$this->userData["usergroup"]=$this->userGroup_uid;
		if	($this->groupGeneral) {
			$this->userData["usergroup"].=",".$this->groupGeneral["uid"];
		}
		$this->testHeader("Add user");
		$this->user_uid = $this->insertInDatabase("be_users",$this->userData);
	}	
	
	/**
	 * Creating directories for users/groups
	 */
	function createDirs()	{
		global $TYPO3_CONF_VARS;
			// *****************************************************************
			// Create USER and GROUP directories, if required
			// *****************************************************************
		if ($this->sysConfig["createNewUserDir"] 
			&& t3lib_div::isFirstPartOfStr($TYPO3_CONF_VARS["BE"]["userHomePath"],$TYPO3_CONF_VARS["BE"]["lockRootPath"])
			&& @is_dir($TYPO3_CONF_VARS["BE"]["userHomePath"]))	{
				$folder=$this->rmDoubleSlash($TYPO3_CONF_VARS["BE"]["userHomePath"]."/".$this->user_uid);
				if (!$this->sysConfig["testingOnly"])	{
					mkdir ($folder, 0700);
					mkdir ($folder."/_temp_", 0700);
				} else {
					debug("CREATE user home dir: ".$folder,1);
				}
		}
		if ($this->sysConfig["createNewGroupDir"] 
			&& t3lib_div::isFirstPartOfStr($TYPO3_CONF_VARS["BE"]["groupHomePath"],$TYPO3_CONF_VARS["BE"]["lockRootPath"])
			&& @is_dir($TYPO3_CONF_VARS["BE"]["groupHomePath"]))	{
				$folder=$this->rmDoubleSlash($TYPO3_CONF_VARS["BE"]["groupHomePath"]."/".$this->userGroup_uid);
				if (!$this->sysConfig["testingOnly"])	{
					mkdir ($folder, 0700);
					mkdir ($folder."/_temp_", 0700);
				} else {
					debug("CREATE group home dir: ".$folder,1);
				}
		}
	}	
	
	/**
	 * Creating virtual directory
	 */
	function createVirtualDir()	{
		$virtualDir=0;		// Boolean flag, that indicates if a folder has been created
		$newFolder=PATH_site.$this->data["FOLDER"];

		if (TYPO3_OS!="WIN" && $this->sysConfig["createVirtualDirs"] && $this->sysConfig["createDomains"] && $this->data["FOLDER"])	{
			if (!@file_exists($newFolder))	{
				if (!$this->sysConfig["testingOnly"])	{
					mkdir ($newFolder, 0700);
					$thelinks=explode(",","clear.gif,.htaccess,fileadmin,index.php,media,showpic.php,typo3,t3lib,tslib,typo3conf,typo3temp,uploads");
					while(list(,$target)=each($thelinks))	{
						$dest=$newFolder."/".$target;
						$target=PATH_site.$target;
						if (@file_exists($target))	{
							symlink ($target,$dest);
						}
					}
				} else {
					debug("CREATE VIRTUAL FOLDER: ".$newFolder,1);
				}
				$virtualDir=1;
			} else {
				die("This folder did exist allready! ".$newFolder);
			}
		}
		return $virtualDir;
	}

	/**
	 * Copying the dummy page structure.
	 */
	function copyDummyPages($src_uid,$new_BE_USER)	{
		if (!$this->sysConfig["testingOnly"])	{
			$tce = t3lib_div::makeInstance("t3lib_TCEmain");
			$tce->stripslashes_values=0;
			$tce->copyTree=2;
			$tce->neverHideAtCopy=1;
	
				// setting the user to admin rights temporarily during copy. The reason is that everything must be copied fully!
			$new_BE_USER->user["admin"]=1;

				// Making copy-command
			$cmd=array();
			$cmd["pages"][$src_uid]["copy"]=$this->sysConfig["pid_newsite"];
			$tce->start(array(),$cmd,$new_BE_USER);
			$tce->process_cmdmap();

				// Unsetting the user.
			unset($new_BE_USER);

				// Getting the new root page id.
			$root_page_pid = $tce->copyMappingArray["pages"][$src_uid];
			return $root_page_pid;
		} else {
			debug("COPY page structure: pid=".$src_uid,1);
			return 99999;
		}
	}	
	
	/**
	 * Set group mount point
	 */
	function setGroupMountPoint($root_page_pid)	{
		$groupUpdate=array();
		if ($this->groupData["db_mountpoints"])	{
			$groupUpdate["db_mountpoints"] = $root_page_pid.",".$this->groupData["db_mountpoints"];
		} else {
			$groupUpdate["db_mountpoints"]=$root_page_pid;
		}

		$this->testHeader("Group Update");
		$this->insertInDatabase("be_groups",$groupUpdate,$this->userGroup_uid);
	}
	
	/**
	 * Setting the root page title and other things.
	 */
	function setRootPageProperties($root_page_pid)	{
		$pageData=array();
		$pageData["alias"]=$this->data["FIELD_username"];
		$pageData["title"]= $this->getRootPageTitle();
		$pageData["perms_userid"]=0;	// The root page should not be owned (and thereby deleteable) by the user

		$this->testHeader("page Data update");
		$this->insertInDatabase("pages",$pageData,$root_page_pid);
	}
	
	/**
	 * Returning the root page title
	 */
	function getRootPageTitle()	{
		return $this->data["TITLE"]?$this->data["TITLE"]:($this->data["DOMAIN"]?$this->data["DOMAIN"]:($this->data["FOLDER"]?$this->data["FOLDER"]:$this->data["FIELD_username"]));
	}
	
	/**
	 * Create Domain Record
	 */
	function createDomainRecord($root_page_pid,$virtualDir)	{
		if (($virtualDir || $this->data["DOMAIN"]) && $this->sysConfig["createDomains"])	{
			if (!$this->data["DOMAIN"])	{
				$path=$this->noDomainURL;
			} else {
				$path=$this->data["DOMAIN"]."/";
			}
			if ($virtualDir)	{	// Only prepend virtual dir if it's set!
				$path.=$this->data["FOLDER"];
			}
			$path = $this->rmSlash($path);		// In any case, remove the last slash of the string.
			
			$domainData=array();
			$domainData["pid"]=$root_page_pid;
			$domainData["tstamp"]=time();
			$domainData["hidden"]=0;
			$domainData["domainName"]=$path;
			$domainData["redirectTo"]="";
			
			if (!$this->doesExist("sys_domain","domainName",$path))	{
				$this->testHeader("sys_domain record");
				$this->insertInDatabase("sys_domain",$domainData);
			}
		}
		return $domainData;
	}
	
	/**
	 * Create Template Record
	 */
	function createTemplateRecord($root_page_pid,$title)	{
		if ($this->sysConfig["pid_templateArchive"])		{
			// ************************
			// Template data
			// ************************
			$templateData=array();
				// standard data
			$templateData["pid"]=$root_page_pid;
			$templateData["tstamp"]=time();
			$templateData["sorting"]=0;
			$templateData["crdate"]=time();
			$templateData["cruser_id"]=0;
			$templateData["deleted"]=0;
			$templateData["hidden"]=0;
			$templateData["starttime"]=0;
			$templateData["endtime"]=0;
	
				// standard config
			$templateData["root"]=1;
			$templateData["clear"]=3;
			$templateData["include_static"]="";
			$templateData["constants"]="";
			$templateData["config"]="";
			$templateData["resources"]="";
			$templateData["nextLevel"]="";
			$templateData["description"]="[AUTO-GENERATED TEMPLATE - ".date("d-m-Y")."]".chr(10);
			
				// User specific:
			$templateData["title"]=$title;
			$templateData["sitetitle"]=$this->data[$this->sysConfig["defaultFormField_sitetitle"]];		
			$templateData["basedOn"]=intval($this->data["TEMPLATE"]);
			
			$this->testHeader("sys_template");
			$this->insertInDatabase("sys_template",$templateData);
		}
	}	

	/**
	 * Create output to browser for created screen.
	 */
	function createdOutput($domainData)	{
			// Get the template file
		$templateFile= $this->sysConfig["templateFile_created"] ? PATH_site.$this->sysConfig["templateFile_created"] : "template_created.html";
		$templateCode = t3lib_div::getURL($templateFile);
		if (!$templateCode)	die("No template file!! (".$templateFile.")");
		
			// ************************************************
			// Output congratulation text:
			// ************************************************
		$this->fieldsArray["USERNAME"]=$this->userData["username"];
		$this->fieldsArray["PASSWORD"]=$this->data["FIELD_password"];
		$this->fieldsArray["WEBSITE_URL"]="http://".$this->rmSlash($domainData["domainName"]?$domainData["domainName"]:$this->noDomainURL)."/";
		$this->fieldsArray["ADMIN_URL"]=$this->fieldsArray["WEBSITE_URL"].TYPO3_mainDir;
		$this->fieldsArray["LINK_URL"]=$this->fieldsArray["ADMIN_URL"]."index.php?u=".rawurlencode($this->fieldsArray["USERNAME"])."&p=".rawurlencode($this->fieldsArray["PASSWORD"]);
		$this->fieldsArray["EMAIL"]=$this->data["FIELD_email"];
		if (!$domainData["domainName"])	{$this->fieldsArray["WEBSITE_URL"].='?'.$this->data["FIELD_username"];}		// Add alias for frontpage if not domain is set.
	
		reset($this->fieldsArray);
		while(list($key,$content)=each($this->fieldsArray))	{
			$templateCode = str_replace("###".$key."###",$content,$templateCode);
		}
		return $templateCode;
	}
	
	/**
	 * Notify user
	 */
	function sendNotifyEmailToUser()	{
		$msg=$this->sysConfig["notifyUser_emailMessage"];
		$subject=$this->sysConfig["notifyUser_emailSubject"];
		reset($this->fieldsArray);
		while(list($key,$content)=each($this->fieldsArray))	{
			$msg = str_replace("###".$key."###",$content,$msg);
		}
		if ($this->sysConfig["notifyUser_email"])	{
			mail ($this->data["FIELD_email"], 
				$subject,
				$msg,
				$this->sysConfig["emailHeader"]);
		}
	}

	/**
	 * Printing test header
	 */
	function testHeader($label)	{
		if ($this->sysConfig["testingOnly"])	{
			echo '<HR>';
			debug($label,1);
		}
	}


	
	// ******************************
	//
	// SELECTING TEMPLATES / PAGES
	// 
	// ******************************

	function getTemplateHTML($uid,$title,$template)	{
		$fN="tmplimages/".$uid;
		$file="";
		if (@is_file($fN.".gif"))	{
			$file=$fN.".gif";
		} elseif (@is_file($fN.".jpg"))	{
			$file=$fN.".jpg";
		}
		if (@is_file($file))	{
			$fI = @getimagesize($file);
			if (is_array($fI))	{
				$image='<img src="'.$file.'" '.$fI[3].' border=0>';
				$image='<a href="#" onClick="select('.$uid.'); return false;">'.$image.'</a>';
			}
		}
	
		$msg = $template;
		$msg = str_replace("###TITLE###",$title,$msg);
		$msg = str_replace("###IMAGE###",$image,$msg);
		return $msg;
	}
	function getPagesHTML($uid,$title,$template)	{
		$admin = t3lib_div::makeInstance("freesite_admin");
		$admin->genTree($uid,"");
	
		$pages=$admin->genTree_HTML;
		$title='<a href="#" onClick="select('.$uid.'); return false;">'.$title.'</a>';
	
		$msg = $template;
		$msg = str_replace("###TITLE###",$title,$msg);
		$msg = str_replace("###PAGES###",$pages,$msg);
		return $msg;
	}	




		// ************************************
		// Get the list of templates or pages:
		// ************************************

	function printSelect($theType)	{
			// Get the template file
		$theKey = "templateFile_".$theType;
		$templateFile= $this->sysConfig[$theKey] ? PATH_site.$this->sysConfig[$theKey] : "template_".$theType.".html";
		$templateCode = t3lib_div::getURL($templateFile);
		if (!$templateCode)	die("No template file!! (".$templateFile.")");
		
		
		$itemPart = $this->getSubpart($templateCode, "###TEMPLATE_ITEM###");
		$out="";
		
		switch($theType)	{
			case "selectTemplate":
					// Template
				$infoUid = t3lib_div::_GP("infoUid");
				if ($infoUid && !$GLOBALS["TYPO3_CONF_VARS"]["BE"]["EXTERNAL"]["FREESITE"]["templateSelect_noInfoLink"])	{
						// Get template record:
					$tRec = t3lib_BEfunc::getRecord("sys_template",$infoUid);
					if (is_array($tRec))	{
						$existTemplate = $this->initialize_editor($tRec["pid"],$tRec["uid"]);		// initialize
						$out.="<HR><h3>Template Information:</h3>";
						$out.='<a href="index.php?script=template&infoUid='.$infoUid.'&allDetails=1">Show all categories</a><BR>';
						$out.=$this->displayExample("");
					}
//					$out.='<HR><a href="index.php?script=template#T'.$infoUid.'">BACK</a>';
				} else {
					$pid = intval($this->sysConfig["pid_templateArchive"]);
					$oTags="";
					$firstUID=0;
					if ($pid)	{
							// Select templates in root
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_template', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime', '', 'sorting');
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							if (!$firstUID) $firstUID=$row[uid];
							$key=$row["uid"];
							$val=$row["title"];
							$out.=$this->getTemplateHTML($key,$val,$itemPart);
						}
							// Select subcategories of template folder.
						$page_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime AND NOT fe_group', '', 'sorting');
						while($page_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($page_res))	{
								// Subcategory templates
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_template', 'pid='.intval($page_row['uid']).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime', '', 'sorting');
							while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
								if (!$firstUID) $firstUID=$row[uid];
								$key=$row['uid'];
								$val=$page_row['title'].' / '.$row['title'];
								if (!$GLOBALS['TYPO3_CONF_VARS']['BE']['EXTERNAL']['FREESITE']['templateSelect_noInfoLink'])	{
									$out.='<a name="T'.$key.'"></a>';
									$out.='<a href="index.php?script=template&infoUid='.$key.'" target="template_infowindow"><img src="'.$GLOBALS["BACK_PATH"].'t3lib/gfx/icon_note.gif" class="absmiddle" width=18 height=16 border=0></a>';
								}
								$out.=$this->getTemplateHTML($key,$val,$itemPart);
							}
						}
					}
				}
			break;
			case "selectPages":
					// Dummy Pages
				$pid = intval($this->sysConfig["pid_dummyPagesArchive"]);
				$oTags="";
				$firstUID=0;
				if ($pid)	{
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid='.intval($pid).' AND NOT deleted AND NOT hidden AND NOT starttime AND NOT endtime AND NOT fe_group', '', 'sorting');
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
						if (!$firstUID) $firstUID = $row[uid];
						$key = $row["uid"];
						$val = $row["title"];
						$out.= $this->getPagesHTML($key,$val,$itemPart);
					}
				}
			break;
		}
		echo $this->substituteSubpart($templateCode, "###TEMPLATE_ITEM###", $out);
	}	
	
	
		// USED TO DISPLAY TEMPLATE DETAILS:
	function displayExample($theOutput)	{
		global $tmpl,$theConstants;
		$categories = $tmpl->ext_getCategoryLabelArray();
		$allDetails = t3lib_div::_GP("allDetails");
		if (is_array($categories))	{
//			debug($categories);
//			debug($tmpl->categories);
			reset($categories);
			$tmpl->ext_localGfxPrefix=t3lib_extMgm::extPath("tstemplate_ceditor");
			$tmpl->ext_localWebGfxPrefix=$this->backPath.t3lib_extMgm::extRelPath("tstemplate_ceditor");
			while(list($category,$label)=each($categories))	{
				$tmpl->helpConfig=array();
				$tmpl->ext_getTSCE_config($category);
//				debug($tmpl->helpConfig);
				$detailFlag = $tmpl->helpConfig["imagetag"] || $tmpl->helpConfig["description"] || $tmpl->helpConfig["header"];
				if ($allDetails || $detailFlag)	{
					$theOutput.="<HR>";
					$theOutput.="<h3>".$label.": ".$tmpl->helpConfig["header"]."</h3>";
				}
				if ($detailFlag)	{
					$theOutput.='<div align="center">'.$tmpl->helpConfig["imagetag"].'</div><BR>'.
						($tmpl->helpConfig["description"] ? implode(explode("//",$tmpl->helpConfig["description"]),"<BR>")."<BR>" : "").
						($tmpl->helpConfig["bulletlist"] ? "<ul><li>".implode(explode("//",$tmpl->helpConfig["bulletlist"]),"<li>")."</ul>" : "<BR>");
				}
				if ($allDetails || $detailFlag)	{
					$theOutput.=$this->getTemplateDetails($category);
				}
			}
		}

		return $theOutput;
	}
	function getTemplateDetails($category)	{
		global $tmpl,$theConstants;

		// Details:
		$subcat="";
		$subcat_name="";
		asort($tmpl->categories[$category]);
		while(list($name,$type)=each($tmpl->categories[$category]))	{
			$params = $theConstants[$name];
			if (is_array($params))	{
				if ($subcat!=$params["subcat_name"])	{
					$subcat=$params["subcat_name"];
					$subcat_name = $params["subcat_name"] ? $tmpl->subCategories[$params["subcat_name"]][0] : "Others";

					$out.='<tr><td colspan=3><img src=clear.gif height=10 width=1></td></tr>';
					$out.='<tr><td colspan=3 bgcolor="#cccccc"><strong>'.fw(strtoupper(htmlspecialchars($subcat_name))).'</strong></td></tr>';
				}

				list($label)=explode("|",$params["label"]);
				$label_parts = explode(":",$label,2);
				if (count($label_parts)==2)	{
					$head=trim($label_parts[0]);
					$body=trim($label_parts[1]);
				} else {
					$head=trim($label_parts[0]);
					$body="";
				}
				if (strlen($head)>35)	{
					if (!$body) {$body=$head;}
					$head=t3lib_div::fixed_lgd($head,35);
				}
				$typeDat=$tmpl->ext_getTypeData($params["type"]);

				$p_name = '<font color="#666666">['.$params["name"].']</font><BR>';
				$p_dlabel='<font color="#666666"><b>Default:</b> '.htmlspecialchars($params["default_value"]).'</font><BR>';			
				$p_label = '<b>'.htmlspecialchars($head).'</b>';
				$p_descrip = $body ? htmlspecialchars($body)."<BR>" : "";
//				debug($typeDat);
				
				$out.='<tr><td nowrap valign=top>'.fw($tmpl->helpConfig["constants"][$params["name"]].$p_label).'<BR><font color="#666666">'.fw(t3lib_div::fixed_lgd(htmlspecialchars($params["type"]),20)).'</font></td><td><img src=clear.gif width=10 height=1></td><td valign=top>'.fw($p_descrip).'</td></tr>';
//				debug($typeDat);
			}
		}
		$out='<table border=0 cellpadding=0 cellspacing=0>'.$out.'</table>';
		return $out;
	}
	function initialize_editor($pageId,$template_uid=0)	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $tmpl,$tplRow,$theConstants;
		
		$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");	// Defined global here!
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->init();
		$tmpl->fieldCompensation = (($GLOBALS["CLIENT"]["BROWSER"]=="net") ? "1" : "1.15");
		
		$tplRow = $tmpl->ext_getFirstTemplate($pageId,$template_uid);	// Get the row of the first VISIBLE template of the page. whereclause like the frontend.
		if (is_array($tplRow))	{	// IF there was a template...
				// Gets the rootLine
			$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
			$rootLine = $sys_page->getRootLine($pageId);
			$tmpl->runThroughTemplates($rootLine,$template_uid);	// This generates the constants/config + hierarchy info for the template.
			$theConstants = $tmpl->generateConfig_constants();	// The editable constants are returned in an array.
			$tmpl->ext_categorizeEditableConstants($theConstants);	// The returned constants are sorted in categories, that goes into the $tmpl->categories array
			$tmpl->ext_regObjectPositions($tplRow["constants"]);		// This array will contain key=[expanded constantname], value=linenumber in template. (after edit_divider, if any)
			return 1;
		}
	}
}


// Include extension?
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/freesite/mod/class.freesite.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/freesite/mod/class.freesite.php"]);
}


?>