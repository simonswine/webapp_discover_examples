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
 * Contains the TypoScript parser class
 *
 * $Id: class.t3lib_tsparser.php,v 1.8 2004/09/13 22:57:19 typo3 Exp $
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   80: class t3lib_TSparser
 *  133:     function parse($string,$matchObj='')
 *  169:     function nextDivider()
 *  185:     function parseSub(&$setup)
 *  337:     function rollParseSub($string,&$setup)
 *  361:     function getVal($string,$setup)
 *  387:     function setVal($string,&$setup,$value,$wipeOut=0)
 *  433:     function error($err,$num=2)
 *  445:     function checkIncludeLines($string)
 *  489:     function checkIncludeLines_array($array)
 *
 *              SECTION: Syntax highlighting
 *  532:     function doSyntaxHighlight($string,$lineNum='',$highlightBlockMode=0)
 *  553:     function regHighLight($code,$pointer,$strlen=-1)
 *  571:     function syntaxHighlight_print($lineNumDat,$highlightBlockMode)
 *
 * TOTAL FUNCTIONS: 12
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */












/**
 * The TypoScript parser
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 * @see t3lib_tstemplate, t3lib_matchcondition, t3lib_BEfunc::getPagesTSconfig(), t3lib_userAuthGroup::fetchGroupData(), t3lib_TStemplate::generateConfig()
 */
class t3lib_TSparser {
	var $strict = 1;				// If set, then key names cannot contain characters other than [:alnum:]_\.-

		// Internal
	var $setup = Array();			// TypoScript hierarchy being build during parsing.
	var $raw; 						// raw data, the input string exploded by chr(10)
	var $rawP; 						// pointer to entry in raw data array
	var $lastComment='';			// Holding the value of the last comment
	var $commentSet=0;				// Internally set, used as internal flag to create a multi-line comment (one of those like /*... */)
	var $multiLineEnabled=0;		// Internally set, when multiline value is accumulated
	var $multiLineObject='';		// Internally set, when multiline value is accumulated
	var $multiLineValue=array();	// Internally set, when multiline value is accumulated
	var $inBrace = 0;				// Internally set, when in brace. Counter.
	var $lastConditionTrue = 1;		// For each condition this flag is set, if the condition is true, else it's cleared. Then it's used by the [ELSE] condition to determine if the next part should be parsed.
	var $sections=array();			// Tracking all conditions found
	var $sectionsMatch=array();		// Tracking all matching conditions found
	var $syntaxHighLight = 0;		// If set, then syntax highlight mode is on; Call the function syntaxHighlight() to use this function
	var $highLightData=array();		// Syntax highlight data is accumulated in this array. Used by syntaxHighlight_print() to construct the output.
	var $highLightData_bracelevel = array();	// Syntax highlight data keeping track of the curly brace level for each line

		// Debugging, analysis:
	var $regComments = 0;			// DO NOT register the comments. This is default for the ordinary sitetemplate!
	var $regLinenumbers = 0;		// DO NOT register the linenumbers. This is default for the ordinary sitetemplate!
	var $errors=array();			// Error accumulation array.
	var $lineNumberOffset=0;		// Used for the error messages line number reporting. Set externally.
	var $breakPointLN=0;			// Line for break point.
	var $highLightStyles=array(
		'prespace' 			=> array('<span class="ts-prespace">','</span>'),	// Space before any content on a line
		'objstr_postspace' 	=> array('<span class="ts-objstr_postspace">','</span>'),	// Space after the object string on a line
		'operator_postspace' => array('<span class="ts-operator_postspace">','</span>'),	// Space after the operator on a line
		'operator' 			=> array('<span class="ts-operator">','</span>'),	// The operator char
		'value' 			=> array('<span class="ts-value">','</span>'),	// The value of a line
		'objstr' 			=> array('<span class="ts-objstr">','</span>'),	// The object string of a line
		'value_copy' 		=> array('<span class="ts-value_copy">','</span>'),	// The value when the copy syntax (<) is used; that means the object reference
		'value_unset' 		=> array('<span class="ts-value_unset">','</span>'),	// The value when an object is unset. Should not exist.
		'ignored'			=> array('<span class="ts-ignored">','</span>'),	// The "rest" of a line which will be ignored.
		'default' 			=> array('<span class="ts-default">','</span>'),	// The default style if none other is applied.
		'comment' 			=> array('<span class="ts-comment">','</span>'),	// Comment lines
		'condition'			=> array('<span class="ts-condition">','</span>'),	// Conditions
		'error' 			=> array('<span class="ts-error">','</span>'),	// Error messages
		'linenum' 			=> array('<span class="ts-linenum">','</span>'),	// Line numbers
	);
	var $highLightBlockStyles = '';		// Additional attributes for the <span> tags for a blockmode line
	var $highLightBlockStyles_basecolor = '#cccccc';		// The hex-HTML color for the blockmode


	/**
	 * Start parsing the input TypoScript text piece. The result is stored in $this->setup
	 *
	 * @param	string		The TypoScript text
	 * @param	object		If is object (instance of t3lib_matchcondition), then this is used to match conditions found in the TypoScript code. If matchObj not specified, then no conditions will work! (Except [GLOBAL])
	 * @return	void
	 */
	function parse($string,$matchObj='')	{
		$this->raw = explode(chr(10),$string);
		$this->rawP = 0;
		$pre = '[GLOBAL]';
		while($pre)	{
			if ($this->breakPointLN && $pre=='[_BREAK]')	{
				$this->error('Breakpoint at '.($this->lineNumberOffset+$this->rawP-2).': Line content was "'.$this->raw[$this->rawP-2].'"',1);
				break;
			}

			if (strtoupper($pre)=='[GLOBAL]' || strtoupper($pre)=='[END]' || (!$this->lastConditionTrue && strtoupper($pre)=='[ELSE]'))	{
				$pre = trim($this->parseSub($this->setup));
				$this->lastConditionTrue=1;
			} else {
				if (strtoupper($pre)!='[ELSE]')	{$this->sections[md5($pre)]=$pre;}	// we're in a specific section. Therefore we log this section
				if ((is_object($matchObj) && $matchObj->match($pre)) || $this->syntaxHighLight)	{
					if (strtoupper($pre)!='[ELSE]')	{$this->sectionsMatch[md5($pre)]=$pre;}
					$pre = trim($this->parseSub($this->setup));
					$this->lastConditionTrue=1;
				} else {
					$pre = trim($this->nextDivider());
					$this->lastConditionTrue=0;
				}
			}
		}
		if ($this->inBrace)	{$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': The script is short of '.$this->inBrace.' end brace(s)',1);	}
		if ($this->multiLineEnabled)	{$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': A multiline value section is not ended with a parenthesis!',1);	}
		$this->lineNumberOffset+=count($this->raw)+1;
	}

	/**
	 * Will search for the next condition. When found it will return the line content (the condition value) and have advanced the internal $this->rawP pointer to point to the next line after the condition.
	 *
	 * @return	string		The condition value
	 * @see parse()
	 */
	function nextDivider()	{
		while (isset($this->raw[$this->rawP]))	{
			$line = ltrim($this->raw[$this->rawP]);
			$this->rawP++;
			if ($line && substr($line,0,1)=='[')	{
				return $line;
			}
		}
	}

	/**
	 * Parsing the $this->raw TypoScript lines from pointer, $this->rawP
	 *
	 * @param	array		Reference to the setup array in which to accumulate the values.
	 * @return	string		Returns the string of the condition found, the exit signal or possible nothing (if it completed parsing with no interruptions)
	 */
	function parseSub(&$setup)	{
		while (isset($this->raw[$this->rawP]))	{
			$line = ltrim($this->raw[$this->rawP]);
			$lineP = $this->rawP;
			$this->rawP++;
			if ($this->syntaxHighLight)	$this->regHighLight("prespace",$lineP,strlen($line));

				// Breakpoint?
			if ($this->breakPointLN && ($this->lineNumberOffset+$this->rawP-1)==($this->breakPointLN+1))	{	// by adding 1 we get that line processed
				return '[_BREAK]';
			}

				// Set comment flag?
			if (!$this->multiLineEnabled && substr($line,0,2)=='/*')	{
				$this->commentSet=1;
			}

			if (!$this->commentSet && ($line || $this->multiLineEnabled))	{	// If $this->multiLineEnabled we will go and get the line values here because we know, the first if() will be true.
				if ($this->multiLineEnabled) {	// If multiline is enabled. Escape by ')'
					if (substr($line,0,1)==')')	{	// Multiline ends...
						if ($this->syntaxHighLight)	$this->regHighLight("operator",$lineP,strlen($line)-1);
						$this->multiLineEnabled=0;	// Disable multiline
						$theValue = implode($this->multiLineValue,chr(10));
						if (strstr($this->multiLineObject,'.'))	{
							$this->setVal($this->multiLineObject,$setup,array($theValue));	// Set the value deeper.
						} else {
							$setup[$this->multiLineObject] = $theValue;	// Set value regularly
							if ($this->lastComment && $this->regComments)	{
								$setup[$this->multiLineObject.'..'].=$this->lastComment;
							}
							if ($this->regLinenumbers)	{
								$setup[$this->multiLineObject.'.ln..'][]=($this->lineNumberOffset+$this->rawP-1);
							}
						}
					} else{
						if ($this->syntaxHighLight)	$this->regHighLight("value",$lineP);
						$this->multiLineValue[]=$this->raw[($this->rawP-1)];
					}
				} elseif ($this->inBrace==0 && substr($line,0,1)=='[')	{	// Beginning of condition (only on level zero compared to brace-levels
					if ($this->syntaxHighLight)	$this->regHighLight("condition",$lineP);
					return $line;
				} else {
					if (substr($line,0,1)=='[' && strtoupper(trim($line))=='[GLOBAL]')	{		// Return if GLOBAL condition is set - no matter what.
						if ($this->syntaxHighLight)	$this->regHighLight("condition",$lineP);
						$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': On return to [GLOBAL] scope, the script was short of '.$this->inBrace.' end brace(s)',1);
						$this->inBrace=0;
						return $line;
					} elseif (strcspn($line,'}#/')!=0)	{	// If not brace-end or comment
						$varL = strcspn($line,' {=<>(');		// Find object name string until we meet an operator	VER2: Added '>'!!
						$objStrName=trim(substr($line,0,$varL));
						if ($this->syntaxHighLight)	$this->regHighLight("objstr",$lineP,strlen(substr($line,$varL)));
						if ($objStrName)	{
							if ($this->strict && eregi('[^[:alnum:]_\.-]',$objStrName,$r))	{
								$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': Object Name String, "'.htmlspecialchars($objStrName).'" contains invalid character "'.$r[0].'". Must be alphanumeric or one of: "_-."');
							} else {
								$line = ltrim(substr($line,$varL));
								if ($this->syntaxHighLight)	{
									$this->regHighLight("objstr_postspace", $lineP, strlen($line));
									if (strlen($line)>0)	{
										$this->regHighLight("operator", $lineP, strlen($line)-1);
										$this->regHighLight("operator_postspace", $lineP, strlen(ltrim(substr($line,1))));
									}
								}
								switch(substr($line,0,1))	{
									case '=':
										if ($this->syntaxHighLight)	$this->regHighLight("value", $lineP, strlen(ltrim(substr($line,1)))-strlen(trim(substr($line,1))));
										if (strstr($objStrName,'.'))	{
											$value = Array();
											$value[0] = trim(substr($line,1));
											$this->setVal($objStrName,$setup,$value);
										} else {
											$setup[$objStrName] = trim(substr($line,1));
											if ($this->lastComment && $this->regComments)	{	// Setting comment..
												$setup[$objStrName.'..'].=$this->lastComment;
											}
											if ($this->regLinenumbers)	{
												$setup[$objStrName.'.ln..'][]=($this->lineNumberOffset+$this->rawP-1);
											}
										}
									break;
									case '{':
										$this->inBrace++;
										if (strstr($objStrName,'.'))	{
											$exitSig=$this->rollParseSub($objStrName,$setup);
											if ($exitSig)	return $exitSig;
										} else {
											if (!isset($setup[$objStrName.'.'])) {$setup[$objStrName.'.'] = Array();}
											$exitSig=$this->parseSub($setup[$objStrName.'.']);
											if ($exitSig)	return $exitSig;
										}
									break;
									case '(':
										$this->multiLineObject = $objStrName;
										$this->multiLineEnabled=1;
										$this->multiLineValue=array();
									break;
									case '<':
										if ($this->syntaxHighLight)	$this->regHighLight("value_copy", $lineP, strlen(ltrim(substr($line,1)))-strlen(trim(substr($line,1))));
										$theVal = trim(substr($line,1));
										if (substr($theVal,0,1)=='.') {
											$res = $this->getVal(substr($theVal,1),$setup);
										} else {
											$res = $this->getVal($theVal,$this->setup);
										}
										$this->setVal($objStrName,$setup,unserialize(serialize($res)),1);
									break;
									case '>':
										if ($this->syntaxHighLight)	$this->regHighLight("value_unset", $lineP, strlen(ltrim(substr($line,1)))-strlen(trim(substr($line,1))));
										$this->setVal($objStrName,$setup,'UNSET');
									break;
									default:
										$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': Object Name String, "'.htmlspecialchars($objStrName).'" was not preceeded by any operator, =<>({');
									break;
								}
							}
							$this->lastComment='';
						}
					} elseif (substr($line,0,1)=='}')	{
						$this->inBrace--;
						$this->lastComment='';
						if ($this->syntaxHighLight)	$this->regHighLight("operator", $lineP, strlen($line)-1);
						if ($this->inBrace<0)	{
							$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': An end brace is in excess.',1);
							$this->inBrace=0;
						} else {
							break;
						}
					} else {
						if ($this->syntaxHighLight)	$this->regHighLight("comment",	$lineP);

							// Comment. The comments are concatenated in this temporary string:
						if ($this->regComments) $this->lastComment.= trim($line).chr(10);
					}
				}
			}

				// Unset comment
			if ($this->commentSet)	{
				if ($this->syntaxHighLight)	$this->regHighLight("comment",	$lineP);
				if (substr($line,0,2)=='*/')	$this->commentSet=0;
			}
		}
	}

	/**
	 * Parsing of TypoScript keys inside a curly brace where the key is composite of at least two keys, thus having to recursively call itself to get the value
	 *
	 * @param	string		The object sub-path, eg "thisprop.another_prot"
	 * @param	array		The local setup array from the function calling this function
	 * @return	string		Returns the exitSignal
	 * @see parseSub()
	 */
	function rollParseSub($string,&$setup)	{
		if ((string)$string!='')	{
			$keyLen = strcspn($string,'.');
			if ($keyLen==strlen($string))	{
				$key = $string.'.';
				if (!isset($setup[$key])){$setup[$key]=Array();}
				$exitSig=$this->parseSub($setup[$key]);
				if ($exitSig)	return $exitSig;
			} else {
				$key = substr($string,0,$keyLen).'.';
				if (!isset($setup[$key])){$setup[$key]=Array();}
				$exitSig=$this->rollParseSub(substr($string,$keyLen+1),$setup[$key]);
				if ($exitSig)	return $exitSig;
			}
		}
	}

	/**
	 * Get a value/property pair for an object path in TypoScript, eg. "myobject.myvalue.mysubproperty". Here: Used by the "copy" operator, <
	 *
	 * @param	string		Object path for which to get the value
	 * @param	array		Global setup code if $string points to a global object path. But if string is prefixed with "." then its the local setup array.
	 * @return	array		An array with keys 0/1 being value/property respectively
	 */
	function getVal($string,$setup)	{
		if ((string)$string!='')	{
			$keyLen = strcspn($string,'.');
			if ($keyLen==strlen($string))	{
				$retArr=array();	// Added 6/6/03. Shouldn't hurt
				if (isset($setup[$string]))	{$retArr[0]=$setup[$string];	}
				if (isset($setup[$string.'.']))	{$retArr[1]=$setup[$string.'.'];	}
				return $retArr;
			} else {
				$key = substr($string,0,$keyLen).'.';
				if ($setup[$key])	{
					return $this->getVal(substr($string,$keyLen+1),$setup[$key]);
				}
			}
		}
	}

	/**
	 * Setting a value/property of an object string in the setup array.
	 *
	 * @param	string		The object sub-path, eg "thisprop.another_prot"
	 * @param	array		The local setup array from the function calling this function.
	 * @param	array		The value/property pair array to set. If only one of them is set, then the other is not touched (unless $wipeOut is set, which it is when copies are made which must include both value and property)
	 * @param	boolean		If set, then both value and property is wiped out when a copy is made of another value.
	 * @return	void
	 */
	function setVal($string,&$setup,$value,$wipeOut=0)	{
		if ((string)$string!='')	{
			$keyLen = strcspn($string,'.');
			if ($keyLen==strlen($string))	{
				if ($value=='UNSET')	{
					unset($setup[$string]);
					unset($setup[$string.'.']);
					if ($this->regLinenumbers)	{
						$setup[$string.'.ln..'][]=($this->lineNumberOffset+$this->rawP-1).'>';
					}
				} else {
					$lnRegisDone=0;
					if ($wipeOut && $this->strict)	{
						if ((isset($setup[$string]) && !isset($value[0])) || (isset($setup[$string.'.']) && !isset($value[1]))) {$this->error('Line '.($this->lineNumberOffset+$this->rawP-1).': Object copied in this line "'.trim($this->raw[($this->rawP-1)]).'" would leave either the value or properties untouched in TypoScript Version 1. Please check that this is not a problem for you.',1);}
						unset($setup[$string]);
						unset($setup[$string.'.']);
						if ($this->regLinenumbers)	{
							$setup[$string.'.ln..'][]=($this->lineNumberOffset+$this->rawP-1).'<';
							$lnRegisDone=1;
						}
					}
					if (isset($value[0])) {$setup[$string] = $value[0];}
					if (isset($value[1])) {$setup[$string.'.'] = $value[1];}
					if ($this->lastComment && $this->regComments)	{
						$setup[$string.'..'].=$this->lastComment;
					}
					if ($this->regLinenumbers && !$lnRegisDone)	{
						$setup[$string.'.ln..'][]=($this->lineNumberOffset+$this->rawP-1);
					}
				}
			} else {
				$key = substr($string,0,$keyLen).'.';
				if (!isset($setup[$key])){$setup[$key]=Array();}
				$this->setVal(substr($string,$keyLen+1),$setup[$key],$value);
			}
		}
	}

	/**
	 * Stacks errors/messages from the TypoScript parser into an internal array, $this->error
	 * If "TT" is a global object (as it is in the frontend when backend users are logged in) the message will be registered here as well.
	 *
	 * @param	string		The error message string
	 * @param	integer		The error severity (in the scale of $GLOBALS['TT']->setTSlogMessage: Approx: 2=warning, 1=info, 0=nothing, 3=fatal.)
	 * @return	void
	 */
	function error($err,$num=2)	{
		if (is_object($GLOBALS['TT']))	$GLOBALS['TT']->setTSlogMessage($err,$num);
		$this->errors[]=array($err,$num,$this->rawP-1,$this->lineNumberOffset);
	}

	/**
	 * Checks the input string (un-parsed TypoScript) for include-commands ("<INCLUDE_TYPOSCRIPT: ....")
	 * Use: t3lib_TSparser::checkIncludeLines()
	 *
	 * @param	string		Unparsed TypoScript
	 * @return	string		Complete TypoScript with includes added.
	 */
	function checkIncludeLines($string)	{
		$splitStr='<INCLUDE_TYPOSCRIPT:';
		if (strstr($string,$splitStr))	{
			$newString='';
			$allParts = explode($splitStr,chr(10).$string.chr(10));	// adds line break char before/after
			reset($allParts);
			while(list($c,$v)=each($allParts))	{
				if (!$c)	{	 // first goes through
					$newString.=$v;
				} elseif (ereg("\r?\n[ ]*$",$allParts[$c-1]))	{	// There must be a line-break char before.
					$subparts=explode('>',$v,2);
					if (ereg("^[ ]*\r?\n",$subparts[1]))	{	// There must be a line-break char after
							// SO, the include was positively recognized:
						$newString.='### '.$splitStr.$subparts[0].'> BEGIN:'.chr(10);
						$params = t3lib_div::get_tag_attributes($subparts[0]);
						if ($params['source'])	{
							$sourceParts = explode(':',$params['source'],2);
							switch(strtolower(trim($sourceParts[0])))	{
								case 'file':
									$filename = t3lib_div::getFileAbsFileName(trim($sourceParts[1]));
									if (strcmp($filename,''))	{	// Must exist and must not contain '..' and must be relative
										if (@is_file($filename) && filesize($filename)<100000)	{	// Max. 100 KB include files!
											$newString.=t3lib_div::getUrl($filename).chr(10);
										}
									}
								break;
							}
						}
						$newString.='### '.$splitStr.$subparts[0].'> END:'.chr(10);
						$newString.=$subparts[1];
					} else $newString.=$splitStr.$v;
				} else $newString.=$splitStr.$v;
			}
			$string=substr($newString,1,-1);	// not the first/last linebreak char.
		}
		return $string;
	}

	/**
	 * Parses the string in each value of the input array for include-commands
	 *
	 * @param	array		Array with TypoScript in each value
	 * @return	array		Same array but where the values has been parsed for include-commands
	 */
	function checkIncludeLines_array($array)	{
		reset($array);
		while(list($k)=each($array))	{
			$array[$k]=t3lib_TSparser::checkIncludeLines($array[$k]);
		}
		return $array;
	}





















	/**********************************
	 *
	 * Syntax highlighting
	 *
	 *********************************/

	/**
	 * Syntax highlight a TypoScript text
	 * Will parse the content. Remember, the internal setup array may contain INvalid parsed content since conditions are ignored!
	 *
	 * @param	string		The TypoScript text
	 * @param	mixed		If blank, linenumbers are NOT printed. If array then the first key is the linenumber offset to add to the internal counter.
	 * @param	boolean		If set, then the highlighted output will be formatted in blocks based on the brace levels. prespace will be ignored and empty lines represented with a single no-break-space.
	 * @return	string		HTML code for the syntax highlighted string
	 */
	function doSyntaxHighlight($string,$lineNum='',$highlightBlockMode=0)	{
		$this->syntaxHighLight=1;
		$this->highLightData=array();
		$this->error=array();
		$string = str_replace(chr(13),'',$string);		// This is done in order to prevent empty <span>..</span> sections around chr(13) content. Should not do anything but help lessen the amount of HTML code.

		$this->parse($string);

		return $this->syntaxHighlight_print($lineNum,$highlightBlockMode);
	}

	/**
	 * Registers a part of a TypoScript line for syntax highlighting.
	 *
	 * @param	string		Key from the internal array $this->highLightStyles
	 * @param	integer		Pointer to the line in $this->raw which this is about
	 * @param	integer		The number of chars LEFT on this line before the end is reached.
	 * @return	void
	 * @access private
	 * @see	parse()
	 */
	function regHighLight($code,$pointer,$strlen=-1)	{
		if ($strlen==-1)	{
			$this->highLightData[$pointer] = array(array($code,0));
		} else {
			$this->highLightData[$pointer][] = array($code,$strlen);
		}
		$this->highLightData_bracelevel[$pointer] = $this->inBrace;
	}

	/**
	 * Formatting the TypoScript code in $this->raw based on the data collected by $this->regHighLight in $this->highLightData
	 *
	 * @param	mixed		If blank, linenumbers are NOT printed. If array then the first key is the linenumber offset to add to the internal counter.
	 * @param	boolean		If set, then the highlighted output will be formatted in blocks based on the brace levels. prespace will be ignored and empty lines represented with a single no-break-space.
	 * @return	string		HTML content
	 * @access private
	 * @see doSyntaxHighlight()
	 */
	function syntaxHighlight_print($lineNumDat,$highlightBlockMode)	{
			// Registers all error messages in relation to their linenumber
		$errA=array();
		foreach($this->errors as $err)	{
			$errA[$err[2]][]=$err[0];
		}
			// Generates the syntax highlighted output:
		$lines=array();
		foreach($this->raw as $rawP => $value)	{
			$start=0;
			$strlen=strlen($value);
			$lineC='';

			if (is_array($this->highLightData[$rawP]))	{
				foreach($this->highLightData[$rawP] as $set)	{
					$len = $strlen-$start-$set[1];
					if ($len > 0)	{
						$part = substr($value,$start,$len);
						$start+=$len;
						$st = $this->highLightStyles[(isset($this->highLightStyles[$set[0]])?$set[0]:'default')];
						if (!$highlightBlockMode || $set[0]!='prespace')			$lineC.=$st[0].htmlspecialchars($part).$st[1];
					}elseif ($len < 0) debug(array($len,$value,$rawP));
				}
			} else debug(array($value));

			if (strlen(substr($value,$start)))	$lineC.=$this->highLightStyles['ignored'][0].htmlspecialchars(substr($value,$start)).$this->highLightStyles['ignored'][1];

			if ($errA[$rawP])	{
				$lineC.=$this->highLightStyles['error'][0].'<strong> - ERROR:</strong> '.htmlspecialchars(implode(';',$errA[$rawP])).$this->highLightStyles['error'][1];
			}

			if ($highlightBlockMode && $this->highLightData_bracelevel[$rawP])	{
				$lineC = str_pad('',$this->highLightData_bracelevel[$rawP]*2,' ',STR_PAD_LEFT).'<span style="'.$this->highLightBlockStyles.($this->highLightBlockStyles_basecolor?'background-color: '.t3lib_div::modifyHTMLColorAll($this->highLightBlockStyles_basecolor,-$this->highLightData_bracelevel[$rawP]*16):'').'">'.(strcmp($lineC,'')?$lineC:'&nbsp;').'</span>';
			}

			if (is_array($lineNumDat))	{
				$lineNum = $rawP+$lineNumDat[0];
				$lineC = $this->highLightStyles['linenum'][0].str_pad($lineNum,4,' ',STR_PAD_LEFT).':'.$this->highLightStyles['linenum'][1].' '.$lineC;
			}


			$lines[] = $lineC;
		}

		return '<pre class="ts-hl">'.implode(chr(10),$lines).'</pre>';
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tsparser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tsparser.php']);
}
?>