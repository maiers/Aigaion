/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Adapted from WIKINDX: http://wikindx.sourceforge.net

Mark Grimshaw 2005
http://bibliophile.sourceforge.net

	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/create/common.js,v 1.3 2006/12/01 14:27:49 reidsma Exp $
********************************/

var agt=navigator.userAgent.toLowerCase();
var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_opera = (agt.indexOf("opera") != -1);
var is_win   = ( (agt.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1) );
var is_mac    = (agt.indexOf("mac")!=-1);
var is_safari = (agt.indexOf("safari") != -1);
var is_konqueror = (agt.indexOf("konqueror") != -1);
var is_gecko = (agt.indexOf("gecko") != -1);

function init(){ // init() is called in body.tpl
 initPreviewLinks();
}

/**
* Create the preview link for bibliographic style editing, hiding the link for non-javascript-enabled browsers.
* 
* @author	Jess Collicott
* @editor	Mark Tsikanovski and Mark Grimshaw
* @version	2
*/
function initPreviewLinks(){ // collect any links for style template preview, add onclick events and make them visible
 var styleLinkKeyString = 'action=previewStyle'; // use this string to detect Style links
 var styleLinkKeyRegEx = new RegExp(styleLinkKeyString,'i');
 var citeLinkKeyString = 'action=previewCite'; // use this string to detect Cite links
 var citeLinkKeyRegEx = new RegExp(citeLinkKeyString,'i');
 var links = document.getElementsByTagName('body').item(0).getElementsByTagName('a'); // get collection of all links
 var linksLength = links.length; // cache
// As of 3.1, style previewing is not working in IE so turn it off all together.
  if (is_ie)
  {
	for (i=0;i<linksLength;i++)
	{
		if (typeof(links[i].href) != 'undefined' && 
			((links[i].href.search(styleLinkKeyRegEx) != -1) || (links[i].href.search(citeLinkKeyRegEx) != -1)))
		{
			links[i].className = 'linkCiteHidden';
		}
	}
	return;
  }

 for (i=0;i<linksLength;i++){
  if (typeof(links[i].href) != 'undefined' && 
	((links[i].href.search(styleLinkKeyRegEx) != -1) || (links[i].href.search(citeLinkKeyRegEx) != -1))){
    if (links[i].className == 'imgLink linkCiteHidden') {
	  links[i].className = 'imgLink linkCite';
	}
	else {
      links[i].className = 'link linkCite';
	}
  }
 }
}

/**
* rewrite creators for style and footnote previews - common code
* 
* @Author Mark Grimshaw with a lot of help from Christian Boulanger
*/
function rewriteCreatorsPreview(templateName, styleArray)
{
	var creatorArray = new Array();
	var currFormField;
	var fieldName;
	var arrayKey;
	var key;
    var a_php = "";
    var total = 0;
// Add rewrite creator strings for the resource type
	var creators = new Array("creator1", "creator2", "creator3", "creator4", "creator5");
	for (index = 0; index < creators.length; index++)
	{
		arrayKey = creators[index] + "_firstString";
		fieldName = templateName + "_" + creators[index] + "_firstString";
		currFormField = document.forms[0][fieldName];
		if(typeof(currFormField) != 'undefined')
			creatorArray[arrayKey] = currFormField.value; // input and textarea
		
		arrayKey = creators[index] + "_firstString_before";
		fieldName = templateName + "_" + creators[index] + "_firstString_before";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
			
		arrayKey = creators[index] + "_remainderString";
		fieldName = templateName + "_" + creators[index] + "_remainderString";
		currFormField = document.forms[0][fieldName];
		if(typeof(currFormField) != 'undefined')
			creatorArray[arrayKey] = currFormField.value; // input and textarea
		
		arrayKey = creators[index] + "_remainderString_before";
		fieldName = templateName + "_" + creators[index] + "_remainderString_before";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
			
		arrayKey = creators[index] + "_remainderString_each";
		fieldName = templateName + "_" + creators[index] + "_remainderString_each";
		currFormField = document.forms[0][fieldName];
		if ((typeof(currFormField) != 'undefined') && currFormField.checked) // checkbox
			creatorArray[arrayKey] = "on";
	}
    for (key in creatorArray)
    {
        ++ total;
		escapeKey = escape(String(styleArray[key]).replace(/ /g, '__WIKINDX__SPACE__'));
		escapeKey = escape(String(creatorArray[key]));
		if(escapeKey.match(/%u/)) // unicode character so length changes
		{
			a_php = a_php + "s:" +
				String(key).length + ":\"" + String(key) + "\";s:" +
				escapeKey.length + ":\"" + escapeKey + "\";";
		}
		else
		{
			a_php = a_php + "s:" +
                String(key).length + ":\"" + String(key) + "\";s:" +
                String(creatorArray[key]).length + ":\"" + String(creatorArray[key]) + "\";";
		}
    }
    a_php = "a:" + total + ":{" + a_php + "}";
    return "&rewriteCreator=" + escape(a_php);
}
/**
* pop-up window for style and footnote previews - common code
* 
* @Author Mark Grimshaw with a lot of help from Christian Boulanger
*/
function popUpStyleFootnotCommon(templateName)
{
	var fieldArray = new Array ("style_titleCapitalization", "style_primaryCreatorFirstStyle", 
			"style_primaryCreatorOtherStyle", "style_primaryCreatorInitials", 
			"style_primaryCreatorFirstName", "style_otherCreatorFirstStyle", 
			"style_otherCreatorOtherStyle", "style_otherCreatorInitials", "style_dayFormat", 
			"style_otherCreatorFirstName", "style_primaryCreatorList", "style_otherCreatorList",
			"style_primaryCreatorListAbbreviationItalic", "style_otherCreatorListAbbreviationItalic", 
			"style_monthFormat", "style_editionFormat", "style_primaryCreatorListMore", 
			"style_primaryCreatorListLimit", "style_dateFormat", 
			"style_primaryCreatorListAbbreviation", "style_otherCreatorListMore", 
			"style_runningTimeFormat", "style_primaryCreatorRepeatString", "style_primaryCreatorRepeat", 
			"style_otherCreatorListLimit", "style_otherCreatorListAbbreviation", "style_pageFormat", 
			"style_editorSwitch", "style_editorSwitchIfYes", "style_primaryCreatorUppercase", 
			"style_otherCreatorUppercase", "style_primaryCreatorSepFirstBetween", 
			"style_primaryCreatorSepNextBetween", "style_primaryCreatorSepNextLast", 
			"style_otherCreatorSepFirstBetween", "style_otherCreatorSepNextBetween", 
			"style_otherCreatorSepNextLast", "style_primaryTwoCreatorsSep", "style_otherTwoCreatorsSep", 
			"style_userMonth_1", "style_userMonth_2", "style_userMonth_3", "style_userMonth_4", 
			"style_userMonth_5", "style_userMonth_6", "style_userMonth_7", "style_userMonth_8", 
			"style_userMonth_9", "style_userMonth_10", "style_userMonth_11", "style_userMonth_12", 
			"style_dateRangeDelimit1", "style_dateRangeDelimit2", "style_dateRangeSameMonth", 
			"style_dateMonthNoDay", "style_dateMonthNoDayString", "style_dayLeadingZero", 
			"style_titleSubtitleSeparator"
		);
	var fieldName;
	var currFormField;
	var styleArray = new Array ();
	for (index = 0; index < fieldArray.length; index++)
	{
		currFormField = document.forms[0][fieldArray[index]];
//alert("HERE: " + fieldArray[index] + " ~ " + currFormField.value);
		if ((currFormField.type == "checkbox") && currFormField.checked)
			styleArray[fieldArray[index]] = "on"; // checkbox
		else if (currFormField.type != "checkbox")
			styleArray[fieldArray[index]] = currFormField.value; // input and textarea
    }
// rewrite creator array
	var creatorQuery = rewriteCreatorsPreview(templateName, styleArray);
// style definition array
    var a_php = "";
    var total = 0;
	var key;
	var escapeKey;
    for (key in styleArray)
    {
		++ total;
		escapeKey = escape(String(styleArray[key]).replace(/ /g, '__WIKINDX__SPACE__'));
		if(escapeKey.match(/%u/)) // unicode character so length changes
		{
			a_php = a_php + "s:" +
				String(key).length + ":\"" + String(key) + "\";s:" +
				escapeKey.length + ":\"" + escapeKey + "\";";
		}
		else
		{
			a_php = a_php + "s:" +
				String(key).length + ":\"" + String(key) + "\";s:" +
				String(styleArray[key]).length + ":\"" + String(styleArray[key]) + "\";";
		}
    }
    a_php = "a:" + total + ":{" + a_php + "}";
	return new Array(a_php, creatorQuery);
}
/**
* pop-up window for style previews
* 
* @Author Mark Grimshaw with a lot of help from Christian Boulanger
*/
function openPopUpStylePreview(url, templateName, fallback)
{
	browserDimensions();
	var w = browserWidth * 0.95;
	var h = browserHeight * 0.4;
	var retArray = new Array();
	retArray = popUpStyleFootnotCommon(templateName);
    url = url + "&style=" + escape(retArray[0]);
	var templateString = document.forms[0][templateName].value; 
	if(fallback)
	{
		var fallbackValue = document.forms[0][fallback].value;
		if(!templateString)
			var fallbackString = document.forms[0]['style_' + fallbackValue].value;
	}
	else
		var fallbackString = '';
	if(!templateString)
		templateName = fallbackValue;
	url = url + retArray[1] + "&templateName=" + templateName + 
		"&templateString=" + escape(templateString) + "&fallbackString=" + escape(fallbackString);
	var popUp = window.open(url,'winMeta','height=' + h + 
		',width=' + w + ',left=10,top=10,status,scrollbars,resizable,dependent');
//		document.write(url);
}
/**
* pop-up window for footnote previews
* 
* @Author Mark Grimshaw with a lot of help from Christian Boulanger
*/
function openPopUpFootnotePreview(url, templateName, fallback)
{
	browserDimensions();
	var w = browserWidth * 0.95;
	var h = browserHeight * 0.4;
	var retArray = new Array();
	retArray = popUpStyleFootnotCommon(templateName);
    url = url + "&style=" + escape(retArray[0]);
	var ftTemplateName = 'footnote_' + templateName + 'Template';
	var styleTemplateName = 'style_' + templateName;
	var ftTemplateString = document.forms[0][ftTemplateName].value; 
	var styleTemplateString = document.forms[0][styleTemplateName].value; 
	var fallbackString = '';
	var templateName = styleTemplateName;
	if(ftTemplateString)
	{
		var templateString = ftTemplateString;
//		var templateName = ftTemplateName;
	}
	else if(styleTemplateString)
	{
		var templateString = styleTemplateString;
	}
	else if(fallback)// fallback footnote
	{
		var templateString = '';
		var fallbackValue = document.forms[0][fallback].value;
		var fallbackString = document.forms[0]['footnote_' + fallbackValue + 'Template'].value;
		if(!fallbackString)
			fallbackString = document.forms[0]['style_' + fallbackValue].value;
		var templateName = fallbackValue;
	}
	url = url + retArray[1] + "&templateName=" + templateName + 
		"&templateString=" + escape(templateString) + "&fallbackString=" + escape(fallbackString);
	var popUp = window.open(url,'winMeta','height=' + h + 
		',width=' + w + ',left=10,top=10,status,scrollbars,resizable,dependent');
//		document.write(url);
}

/**
* pop-up window for in-text citation previews
* 
* @Author Mark Grimshaw with a lot of help from Christian Boulanger
*/
function openPopUpCitePreview(url)
{
 browserDimensions();
 var w = browserWidth * 0.95;
 var h = browserHeight * 0.2;
	var fieldArray = new Array (
			"cite_creatorStyle", "cite_creatorOtherStyle", 
			"cite_creatorInitials", "cite_creatorFirstName", 
			"cite_useInitials", "cite_creatorUppercase", "cite_twoCreatorsSep", 
			"cite_creatorSepFirstBetween", "cite_creatorSepNextBetween", "cite_creatorSepNextLast",
			"cite_creatorList", "cite_creatorListMore", 
			"cite_creatorListLimit", "cite_creatorListAbbreviation", "cite_creatorListAbbreviationItalic", 
			"cite_creatorListSubsequent", "cite_creatorListSubsequentMore", 
			"cite_creatorListSubsequentLimit", "cite_creatorListSubsequentAbbreviation", 
			"cite_firstChars", "cite_lastChars", "cite_template", 
			"cite_replaceYear", "cite_followCreatorTemplate", "cite_followCreatorPageSplit", 
			"cite_consecutiveCitationSep", "cite_consecutiveCreatorSep", "cite_consecutiveCreatorTemplate", 
			"cite_subsequentCreatorTemplate", "cite_pageFormat", 
			"cite_yearFormat", "cite_titleCapitalization", "cite_titleSubtitleSeparator", 
			"cite_ambiguous", "cite_ambiguousTemplate", "cite_subsequentCreatorRange", "cite_subsequentFields", 
			"cite_removeTitle"
		);
	var arrayKey;
	var fieldName;
	var currFormField;
	var citeArray = new Array ();
	var creatorArray = new Array ();
	for (index = 0; index < fieldArray.length; index++)
	{
		currFormField = document.forms[0][fieldArray[index]];
		if ((currFormField.type == "checkbox") && currFormField.checked)
			citeArray[fieldArray[index]] = "on"; // checkbox
		else if (currFormField.type != "checkbox")
			citeArray[fieldArray[index]] = currFormField.value; // input and textarea
    }
// rewrite creator array
    var a_php = "";
// style definition array
    a_php = "";
    total = 0;
	var key;
	var escapeKey;
    for (key in citeArray)
    {
		++ total;
		escapeKey = escape(String(citeArray[key]).replace(/ /g, '__WIKINDX__SPACE__'));
		if(escapeKey.match(/%u/)) // unicode character so length changes
		{
			a_php = a_php + "s:" +
				String(key).length + ":\"" + String(key) + "\";s:" +
				escapeKey.length + ":\"" + escapeKey + "\";";
		}
		else
		{
			a_php = a_php + "s:" +
				String(key).length + ":\"" + String(key) + "\";s:" +
				String(citeArray[key]).length + ":\"" + String(citeArray[key]) + "\";";
		}
    }
    a_php = "a:" + total + ":{" + a_php + "}";
    url = url + "&cite=" + escape(a_php);
	var popUp = window.open(url,'winMeta','height=' + h + 
		',width=' + w + ',left=10,top=10,status,scrollbars,resizable,dependent');
//		document.write(url);
}


// Get browser dimensions.
// Code adapted from http://www.howtocreate.co.uk/tutorials/index.php?tut=0&part=16
function browserDimensions()
{
	if( typeof( window.innerWidth ) == 'number' )
	{
//Non-IE
		browserWidth = window.innerWidth;
		browserHeight  = window.innerHeight;
	}
	else if( document.documentElement &&
      ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
	{
//IE 6+ in 'standards compliant mode'
		browserWidth = document.documentElement.clientWidth;
		browserHeight  = document.documentElement.clientHeight;
	}
	else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
	{
//IE 4 compatible
		browserWidth = document.body.clientWidth;
		browserHeight  = document.body.clientHeight;
	}
}

/* ===== common JavaScript functions ===== */

// placeholder
