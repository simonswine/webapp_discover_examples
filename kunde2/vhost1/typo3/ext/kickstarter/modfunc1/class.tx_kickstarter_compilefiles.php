<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2001-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * TYPO3 Extension Repository
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */


class tx_kickstarter_compilefiles {

		// Internal:
	var $fileArray=array();
	var $ext_tables=array();
	var $ext_tca=array();
	var $ext_tables_sql=array();
	var $ext_localconf=array();
	var $ext_locallang=array();
	var $ext_locallang_db=array();

	var $extKey="";

	var $charMaxLng = 2;	// Varchars are created instead of chars when over this length.


	function makeFilesArray($extKey)	{

		$this->ext_localconf=array();
		$this->ext_tables=array();
		$this->fileArray=array();

			// TSconfig?
		if (is_array($this->wizArray["TSconfig"]))	{
			$content = current($this->wizArray["TSconfig"]);
				// Page TSconfig:
			if (trim($content["page_TSconfig"]))	{
				$this->ext_localconf[]=trim($this->wrapBody("
					t3lib_extMgm::addPageTSConfig('
					",trim($this->slashValueForSingleDashes($content["page_TSconfig"])),"
					');
				"));
			}
				// User TSconfig:
			if (trim($content["user_TSconfig"]))	{
				$this->ext_localconf[]=trim($this->wrapBody("
					t3lib_extMgm::addUserTSConfig('
					",trim($this->slashValueForSingleDashes($content["user_TSconfig"])),"
					');
				"));
			}
		}

			// TypoScript
		if (is_array($this->wizArray["ts"]))	{
			$content = current($this->wizArray["ts"]);
				// Page TSconfig:
			if (trim($content["constants"]))	{
				$this->addFileToFileArray("ext_typoscript_constants.txt",$content["constants"],1);
				$this->EM_CONF_presets["clearCacheOnLoad"]=1;
			}
				// User TSconfig:
			if (trim($content["setup"]))	{
				$this->addFileToFileArray("ext_typoscript_setup.txt",$content["setup"],1);
				$this->EM_CONF_presets["clearCacheOnLoad"]=1;
#debug(array($this->fileArray["ext_typoscript_setup.txt"]));
			}
		}

		if (is_array($this->wizArray["module"]))	{
			reset($this->wizArray["module"]);
			while(list($k,$config)=each($this->wizArray["module"]))	{
				$this->renderExtPart_module($k,$config,$extKey);
			}
		}

		if (is_array($this->wizArray["moduleFunction"]))	{
			reset($this->wizArray["moduleFunction"]);
			while(list($k,$config)=each($this->wizArray["moduleFunction"]))	{
				$this->renderExtPart_moduleFunction($k,$config,$extKey);
			}
		}

		if (is_array($this->wizArray["cm"]))	{
			reset($this->wizArray["cm"]);
			while(list($k,$config)=each($this->wizArray["cm"]))	{
				$this->renderExtPart_cm($k,$config,$extKey);
			}
		}

		// This should be BEFORE PI.
		if (is_array($this->wizArray["fields"]))	{
			reset($this->wizArray["fields"]);
			while(list($k,$config)=each($this->wizArray["fields"]))	{
				$this->renderExtPart_fields($k,$config,$extKey);
				$this->EM_CONF_presets["modify_tables"][]=$config["which_table"];
			}
		}

		if (is_array($this->wizArray["tables"]))	{
			reset($this->wizArray["tables"]);
			while(list($k,$config)=each($this->wizArray["tables"]))	{
				$this->renderExtPart_tables($k,$config,$extKey);
			}
		}

		if (is_array($this->wizArray["pi"]))	{
			reset($this->wizArray["pi"]);
			while(list($k,$config)=each($this->wizArray["pi"]))	{
				$this->renderExtPart_PI($k,$config,$extKey);
				$this->EM_CONF_presets["clearCacheOnLoad"]=1;
			}
			$this->EM_CONF_presets["dependencies"][]="cms";
		}

		if (is_array($this->wizArray["sv"]))	{
			reset($this->wizArray["sv"]);
			while(list($k,$config)=each($this->wizArray["sv"]))	{
				$this->renderExtPart_SV($k,$config,$extKey);
				$this->EM_CONF_presets["clearCacheOnLoad"]=1;
			}
		}

		// Write the ext_localconf.php file:
		if (count($this->ext_localconf))	{
			$this->addFileToFileArray("ext_localconf.php",trim($this->wrapBody('
				<?php
				if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
					',
				implode(chr(10),$this->ext_localconf),
				'?>
			',0)));
		}
		// Write the ext_tables.php file:
		if (count($this->ext_tables))	{
			$this->addFileToFileArray("ext_tables.php",trim($this->wrapBody('
				<?php
				if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

				',implode(chr(10),$this->ext_tables),'
				?>
			',0)));
		}
		// Write the tca.php file:
		if (count($this->ext_tca))	{
			$this->addFileToFileArray("tca.php",trim($this->wrapBody('
				<?php
				if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

				',implode(chr(10),$this->ext_tca),'
				?>
			',0)));
		}
		// Write the ext_tables.sql file:
		if (count($this->ext_tables_sql))	{
			$this->addFileToFileArray("ext_tables.sql",trim($this->sPS(implode(chr(10),$this->ext_tables_sql))));
		}
		// Local lang file:
		if (count($this->ext_locallang))	{
			$this->addLocalLangFile($this->ext_locallang,"locallang.php",'Language labels for extension "'.$extKey.'"');
		}
		// Local lang DB file:
		if (count($this->ext_locallang_db))	{
			$this->addLocalLangFile($this->ext_locallang_db,"locallang_db.php",'Language labels for database tables/fields belonging to extension "'.$extKey.'"');
		}

		// The form used to generate the extension:
		$this->dontPrintImages = 1;
		$this->addFileToFileArray("doc/wizard_form.html",trim($this->sPS('
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

			<html>
			<head>
				<title>Untitled</title>
			</head>

			<body>

				'.$this->totalForm().'
			</body>
			</html>
		')));
		$this->addFileToFileArray("doc/wizard_form.dat",serialize($this->wizArray));

			// icon:
		$this->addFileToFileArray("ext_icon.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/notfound.gif"));


#		debug($this->wizArray);
#		debug ($this->fileArray);
#		return $dataArr;
	}


	function addLocalLangFile($arr,$filename,$description)	{
		$lines=array();
		reset($arr);
		$lines[]='<?php';
		$lines[]=trim($this->sPS('
			/**
			 * '.$description.'
			 *
			 * This file is detected by the translation tool.
			 */
		'));
		$lines[]='';
		$lines[]='$LOCAL_LANG = Array (';
		while(list($lK,$labels)=each($arr))	{
			if (is_array($labels))	{
				$lines[]='	"'.$lK.'" => Array (';
				while(list($l,$v)=each($labels))	{
					if (strcmp($v[0],""))	$lines[]='		"'.$l.'" => "'.addslashes($v[0]).'",	'.$this->WOPcomment($v[1]);
				}
				$lines[]='	),';
			}
		}
		$lines[]=');';
		$lines[]='?>';
		$this->addFileToFileArray($filename,implode(chr(10),$lines));
	}

	/**
	 * MAKES a Plugin
	 */
	function renderExtPart_fields($k,$config,$extKey)	{
		$WOP="[fields][".$k."]";
		$tableName=$config["which_table"];
	#	$tableName = $this->returnName($extKey,"fields",$tableName);
#		$prefix = "tx_".str_replace("_","",$extKey)."_";
		$prefix = $this->returnName($extKey,"fields")."_";

		$DBfields=array();
		$columns=array();
		$ctrl=array();
		$enFields=array();

		if (is_array($config["fields"]))	{
			reset($config["fields"]);
			while(list($i,$fConf)=each($config["fields"]))	{
				$fConf["fieldname"] = $prefix.$fConf["fieldname"];
				$this->makeFieldTCA($DBfields,$columns,$fConf,$WOP."[fields][".$i."]",$tableName,$extKey);
			}
		}

		if ($tableName=="tt_address")	$this->EM_CONF_presets["dependencies"][]="tt_address";
		if ($tableName=="tt_news")	$this->EM_CONF_presets["dependencies"][]="tt_news";
		if (t3lib_div::inList("tt_content,fe_users,fe_groups",$tableName))	$this->EM_CONF_presets["dependencies"][]="cms";

		$createTable = $this->wrapBody('
			#
			# Table structure for table \''.$tableName.'\'
			#
			CREATE TABLE '.$tableName.' (
		', ereg_replace(",[[:space:]]*$","",implode(chr(10),$DBfields)), '

			);
		');
		$this->ext_tables_sql[]=chr(10).$createTable.chr(10);


			// Finalize ext_tables.php:
		$this->ext_tables[]=$this->wrapBody('
			$tempColumns = Array (
				', implode(chr(10),$columns)	,'
			);
		');


		list($typeList) = $this->implodeColumns($columns);
		$applyToAll=1;
		if (is_array($this->wizArray["pi"]))	{
			reset($this->wizArray["pi"]);
			while(list(,$fC)=each($this->wizArray["pi"]))	{
				if ($fC["apply_extended"]==$k)	{
					$applyToAll=0;
					$this->_apply_extended_types[$k]=$typeList;
				}
			}
		}
		$this->ext_tables[]=$this->sPS('
			t3lib_div::loadTCA("'.$tableName.'");
			t3lib_extMgm::addTCAcolumns("'.$tableName.'",$tempColumns,1);
			'.($applyToAll?'t3lib_extMgm::addToAllTCAtypes("'.$tableName.'","'.$typeList.'");':'').'
		');
	}

	/**
	 * MAKES a Plugin
	 */
	function renderExtPart_tables($k,$config,$extKey)	{
		$WOP="[tables][".$k."]";
		$tableName=$config["tablename"];
		$tableName = $this->returnName($extKey,"tables",$tableName);

		$DBfields=array();
		$columns=array();
		$ctrl=array();
		$enFields=array();

//str_replace("\\'","'",addslashes($this->getSplitLabels($config,"title")))
		$ctrl[] = trim($this->sPS('
			"title" => "'.$this->getSplitLabels_reference($config,"title",$tableName).'",		'.$this->WOPcomment('WOP:'.$WOP.'[title]').'
			"label" => "'.($config["header_field"]?$config["header_field"]:"uid").'",	'.$this->WOPcomment('WOP:'.$WOP.'[header_field]').'
			"tstamp" => "tstamp",
			"crdate" => "crdate",
			"cruser_id" => "cruser_id",
		',0));
		$DBfields[] = trim($this->sPS("
			uid int(11) DEFAULT '0' NOT NULL auto_increment,
			pid int(11) DEFAULT '0' NOT NULL,
			tstamp int(11) unsigned DEFAULT '0' NOT NULL,
			crdate int(11) unsigned DEFAULT '0' NOT NULL,
			cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
		",0));

		if ($config["type_field"])	{
			$ctrl[] = '"type" => "'.$config["type_field"].'",	'.$this->WOPcomment('WOP:'.$WOP.'[type_field]');
		}
		if ($config["versioning"])	{
			$ctrl[] = '"versioning" => "1",	'.$this->WOPcomment('WOP:'.$WOP.'[versioning]');
			$DBfields[] = "t3ver_oid int(11) unsigned DEFAULT '0' NOT NULL,";
			$DBfields[] = "t3ver_id int(11) unsigned DEFAULT '0' NOT NULL,";
			$DBfields[] = "t3ver_label varchar(30) DEFAULT '' NOT NULL,";
		}
		if ($config["localization"])	{
			$ctrl[] = '"languageField" => "sys_language_uid",	'.$this->WOPcomment('WOP:'.$WOP.'[localization]');
			$ctrl[] = '"transOrigPointerField" => "l18n_parent",	'.$this->WOPcomment('WOP:'.$WOP.'[localization]');
			$ctrl[] = '"transOrigDiffSourceField" => "l18n_diffsource",	'.$this->WOPcomment('WOP:'.$WOP.'[localization]');

			$DBfields[] = "sys_language_uid int(11) DEFAULT '0' NOT NULL,";
			$DBfields[] = "l18n_parent int(11) DEFAULT '0' NOT NULL,";
			$DBfields[] = "l18n_diffsource mediumblob NOT NULL,";

			$columns["sys_language_uid"] = trim($this->sPS("
				'sys_language_uid' => Array (		".$this->WOPcomment('WOP:'.$WOP.'[localization]')."
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
					'config' => Array (
						'type' => 'select',
						'foreign_table' => 'sys_language',
						'foreign_table_where' => 'ORDER BY sys_language.title',
						'items' => Array(
							Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
							Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
						)
					)
				),
			"));

			$columns["l18n_parent"] = trim($this->sPS("
				'l18n_parent' => Array (		".$this->WOPcomment('WOP:'.$WOP.'[localization]')."
					'displayCond' => 'FIELD:sys_language_uid:>:0',
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
					'config' => Array (
						'type' => 'select',
						'items' => Array (
							Array('', 0),
						),
						'foreign_table' => '".$tableName."',
						'foreign_table_where' => 'AND ".$tableName.".pid=###CURRENT_PID### AND ".$tableName.".sys_language_uid IN (-1,0)',
					)
				),
			"));

			$columns["l18n_diffsource"] = trim($this->sPS("
				'l18n_diffsource' => Array (		".$this->WOPcomment('WOP:'.$WOP.'[localization]')."
					'config' => Array (
						'type' => 'passthrough'
					)
				),
			"));
		}
		if ($config["sorting"])	{
			$ctrl[] = '"sortby" => "sorting",	'.$this->WOPcomment('WOP:'.$WOP.'[sorting]');
			$DBfields[] = "sorting int(10) unsigned DEFAULT '0' NOT NULL,";
		} else {
			$ctrl[] = '"default_sortby" => "ORDER BY '.trim($config["sorting_field"].' '.($config["sorting_desc"]?"DESC":"")).'",	'.$this->WOPcomment('WOP:'.$WOP.'[sorting] / '.$WOP.'[sorting_field] / '.$WOP.'[sorting_desc]');
		}
		if ($config["add_deleted"])	{
			$ctrl[] = '"delete" => "deleted",	'.$this->WOPcomment('WOP:'.$WOP.'[add_deleted]');
			$DBfields[] = "deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,";
		}
		if ($config["add_hidden"])	{
			$enFields[] = '"disabled" => "hidden",	'.$this->WOPcomment('WOP:'.$WOP.'[add_hidden]');
			$DBfields[] = "hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,";
			$columns["hidden"] = trim($this->sPS('
				"hidden" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[add_hidden]').'
					"exclude" => 1,
					"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
					"config" => Array (
						"type" => "check",
						"default" => "0"
					)
				),
			'));
		}
		if ($config["add_starttime"])	{
			$enFields[] = '"starttime" => "starttime",	'.$this->WOPcomment('WOP:'.$WOP.'[add_starttime]');
			$DBfields[] = "starttime int(11) unsigned DEFAULT '0' NOT NULL,";
			$columns["starttime"] = trim($this->sPS('
				"starttime" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[add_starttime]').'
					"exclude" => 1,
					"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
					"config" => Array (
						"type" => "input",
						"size" => "8",
						"max" => "20",
						"eval" => "date",
						"default" => "0",
						"checkbox" => "0"
					)
				),
			'));
		}
		if ($config["add_endtime"])	{
			$enFields[] = '"endtime" => "endtime",	'.$this->WOPcomment('WOP:'.$WOP.'[add_endtime]');
			$DBfields[] = "endtime int(11) unsigned DEFAULT '0' NOT NULL,";
			$columns["endtime"] = trim($this->sPS('
				"endtime" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[add_endtime]').'
					"exclude" => 1,
					"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
					"config" => Array (
						"type" => "input",
						"size" => "8",
						"max" => "20",
						"eval" => "date",
						"checkbox" => "0",
						"default" => "0",
						"range" => Array (
							"upper" => mktime(0,0,0,12,31,2020),
							"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
						)
					)
				),
			'));
		}
		if ($config["add_access"])	{
			$enFields[] = '"fe_group" => "fe_group",	'.$this->WOPcomment('WOP:'.$WOP.'[add_access]');
			$DBfields[] = "fe_group int(11) DEFAULT '0' NOT NULL,";
			$columns["fe_group"] = trim($this->sPS('
				"fe_group" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[add_access]').'
					"exclude" => 1,
					"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
					"config" => Array (
						"type" => "select",
						"items" => Array (
							Array("", 0),
							Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
							Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
							Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
						),
						"foreign_table" => "fe_groups"
					)
				),
			'));
		}
			// Add enable fields in header:
		if (is_array($enFields) && count($enFields))	{
			$ctrl[]=trim($this->wrapBody('
				"enablecolumns" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[add_hidden] / '.$WOP.'[add_starttime] / '.$WOP.'[add_endtime] / '.$WOP.'[add_access]').'
				',implode(chr(10),$enFields),'
				),
			'));
		}
			// Add dynamic config file.
		$ctrl[]= '"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",';
		$ctrl[]= '"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_'.$tableName.'.gif",';

		if ($config["allow_on_pages"])	{
			$this->ext_tables[]=$this->sPS('
				'.$this->WOPcomment('WOP:'.$WOP.'[allow_on_pages]').'
				t3lib_extMgm::allowTableOnStandardPages("'.$tableName.'");
			');
		}
		if ($config["allow_ce_insert_records"])	{
			$this->ext_tables[]=$this->sPS('
				'.$this->WOPcomment('WOP:'.$WOP.'[allow_ce_insert_records]').'
				t3lib_extMgm::addToInsertRecords("'.$tableName.'");
			');
		}
		if ($config["save_and_new"])	{
			$this->ext_localconf[]=trim($this->wrapBody("
				t3lib_extMgm::addUserTSConfig('
					","options.saveDocNew.".$tableName."=1","
				');
			"));
		}

		if (is_array($config["fields"]))	{
			reset($config["fields"]);
			while(list($i,$fConf)=each($config["fields"]))	{
				$this->makeFieldTCA($DBfields,$columns,$fConf,$WOP."[fields][".$i."]",$tableName,$extKey);
			}
		}



			// Finalize tables.sql:
		$DBfields[]=$this->sPS('
			PRIMARY KEY (uid),
			KEY parent (pid)
		');
		$createTable = $this->wrapBody('
			#
			# Table structure for table \''.$tableName.'\'
			#
			CREATE TABLE '.$tableName.' (
		', implode(chr(10),$DBfields), '
			);
		');
		$this->ext_tables_sql[]=chr(10).$createTable.chr(10);

			// Finalize tca.php:
		$tca_file="";
		list($typeList,$palList) = $this->implodeColumns($columns);
		$tca_file.=$this->wrapBody('
			$TCA["'.$tableName.'"] = Array (
				"ctrl" => $TCA["'.$tableName.'"]["ctrl"],
				"interface" => Array (
					"showRecordFieldList" => "'.implode(",",array_keys($columns)).'"
				),
				"feInterface" => $TCA["'.$tableName.'"]["feInterface"],
				"columns" => Array (
			', trim(implode(chr(10),$columns))	,'
				),
				"types" => Array (
					"0" => Array("showitem" => "'.$typeList.'")
				),
				"palettes" => Array (
					"1" => Array("showitem" => "'.$palList.'")
				)
			);
		',2);
		$this->ext_tca[]=chr(10).$tca_file.chr(10);

			// Finalize ext_tables.php:
		$this->ext_tables[]=$this->wrapBody('
			$TCA["'.$tableName.'"] = Array (
				"ctrl" => Array (
			', implode(chr(10),$ctrl)	,'
				),
				"feInterface" => Array (
					"fe_admin_fieldList" => "'.implode(", ",array_keys($columns)).'",
				)
			);
		',2);


				// Add wizard icon
			$this->addFileToFileArray($pathSuffix."icon_".$tableName.".gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/".$config["defIcon"]));

	}

	function implodeColumns($columns)	{
		reset($columns);
		$outems=array();
		$paltems=array();
		$c=0;
		$hiddenFlag=0;
		$titleDivFlag=0;
		while(list($fN)=each($columns))	{
			if (!$hiddenFlag || !t3lib_div::inList("starttime,endtime,fe_group",$fN))	{
				$outTem = array($fN,"","","","");
				$outTem[3] = $this->_typeP[$fN];
				if ($c==0)	$outTem[4]="1-1-1";
				if ($fN=="title")	{
					$outTem[4]="2-2-2";
					$titleDivFlag=1;
				} elseif ($titleDivFlag)	{
					$outTem[4]="3-3-3";
					$titleDivFlag=0;
				}
				if ($fN=="hidden")	{
					$outTem[2]="1";
					$hiddenFlag=1;
				}
				$outems[] = str_replace(",","",str_replace(chr(9),";",trim(str_replace(";","",implode(chr(9),$outTem)))));
				$c++;
			} else {
				$paltems[]=$fN;
			}
		}
		return array(implode(", ",$outems),implode(", ",$paltems));
	}
	function makeFieldTCA(&$DBfields,&$columns,$fConf,$WOP,$table,$extKey)	{
		if (!(string)$fConf["type"])	return;
		$id = $table."_".$fConf["fieldname"];
#debug($fConf);

		$configL=array();
		$t = (string)$fConf["type"];
		switch($t)	{
			case "input":
			case "input+":
				$isString =1;
				$configL[]='"type" => "input",	'.$this->WOPcomment('WOP:'.$WOP.'[type]');
				$configL[]='"size" => "'.t3lib_div::intInRange($fConf["conf_size"],5,48,30).'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_size]');
				if (intval($fConf["conf_max"]))	$configL[]='"max" => "'.t3lib_div::intInRange($fConf["conf_max"],1,255).'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_max]');

				$evalItems=array();
				if ($fConf["conf_required"])	{$evalItems[0][] = "required";			$evalItems[1][] = $WOP.'[conf_required]';}

				if ($t=="input+")	{
					$isString = !$fConf["conf_eval"] || t3lib_div::inList("alphanum,upper,lower",$fConf["conf_eval"]);
					if ($fConf["conf_varchar"] && $isString)		{$evalItems[0][] = "trim";			$evalItems[1][] = $WOP.'[conf_varchar]';}
					if ($fConf["conf_eval"]=="int+")	{
						$configL[]='"range" => Array ("lower"=>0,"upper"=>1000),	'.$this->WOPcomment('WOP:'.$WOP.'[conf_eval] = int+ results in a range setting');
						$fConf["conf_eval"]="int";
					}
					if ($fConf["conf_eval"])		{$evalItems[0][] = $fConf["conf_eval"];			$evalItems[1][] = $WOP.'[conf_eval]';}
					if ($fConf["conf_check"])	$configL[]='"checkbox" => "'.($isString?"":"0").'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_check]');

					if ($fConf["conf_stripspace"])		{$evalItems[0][] = "nospace";			$evalItems[1][] = $WOP.'[conf_stripspace]';}
					if ($fConf["conf_pass"])		{$evalItems[0][] = "password";			$evalItems[1][] = $WOP.'[conf_pass]';}
					if ($fConf["conf_unique"])	{
						if ($fConf["conf_unique"]=="L")		{$evalItems[0][] = "uniqueInPid";			$evalItems[1][] = $WOP.'[conf_unique] = Local (unique in this page (PID))';}
						if ($fConf["conf_unique"]=="G")		{$evalItems[0][] = "unique";			$evalItems[1][] = $WOP.'[conf_unique] = Global (unique in whole database)';}
					}

					$wizards =array();
					if ($fConf["conf_wiz_color"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_color]').'
							"color" => Array(
								"title" => "Color:",
								"type" => "colorbox",
								"dim" => "12x12",
								"tableStyle" => "border:solid 1px black;",
								"script" => "wizard_colorpicker.php",
								"JSopenParams" => "height=300,width=250,status=0,menubar=0,scrollbars=1",
							),
						'));
					}
					if ($fConf["conf_wiz_link"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_link]').'
							"link" => Array(
								"type" => "popup",
								"title" => "Link",
								"icon" => "link_popup.gif",
								"script" => "browse_links.php?mode=wizard",
								"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
							),
						'));
					}
					if (count($wizards))	{
						$configL[]=trim($this->wrapBody('
							"wizards" => Array(
								"_PADDING" => 2,
								',implode(chr(10),$wizards),'
							),
						'));
					}
				} else {
					if ($fConf["conf_varchar"])		{$evalItems[0][] = "trim";			$evalItems[1][] = $WOP.'[conf_varchar]';}
				}

				if (count($evalItems))	$configL[]='"eval" => "'.implode(",",$evalItems[0]).'",	'.$this->WOPcomment('WOP:'.implode(" / ",$evalItems[1]));

				if (!$isString)	{
					$DBfields[] = $fConf["fieldname"]." int(11) DEFAULT '0' NOT NULL,";
				} elseif (!$fConf["conf_varchar"])		{
					$DBfields[] = $fConf["fieldname"]." tinytext NOT NULL,";
				} else {
					$varCharLn = (intval($fConf["conf_max"])?t3lib_div::intInRange($fConf["conf_max"],1,255):255);
					$DBfields[] = $fConf["fieldname"]." ".($varCharLn>$this->charMaxLng?'var':'')."char(".$varCharLn.") DEFAULT '' NOT NULL,";
				}
			break;
			case "link":
				$DBfields[] = $fConf["fieldname"]." tinytext NOT NULL,";
				$configL[]=trim($this->sPS('
					"type" => "input",
					"size" => "15",
					"max" => "255",
					"checkbox" => "",
					"eval" => "trim",
					"wizards" => Array(
						"_PADDING" => 2,
						"link" => Array(
							"type" => "popup",
							"title" => "Link",
							"icon" => "link_popup.gif",
							"script" => "browse_links.php?mode=wizard",
							"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
						)
					)
				'));
			break;
			case "datetime":
			case "date":
				$DBfields[] = $fConf["fieldname"]." int(11) DEFAULT '0' NOT NULL,";
				$configL[]=trim($this->sPS('
					"type" => "input",
					"size" => "'.($t=="datetime"?12:8).'",
					"max" => "20",
					"eval" => "'.$t.'",
					"checkbox" => "0",
					"default" => "0"
				'));
			break;
			case "integer":
				$DBfields[] = $fConf["fieldname"]." int(11) DEFAULT '0' NOT NULL,";
				$configL[]=trim($this->sPS('
					"type" => "input",
					"size" => "4",
					"max" => "4",
					"eval" => "int",
					"checkbox" => "0",
					"range" => Array (
						"upper" => "1000",
						"lower" => "10"
					),
					"default" => 0
				'));
			break;
			case "textarea":
			case "textarea_nowrap":
				$DBfields[] = $fConf["fieldname"]." text NOT NULL,";
				$configL[]='"type" => "text",';
				if ($t=="textarea_nowrap")	{
					$configL[]='"wrap" => "OFF",';
				}
				$configL[]='"cols" => "'.t3lib_div::intInRange($fConf["conf_cols"],5,48,30).'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_cols]');
				$configL[]='"rows" => "'.t3lib_div::intInRange($fConf["conf_rows"],1,20,5).'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rows]');
				if ($fConf["conf_wiz_example"])	{
					$wizards =array();
					$wizards[] = trim($this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_example]').'
						"example" => Array(
							"title" => "Example Wizard:",
							"type" => "script",
							"notNewRecords" => 1,
							"icon" => t3lib_extMgm::extRelPath("'.$extKey.'")."'.$id.'/wizard_icon.gif",
							"script" => t3lib_extMgm::extRelPath("'.$extKey.'")."'.$id.'/index.php",
						),
					'));

					$cN = $this->returnName($extKey,"class",$id."wiz");
					$this->writeStandardBE_xMod(
						$extKey,
						array("title"=>"Example Wizard title..."),
						$id.'/',
						$cN,
						0,
						$id."wiz"
					);
					$this->addFileToFileArray($id."/wizard_icon.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/notfound.gif"));

					$configL[]=trim($this->wrapBody('
						"wizards" => Array(
							"_PADDING" => 2,
							',implode(chr(10),$wizards),'
						),
					'));
				}
			break;
			case "textarea_rte":
				$DBfields[] = $fConf["fieldname"]." text NOT NULL,";
				$configL[]='"type" => "text",';
				$configL[]='"cols" => "30",';
				$configL[]='"rows" => "5",';
				if ($fConf["conf_rte_fullscreen"])	{
					$wizards =array();
					$wizards[] = trim($this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_fullscreen]').'
						"RTE" => Array(
							"notNewRecords" => 1,
							"RTEonly" => 1,
							"type" => "script",
							"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
							"icon" => "wizard_rte2.gif",
							"script" => "wizard_rte.php",
						),
					'));
					$configL[]=trim($this->wrapBody('
						"wizards" => Array(
							"_PADDING" => 2,
							',implode(chr(10),$wizards),'
						),
					'));
				}

				$rteImageDir = "";
				if ($fConf["conf_rte_separateStorageForImages"] && t3lib_div::inList("moderate,basic,custom",$fConf["conf_rte"]))	{
					$this->EM_CONF_presets["createDirs"][]=$this->ulFolder($extKey)."rte/";
					$rteImageDir = "|imgpath=".$this->ulFolder($extKey)."rte/";
				}

				$transformation="ts_images-ts_reglinks";
				if ($fConf["conf_mode_cssOrNot"] && t3lib_div::inList("moderate,custom",$fConf["conf_rte"]))	{
					$transformation="ts_css";
				}


				switch($fConf["conf_rte"])	{
					case "tt_content":
						$typeP = 'richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts]';
					break;
					case "moderate":
						$typeP = 'richtext[*]:rte_transform[mode='.$transformation.''.$rteImageDir.']';
					break;
					case "basic":
						$typeP = 'richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css'.$rteImageDir.']';
						$this->ext_localconf[]=trim($this->wrapBody("
								t3lib_extMgm::addPageTSConfig('

									# ***************************************************************************************
									# CONFIGURATION of RTE in table \"".$table."\", field \"".$fConf["fieldname"]."\"
									# ***************************************************************************************

									",trim($this->slashValueForSingleDashes(str_replace(chr(9),"  ",$this->sPS("
										RTE.config.".$table.".".$fConf["fieldname"]." {
											hidePStyleItems = H1, H4, H5, H6
											proc.exitHTMLparser_db=1
											proc.exitHTMLparser_db {
												keepNonMatchedTags=1
												tags.font.allowedAttribs= color
												tags.font.rmTagIfNoAttrib = 1
												tags.font.nesting = global
											}
										}
									")))),"
								');
						",0));
					break;
					case "none":
						$typeP = 'richtext[*]';
					break;
					case "custom":
						$enabledButtons=array();
						$traverseList = explode(",","cut,copy,paste,formatblock,class,fontstyle,fontsize,textcolor,bold,italic,underline,left,center,right,orderedlist,unorderedlist,outdent,indent,link,table,image,line,user,chMode");
						$HTMLparser=array();
						$fontAllowedAttrib=array();
						$allowedTags_WOP = array();
						$allowedTags=array();
						while(list(,$lI)=each($traverseList))	{
							$nothingDone=0;
							if ($fConf["conf_rte_b_".$lI])	{
								$enabledButtons[]=$lI;
								switch($lI)	{
									case "formatblock":
									case "left":
									case "center":
									case "right":
										$allowedTags[]="div";
										$allowedTags[]="p";
									break;
									case "class":
										$allowedTags[]="span";
									break;
									case "fontstyle":
										$allowedTags[]="font";
										$fontAllowedAttrib[]="face";
									break;
									case "fontsize":
										$allowedTags[]="font";
										$fontAllowedAttrib[]="size";
									break;
									case "textcolor":
										$allowedTags[]="font";
										$fontAllowedAttrib[]="color";
									break;
									case "bold":
										$allowedTags[]="b";
										$allowedTags[]="strong";
									break;
									case "italic":
										$allowedTags[]="i";
										$allowedTags[]="em";
									break;
									case "underline":
										$allowedTags[]="u";
									break;
									case "orderedlist":
										$allowedTags[]="ol";
										$allowedTags[]="li";
									break;
									case "unorderedlist":
										$allowedTags[]="ul";
										$allowedTags[]="li";
									break;
									case "outdent":
									case "indent":
										$allowedTags[]="blockquote";
									break;
									case "link":
										$allowedTags[]="a";
									break;
									case "table":
										$allowedTags[]="table";
										$allowedTags[]="tr";
										$allowedTags[]="td";
									break;
									case "image":
										$allowedTags[]="img";
									break;
									case "line":
										$allowedTags[]="hr";
									break;
									default:
										$nothingDone=1;
									break;
								}
								if (!$nothingDone)	$allowedTags_WOP[] = $WOP.'[conf_rte_b_'.$lI.']';
							}
						}
						if (count($fontAllowedAttrib))	{
							$HTMLparser[]="tags.font.allowedAttribs = ".implode(",",$fontAllowedAttrib);
							$HTMLparser[]="tags.font.rmTagIfNoAttrib = 1";
							$HTMLparser[]="tags.font.nesting = global";
						}
						if (count($enabledButtons))	{
							$typeP = 'richtext['.implode("|",$enabledButtons).']:rte_transform[mode='.$transformation.''.$rteImageDir.']';
						}

						$rte_colors=array();
						$setupUpColors=array();
						for ($a=1;$a<=3;$a++)	{
							if ($fConf["conf_rte_color".$a])	{
								$rte_colors[$id.'_color'.$a]=trim($this->sPS('
									'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_color'.$a.']').'
									'.$id.'_color'.$a.' {
										name = Color '.$a.'
										value = '.$fConf["conf_rte_color".$a].'
									}
								'));
								$setupUpColors[]=trim($fConf["conf_rte_color".$a]);
							}
						}

						$rte_classes=array();
						for ($a=1;$a<=6;$a++)	{
							if ($fConf["conf_rte_class".$a])	{
								$rte_classes[$id.'_class'.$a]=trim($this->sPS('
									'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class'.$a.']').'
									'.$id.'_class'.$a.' {
										name = '.$fConf["conf_rte_class".$a].'
										value = '.$fConf["conf_rte_class".$a."_style"].'
									}
								'));
							}
						}

						$PageTSconfig= Array();
						if ($fConf["conf_rte_removecolorpicker"])	{
							$PageTSconfig[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removecolorpicker]');
							$PageTSconfig[]="disableColorPicker = 1";
						}
						if (count($rte_classes))	{
							$PageTSconfig[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class*]');
							$PageTSconfig[]="classesParagraph = ".implode(", ",array_keys($rte_classes));
							$PageTSconfig[]="classesCharacter = ".implode(", ",array_keys($rte_classes));
							if (in_array("p",$allowedTags) || in_array("div",$allowedTags))	{
								$HTMLparser[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class*]');
								if (in_array("p",$allowedTags))	{$HTMLparser[]="p.fixAttrib.class.list = ,".implode(",",array_keys($rte_classes));}
								if (in_array("div",$allowedTags))	{$HTMLparser[]="div.fixAttrib.class.list = ,".implode(",",array_keys($rte_classes));}
							}
						}
						if (count($rte_colors))		{
							$PageTSconfig[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_color*]');
							$PageTSconfig[]="colors = ".implode(", ",array_keys($rte_colors));

							if (in_array("color",$fontAllowedAttrib) && $fConf["conf_rte_removecolorpicker"])	{
								$HTMLparser[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removecolorpicker]');
								$HTMLparser[]="tags.font.fixAttrib.color.list = ,".implode(",",$setupUpColors);
								$HTMLparser[]="tags.font.fixAttrib.color.removeIfFalse = 1";
							}
						}
						if (!strcmp($fConf["conf_rte_removePdefaults"],1))	{
							$PageTSconfig[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removePdefaults]');
							$PageTSconfig[]="hidePStyleItems = H1, H2, H3, H4, H5, H6, PRE";
						} elseif ($fConf["conf_rte_removePdefaults"]=="H2H3")	{
							$PageTSconfig[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removePdefaults]');
							$PageTSconfig[]="hidePStyleItems = H1, H4, H5, H6";
						} else {
							$allowedTags[]="h1";
							$allowedTags[]="h2";
							$allowedTags[]="h3";
							$allowedTags[]="h4";
							$allowedTags[]="h5";
							$allowedTags[]="h6";
							$allowedTags[]="pre";
						}


						$allowedTags = array_unique($allowedTags);
						if (count($allowedTags))	{
							$HTMLparser[]="	".$this->WOPcomment('WOP:'.implode(" / ",$allowedTags_WOP));
							$HTMLparser[]='allowTags = '.implode(", ",$allowedTags);
						}
						if ($fConf["conf_rte_div_to_p"])	{
							$HTMLparser[]="	".$this->WOPcomment('WOP:'.$WOP.'[conf_rte_div_to_p]');
							$HTMLparser[]='tags.div.remap = P';
						}
						if (count($HTMLparser))	{
							$PageTSconfig[]=trim($this->wrapBody('
								proc.exitHTMLparser_db=1
								proc.exitHTMLparser_db {
									',implode(chr(10),$HTMLparser),'
								}
							'));
						}

						$finalPageTSconfig=array();
						if (count($rte_colors))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.colors {
								',implode(chr(10),$rte_colors),'
								}
							'));
						}
						if (count($rte_classes))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.classes {
								',implode(chr(10),$rte_classes),'
								}
							'));
						}
						if (count($PageTSconfig))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.config.'.$table.'.'.$fConf["fieldname"].' {
								',implode(chr(10),$PageTSconfig),'
								}
							'));
						}
						if (count($finalPageTSconfig))	{
							$this->ext_localconf[]=trim($this->wrapBody("
								t3lib_extMgm::addPageTSConfig('

									# ***************************************************************************************
									# CONFIGURATION of RTE in table \"".$table."\", field \"".$fConf["fieldname"]."\"
									# ***************************************************************************************

								",trim($this->slashValueForSingleDashes(str_replace(chr(9),"  ",implode(chr(10).chr(10),$finalPageTSconfig)))),"
								');
							",0));
						}
					break;
				}
				$this->_typeP[$fConf["fieldname"]]	= $typeP;
			break;
			case "check":
			case "check_4":
			case "check_10":
				$configL[]='"type" => "check",';
				if ($t=="check")	{
					$DBfields[] = $fConf["fieldname"]." tinyint(3) unsigned DEFAULT '0' NOT NULL,";
					if ($fConf["conf_check_default"])	$configL[]='"default" => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_check_default]');
				} else {
					$DBfields[] = $fConf["fieldname"]." int(11) unsigned DEFAULT '0' NOT NULL,";
				}
				if ($t=="check_4" || $t=="check_10")	{
					$configL[]='"cols" => 4,';
					$cItems=array();
#					$aMax = ($t=="check_4"?4:10);
					$aMax = intval($fConf["conf_numberBoxes"]);
					for($a=0;$a<$aMax;$a++)	{
//						$cItems[]='Array("'.($fConf["conf_boxLabel_".$a]?str_replace("\\'","'",addslashes($this->getSplitLabels($fConf,"conf_boxLabel_".$a))):'English Label '.($a+1).'|Danish Label '.($a+1).'|German Label '.($a+1).'| etc...').'", ""),';
						$cItems[]='Array("'.addslashes($this->getSplitLabels_reference($fConf,"conf_boxLabel_".$a,$table.".".$fConf["fieldname"].".I.".$a)).'", ""),';
					}
					$configL[]=trim($this->wrapBody('
						"items" => Array (
							',implode(chr(10),$cItems),'
						),
					'));
				}
			break;
			case "radio":
			case "select":
				$configL[]='"type" => "'.($t=="select"?"select":"radio").'",';
				$notIntVal=0;
				$len=array();
				for($a=0;$a<t3lib_div::intInRange($fConf["conf_select_items"],1,20);$a++)	{
					$val = $fConf["conf_select_itemvalue_".$a];
					$notIntVal+= t3lib_div::testInt($val)?0:1;
					$len[]=strlen($val);
					if ($fConf["conf_select_icons"] && $t=="select")	{
						$icon = ', t3lib_extMgm::extRelPath("'.$extKey.'")."'."selicon_".$id."_".$a.".gif".'"';
										// Add wizard icon
						$this->addFileToFileArray("selicon_".$id."_".$a.".gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/wiz.gif"));
					} else $icon="";
//					$cItems[]='Array("'.str_replace("\\'","'",addslashes($this->getSplitLabels($fConf,"conf_select_item_".$a))).'", "'.addslashes($val).'"'.$icon.'),';
					$cItems[]='Array("'.addslashes($this->getSplitLabels_reference($fConf,"conf_select_item_".$a,$table.".".$fConf["fieldname"].".I.".$a)).'", "'.addslashes($val).'"'.$icon.'),';
				}
				$configL[]=trim($this->wrapBody('
					'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_items]').'
					"items" => Array (
						',implode(chr(10),$cItems),'
					),
				'));
				if ($fConf["conf_select_pro"] && $t=="select")	{
					$cN = $this->returnName($extKey,"class",$id);
					$configL[]='"itemsProcFunc" => "'.$cN.'->main",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_pro]');

					$classContent= $this->sPS('
						class '.$cN.' {
							function main(&$params,&$pObj)	{
/*								debug("Hello World!",1);
								debug("\$params:",1);
								debug($params);
								debug("\$pObj:",1);
								debug($pObj);
	*/
									// Adding an item!
								$params["items"][]=Array($pObj->sL("Added label by PHP function|Tilføjet Dansk tekst med PHP funktion"), 999);

								// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
							}
						}
					');

					$this->addFileToFileArray("class.".$cN.".php",$this->PHPclassFile($extKey,"class.".$cN.".php",$classContent,"Class/Function which manipulates the item-array for table/field ".$id."."));

					$this->ext_tables[]=$this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_pro]:').'
						if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("'.$extKey.'")."'.'class.'.$cN.'.php");
					');
				}

				$numberOfRelations = t3lib_div::intInRange($fConf["conf_relations"],1,100);
				if ($t=="select")	{
					$configL[]='"size" => '.t3lib_div::intInRange($fConf["conf_relations_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_selsize]');
					$configL[]='"maxitems" => '.$numberOfRelations.',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations]');
				}

				if ($numberOfRelations>1 && $t=="select")	{
					if ($numberOfRelations*4 < 256)	{
						$DBfields[] = $fConf["fieldname"]." varchar(".($numberOfRelations*4).") DEFAULT '' NOT NULL,";
					} else {
						$DBfields[] = $fConf["fieldname"]." text NOT NULL,";
					}
				} elseif ($notIntVal)	{
					$varCharLn = t3lib_div::intInRange(max($len),1);
					$DBfields[] = $fConf["fieldname"]." ".($varCharLn>$this->charMaxLng?'var':'')."char(".$varCharLn.") DEFAULT '' NOT NULL,";
				} else {
					$DBfields[] = $fConf["fieldname"]." int(11) unsigned DEFAULT '0' NOT NULL,";
				}
			break;
			case "rel":
				if ($fConf["conf_rel_type"]=="group")	{
					$configL[]='"type" => "group",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
					$configL[]='"internal_type" => "db",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				} else {
					$configL[]='"type" => "select",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				}

				if ($fConf["conf_rel_type"]!="group" && $fConf["conf_relations"]==1 && $fConf["conf_rel_dummyitem"])	{
					$configL[]=trim($this->wrapBody('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_dummyitem]').'
						"items" => Array (
							','Array("",0),','
						),
					'));
				}

				if (t3lib_div::inList("tt_content,fe_users,fe_groups",$fConf["conf_rel_table"]))		$this->EM_CONF_presets["dependencies"][]="cms";

				if ($fConf["conf_rel_table"]=="_CUSTOM")	{
					$fConf["conf_rel_table"]=$fConf["conf_custom_table_name"]?$fConf["conf_custom_table_name"]:"NO_TABLE_NAME_AVAILABLE";
				}

				if ($fConf["conf_rel_type"]=="group")	{
					$configL[]='"allowed" => "'.($fConf["conf_rel_table"]!="_ALL"?$fConf["conf_rel_table"]:"*").'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]');
					if ($fConf["conf_rel_table"]=="_ALL")	$configL[]='"prepend_tname" => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]=_ALL');
				} else {
					switch($fConf["conf_rel_type"])	{
						case "select_cur":
							$where="AND ".$fConf["conf_rel_table"].".pid=###CURRENT_PID### ";
						break;
						case "select_root":
							$where="AND ".$fConf["conf_rel_table"].".pid=###SITEROOT### ";
						break;
						case "select_storage":
							$where="AND ".$fConf["conf_rel_table"].".pid=###STORAGE_PID### ";
						break;
						default:
							$where="";
						break;
					}
					$configL[]='"foreign_table" => "'.$fConf["conf_rel_table"].'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]');
					$configL[]='"foreign_table_where" => "'.$where.'ORDER BY '.$fConf["conf_rel_table"].'.uid",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				}
				$configL[]='"size" => '.t3lib_div::intInRange($fConf["conf_relations_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_selsize]');
				$configL[]='"minitems" => 0,';
				$configL[]='"maxitems" => '.t3lib_div::intInRange($fConf["conf_relations"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations]');

				if ($fConf["conf_relations_mm"])	{
					$mmTableName=$id."_mm";
					$configL[]='"MM" => "'.$mmTableName.'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_mm]');
					$DBfields[] = $fConf["fieldname"]." int(11) unsigned DEFAULT '0' NOT NULL,";

					$createTable = $this->sPS("
						#
						# Table structure for table '".$mmTableName."'
						# ".$this->WOPcomment('WOP:'.$WOP.'[conf_relations_mm]')."
						#
						CREATE TABLE ".$mmTableName." (
						  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
						  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
						  tablenames varchar(30) DEFAULT '' NOT NULL,
						  sorting int(11) unsigned DEFAULT '0' NOT NULL,
						  KEY uid_local (uid_local),
						  KEY uid_foreign (uid_foreign)
						);
					");
					$this->ext_tables_sql[]=chr(10).$createTable.chr(10);
				} elseif (t3lib_div::intInRange($fConf["conf_relations"],1,100)>1 || $fConf["conf_rel_type"]=="group") {
					$DBfields[] = $fConf["fieldname"]." blob NOT NULL,";
				} else {
					$DBfields[] = $fConf["fieldname"]." int(11) unsigned DEFAULT '0' NOT NULL,";
				}

				if ($fConf["conf_rel_type"]!="group")	{
					$wTable=$fConf["conf_rel_table"];
					$wizards =array();
					if ($fConf["conf_wiz_addrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_addrec]').'
							"add" => Array(
								"type" => "script",
								"title" => "Create new record",
								"icon" => "add.gif",
								"params" => Array(
									"table"=>"'.$wTable.'",
									"pid" => "###CURRENT_PID###",
									"setValue" => "prepend"
								),
								"script" => "wizard_add.php",
							),
						'));
					}
					if ($fConf["conf_wiz_listrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_listrec]').'
							"list" => Array(
								"type" => "script",
								"title" => "List",
								"icon" => "list.gif",
								"params" => Array(
									"table"=>"'.$wTable.'",
									"pid" => "###CURRENT_PID###",
								),
								"script" => "wizard_list.php",
							),
						'));
					}
					if ($fConf["conf_wiz_editrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_editrec]').'
							"edit" => Array(
								"type" => "popup",
								"title" => "Edit",
								"script" => "wizard_edit.php",
								"popup_onlyOpenIfSelected" => 1,
								"icon" => "edit2.gif",
								"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
							),
						'));
					}
					if (count($wizards))	{
						$configL[]=trim($this->wrapBody('
							"wizards" => Array(
								"_PADDING" => 2,
								"_VERTICAL" => 1,
								',implode(chr(10),$wizards),'
							),
						'));
					}
				}
			break;
			case "files":
				$configL[]='"type" => "group",';
				$configL[]='"internal_type" => "file",';
				switch($fConf["conf_files_type"])	{
					case "images":
						$configL[]='"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
					break;
					case "webimages":
						$configL[]='"allowed" => "gif,png,jpeg,jpg",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
					break;
					case "all":
						$configL[]='"allowed" => "",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
						$configL[]='"disallowed" => "php,php3",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
					break;
				}
				$configL[]='"max_size" => '.t3lib_div::intInRange($fConf["conf_max_filesize"],1,1000,500).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_max_filesize]');

				$this->EM_CONF_presets["uploadfolder"]=1;

				$ulFolder = 'uploads/tx_'.str_replace("_","",$extKey);
				$configL[]='"uploadfolder" => "'.$ulFolder.'",';
				if ($fConf["conf_files_thumbs"])	$configL[]='"show_thumbs" => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_thumbs]');

				$configL[]='"size" => '.t3lib_div::intInRange($fConf["conf_files_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_selsize]');
				$configL[]='"minitems" => 0,';
				$configL[]='"maxitems" => '.t3lib_div::intInRange($fConf["conf_files"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files]');

				$DBfields[] = $fConf["fieldname"]." blob NOT NULL,";
			break;
			case "none":
				$DBfields[] = $fConf["fieldname"]." tinytext NOT NULL,";
				$configL[]=trim($this->sPS('
					"type" => "none",
				'));
			break;
			case "passthrough":
				$DBfields[] = $fConf["fieldname"]." tinytext NOT NULL,";
				$configL[]=trim($this->sPS('
					"type" => "passthrough",
				'));
			break;
			default:
				debug("Unknown type: ".(string)$fConf["type"]);
			break;
		}

		if ($t=="passthrough")	{
			$columns[$fConf["fieldname"]] = trim($this->wrapBody('
				"'.$fConf["fieldname"].'" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[fieldname]').'
					"config" => Array (
						',implode(chr(10),$configL),'
					)
				),
			',2));
		} else {
			$columns[$fConf["fieldname"]] = trim($this->wrapBody('
				"'.$fConf["fieldname"].'" => Array (		'.$this->WOPcomment('WOP:'.$WOP.'[fieldname]').'
					"exclude" => '.($fConf["excludeField"]?1:0).',		'.$this->WOPcomment('WOP:'.$WOP.'[excludeField]').'
					"label" => "'.addslashes($this->getSplitLabels_reference($fConf,"title",$table.".".$fConf["fieldname"])).'",		'.$this->WOPcomment('WOP:'.$WOP.'[title]').'
					"config" => Array (
						',implode(chr(10),$configL),'
					)
				),
			',2));
		}
	}

	/**
	 * MAKES a Plugin
	 */
	function renderExtPart_PI($k,$config,$extKey)	{
		$WOP="[pi][".$k."]";
		$cN = $this->returnName($extKey,"class","pi".$k);
		$pathSuffix = "pi".$k."/";

#debug($config);
		$setType="";
		switch($config["addType"])	{
			case "list_type":
				$setType="list_type";

				$this->ext_tables[]=$this->sPS('
					'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
					t3lib_div::loadTCA("tt_content");
					$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi'.$k.'"]="layout,select_key";
					'.($config["apply_extended"]?'$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi'.$k.'"]="'.$this->_apply_extended_types[$config["apply_extended"]].'";':'').'
				');

				$this->ext_localconf[]=$this->sPS('
					'.$this->WOPcomment('WOP:'.$WOP.'[addType] / '.$WOP.'[tag_name]').'
					  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
					t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
						tt_content.CSS_editor.ch.'.$cN.' = < plugin.'.$cN.'.CSS_editor
					",43);
				');
			break;
			case "textbox":
				$setType="splash_layout";

				if ($config["apply_extended"])	{
					$this->ext_tables[]=$this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
						t3lib_div::loadTCA("tt_content");
						$TCA["tt_content"]["types"]["splash"]["subtype_value_field"]="splash_layout";
						$TCA["tt_content"]["types"]["splash"]["subtypes_addlist"][$_EXTKEY."_pi'.$k.'"]="'.$this->_apply_extended_types[$config["apply_extended"]].'";
					');
				}
			break;
			case "menu_sitemap":
				$setType="menu_type";

				if ($config["apply_extended"])	{
					$this->ext_tables[]=$this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
						t3lib_div::loadTCA("tt_content");
						$TCA["tt_content"]["types"]["menu"]["subtype_value_field"]="menu_type";
						$TCA["tt_content"]["types"]["menu"]["subtypes_addlist"][$_EXTKEY."_pi'.$k.'"]="'.$this->_apply_extended_types[$config["apply_extended"]].'";
					');
				}
			break;
			case "ce":
				$setType="CType";

				$tFields=array();
				$tFields[] = "CType;;4;button;1-1-1, header;;3;;2-2-2";
				if ($config["apply_extended"])	{
					$tFields[] = $this->_apply_extended_types[$config["apply_extended"]];
				}
				$this->ext_tables[]=$this->sPS('
					'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
					t3lib_div::loadTCA("tt_content");
					$TCA["tt_content"]["types"][$_EXTKEY."_pi'.$k.'"]["showitem"]="'.implode(", ",$tFields).'";
				');
			break;
			case "header":
				$setType="header_layout";
			break;
			case "includeLib":
				if ($config["plus_user_ex"])	$setType="includeLib";
			break;
			case "typotags":
				$tagName = ereg_replace("[^a-z0-9_]","",strtolower($config["tag_name"]));
				if ($tagName)	{
					$this->ext_localconf[]=$this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[addType] / '.$WOP.'[tag_name]').'
						  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
						t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
							tt_content.text.20.parseFunc.tags.'.$tagName.' = < plugin.".t3lib_extMgm::getCN($_EXTKEY)."_pi'.$k.'
						",43);
					');
				}
			break;
			default:
			break;
		}

		$cache= $config["plus_user_obj"] ? 0 : 1;

		$this->ext_localconf[]=$this->sPS('
			'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
			t3lib_extMgm::addPItoST43($_EXTKEY,"pi'.$k.'/class.'.$cN.'.php","_pi'.$k.'","'.$setType.'",'.$cache.');
		');

		if ($setType && !t3lib_div::inList("typotags,includeLib",$setType))	{
			$this->ext_tables[]=$this->sPS('
				'.$this->WOPcomment('WOP:'.$WOP.'[addType]').'
				t3lib_extMgm::addPlugin(Array("'.addslashes($this->getSplitLabels_reference($config,"title","tt_content.".$setType."_pi".$k)).'", $_EXTKEY."_pi'.$k.'"),"'.$setType.'");
			');
		}

			// Make Plugin class:
		switch($config["addType"])	{
			case "list_type":
				if ($config["list_default"])	{
					if (is_array($this->wizArray["tables"][$config["list_default"]]))	{
						$tempTableConf = $this->wizArray["tables"][$config["list_default"]];
						$tableName = $this->returnName($extKey,"tables",$tempTableConf["tablename"]);

						$ll=array();

						$theLines = Array();
						$theLines["getListRow"]=Array();
						$theLines["getListHeader"]=Array();
						$theLines["getFieldContent"]=Array();
						$theLines["getFieldHeader"]=Array();
						$theLines["singleRows"]=Array();
						$theLines["listItemRows"]=Array();
						$theLines["singleRows_section"]=Array();
						$P_classes=array();

						$theLines["searchFieldList"]=Array();
						$theLines["orderByList"]=Array();

						$tcol="uid";
						$theLines["getListRow"][$tcol] = '<td><p>\'.$this->getFieldContent("'.$tcol.'").\'</p></td>';
						$theLines["getListHeader"][$tcol] = '<td><p>\'.$this->getFieldHeader_sortLink("'.$tcol.'").\'</p></td>';
						$theLines["orderByList"][$tcol]=$tcol;

						if (is_array($tempTableConf["fields"]))	{
							reset($tempTableConf["fields"]);
							while(list(,$fC)=each($tempTableConf["fields"]))	{
								$tcol = $fC["fieldname"];
								if ($tcol)	{
									$theLines["singleRows"][$tcol] = trim($this->sPS('
										<tr>
											<td nowrap valign="top"\'.$this->pi_classParam("singleView-HCell").\'><p>\'.$this->getFieldHeader("'.$tcol.'").\'</p></td>
											<td valign="top"><p>\'.$this->getFieldContent("'.$tcol.'").\'</p></td>
										</tr>
									'));

									if ($this->fieldIsRTE($fC))	{
										$theLines["singleRows_section"][$tcol] = trim($this->sPS('
											\'.$this->getFieldContent("'.$tcol.'").\'
										'));
									} else {
										$tempN='singleViewField-'.str_replace("_","-",$tcol);
										$theLines["singleRows_section"][$tcol] = trim($this->sPS('
											<p\'.$this->pi_classParam("'.$tempN.'").\'><strong>\'.$this->getFieldHeader("'.$tcol.'").\':</strong> \'.$this->getFieldContent("'.$tcol.'").\'</p>
										'));
										$P_classes["SV"][]=$tempN;
									}

									if (!strstr($fC["type"],"textarea"))	{
										$theLines["getListRow"][$tcol] = '<td valign="top"><p>\'.$this->getFieldContent("'.$tcol.'").\'</p></td>';
										$theLines["getListHeader"][$tcol] = '<td nowrap><p>\'.$this->getFieldHeader("'.$tcol.'").\'</p></td>';

										$tempN='listrowField-'.str_replace("_","-",$tcol);
										$theLines["listItemRows"][$tcol] = trim($this->sPS('
											<p\'.$this->pi_classParam("'.$tempN.'").\'>\'.$this->getFieldContent("'.$tcol.'").\'</p>
										'));
										$P_classes["LV"][]=$tempN;
									}


									$this->addLocalConf($ll,array("listFieldHeader_".$tcol=>$fC["title"]),"listFieldHeader_".$tcol,"pi",$k,1,1);

									if ($tcol=="title")	{
										$theLines["getFieldContent"][$tcol] = trim($this->sPS('
												case "'.$tcol.'":
														// This will wrap the title in a link.
													return $this->pi_list_linkSingle($this->internal["currentRow"]["'.$tcol.'"],$this->internal["currentRow"]["uid"],1);
												break;
										'));
										$theLines["getFieldHeader"][$tcol] = trim($this->sPS('
												case "'.$tcol.'":
													return $this->pi_getLL("listFieldHeader_'.$tcol.'","<em>'.$tcol.'</em>");
												break;
										'));
									} elseif ($this->fieldIsRTE($fC)) {
											$theLines["getFieldContent"][$tcol] = trim($this->sPS('
													case "'.$tcol.'":
														return $this->pi_RTEcssText($this->internal["currentRow"]["'.$tcol.'"]);
													break;
											'));
									} elseif ($fC["type"]=="datetime")	{
										$theLines["getFieldContent"][$tcol] = trim($this->sPS('
												case "'.$tcol.'":
													return strftime("%d-%m-%y %H:%M:%S",$this->internal["currentRow"]["'.$tcol.'"]);
												break;
										'));
									} elseif ($fC["type"]=="date")	{
										$theLines["getFieldContent"][$tcol] = trim($this->sPS('
												case "'.$tcol.'":
														// For a numbers-only date, use something like: %d-%m-%y
													return strftime("%A %e. %B %Y",$this->internal["currentRow"]["'.$tcol.'"]);
												break;
										'));
									}
									if (strstr($fC["type"],"input"))	{
										$theLines["getListHeader"][$tcol] = '<td><p>\'.$this->getFieldHeader_sortLink("'.$tcol.'").\'</p></td>';
										$theLines["orderByList"][$tcol]=$tcol;
									}
									if (strstr($fC["type"],"input")||strstr($fC["type"],"textarea"))	{
										$theLines["searchFieldList"][$tcol]=$tcol;
									}
								}
							}
						}

						$theLines["singleRows"]["tstamp"] = trim($this->sPS('
							<tr>
								<td nowrap\'.$this->pi_classParam("singleView-HCell").\'><p>Last updated:</p></td>
								<td valign="top"><p>\'.date("d-m-Y H:i",$this->internal["currentRow"]["tstamp"]).\'</p></td>
							</tr>
						'));
						$theLines["singleRows"]["crdate"] = trim($this->sPS('
							<tr>
								<td nowrap\'.$this->pi_classParam("singleView-HCell").\'><p>Created:</p></td>
								<td valign="top"><p>\'.date("d-m-Y H:i",$this->internal["currentRow"]["crdate"]).\'</p></td>
							</tr>
						'));

							// Add title to local lang file
						$ll = $this->addStdLocalLangConf($ll,$k);

						$this->addLocalLangFile($ll,$pathSuffix."locallang.php",'Language labels for plugin "'.$cN.'"');


						$innerMainContent = $this->sPS('
							/**
							 * [Put your description here]
							 */
							function main($content,$conf)	{
								switch((string)$conf["CMD"])	{
									case "singleView":
										list($t) = explode(":",$this->cObj->currentRecord);
										$this->internal["currentTable"]=$t;
										$this->internal["currentRow"]=$this->cObj->data;
										return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
									break;
									default:
										if (strstr($this->cObj->currentRecord,"tt_content"))	{
											$conf["pidList"] = $this->cObj->data["pages"];
											$conf["recursive"] = $this->cObj->data["recursive"];
										}
										return $this->pi_wrapInBaseClass($this->listView($content,$conf));
									break;
								}
							}
						');

						$innerMainContent.= $this->sPS('
							/**
							 * [Put your description here]
							 */
							function listView($content,$conf)	{
								$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
								$this->pi_setPiVarDefaults();
								$this->pi_loadLL();		// Loading the LOCAL_LANG values
								'.(!$cache ? '$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it\'s a USER_INT object!' : '').'
								$lConf = $this->conf["listView."];	// Local settings for the listView function

								if ($this->piVars["showUid"])	{	// If a single element should be displayed:
									$this->internal["currentTable"] = "'.$tableName.'";
									$this->internal["currentRow"] = $this->pi_getRecord("'.$tableName.'",$this->piVars["showUid"]);

									$content = $this->singleView($content,$conf);
									return $content;
								} else {
									$items=array(
										"1"=> $this->pi_getLL("list_mode_1","Mode 1"),
										"2"=> $this->pi_getLL("list_mode_2","Mode 2"),
										"3"=> $this->pi_getLL("list_mode_3","Mode 3"),
									);
									if (!isset($this->piVars["pointer"]))	$this->piVars["pointer"]=0;
									if (!isset($this->piVars["mode"]))	$this->piVars["mode"]=1;

										// Initializing the query parameters:
									list($this->internal["orderBy"],$this->internal["descFlag"]) = explode(":",$this->piVars["sort"]);
									$this->internal["results_at_a_time"]=t3lib_div::intInRange($lConf["results_at_a_time"],0,1000,3);		// Number of results to show in a listing.
									$this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
									$this->internal["searchFieldList"]="'.implode(",",$theLines["searchFieldList"]).'";
									$this->internal["orderByList"]="'.implode(",",$theLines["orderByList"]).'";

										// Get number of records:
									$res = $this->pi_exec_query("'.$tableName.'",1);
									list($this->internal["res_count"]) = $GLOBALS[\'TYPO3_DB\']->sql_fetch_row($res);

										// Make listing query, pass query to SQL database:
									$res = $this->pi_exec_query("'.$tableName.'");
									$this->internal["currentTable"] = "'.$tableName.'";

										// Put the whole list together:
									$fullTable="";	// Clear var;
								#	$fullTable.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!

										// Adds the mode selector.
									$fullTable.=$this->pi_list_modeSelector($items);

										// Adds the whole list table
									$fullTable.='.($config["list_default_listmode"]?'$this->makelist($res);':'$this->pi_list_makelist($res);').'

										// Adds the search box:
									$fullTable.=$this->pi_list_searchBox();

										// Adds the result browser:
									$fullTable.=$this->pi_list_browseresults();

										// Returns the content from the plugin.
									return $fullTable;
								}
							}
						');


						if ($config["list_default_listmode"])	{
							$innerMainContent.= $this->wrapBody('
								/**
								 * [Put your description here]
								 */
								function makelist($res)	{
									$items=Array();
										// Make list table rows
									while($this->internal["currentRow"] = $GLOBALS[\'TYPO3_DB\']->sql_fetch_assoc($res))	{
										$items[]=$this->makeListItem();
									}

									$out = \'<div\'.$this->pi_classParam("listrow").\'>
										\'.implode(chr(10),$items).\'
										</div>\';
									return $out;
								}

								/**
								 * [Put your description here]
								 */
								function makeListItem()	{
									$out=\'
										',implode(chr(10),$theLines["listItemRows"]),'
										\';
									return $out;
								}
							',3);
						}

						// Single display:
						if ($config["list_default_singlemode"])	{
							$innerMainContent.= $this->wrapBody('
								/**
								 * [Put your description here]
								 */
								function singleView($content,$conf)	{
									$this->conf=$conf;
									$this->pi_setPiVarDefaults();
									$this->pi_loadLL();
									'.(!$cache ? '$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it\'s a USER_INT object!' : '').'

										// This sets the title of the page for use in indexed search results:
									if ($this->internal["currentRow"]["title"])	$GLOBALS["TSFE"]->indexedDocTitle=$this->internal["currentRow"]["title"];

									$content=\'<div\'.$this->pi_classParam("singleView").\'>
										<H2>Record "\'.$this->internal["currentRow"]["uid"].\'" from table "\'.$this->internal["currentTable"].\'":</H2>
										',implode(chr(10),$theLines["singleRows_section"]),'
									<p>\'.$this->pi_list_linkSingle($this->pi_getLL("back","Back"),0).\'</p></div>\'.
									$this->pi_getEditPanel();

									return $content;
								}
							',3);
						} else {
							$innerMainContent.= $this->wrapBody('
								/**
								 * [Put your description here]
								 */
								function singleView($content,$conf)	{
									$this->conf=$conf;
									$this->pi_setPiVarDefaults();
									$this->pi_loadLL();
									'.(!$cache ? '$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it\'s a USER_INT object!' : '').'

										// This sets the title of the page for use in indexed search results:
									if ($this->internal["currentRow"]["title"])	$GLOBALS["TSFE"]->indexedDocTitle=$this->internal["currentRow"]["title"];

									$content=\'<div\'.$this->pi_classParam("singleView").\'>
										<H2>Record "\'.$this->internal["currentRow"]["uid"].\'" from table "\'.$this->internal["currentTable"].\'":</H2>
										<table>
											',implode(chr(10),$theLines["singleRows"]),'
										</table>
									<p>\'.$this->pi_list_linkSingle($this->pi_getLL("back","Back"),0).\'</p></div>\'.
									$this->pi_getEditPanel();

									return $content;
								}
							',3);
						}

						$this->ext_localconf[]=$this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[...]').'
							t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
								tt_content.shortcut.20.0.conf.'.$tableName.' = < plugin.".t3lib_extMgm::getCN($_EXTKEY)."_pi'.$k.'
								tt_content.shortcut.20.0.conf.'.$tableName.'.CMD = singleView
							",43);
						');

						if (!$config["list_default_listmode"])	{
							$innerMainContent.= $this->wrapBody('
								/**
								 * [Put your description here]
								 */
								function pi_list_row($c)	{
									$editPanel = $this->pi_getEditPanel();
									if ($editPanel)	$editPanel="<TD>".$editPanel."</TD>";

									return \'<tr\'.($c%2 ? $this->pi_classParam("listrow-odd") : "").\'>
											',implode(chr(10),$theLines["getListRow"]),'
											\'.$editPanel.\'
										</tr>\';
								}
							',3);
							$innerMainContent.= $this->wrapBody('
								/**
								 * [Put your description here]
								 */
								function pi_list_header()	{
									return \'<tr\'.$this->pi_classParam("listrow-header").\'>
											',implode(chr(10),$theLines["getListHeader"]),'
										</tr>\';
								}
							',3);
						}
						$innerMainContent.= $this->wrapBody('
							/**
							 * [Put your description here]
							 */
							function getFieldContent($fN)	{
								switch($fN) {
									case "uid":
										return $this->pi_list_linkSingle($this->internal["currentRow"][$fN],$this->internal["currentRow"]["uid"],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
									break;
									',implode(chr(10),$theLines["getFieldContent"]),'
									default:
										return $this->internal["currentRow"][$fN];
									break;
								}
							}
						',2);
						$innerMainContent.= $this->wrapBody('
							/**
							 * [Put your description here]
							 */
							function getFieldHeader($fN)	{
								switch($fN) {
									',implode(chr(10),$theLines["getFieldHeader"]),'
									default:
										return $this->pi_getLL("listFieldHeader_".$fN,"[".$fN."]");
									break;
								}
							}
						',2);
						$innerMainContent.= $this->sPS('
							/**
							 * [Put your description here]
							 */
							function getFieldHeader_sortLink($fN)	{
								return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array("sort"=>$fN.":".($this->internal["descFlag"]?0:1)));
							}
						');






						$CSS_editor_code="";
						$pCSSSel = str_replace("_","-",$cN);

						if ($config["list_default_listmode"])	{
							$temp_merge=array();
							if (is_array($P_classes["LV"]))	{
								while(list($c,$LVc)=each($P_classes["LV"]))	{
									$temp_merge[]=$this->sPS('
										P_'.$c.' = ['.$LVc.']
										P_'.$c.'.selector = +.'.$pCSSSel.'-'.$LVc.'
										P_'.$c.'.attribs = BODYTEXT
										P_'.$c.'.example = <p class="'.$pCSSSel.'-'.$LVc.'">['.$LVc.'] text <a href="#">with a link</a> in it.</p><p class="'.$pCSSSel.'-'.$LVc.'">In principio creavit Deus caelum et terram terra autem erat inanis et vacua et tenebrae super faciem abyssi et spiritus...</p>
										P_'.$c.'.exampleStop = 1
										P_'.$c.'.ch.links = < CSS_editor.ch.A
									',1);
								}
							}
							$CSS_editor_code.=$this->wrapBody('
								list = List display
								list.selector = .'.$pCSSSel.'-listrow
								list.example = <div class="'.$pCSSSel.'-listrow"><p>This is regular bodytext in the list display.</p><p>Viditque Deus cuncta quae fecit et erant valde bona et factum est vespere et mane dies sextus.</p></div>
								list.exampleWrap = <div class="'.$pCSSSel.'-listrow"> | </div>
								list.ch.P < .P
								list.ch.P.exampleStop = 0
								list.ch.P.ch {
								',implode(chr(10),$temp_merge),'
								}
							');
						} else {
							$CSS_editor_code.=$this->sPS('
								list = List display
								list.selector = .'.$pCSSSel.'-listrow
								list.example = <div class="'.$pCSSSel.'-listrow"><table><tr class="'.$pCSSSel.'-listrow-header"><td nowrap><p>Time / Date:</p></td><td><p><a HREF="#">Title:</a></p></td></tr><tr><td valign="top"><p>25-08-02</p></td><td valign="top"><p><a HREF="#">New company name...</a></p></td></tr><tr class="'.$pCSSSel.'-listrow-odd"><td valign="top"><p>16-08-02</p></td><td valign="top"><p><a HREF="#">Yet another headline here</a></p></td></tr><tr><td valign="top"><p>05-08-02</p></td><td valign="top"><p><a HREF="#">The third line - even row</a></p></td></tr></table></div>
								list.exampleStop = 1
								list.ch {
									TABLE = Table
									TABLE.selector = TABLE
									TABLE.attribs = TABLE
									TD = Table cells
									TD.selector = TD
									TD.attribs = TD
									TD_header = Header row cells
									TD_header.selector = TR.'.$pCSSSel.'-listrow-header TD
									TD_header.attribs = TD
									TD_odd = Odd rows cells
									TD_odd.selector = TR.'.$pCSSSel.'-listrow-odd TD
									TD_odd.attribs = TD
								}
								list.ch.TD.ch.P < .P
								list.ch.TD_header.ch.P < .P
								list.ch.TD_odd.ch.P < .P
							');
						}

						if ($config["list_default_singlemode"])	{
							$temp_merge=array();
							if (is_array($P_classes["SV"]))	{
								while(list($c,$LVc)=each($P_classes["SV"]))	{
									$temp_merge[]=$this->sPS('
										P_'.$c.' = ['.$LVc.']
										P_'.$c.'.selector = +.'.$pCSSSel.'-'.$LVc.'
										P_'.$c.'.attribs = BODYTEXT
										P_'.$c.'.example = <p class="'.$pCSSSel.'-'.$LVc.'">['.$LVc.'] text <a href="#">with a link</a> in it.</p><p class="'.$pCSSSel.'-'.$LVc.'">In principio creavit Deus caelum et terram terra autem erat inanis et vacua et tenebrae super faciem abyssi et spiritus...</p>
										P_'.$c.'.exampleStop = 1
										P_'.$c.'.ch.links = < CSS_editor.ch.A
									',1);
								}
							}
							$CSS_editor_code.=$this->wrapBody('
								single = Single display
								single.selector = .'.$pCSSSel.'-singleView
								single.example = <div class="'.$pCSSSel.'-singleView"><H2>Header, if any:</H2><p>This is regular bodytext in the list display.</p><p>Viditque Deus cuncta quae fecit et erant valde bona et factum est vespere et mane dies sextus.</p><p><a href="#">Back</a></p></div>
								single.exampleWrap = <div class="'.$pCSSSel.'-singleView"> | </div>
								single.ch.P < .P
								single.ch.P.exampleStop = 0
								single.ch.P.ch {
								',implode(chr(10),$temp_merge),'
								}
							');
						} else {
							$CSS_editor_code.=$this->sPS('
								single = Single display
								single.selector = .'.$pCSSSel.'-singleView
								single.example = <div class="'.$pCSSSel.'-singleView"><H2>Header, if any:</H2><table><tr><td nowrap valign="top" class="'.$pCSSSel.'-singleView-HCell"><p>Date:</p></td><td valign="top"><p>13-09-02</p></td></tr><tr><td nowrap valign="top" class="'.$pCSSSel.'-singleView-HCell"><p>Title:</p></td><td valign="top"><p><a HREF="#">New title line</a></p></td></tr><tr><td nowrap valign="top" class="'.$pCSSSel.'-singleView-HCell"><p>Teaser text:</p></td><td valign="top"><p>Vocavitque Deus firmamentum caelum et factum est vespere et mane dies secundus dixit vero Deus congregentur.</p><p>Aquae quae sub caelo sunt in locum unum et appareat arida factumque est ita et vocavit Deus aridam terram congregationesque aquarum appellavit maria et vidit Deus quod esset bonum et ait germinet terra herbam virentem et facientem semen et lignum pomiferum faciens fructum iuxta genus suum cuius semen in semet ipso sit super terram et factum est ita et protulit terra herbam virentem et adferentem semen iuxta genus suum lignumque faciens fructum et habens unumquodque sementem secundum speciem suam et vidit Deus quod esset bonum.</p></td></tr><tr><td nowrap class="'.$pCSSSel.'-singleView-HCell"><p>Last updated:</p></td><td valign="top"><p>25-08-2002 18:28</p></td></tr><tr><td nowrap class="'.$pCSSSel.'-singleView-HCell"><p>Created:</p></td><td valign="top"><p>25-08-2002 18:27</p></td></tr></table><p><a href="#">Back</a></p></div>
								single.exampleStop = 1
								single.ch {
									TABLE = Table
									TABLE.selector = TABLE
									TABLE.attribs = TABLE
									TD = Table cells
									TD.selector = TD
									TD.attribs = TD
									TD.ch {
		  								TD = Header cells
			  							TD.selector = +.'.$pCSSSel.'-singleView-HCell
										TD.attribs = TD
									}
								}
								single.ch.P < .P
								single.ch.H2 < .H2
								single.ch.TD.ch.P < .P
								single.ch.TD.ch.TD.ch.P < .P
							');
						}

						$this->addFileToFileArray($config["plus_not_staticTemplate"]?"ext_typoscript_editorcfg.txt":$pathSuffix."static/editorcfg.txt",$this->wrapBody('
							plugin.'.$cN.'.CSS_editor = Plugin: "'.$cN.'"
							plugin.'.$cN.'.CSS_editor.selector = .'.$pCSSSel.'
							plugin.'.$cN.'.CSS_editor.exampleWrap = <HR><strong>Plugin: "'.$cN.'"</strong><HR><div class="'.$pCSSSel.'"> | </div>
							plugin.'.$cN.'.CSS_editor.ch {
								P = Text
								P.selector = P
								P.attribs = BODYTEXT
								P.example = <p>General text wrapped in &lt;P&gt;:<BR>This is text <a href="#">with a link</a> in it. In principio creavit Deus caelum et terram terra autem erat inanis et vacua et tenebrae super faciem abyssi et spiritus...</p>
								P.exampleStop = 1
								P.ch.links = < CSS_editor.ch.A

								H2 = Header 2
								H2.selector = H2
								H2.attribs = HEADER
								H2.example = <H2>Header 2 example <a href="#"> with link</a></H2><p>Bodytext, Et praeessent diei ac nocti et dividerent lucem ac tenebras et vidit Deus quod esset bonum et factum est...</p>
								H2.ch.links = < CSS_editor.ch.A
								H2.exampleStop = 1

								H3 = Header 3
								H3.selector = H3
								H3.attribs = HEADER
								H3.example = <h3>Header 3 example <a href="#"> with link</a></h3><p>Bodytext, Et praeessent diei ac nocti et dividerent lucem ac tenebras et vidit Deus quod esset bonum et factum est...</p>
								H3.ch.links = < CSS_editor.ch.A
								H3.exampleStop = 1


									## LISTING:
								modeSelector = Mode selector
								modeSelector.selector = .'.$pCSSSel.'-modeSelector
								modeSelector.example = <div class="'.$pCSSSel.'-modeSelector"><table><tr><td class="'.$pCSSSel.'-modeSelector-SCell"><p><a HREF="#">Mode 1 (S)</a></p></td><td><p><a HREF="#">Mode 2</a></p></td><td><p><a HREF="#">Mode 3</a></p></td></tr></table></div>
								modeSelector.exampleStop = 1
								modeSelector.ch.P < .P
								modeSelector.ch.TABLE = Table
								modeSelector.ch.TABLE.selector = TABLE
								modeSelector.ch.TABLE.attribs = TABLE
								modeSelector.ch.TD = Table cells
								modeSelector.ch.TD.selector = TD
								modeSelector.ch.TD.attribs = TD
								modeSelector.ch.TD.ch {
								  TD = Selected table cells
								  TD.selector = + .'.$pCSSSel.'-modeSelector-SCell
								  TD.attribs = TD
								}
								modeSelector.ch.TD.ch.TD.ch.P < .P


								browsebox = Browsing box
								browsebox.selector = .'.$pCSSSel.'-browsebox
								browsebox.example = <div class="'.$pCSSSel.'-browsebox"><p>Displaying results <span class="'.$pCSSSel.'-browsebox-strong">1 to 3</span> out of <span class="'.$pCSSSel.'-browsebox-strong">4</span></p><table><tr><td class="'.$pCSSSel.'-browsebox-SCell"><p><a HREF="#">Page 1 (S)</a></p></td><td><p><a HREF="#">Page 2</a></p></td><td><p><a HREF="#">Next ></a></p></td></tr></table></div>
								browsebox.exampleStop = 1
								browsebox.ch.P < .P
								browsebox.ch.P.ch.strong = Emphasized numbers
								browsebox.ch.P.ch.strong {
								  selector = SPAN.'.$pCSSSel.'-browsebox-strong
								  attribs = TEXT
								}
								browsebox.ch.TABLE = Table
								browsebox.ch.TABLE.selector = TABLE
								browsebox.ch.TABLE.attribs = TABLE
								browsebox.ch.TD = Table cells
								browsebox.ch.TD.selector = TD
								browsebox.ch.TD.attribs = TD
								browsebox.ch.TD.ch {
								  TD = Selected table cells
								  TD.selector = + .'.$pCSSSel.'-browsebox-SCell
								  TD.attribs = TD
								}
								browsebox.ch.TD.ch.P < .P
								browsebox.ch.TD.ch.TD.ch.P < .P


								searchbox = Search box
								searchbox.selector = .'.$pCSSSel.'-searchbox
								searchbox.example = <div class="'.$pCSSSel.'-searchbox"><table><form action="#" method="POST"><tr><td><input type="text" name="'.$cN.'[sword]" value="Search word" class="'.$pCSSSel.'-searchbox-sword"></td><td><input type="submit" value="Search" class="'.$pCSSSel.'-searchbox-button"></td></tr></form></table></div>
								searchbox.exampleStop = 1
								searchbox.ch {
									TABLE = Table
									TABLE.selector = TABLE
									TABLE.attribs = TABLE
									TD = Table cells
									TD.selector = TD
									TD.attribs = TD
									INPUT = Form fields
									INPUT.selector = INPUT
									INPUT.attribs = TEXT,background-color,width
									INPUT.ch {
										sword = Search word field
										sword.selector = +.'.$pCSSSel.'-searchbox-sword
										sword.attribs = TEXT,background-color,width

										button = Submit button
										button.selector = +.'.$pCSSSel.'-searchbox-button
										button.attribs = TEXT,background-color,width
									}
								}
								',$CSS_editor_code,'
							}
						'),1);

						$this->addFileToFileArray($config["plus_not_staticTemplate"]?"ext_typoscript_setup.txt":$pathSuffix."static/setup.txt",$this->sPS('
							plugin.'.$cN.' {
								CMD =
								pidList =
								recursive =
							}
							plugin.'.$cN.'.listView {
								results_at_a_time =
								maxPages =
							}
							  # Example of default set CSS styles (these go into the document header):
							plugin.'.$cN.'._CSS_DEFAULT_STYLE (
							  .'.$pCSSSel.' H2 { margin-top: 0px; margin-bottom: 0px; }
							)
							  # Example of how to overrule LOCAL_LANG values for the plugin:
							plugin.'.$cN.'._LOCAL_LANG.default {
							  pi_list_searchBox_search = Search!
							}
							  # Example of how to set default values from TS in the incoming array, $this->piVars of the plugin:
							plugin.'.$cN.'._DEFAULT_PI_VARS.test = test
						'),1);

						$this->EM_CONF_presets["clearCacheOnLoad"]=1;

						if (!$config["plus_not_staticTemplate"])	{
							$this->ext_tables[]=$this->sPS('
								t3lib_extMgm::addStaticFile($_EXTKEY,"'.$pathSuffix.'static/","'.addslashes(trim($config['title'])).'");
							');
						}
					}
				} else {
						// Add title to local lang file
					$ll=$this->addStdLocalLangConf($ll,$k,1);
					$this->addLocalConf($ll,array("submit_button_label"=>"Click here to submit value"),"submit_button_label","pi",$k,1,1);

					$this->addLocalLangFile($ll,$pathSuffix."locallang.php",'Language labels for plugin "'.$cN.'"');


					$innerMainContent = $this->sPS('
						/**
						 * [Put your description here]
						 */
						function main($content,$conf)	{
							$this->conf=$conf;
							$this->pi_setPiVarDefaults();
							$this->pi_loadLL();
							'.(!$cache ? '$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it\'s a USER_INT object!' : '').'

							$content=\'
								<strong>This is a few paragraphs:</strong><BR>
								<p>This is line 1</p>
								<p>This is line 2</p>

								<h3>This is a form:</h3>
								<form action="\'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).\'" method="POST">
									<input type="hidden" name="no_cache" value="1">
									<input type="text" name="\'.$this->prefixId.\'[input_field]" value="\'.htmlspecialchars($this->piVars["input_field"]).\'">
									<input type="submit" name="\'.$this->prefixId.\'[submit_button]" value="\'.htmlspecialchars($this->pi_getLL("submit_button_label")).\'">
								</form>
								<BR>
								<p>You can click here to \'.$this->pi_linkToPage("get to this page again",$GLOBALS["TSFE"]->id).\'</p>
							\';

							return $this->pi_wrapInBaseClass($content);
						}
					');


					$CSS_editor_code="";
					$pCSSSel = str_replace("_","-",$cN);

					$this->addFileToFileArray($config["plus_not_staticTemplate"]?"ext_typoscript_editorcfg.txt":$pathSuffix."static/editorcfg.txt",$this->sPS('
						plugin.'.$cN.'.CSS_editor = Plugin: "'.$cN.'"
						plugin.'.$cN.'.CSS_editor.selector = .'.$pCSSSel.'
						plugin.'.$cN.'.CSS_editor.exampleWrap = <HR><strong>Plugin: "'.$cN.'"</strong><HR><div class="'.$pCSSSel.'"> | </div>
						plugin.'.$cN.'.CSS_editor.ch {
							P = Text
							P.selector = P
							P.attribs = BODYTEXT
							P.example = <p>General text wrapped in &lt;P&gt;:<BR>This is text <a href="#">with a link</a> in it. In principio creavit Deus caelum et terram terra autem erat inanis et vacua et tenebrae super faciem abyssi et spiritus...</p>
							P.exampleStop = 1
							P.ch.links = < CSS_editor.ch.A

							H3 = Header 3
							H3.selector = H3
							H3.attribs = HEADER
							H3.example = <h3>Header 3 example <a href="#"> with link</a></h3><p>Bodytext, Et praeessent diei ac nocti et dividerent lucem ac tenebras et vidit Deus quod esset bonum et factum est...</p>
							H3.ch.links = < CSS_editor.ch.A
							H3.exampleStop = 1
						}
					'),1);

					if (!$config["plus_not_staticTemplate"])	{
						$this->ext_tables[]=$this->sPS('
							t3lib_extMgm::addStaticFile($_EXTKEY,"'.$pathSuffix.'static/","'.addslashes(trim($config['title'])).'");
						');
					}
				}
			break;
			case "textbox":
				$this->ext_localconf[]=$this->sPS('
					  ## Setting TypoScript for the image in the textbox:
					t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
						plugin.'.$cN.'_pi'.$k.'.IMAGEcObject {
						  file.width=100
						}
					",43);
				');

				$innerMainContent = $this->sPS('
					/**
					 * [Put your description here]
					 */
					function main($content,$conf)	{

							// Processes the image-field content:
							// $conf["IMAGEcObject."] is passed to the getImage() function as TypoScript
							// configuration for the image (except filename which is set automatically here)
						$imageFiles = explode(",",$this->cObj->data["image"]);	// This returns an array with image-filenames, if many
						$imageRows=array();	// Accumulates the images
						reset($imageFiles);
						while(list(,$iFile)=each($imageFiles))	{
							$imageRows[] = "<tr>
								<td>".$this->getImage($iFile,$conf["IMAGEcObject."])."</td>
							</tr>";
						}
						$imageBlock = count($imageRows)?\'<table border=0 cellpadding=5 cellspacing=0>\'.implode("",$imageRows).\'</table>\':\'<img src=clear.gif width=100 height=1>\';

							// Sets bodytext
						$bodyText = nl2br($this->cObj->data["bodytext"]);

							// And compiles everything into a table:
						$finalContent = \'<table border=1>
							<tr>
								<td valign=top>\'.$imageBlock.\'</td>
								<td valign=top>\'.$bodyText.\'</td>
							</tr>
						</table>\';

							// And returns content
						return $finalContent;
					}
						/**
						 * This calls a function in the TypoScript API which will return an image tag with the image
						 * processed according to the parsed TypoScript content in the $TSconf array.
						 */
					function getImage($filename,$TSconf)	{
						list($theImage)=explode(",",$filename);
						$TSconf["file"] = "uploads/pics/".$theImage;
						$img = $this->cObj->IMAGE($TSconf);
						return $img;
					}
				');
			break;
			case "header":
				$innerMainContent = $this->sPS('
					/**
					 * [Put your description here]
					 */
					function main($content,$conf)	{
						return "<H1>".$this->cObj->data["header"]."</H1>";
					}
				');
			break;
			case "menu_sitemap":
				$innerMainContent = $this->sPS('

					/**
					 * [Put your description here]
					 */
					function main($content,$conf)	{
							// Get the PID from which to make the menu.
							// If a page is set as reference in the \'Startingpoint\' field, use that
							// Otherwise use the page\'s id-number from TSFE
						$menuPid = intval($this->cObj->data["pages"]?$this->cObj->data["pages"]:$GLOBALS["TSFE"]->id);

							// Now, get an array with all the subpages to this pid:
							// (Function getMenu() is found in class.t3lib_page.php)
						$menuItems_level1 = $GLOBALS["TSFE"]->sys_page->getMenu($menuPid);

							// Prepare vars:
						$tRows=array();

							// Traverse menuitems:
						reset($menuItems_level1);
						while(list($uid,$pages_row)=each($menuItems_level1))	{
							$tRows[]=\'<tr bgColor="#cccccc"><td>\'.$this->pi_linkToPage(
								$pages_row["nav_title"]?$pages_row["nav_title"]:$pages_row["title"],
								$pages_row["uid"],
								$pages_row["target"]
							).\'</td></tr>\';
						}

						$totalMenu = \'<table border=0 cellpadding=0 cellspacing=2>
							<tr><td>This is a menu. Go to your favourite page:</td></tr>
							\'.implode(\'\',$tRows).
							\'</table><BR>(\'.$this->tellWhatToDo(\'Click here if you want to know where to change the menu design\').\')\';

						return $totalMenu;
					}

					function tellWhatToDo($str)	{
						return \'<a href="#" onClick="alert(\\\'Open the PHP-file \'.t3lib_extMgm::siteRelPath("'.$extKey.'").\''.$pathSuffix.'class.'.$cN.'.php and edit the function main()\nto change how the menu is rendered! It is pure PHP coding!\\\')">\'.$str.\'</a>\';
					}
				');
			break;
			case "typotags":
				$innerMainContent = $this->sPS('
					/**
					 * [Put your description here]
					 */
					function main($content,$conf)	{
						$tag_content = $this->cObj->getCurrentVal();
						return "<b>".$this->tellWhatToDo(strtoupper($tag_content))."</b>";
					}
					function tellWhatToDo($str)	{
						return \'<a href="#" onClick="alert(\\\'Open the PHP-file \'.t3lib_extMgm::siteRelPath("'.$extKey.'").\''.$pathSuffix.'class.'.$cN.'.php and edit the function main()\nto change how the tag content is processed!\\\')">\'.$str.\'</a>\';
					}
				');
			break;
			default:
				$innerMainContent = $this->sPS('
					/**
					 * [Put your description here]
					 */
					function main($content,$conf)	{
						return "Hello World!<HR>
							Here is the TypoScript passed to the method:".
									t3lib_div::view_array($conf);
					}
				');
			break;
		}
		$indexContent= $this->wrapBody('
			require_once(PATH_tslib."class.tslib_pibase.php");

			class '.$cN.' extends tslib_pibase {
				var $prefixId = "'.$cN.'";		// Same as class name
				var $scriptRelPath = "'.($pathSuffix."class.".$cN.".php").'";	// Path to this script relative to the extension dir.
				var $extKey = "'.$extKey.'";	// The extension key.

				',$innerMainContent,'
			}
		');
		$this->addFileToFileArray($pathSuffix."class.".$cN.".php",$this->PHPclassFile($extKey,$pathSuffix."class.".$cN.".php",$indexContent,"Plugin '".$config["title"]."' for the '".$extKey."' extension."));

			// Add wizard?
		if ($config["plus_wiz"] && $config["addType"]=="list_type")	{
			$this->addLocalConf($this->ext_locallang,$config,"title","pi",$k);
			$this->addLocalConf($this->ext_locallang,$config,"plus_wiz_description","pi",$k);

			$indexContent= $this->sPS('
				class '.$cN.'_wizicon {
					function proc($wizardItems)	{
						global $LANG;

						$LL = $this->includeLocalLang();

						$wizardItems["plugins_'.$cN.'"] = array(
							"icon"=>t3lib_extMgm::extRelPath("'.$extKey.'")."'.$pathSuffix.'ce_wiz.gif",
							"title"=>$LANG->getLLL("pi'.$k.'_title",$LL),
							"description"=>$LANG->getLLL("pi'.$k.'_plus_wiz_description",$LL),
							"params"=>"&defVals[tt_content][CType]=list&defVals[tt_content][list_type]='.$extKey.'_pi'.$k.'"
						);

						return $wizardItems;
					}
					function includeLocalLang()	{
						include(t3lib_extMgm::extPath("'.$extKey.'")."locallang.php");
						return $LOCAL_LANG;
					}
				}
			');
			$this->addFileToFileArray($pathSuffix."class.".$cN."_wizicon.php",$this->PHPclassFile($extKey,$pathSuffix."class.".$cN."_wizicon.php",$indexContent,"Class that adds the wizard icon."));

				// Add wizard icon
			$this->addFileToFileArray($pathSuffix."ce_wiz.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/wiz.gif"));

				// Add clear.gif
			$this->addFileToFileArray($pathSuffix."clear.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/clear.gif"));

			$this->ext_tables[]=$this->sPS('
				'.$this->WOPcomment('WOP:'.$WOP.'[plus_wiz]:').'
				if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["'.$cN.'_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi'.$k.'/class.'.$cN.'_wizicon.php";
			');
		}
	}

	/**
	 * MAKES a Service
	 */
	function renderExtPart_SV($k,$config,$extKey)	{
		$WOP='[sv]['.$k.']';
		$cN = $this->returnName($extKey,'class','sv'.$k);
		$pathSuffix = 'sv'.$k.'/';

		$this->ext_tables[]=$this->sPS('
			'.$this->WOPcomment('WOP:'.$WOP.'[type]').'
			t3lib_extMgm::addService($_EXTKEY,  \''.$config['type'].'\' /* sv type */,  \''.$cN.'\' /* sv key */,
					array(

						\'title\' => \''.addslashes($config['title']).'\','.$this->WOPcomment('	WOP:'.$WOP.'[title]').'
						\'description\' => \''.addslashes($config['description']).'\','.$this->WOPcomment('	WOP:'.$WOP.'[description]').'

						\'subtype\' => \''.$config['subtype'].'\','.$this->WOPcomment('	WOP:'.$WOP.'[subtype]').'

						\'available\' => TRUE,
						\'priority\' => '.$config['priority'].','.$this->WOPcomment('	WOP:'.$WOP.'[priority]').'
						\'quality\' => '.$config['quality'].','.$this->WOPcomment('	WOP:'.$WOP.'[quality]').'

						\'os\' => \''.$config['os'].'\','.$this->WOPcomment('	WOP:'.$WOP.'[os]').'
						\'exec\' => \''.$config['exec'].'\','.$this->WOPcomment('	WOP:'.$WOP.'[exec]').'

						\'classFile\' => t3lib_extMgm::extPath($_EXTKEY).\'sv'.$k.'/class.'.$cN.'.php\',
						\'className\' => \''.$cN.'\',
					)
				);
		');

		$innerMainContent = $this->sPS('

			/**
			 * [Put your description here]
			 */
			function init()	{
				$available = parent::init();

				// Here you can initialize your class.

				// The class have to do a strict check if the service is available.
				// The needed external programs are already checked in the parent class.

				// If there\'s no reason for initialization you can remove this function.

				return $available;
			}

			/**
			 * [Put your description here]
			 * performs the service processing
			 *
			 * @param	string 	Content which should be processed.
			 * @param	string 	Content type
			 * @param	array 	Configuration array
			 * @return	boolean
			 */
			function process($content=\'\', $type=\'\', $conf=array())	{

				// Depending on the service type there\'s not a process() function.
				// You have to implement the API of that service type.

				return FALSE;
			}
		');

		$indexContent= $this->wrapBody('
			require_once(PATH_t3lib.\'class.t3lib_svbase.php\');

			class '.$cN.' extends t3lib_svbase {
				var $prefixId = \''.$cN.'\';		// Same as class name
				var $scriptRelPath = \''.($pathSuffix.'class.'.$cN.'.php').'\';	// Path to this script relative to the extension dir.
				var $extKey = \''.$extKey.'\';	// The extension key.

				',$innerMainContent,'
			}
		');
		$this->addFileToFileArray($pathSuffix."class.".$cN.".php",$this->PHPclassFile($extKey,$pathSuffix."class.".$cN.".php",$indexContent,"Service '".$config['title']."' for the '".$extKey."' extension."));
	}

	/**
	 * MAKES a Backend Module
	 */
	function renderExtPart_module($k,$config,$extKey)	{
		$WOP="[module][".$k."]";
		$mN = ($config["position"]!="_MAIN"?$config["position"]."_":"").$this->returnName($extKey,"module","M".$k);
		$cN = $this->returnName($extKey,"class","module".$k);
		$pathSuffix = "mod".$k."/";

			// Insert module:
		switch($config["subpos"])	{
			case "top":
				$subPos="top";
			break;
			case "web_after_page":
				$subPos="after:layout";
			break;
			case "web_before_info":
				$subPos="before:info";
			break;
		}
		$this->ext_tables[]=$this->sPS('
			'.$this->WOPcomment('WOP:'.$WOP).'
			if (TYPO3_MODE=="BE")	{
					'.$this->WOPcomment('1. and 2. parameter is WOP:'.$WOP.'[position] , 3. parameter is WOP:'.$WOP.'[subpos]').'
				t3lib_extMgm::addModule("'.
					($config["position"]!="_MAIN"?$config["position"]:$this->returnName($extKey,"module","M".$k)).
					'","'.
					($config["position"]!="_MAIN"?$this->returnName($extKey,"module","M".$k):"").
					'","'.
					$subPos.
					'",t3lib_extMgm::extPath($_EXTKEY)."'.$pathSuffix.'");
			}
		');

			// Make conf.php file:
		$content = $this->sPS('
				// DO NOT REMOVE OR CHANGE THESE 3 LINES:
			define("TYPO3_MOD_PATH", "ext/'.$extKey.'/'.$pathSuffix.'");
			$BACK_PATH="../../../";
			$MCONF["name"]="'.$mN.'";

				'.$this->WOPcomment('WOP:'.$WOP.'[admin_only]: If the flag was set the value is "admin", otherwise "user,group"').'
			$MCONF["access"]="'.($config["admin_only"]?"admin":"user,group").'";
			$MCONF["script"]="index.php";

			$MLANG["default"]["tabs_images"]["tab"] = "moduleicon.gif";
			$MLANG["default"]["ll_ref"]="LLL:EXT:'.$extKey.'/'.$pathSuffix.'locallang_mod.php";
		');
		$this->EM_CONF_presets["module"][]=ereg_replace("\/$","",$pathSuffix);


		$ll=array();
		$this->addLocalConf($ll,$config,"title","module",$k,1,0,"mlang_tabs_tab");
		$this->addLocalConf($ll,$config,"description","module",$k,1,0,"mlang_labels_tabdescr");
		$this->addLocalConf($ll,$config,"tablabel","module",$k,1,0,"mlang_labels_tablabel");
		$this->addLocalLangFile($ll,$pathSuffix."locallang_mod.php",'Language labels for module "'.$mN.'" - header, description');

//			$MLANG["default"]["tabs"]["tab"] = "'.addslashes($config["title"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[title]').'
//			$MLANG["default"]["labels"]["tabdescr"] = "'.addslashes($config["description"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[description]').'
//			$MLANG["default"]["labels"]["tablabel"] = "'.addslashes($config["tablabel"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[tablabel]').'

/*
		if (count($this->selectedLanguages))	{
			reset($this->selectedLanguages);
			while(list($lk,$lv)=each($this->selectedLanguages))	{
				if ($lv)	{
					$content.= $this->sPS('
							// '.$this->languages[$lk].' language:
						$MLANG["'.$lk.'"]["tabs"]["tab"] = "'.addslashes($config["title_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[title_'.$lk.']').'
						$MLANG["'.$lk.'"]["labels"]["tabdescr"] = "'.addslashes($config["description_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[description_'.$lk.']').'
						$MLANG["'.$lk.'"]["labels"]["tablabel"] = "'.addslashes($config["tablabel_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[tablabel_'.$lk.']').'
					');
				}
			}
		}
*/
		$content=$this->wrapBody('
			<?php
			',$content,'
			?>
		',0);

		$this->addFileToFileArray($pathSuffix."conf.php",trim($content));

			// Add title to local lang file
		$ll=array();
		$this->addLocalConf($ll,$config,"title","module",$k,1);
		$this->addLocalConf($ll,array("function1"=>"Function #1"),"function1","module",$k,1,1);
		$this->addLocalConf($ll,array("function2"=>"Function #2"),"function2","module",$k,1,1);
		$this->addLocalConf($ll,array("function3"=>"Function #3"),"function3","module",$k,1,1);
		$this->addLocalLangFile($ll,$pathSuffix."locallang.php",'Language labels for module "'.$mN.'"');

			// Add clear.gif
		$this->addFileToFileArray($pathSuffix."clear.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/clear.gif"));

			// Add clear.gif
		$this->addFileToFileArray($pathSuffix."moduleicon.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/notfound_module.gif"));


			// Make module index.php file:
		$indexContent = $this->sPS('
				// DEFAULT initialization of a module [BEGIN]
			unset($MCONF);
			require ("conf.php");
			require ($BACK_PATH."init.php");
			require ($BACK_PATH."template.php");
			$LANG->includeLLFile("EXT:'.$extKey.'/'.$pathSuffix.'locallang.php");
			#include ("locallang.php");
			require_once (PATH_t3lib."class.t3lib_scbase.php");
			$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
				// DEFAULT initialization of a module [END]
		');

		$indexContent.= $this->sPS('
			class '.$cN.' extends t3lib_SCbase {
				var $pageinfo;

				/**
				 *
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					/*
					if (t3lib_div::_GP("clear_all_cache"))	{
						$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						"function" => Array (
							"1" => $LANG->getLL("function1"),
							"2" => $LANG->getLL("function2"),
							"3" => $LANG->getLL("function3"),
						)
					);
					parent::menuConfig();
				}

					// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				/**
				 * Main function of the module. Write the content to $this->content
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance("mediumDoc");
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form=\'<form action="" method="POST">\';

							// JavaScript
						$this->doc->JScode = \'
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						\';
						$this->doc->postCode=\'
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = \'.intval($this->id).\';
							</script>
						\';

						$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

						$this->content.=$this->doc->startPage($LANG->getLL("title"));
						$this->content.=$this->doc->header($LANG->getLL("title"));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance("mediumDoc");
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL("title"));
						$this->content.=$this->doc->header($LANG->getLL("title"));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 */
				function moduleContent()	{
					switch((string)$this->MOD_SETTINGS["function"])	{
						case 1:
							$content="<div align=center><strong>Hello World!</strong></div><BR>
								The \'Kickstarter\' has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script \'".substr(t3lib_extMgm::extPath("'.$extKey.'"),strlen(PATH_site))."'.$pathSuffix.'index.php\' and edit it!
								<HR>
								<BR>This is the GET/POST vars sent to the script:<BR>".
								"GET:".t3lib_div::view_array($_GET)."<BR>".
								"POST:".t3lib_div::view_array($_POST)."<BR>".
								"";
							$this->content.=$this->doc->section("Message #1:",$content,0,1);
						break;
						case 2:
							$content="<div align=center><strong>Menu item #2...</strong></div>";
							$this->content.=$this->doc->section("Message #2:",$content,0,1);
						break;
						case 3:
							$content="<div align=center><strong>Menu item #3...</strong></div>";
							$this->content.=$this->doc->section("Message #3:",$content,0,1);
						break;
					}
				}
			}
		');

		$SOBE_extras["firstLevel"]=0;
		$SOBE_extras["include"]=1;
		$this->addFileToFileArray($pathSuffix."index.php",$this->PHPclassFile($extKey,$pathSuffix."index.php",$indexContent,"Module '".$config["title"]."' for the '".$extKey."' extension.",$cN,$SOBE_extras));

	}

	/**
	 * MAKES a Backend Module Extension (item in function menu)
	 */
	function renderExtPart_moduleFunction($k,$config,$extKey)	{
		$WOP="[moduleFunction][".$k."]";
		$cN = $this->returnName($extKey,"class","modfunc".$k);
		$pathSuffix = "modfunc".$k."/";

		$position =$config["position"];
		$subPos="";
		switch($config["position"])	{
			case "user_task";
				$this->EM_CONF_presets["dependencies"][]="taskcenter";
			break;
			case "web_ts";
				$this->EM_CONF_presets["dependencies"][]="tstemplate";
			break;
			case "web_func_wizards";
				$this->EM_CONF_presets["dependencies"][]="func_wizards";
				$position="web_func";
				$subPos="wiz";
			break;
		}

		$this->ext_tables[]=$this->sPS('
			if (TYPO3_MODE=="BE")	{
				t3lib_extMgm::insertModuleFunction(
					"'.$position.'",		'.$this->WOPcomment('WOP:'.$WOP.'[position]').'
					"'.$cN.'",
					t3lib_extMgm::extPath($_EXTKEY)."'.$pathSuffix.'class.'.$cN.'.php",
					"'.addslashes($this->getSplitLabels_reference($config,"title","moduleFunction.".$cN)).'"'.($subPos?',
					"'.$subPos.'"	'.$this->WOPcomment('WOP:'.$WOP.'[position]'):'').'
				);
			}
		');


			// Add title to local lang file
		$ll=array();
		$this->addLocalConf($ll,$config,"title","module",$k,1);
		$this->addLocalConf($ll,array("checklabel"=>"Check box #1"),"checklabel","modfunc",$k,1,1);
		$this->addLocalLangFile($ll,$pathSuffix."locallang.php",'Language labels for module "'.$mN.'"');

		if ($position!="user_task")	{
			$indexContent.= $this->sPS('
				require_once(PATH_t3lib."class.t3lib_extobjbase.php");

				class '.$cN.' extends t3lib_extobjbase {
					function modMenu()	{
						global $LANG;

						return Array (
							"'.$cN.'_check" => "",
						);
					}

					function main()	{
							// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
						global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

						$theOutput.=$this->pObj->doc->spacer(5);
						$theOutput.=$this->pObj->doc->section($LANG->getLL("title"),"Dummy content here...",0,1);

						$menu=array();
						$menu[]=t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET['.$cN.'_check]",$this->pObj->MOD_SETTINGS["'.$cN.'_check"]).$LANG->getLL("checklabel");
						$theOutput.=$this->pObj->doc->spacer(5);
						$theOutput.=$this->pObj->doc->section("Menu",implode(" - ",$menu),0,1);

						return $theOutput;
					}
				}
			');
		} else {
			$indexContent.= $this->sPS('
				class '.$cN.' extends mod_user_task {
					/**
					 * Makes the content for the overview frame...
					 */
					function overview_main(&$pObj)	{
						$icon = \'<img src="\'.$this->backPath.t3lib_extMgm::extRelPath("'.$extKey.'").\'ext_icon.gif" width=18 height=16 class="absmiddle">\';
						$content.=$pObj->doc->section($icon."&nbsp;".$this->headLink("'.$cN.'",0),$this->overviewContent(),1,1);
						return $content;
					}
					function main() {
						global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

						return $this->mainContent();
					}
					function overviewContent()	{
						return "Content in overview frame...";
					}
					function mainContent()	{
						return "Content in main frame...";
					}
				}
			');
		}

		$this->addFileToFileArray($pathSuffix."class.".$cN.".php",$this->PHPclassFile($extKey,$pathSuffix."class.".$cN.".php",$indexContent,"Module extension (addition to function menu) '".$config["title"]."' for the '".$extKey."' extension."));
	}

	/**
	 * MAKES a Click Menu
	 */
	function renderExtPart_cm($k,$config,$extKey)	{
		$WOP="[cm][".$k."]";
		$cN = $this->returnName($extKey,"class","cm".$k);
		$filename = 'class.'.$cN.'.php';
		$pathSuffix = "cm".$k."/";

			// This will make sure our item is inserted in the clickmenu!
		$this->ext_tables[]=$this->sPS('
			'.$this->WOPcomment('WOP:'.$WOP.':').'
			if (TYPO3_MODE=="BE")	{
				$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
					"name" => "'.$cN.'",
					"path" => t3lib_extMgm::extPath($_EXTKEY)."'.$filename.'"
				);
			}
		');
			// Add title to the locallang file.
		$this->addLocalConf($this->ext_locallang,$config,"title","cm",$k);

			// Add icon
		$this->addFileToFileArray($pathSuffix."cm_icon.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/notfound_module.gif"));

			// 	Building class:
		$content = "";
		$content.=$this->sPS('
				// Adds the regular item:
			$LL = $this->includeLL();

				// Repeat this (below) for as many items you want to add!
				// Remember to add entries in the localconf.php file for additional titles.
			$url = t3lib_extMgm::extRelPath("'.$extKey.'")."'.$pathSuffix.'index.php?id=".$uid;
			$localItems[] = $backRef->linkItem(
				$GLOBALS["LANG"]->getLLL("cm'.$k.'_title",$LL),
				$backRef->excludeIcon(\'<img src="\'.t3lib_extMgm::extRelPath("'.$extKey.'").\''.$pathSuffix.'cm_icon.gif" width="15" height="12" border=0 align=top>\'),
				$backRef->urlRefForCM($url),
				1	// Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
			);
		');
		if ($config["second_level"])	{
			$secondContent = $content;
			$secondContent.=chr(10).'$menuItems=array_merge($menuItems,$localItems);';

			$content = "";
			$content.=$this->sPS('
				$LL = $this->includeLL();

				$localItems[]="spacer";
				$localItems["moreoptions_'.$cN.'"]=$backRef->linkItem(
					$GLOBALS["LANG"]->getLLL("cm'.$k.'_title_activate",$LL),
					$backRef->excludeIcon(\'<img src="\'.t3lib_extMgm::extRelPath("'.$extKey.'").\''.$pathSuffix.'cm_icon_activate.gif" width="15" height="12" border=0 align=top>\'),
					"top.loadTopMenu(\'".t3lib_div::linkThisScript()."&cmLevel=1&subname=moreoptions_'.$cN.'\');return false;",
					0,
					1
				);
			');

				// Add activate title to the locallang file.
			$this->addLocalConf($this->ext_locallang,array("title_activate"=>"...Second level ->"),"title_activate","cm",$k,0,1);
				// Add activate icon
			$this->addFileToFileArray($pathSuffix."cm_icon_activate.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/notfound_module.gif"));
		}

		if ($config["only_page"])	$content=$this->sPS('
				// Returns directly, because the clicked item was not from the pages table '.$this->WOPcomment('(WOP:'.$WOP.'[only_page])').'
			if ($table!="pages")	return $menuItems;
		').$content;

		$content.=$this->sPS('
			'.$this->WOPcomment('(WOP:'.$WOP.'[options] BEGIN) Inserts the item at the chosen location').'
		');
		if ($config["options"]=="top")	{	// In top:
			$content.=$this->sPS('
				$menuItems=array_merge($localItems,$menuItems);
			');
		} elseif ($config["options"]=="before_delete")	{	// Just before "Delete" and its preceding divider line:
			$content.=$this->sPS('
					// Find position of "delete" element:
				reset($menuItems);
				$c=0;
				while(list($k)=each($menuItems))	{
					$c++;
					if (!strcmp($k,"delete"))	break;
				}
					// .. subtract two (delete item + divider line)
				$c-=2;
					// ... and insert the items just before the delete element.
				array_splice(
					$menuItems,
					$c,
					0,
					$localItems
				);
			');
		} else	{	// In bottom (default):
			$content.=$this->sPS('
				// Simply merges the two arrays together and returns ...
				$menuItems=array_merge($menuItems,$localItems);
			');
		}
		$content.=$this->sPS('
			'.$this->WOPcomment('(WOP:'.$WOP.'[options] END)').'
		');

		if ($config["only_if_edit"])	$content=$this->wrapBody('
			if ($backRef->editOK)	{
			',$content,'
			}
		');


		if ($config["remove_view"])	$content.=$this->sPS('
				// Removes the view-item from clickmenu  '.$this->WOPcomment('(WOP:'.$WOP.'[remove_view])').'
			unset($menuItems["view"]);
		');

		$content=$this->wrapBody('
			if (!$backRef->cmLevel)	{
			',$content,'
			}
		');

		if ($config["second_level"])	{
			$content.=$this->wrapBody('
				else {
				',$secondContent,'
				}
			');
		}




			// Now wrap the function body around this:
		$content=$this->wrapBody('
			function main(&$backRef,$menuItems,$table,$uid)	{
				global $BE_USER,$TCA,$LANG;

				$localItems = Array();
				',$content,'
				return $menuItems;
			}
		');
			// Add include locallanguage function:
		$content.=$this->addLLFunc($extKey);

			// Now wrap the function body around this:
		$content=$this->wrapBody('
			class '.$cN.' {
				',$content,'
			}
		');


#		$this->printPre($content);

		$this->addFileToFileArray($filename,$this->PHPclassFile($extKey,$filename,$content,"Addition of an item to the clickmenu"));


		$cN = $this->returnName($extKey,"class","cm".$k);
		$this->writeStandardBE_xMod($extKey,$config,$pathSuffix,$cN,$k,"cm");
	}

	function writeStandardBE_xMod($extKey,$config,$pathSuffix,$cN,$k,$k_prefix)	{
			// Make conf.php file:
		$content = $this->sPS('
				// DO NOT REMOVE OR CHANGE THESE 3 LINES:
			define("TYPO3_MOD_PATH", "ext/'.$extKey.'/'.$pathSuffix.'");
			$BACK_PATH="../../../";
			$MCONF["name"]="xMOD_'.$cN.'";
		');
		$content=$this->wrapBody('
			<?php
			',$content,'
			?>
		',0);
		$this->addFileToFileArray($pathSuffix."conf.php",trim($content));
		$this->EM_CONF_presets["module"][]=ereg_replace("\/$","",$pathSuffix);

			// Add title to local lang file
		$ll=array();
		$this->addLocalConf($ll,$config,"title",$k_prefix,$k,1);
		$this->addLocalConf($ll,array("function1"=>"Function #1"),"function1",$k_prefix,$k,1,1);
		$this->addLocalConf($ll,array("function2"=>"Function #2"),"function2",$k_prefix,$k,1,1);
		$this->addLocalConf($ll,array("function3"=>"Function #3"),"function3",$k_prefix,$k,1,1);
		$this->addLocalLangFile($ll,$pathSuffix."locallang.php",'Language labels for '.$extKey.' module '.$k_prefix.$k);

			// Add clear.gif
		$this->addFileToFileArray($pathSuffix."clear.gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/clear.gif"));

			// Make module index.php file:
		$indexContent = $this->sPS('
				// DEFAULT initialization of a module [BEGIN]
			unset($MCONF);
			require ("conf.php");
			require ($BACK_PATH."init.php");
			require ($BACK_PATH."template.php");
			$LANG->includeLLFile("EXT:'.$extKey.'/'.$pathSuffix.'locallang.php");
			#include ("locallang.php");
			require_once (PATH_t3lib."class.t3lib_scbase.php");
				// ....(But no access check here...)
				// DEFAULT initialization of a module [END]
		');

		$indexContent.= $this->sPS('
			class '.$cN.' extends t3lib_SCbase {
				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						"function" => Array (
							"1" => $LANG->getLL("function1"),
							"2" => $LANG->getLL("function2"),
							"3" => $LANG->getLL("function3"),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

						// Draw the header.
					$this->doc = t3lib_div::makeInstance("mediumDoc");
					$this->doc->backPath = $BACK_PATH;
					$this->doc->form=\'<form action="" method="POST">\';

						// JavaScript
					$this->doc->JScode = \'
						<script language="javascript" type="text/javascript">
							script_ended = 0;
							function jumpToUrl(URL)	{
								document.location = URL;
							}
						</script>
					\';

					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
					if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{
						if ($BE_USER->user["admin"] && !$this->id)	{
							$this->pageinfo=array("title" => "[root-level]","uid"=>0,"pid"=>0);
						}

						$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

						$this->content.=$this->doc->startPage($LANG->getLL("title"));
						$this->content.=$this->doc->header($LANG->getLL("title"));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
						}
					}
					$this->content.=$this->doc->spacer(10);
				}
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				function moduleContent()	{
					switch((string)$this->MOD_SETTINGS["function"])	{
						case 1:
							$content="<div align=center><strong>Hello World!</strong></div><BR>
								The \'Kickstarter\' has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script \'".substr(t3lib_extMgm::extPath("'.$extKey.'"),strlen(PATH_site))."'.$pathSuffix.'index.php\' and edit it!
								<HR>
								<BR>This is the GET/POST vars sent to the script:<BR>".
								"GET:".t3lib_div::view_array($_GET)."<BR>".
								"POST:".t3lib_div::view_array($_POST)."<BR>".
								"";
							$this->content.=$this->doc->section("Message #1:",$content,0,1);
						break;
						case 2:
							$content="<div align=center><strong>Menu item #2...</strong></div>";
							$this->content.=$this->doc->section("Message #2:",$content,0,1);
						break;
						case 3:
							$content="<div align=center><strong>Menu item #3...</strong></div>";
							$this->content.=$this->doc->section("Message #3:",$content,0,1);
						break;
					}
				}
			}
		');

		$this->addFileToFileArray($pathSuffix."index.php",$this->PHPclassFile($extKey,$pathSuffix."index.php",$indexContent,$extKey.' module '.$k_prefix.$k,$cN));
	}

	function addLLfunc($extKey)	{
		return $this->sPS('
			/**
			 * Includes the [extDir]/locallang.php and returns the $LOCAL_LANG array found in that file.
			 */
			function includeLL()	{
				include(t3lib_extMgm::extPath("'.$extKey.'")."locallang.php");
				return $LOCAL_LANG;
			}
		');
	}
	function addStdLocalLangConf($ll,$k,$onlyMode=0)	{
		$this->addLocalConf($ll,array(
			"list_mode_1"=>"Mode 1",
			"list_mode_1_dk"=>"Visning 1"
		),"list_mode_1","pi",$k,1,1);
		$this->addLocalConf($ll,array(
			"list_mode_2"=>"Mode 2",
			"list_mode_2_dk"=>"Visning 2"
		),"list_mode_2","pi",$k,1,1);
		$this->addLocalConf($ll,array(
			"list_mode_3"=>"Mode 3",
			"list_mode_3_dk"=>"Visning 3"
		),"list_mode_3","pi",$k,1,1);
		$this->addLocalConf($ll,array(
			"back"=>"Back",
			"back_dk"=>"Tilbage"
		),"back","pi",$k,1,1);

		if (!$onlyMode)	{
			$this->addLocalConf($ll,array(
				"pi_list_browseresults_prev"=>"< Previous",
				"pi_list_browseresults_prev_dk"=>"< Forrige"
			),"pi_list_browseresults_prev","pi",$k,1,1);
			$this->addLocalConf($ll,array(
				"pi_list_browseresults_page"=>"Page",
				"pi_list_browseresults_page_dk"=>"Side"
			),"pi_list_browseresults_page","pi",$k,1,1);
			$this->addLocalConf($ll,array(
				"pi_list_browseresults_next"=>"Next >",
				"pi_list_browseresults_next_dk"=>"Næste >"
			),"pi_list_browseresults_next","pi",$k,1,1);
			$this->addLocalConf($ll,array(
				"pi_list_browseresults_displays"=>"Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>",
				"pi_list_browseresults_displays_dk"=>"Viser resultaterne ###SPAN_BEGIN###%s til %s</span> ud af ###SPAN_BEGIN###%s</span>"
			),"pi_list_browseresults_displays","pi",$k,1,1);

			$this->addLocalConf($ll,array(
				"pi_list_searchBox_search"=>"Search",
				"pi_list_searchBox_search_dk"=>"Søg"
			),"pi_list_searchBox_search","pi",$k,1,1);
		}

		return $ll;
	}
	function wrapBody($before,$content,$after,$indent=1)	{
		$parts=array();
		$parts[] = $this->sPS($before,0);
		$parts[] = $this->indentLines(rtrim($content),$indent);
		$parts[] = chr(10).$this->sPS($after,0);

		return implode("",$parts);
	}
	function sPS($content,$preLines=1)	{
		$lines = explode(chr(10),str_replace(chr(13),"",$content));
		$lastLineWithContent=0;
		$firstLineWithContent=-1;
		$min=array();
		reset($lines);
		while(list($k,$v)=each($lines))	{
			if (trim($v))	{
				if ($firstLineWithContent==-1)	$firstLineWithContent=$k;
				list($preSpace) = split("[^[:space:]]",$v,2);
				$min[]=count(explode(chr(9),$preSpace));
				$lastLineWithContent=$k;
			}
		}
		$number_of=count($min) ? min($min) : 0;
		$newLines=array();
		if ($firstLineWithContent>=0)	{
			for ($a=$firstLineWithContent;$a<=$lastLineWithContent;$a++)	{
				$parts = explode(chr(9),$lines[$a],$number_of);
				$newLines[]=end($parts);
			}
		}
		return str_pad("",$preLines,chr(10)).implode(chr(10),$newLines).chr(10);
	}
	function indentLines($content,$number=1)	{
		$preTab = str_pad("",$number,chr(9));
		$lines = explode(chr(10),str_replace(chr(13),"",$content));
		while(list($k,$v)=each($lines))	{
			$lines[$k]=$preTab.$v;
		}
		return implode(chr(10),$lines);
	}

	function printPre($content)	{
		echo '<pre>'.htmlspecialchars(str_replace(chr(9),"    ",$content)).'</pre>';
	}
	function addLocalConf(&$lArray,$confArray,$key,$prefix,$subPrefix,$dontPrefixKey=0,$noWOP=0,$overruleKey="")	{
		reset($this->languages);

		$overruleKey = $overruleKey ? $overruleKey : ($dontPrefixKey?"":$prefix.$subPrefix."_").$key;

		$lArray["default"][$overruleKey] = array($confArray[$key],(!$noWOP?'WOP:['.$prefix.']['.$subPrefix.']['.$key.']':''));
		while(list($k)=each($this->languages))	{
			$lArray[$k][$overruleKey] = array(trim($confArray[$key."_".$k]),(!$noWOP?'WOP:['.$prefix.']['.$subPrefix.']['.$key."_".$k.']':''));
		}
		return $lArray;
	}

	function replaceMarkers($content,$markers)	{
		reset($markers);
		while(list($k,$v)=each($markers))	{
			$content = str_replace($k,$v,$content);
		}
		return $content;
	}
	function returnName($extKey,$type,$suffix="")	{
		if (substr($extKey,0,5)=="user_")	{
			$extKey = substr($extKey,5);
			switch($type)	{
				case "class":
					return "user_".str_replace("_","",$extKey).($suffix?"_".$suffix:"");
				break;
				case "tables":
				case "fields":
				case "fields":
					return "user_".str_replace("_","",$extKey).($suffix?"_".$suffix:"");
				break;
				case "module":
					return "u".str_replace("_","",$extKey).$suffix;
				break;
			}
		} else {
			switch($type)	{
				case "class":
					return "tx_".str_replace("_","",$extKey).($suffix?"_".$suffix:"");
				break;
				case "tables":
				case "fields":
				case "fields":
					return "tx_".str_replace("_","",$extKey).($suffix?"_".$suffix:"");
				break;
				case "module":
					return "tx".str_replace("_","",$extKey).$suffix;
				break;
			}
		}
	}


	function PHPclassFile($extKey,$filename,$content,$desrc,$SOBE_class="",$SOBE_extras="")	{
		$file = trim($this->sPS('
			<?php
			/***************************************************************
			*  Copyright notice
			*
			*  (c) 2004 '.$this->userField("name").' ('.$this->userField("email").')
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
			*
			*  This script is distributed in the hope that it will be useful,
			*  but WITHOUT ANY WARRANTY; without even the implied warranty of
			*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			*  GNU General Public License for more details.
			*
			*  This copyright notice MUST APPEAR in all copies of the script!
			***************************************************************/
			/**
			 * '.$desrc.'
			 *
			 * @author	'.$this->userField("name").' <'.$this->userField("email").'>
			 */
		'));

		$file.="\n\n\n".$content."\n\n\n";

		$file.=trim($this->sPS('

			if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/'.$extKey.'/'.$filename.'"])	{
				include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/'.$extKey.'/'.$filename.'"]);
			}
			'.($SOBE_class?'



			// Make instance:
			$SOBE = t3lib_div::makeInstance("'.$SOBE_class.'");
			$SOBE->init();
			'.($SOBE_extras["include"]?'
			// Include files?
			foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);':'').'
			'.($SOBE_extras["firstLevel"]?'
			$SOBE->checkExtObj();	// Checking for first level external objects':'').'
			$SOBE->main();
			$SOBE->printContent();
			':'').'
			?>
		'));

		return $file;
	}


	function slashValueForSingleDashes($value)	{
		return str_replace("'","\'",str_replace('\\','\\\\',$value));
	}
	function getSplitLabels_reference($config,$key,$LLkey)	{
		$this->ext_locallang_db["default"][$LLkey]=array(trim($config[$key]));
		if (count($this->languages))	{
			reset($this->languages);
			while(list($lk,$lv)=each($this->languages))	{
				if (isset($this->selectedLanguages[$lk]))	{
					$this->ext_locallang_db[$lk][$LLkey]=array(trim($config[$key."_".$lk]));
				}
			}
		}
		return "LLL:EXT:".$this->extKey."/locallang_db.php:".$LLkey;
	}
	function getSplitLabels($config,$key)	{
		$language=array();
		$language[]=str_replace("|","",$config[$key]);
		if (count($this->languages))	{
			reset($this->languages);
			while(list($lk,$lv)=each($this->languages))	{
				if (isset($this->selectedLanguages[$lk]))	{
					$language[]=str_replace("|","",$config[$key."_".$lk]);
				} else $language[]="";
			}
		}
		$out = implode("|",$language);
		$out = str_replace(chr(10),"",$out);
		$out = rtrim(str_replace("|",chr(10),$out));
		$out = str_replace(chr(10),"|",$out);
		return $out;
	}
	function makeFileArray($name,$content)	{
	#	echo '<HR><strong>'.$name.'</strong><HR><pre>'.htmlspecialchars($content).'</pre>';

		return array(
			"name" => $name,
			"size" => strlen($content),
			"mtime" => time(),
			"is_executable" => 0,
			"content" => $content,
			"content_md5" => md5($content)
		);
	}
	function addFileToFileArray($name,$content,$mode=0)	{
		switch($mode)	{
			case 1:	// Append
				$this->fileArray[$name]=$this->makeFileArray($name,$this->fileArray[$name]["content"].chr(10).$content);
			break;
			case -1:	// Prepend
				$this->fileArray[$name]=$this->makeFileArray($name,$content.chr(10).$this->fileArray[$name]["content"]);
			break;
			default:	// Substitution:
				$this->fileArray[$name]=$this->makeFileArray($name,$content);
			break;
		}
	}
	function WOPcomment($str)	{
		return $str&&$this->outputWOP ? "## ".$str : "";
	}
	function makeEMCONFpreset($prefix="")	{
		$this->_addArray = $this->wizArray["emconf"][1];
		$EM_CONF=array();
		$presetFields = explode(",","title,description,category,shy,dependencies,conflicts,priority,module,state,internal,uploadfolder,createDirs,modify_tables,clearCacheOnLoad,lockType,author,author_email,author_company,private,download_password,version");
		while(list(,$s)=each($presetFields))	{
			$EM_CONF[$prefix.$s]="";
		}


		$EM_CONF[$prefix."uploadfolder"] = $this->EM_CONF_presets["uploadfolder"]?1:0;
		$EM_CONF[$prefix."clearCacheOnLoad"] = $this->EM_CONF_presets["clearCacheOnLoad"]?1:0;

		if (is_array($this->EM_CONF_presets["createDirs"]))	{
			$EM_CONF[$prefix."createDirs"] = implode(",",array_unique($this->EM_CONF_presets["createDirs"]));
		}

		if (is_array($this->EM_CONF_presets["dependencies"]) || $this->wizArray["emconf"][1]["dependencies"])	{
			$aa= t3lib_div::trimExplode(",",strtolower($this->wizArray["emconf"][1]["dependencies"]),1);
			$EM_CONF[$prefix."dependencies"] = implode(",",array_unique(array_merge($this->EM_CONF_presets["dependencies"],$aa)));
		}
		unset($this->_addArray["dependencies"]);
		if (is_array($this->EM_CONF_presets["module"]))	{
			$EM_CONF[$prefix."module"] = implode(",",array_unique($this->EM_CONF_presets["module"]));
		}
		if (is_array($this->EM_CONF_presets["modify_tables"]))	{
			$EM_CONF[$prefix."modify_tables"] = implode(",",array_unique($this->EM_CONF_presets["modify_tables"]));
		}

		return $EM_CONF;
	}
	function userField($k)	{
	  $v = "";
	  if($k == "name") {
	    $v = ($GLOBALS['BE_USER']->user['realName'] != "") ? $GLOBALS['BE_USER']->user['realName'] : $this->wizArray["emconf"][1]["author"];
	  } else if ($k == "email") {
	    $v = ($GLOBALS['BE_USER']->user['email'] != "") ? $GLOBALS['BE_USER']->user['email'] : $this->wizArray["emconf"][1]["author_email"];
	  }
	  return $v;
	}

	function ulFolder($eKey)	{
		return "uploads/tx_".str_replace("_","",$eKey)."/";
	}
	function fieldIsRTE($fC)	{
		return !strcmp($fC["type"],"textarea_rte") &&
						($fC["conf_rte"]=="basic" ||
						(t3lib_div::inList("custom,moderate",$fC["conf_rte"]) && $fC["conf_mode_cssOrNot"])
						);
	}
}

// Include extension?
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/kickstarter/modfunc1/class.tx_kickstarter_compilefiles.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/kickstarter/modfunc1/class.tx_kickstarter_compilefiles.php"]);
}

?>