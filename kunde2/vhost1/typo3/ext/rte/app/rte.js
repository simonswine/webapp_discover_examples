/***************************************************************
*
*  JavaScript Editor
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
***************************************************************/
/**
 * Original Author: Unknown
 * Typo3 Modifications:
 *		Kasper Skårhøj <kasperYYYY@typo3.com>
 *		Martin van Es <m.vanes@drecomm.nl>
 *		Andreas Otto <andreas.otto@dkd.de>, http://bugs.typo3.org/bug_view_page.php?bug_id=0000283
 *
 * TYPO3 CVS ID: $Id: rte.js,v 1.2 2004/08/13 13:13:39 a-otto Exp $
 */













 // List of emoticon gifs. Add or remove to change selection
// arEmoticons - 12x12 pixels
// arBigEmoticons - 16x16 pixels
var arEmoticons = new Array("emsmile.gif","emsad.gif","emdgust.gif","emcrook.gif","emsmiled.gif","emsmilep.gif","emsmileo.gif","emwink.gif", "emrose.gif","emlips.gif","emunlove.gif","emlove.gif","emvamp.gif","embeer.gif","emcocktl.gif","emgift.gif","ememail.gif","emthdown.gif","emthup.gif","emphone.gif","emphoto.gif","emmessag.gif","emfemale.gif","emmale.gif");
var arBigEmoticons = new Array("emarrow_right.gif","emarrow_left.gif","emmail_alert.gif","emwww_link.gif","emexclaim.gif","emhammer.gif");


// Customize Font List
// FONTNAME_TEXT - Displayed in the pop-up
// FONTNAMEDEF_TEXT - The font definition used in the HTML
var L_FONTARIAL_TEXT = "Arial";
var L_FONTARIALDEF_TEXT = "Geneva, Arial, Sans-serif";
var L_FONTARIALBLACK_TEXT = "Arial Black";
var L_FONTARIALBLACKDEF_TEXT = "Arial Black, Geneva, Arial, Sans-serif";
var L_FONTCOURIERNEW_TEXT = "Courier New";
var L_FONTCOURIERNEWDEF_TEXT = "Courier New, Courier, Monospace";
var L_FONTTIMESNEWROMAN_TEXT = "Times New Roman";
var L_FONTTIMESNEWROMANDEF_TEXT = "Times New Roman, Times, Serif";
var L_FONTVERDANA_TEXT = "Verdana";
var L_FONTVERDANADEF_TEXT = "Verdana, Geneva, Arial, Sans-serif";
var L_LUCIDAHAND_TEXT = "Lucida Handwriting";
var L_LUCIDAHANDDEF_TEXT = "Lucida Handwriting, Cursive";
var L_GARAMOND_TEXT = "Garamond";
var L_GARAMONDDEF_TEXT = "Garamond, Times, Serif";
var L_WEBDINGS_TEXT = "Webdings";
var L_WEBDINGSDEF_TEXT = "Webdings";
var L_WINGDINGS_TEXT = "Wingdings";
var L_WINGDINGSDEF_TEXT = "Wingdings";

// Add/ Remove fonts by modifying array
// _CFont(Definition, Display Text, Symbol)
// Set Symbol=true for non-alphabetic fonts to append display text in default font to the sample string
function _CFont(szDef,szText,bSymbol) {
	return new Array(szDef,szText,bSymbol);
};

defaultFonts = new Array();
defaultFonts[0] = _CFont(L_FONTARIALDEF_TEXT, L_FONTARIAL_TEXT, false);
defaultFonts[1] = _CFont(L_FONTARIALBLACKDEF_TEXT, L_FONTARIALBLACK_TEXT, false);
defaultFonts[2] = _CFont(L_FONTVERDANADEF_TEXT, L_FONTVERDANA_TEXT, false);
defaultFonts[3] = _CFont(L_FONTTIMESNEWROMANDEF_TEXT, L_FONTTIMESNEWROMAN_TEXT, false);
defaultFonts[4] = _CFont(L_GARAMONDDEF_TEXT,L_GARAMOND_TEXT, false);
defaultFonts[5] = _CFont(L_LUCIDAHANDDEF_TEXT,L_LUCIDAHAND_TEXT, false);
defaultFonts[6] = _CFont(L_FONTCOURIERNEWDEF_TEXT, L_FONTCOURIERNEW_TEXT, false);
defaultFonts[7] = _CFont(L_WEBDINGSDEF_TEXT, L_WEBDINGS_TEXT, true);
defaultFonts[8] = _CFont(L_WINGDINGSDEF_TEXT, L_WINGDINGS_TEXT, true);

// Width of each toolbar button
// Entry 5-8 are specify "Paragraph","Font Style", and "Font Size" respectively
// Update widths if localized
var L_TOOLBARGIF_TEXT = "rte_tbEN.gif";
//var aSizes = new Array(25,25,25,7,80,76,71,49,7,25,25,25,8,25,25,25,8,25,25,25,25,8,25,25,25,25,8,25,25,8,25);
var aSizes = new Array(25,25,25,8,25,25,45,41,41,8,25,25,25,8,25,25,25,8,25,25,25,25,8,25,25,25,25,8,25,25,25);







/**
 *
 * Get object functions
 *
 */


/**
 * Getting first parent block of oEl
 */
function getBlockObject(oEl) {
	var sBlocks = "|H1|H2|H3|H4|H5|H6|P|PRE|LI|TD|DIV|BLOCKQUOTE|DT|DD|TABLE|HR|IMG|"

	while ((oEl!=null) && (sBlocks.indexOf("|"+oEl.tagName+"|")==-1))	{
		oEl = oEl.parentElement;
	}
	return oEl;
}

/**
 * Getting HTML element, sTag from oEl
 */
function getElementObject(oEl,sTag) {
	while (oEl!=null && oEl.tagName!=sTag)	{
		oEl = oEl.parentElement;
	}
	return oEl;
}

/**
 * Print properties of an object
 */
function debugObj(obj,name)	{
	var acc;
	for (i in obj) {
		if (obj[i])	{
			acc+=i+":  "+obj[i]+"\n";
		}
	}
	alert("Object: "+name+"\n\n"+acc);
}


/**
 *
 * "Input/Output" functions
 *
 */

// Set HTML content in the editor. bMode determines if the raw HTML-code is displayed or the formatted code is (the little checkbox beneath the editor)
function setHTML(szHTML,noReset) {
	if (theEditor.bMode) {
		idEditbox.document.body.innerHTML = szHTML;
	} else {
		idEditbox.document.body.innerText = szHTML;
	}
	if (!noReset)	editor_resetCursor(true);
}

// Get HTML content from editor
function getHTML() {
	var szRet = (theEditor.bMode ? idEditbox.document.body.innerHTML : idEditbox.document.body.innerText);
	return szRet;
}

// Get plain text content from editor
function getText() {
	var szRet = "";
	if (theEditor.bMode) {
		szRet = idEditbox.document.body.innerText;
	} else {
		setMode(true);
		szRet = idEditbox.document.body.innerText;
		setMode(false);
	}
	return szRet;
}

// Returns the body of the editor as an object
function getBody() {
	var oRet = idEditbox.document.body;
	return oRet;
}
function getWidth() {
	var nRet = document.body.offsetWidth;
	return nRet;
}
function getHeight() {
	var nRet = document.body.offsetHeight;
	return nRet;
}

// Inserts HTML content substituting the selection or at the cursor point
function insertHTML(szHTML) {
	var sType;
	var sel = theEditor.GetSelection();
	sType = sel.type;
	if (theEditor.bMode) {
		if (sType=="Control") {
			sel.item(0).outerHTML = szHTML;
		} else {
			sel.pasteHTML(szHTML);
		}
	} else {
		sel.text = szHTML
	}
}

// Set focus to the editor window
function setFocus() {
	idEditbox.focus();
}

// Inserts HTML content at the end of the existing code.
function appendHTML(szHTML) {
	if (theEditor.bMode)  {
		idEditbox.document.body.insertAdjacentHTML("beforeEnd",szHTML);
	} else {
		idEditbox.document.body.insertAdjacentText("beforeEnd",szHTML);
	}
}

/**
 * Setting background color of editor body - we DON'T set that, so it's commented.
 */
function setBGColor(szValue) {
	theEditor.bgColor = szValue;
	if (theEditor.bMode) {
//		idEditbox.document.body.bgColor = theEditor.bgColor;
	}
}

/**
 * Getting current background color
 */
function getBGColor() {
	var szRet = theEditor.bgColor;
	return szRet;
}

/**
 * Sets the stylesheet of the editor
 */
function setDefaultStyle(szValue) {
	theEditor.css = szValue;
	if (theEditor.bMode) {
		idEditbox.document.body.style.cssText = theEditor.css;
	}
}

/**
 * Gets the stylesheet of the editor as an object
 */
function getDefaultStyle() {
	var oRet = theEditor.css;
	return oRet;
}

// Set editor skin
function setSkin(szSkin) {
	if (szSkin == null) {
		document.styleSheets.skin.cssText = theEditor.defaultSkin;
	} else {
		document.styleSheets.skin.cssText = szSkin;
	}
	document.styleSheets.skin.disabled = false;
}

// Sets the toolbar buttons
function setToolbar(id,theEditor) {
	var el = document.all[id];
	if (el) {
		el.style.display = (theEditor) ? "" : "none";
	}
	if (id=="tbmode") {
		setSizes();
	}
}

// Setting the mode: Either edit raw HTML or design mode.
function setMode(bMode) {
	if (bMode!=theEditor.bMode) {
		theEditor.bMode = bMode;
		var objBody = idEditbox.document.body;
		if (!bMode&& !theEditor.bMode) {
			edHidePopup();
			objBody.bgColor = objBody.style.cssText = "";
			if (theEditor.customButtons) {
				idStandardBar.style.display = "none";
			} else {
				idToolbar.style.display = "none";
			}
			objBody.innerText = idEditbox.document.body.innerHTML;
			objBody.className = "textMode";
		}
		if ((bMode) && (theEditor.bMode)) {
			setDefaultStyle(theEditor.css);
			setBGColor(theEditor.bgColor);
			objBody.className = idStandardBar.style.display = idToolbar.style.display = "";
			objBody.innerHTML = idEditbox.document.body.innerText;
		}
		setSizes();
		cbMode.checked = !bMode;
		editor_resetCursor(true);
		setFocus();
	}
	return bMode;
}













/**
 * Initializing the editor:
 * Practically, this writes out the BODY of the HTML-document of the editor and in addition the styles for the editor is set.
 * This is the place to add any CSS information used by the editor itself.
 */
function initEditor() {
	theEditor = new editorObj();
	window.onresize = setSizes;

	var sz="";
	sz+="<STYLE>\n"
//	+ ".DataBound{border:1 solid #999999;margin:1;font-family:Courier;background:#F1F1F1}\n"
		// Style for the HTML-source mode (bMode)
	+ ".textMode {border-top: 1px black solid;font: 12px monospace;}\n"	// 	white-space : nowrap;
		// Style for tables without borders (so they are visible)
	+ ".NOBORDER TD {border:1px gray dotted}\n"
	+ "\n"+inlineStyle+"\n";

	if (classes_style)	{
		for (var i=0; i < classes_style.length; i++)	{
			sz += classes_style[i]+"\n";
		}
	}

	sz += '</STYLE><BODY'+(conf_enableRightClick?'':' ONCONTEXTMENU="return false;"')+'>'
	+"<DIV></DIV>"
	+"</BODY>";
//	alert(sz);

	edInitPopup();
	var d = idEditbox.document;
	d.designMode = "on";
	d = idEditbox.document;
	d.open("text/html","replace");
	d.write(sz);
	d.close();
	d.body.onblur = theEditor.SaveSelection;
	d.onkeydown = editor_keyDownHandler;
	d.onmousedown = editor_clickHandler;
	d.ondblclick = editor_dblClickHandler;
	setTimeout("pageReady()",0);
}


/**
 * Starting the editor
 * This function is called by initEditor with a setTimeout() function
 */
function pageReady() {
	if (!conf_enableRightClick)	{
		idEditbox.document.body.oncontextmenu = new Function("return false");
	} else idEditbox.document.body.oncontextmenu = new Function("return rightClick()");
	if (theEditor.szSearch!="")	{
		idPopup.document.domain = idEditbox.document.domain = document.domain = theEditor.szSearch;
	}
	//editor_resetCursor(false)
	setButtons();	// Setting the buttons.
	if (self.parent.RTELoaded)	{
		self.parent.RTELoaded(self);
	}
	if (document.styleSheets.skin.disabled) {
		alert();
		setSkin(null);
	}
	setSizes();
	idEditor.style.visibility="";
	tbmode.style.background = theBackGroundColor;
	spitItIn();		// This inserts the Typo3 content into the editor!
	if (!getHTML())	{setHTML("<DIV>&nbsp;</DIV>");}
}

function rightClick()	{
//	var oSel	= theEditor.GetSelection();
//if (oSel.type=="None")	debugObj(oSel);
	theEditor.SaveSelectionPopup();

	showPopUpMenu('copycutpaste');
	return false;
}

// Sets the cursor to the top position of the text
function editor_resetCursor(bDir) {
	var tr = idEditbox.document.body.createTextRange();
	tr.collapse(bDir);
	tr.select();
}

// Clickhandler, cancels a selection
function editor_clickHandler() {
	theEditor.selection = null;
}

// Key-down handler. Saves selection if Tab is pressed
function editor_keyDownHandler() {
	var ev = this.parentWindow.event;
	if (ev.keyCode==9) {	// Tab...
		theEditor.SaveSelection();
	} else {
		theEditor.selection=null;
	}
}

// Handles double-clicks
// In this case images are restored to their original size and tables pop-up menu is shown
function editor_dblClickHandler() {
	// Shortcuts
	var el = this.parentWindow.event.srcElement;
	if (el.tagName=="IMG")  {
		el.removeAttribute("width");
		el.removeAttribute("height");
		el.style.removeAttribute("width");
		el.style.removeAttribute("height");
		el.width = el.width;
		el.height = el.height;
	}
	if (el.tagName=="TABLE") {
		showPopUpMenu('Table');
	}
}

// Resets the sizes of the edit-box and pop-up window
function setSizes() {
	document.all.idEditbox.style.pixelHeight = document.body.clientHeight - idToolbar.offsetHeight - document.all.idMode.offsetHeight;
	document.all.idPopup.style.pixelLeft = (document.body.clientWidth - idPopup.document.all.puRegion.offsetWidth) / 2;
}







/**
 * Drawing the tool bar
 */
function drawToolbar(){
	var aIds = new Array("cut","copy","paste","bar1","formatblock","class","fontstyle","fontsize","textcolor","bar2","bold","italic","underline","bar3","left","center","right","bar4","orderedlist","unorderedlist","outdent","indent","bar5","link","table","bgcolor","image","bar6","emoticon","line","user");
	var aTips = new Array(L_TIPCUT_TEXT,L_TIPCOPY_TEXT,L_TIPPASTE_TEXT,"",L_TIPP_TEXT,L_TIPCLASS_TEXT,L_TIPFSTYLE_TEXT,L_TIPFSIZE_TEXT,L_TIPFGCOLOR_TEXT,"",L_TIPB_TEXT,L_TIPI_TEXT,L_TIPU_TEXT,"",L_TIPLJ_TEXT,L_TIPCJ_TEXT,L_TIPRJ_TEXT,"",L_TIPOL_TEXT,L_TIPUL_TEXT,L_TIPDINDENT_TEXT,L_TIPIINDENT_TEXT,"",L_TIPLINK_TEXT,L_TIPTABLE_TEXT,L_TIPBGCOLOR_TEXT,L_TIPPICTURE_TEXT,"",L_TIPEMOTICON_TEXT,L_TIPLINE_TEXT,L_TIPUSER_TEXT);
	var aCommand = new Array("formatSelection('cut')","formatSelection('copy')","formatSelection('paste')",null,"showPopUpMenu('formatblock')","showPopUpMenu('class')","showPopUpMenu('font')","showPopUpMenu('fontsize')","showPopUpMenu('ForeColor')",null,"formatSelection('bold')","formatSelection('italic')","formatSelection('underline')",null,"formatSelection('Justify','Left')","formatSelection('Justify','Center')","formatSelection('Justify','Right')",null,"formatSelection('insertorderedlist')","formatSelection('insertunorderedlist')","formatSelection('outdent')","formatSelection('indent')",null,"showPopUpMenu('Link')","showPopUpMenu('Table')","showPopUpMenu('BackColor')","showPopUpMenu('Image')",null,"showPopUpMenu('Emoticon')","formatSelection('InsertHorizontalRule')","showPopUpMenu('User')");
	var sz = "<DIV ID=idStandardBar><NOBR>", iLeft=0, iHeight=24
	for (var i = 0 ; i < aSizes.length; i++) {
		sz	+=  ''
		+'<SPAN CLASS=tbButton ONKEYPRESS="if (event.keyCode==13) {'+aCommand[i]+';event.keyCode=0}" '+(aTips[i]=='' ? '' : ('TABINDEX=' + (i+1)))
		+' ID="tb'+aIds[i]+'" STYLE="width: ' + aSizes[i] + ';height:'+iHeight+'">'
		+'<SPAN STYLE="position:absolute;width:' + aSizes[i] + ';height:' + iHeight + ';clip: rect(0 ' + aSizes[i] + ' ' + iHeight + ' 0)">'
		+'<IMG TITLE="' + aTips[i] + '" ONCLICK="' + aCommand[i] + '; event.cancelBubble=true" ONMOUSEDOWN="if (event.button==1) this.style.pixelTop=-' + (iHeight*2) + '" ONMOUSEOVER="this.style.pixelTop=-' + iHeight + '" ONMOUSEOUT="this.style.pixelTop=0" ONMOUSEUP="this.style.pixelTop=-' + iHeight + '" SRC="' + L_TOOLBARGIF_TEXT + '" STYLE="position:absolute;top:0;left:-' + iLeft + '">'
		+'</SPAN></SPAN>'
		+ (aTips[i]=='' ?  '</NOBR><NOBR>' : '')
		iLeft += aSizes[i]
	}
	sz += '</NOBR>';
	sz += '</DIV>';
	document.write(sz);
}

/**
 * Drawing the HTML/Design mode checkbox
 * The printIt parameter is a boolean, if set to 1, the checkbox is printed. Else not.
 */
function drawModeSelect(printIt) {
	if (printIt)	{
		var sz = '<TABLE CELLSPACING=0 CELLPADDING=0 ID=idMode>'
		+	'<TR><TD><INPUT TYPE=checkbox ID=cbMode ONCLICK="setMode(!this.checked);"></TD>'
		+   '<TD><LABEL FOR=cbMode>' + L_MODETITLE_TEXT + '</LABEL>'
		+   '</TD></TR></TABLE>';
		document.write(sz);
		cbMode.checked = false;
	} else {
		var sz = '<TABLE CELLSPACING=0 CELLPADDING=0 ID=idMode><TR><TD></TD><TD></TD></TR></TABLE>';
		document.write(sz);
	}
}







/**********************************
 *
 * Editor Functions
 *
 *********************************/
// Editor object
function editorObj(){
	this.selection		= null;
	this.bMode			= true;
	this.customButtons 	= false;
	this.css = this.bgColor	= "";
	this.defaultSkin	= document.styleSheets.skin.cssText;
	this.popupSkin		= document.styleSheets.popupSkin.cssText;
	this.aLinks			= new Array();
	this.szSearch		= "";	// location.search.substring(1);
	this.aBindings		= new Array();
	this.aListPopups	= new Object();
	this.aCache			= new Object();

	this.RestoreSelection	= editorObj_RestoreSelection;
	this.GetSelection	= editorObj_GetSelection;
	this.SaveSelection	= editorObj_SaveSelection;
	this.SaveSelectionPopup	= editorObj_SaveSelectionPopup;
}

// Restoring selection of the window
function editorObj_RestoreSelection() {
	if (this.selection) {
		this.selection.select();
		this.selection=null;	// Kasper added 090502
	}
}

// Getting current selection of the window
function editorObj_GetSelection() {
	var oSel = this.selection
	if (!oSel) {
		oSel = idEditbox.document.selection.createRange();
		oSel.type = idEditbox.document.selection.type;
	}
	return oSel;
}

// Saving selection of the window
function editorObj_SaveSelection() {
	theEditor.selection = idEditbox.document.selection.createRange();
	theEditor.selection.type = idEditbox.document.selection.type;
}

// Saving selection of the window
function editorObj_SaveSelectionPopup() {
	theEditor.selection_popup = idEditbox.document.selection.createRange();
	theEditor.selection_popup.type = idEditbox.document.selection.type;
}

// Formatting a selection
function formatSelection(szHow, szValue, className) {
	var oSel	= theEditor.GetSelection();
	var sType   = oSel.type;
	var oTarget = (sType == "None" ? idEditbox.document : oSel);
	var oBlock  = (oSel.parentElement != null ? getBlockObject(oSel.parentElement()) : oSel.item(0));
	setFocus();
	switch(szHow)	{
		case "BackColor":	// Setting background color of the table cell, row or table or document background (which is not enabled at this point)
			var el = null;
			if (oSel.parentElement != null) {
				el =  getElementObject(oSel.parentElement(),"TD");
				if (!el) el =  getElementObject(oSel.parentElement(),"TH");
				if (!el) el =  getElementObject(oSel.parentElement(),"TR");
				if (!el) el =  getElementObject(oSel.parentElement(),"TABLE");
			} else {
				el = getElementObject(oSel.item(0),"TABLE");
			}
			if (el) {
				if (szValue)	{	// Set:
					el.bgColor = szValue;
				} else {	// Remove:
					el.bgColor="";
//					el.removeAttribute("bgcolor");
//					el.style.removeAttribute("bgcolor");
				}
			} else {
				setBGColor(szValue);
			}
		break;
		case "Justify":
			if (oBlock) {
				oBlock.style.textAlign = "";
				if (((oBlock.tagName=="TABLE") || (oBlock.tagName=="IMG")) && (("left"==oBlock.align) && ("Left"==szValue))) {		// This is setting the align-ment to nothing ONLY if the align-ment is already "left"!
					oBlock.align = "";
					break;
				}
				oBlock.align = szValue;
	//			if ((oBlock.tagName=="HR") || ((oBlock.tagName=="IMG") && szValue!="Center")) break;
			}
//			szHow=szHow+szValue;
//			szValue="";
			// Fall through
		break;
		case "SetClass":
			if (oSel.type != "Text")	{	// If no text is selected, find the first parent element
				if (oSel.parentElement != null)  	{
					if (className=="_remove_formatting")	{
						if (oSel.parentElement().tagName!="BODY")	{
							oSel.parentElement().outerHTML=oSel.parentElement().outerText;
						}
					} else {
						oSel.parentElement().className=className;
					}
				} else {
					oSel.item(0).className=className;
				}
			} else {
				if (className=="_remove_formatting")	{
					if (oSel.parentElement() && oSel.parentElement().tagName!="BODY")	{
						oSel.parentElement().outerHTML=oSel.parentElement().outerText;
					}
				} else {
					oSel.pasteHTML("<span class="+className+">"+oSel.htmlText+"</span>");
				}
			}
		break;
		case "FormatBlock":
			if (oBlock) {
				if (className)	{
					oBlock.className=className;
				} else {
					oBlock.className="";
				}
				oTarget.execCommand(szHow, false, szValue);
			}
		break;
		default:
			oTarget.execCommand(szHow, false, szValue);
		break
	}
	theEditor.RestoreSelection();
	setFocus();
	return true;
}












/*****************************************'
 *
 *  POP-UP functions
 *
 ******************************************/
// Write the pop-up basis. Called during initialization
function edInitPopup() {
	var sz='<HTML ID=popup><STYLE>'
	+ document.styleSheets.defPopupSkin.cssText+"\n"
	+ document.styleSheets.popupSkin.cssText+"</STYLE>"
	+ '<BODY ONSCROLL="return false" SCROLL=no TABINDEX=-1 ONSELECTSTART="return event.srcElement.tagName==\'INPUT\'"><DIV ID=puRegion>'
	+ '<TABLE ID=header><TR><TH NOWRAP ID=caption></TH><TH VALIGN=middle ALIGN=RIGHT>'
//	+ '<DIV ID=close ONCLICK="parent.edHidePopup()">'
//	+ L_CLOSEBUTTON_TEXT
//	+ '</DIV>'
	+ '<a href="#" ONCLICK="parent.edHidePopup();return false;"><img src="'+BACK_PATH+'gfx/close.gif" width="11" height="10" hspace=4 border="0"></a>'
	+ '</TH></TR></TABLE>'
	+ '<DIV ALIGN=CENTER ID=content></DIV>'
	+ '</DIV></BODY></HTML>';

	idPopup.document.open("text/html","replace");
	idPopup.document.write(sz);
	idPopup.document.close();
}

/**
 * Hiding the popup
 */
function edHidePopup(noFocus) {
	document.all.idPopup.style.zIndex=-1;
	document.all.idPopup.style.visibility = "hidden";
	idPopup.document._theType = "";
   	idPopup.document.onkeydown=idPopup.document.onmouseover=idPopup.document.onclick = null;

	theEditor.RestoreSelection();
	if (!noFocus)	setFocus();
}

/**
 * Rendering popup. These are cached so they need not load the next time.
 */
function showPopUpMenu(theType) {
	var oRenderer;
	var szCacheKey = "PopupRenderer." + theType;

   	if (idPopup.document._theType == theType)	{	// If this popup is already shown, then hide it!
		edHidePopup();
	} else {	// Else show the popup
		document.all.idPopup.style.zIndex = -1;
		oRenderer = theEditor.aCache[szCacheKey];	// Getting from cache
		if ((!oRenderer) || isInCommaList("Link,class,Image,User",theType))	{	// Make sure certain types of pop-ups are NOT cached!
			theEditor.aCache[szCacheKey] = oRenderer = new renderPopup(theType);
		}
		// Force Sizing
		document.all.idPopup.style.visibility = ""
		idPopup.document.all.puRegion.style.pixelHeight = idPopup.document.all.puRegion.style.pixelWidth = 100

		idPopup.document._theType					= theType
		idPopup.document._renderer				= oRenderer
		idPopup.document.all.caption.innerText	= oRenderer.GetCaption()
		idPopup.document.all.content.innerHTML	= oRenderer.GetHTML()
		idPopup.document.onkeydown				= new Function("this._renderer.OnKeyDown()")
		idPopup.document.onmouseover			= new Function("this._renderer.OnMouseOver()")
		idPopup.document.onclick				= new Function("this._renderer.OnClick()")
		oRenderer.ResetContext(idPopup.document);
		setTimeout("renderPopup_Display('" + theType + "')",0)
	}
}

/**
 * Showing popup. This is done by a setTimeout-function (see above)
 */
function renderPopup_Display(theType) {
	var szCacheKey = "PopupRenderer." + theType;
	var oRenderer = theEditor.aCache[szCacheKey];
		// Setting position and size:
	if (oRenderer.autoSize) {
		idPopup.document.all.puRegion.style.pixelHeight = document.all.idPopup.style.pixelHeight = idPopup.document.all.puRegion.offsetHeight;
		idPopup.document.all.puRegion.style.pixelWidth = document.all.idPopup.style.pixelWidth = idPopup.document.all.puRegion.offsetWidth + 50;
		document.all.idPopup.style.pixelLeft = (document.body.clientWidth - idPopup.document.all.puRegion.offsetWidth) / 2;
	} else {
		idPopup.document.all.puRegion.style.pixelHeight  = document.all.idPopup.style.pixelHeight = document.body.clientHeight - idToolbar.offsetHeight- document.all.idMode.offsetHeight-20;
		idPopup.document.all.puRegion.style.pixelWidth  = document.all.idPopup.style.pixelWidth = document.body.clientWidth - 50;
		document.all.idPopup.style.pixelLeft = 25;
	}
	document.all.idPopup.style.zIndex=2;
	idPopup.focus();
}

/**
 * This makes the HTML code for the pop-up menus on request
 */
function renderPopup(theType){
	this.theType		=  theType;
	this.elCurrent	=  this.oDocument  = null;

	this.ResetContext   =  renderPopup_ResetContext;
	this.GetCaption	= renderPopup_GetCaption;
	this.GetHTML	= renderPopup_GetHTML;
	this.autoSize	= true;
	this.OnMouseOver = new Function();
	this.OnKeyDown	= renderPopup_genericOnKeyDown;
	switch(theType) {
		case "formatblock":
		case "class":
		case "font":
		case "fontsize":
			this.OnKeyDown  = renderPopup_onKeyDown;
		case "copycutpaste":
			this.OnMouseOver= renderPopup_onMouseOver;
		case "BackColor":
		case "ForeColor":
			this.OnClick	= renderPopup_onClick;
			this.Highlight  = renderPopup_highlight;
			this.Select		= renderPopup_select;
		break;
		default:
			this.OnClick	= new Function();
		break;
	}
	switch(theType)	{
		case "copycutpaste":
			this.szCaption		= L_OPTIONS;
			this.PrepareHTML	= renderPopup_copyCutPaste;
			this.szHTML			= this.PrepareHTML();
		break;
		case "class":
			this.szCaption		= L_PUTITLECLASS_TEXT;
			this.PrepareHTML	= renderPopup_characterStyleRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		case "formatblock":
			this.szCaption		= L_PUTITLEPARAGRAPHSTYLE_TEXT;
			this.PrepareHTML	= renderPopup_paragraphStyleRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		case "font":
			this.szCaption		= L_PUTITLEFONTFACE_TEXT;
			this.PrepareHTML	= renderPopup_fontFaceRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		case "fontsize":
			this.szCaption		= L_PUTITLEFONTSIZE_TEXT;
			this.PrepareHTML	= renderPopup_fontSizeRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		case "Link":
			this.szCaption		= L_PUTITLELINK_TEXT;
			this.PrepareHTML	= renderPopup_linkRenderer;
			this.szHTML			= this.PrepareHTML();
   			this.autoSize		= false;
		break;
		case "Table":
			this.szCaption		= L_PUTITLENEWTABLE_TEXT;
			this.PrepareHTML	= renderPopup_tableRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		case "Image":
			this.szCaption		= L_PUTITLEIMAGE_TEXT;
			this.PrepareHTML	= renderPopup_image;
			this.szHTML			= this.PrepareHTML();
   			this.autoSize		= false;
		break;
		case "User":
			this.szCaption		= L_PUTITLEUSER_TEXT;
			this.PrepareHTML	= renderPopup_user;
			this.szHTML			= this.PrepareHTML();
   			this.autoSize		= false;
		break;
		case "BackColor":
			this.szCaption		= L_PUTITLEBGCOLOR_TEXT;
			this.szHTML			= "<DIV ID=ColorPopup ALIGN=CENTER><BR>" + renderPopup_colorTable("") + "</DIV>";
		break;
		case "ForeColor":
			this.szCaption		= L_PUTITLETEXTCOLOR_TEXT;
			this.szHTML			= "<DIV ID=ColorPopup ALIGN=CENTER><BR>" + renderPopup_colorTable("") + "</DIV>";
		break;
		case "Emoticon":
			this.szCaption		= L_PUTITLEEMOTICON_TEXT;
			this.PrepareHTML	= renderPopup_emoticonRenderer;
			this.szHTML			= this.PrepareHTML();
		break;
		default:
			this.szCaption		= "";
		break;
	}
}

function renderPopup_ResetContext(oDoc) {
	this.oDocument  = oDoc;
	this.elCurrent  = null;

	if (this.theType=="Table") {
		var oSel	= idEditbox.document.selection.createRange();
		var oBlock  = (oSel.parentElement != null ? getElementObject(oSel.parentElement(),"TABLE") : getElementObject(oSel.item(0),"TABLE"))
		if (oBlock!=null) {
			oDoc.all.tabEdit.className=""
			oDoc.all.tabEditBodytxtPadding.value = oBlock.cellPadding
			oDoc.all.tabEditBodytxtSpacing.value = oBlock.cellSpacing
			oDoc.all.tabEditBodytxtBorder.value = oBlock.border
			oDoc.all.tabEditBodytxtBorderColor.value = oBlock.borderColor
			oDoc.all.tabEditBodytxtBackgroundImage.value = oBlock.background
			oDoc.all.tabEditBodytxtBackgroundColor.value = oBlock.bgColor
		}
		oDoc.elCurrent = oBlock
	}
}
function renderPopup_GetCaption(){
	return this.szCaption
}
function renderPopup_GetHTML(){
	return this.szHTML
}




/**
 * Various handlers for the pop-up menus
 */
function renderPopup_onClick() {
	var elTD = getElementObject(this.oDocument.parentWindow.event.srcElement, "TD")
	if (elTD && elTD._item)	{
		this.Select(elTD);
	} else {
		var elTR = getElementObject(this.oDocument.parentWindow.event.srcElement, "TR")
		if (elTR && elTR._item)	{
			this.Select(elTR);
		}
	}
}
function renderPopup_genericOnKeyDown() {
	var ev		= this.oDocument.parentWindow.event
	if (ev.keyCode==27) edHidePopup();
}
function renderPopup_onKeyDown() {
	var el
	var iRow = iCell	= 0
	var ev		= this.oDocument.parentWindow.event
	var idList  = this.oDocument.all.idList
	var elTR	= getElementObject(this.elCurrent,"TR")
	var elTD	= getElementObject(this.elCurrent,"TD")


	if (elTR != null) 	{
		iRow	= elTR.rowIndex
		iCell   = elTD.cellIndex
	}

	switch (ev.keyCode) 	{
		case 37:
			iCell--
			if (iCell < 0)
				iCell = idList.rows[iRow].cells.length-1
			break
		case 38:
			iRow--
			if (iRow < 0)
				iRow = idList.rows.length-1
			break
		case 39:
			iCell++
			if (iCell > idList.rows[iRow].cells.length-1)
				iCell = 0
			break
		case 40:
			iRow++
			if (iRow > idList.rows.length-1)
				iRow = 0
			break
		case 13:
			break;
		case 27:
			edHidePopup();
			break;
		default:
			return;
	}

	el = idList.rows[iRow].cells[iCell]
	if (el && el._item)
		if (13 == ev.keyCode) {
			ev.keyCode=0
			this.Select(el)
		} else
			this.Highlight(el)
}
function renderPopup_onMouseOver() {
	var el = getElementObject(this.oDocument.parentWindow.event.srcElement, "TD");
	if (el && el._item && el != this.elCurrent)	{
		this.Highlight(el);
	} else {
		el = getElementObject(this.oDocument.parentWindow.event.srcElement, "TR");
		if (el && el._item && el != this.elCurrent)	{
			this.Highlight(el);
		}
	}
}
function renderPopup_highlight(el) {
	var elC = this.elCurrent;
	if (elC) {
		elC.style.borderWidth = elC.style.borderColor = elC.style.borderStyle = elC.style.background = "";
	}
//	el.style.borderWidth	=   "1px";
//	el.style.borderColor	=   "green";
//	el.style.borderStyle	=   "solid";
	el.style.background		=   "#D2CFD0";

	this.elCurrent			=   el;
}
function renderPopup_select(elTD) {
	theEditor.RestoreSelection();

	var el = elTD.children[0];
	switch (this.theType) 	{
		case "font":
			formatSelection("FontName",el.face);
		break
		case "fontsize":
			formatSelection("FontSize",el.size);
		break
		case "formatblock":
			var theTag = '<'+el.tagName+'>';
			var theClassName = "";
			if (el.className)	{
				theClassName = el.className.substr(7);
			}

			formatSelection("FormatBlock",theTag,theClassName);
		break
		case "class":
			var theClassName = el.className.substr(7);
			if (el._remove)	theClassName="_remove_formatting";
			formatSelection("SetClass","",theClassName);
		break
		case "ForeColor":
			formatSelection("ForeColor", elTD.bgColor);
		break
		case "BackColor":
			formatSelection("BackColor",elTD.bgColor);
		break
		case "copycutpaste":
			theEditor.selection = theEditor.selection_popup;
			theEditor.selection_popup="";
			theEditor.RestoreSelection();

			switch(elTD._item)	{
				case "copy":
				case "cut":
				case "paste":
				case "bold":
				case "italic":
				case "underline":
					formatSelection(elTD._item);
				break;
			}
		break
	}
	edHidePopup();
}























/**
 * Copy-Cut-Paste dialog
 */
function renderPopup_copyCutPaste()	{
	var sz;

		// Find the oSel type
	var oSel	= theEditor.GetSelection();

	var sz='<TABLE ALIGN=center ID="idListTR" CELLSPACING=0 CELLPADDING=0 BORDER=0 style="font-family : Verdana, Arial, Helvetica, sans-serif; font-size : 10;">';
	sz+= '<TR><TD></TD><TD><img src=clear.gif width=100 height=5></TD><TD></TD></TR>';

	var divider = '<TR><TD colspan=3 align=center><img src=rte_div.gif height=2 vspace=4 width="100%"></TD></TR>';
	var iconSpace = ' hspace=4 vspace=2';

		// Distance
	sz+= '<TR _item="cut"><TD align=center><img src="rte_cut.gif" width="9" height="14" border="0"'+iconSpace+'></TD><TD>'+L_TIPCUT_TEXT2+'</TD><TD>Ctrl+X</TD></TR>';
	sz+= '<TR _item="copy"><TD align=center><img src="rte_copy.gif" width="15" height="13" border="0"'+iconSpace+'></TD><TD>'+L_TIPCOPY_TEXT2+'</TD><TD>Ctrl+C</TD></TR>';
	sz+= '<TR _item="paste"><TD align=center><img src="rte_paste.gif" width="16" height="14" border="0"'+iconSpace+'></TD><TD>'+L_TIPPASTE_TEXT2+'</TD><TD>Ctrl+V</TD></TR>';
	sz+= divider;
	sz+= '<TR _item="bold"><TD align=center><img src="rte_bold.gif" width="9" height="14" border="0"'+iconSpace+'></TD><TD>'+L_TIPB_TEXT+'</TD><TD>Ctrl+B</TD></TR>';
	sz+= '<TR _item="italic"><TD align=center><img src="rte_italics.gif" width="9" height="14" border="0"'+iconSpace+'></TD><TD>'+L_TIPI_TEXT+'</TD><TD>Ctrl+I</TD></TR>';
	sz+= '<TR _item="underline"><TD align=center><img src="rte_under.gif" width="9" height="14" border="0"'+iconSpace+'></TD><TD>'+L_TIPU_TEXT+'</TD><TD>Ctrl+U</TD></TR>';

		// End:
	sz+='</TABLE>';
	return sz;
}

/**
 * Renders the symbol (emoticon) pop-up
 */
function renderPopup_emoticonRenderer() {
	var sz = "<DIV ALIGN=center>"
	for (var i=0; i < arEmoticons.length; i++) {
		sz +=  '<IMG TABINDEX='+(i+1)+' ONCLICK="parent.renderPopup_selectEmoticon(this);" CLASS=emoticon SRC="'+L_EMOTICONPATH_TEXT+arEmoticons[i]+'" WIDTH=12 HEIGHT=12 HSPACE=3 VSPACE=3>';
		if ((i+1)%8==0) sz+="<BR>";
	}
	if (i%8!=0) sz+="<BR>";
	for (var i=0; i < arBigEmoticons.length; i++) {
		sz += '<IMG TABINDEX='+(i+arEmoticons.length)+' ONCLICK="parent.renderPopup_selectEmoticon(this);" CLASS=emoticon SRC="'+L_EMOTICONPATH_TEXT+arBigEmoticons[i]+'" WIDTH=16 HEIGHT=16 HSPACE=4 VSPACE=4>';
		if ((i+1)%6==0) sz+="<BR>";
	}
	sz+="</DIV>";
	return sz;
}
function renderPopup_selectEmoticon(elImg) {
	insertHTML('<IMG SRC="'+elImg.src+'" WIDTH='+elImg.width+' HEIGHT='+elImg.height+'>');
	theEditor.RestoreSelection();
	edHidePopup();
}

/**
 * Font size pop-up
 */
function renderPopup_fontSizeRenderer(){
	var sz  =  '<TABLE ALIGN=center ID=idList CELLSPACING=0 CELLPADDING=0 style="'+conf_fontSizeStyle+'">';
	for (var i=1; i <= 7; i++) 	{
		if (conf_hideFontSizes!="*" && !isInCommaList(conf_hideFontSizes,i))	{
			sz+='<TR><TD NOWRAP _item='+i+' ALIGN=center STYLE="margin:0pt;padding:0pt">';
			sz+='<FONT SIZE='+i+'>';
			sz+=L_STYLESAMPLE_TEXT+' ('+i+')';
			sz+='</FONT>';
			sz+='</TD></TR>';
		}
	}
	sz+='</TABLE>';
	return sz;
}

/**
 * Font face pop-up
 */
function renderPopup_fontFaceRenderer(){
	var sz  =  '<TABLE ALIGN=center ID=idList CELLSPACING=0 CELLPADDING=0>';
		// Default fonts
	for (var i=0; i < defaultFonts.length; i++) 	{
		if (conf_hideFontFaces!="*" && !isInCommaList(conf_hideFontFaces,i+1))	{
			sz+='<TR><TD NOWRAP _item='+i+' ALIGN=center STYLE="margin:0pt;padding:0pt"><FONT FACE="'+defaultFonts[i][0]+'">'+defaultFonts[i][1]+'</FONT>'
			+ (defaultFonts[i][2] ? ('(' + defaultFonts[i][1] + ')') : '')+'</TD></TR>';
		}
	}

		// Inserting extra fonts
	if (conf_fontFace)	{
		var index=1;
		var theFont = split(conf_fontFace, ",", index);
		i=defaultFonts.length;
		while (theFont) {
			if (fonts_label[theFont])	{
				sz+='<TR><TD NOWRAP _item='+i+' ALIGN=center STYLE="margin:0pt;padding:0pt">'
				+ '<FONT FACE="'+fonts_value[theFont]+'">'+fonts_label[theFont]+'</FONT>'
				+ '</TD></TR>';
				i++;
			}
			index++;
			theFont = split(conf_fontFace, ",", index);
		}
	}

	sz  +=  '</TABLE>';
	return sz;
}

/**
 * Paragraph style popup
 */
function renderPopup_paragraphStyleRenderer() {
	var sz;
	var defaultParagraphs   = new Array();
	var thisStyle;

	defaultParagraphs[0] = new Array("<P>", L_STYLENORMAL_TEXT + " (P)","P");
	for (var i=1; i <= 6; i++)	{
		defaultParagraphs[i] = new Array("<H"+i+">", L_STYLEHEADING_TEXT + i + " (H" + i + ")","H"+i);
	}
	defaultParagraphs[7] = new Array("<PRE>", L_STYLEFORMATTED_TEXT + "(PRE)","PRE");

	sz = '<TABLE CLASS=block ALIGN=center ID=idList CELLSPACING=0 CELLPADDING=0 style="'+main_elements_style["P"]+'">';
		// Inserting the ordinary block HTML tags, like P and Hx and PRE:
	for (var i=0; i < defaultParagraphs.length; i++) {
		if (conf_hidePStyleItems!="*" && !isInCommaList(conf_hidePStyleItems,defaultParagraphs[i][2]))	{
			thisStyle = conf_showExampleInPopups ? (main_elements_style[defaultParagraphs[i][2]]?main_elements_style[defaultParagraphs[i][2]]:"") : conf_NeutralStyle;
			sz+= '<TR><TD NOWRAP _item='+i+' '+(conf_showExampleInPopups?'ALIGN=center':'')+' STYLE="margin:0pt;padding:0pt;">'
			+ '<'+defaultParagraphs[i][2]+' style="'+thisStyle+'">'
			+ defaultParagraphs[i][1]
			+ '</'+defaultParagraphs[i][2]+'>'
			+ '</TD></TR>';
		}
	}

		// Inserting the Class Formatting
	if (conf_classesParagraph)	{
		var index=1;
		var theClass = split(conf_classesParagraph, ",", index);
		i=defaultParagraphs.length;
		while (theClass) {
			if (classes_label[theClass])	{
				thisStyle = conf_showExampleInPopups ? "" : conf_NeutralStyle;
				sz+= '<TR><TD NOWRAP _item='+i+' '+(conf_showExampleInPopups?'ALIGN=center':'')+' STYLE="margin:0pt;padding:0pt;">'
				+ '<DIV class="CLASSES'+theClass+'" style="'+thisStyle+'">'
				+ classes_label[theClass]
				+ '</DIV>'
				+ '</TD></TR>';
				i++;
			}
			index++;
			theClass = split(conf_classesParagraph, ",", index);
		}
	}

	sz+='</TABLE>';
	return sz;
}


/**
 * Character style popup
 */
function renderPopup_characterStyleRenderer() {
	var sz;
	var thisStyle;

		// Find the oSel type
	var oSel	= theEditor.GetSelection();
	var classesC = "";
	var elName = "";
	var currentClassName = "";
	if (oSel.type != "Text")	{	// If no text is selected, find the first parent element
		if (oSel.parentElement != null)  	{
			elName = oSel.parentElement().tagName;
			classesC = conf_classesCharacter;
			currentClassName = oSel.parentElement().className;
			switch(elName)	{
				case "A":
					classesC = conf_classesLinks;
				break;
				case "TD":
					classesC = conf_classesTD;
				break;
			}
		} else {
			elName = oSel.item(0).tagName;
			switch(elName)	{
				case "TABLE":
					classesC = conf_classesTable;
				break;
				case "IMG":
					classesC = conf_classesImage;
				break;
			}
			currentClassName = oSel.item(0).className;
		}
	} else {
//		debugObj(oSel);
//		debugObj(oSel.parentElement());
		classesC = conf_classesCharacter;
	}

	sz='<BR><TABLE CLASS=block ALIGN=center ID=idList CELLSPACING=0 CELLPADDING=0 style="'+main_elements_style["P"]+'">';
		// Inserting the Class Formatting
	var i=1;
	if (classesC)	{
		var index=1;
		var theClass = split(classesC, ",", index);
		while (theClass) {
			if (classes_label[theClass])	{
				thisStyle = conf_showExampleInPopups ? "" : conf_NeutralStyle;
				sz+= '<TR><TD NOWRAP _item='+i+' '+(conf_showExampleInPopups?'ALIGN=center':'')+' STYLE="margin:0pt;padding:0pt;">'
				+ '<DIV class="CLASSES'+theClass+'" style="'+thisStyle+'">'
				+ classes_label[theClass]
				+ '</DIV>'
				+ '</TD></TR>';
				i++;
			}
			index++;
			theClass = split(classesC, ",", index);
		}
	}

		// Distance
	sz+= '<TR><TD ALIGN=center STYLE="margin:0pt;padding:0pt;">&nbsp;</TD></TR>';
		// No class:
	if (currentClassName)	{
		sz+= '<TR><TD NOWRAP _item='+i+' ALIGN=center STYLE="margin:0pt;padding:0pt;">'
		+ '<DIV class="CLASSES"><b>'+L_REMOVECLASS_TEXT+' ('+(classes_label[currentClassName]?classes_label[currentClassName]:'<em>'+currentClassName+'</em>')+')</b></DIV>'
		+ '</TD></TR>';
		i++;
	}
		// Remove formatting:
	sz+= '<TR><TD NOWRAP _item='+i+' ALIGN=center STYLE="margin:0pt;padding:0pt;">'
	+ '<DIV class="CLASSES" _remove=1><b>'+L_REMOVEALLHTML_TEXT+'</b></DIV>'
	+ '</TD></TR>';

		// Target tag
	if (elName)	{
		sz+= '<TR><TD ALIGN=center STYLE="margin:0pt;padding:0pt;"><DIV class="CLASSES">('+L_TARGETTAGCLASS_TEXT+': <em>'+elName+'</em>)</DIV></TD></TR>';
	}

		// End:
	sz+='</TABLE>';
	return sz
}

















/**
 * TABLE popup
 */
function renderPopup_tableRendererPage(szID,bDisplay) {
	var sz=""
	+   "<TABLE height=100% " + ((!bDisplay) ? " style=\"display: none\"" : "") + " width=100% CELLSPACING=0 CELLPADDING=0 ID=" + szID + ">"
	+	   "<TR ID=tableContents>"
	+		   "<TD ID=tableOptions VALIGN=TOP NOWRAP WIDTH=130 ROWSPAN=2>"
	+			   "<A HREF=\"javascript:parent._CTablePopupRenderer_Select(this,'" + szID + "','prop1')\">"
	+				   L_TABLEROWSANDCOLS_TEXT
	+			   "</A>"
	+			   "<BR>"
	+			   "<A HREF=\"javascript:parent._CTablePopupRenderer_Select(this,'" + szID + "','prop2')\">"
	+				   L_TABLEPADDINGANDSPACING_TEXT
	+			   "</A>"
	+			   "<BR>"
	+			   "<A HREF=\"javascript:parent._CTablePopupRenderer_Select(this,'" + szID + "','prop3')\">"
	+				   L_TABLEBORDERS_TEXT
	+			   "</A>"
	+			   "<BR>"
	+			   "<A HREF=\"javascript:parent._CTablePopupRenderer_Select(this,'" + szID + "','prop4')\">"
	+				   L_TABLEBG_TEXT
	+			   "</A>"
	+			   "<BR>"
	+		   "</TD>"
	+		   "<TD BGCOLOR=black ID=puDivider ROWSPAN=2>"
	+		   "</TD>"
	+		   "<TD ID=tableProps VALIGN=TOP>"
	if (szID=="tabNewBody") {
		sz+= "<DIV ID='" + szID + "prop1'>"
		+	"<P CLASS=tablePropsTitle>" + L_TABLEROWSANDCOLS_TEXT + "</P>"
		+  "<TABLE><TR><TD>"
		+				   L_TABLEINPUTROWS_TEXT
		+				   "</TD><TD><INPUT SIZE=2 TYPE=text ID=" + szID + "txtRows VALUE=2 >"
		+				   "</TD></TR><TR><TD>"
		+				   L_TABLEINPUTCOLUMNS_TEXT
		+				   "</TD><TD><INPUT SIZE=2 TYPE=text ID=" + szID + "txtColumns VALUE=2 >"
		+			   "</TD></TR></TABLE></DIV>"
	}
	else  {
		sz+= "<DIV ID='" + szID + "prop1'>"
			+	"<P CLASS=tablePropsTitle>" + L_TABLEROWSANDCOLS_TEXT + "</P>"
			+   "<INPUT type=button ID=" + szID + "txtRows VALUE=\"" + L_TABLEINSERTROW_TEXT + "\" ONCLICK=\"parent._CTablePopupRenderer_AddRow(this)\"><P>"
			+   "<INPUT type=button ID=" + szID + "txtCells VALUE=\"" + L_TABLEINSERTCELL_TEXT + "\" ONCLICK=\"parent._CTablePopupRenderer_AddCell(this)\"><BR>"
			+	"</DIV>"

	}

	sz +=			   "<DIV ID='" + szID + "prop2' STYLE=\"display: none\">"
	+					"<P CLASS=tablePropsTitle>" + L_TABLEPADDINGANDSPACING_TEXT + "</P>"
	+				   L_TABLEINPUTCELLPADDING_TEXT
	+				   "<INPUT SIZE=2 TYPE=text ID=" + szID + "txtPadding VALUE=0>"
	+				   "<BR>"
	+				   L_TABLEINPUTCELLSPACING_TEXT
	+				   "<INPUT SIZE=2 TYPE=text ID=" + szID + "txtSpacing VALUE=0>"
	+			   "</DIV>"
	+			   "<DIV ID=" + szID + "prop3 STYLE=\"display: none\">"
	+					"<P CLASS=tablePropsTitle>" + L_TABLEBORDERS_TEXT + "</P>"
	+				   L_TABLEINPUTBORDER_TEXT
	+				   "<INPUT SIZE=2 TYPE=text ID=" + szID + "txtBorder VALUE=1>"
	+				   "<BR>"
	+				   L_TABLEINPUTBORDERCOLOR_TEXT
	+				   "<INPUT SIZE=4 TYPE=text ID=" + szID + "txtBorderColor value=#000000><BR>"
	+				   renderPopup_colorTable("idBorder"+szID, "", "parent._CTablePopupRenderer_ColorSelect(this,'" + szID + "txtBorderColor')")
	+			   "</DIV>"
	+			   "<DIV ID=" + szID + "prop4 SIZE=12 STYLE=\"display: none\">"
	+					"<P CLASS=tablePropsTitle>" + L_TABLEBG_TEXT + "</P>"
	+				   L_TABLEINPUTBGIMGURL_TEXT
	+				   "<INPUT TYPE=text ID=" + szID + "txtBackgroundImage SIZE=15>"
	+				   "<BR>"
	+				   L_TABLEINPUTBGCOLOR_TEXT
	+				   "<INPUT TYPE=text SIZE=4 ID=" + szID + "txtBackgroundColor><BR>"
	+				   renderPopup_colorTable("idBackground"+szID, "", "parent._CTablePopupRenderer_ColorSelect(this,'" + szID + "txtBackgroundColor')")
	+			   "</DIV>"
	+		   "</TD>"
	+	   "</TR><TR><TD align=center ID=tableButtons valign=bottom>"
	if (szID=="tabNewBody") {
		sz +=	"<INPUT TYPE=submit ONCLICK=\"parent._CTablePopupRenderer_BuildTable('" + szID + "',this.document)\" VALUE=\"" + L_TABLEINSERT_TEXT + "\">"
			+   " <INPUT TYPE=reset VALUE=\"" + L_CANCEL_TEXT + "\" ONCLICK=\"parent.edHidePopup()\">"
	} else {
		sz +=	"<INPUT TYPE=submit ONCLICK=\"parent._CTablePopupRenderer_BuildTable('" + szID + "',this.document)\" VALUE=\"" + L_TABLEUPDATE_TEXT + "\">"
			+   " <INPUT TYPE=reset VALUE=\"" + L_CANCEL_TEXT + "\" ONCLICK=\"parent.edHidePopup()\">"
	}
	sz+=   "</TD></TR></TABLE>"
	return sz
}
function renderPopup_tableRenderer(){
	var sz  = "<TABLE CLASS=tabBox ID=\"tabSelect\" CELLSPACING=0 CELLPADDING=0 WIDTH=95%><TR HEIGHT=15><TD CLASS=tabItem STYLE=\"border-bottom: none\" NOWRAP><DIV ONCLICK=\"if (tabEdit.className!='disabled') {this.className='selected';this.parentElement.style.borderBottom = tabEdit.className=tabNewBody.style.display='';tabEditBody.style.display='none';tabEdit.parentElement.style.borderBottom='1px black solid'}\" CLASS=selected ID=tabNew>"+L_TABLENEW_TEXT+"</DIV></TD>"
	+   "<TD CLASS=tabItem NOWRAP><DIV ONCLICK=\"if (this.className!='disabled') {this.className='selected';this.parentElement.style.borderBottom = tabNew.className=tabEditBody.style.display='';tabNew.parentElement.style.borderBottom='1px black solid';tabNewBody.style.display='none'}\" CLASS=disabled ID=tabEdit>"+L_TABLEEDIT_TEXT+"</DIV></TD><TD CLASS=tabSpace WIDTH=100%>&nbsp;</TD></TR><TR><TD VALIGN=TOP CLASS=tabBody COLSPAN=3>"
	+   renderPopup_tableRendererPage("tabNewBody",true)
	+   renderPopup_tableRendererPage("tabEditBody",false)
	+	"</TD></TR></TABLE>"
	return sz
}
function _CTablePopupRenderer_Select(el,szID, id) {
	var d = el.document

	for (var i = 1; i < 5; i++)
		d.all[szID + "prop" + i].style.display = "none"

	d.all[szID + id].style.display = ""
}
function _CTablePopupRenderer_ColorSelect(el,id) {
	el.document.all[id].value = el.bgColor
}
function _CTablePopupRenderer_AddRow(el) {
	var elRow = el.document.elCurrent.insertRow()
	for (var i=0;i<el.document.elCurrent.rows[0].cells.length;i++) {
		var elCell = elRow.insertCell()
		elCell.innerHTML = "&nbsp;"
	}
}
function _CTablePopupRenderer_AddCell(el) {
	for (var i=0;i<el.document.elCurrent.rows.length;i++) {
		var elCell = el.document.elCurrent.rows[i].insertCell()
		elCell.innerHTML = "&nbsp;"
	}
}
function _CTablePopupRenderer_BuildTable(szID, d) {
	if (szID=="tabNewBody") {
		var sz =   ""
		+   "<TABLE "
		+  (((d.all[szID + "txtBorder"].value=="") || (d.all[szID + "txtBorder"].value=="0")) ? "class=\"NOBORDER\"" : "")
		+	   (d.all[szID + "txtPadding"].value != "" ? "cellPadding=\"" + d.all[szID + "txtPadding"].value + "\" " : "")
		+	   (d.all[szID + "txtSpacing"].value != "" ? "cellSpacing=\"" + d.all[szID + "txtSpacing"].value + "\" " : "")

		+	   (d.all[szID + "txtBorder"].value != "" ? "border=\"" + d.all[szID + "txtBorder"].value + "\" " : "")
		+	   (d.all[szID + "txtBorderColor"].value != "" ? "bordercolor=\"" + d.all[szID + "txtBorderColor"].value + "\" " : "")
		+	   (d.all[szID + "txtBackgroundImage"].value != "" ? "background=\"" + d.all[szID + "txtBackgroundImage"].value + "\" " : "")

		+	   (d.all[szID + "txtBackgroundColor"].value != "" ? "bgColor=\"" + d.all[szID + "txtBackgroundColor"].value + "\" " : "")
		+   ">"

		for (var r=0; r < d.all[szID + "txtRows"].value; r++)
		{
			sz +=  "<TR>"

			for (var c=0; c < d.all[szID + "txtColumns"].value; c++)
				sz +=  "<TD>&nbsp;</TD>"

			sz +=  "</TR>"
		}

		sz +=  "</TABLE>"
		insertHTML(sz)
   } else if (d.elCurrent) {
			d.elCurrent.cellPadding = d.all.tabEditBodytxtPadding.value
			d.elCurrent.cellSpacing = d.all.tabEditBodytxtSpacing.value
			d.elCurrent.border = d.all.tabEditBodytxtBorder.value
			d.elCurrent.className = (d.elCurrent.border=="" || d.elCurrent.border==0) ? "NOBORDER" : ""
 			d.elCurrent.borderColor = d.all.tabEditBodytxtBorderColor.value
			d.elCurrent.bgColor = d.all.tabEditBodytxtBackgroundColor.value
			d.elCurrent.background = d.all.tabEditBodytxtBackgroundImage.value
   }
	edHidePopup();
}

























/**
 * Loading the image selector
 */
function renderPopup_image(){
	var oSel = theEditor.GetSelection();
	var oEl, sType = oSel.type;
	idPopup.document._selectedImage="";
	var addParams="?"+conf_RTEtsConfigParams;
	if ((oSel.item) && (oSel.item(0).tagName=="IMG")) 	{
		idPopup.document._selectedImage=oSel.item(0);
		addParams="?act=image"+conf_RTEtsConfigParams;
	}
	return '<iframe ID="idLinks" width="98%" height="90%" style="visibility: visible; border: none;" src="rte_select_image.php'+addParams+'"></iframe>';
}

/**
 * Displaying user-selector
 */
function renderPopup_user() {
	var oSel = theEditor.GetSelection();
	GLOBAL_SEL = oSel;	// Remember selection from before...
	var addParams="?"+conf_RTEtsConfigParams;

	return '<iframe id="idLinks" width="98%" height="90%" style="visibility: visible; border: none;" src="rte_user.php'+addParams+'"></iframe>';
}

/**
 * Inserting image
 */
function renderPopup_insertImage(image) {
	insertHTML(image);
	idPopup.document._selectedImage="";
//	theEditor.RestoreSelection();
	edHidePopup();
}

/**
 * Removing all links from current selection
 */
function renderPopup_unLink()  {
	var oSel = theEditor.GetSelection();
	oSel.execCommand("UnLink",false,"");
	idEditbox.focus();
}

/**
 * Adding link (url) with target (target) to current selection
 */
function renderPopup_addLink(url,target) {
	var szURL = url;
	var theType = "";

	var oSel = GLOBAL_SEL; 	//? GLOBAL_SEL : theEditor.GetSelection();	// Here we are getting the GLOBAL_SEL which were set when we opened the Link window. The point is, that in MSIE 5.x the selection in the main window MAY be lost if one select and edit content in a input-field in the window. If this happens, the link is set on the first word in the text and not the selection it should be set for. So therefore we store and restore the selection in between...
//debugObj(GLOBAL_SEL);
//debugObj(oSel);

	var sType = oSel.type;
	var oEl;
	if (szURL!="") 	{
			// Begin section: This is for MSIE <6 if you place the cursor in a link, the selection is only extended to include the whole link, if this code is executed. MSIE6+ seems to keep the selection from the time when the the link-window appears.
		if ((oSel.parentElement) && (oSel.text=="")) 	{
			oEl = getElementObject(oSel.parentElement(),"A");
			if (oEl && oSel.moveToElementText)	{
				oSel.moveToElementText(oEl);	// Setting the selection to the current element
			}
		}
			// End section
		if ((oSel.parentElement) && (oSel.text=="")) 	{
			oSel.expand("word");
			if (oSel.text=="") 	{
				var oStore = oSel.duplicate();
				oSel.text = szURL;
				oSel.setEndPoint("StartToStart",oStore);
			}
			oSel.select();
			sType="Text";
		}

		if ((oSel.item) && (oSel.item(0).tagName=="IMG")) 	{
				oSel.item(0).width = oSel.item(0).offsetWidth
				oSel.item(0).height = oSel.item(0).offsetHeight
//				oSel.item(0).border = (d.all.displayBorder.checked) ? 1 : ""
				oSel.item(0).border = "";
		}

		oSel.execCommand("UnLink",false,"");
		oSel.execCommand("CreateLink",false,szURL);

		if (oSel.parentElement)  	{
			oEl = getElementObject(oSel.parentElement(),"A");
		} else {
			oEl = getElementObject(oSel.item(0),"A");
		}
		if (oEl)	{
			oEl.target=target;
		}
	}
	idEditbox.focus()
}

/**
 * Displaying the link selector and passing the current link url and target
 */
function renderPopup_linkRenderer() {
	var oSel = theEditor.GetSelection();
	var oEl, sType = oSel.type;
	if (oSel.parentElement)  	{
		oEl = getElementObject(oSel.parentElement(),"A");
	} else {
		oEl = getElementObject(oSel.item(0),"A");
	}
	var addUrlParams="?"+conf_RTEtsConfigParams;
	if (oEl)	{
		if (oSel.moveToElementText)	oSel.moveToElementText(oEl);	// Setting the selection to the current element
		addUrlParams="?curUrl[href]="+escape(oEl.href)+"&curUrl[target]="+escape(oEl.target)+conf_RTEtsConfigParams;
	} else if (oSel.htmlText)	{
		var offset = oSel.htmlText.indexOf("<a");
		if (offset==-1)	{
			offset = oSel.htmlText.indexOf("<A");
		}
		if (offset!=-1)	{
			var ATagContent = oSel.htmlText.substring(offset+2);
			offset = ATagContent.indexOf(">");
			ATagContent = ATagContent.substring(0,offset);
			addUrlParams="?curUrl[all]="+escape(ATagContent)+conf_RTEtsConfigParams;
		}
	}

	GLOBAL_SEL = oSel;	// Remember selection from before...

	return '<IFRAME ID=idLinks WIDTH=98% HEIGHT=90% STYLE="visibility: visible;border: none" SRC="'+BACK_PATH+'browse_links.php'+addUrlParams+'"></IFRAME>';
}

/**
 * Making color selector table
 */
function renderPopup_colorTable(sID,fmt,szClick) {
	var sz;
	var cPick = new Array("00","33","66","99","CC","FF");
	var iColors = cPick.length;
	var szColor = "";
	var szColorId = "";

	sz = '<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD VALIGN=middle><DIV CLASS=currentColor ID="'+sID+'Current">&nbsp;</DIV></TD><TD>'
	+ '<TABLE ONMOUSEOUT="document.all.'+sID+'Current.style.backgroundColor=\'\';" ONMOUSEOVER="document.all.'+sID+'Current.style.backgroundColor=event.srcElement.bgColor;" CLASS=colorTable CELLSPACING=0 CELLPADDING=0 ID="'+sID+'">';
		// Making colorPicker
	if (!conf_disableColorPicker)	{
		for (var r=0;r<iColors;r++) {
			sz+='<TR>'
			for (var g=iColors-1;g>=0;g--)	{
				for (var b=iColors-1;b>=0;b--) {
					szColor = cPick[r]+cPick[g]+cPick[b];
					sz+='<TD BGCOLOR="#'+szColor+'" _item="'+szColor+'" TITLE="#'+szColor+'"'+(szClick?' ONCLICK="'+szClick+'"':'')+'></TD>';	// &nbsp;
				}
			}
			sz+='</TR>';
		}
	}

	if (conf_colors && !conf_disableColorPicker)	{
		sz+='<TR><TD colspan=36></TD></TR>';
	}

		// Making specific color selector:
	if (conf_colors)	{
		var index=1;
		var theColor = split(conf_colors, ",", index);
		while (theColor) {
			if (colors_label[theColor])	{
				szColor = colors_value[theColor];
				sz+='<TR>';
				sz+='<TD style="width:36px;" colspan=6 BGCOLOR="'+szColor+'" _item="spec_'+theColor+'" TITLE="'+szColor+'"'+(szClick?' ONCLICK="'+szClick+'"':'')+'>&nbsp;</TD>';
				sz+='<TD colspan=2></TD>';
				sz+='<TD colspan=28><nobr>'+colors_label[theColor]+'</nobr></TD>';
				sz+='</TR>';
			}
			index++;
			theColor = split(conf_colors, ",", index);
		}
	}
	sz+='</TABLE></TD></TR></TABLE>';
	return sz;
}

/**
 * Returns true if theVal is in the commalist theList
 */
function isInCommaList (theList, theVal)	{
	var theWorkList = ","+theList+",";
	return (theWorkList.indexOf(","+theVal+",")!=-1);
}

/**
 * Splitting a string, theStrl, by delimiter, delim, and return index, index
 */
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

