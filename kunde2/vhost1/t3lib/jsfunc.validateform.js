/***************************************************************
*
*  Universal validate-form
*
* $Id: jsfunc.validateform.js,v 1.4 2004/04/19 15:25:53 typo3 Exp $
*
*
*
*  Copyright notice
*
*  (c) 1998-2003 Kasper Skaarhoj
*  All rights reserved
*
*  This script is part of the TYPO3 t3lib/ library provided by
*  Kasper Skaarhoj <kasper@typo3.com> together with TYPO3
*
*  Released under GNU/GPL (see license file in tslib/)
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
*  This copyright notice MUST APPEAR in all copies of this script
***************************************************************/


function validateForm(theFormname,theFieldlist,goodMess,badMess,emailMess)	{
	if (document[theFormname] && theFieldlist)	{
		var index=1;
		var theField = split(theFieldlist, ",", index);
		var msg="";
		var theEreg = '';
		var theEregMsg = '';
		var specialMode = '';

		while (theField) {
			theEreg = '';
			specialMode = '';

				// Check special modes:
			if (theField == '_EREG')	{	// EREG mode: _EREG,[error msg],[JS ereg],[fieldname],[field Label]
				specialMode = theField;

				index++;
				theEregMsg = unescape(split(theFieldlist, ",", index));
				index++;
				theEreg = unescape(split(theFieldlist, ",", index));
			} else if (theField == '_EMAIL')	{
				specialMode = theField;
			}

				// Get real field name if special mode has been set:
			if (specialMode)	{
				index++;
				theField = split(theFieldlist, ",", index);
			}

			index++;
			theLabel = unescape(split(theFieldlist, ",", index));
			theField = unescape(theField);
			if (document[theFormname][theField])	{
				var fObj = document[theFormname][theField];
				var type=fObj.type;
				if (!fObj.type)	{
					type="radio";
				}
				var value="";
				switch(type)	{
					case "text":
					case "textarea":
						value = fObj.value;
					break;
					case "select-one":
						if (fObj.selectedIndex>=0)	{
							value = fObj.options[fObj.selectedIndex].value;
						}
					break;
					case "select-multiple":
						var l=fObj.length;
						for (a=0;a<l;a++)	{
							if (fObj.options[a].selected)	{
								 value+= fObj.options[a].value;
							}
						}
					break;
					case "radio":
						var l=fObj.length;
						for (a=0; a<l;a++)	{
							if (fObj[a].checked)	{
								value = fObj[a].value;
							}
						}
					break;
					default:
						value=1;
				}

				switch(specialMode)	{
					case "_EMAIL":
						var theRegEx_notValid = new RegExp("(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)", "gi");
						var theRegEx_isValid = new RegExp("^.+\@[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})$","");
						if (!theRegEx_isValid.test(value))	{	// This part was supposed to be a part of the condition: " || theRegEx_notValid.test(value)" - but I couldn't make it work (Mozilla Firefox, linux) - Anyone knows why?
							msg+="\n"+theLabel+' ('+(emailMess ? unescape(emailMess) : 'Email address not valid!')+')';
						}
					break;
					case "_EREG":
						var theRegEx_isValid = new RegExp(theEreg,"");
						if (!theRegEx_isValid.test(value))	{
							msg+="\n"+theLabel+' ('+theEregMsg+')';
						}
					break;
					default:
						if (!value)	{
							msg+="\n"+theLabel;
						}
				}
			}
			index++;
			theField = split(theFieldlist, ",", index);
		}
		if (msg)	{
			var theBadMess = unescape(badMess);
			if (!theBadMess)	{
				theBadMess = "You must fill in these fields:";
			}
			theBadMess+="\n";
			alert(theBadMess+msg);
			return false;
		} else {
			var theGoodMess = unescape(goodMess);
			if (theGoodMess)	{
				alert(theGoodMess);
			}
			return true;
		}
	}
}
function split(theStr1, delim, index) {
	var theStr = ''+theStr1;
	var lengthOfDelim = delim.length;
	sPos = -lengthOfDelim;
	if (index<1) {index=1;}
	for (a=1; a<index; a++)	{
		sPos = theStr.indexOf(delim, sPos+lengthOfDelim);
		if (sPos==-1)	{return null;}
	}
	ePos = theStr.indexOf(delim, sPos+lengthOfDelim);
	if(ePos == -1)	{ePos = theStr.length;}
	return (theStr.substring(sPos+lengthOfDelim,ePos));
}
