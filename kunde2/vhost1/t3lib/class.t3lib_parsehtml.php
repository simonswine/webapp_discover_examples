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
 * Contains class with functions for parsing HTML code.
 *
 * $Id: class.t3lib_parsehtml.php,v 1.20 2004/09/13 22:57:18 typo3 Exp $
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  106: class t3lib_parsehtml
 *  123:     function getSubpart($content, $marker)
 *  151:     function substituteSubpart($content,$marker,$subpartContent,$recursive=1,$keepMarker=0)
 *
 *              SECTION: Parsing HTML code
 *  223:     function splitIntoBlock($tag,$content,$eliminateExtraEndTags=0)
 *  284:     function splitIntoBlockRecursiveProc($tag,$content,&$procObj,$callBackContent,$callBackTags,$level=0)
 *  320:     function splitTags($tag,$content)
 *  354:     function getAllParts($parts,$tag_parts=1,$include_tag=1)
 *  373:     function removeFirstAndLastTag($str)
 *  392:     function getFirstTag($str)
 *  407:     function getFirstTagName($str,$preserveCase=FALSE)
 *  422:     function get_tag_attributes($tag,$deHSC=0)
 *  464:     function split_tag_attributes($tag)
 *  507:     function checkTagTypeCounts($content,$blockTags='a,b,blockquote,body,div,em,font,form,h1,h2,h3,h4,h5,h6,i,li,map,ol,option,p,pre,select,span,strong,table,td,textarea,tr,u,ul', $soloTags='br,hr,img,input,area')
 *
 *              SECTION: Clean HTML code
 *  600:     function HTMLcleaner($content, $tags=array(),$keepAll=0,$hSC=0,$addConfig=array())
 *  796:     function bidir_htmlspecialchars($value,$dir)
 *  818:     function prefixResourcePath($main_prefix,$content,$alternatives=array())
 *  899:     function prefixRelPath($prefix,$srcVal)
 *  917:     function cleanFontTags($value,$keepFace=0,$keepSize=0,$keepColor=0)
 *  948:     function mapTags($value,$tags=array(),$ltChar='<',$ltChar2='<')
 *  965:     function unprotectTags($content,$tagList='')
 *  998:     function stripTagsExcept($value,$tagList)
 * 1021:     function caseShift($str,$flag,$cacheKey='')
 * 1045:     function compileTagAttribs($tagAttrib,$meta=array(), $xhtmlClean=0)
 * 1074:     function get_tag_attributes_classic($tag,$deHSC=0)
 * 1087:     function indentLines($content, $number=1, $indentChar="\t")
 * 1104:     function HTMLparserConfig($TSconfig,$keepTags=array())
 * 1228:     function XHTML_clean($content)
 * 1250:     function processTag($value,$conf,$endTag,$protected=0)
 * 1296:     function processContent($value,$dir,$conf)
 *
 * TOTAL FUNCTIONS: 28
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */




















/**
 * Functions for parsing HTML.
 * You are encouraged to use this class in your own applications
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_parsehtml {
	var $caseShift_cache=array();


	// *******************************************'
	// COPY FROM class.tslib_content.php: / BEGIN
	// substituteSubpart
	// Cleaned locally 2/2003 !!!! (so different from tslib_content version)
	// *******************************************'

	/**
	 * Returns the first subpart encapsulated in the marker, $marker (possibly present in $content as a HTML comment)
	 *
	 * @param	string		Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
	 * @param	string		Marker string, eg. "###CONTENT_PART###"
	 * @return	string
	 */
	function getSubpart($content, $marker)	{
		if ($marker && strstr($content,$marker))	{
			$start = strpos($content, $marker)+strlen($marker);
			$stop = @strpos($content, $marker, $start+1);
			$sub = substr($content, $start, $stop-$start);

			$reg=Array();
			ereg('^[^<]*-->',$sub,$reg);
			$start+=strlen($reg[0]);

			$reg=Array();
			ereg('<!--[^>]*$',$sub,$reg);
			$stop-=strlen($reg[0]);

			return substr($content, $start, $stop-$start);
		}
	}

	/**
	 * Substitutes a subpart in $content with the content of $subpartContent.
	 *
	 * @param	string		Content with subpart wrapped in fx. "###CONTENT_PART###" inside.
	 * @param	string		Marker string, eg. "###CONTENT_PART###"
	 * @param	array		If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the content of the subpart (fetched by getSubpart())
	 * @param	boolean		If $recursive is set, the function calls itself with the content set to the remaining part of the content after the second marker. This means that proceding subparts are ALSO substituted!
	 * @param	boolean		If set, the marker around the subpart is not removed, but kept in the output
	 * @return	string		Processed input content
	 */
	function substituteSubpart($content,$marker,$subpartContent,$recursive=1,$keepMarker=0)	{
		$start = strpos($content, $marker);
		$stop = @strpos($content, $marker, $start+1)+strlen($marker);
		if ($start && $stop>$start)	{
			// code before
			$before = substr($content, 0, $start);
			$reg=Array();
			ereg('<!--[^>]*$',$before,$reg);
			$start-=strlen($reg[0]);
			if ($keepMarker)	{
				$reg_k=Array();
				if ($reg[0])	ereg('^[^>]*-->',substr($content,$start),$reg_k);
				$before_marker = substr($content, $start, strlen($reg_k[0]?$reg_k[0]:$marker));
			}
			$before = substr($content, 0, $start);
				// code after
			$after = substr($content, $stop);
			$reg=Array();
			ereg('^[^<]*-->',$after,$reg);
			$stop+=strlen($reg[0]);
			if ($keepMarker)	{
				$reg_k=Array();
				if ($reg[0])	ereg('<!--[^<]*$',substr($content,0,$stop),$reg_k);
				$sLen = strlen($reg_k[0]?$reg_k[0]:$marker);
				$after_marker = substr($content, $stop-$sLen,$sLen);
			}
			$after = substr($content, $stop);


				// replace?
			if (is_array($subpartContent))	{
				$substContent=$subpartContent[0].$this->getSubpart($content,$marker).$subpartContent[1];
			} else {
				$substContent=$subpartContent;
			}

			if ($recursive && strpos($after, $marker))	{
				return $before.($keepMarker?$before_marker:'').$substContent.($keepMarker?$after_marker:'').$this->substituteSubpart($after,$marker,$subpartContent);
			} else {
				return $before.($keepMarker?$before_marker:'').$substContent.($keepMarker?$after_marker:'').$after;
			}
		} else {
			return $content;
		}
	}
	// *******************************************'
	// COPY FROM class.tslib_content.php: / END
	// *******************************************'







	/************************************
	 *
	 * Parsing HTML code
	 *
	 ************************************/

	/**
	 * Returns an array with the $content divided by tag-blocks specified with the list of tags, $tag
	 * Even numbers in the array are outside the blocks, Odd numbers are block-content.
	 * Use ->getAllParts() and ->removeFirstAndLastTag() to process the content if needed.
	 *
	 * @param	string		List of tags, comma separated.
	 * @param	string		HTML-content
	 * @param	boolean		If set, excessive end tags are ignored - you should probably set this in most cases.
	 * @return	array		Even numbers in the array are outside the blocks, Odd numbers are block-content.
	 * @see splitTags(), getAllParts(), removeFirstAndLastTag()
	 */
	function splitIntoBlock($tag,$content,$eliminateExtraEndTags=0)	{
		$tags=array_unique(t3lib_div::trimExplode(',',$tag,1));
		$regexStr = '</?('.implode('|',$tags).')(>|[[:space:]][^>]*>)';

		$parts = spliti($regexStr,$content);

		$newParts=array();
		$pointer=strlen($parts[0]);
		$buffer=$parts[0];
		$nested=0;
		reset($parts);
		next($parts);
		while(list($k,$v)=each($parts))	{
			$isEndTag= substr($content,$pointer,2)=='</' ? 1 : 0;
			$tagLen = strcspn(substr($content,$pointer),'>')+1;

			if (!$isEndTag)	{	// We meet a start-tag:
				if (!$nested)	{	// Ground level:
					$newParts[]=$buffer;	// previous buffer stored
					$buffer='';
				}
				$nested++;	// We are inside now!
				$mbuffer=substr($content,$pointer,strlen($v)+$tagLen);	// New buffer set and pointer increased
				$pointer+=strlen($mbuffer);
				$buffer.=$mbuffer;
			} else {	// If we meet an endtag:
				$nested--;	// decrease nested-level
				$eliminated=0;
				if ($eliminateExtraEndTags && $nested<0)	{
					$nested=0;
					$eliminated=1;
				} else {
					$buffer.=substr($content,$pointer,$tagLen);	// In any case, add the endtag to current buffer and increase pointer
				}
				$pointer+=$tagLen;
				if (!$nested && !$eliminated)	{	// if we're back on ground level, (and not by eliminating tags...
					$newParts[]=$buffer;
					$buffer='';
				}
				$mbuffer=substr($content,$pointer,strlen($v));	// New buffer set and pointer increased
				$pointer+=strlen($mbuffer);
				$buffer.=$mbuffer;
			}

		}
		$newParts[]=$buffer;
		return $newParts;
	}

	/**
	 * Splitting content into blocks *recursively* and processing tags/content with call back functions.
	 *
	 * @param	string		Tag list, see splitIntoBlock()
	 * @param	string		Content, see splitIntoBlock()
	 * @param	object		Object where call back methods are.
	 * @param	string		Name of call back method for content; "function callBackContent($str,$level)"
	 * @param	string		Name of call back method for tags; "function callBackTags($tags,$level)"
	 * @param	integer		Indent level
	 * @return	string		Processed content
	 * @see splitIntoBlock()
	 */
	function splitIntoBlockRecursiveProc($tag,$content,&$procObj,$callBackContent,$callBackTags,$level=0)	{
		$parts = $this->splitIntoBlock($tag,$content,TRUE);
		foreach($parts as $k => $v)	{
			if ($k%2)	{
				$firstTagName = $this->getFirstTagName($v, TRUE);
				$tagsArray = array();
				$tagsArray['tag_start'] = $this->getFirstTag($v);
				$tagsArray['tag_end'] = '</'.$firstTagName.'>';
				$tagsArray['tag_name'] = strtolower($firstTagName);
				$tagsArray['add_level'] = 1;
				$tagsArray['content'] = $this->splitIntoBlockRecursiveProc($tag,$this->removeFirstAndLastTag($v),$procObj,$callBackContent,$callBackTags,$level+$tagsArray['add_level']);

				if ($callBackTags)	$tagsArray = $procObj->$callBackTags($tagsArray,$level);

				$parts[$k] =
					$tagsArray['tag_start'].
					$tagsArray['content'].
					$tagsArray['tag_end'];
			} else {
				if ($callBackContent)	$parts[$k] = $procObj->$callBackContent($parts[$k],$level);
			}
		}

		return implode('',$parts);
	}

	/**
	 * Returns an array with the $content divided by tag-blocks specified with the list of tags, $tag
	 * Even numbers in the array are outside the blocks, Odd numbers are block-content.
	 * Use ->getAllParts() and ->removeFirstAndLastTag() to process the content if needed.
	 *
	 * @param	string		List of tags
	 * @param	string		HTML-content
	 * @return	array		Even numbers in the array are outside the blocks, Odd numbers are block-content.
	 * @see splitIntoBlock(), getAllParts(), removeFirstAndLastTag()
	 */
	function splitTags($tag,$content)	{
		$tags = t3lib_div::trimExplode(',',$tag,1);
		$regexStr = '<('.implode('|',$tags).')(>|\/>|[[:space:]][^>]*>)';
		$parts = spliti($regexStr,$content);

		$pointer = strlen($parts[0]);
		$newParts = array();
		$newParts[] = $parts[0];
		reset($parts);
		next($parts);
		while(list($k,$v)=each($parts))	{
			$tagLen = strcspn(substr($content,$pointer),'>')+1;

				// Set tag:
			$tag = substr($content,$pointer,$tagLen);	// New buffer set and pointer increased
			$newParts[] = $tag;
			$pointer+= strlen($tag);

				// Set content:
			$newParts[] = $v;
			$pointer+= strlen($v);
		}
		return $newParts;
	}

	/**
	 * Returns an array with either tag or non-tag content of the result from ->splitIntoBlock()/->splitTags()
	 *
	 * @param	array		Parts generated by ->splitIntoBlock() or >splitTags()
	 * @param	boolean		Whether to return the tag-parts (default,true) or what was outside the tags.
	 * @param	boolean		Whether to include the tags in the tag-parts (most useful for input made by ->splitIntoBlock())
	 * @return	array		Tag-parts/Non-tag-parts depending on input argument settings
	 * @see splitIntoBlock(), splitTags()
	 */
	function getAllParts($parts,$tag_parts=1,$include_tag=1)	{
		reset($parts);
		$newParts=array();
		while(list($k,$v)=each($parts))	{
			if (($k+($tag_parts?0:1))%2)	{
				if (!$include_tag)	$v=$this->removeFirstAndLastTag($v);
				$newParts[]=$v;
			}
		}
		return $newParts;
	}

	/**
	 * Removes the first and last tag in the string
	 * Anything before and after the first and last tags respectively is also removed
	 *
	 * @param	string		String to process
	 * @return	string
	 */
	function removeFirstAndLastTag($str)	{
			// First:
		$endLen = strcspn($str,'>')+1;
		$str = substr($str,$endLen);
			// Last:
		$str = strrev($str);
		$endLen = strcspn($str,'<')+1;
		$str = substr($str,$endLen);
			// return
		return strrev($str);
	}

	/**
	 * Returns the first tag in $str
	 * Actually everything from the begining of the $str is returned, so you better make sure the tag is the first thing...
	 *
	 * @param	string		HTML string with tags
	 * @return	string
	 */
	function getFirstTag($str)	{
			// First:
		$endLen = strcspn($str,'>')+1;
		$str = substr($str,0,$endLen);
		return $str;
	}

	/**
	 * Returns the NAME of the first tag in $str
	 *
	 * @param	string		HTML tag (The element name MUST be separated from the attributes by a space character! Just *whitespace* will not do)
	 * @param	boolean		If set, then the tag is NOT converted to uppercase by case is preserved.
	 * @return	string		Tag name in upper case
	 * @see getFirstTag()
	 */
	function getFirstTagName($str,$preserveCase=FALSE)	{
		list($tag) = split('[[:space:]]',substr(trim($this->getFirstTag($str)),1,-1), 2);
		if (!$preserveCase)	$tag = strtoupper($tag);

		return trim($tag);
	}

	/**
	 * Returns an array with all attributes as keys. Attributes are only lowercase a-z
	 * If a attribute is empty (shorthand), then the value for the key is empty. You can check if it existed with isset()
	 *
	 * @param	string		Tag: $tag is either a whole tag (eg '<TAG OPTION ATTRIB=VALUE>') or the parameterlist (ex ' OPTION ATTRIB=VALUE>')
	 * @param	boolean		If set, the attribute values are de-htmlspecialchar'ed. Should actually always be set!
	 * @return	array		array(Tag attributes,Attribute meta-data)
	 */
	function get_tag_attributes($tag,$deHSC=0)	{
		list($components,$metaC) = $this->split_tag_attributes($tag);
		$name = '';	 // attribute name is stored here
		$valuemode = '';
		$attributes = array();
		$attributesMeta = array();
		if (is_array($components))	{
			while (list($key,$val) = each ($components))	{
				if ($val != '=')	{	// Only if $name is set (if there is an attribute, that waits for a value), that valuemode is enabled. This ensures that the attribute is assigned it's value
					if ($valuemode)	{
						if ($name)	{
							$attributes[$name] = $deHSC?t3lib_div::htmlspecialchars_decode($val):$val;
							$attributesMeta[$name]['dashType']=$metaC[$key];
							$name = '';
						}
					} else {
						if ($namekey = ereg_replace('[^a-zA-Z0-9_:-]','',$val))	{
							$name = strtolower($namekey);
							$attributesMeta[$name]=array();
							$attributesMeta[$name]['origTag']=$namekey;
							$attributes[$name] = '';
						}
					}
					$valuemode = '';
				} else {
					$valuemode = 'on';
				}
			}
			if (is_array($attributes))	reset($attributes);
			return array($attributes,$attributesMeta);
		}
	}

	/**
	 * Returns an array with the 'components' from an attribute list. The result is normally analyzed by get_tag_attributes
	 * Removes tag-name if found
	 *
	 * @param	string		The tag or attributes
	 * @return	array
	 * @access private
	 * @see t3lib_div::split_tag_attributes()
	 */
	function split_tag_attributes($tag)	{
		$tag_tmp = trim(eregi_replace ('^<[^[:space:]]*','',trim($tag)));
			// Removes any > in the end of the string
		$tag_tmp = trim(eregi_replace ('>$','',$tag_tmp));

		$metaValue = array();
		$value = array();
		while (strcmp($tag_tmp,''))	{	// Compared with empty string instead , 030102
			$firstChar=substr($tag_tmp,0,1);
			if (!strcmp($firstChar,'"') || !strcmp($firstChar,"'"))	{
				$reg=explode($firstChar,$tag_tmp,3);
				$value[]=$reg[1];
				$metaValue[]=$firstChar;
				$tag_tmp=trim($reg[2]);
			} elseif (!strcmp($firstChar,'=')) {
				$value[] = '=';
				$metaValue[]='';
				$tag_tmp = trim(substr($tag_tmp,1));		// Removes = chars.
			} else {
					// There are '' around the value. We look for the next ' ' or '>'
				$reg = split('[[:space:]=]',$tag_tmp,2);
				$value[] = trim($reg[0]);
				$metaValue[]='';
				$tag_tmp = trim(substr($tag_tmp,strlen($reg[0]),1).$reg[1]);
			}
		}
		if (is_array($value))	reset($value);
		return array($value,$metaValue);
	}

	/**
	 * Checks whether block/solo tags are found in the correct amounts in HTML content
	 * Block tags are tags which are required to have an equal amount of start and end tags, eg. "<table>...</table>"
	 * Solo tags are tags which are required to have ONLY start tags (possibly with an XHTML ending like ".../>")
	 * NOTICE: Correct XHTML might actually fail since "<br></br>" is allowed as well as "<br/>". However only the LATTER is accepted by this function (with "br" in the "solo-tag" list), the first example will result in a warning.
	 * NOTICE: Correct XHTML might actually fail since "<p/>" is allowed as well as "<p></p>". However only the LATTER is accepted by this function (with "p" in the "block-tag" list), the first example will result in an ERROR!
	 * NOTICE: Correct HTML version "something" allows eg. <p> and <li> to be NON-ended (implicitly ended by other tags). However this is NOT accepted by this function (with "p" and "li" in the block-tag list) and it will result in an ERROR!
	 *
	 * @param	string		HTML content to analyze
	 * @param	string		Tag names for block tags (eg. table or div or p) in lowercase, commalist (eg. "table,div,p")
	 * @param	string		Tag names for solo tags (eg. img, br or input) in lowercase, commalist ("img,br,input")
	 * @return	array		Analyse data.
	 */
	function checkTagTypeCounts($content,$blockTags='a,b,blockquote,body,div,em,font,form,h1,h2,h3,h4,h5,h6,i,li,map,ol,option,p,pre,select,span,strong,table,td,textarea,tr,u,ul', $soloTags='br,hr,img,input,area')	{
		$content = strtolower($content);
		$analyzedOutput=array();
		$analyzedOutput['counts']=array();	// Counts appearances of start-tags
		$analyzedOutput['errors']=array();	// Lists ERRORS
		$analyzedOutput['warnings']=array();	// Lists warnings.
		$analyzedOutput['blocks']=array();	// Lists stats for block-tags
		$analyzedOutput['solo']=array();	// Lists stats for solo-tags

			// Block tags, must have endings...
		$blockTags = explode(',',$blockTags);
		foreach($blockTags as $tagName)	{
			$countBegin = count(split('<'.$tagName.'[^[:alnum:]]',$content))-1;
			$countEnd = count(split('<\/'.$tagName.'[^[:alnum:]]',$content))-1;
			$analyzedOutput['blocks'][$tagName]=array($countBegin,$countEnd,$countBegin-$countEnd);
			if ($countBegin)	$analyzedOutput['counts'][$tagName]=$countBegin;
			if ($countBegin-$countEnd)	{
				if ($countBegin-$countEnd > 0)	{
					$analyzedOutput['errors'][$tagName]='There were more start-tags ('.$countBegin.') than end-tags ('.$countEnd.') for the element "'.$tagName.'". There should be an equal amount!';
				} else {
					$analyzedOutput['warnings'][$tagName]='There were more end-tags ('.$countEnd.') than start-tags ('.$countBegin.') for the element "'.$tagName.'". There should be an equal amount! However the problem is not fatal.';
				}
			}
		}

			// Solo tags, must NOT have endings...
		$soloTags = explode(',',$soloTags);
		foreach($soloTags as $tagName)	{
			$countBegin = count(split('<'.$tagName.'[^[:alnum:]]',$content))-1;
			$countEnd = count(split('<\/'.$tagName.'[^[:alnum:]]',$content))-1;
			$analyzedOutput['solo'][$tagName]=array($countBegin,$countEnd);
			if ($countBegin)	$analyzedOutput['counts'][$tagName]=$countBegin;
			if ($countEnd)	{
				$analyzedOutput['warnings'][$tagName]='There were end-tags found ('.$countEnd.') for the element "'.$tagName.'". This was not expected (although XHTML technically allows it).';
			}
		}

		return $analyzedOutput;
	}












	/*********************************
	 *
	 * Clean HTML code
	 *
	 *********************************/

	/**
	 * Function that can clean up HTML content according to configuration given in the $tags array.
	 *
	 * Initializing the $tags array to allow a list of tags (in this case <B>,<I>,<U> and <A>), set it like this:		 $tags = array_flip(explode(',','b,a,i,u'))
	 * If the value of the $tags[$tagname] entry is an array, advanced processing of the tags is initialized. These are the options:
	 *
	 * 	$tags[$tagname] = Array(
	 * 		'overrideAttribs' => ''		If set, this string is preset as the attributes of the tag
	 * 		'allowedAttribs' =>   '0' (zero) = no attributes allowed, '[commalist of attributes]' = only allowed attributes. If blank, all attributes are allowed.
	 * 		'fixAttrib' => Array(
	 * 			'[attribute name]' => Array (
	 * 				'set' => Force the attribute value to this value.
	 * 				'unset' => Boolean: If set, the attribute is unset.
	 * 				'default' => 	If no attribute exists by this name, this value is set as default value (if this value is not blank)
	 * 				'always' => 	Boolean. If set, the attribute is always processed. Normally an attribute is processed only if it exists
	 * 				'trim,intval,lower,upper' => 	All booleans. If any of these keys are set, the value is passed through the respective PHP-functions.
	 * 				'range' => Array ('[low limit]','[high limit, optional]')		Setting integer range.
	 * 				'list' => Array ('[value1/default]','[value2]','[value3]')		Attribute must be in this list. If not, the value is set to the first element.
	 * 				'removeIfFalse' => 	Boolean/'blank'.	If set, then the attribute is removed if it is 'false'. If this value is set to 'blank' then the value must be a blank string (that means a 'zero' value will not be removed)
	 * 				'removeIfEquals' => 	[value]	If the attribute value matches the value set here, then it is removed.
	 * 				'casesensitiveComp' => 1	If set, then the removeIfEquals and list comparisons will be case sensitive. Otherwise not.
	 * 			)
	 * 		),
	 * 		'protect' => '',	Boolean. If set, the tag <> is converted to &lt; and &gt;
	 * 		'remap' => '',		String. If set, the tagname is remapped to this tagname
	 * 		'rmTagIfNoAttrib' => '',	Boolean. If set, then the tag is removed if no attributes happend to be there.
	 * 		'nesting' => '',	Boolean/'global'. If set true, then this tag must have starting and ending tags in the correct order. Any tags not in this order will be discarded. Thus '</B><B><I></B></I></B>' will be converted to '<B><I></B></I>'. Is the value 'global' then true nesting in relation to other tags marked for 'global' nesting control is preserved. This means that if <B> and <I> are set for global nesting then this string '</B><B><I></B></I></B>' is converted to '<B></B>'
	 * 	)
	 *
	 * @param	string		$content; is the HTML-content being processed. This is also the result being returned.
	 * @param	array		$tags; is an array where each key is a tagname in lowercase. Only tags present as keys in this array are preserved. The value of the key can be an array with a vast number of options to configure.
	 * @param	string		$keepAll; boolean/'protect', if set, then all tags are kept regardless of tags present as keys in $tags-array. If 'protect' then the preserved tags have their <> converted to &lt; and &gt;
	 * @param	integer		$hSC; Values -1,0,1,2: Set to zero= disabled, set to 1 then the content BETWEEN tags is htmlspecialchar()'ed, set to -1 its the opposite and set to 2 the content will be HSC'ed BUT with preservation for real entities (eg. "&amp;" or "&#234;")
	 * @param	array		Configuration array send along as $conf to the internal functions ->processContent() and ->processTag()
	 * @return	string		Processed HTML content
	 */
	function HTMLcleaner($content, $tags=array(),$keepAll=0,$hSC=0,$addConfig=array())	{
		$newContent = array();
		$tokArr = explode('<',$content);
		$newContent[] = $this->processContent(current($tokArr),$hSC,$addConfig);
		next($tokArr);

		$c = 1;
		$tagRegister = array();
		$tagStack = array();
		while(list(,$tok)=each($tokArr))	{
			$firstChar = substr($tok,0,1);
#			if (strcmp(trim($firstChar),''))	{		// It is a tag...
			if (ereg('[[:alnum:]\/]',$firstChar))	{		// It is a tag... (first char is a-z0-9 or /) (fixed 19/01 2004). This also avoids triggering on <?xml..> and <!DOCTYPE..>
				$tagEnd = strcspn($tok,'>');
				if (strlen($tok)!=$tagEnd)	{	// If there is and end-bracket...
					$endTag = $firstChar=='/' ? 1 : 0;
					$tagContent = substr($tok,$endTag,$tagEnd-$endTag);
					$tagParts = split('[[:space:]]',$tagContent,2);
					$tagName = strtolower($tagParts[0]);
					if (isset($tags[$tagName]))	{
						if (is_array($tags[$tagName]))	{	// If there is processing to do for the tag:

							if (!$endTag)	{	// If NOT an endtag, do attribute processing (added dec. 2003)
									// Override attributes
								if (strcmp($tags[$tagName]['overrideAttribs'],''))	{
									$tagParts[1]=$tags[$tagName]['overrideAttribs'];
								}

									// Allowed tags
								if (strcmp($tags[$tagName]['allowedAttribs'],''))	{
									if (!strcmp($tags[$tagName]['allowedAttribs'],'0'))	{	// No attribs allowed
										$tagParts[1]='';
									} elseif (trim($tagParts[1])) {
										$tagAttrib = $this->get_tag_attributes($tagParts[1]);
										$tagParts[1]='';
										$newTagAttrib = array();
										$tList = t3lib_div::trimExplode(',',strtolower($tags[$tagName]['allowedAttribs']),1);
										while(list(,$allowTag)=each($tList))	{
											if (isset($tagAttrib[0][$allowTag]))	$newTagAttrib[$allowTag]=$tagAttrib[0][$allowTag];
										}
										$tagParts[1]=$this->compileTagAttribs($newTagAttrib,$tagAttrib[1]);
									}
								}

									// Fixed attrib values
								if (is_array($tags[$tagName]['fixAttrib']))	{
									$tagAttrib = $this->get_tag_attributes($tagParts[1]);
									$tagParts[1]='';
									reset($tags[$tagName]['fixAttrib']);
									while(list($attr,$params)=each($tags[$tagName]['fixAttrib']))	{
										if (strlen($params['set']))	$tagAttrib[0][$attr] = $params['set'];
										if (strlen($params['unset']))	unset($tagAttrib[0][$attr]);
										if (strcmp($params['default'],'') && !isset($tagAttrib[0][$attr]))	$tagAttrib[0][$attr]=$params['default'];
										if ($params['always'] || isset($tagAttrib[0][$attr]))	{
											if ($params['trim'])	{$tagAttrib[0][$attr]=trim($tagAttrib[0][$attr]);}
											if ($params['intval'])	{$tagAttrib[0][$attr]=intval($tagAttrib[0][$attr]);}
											if ($params['lower'])	{$tagAttrib[0][$attr]=strtolower($tagAttrib[0][$attr]);}
											if ($params['upper'])	{$tagAttrib[0][$attr]=strtoupper($tagAttrib[0][$attr]);}
											if ($params['range'])	{
												if (isset($params['range'][1]))	{
													$tagAttrib[0][$attr]=t3lib_div::intInRange($tagAttrib[0][$attr],intval($params['range'][0]),intval($params['range'][1]));
												} else {
													$tagAttrib[0][$attr]=t3lib_div::intInRange($tagAttrib[0][$attr],intval($params['range'][0]));
												}
											}
											if (is_array($params['list']))	{
												if (!in_array($this->caseShift($tagAttrib[0][$attr],$params['casesensitiveComp']),$this->caseShift($params['list'],$params['casesensitiveComp'],$tagName)))	$tagAttrib[0][$attr]=$params['list'][0];
											}
											if (($params['removeIfFalse'] && $params['removeIfFalse']!='blank' && !$tagAttrib[0][$attr]) || ($params['removeIfFalse']=='blank' && !strcmp($tagAttrib[0][$attr],'')))	{
												unset($tagAttrib[0][$attr]);
											}
											if (strcmp($params['removeIfEquals'],'') && !strcmp($this->caseShift($tagAttrib[0][$attr],$params['casesensitiveComp']),$this->caseShift($params['removeIfEquals'],$params['casesensitiveComp'])))	{
												unset($tagAttrib[0][$attr]);
											}
											if ($params['prefixLocalAnchors'])	{
												if (substr($tagAttrib[0][$attr],0,1)=='#')	{
													$prefix = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
													$tagAttrib[0][$attr] = $prefix.$tagAttrib[0][$attr];
													if ($params['prefixLocalAnchors']==2 && t3lib_div::isFirstPartOfStr($prefix,t3lib_div::getIndpEnv('TYPO3_SITE_URL')))		{
														$tagAttrib[0][$attr] = substr($tagAttrib[0][$attr],strlen(t3lib_div::getIndpEnv('TYPO3_SITE_URL')));
													}
												}
											}
											if ($params['prefixRelPathWith'])	{
												$urlParts = parse_url($tagAttrib[0][$attr]);
												if (!$urlParts['scheme'] && substr($urlParts['path'],0,1)!='/')	{	// If it is NOT an absolute URL (by http: or starting "/")
													$tagAttrib[0][$attr] = $params['prefixRelPathWith'].$tagAttrib[0][$attr];
												}
											}
											if ($params['userFunc'])	{
												$tagAttrib[0][$attr] = t3lib_div::callUserFunction($params['userFunc'],$tagAttrib[0][$attr],$this);
											}
										}
									}
									$tagParts[1]=$this->compileTagAttribs($tagAttrib[0],$tagAttrib[1]);
								}
							} else {	// If endTag, remove any possible attributes:
								$tagParts[1]='';
							}

								// Protecting the tag by converting < and > to &lt; and &gt; ??
							if ($tags[$tagName]['protect'])	{
								$lt = '&lt;';	$gt = '&gt;';
							} else {
								$lt = '<';	$gt = '>';
							}
								// Remapping tag name?
							if ($tags[$tagName]['remap'])	$tagParts[0] = $tags[$tagName]['remap'];

								// rmTagIfNoAttrib
							if ($endTag || trim($tagParts[1]) || !$tags[$tagName]['rmTagIfNoAttrib'])	{
								$setTag=1;

								if ($tags[$tagName]['nesting'])	{
									if (!is_array($tagRegister[$tagName]))	$tagRegister[$tagName]=array();

									if ($endTag)	{
/*										if ($tags[$tagName]['nesting']=='global')	{
											$lastEl = end($tagStack);
											$correctTag = !strcmp($tagName,$lastEl);
										} else $correctTag=1;
	*/
										$correctTag=1;
										if ($tags[$tagName]['nesting']=='global')	{
											$lastEl = end($tagStack);
											if (strcmp($tagName,$lastEl))	{
												if (in_array($tagName,$tagStack))	{
													while(count($tagStack) && strcmp($tagName,$lastEl))	{
														$elPos = end($tagRegister[$lastEl]);
														unset($newContent[$elPos]);

														array_pop($tagRegister[$lastEl]);
														array_pop($tagStack);
														$lastEl = end($tagStack);
													}
												} else {
													$correctTag=0;	// In this case the
												}
											}
										}
										if (!count($tagRegister[$tagName]) || !$correctTag)	{
											$setTag=0;
										} else {
											array_pop($tagRegister[$tagName]);
											if ($tags[$tagName]['nesting']=='global')	{array_pop($tagStack);}
										}
									} else {
										array_push($tagRegister[$tagName],$c);
										if ($tags[$tagName]['nesting']=='global')	{array_push($tagStack,$tagName);}
									}
								}

								if ($setTag)	{
										// Setting the tag
									$newContent[$c++]=$this->processTag($lt.($endTag?'/':'').trim($tagParts[0].' '.$tagParts[1]).$gt,$addConfig,$endTag,$lt=='&lt;');
								}
							}
						} else {
							$newContent[$c++]=$this->processTag('<'.($endTag?'/':'').$tagContent.'>',$addConfig,$endTag);
						}
					} elseif ($keepAll) {	// This is if the tag was not defined in the array for processing:
						if (!strcmp($keepAll,'protect'))	{
							$lt = '&lt;';	$gt = '&gt;';
						} else {
							$lt = '<';	$gt = '>';
						}
						$newContent[$c++]=$this->processTag($lt.($endTag?'/':'').$tagContent.$gt,$addConfig,$endTag,$lt=='&lt;');
					}
					$newContent[$c++]=$this->processContent(substr($tok,$tagEnd+1),$hSC,$addConfig);
				} else {
					$newContent[$c++]=$this->processContent('<'.$tok,$hSC,$addConfig);	// There were not end-bracket, so no tag...
				}
			} else {
				$newContent[$c++]=$this->processContent('<'.$tok,$hSC,$addConfig);	// It was not a tag anyways
			}
		}

			// Unsetting tags:
		reset($tagRegister);
		while(list($tag,$positions)=each($tagRegister))	{
			reset($positions);
			while(list(,$pKey)=each($positions))	{
				unset($newContent[$pKey]);
			}
		}

		return implode('',$newContent);
	}

	/**
	 * Converts htmlspecialchars forth ($dir=1) AND back ($dir=-1)
	 *
	 * @param	string		Input value
	 * @param	integer		Direction: forth ($dir=1, dir=2 for preserving entities) AND back ($dir=-1)
	 * @return	string		Output value
	 */
	function bidir_htmlspecialchars($value,$dir)	{
		if ($dir==1)	{
			$value = htmlspecialchars($value);
		} elseif ($dir==2)	{
			$value = t3lib_div::deHSCentities(htmlspecialchars($value));
		} elseif ($dir==-1) {
			$value = str_replace('&gt;','>',$value);
			$value = str_replace('&lt;','<',$value);
			$value = str_replace('&quot;','"',$value);
			$value = str_replace('&amp;','&',$value);
		}
		return $value;
	}

	/**
	 * Prefixes the relative paths of hrefs/src/action in the tags [td,table,body,img,input,form,link,script,a] in the $content with the $main_prefix or and alternative given by $alternatives
	 *
	 * @param	string		Prefix string
	 * @param	string		HTML content
	 * @param	array		Array with alternative prefixes for certain of the tags. key=>value pairs where the keys are the tag element names in uppercase
	 * @return	string		Processed HTML content
	 */
	function prefixResourcePath($main_prefix,$content,$alternatives=array())	{

		$parts = $this->splitTags('td,table,body,img,input,form,link,script,a',$content);
		foreach($parts as $k => $v)	{
			if ($k%2)	{
				$params = $this->get_tag_attributes($v,1);
				$tagEnd = substr($v,-2)=='/>' ? ' />' : '>';	// Detect tag-ending so that it is re-applied correctly.
				$firstTagName = $this->getFirstTagName($v);	// The 'name' of the first tag
				$somethingDone=0;
				$prefix = isset($alternatives[strtoupper($firstTagName)]) ? $alternatives[strtoupper($firstTagName)] : $main_prefix;
				switch(strtolower($firstTagName))	{
						// background - attribute:
					case 'td':
					case 'body':
					case 'table':
						$src = $params[0]['background'];
						if ($src)	{
							$params[0]['background'] = $this->prefixRelPath($prefix,$params[0]['background']);
							$somethingDone=1;
						}
					break;
						// src attribute
					case 'img':
					case 'input':
					case 'script':
						$src = $params[0]['src'];
						if ($src)	{
							$params[0]['src'] = $this->prefixRelPath($prefix,$params[0]['src']);
							$somethingDone=1;
						}
					break;
					case 'link':
					case 'a':
						$src = $params[0]['href'];
						if ($src)	{
							$params[0]['href'] = $this->prefixRelPath($prefix,$params[0]['href']);
							$somethingDone=1;
						}
					break;
						// action attribute
					case 'form':
						$src = $params[0]['action'];
						if ($src)	{
							$params[0]['action'] = $this->prefixRelPath($prefix,$params[0]['action']);
							$somethingDone=1;
						}
					break;
				}
				if ($somethingDone)	{
					$tagParts = split('[[:space:]]',$v,2);
					$tagParts[1]=$this->compileTagAttribs($params[0],$params[1]);
					$parts[$k] = '<'.trim(strtolower($firstTagName).' '.$tagParts[1]).
									$tagEnd;
				}
			}
		}
		$content = implode('',$parts);

			// Fix <style> section:
		$prefix = isset($alternatives['style']) ? $alternatives['style'] : $main_prefix;
		if (strlen($prefix))	{
			$parts = $this->splitIntoBlock('style',$content);
			foreach($parts as $k => $v)	{
				if ($k%2)	{
					$parts[$k] = eregi_replace('(url[[:space:]]*\([[:space:]]*["\']?)([^"\')]*)(["\']?[[:space:]]*\))','\1'.$prefix.'\2\3',$parts[$k]);
				}
			}
			$content = implode('',$parts);
		}

		return $content;
	}

	/**
	 * Internal sub-function for ->prefixResourcePath()
	 *
	 * @param	string		Prefix string
	 * @param	string		Relative path/URL
	 * @return	string		Output path, prefixed if no scheme in input string
	 * @access private
	 */
	function prefixRelPath($prefix,$srcVal)	{
		$pU = parse_url($srcVal);
		if (!$pU['scheme'] && substr($srcVal, 0, 1)!='/')	{ // If not an absolute URL.
			$srcVal = $prefix.$srcVal;
		}
		return $srcVal;
	}

	/**
	 * Cleans up the input $value for fonttags.
	 * If keepFace,-Size and -Color is set then font-tags with an allowed property is kept. Else deleted.
	 *
	 * @param	string		HTML content with font-tags inside to clean up.
	 * @param	boolean		If set, keep "face" attribute
	 * @param	boolean		If set, keep "size" attribute
	 * @param	boolean		If set, keep "color" attribute
	 * @return	string		Processed HTML content
	 */
	function cleanFontTags($value,$keepFace=0,$keepSize=0,$keepColor=0)	{
		$fontSplit = $this->splitIntoBlock('font',$value);	// ,1 ?? - could probably be more stable if splitTags() was used since this depends on end-tags being properly set!
		reset($fontSplit);
		while(list($k,$v)=each($fontSplit))	{
			if ($k%2)	{	// font:
				$attribArray=$this->get_tag_attributes_classic($this->getFirstTag($v));
				$newAttribs=array();
				if ($keepFace && $attribArray['face'])	$newAttribs[]='face="'.$attribArray['face'].'"';
				if ($keepSize && $attribArray['size'])	$newAttribs[]='size="'.$attribArray['size'].'"';
				if ($keepColor && $attribArray['color'])	$newAttribs[]='color="'.$attribArray['color'].'"';

				$innerContent = $this->cleanFontTags($this->removeFirstAndLastTag($v),$keepFace,$keepSize,$keepColor);
				if (count($newAttribs))	{
					$fontSplit[$k]='<font '.implode(' ',$newAttribs).'>'.$innerContent.'</font>';
				} else {
					$fontSplit[$k]=$innerContent;
				}
			}
		}
		return implode('',$fontSplit);
	}

	/**
	 * This is used to map certain tag-names into other names.
	 *
	 * @param	string		HTML content
	 * @param	array		Array with tag key=>value pairs where key is from-tag and value is to-tag
	 * @param	string		Alternative less-than char to search for (search regex string)
	 * @param	string		Alternative less-than char to replace with (replace regex string)
	 * @return	string		Processed HTML content
	 */
	function mapTags($value,$tags=array(),$ltChar='<',$ltChar2='<')	{

		foreach($tags as $from => $to)	{
			$value = eregi_replace($ltChar.$from.'>',$ltChar2.$to.'>',$value);
			$value = eregi_replace($ltChar.$from.'[[:space:]]([^>]*)>',$ltChar2.$to.' \\1>',$value);
			$value = eregi_replace($ltChar.'\/'.$from.'[^>]*>',$ltChar2.'/'.$to.'>',$value);
		}
		return $value;
	}

	/**
	 * This converts htmlspecialchar()'ed tags (from $tagList) back to real tags. Eg. '&lt;strong&gt' would be converted back to '<strong>' if found in $tagList
	 *
	 * @param	string		HTML content
	 * @param	string		Tag list, separated by comma. Lowercase!
	 * @return	string		Processed HTML content
	 */
	function unprotectTags($content,$tagList='')	{
		$tagsArray = t3lib_div::trimExplode(',',$tagList,1);
		$contentParts = explode('&lt;',$content);
		next($contentParts);	// bypass the first
		while(list($k,$tok)=each($contentParts))	{
			$firstChar = substr($tok,0,1);
			if (strcmp(trim($firstChar),''))	{
				$subparts = explode('&gt;',$tok,2);
				$tagEnd = strlen($subparts[0]);
				if (strlen($tok)!=$tagEnd)	{
					$endTag = $firstChar=='/' ? 1 : 0;
					$tagContent = substr($tok,$endTag,$tagEnd-$endTag);
					$tagParts = split('[[:space:]]',$tagContent,2);
					$tagName = strtolower($tagParts[0]);
					if (!strcmp($tagList,'') || in_array($tagName,$tagsArray))	{
						$contentParts[$k] = '<'.$subparts[0].'>'.$subparts[1];
					} else $contentParts[$k] = '&lt;'.$tok;
				} else $contentParts[$k] = '&lt;'.$tok;
			} else $contentParts[$k] = '&lt;'.$tok;
		}

		return implode('',$contentParts);
	}

	/**
	 * Strips tags except the tags in the list, $tagList
	 * OBSOLETE - use PHP function strip_tags()
	 *
	 * @param	string		Value to process
	 * @param	string		List of tags
	 * @return	string		Output value
	 * @ignore
	 */
	function stripTagsExcept($value,$tagList)	{
		$tags=t3lib_div::trimExplode(',',$tagList,1);
		$forthArr=array();
		$backArr=array();
		while(list(,$theTag)=each($tags))	{
			$forthArr[$theTag]=md5($theTag);
			$backArr[md5($theTag)]=$theTag;
		}
			$value = $this->mapTags($value,$forthArr,'<','_');
			$value=strip_tags($value);
			$value = $this->mapTags($value,$backArr,'_','<');
		return $value;
	}

	/**
	 * Internal function for case shifting of a string or whole array
	 *
	 * @param	mixed		Input string/array
	 * @param	boolean		If $str is a string AND this boolean is true, the string is returned in uppercase
	 * @param	string		Key string used for internal caching of the results. Could be an MD5 hash of the serialized version of the input $str if that is an array.
	 * @return	string		Output string, processed
	 * @access private
	 */
	function caseShift($str,$flag,$cacheKey='')	{
		if (is_array($str))	{
			if (!$cacheKey || !isset($this->caseShift_cache[$cacheKey]))	{
				reset($str);
				while(list($k)=each($str))	{
					$str[$k] = strtoupper($str[$k]);
				}
				if ($cacheKey)	$this->caseShift_cache[$cacheKey]=$str;
			} else {
				$str = $this->caseShift_cache[$cacheKey];
			}
		} elseif (!$flag)	$str = strtoupper($str);
		return $str;
	}

	/**
	 * Compiling an array with tag attributes into a string
	 *
	 * @param	array		Tag attributes
	 * @param	array		Meta information about these attributes (like if they were quoted)
	 * @param	boolean		If set, then the attribute names will be set in lower case, value quotes in double-quotes and the value will be htmlspecialchar()'ed
	 * @return	string		Imploded attributes, eg: 'attribute="value" attrib2="value2"'
	 * @access private
	 */
	function compileTagAttribs($tagAttrib,$meta=array(), $xhtmlClean=0)	{
		$accu=array();
		reset($tagAttrib);
		while(list($k,$v)=each($tagAttrib))	{
			if ($xhtmlClean)	{
				$attr=strtolower($k);
				if (strcmp($v,'') || isset($meta[$k]['dashType']))	{
					$attr.='="'.htmlspecialchars($v).'"';
				}
			} else {
				$attr=$meta[$k]['origTag']?$meta[$k]['origTag']:$k;
				if (strcmp($v,'') || isset($meta[$k]['dashType']))	{
					$dash=$meta[$k]['dashType']?$meta[$k]['dashType']:(t3lib_div::testInt($v)?'':'"');
					$attr.='='.$dash.$v.$dash;
				}
			}
			$accu[]=$attr;
		}
		return implode(' ',$accu);
	}

	/**
	 * Get tag attributes, the classic version (which had some limitations?)
	 *
	 * @param	string		The tag
	 * @param	boolean		De-htmlspecialchar flag.
	 * @return	array
	 * @access private
	 */
	function get_tag_attributes_classic($tag,$deHSC=0)	{
		$attr=$this->get_tag_attributes($tag,$deHSC);
		return is_array($attr[0])?$attr[0]:array();
	}

	/**
	 * Indents input content with $number instances of $indentChar
	 *
	 * @param	string		Content string, multiple lines.
	 * @param	integer		Number of indents
	 * @param	string		Indent character/string
	 * @return	string		Indented code (typ. HTML)
	 */
	function indentLines($content, $number=1, $indentChar="\t")	{
		$preTab = str_pad('', $number*strlen($indentChar), $indentChar);
		$lines = explode(chr(10),str_replace(chr(13),'',$content));
		while(list($k,$v) = each($lines))	{
			$lines[$k] = $preTab.$v;
		}
		return implode(chr(10), $lines);
	}

	/**
	 * Converts TSconfig into an array for the HTMLcleaner function.
	 *
	 * @param	array		TSconfig for HTMLcleaner
	 * @param	array		Array of tags to keep (?)
	 * @return	array
	 * @access private
	 */
	function HTMLparserConfig($TSconfig,$keepTags=array())	{
			// Allow tags (base list, merged with incoming array)
		$alTags = array_flip(t3lib_div::trimExplode(',',strtolower($TSconfig['allowTags']),1));
		$keepTags = array_merge($alTags,$keepTags);

			// Set config properties.
		if (is_array($TSconfig['tags.']))	{
			reset($TSconfig['tags.']);
			while(list($key,$tagC)=each($TSconfig['tags.']))	{
				if (!is_array($tagC) && $key==strtolower($key))	{
					if (!strcmp($tagC,'0'))	unset($keepTags[$key]);
					if (!strcmp($tagC,'1') && !isset($keepTags[$key]))	$keepTags[$key]=1;
				}
			}

			reset($TSconfig['tags.']);
			while(list($key,$tagC)=each($TSconfig['tags.']))	{
				if (is_array($tagC) && $key==strtolower($key))	{
					$key=substr($key,0,-1);
					if (!is_array($keepTags[$key]))	$keepTags[$key]=array();
					if (is_array($tagC['fixAttrib.']))	{
						reset($tagC['fixAttrib.']);
						while(list($atName,$atConfig)=each($tagC['fixAttrib.']))	{
							if (is_array($atConfig))	{
								$atName=substr($atName,0,-1);
								if (!is_array($keepTags[$key]['fixAttrib'][$atName]))	{
									$keepTags[$key]['fixAttrib'][$atName]=array();
								}
								$keepTags[$key]['fixAttrib'][$atName] = array_merge($keepTags[$key]['fixAttrib'][$atName],$atConfig);		// Candidate for t3lib_div::array_merge() if integer-keys will some day make trouble...
								if (strcmp($keepTags[$key]['fixAttrib'][$atName]['range'],''))	$keepTags[$key]['fixAttrib'][$atName]['range'] = t3lib_div::trimExplode(',',$keepTags[$key]['fixAttrib'][$atName]['range']);
								if (strcmp($keepTags[$key]['fixAttrib'][$atName]['list'],''))	$keepTags[$key]['fixAttrib'][$atName]['list'] = t3lib_div::trimExplode(',',$keepTags[$key]['fixAttrib'][$atName]['list']);
							}
						}
					}
					unset($tagC['fixAttrib.']);
					unset($tagC['fixAttrib']);
					$keepTags[$key] = array_merge($keepTags[$key],$tagC);			// Candidate for t3lib_div::array_merge() if integer-keys will some day make trouble...
				}
			}
		}
			// localNesting
		if ($TSconfig['localNesting'])	{
			$lN = t3lib_div::trimExplode(',',strtolower($TSconfig['localNesting']),1);
			while(list(,$tn)=each($lN))	{
				if (isset($keepTags[$tn]))	{
					$keepTags[$tn]['nesting']=1;
				}
			}
		}
		if ($TSconfig['globalNesting'])	{
			$lN = t3lib_div::trimExplode(',',strtolower($TSconfig['globalNesting']),1);
			while(list(,$tn)=each($lN))	{
				if (isset($keepTags[$tn]))	{
					if (!is_array($keepTags[$tn]))	$keepTags[$tn]=array();
					$keepTags[$tn]['nesting']='global';
				}
			}
		}
		if ($TSconfig['rmTagIfNoAttrib'])	{
			$lN = t3lib_div::trimExplode(',',strtolower($TSconfig['rmTagIfNoAttrib']),1);
			while(list(,$tn)=each($lN))	{
				if (isset($keepTags[$tn]))	{
					if (!is_array($keepTags[$tn]))	$keepTags[$tn]=array();
					$keepTags[$tn]['rmTagIfNoAttrib']=1;
				}
			}
		}
		if ($TSconfig['noAttrib'])	{
			$lN = t3lib_div::trimExplode(',',strtolower($TSconfig['noAttrib']),1);
			while(list(,$tn)=each($lN))	{
				if (isset($keepTags[$tn]))	{
					if (!is_array($keepTags[$tn]))	$keepTags[$tn]=array();
					$keepTags[$tn]['allowedAttribs']=0;
				}
			}
		}
		if ($TSconfig['removeTags'])	{
			$lN = t3lib_div::trimExplode(',',strtolower($TSconfig['removeTags']),1);
			while(list(,$tn)=each($lN))	{
				$keepTags[$tn]=array();
				$keepTags[$tn]['allowedAttribs']=0;
				$keepTags[$tn]['rmTagIfNoAttrib']=1;
			}
		}

			// Create additional configuration:
		$addConfig=array();
		if ($TSconfig['xhtml_cleaning'])	{
			$addConfig['xhtml']=1;
		}

		return array(
			$keepTags,
			''.$TSconfig['keepNonMatchedTags'],
			intval($TSconfig['htmlSpecialChars']),
			$addConfig
		);
	}

	/**
	 * Tries to convert the content to be XHTML compliant and other stuff like that.
	 * STILL EXPERIMENTAL. See comments below.
	 *
	 * 			What it does NOT do (yet) according to XHTML specs.:
	 * 			- Wellformedness: Nesting is NOT checked
	 * 			- name/id attribute issue is not observed at this point.
	 * 			- Certain nesting of elements not allowed. Most interesting, <PRE> cannot contain img, big,small,sub,sup ...
	 * 			- Wrapping scripts and style element contents in CDATA - or alternatively they should have entitites converted.
	 * 			- Setting charsets may put some special requirements on both XML declaration/ meta-http-equiv. (C.9)
	 * 			- UTF-8 encoding is in fact expected by XML!!
	 * 			- stylesheet element and attribute names are NOT converted to lowercase
	 * 			- ampersands (and entities in general I think) MUST be converted to an entity reference! (&amps;). This may mean further conversion of non-tag content before output to page. May be related to the charset issue as a whole.
	 * 			- Minimized values not allowed: Must do this: selected="selected"
	 *
	 * 			What it does at this point:
	 * 			- All tags (frame,base,meta,link + img,br,hr,area,input) is ended with "/>" - others?
	 * 			- Lowercase for elements and attributes
	 * 			- All attributes in quotes
	 * 			- Add "alt" attribute to img-tags if it's not there already.
	 *
	 * @param	string		Content to clean up
	 * @return	string		Cleaned up content returned.
	 * @access private
	 */
	function XHTML_clean($content)	{
		$content = $this->HTMLcleaner(
			$content,
			array(),	// No tags treated specially
			1,			// Keep ALL tags.
			0,			// All content is htmlspecialchar()'ed (or ??) - if we do, <script> content will break...
			array('xhtml' => 1)
		);
		return $content;
	}

	/**
	 * Processing all tags themselves
	 * (Some additions by Sacha Vorbeck)
	 *
	 * @param	string		Tag to process
	 * @param	array		Configuration array passing instructions for processing. If count()==0, function will return value unprocessed. See source code for details
	 * @param	boolean		Is endtag, then set this.
	 * @param	boolean		If set, just return value straight away
	 * @return	string		Processed value.
	 * @access private
	 */
	function processTag($value,$conf,$endTag,$protected=0)	{
			// Return immediately if protected or no parameters
		if ($protected || !count($conf))	return $value;
			// OK then, begin processing for XHTML output:
			// STILL VERY EXPERIMENTAL!!
		if ($conf['xhtml'])	{
			if ($endTag)	{	// Endtags are just set lowercase right away
				$value = strtolower($value);
			} elseif (substr($value,0,2)!='<!') {	// ... and comments are ignored.
				$inValue = substr($value,1,(substr($value,-2)=='/>'?-2:-1));	// Finding inner value with out < >
				list($tagName,$tagP)=split('[[:space:]]',$inValue,2);	// Separate attributes and tagname
				$tagName = strtolower($tagName);

					// Process attributes
				$tagAttrib = $this->get_tag_attributes($tagP);
				if (!strcmp($tagName,'img') && !isset($tagAttrib[0]['alt']))		$tagAttrib[0]['alt']='';	// Set alt attribute for all images (not XHTML though...)
				if (!strcmp($tagName,'script') && !isset($tagAttrib[0]['type']))	$tagAttrib[0]['type']='text/javascript';	// Set type attribute for all script-tags
				$outA=array();
				reset($tagAttrib[0]);
				while(list($attrib_name,$attrib_value)=each($tagAttrib[0]))	{
						// Set attributes: lowercase, always in quotes, with htmlspecialchars converted.
					$outA[]=$attrib_name.'="'.htmlspecialchars($this->bidir_htmlspecialchars($attrib_value,-1)).'"';
				}
				$newTag='<'.trim($tagName.' '.implode(' ',$outA));
					// All tags that are standalone (not wrapping, not having endtags) should be ended with '/>'
				if (t3lib_div::inList('img,br,hr,meta,link,base,area,input',$tagName) || substr($value,-2)=='/>')	{
					$newTag.=' />';
				} else {
					$newTag.='>';
				}
				$value = $newTag;
			}
		}

		return $value;
	}

	/**
	 * Processing content between tags for HTML_cleaner
	 *
	 * @param	string		The value
	 * @param	integer		Direction, either -1 or +1. 0 (zero) means no change to input value.
	 * @param	mixed		Not used, ignore.
	 * @return	string		The processed value.
	 * @access private
	 */
	function processContent($value,$dir,$conf)	{
		if ($dir!=0)	$value = $this->bidir_htmlspecialchars($value,$dir);
		return $value;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_parsehtml.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_parsehtml.php']);
}
?>
