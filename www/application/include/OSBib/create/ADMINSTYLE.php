<?php
/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Adapted from WIKINDX: http://wikindx.sourceforge.net

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
********************************/
/*****
*	ADMINSTYLE class.
*
*	Administration of citation bibliographic styles
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/create/ADMINSTYLE.php,v 1.3 2006/12/01 14:27:49 reidsma Exp $
*****/
class ADMINSTYLE
{
// Constructor
	function ADMINSTYLE($vars)
	{$this->footnotePages = FALSE;
		$this->vars = $vars;
/**
* THE OSBIB Version number
*/
		$this->osbibVersion = "3.1";
		include_once(OSBIB__SESSION);
		$this->session = new SESSION();
		include_once(OSBIB__MESSAGES);
		$this->messages = new MESSAGES();
		include_once(OSBIB__SUCCESS);
		$this->success = new SUCCESS();
		include_once(OSBIB__ERRORS);
		$this->errors = new ERRORS();
		include_once(OSBIB__MISC);
		include_once(OSBIB__FORM);
		include_once(OSBIB__LOADSTYLE);
		$this->style = new LOADSTYLE();
		$this->styles = $this->style->loadDir(OSBIB_STYLE_DIR);
		$this->creators = array('creator1', 'creator2', 'creator3', 'creator4', 'creator5');
	}
// check we really are admin
	function gateKeep($method)
	{
// else, run $method
		return $this->$method();
	}
// display options for styles
	function display($message = FALSE)
	{
// Clear previous style in session
		$this->session->clearArray("cite");
		$this->session->clearArray("style");
		$this->session->clearArray("partial");
		$this->session->clearArray("footnote");
		$pString = MISC::h($this->messages->text("heading", "styles"), FALSE, 3);
		if($message)
			$pString .= MISC::p($message);
		$pString .= MISC::p(MISC::a("link", $this->messages->text("style", "addLabel"), 
			"index.php?action=adminStyleAddInit"));
		if(sizeof($this->styles))
		{
			$pString .= MISC::p(MISC::a("link", $this->messages->text("style", "copyLabel"), 
				"index.php?action=adminStyleCopyInit"));
			$pString .= MISC::p(MISC::a("link", $this->messages->text("style", "editLabel"), 
				"index.php?action=adminStyleEditInit"));
		}
		return $pString;
	}
// Add a style - display options.
	function addInit($error = FALSE)
	{
		$pString = MISC::h($this->messages->text("heading", "styles", 
			" (" . $this->messages->text("style", "addLabel") . ")"), FALSE, 3);
		if($error)
			$pString .= MISC::p($error, "error", "center");
		$pString .= $this->displayStyleForm('add');
		return $pString;
	}
// Write style to text file
	function add()
	{
		if($error = $this->validateInput('add'))
			$this->badInput($error, 'addInit');
		$this->writeFile();
		$pString = $this->success->text("style", " " . $this->messages->text("misc", "added") . " ");
		$this->styles = $this->style->loadDir(OSBIB_STYLE_DIR);
		return $this->display($pString);
	}
// display styles for editing
	function editInit($error = FALSE)
	{
		$pString = MISC::h($this->messages->text("heading", "styles", 
			" (" . $this->messages->text("style", "editLabel") . ")"), FALSE, 3);
		$pString .= FORM::formHeader("adminStyleEditDisplay");
		$styleFile = $this->session->getVar('editStyleFile');
		if($styleFile)
			$pString .= FORM::selectedBoxValue(FALSE, "editStyleFile", $this->styles, $styleFile, 20);
		else
			$pString .= FORM::selectFBoxValue(FALSE, "editStyleFile", $this->styles, 20);
		$pString .= MISC::br() . FORM::formSubmit('Edit');
		$pString .= FORM::formEnd();
		return $pString;
	}
// Display a style for editing.
	function editDisplay($error = FALSE)
	{
		if(!$error)
			$this->loadEditSession();
		$pString = MISC::h($this->messages->text("heading", "styles", 
			" (" . $this->messages->text("style", "editLabel") . ")"), FALSE, 3);
		if($error)
			$pString .= MISC::p($error, "error", "center");
		$pString .= $this->displayStyleForm('edit');
		return $pString;
	}
// Read data from style file and load it into the session
	function loadEditSession($copy = FALSE)
	{
// Clear previous style in session
		$this->session->clearArray("style");
		$this->session->clearArray("cite");
		$this->session->clearArray("footnote");
		include_once(OSBIB__PARSEXML);
		$parseXML = new PARSEXML();
		include_once(OSBIB__STYLEMAP);
		$styleMap = new STYLEMAP();
		$resourceTypes = array_keys($styleMap->types);
		$this->session->setVar('editStyleFile', $this->vars['editStyleFile']);
		$dir = strtolower($this->vars['editStyleFile']);
		$fileName = $this->vars['editStyleFile'] . ".xml";
		if($fh = fopen(OSBIB_STYLE_DIR . "/" . $dir . "/" . $fileName, "r"))
		{
			list($info, $citation, $footnote, $common, $types) = $parseXML->extractEntries($fh);
			if(!$copy)
			{
				$this->session->setVar("style_shortName", $this->vars['editStyleFile']);
				$this->session->setVar("style_longName", base64_encode($info['description']));
			}
			foreach($citation as $array)
			{
				if(array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array))
					$this->session->setVar("cite_" . $array['_NAME'], 
					base64_encode(htmlspecialchars($array['_DATA'])));
			}
			$this->arrayToTemplate($footnote, TRUE);
			foreach($resourceTypes as $type)
			{
				$type = 'footnote_' . $type;
				$sessionKey = $type . 'Template';
				if(!empty($this->$type))
					$this->session->setVar($sessionKey, base64_encode(htmlspecialchars($this->$type)));
				unset($this->$type);
			}
			foreach($common as $array)
			{
				if(array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array))
					$this->session->setVar("style_" . $array['_NAME'], 
					base64_encode(htmlspecialchars($array['_DATA'])));
			}
			$this->arrayToTemplate($types);
			foreach($resourceTypes as $type)
			{
				$sessionKey = 'style_' . $type;
				if(!empty($this->$type))
					$this->session->setVar($sessionKey, base64_encode(htmlspecialchars($this->$type)));
				if(array_key_exists($type, $this->fallback))
				{
					$sessionKey .= "_generic";
					$this->session->setVar($sessionKey, base64_encode($this->fallback[$type]));
				}
				$partialName = 'partial_' . $type . 'Template';
				if($this->$partialName)
					$this->session->setVar($partialName, base64_encode(htmlspecialchars($this->$partialName)));
				$partialReplace = 'partial_' . $type . 'Replace';
				if($this->$partialReplace)
					$this->session->setVar($partialReplace, 
					base64_encode(htmlspecialchars($this->$partialReplace)));
				else
					$this->session->delVar($partialReplace);
			}
		}
		else
			$this->badInput($this->errors->text("file", "read"));
	}
// Transform XML nodal array to resource type template strings for loading into the style editor
	function arrayToTemplate($types, $footnote = FALSE)
	{
		$this->fallback = array();
		foreach($types as $resourceArray)
		{
			if($footnote && ($resourceArray['_NAME'] != 'resource'))
			{
				$this->session->setVar("footnote_" . $resourceArray['_NAME'], 
					base64_encode(htmlspecialchars($resourceArray['_DATA'])));
				continue;
			}
			$temp = $tempArray = $newArray = $independent = array();
			$empty = $ultimate = $preliminary = $partial = $partialReplace = FALSE;
/**
* The resource type which will be our array name
*/
			if($footnote)
				$type = "footnote_" . $resourceArray['_ATTRIBUTES']['name'];
			else
			{
				$type = $resourceArray['_ATTRIBUTES']['name'];
				$this->writeSessionRewriteCreators($type, $resourceArray);
			}
			$styleDefinition = $resourceArray['_ELEMENTS'];
			foreach($styleDefinition as $array)
			{
				if(array_key_exists('_NAME', $array) && array_key_exists('_DATA', $array) 
					 && array_key_exists('_ELEMENTS', $array))
				{
					if($array['_NAME'] == 'ultimate')
					{
						$temp['ultimate'] = $array['_DATA'];
						continue;
					}
					if($array['_NAME'] == 'partial')
					{
						$temp['partial'] = $array['_DATA'];
						continue;
					}
					if($array['_NAME'] == 'partialReplace')
					{
						$temp['partialReplace'] = $array['_DATA'];
						continue;
					}
					if($array['_NAME'] == 'preliminaryText')
					{
						$temp['preliminaryText'] = $array['_DATA'];
						continue;
					}
					if(empty($array['_ELEMENTS']) && !$footnote)
					{
						$this->fallback[$type] = $array['_DATA'];
//						$empty = TRUE;
					}
					foreach($array['_ELEMENTS'] as $elements)
					{
						if($array['_NAME'] == 'independent')
						{
							$split = explode("_", $elements['_NAME']);
							$temp[$array['_NAME']][$split[1]] 
							= $elements['_DATA'];
						}
						else
							$temp[$array['_NAME']][$elements['_NAME']] 
							= $elements['_DATA'];
					}
				}
			}
//			if($empty)
//			{
//				$this->$type = array();
//				continue;
//			}
/**
* Now parse the temp array into template strings
*/
			$alternates = $fieldKeys = array();
			$index = 0;
			foreach($temp as $key => $value)
			{
				if(!is_array($value))
				{
					if($key == 'ultimate')
						$ultimate = $value;
					else if($key == 'preliminaryText')
						$preliminary = $value;
					else if($key == 'partial')
						$partial = $value;
					else if(($key == 'partialReplace') && $value)
						$partialReplace = $value;
					continue;
				}
				if(($key == 'independent'))
				{
					$independent = $value;
					continue;
				}
				$pre = $post = $dependentPre = $dependentPost = $dependentPreAlternative = 
					$dependentPostAlternative = $singular = $plural = $string = FALSE;
				if(array_key_exists('alternatePreFirst', $value))
					$alternates[$key]['preFirst'] = $value['alternatePreFirst'];
				if(array_key_exists('alternatePreSecond', $value))
					$alternates[$key]['preSecond'] = $value['alternatePreSecond'];
				if(array_key_exists('alternatePostFirst', $value))
					$alternates[$key]['postFirst'] = $value['alternatePostFirst'];
				if(array_key_exists('alternatePostSecond', $value))
					$alternates[$key]['postSecond'] = $value['alternatePostSecond'];
				if(array_key_exists('pre', $value))
					$string .= $value['pre'];
				$string .= $key;
				if(array_key_exists('post', $value))
					$string .= $value['post'];
				if(array_key_exists('dependentPre', $value))
				{
					$replace = "%" . $value['dependentPre'] . "%";
					if(array_key_exists('dependentPreAlternative', $value))
						$replace .= $value['dependentPreAlternative'] . "%";
					$string = str_replace("__DEPENDENT_ON_PREVIOUS_FIELD__", $replace, $string);
				}
				if(array_key_exists('dependentPost', $value))
				{
					$replace = "%" . $value['dependentPost'] . "%";
					if(array_key_exists('dependentPostAlternative', $value))
						$replace .= $value['dependentPostAlternative'] . "%";
					$string = str_replace("__DEPENDENT_ON_NEXT_FIELD__", $replace, $string);
				}
				if(array_key_exists('singular', $value) && array_key_exists('plural', $value))
				{
					$replace = "^" . $value['singular'] . "^" . $value['plural'] . "^";
					$string = str_replace("__SINGULAR_PLURAL__", $replace, $string);
				}
				$tempArray[$key] = $string;
				$fieldNames[$key] = $index;
				++$index;
			}
			if(!empty($tempArray))
			{
				foreach($alternates as $field => $altArray)
				{
					$alternateFound = 0;
					if(array_key_exists('preFirst', $altArray) && 
						array_key_exists($altArray['preFirst'], $tempArray))
					{
						$final = '$' . $tempArray[$altArray['preFirst']] . '$';
						unset($tempArray[$altArray['preFirst']]);
						$alternateFound = TRUE;
					}
					else
						$final = '$$';
					if(array_key_exists('preSecond', $altArray) && 
						array_key_exists($altArray['preSecond'], $tempArray))
					{
						$final .= $tempArray[$altArray['preSecond']] . '$';
						unset($tempArray[$altArray['preSecond']]);
						$alternateFound = TRUE;
					}
					else
						$final .= '$';
					if($alternateFound)
						array_splice($tempArray, $fieldNames[$field] + 1, 0, $final);
					$alternateFound = 0;
					if(array_key_exists('postFirst', $altArray) && 
						array_key_exists($altArray['postFirst'], $tempArray))
					{
						$final = '#' . $tempArray[$altArray['postFirst']] . '#';
						unset($tempArray[$altArray['postFirst']]);
						++$alternateFound;
					}
					else
						$final = '##';
					if(array_key_exists('postSecond', $altArray) && 
						array_key_exists($altArray['postSecond'], $tempArray))
					{
						$final .= $tempArray[$altArray['postSecond']] . '#';
						unset($tempArray[$altArray['postSecond']]);
						++$alternateFound;
					}
					else
						$final .= '#';
					if($alternateFound)
						array_splice($tempArray, $fieldNames[$field] - $alternateFound, 0, $final);
				}
				$tempArray = array_values($tempArray); // i.e. remove named keys.
			}
			if(!empty($independent))
			{
				$firstOfPair = FALSE;
				foreach($tempArray as $index => $value)
				{
					if(!$firstOfPair)
					{
						if(array_key_exists($index, $independent))
						{
							$newArray[] = $independent[$index] . '|' . $value;
//							$newArray[] = $value . '|' . $independent[$index];
							$firstOfPair = TRUE;
							continue;
						}
					}
					else
					{
						if(array_key_exists($index, $independent))
						{
							$newArray[] = $value . '|' . $independent[$index];
							$firstOfPair = FALSE;
							continue;
						}
					}
					$newArray[] = $value;
				}
			}
			else
				$newArray = $tempArray;
			$tempString = join('|', $newArray);
			if($ultimate && (substr($tempString, -1, 1) != $ultimate))
				$tempString .= '|' . $ultimate;
			if($preliminary)
				$tempString = $preliminary . '|' . $tempString;
			$this->$type = $tempString;
			if(!$footnote)
			{
				$partialName = 'partial_' . $type . 'Template';
				$this->$partialName = $partial;
				$partialReplaceName = 'partial_' . $type . 'Replace';
				$this->$partialReplaceName = $partialReplace;
			}
		}
	}
// Add resource-specific rewrite creator fields to session
	function writeSessionRewriteCreators($type, $array)
	{
		foreach($this->creators as $creatorField)
		{
			$name = $creatorField . "_firstString";
			if(array_key_exists($name, $array['_ATTRIBUTES']))
			{
				$sessionKey = 'style_' . $type . "_" . $name;
				$this->session->setVar($sessionKey, 
					base64_encode(htmlspecialchars($array['_ATTRIBUTES'][$name])));
			}
			$name = $creatorField . "_firstString_before";
			if(array_key_exists($name, $array['_ATTRIBUTES']))
			{
				$sessionKey = 'style_' . $type . "_" . $name;
				$this->session->setVar($sessionKey, 
					base64_encode(htmlspecialchars($array['_ATTRIBUTES'][$name])));
			}
			$name = $creatorField . "_remainderString";
			if(array_key_exists($name, $array['_ATTRIBUTES']))
			{
				$sessionKey = 'style_' . $type . "_" . $name;
				$this->session->setVar($sessionKey, 
					base64_encode(htmlspecialchars($array['_ATTRIBUTES'][$name])));
			}
			$name = $creatorField . "_remainderString_before";
			if(array_key_exists($name, $array['_ATTRIBUTES']))
			{
				$sessionKey = 'style_' . $type . "_" . $name;
				$this->session->setVar($sessionKey, 
					base64_encode(htmlspecialchars($array['_ATTRIBUTES'][$name])));
			}
			$name = $creatorField . "_remainderString_each";
			if(array_key_exists($name, $array['_ATTRIBUTES']))
			{
				$sessionKey = 'style_' . $type . "_" . $name;
				$this->session->setVar($sessionKey, 
					base64_encode(htmlspecialchars($array['_ATTRIBUTES'][$name])));
			}
		}
	}
// Edit groups
	function edit()
	{
		if($error = $this->validateInput('edit'))
			$this->badInput($error, 'editDisplay');
		$dirName = OSBIB_STYLE_DIR . "/" . strtolower(trim($this->vars['styleShortName']));
		$fileName = $dirName . "/" . strtoupper(trim($this->vars['styleShortName'])) . ".xml";
		$this->writeFile($fileName);
		$pString = $this->success->text("style", " " . $this->messages->text("misc", "edited") . " ");
		return $this->display($pString);
	}
// display groups for copying and making a new style
	function copyInit($error = FALSE)
	{
		$pString = MISC::h($this->messages->text("heading", "styles", 
			" (" . $this->messages->text("style", "copyLabel") . ")"), FALSE, 3);
		$pString .= FORM::formHeader("adminStyleCopyDisplay");
		$pString .= FORM::selectFBoxValue(FALSE, "editStyleFile", $this->styles, 20);
		$pString .= MISC::br() . FORM::formSubmit('Edit');
		$pString .= FORM::formEnd();
		return $pString;
	}
// Display a style for copying.
	function copyDisplay($error = FALSE)
	{
		if(!$error)
			$this->loadEditSession(TRUE);
		$pString = MISC::h($this->messages->text("heading", "styles", 
			" (" . $this->messages->text("style", "copyLabel") . ")"), FALSE, 3);
		if($error)
			$pString .= MISC::p($error, "error", "center");
		$pString .= $this->displayStyleForm('copy');
		return $pString;
	}
// display the citation templating form
	function displayCiteForm($type)
	{
		include_once(OSBIB__TABLE);
		include_once(OSBIB__STYLEMAP);
		$this->map = new STYLEMAP();
		$pString = MISC::h($this->messages->text("cite", "citationFormat") . " (" . 
			$this->messages->text("cite", "citationFormatInText") . ")");
// 1st., creator style
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$exampleName = array("Joe Bloggs", "Bloggs, Joe", "Bloggs Joe", 
			$this->messages->text("cite", "lastName"));
		$exampleInitials = array("T. U. ", "T.U.", "T U ", "TU");
		$example = array($this->messages->text("style", "creatorFirstNameFull"), 
			$this->messages->text("style", "creatorFirstNameInitials"));
		$firstStyle = base64_decode($this->session->getVar("cite_creatorStyle"));
		$otherStyle = base64_decode($this->session->getVar("cite_creatorOtherStyle"));
		$initials = base64_decode($this->session->getVar("cite_creatorInitials"));
		$firstName = base64_decode($this->session->getVar("cite_creatorFirstName"));
		$useInitials = base64_decode($this->session->getVar("cite_useInitials")) ? TRUE : FALSE;
		$td = MISC::b($this->messages->text("cite", "creatorStyle")) . MISC::br() . 
			FORM::selectedBoxValue($this->messages->text("style", "creatorFirstStyle"), 
			"cite_creatorStyle", $exampleName, $firstStyle, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorOthers"), 
			"cite_creatorOtherStyle", $exampleName, $otherStyle, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= $this->messages->text("cite", "useInitials") . ' ' . FORM::checkbox(FALSE, 
			"cite_useInitials", $useInitials);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorInitials"), 
			"cite_creatorInitials", $exampleInitials, $initials, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorFirstName"),
			"cite_creatorFirstName", $example, $firstName, 2);
		$uppercase = base64_decode($this->session->getVar("cite_creatorUppercase")) ? 
			TRUE : FALSE;
		$td .= MISC::P(FORM::checkbox($this->messages->text("style", "uppercaseCreator"), 
			"cite_creatorUppercase", $uppercase));
		$pString .= TABLE::td($td);
// Delimiters
		$twoCreatorsSep = stripslashes(base64_decode($this->session->getVar("cite_twoCreatorsSep")));
		$betweenFirst = stripslashes(base64_decode($this->session->getVar("cite_creatorSepFirstBetween")));
		$betweenNext = stripslashes(base64_decode($this->session->getVar("cite_creatorSepNextBetween")));
		$last = stripslashes(base64_decode($this->session->getVar("cite_creatorSepNextLast")));
		$td = MISC::b($this->messages->text("cite", "creatorSep")) . 
			MISC::p($this->messages->text("style", "ifOnlyTwoCreators") . "&nbsp;" . 
			FORM::textInput(FALSE, "cite_twoCreatorsSep", $twoCreatorsSep, 7, 255)) . 
			$this->messages->text("style", "sepCreatorsFirst") . "&nbsp;" . 
			FORM::textInput(FALSE, "cite_creatorSepFirstBetween", 
				$betweenFirst, 7, 255) . MISC::br() . 
			MISC::p($this->messages->text("style", "sepCreatorsNext") . MISC::br() . 
			$this->messages->text("style", "creatorSepBetween") . "&nbsp;" . 
			FORM::textInput(FALSE, "cite_creatorSepNextBetween", $betweenNext, 7, 255) . 
			$this->messages->text("style", "creatorSepLast") . "&nbsp;" . 
			FORM::textInput(FALSE, "cite_creatorSepNextLast", $last, 7, 255));
		$td .= MISC::br() . "&nbsp;" . MISC::br();
// List abbreviation
		$example = array($this->messages->text("style", "creatorListFull"), 
			$this->messages->text("style", "creatorListLimit"));
		$list = base64_decode($this->session->getVar("cite_creatorList"));
		$listMore = stripslashes(base64_decode($this->session->getVar("cite_creatorListMore")));
		$listLimit = stripslashes(base64_decode($this->session->getVar("cite_creatorListLimit")));
		$listAbbreviation = stripslashes(base64_decode($this->session->getVar("cite_creatorListAbbreviation")));
		$italic = base64_decode($this->session->getVar("cite_creatorListAbbreviationItalic")) ? 
			TRUE : FALSE;
		$td .= MISC::b($this->messages->text("cite", "creatorList")) . 
			MISC::p(FORM::selectedBoxValue(FALSE, 
			"cite_creatorList", $example, $list, 2) . MISC::br() . 
			$this->messages->text("style", "creatorListIf") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListMore", $listMore, 3) . 
			$this->messages->text("style", "creatorListOrMore") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListLimit", $listLimit, 3) . MISC::br() . 
			$this->messages->text("style", "creatorListAbbreviation") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListAbbreviation", $listAbbreviation, 15) . ' ' . 
			FORM::checkbox(FALSE, "cite_creatorListAbbreviationItalic", $italic) . ' ' . 
			$this->messages->text("style", "italics"));
		$list = base64_decode($this->session->getVar("cite_creatorListSubsequent"));
		$listMore = stripslashes(base64_decode($this->session->getVar("cite_creatorListSubsequentMore")));
		$listLimit = stripslashes(base64_decode($this->session->getVar("cite_creatorListSubsequentLimit")));
		$listAbbreviation = stripslashes(base64_decode(
			$this->session->getVar("cite_creatorListSubsequentAbbreviation")));
		$italic = base64_decode($this->session->getVar("cite_creatorListSubsequentAbbreviationItalic")) ? 
			TRUE : FALSE;
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= MISC::b($this->messages->text("cite", "creatorListSubsequent")) . 
			MISC::p(FORM::selectedBoxValue(FALSE, 
			"cite_creatorListSubsequent", $example, $list, 2) . MISC::br() . 
			$this->messages->text("style", "creatorListIf") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListSubsequentMore", $listMore, 3) . 
			$this->messages->text("style", "creatorListOrMore") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListSubsequentLimit", $listLimit, 3) . MISC::br() . 
			$this->messages->text("style", "creatorListAbbreviation") . ' ' . 
			FORM::textInput(FALSE, "cite_creatorListSubsequentAbbreviation", $listAbbreviation, 15) . ' ' . 
			FORM::checkbox(FALSE, "cite_creatorListSubsequentAbbreviationItalic", $italic) . ' ' . 
			$this->messages->text("style", "italics"));
		$pString .= TABLE::td($td, FALSE, FALSE, "top");
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
// Miscellaneous citation formatting
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();

		$firstChars = stripslashes(base64_decode($this->session->getVar("cite_firstChars")));
		$template = stripslashes(base64_decode($this->session->getVar("cite_template")));
		$lastChars = stripslashes(base64_decode($this->session->getVar("cite_lastChars")));
		$td = $this->messages->text("cite", "enclosingCharacters") . MISC::br() . 
			FORM::textInput(FALSE, "cite_firstChars", $firstChars, 3, 255) . ' ... ' . 
			FORM::textInput(FALSE, "cite_lastChars", $lastChars, 3, 255);
		$td .= MISC::br() . "&nbsp;" . MISC::br();

		$availableFields = join(', ', $this->map->citation);
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_template", $template, 40, 255) . 
			" " . MISC::span('*', 'required') . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");

		$replaceYear = stripslashes(base64_decode($this->session->getVar("cite_replaceYear")));
		$td .= MISC::p(FORM::textInput($this->messages->text("cite", "replaceYear"), 
			"cite_replaceYear", $replaceYear, 10, 255));

		$td .= $this->messages->text("cite", "followCreatorTemplate");
		$template = stripslashes(base64_decode($this->session->getVar("cite_followCreatorTemplate")));
		$td .= MISC::p($this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_followCreatorTemplate", $template, 40, 255)) . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");

		$pageSplit = base64_decode($this->session->getVar("cite_followCreatorPageSplit")) ? 
			TRUE : FALSE;
		$td .= MISC::P($this->messages->text("cite", "followCreatorPageSplit") . "&nbsp;&nbsp;" . 
			FORM::checkbox(FALSE, "cite_followCreatorPageSplit", $pageSplit));

		$consecutiveSep = stripslashes(base64_decode($this->session->getVar("cite_consecutiveCitationSep")));
		$td .= MISC::p($this->messages->text("cite", "consecutiveCitationSep") . ' ' . 
			FORM::textInput(FALSE, "cite_consecutiveCitationSep", $consecutiveSep, 7));

// Consecutive citations by same author(s)
		$consecutiveSep = stripslashes(base64_decode($this->session->getVar("cite_consecutiveCreatorSep")));
		$template = stripslashes(base64_decode($this->session->getVar("cite_consecutiveCreatorTemplate")));
		$availableFields = join(', ', $this->map->citation);
		$td .= MISC::p($this->messages->text("cite", "consecutiveCreator"));
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_consecutiveCreatorTemplate", $template, 40, 255) . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");
		$td .= $this->messages->text("cite", "consecutiveCreatorSep") . ' ' . 
			FORM::textInput(FALSE, "cite_consecutiveCreatorSep", $consecutiveSep, 7);

// Subsequent citations by same author(s)
		$template = stripslashes(base64_decode($this->session->getVar("cite_subsequentCreatorTemplate")));
		$td .= MISC::p($this->messages->text("cite", "subsequentCreator"));
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_subsequentCreatorTemplate", $template, 40, 255) . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");
			
		$fields = base64_decode($this->session->getVar("cite_subsequentFields")) ? 
			TRUE : FALSE;
		$td .= MISC::P($this->messages->text("cite", "subsequentFields") . "&nbsp;&nbsp;" . 
			FORM::checkbox(FALSE, "cite_subsequentFields", $fields));

		$example = array($this->messages->text("cite", "subsequentCreatorRange1"), 
			$this->messages->text("cite", "subsequentCreatorRange2"));
		$input = base64_decode($this->session->getVar("cite_subsequentCreatorRange"));
		$td .= FORM::selectedBoxValue($this->messages->text("cite", "subsequentCreatorRange"), 
			"cite_subsequentCreatorRange", $example, $input, 2);
		$pString .= TABLE::td($td, FALSE, FALSE, "top");
		
		$example = array("132-9", "132-39", "132-139");
		$input = base64_decode($this->session->getVar("cite_pageFormat"));
		$td = FORM::selectedBoxValue($this->messages->text("style", "pageFormat"), 
			"cite_pageFormat", $example, $input, 3);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$example = array("1998", "'98", "98");
		$year = base64_decode($this->session->getVar("cite_yearFormat"));
		$td .= FORM::selectedBoxValue($this->messages->text("cite", "yearFormat"), 
			"cite_yearFormat", $example, $year, 3);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$example = array($this->messages->text("style", "titleAsEntered"), 
			"Wikindx bibliographic management system");
		$titleCapitalization = base64_decode($this->session->getVar("cite_titleCapitalization"));
		$td .= MISC::p($this->messages->text("style", "titleCapitalization") . MISC::br() .
			FORM::selectedBoxValue(FALSE, "cite_titleCapitalization", $example, $titleCapitalization, 2));
		$separator = base64_decode($this->session->getVar("cite_titleSubtitleSeparator"));
		$td .= MISC::p($this->messages->text("style", "titleSubtitleSeparator") . ":&nbsp;&nbsp;" . 
			FORM::textInput(FALSE, "cite_titleSubtitleSeparator", $separator, 4));

// Ambiguous citations
		$ambiguous = base64_decode($this->session->getVar("cite_ambiguous"));
		$example = array($this->messages->text("cite", "ambiguousUnchanged"), 
			$this->messages->text("cite", "ambiguousYear"), $this->messages->text("cite", "ambiguousTitle"));
		$template = stripslashes(base64_decode($this->session->getVar("cite_ambiguousTemplate")));
		$td .= MISC::p(FORM::selectedBoxValue(MISC::b($this->messages->text("cite", "ambiguous")), 
			"cite_ambiguous", $example, $ambiguous, 3));
		$availableFields = join(', ', $this->map->citation);
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_ambiguousTemplate", $template, 40, 255) . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");
		
		$removeTitle = base64_decode($this->session->getVar("cite_removeTitle")) ? 
			TRUE : FALSE;
		$td .= MISC::P($this->messages->text("cite", "removeTitle") . "&nbsp;&nbsp;" . 
			FORM::checkbox(FALSE, "cite_removeTitle", $removeTitle));
			
		$td .= MISC::p(MISC::a("link linkCiteHidden", "preview", 
				"javascript:openPopUpCitePreview('index.php?action=previewCite')"));
		$pString .= TABLE::td($td, FALSE, FALSE, "top");
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		
// Endnote style citations
		$pString .= MISC::h($this->messages->text("cite", "citationFormat") . " (" . 
			$this->messages->text("cite", "citationFormatEndnote") . ")");
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$td = MISC::p(MISC::b($this->messages->text("cite", "endnoteFormat1")));
		$firstChars = stripslashes(base64_decode($this->session->getVar("cite_firstCharsEndnoteInText")));
		$lastChars = stripslashes(base64_decode($this->session->getVar("cite_lastCharsEndnoteInText")));
		$td .= $this->messages->text("cite", "enclosingCharacters") . MISC::br() . 
			FORM::textInput(FALSE, "cite_firstCharsEndnoteInText", $firstChars, 3, 255) . ' ... ' . 
			FORM::textInput(FALSE, "cite_lastCharsEndnoteInText", $lastChars, 3, 255);
		$td .= MISC::br() . "&nbsp;" . MISC::br();

		$template = stripslashes(base64_decode($this->session->getVar("cite_templateEndnoteInText")));
		$availableFields = join(', ', $this->map->citationEndnoteInText);
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_templateEndnoteInText", $template, 40, 255) . 
			" " . MISC::span('*', 'required') . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");
			
		$citeFormat = array($this->messages->text("cite", "normal"), 
			$this->messages->text("cite", "superscript"), $this->messages->text("cite", "subscript"));
		$input = base64_decode($this->session->getVar("cite_formatEndnoteInText"));
		$td .= MISC::p(FORM::selectedBoxValue(FALSE, "cite_formatEndnoteInText", $citeFormat, $input, 3));
		
		$consecutiveSep = stripslashes(base64_decode(
			$this->session->getVar("cite_consecutiveCitationEndnoteInTextSep")));
		$td .= MISC::p($this->messages->text("cite", "consecutiveCitationSep") . ' ' . 
			FORM::textInput(FALSE, "cite_consecutiveCitationEndnoteInTextSep", $consecutiveSep, 7));

		$endnoteStyleArray = array($this->messages->text("cite", "endnoteStyle1"), 
			$this->messages->text("cite", "endnoteStyle2"), $this->messages->text("cite", "endnoteStyle3"));
		$endnoteStyle = base64_decode($this->session->getVar("cite_endnoteStyle"));
		$td .= MISC::p(FORM::selectedBoxValue($this->messages->text("cite", "endnoteStyle"), 
			"cite_endnoteStyle", $endnoteStyleArray, $endnoteStyle, 3));
		
		$pString .= TABLE::td($td);
		
		$td = MISC::p(MISC::b($this->messages->text("cite", "endnoteFormat2")));
		$td .= MISC::p($this->messages->text("cite", "endnoteFieldFormat"), "small");
		$template = stripslashes(base64_decode($this->session->getVar("cite_templateEndnote")));
		$availableFields = join(', ', $this->map->citationEndnote);
		$td .= $this->messages->text("cite", "template") . ' ' . 
			FORM::textInput(FALSE, "cite_templateEndnote", $template, 40, 255) . " " . 
			MISC::span('*', 'required') . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");
			
		$availableFields = join(', ', $this->map->citationEndnote);
		$ibid = stripslashes(base64_decode($this->session->getVar("cite_ibid")));
		$td .= FORM::textInput($this->messages->text("cite", "ibid"), "cite_ibid", $ibid, 40, 255);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$idem = stripslashes(base64_decode($this->session->getVar("cite_idem")));
		$td .= FORM::textInput($this->messages->text("cite", "idem"), "cite_idem", $idem, 40, 255);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$opCit = stripslashes(base64_decode($this->session->getVar("cite_opCit")));
		$td .= FORM::textInput($this->messages->text("cite", "opCit"), "cite_opCit", $opCit, 40, 255) . 
			MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
			MISC::br() . $availableFields, "small");

		$firstChars = stripslashes(base64_decode($this->session->getVar("cite_firstCharsEndnoteID")));
		$lastChars = stripslashes(base64_decode($this->session->getVar("cite_lastCharsEndnoteID")));
		$td .= MISC::p($this->messages->text("cite", "endnoteIDEnclose") . MISC::br() . 
			FORM::textInput(FALSE, "cite_firstCharsEndnoteID", $firstChars, 3, 255) . ' ... ' . 
			FORM::textInput(FALSE, "cite_lastCharsEndnoteID", $lastChars, 3, 255));
			
		$input = base64_decode($this->session->getVar("cite_formatEndnoteID"));
		$td .= MISC::p(FORM::selectedBoxValue(FALSE, "cite_formatEndnoteID", $citeFormat, $input, 3));
		
		$pString .= TABLE::td($td);
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		
// Creator formatting for footnotes
		$pString .= MISC::h($this->messages->text("cite", "citationFormatFootnote"));
		$pString .= $this->creatorFormatting("footnote", TRUE);

// bibliography order
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::h($this->messages->text("cite", "orderBib1"));
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$heading = MISC::p($this->messages->text("cite", "orderBib2"));
		$sameIdOrderBib = base64_decode($this->session->getVar("cite_sameIdOrderBib")) ? TRUE : FALSE;
		$heading .= MISC::P($this->messages->text("cite", "orderBib3") . "&nbsp;&nbsp;" . 
			FORM::checkbox(FALSE, "cite_sameIdOrderBib", $sameIdOrderBib));
		$order1 = base64_decode($this->session->getVar("cite_order1"));
		$order2 = base64_decode($this->session->getVar("cite_order2"));
		$order3 = base64_decode($this->session->getVar("cite_order3"));
		$radio = !base64_decode($this->session->getVar("cite_order1desc")) ? 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order1desc", 0, TRUE) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order1desc", 1) : 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order1desc", 0) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order1desc", 1, TRUE);
		$orderArray = array($this->messages->text("list", "creator"), 
			$this->messages->text("list", "year"), $this->messages->text("list", "title"));
		$pString .= TABLE::td($heading . FORM::selectedBoxValue($this->messages->text("powerSearch", "order1"), 
			"cite_order1", $orderArray, $order1, 3) . MISC::p($radio));
		$radio = !base64_decode($this->session->getVar("cite_order2desc")) ? 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order2desc", 0, TRUE) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order2desc", 1) : 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order2desc", 0) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order2desc", 1, TRUE);
		$pString .= TABLE::td(FORM::selectedBoxValue($this->messages->text("powerSearch", "order2"), 
			"cite_order2", $orderArray, $order2, 3) . MISC::p($radio), FALSE, FALSE, "bottom");
		$radio = !base64_decode($this->session->getVar("cite_order3desc")) ? 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order3desc", 0, TRUE) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order3desc", 1) : 
			$this->messages->text("powerSearch", "ascending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order3desc", 0) . MISC::br() . 
			$this->messages->text("powerSearch", "descending") . "&nbsp;&nbsp;" . 
			FORM::radioButton(FALSE, "cite_order3desc", 1, TRUE);
		$pString .= TABLE::td(FORM::selectedBoxValue($this->messages->text("powerSearch", "order3"), 
			"cite_order3", $orderArray, $order3, 3) . MISC::p($radio), FALSE, FALSE, "bottom");
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		return $pString;
	}
// display the style form for both adding and editing
	function displayStyleForm($type)
	{
		include_once(OSBIB__TABLE);
		include_once(OSBIB__STYLEMAP);
		$this->map = new STYLEMAP();
		$types = array_keys($this->map->types);
		if($type == 'add')
			$pString = FORM::formHeader("adminStyleAdd");
		else if($type == 'edit')
			$pString = FORM::formHeader("adminStyleEdit");
		else // copy
			$pString = FORM::formHeader("adminStyleAdd");
		$pString .= TABLE::tableStart();
		$pString .= TABLE::trStart();
		$input = stripslashes($this->session->getVar("style_shortName"));
		if($type == 'add')
			$pString .= TABLE::td(FORM::textInput($this->messages->text("style", "shortName"), 
				"styleShortName", $input, 20, 255) . " " . MISC::span('*', 'required') . 
				MISC::br() . $this->messages->text("hint", "styleShortName"));
		else if($type == 'edit')
			$pString .= FORM::hidden("editStyleFile", $this->vars['editStyleFile']) . 
				FORM::hidden("styleShortName", $input) . 
				TABLE::td(MISC::b($this->vars['editStyleFile'] . ":&nbsp;&nbsp;"), 
				FALSE, FALSE, "top");
		else // copy
			$pString .= TABLE::td(FORM::textInput($this->messages->text("style", "shortName"), 
				"styleShortName", $input, 20, 255) . " " . MISC::span('*', 'required') . 
				MISC::br() . $this->messages->text("hint", "styleShortName"));
		$input = stripslashes(base64_decode($this->session->getVar("style_longName")));
		$pString .= TABLE::td(FORM::textInput($this->messages->text("style", "longName"), 
			"styleLongName", $input, 50, 255) . " " . MISC::span('*', 'required'));			
		$input = base64_decode($this->session->getVar("cite_citationStyle"));
		$example = array($this->messages->text("cite", "citationFormatInText"), 
			$this->messages->text("cite", "citationFormatEndnote"));
		$pString .= TABLE::td(FORM::selectedBoxValue($this->messages->text("cite", "citationFormat"), 
			"cite_citationStyle", $example, $input, 2) . " " . MISC::span('*', 'required'));
		
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::p(MISC::hr());
		$pString .= $this->displayCiteForm('copy');
		$pString .= MISC::p(MISC::hr() . MISC::hr());
		$pString .= MISC::h($this->messages->text("style", "bibFormat"));
		
// Creator formatting for bibliography
		$pString .= $this->creatorFormatting("style");
// Editor replacements
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$switch = base64_decode($this->session->getVar("style_editorSwitch"));
		$editorSwitchIfYes = stripslashes(base64_decode($this->session->getVar("style_editorSwitchIfYes")));
		$example = array($this->messages->text("style", "no"), $this->messages->text("style", "yes"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "editorSwitchHead")) . MISC::br() . 
			FORM::selectedBoxValue($this->messages->text("style", "editorSwitch"), 
			"style_editorSwitch", $example, $switch, 2));
		$pString .= TABLE::td(
			FORM::textInput($this->messages->text("style", "editorSwitchIfYes"), 
			"style_editorSwitchIfYes", $editorSwitchIfYes, 30, 255), FALSE, FALSE, "bottom");
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		
// Title capitalization, edition, day and month, runningTime and page formats
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$example = array($this->messages->text("style", "titleAsEntered"), 
			"Wikindx bibliographic management system");
		$input = base64_decode($this->session->getVar("style_titleCapitalization"));
		$td = MISC::p(MISC::b($this->messages->text("style", "titleCapitalization")) . MISC::br() .
			FORM::selectedBoxValue(FALSE, "style_titleCapitalization", $example, $input, 2));
		$input = base64_decode($this->session->getVar("style_titleSubtitleSeparator"));
		$td .= MISC::p($this->messages->text("style", "titleSubtitleSeparator") . ":&nbsp;&nbsp;" . 
			FORM::textInput(FALSE, "style_titleSubtitleSeparator", $input, 4));
		$pString .= TABLE::td($td);
		$example = array("3", "3.", "3rd");
		$input = base64_decode($this->session->getVar("style_editionFormat"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "editionFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_editionFormat", $example, $input, 3));
		$example = array("132-9", "132-39", "132-139");
		$input = base64_decode($this->session->getVar("style_pageFormat"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "pageFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_pageFormat", $example, $input, 3));
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$example = array("10", "10.", "10th");
		$input = base64_decode($this->session->getVar("style_dayFormat"));
		$leadingZero = base64_decode($this->session->getVar("style_dayLeadingZero")) ? 
			TRUE : FALSE;
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "dayFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_dayFormat", $example, $input, 3) . 
			MISC::P(FORM::checkbox($this->messages->text("style", "dayLeadingZero"), 
			"style_dayLeadingZero", $leadingZero)));
			
		$example = array("Feb", "February", $this->messages->text("style", "userMonthSelect"));
		$input = base64_decode($this->session->getVar("style_monthFormat"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "monthFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_monthFormat", $example, $input, 3));
		$example = array("Day Month", "Month Day");
		$input = base64_decode($this->session->getVar("style_dateFormat"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "dateFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_dateFormat", $example, $input, 2));
			
		$input = base64_decode($this->session->getVar("style_dateMonthNoDay"));
		$inputString = stripslashes(base64_decode($this->session->getVar("style_dateMonthNoDayString")));
		$example = array($this->messages->text("style", "dateMonthNoDay1"), 
			$this->messages->text("style", "dateMonthNoDay2"));
		$pString .= TABLE::td(FORM::selectedBoxValue($this->messages->text("style", "dateMonthNoDay"),
			"style_dateMonthNoDay", $example, $input, 2) . MISC::br() . 
			FORM::textInput(FALSE, "style_dateMonthNoDayString", $inputString, 30, 255) . MISC::br() . 
			MISC::span($this->messages->text("style", "dateMonthNoDayHint"), 'hint'));
		
		$pString .= TABLE::trEnd();
		$pString .= TABLE::trStart();
		$monthString = '';	
		for($i = 1; $i <= 12; $i++)
		{
			$input = stripslashes(base64_decode($this->session->getVar("style_userMonth_$i")));
			if($i == 7)
				$monthString .= MISC::br() . "$i:&nbsp;&nbsp;" . 
				FORM::textInput(FALSE, "style_userMonth_$i", $input, 15, 255);
			else
				$monthString .= "$i:&nbsp;&nbsp;" . 
				FORM::textInput(FALSE, "style_userMonth_$i", $input, 15, 255);
		}
		$pString .= TABLE::td($this->messages->text("style", "userMonths") . MISC::br() . 
			$monthString, FALSE, FALSE, FALSE, 5);
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
	
// Date range formatting
		$pString .= MISC::b($this->messages->text("style", "dateRange")) . MISC::br();
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$input = stripslashes(base64_decode($this->session->getVar("style_dateRangeDelimit1")));
		$input = stripslashes(base64_decode($this->session->getVar("style_dateRangeDelimit1")));
		$pString .= TABLE::td(FORM::textInput($this->messages->text("style", "dateRangeDelimit1"), 
			"style_dateRangeDelimit1", $input, 6, 255));
		$input = base64_decode($this->session->getVar("style_dateRangeDelimit2"));
		$pString .= TABLE::td(FORM::textInput($this->messages->text("style", "dateRangeDelimit2"), 
			"style_dateRangeDelimit2", $input, 6, 255));
		$pString .= TABLE::trEnd();
		$pString .= TABLE::trStart();
		$input = base64_decode($this->session->getVar("style_dateRangeSameMonth"));
		$example = array($this->messages->text("style", "dateRangeSameMonth1"), 
			$this->messages->text("style", "dateRangeSameMonth2"));
		$pString .= TABLE::td(FORM::selectedBoxValue($this->messages->text("style", "dateRangeSameMonth"),
			"style_dateRangeSameMonth", $example, $input, 2), FALSE, FALSE, FALSE, 2);
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		
		$pString .= TABLE::tableStart("styleTable", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$example = array("3'45\"", "3:45", "3,45", "3 hours, 45 minutes", "3 hours and 45 minutes");
		$input = base64_decode($this->session->getVar("style_runningTimeFormat"));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "runningTimeFormat")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, "style_runningTimeFormat", $example, $input, 5));
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . MISC::hr() . MISC::br();

// print some basic advice
		$pString .= MISC::p(
			$this->messages->text("style", "templateHelp1") . 
			MISC::br() . $this->messages->text("style", "templateHelp2") . 
			MISC::br() . $this->messages->text("style", "templateHelp3") . 
			MISC::br() . $this->messages->text("style", "templateHelp4") . 
			MISC::br() . $this->messages->text("style", "templateHelp5") . 
			MISC::br() . $this->messages->text("style", "templateHelp6")
			, "small");

		$generic = array("genericBook" => $this->messages->text("resourceType", "genericBook"), 
			"genericArticle" => $this->messages->text("resourceType", "genericArticle"), 
			"genericMisc" => $this->messages->text("resourceType", "genericMisc"));
		$availableFieldsCitation = join(', ', $this->map->citation);
// Resource types
		foreach($types as $key)
		{
			if(($key == 'genericBook') || ($key == 'genericArticle') || ($key == 'genericMisc'))
			{
				$required = MISC::span('*', 'required');
				$fallback = FALSE;
				$citationString = FALSE;
				$formElementName = FALSE;
			}
			else
			{
				$required = FALSE;
				$formElementName = "style_" . $key . "_generic";
				$input = $this->session->issetVar($formElementName) ? 
					base64_decode($this->session->getVar($formElementName)) : "genericMisc";
				$fallback = FORM::selectedBoxValue($this->messages->text("style", "fallback"), 
					$formElementName, $generic, $input, 3);
// Replacement citation template for in-text citation for this type
				$citationStringName = "cite_" . $key . "Template";
				$citationNotInBibliography = "cite_" . $key . "_notInBibliography";
				$input = stripslashes(base64_decode($this->session->getVar($citationStringName)));
				$notAdd = base64_decode($this->session->getVar($citationNotInBibliography)) ? TRUE : FALSE;
				$checkBox = "&nbsp;&nbsp;" . $this->messages->text("cite", "notInBibliography") . 
				"&nbsp;" . FORM::checkbox(FALSE, $citationNotInBibliography, $notAdd);
				$citationString = MISC::p(FORM::textInput($this->messages->text("cite", "typeReplace"), 
					$citationStringName, $input, 60, 255) . $checkBox . MISC::br() . 
					MISC::i($this->messages->text("style", "availableFields")) . 
					MISC::br() . $availableFieldsCitation, "small");
			}
			$keyName = 'style_' . $key;
			$partialTemplateName = "partial_" . $key . "Template";
			$partialReplaceName = "partial_" . $key . "Replace";
			$partialReplace = base64_decode($this->session->getVar($partialReplaceName)) ? TRUE : FALSE;
			$partialReplaceString = $this->messages->text("style", "partialReplace") . ":&nbsp;&nbsp;" . 
				FORM::checkbox(FALSE, $partialReplaceName, $partialReplace);
			$input = stripslashes(base64_decode($this->session->getVar($partialTemplateName)));
			$partialTemplate = MISC::p(FORM::textInput($this->messages->text("style", "partialTemplate"), 
					$partialTemplateName, $input, 50, 255) . MISC::br() . $partialReplaceString);
			$previewFootnote = MISC::a("link linkCiteHidden", "preview", 
				"javascript:openPopUpFootnotePreview('index.php?action=previewStyle', 
				'$key', '$formElementName')");
// Footnote template
			$footnoteTemplateName = "footnote_" . $key . "Template";
			$input = stripslashes(base64_decode($this->session->getVar($footnoteTemplateName)));
			$footnoteTemplate = MISC::p(FORM::textareaInput($this->messages->text("cite", "footnoteTemplate"), 
				$footnoteTemplateName, $input, 80, 3) . $previewFootnote );
			$rewriteCreatorString = $this->rewriteCreators($key, $this->map->$key);
			$pString .= MISC::br() . MISC::hr() . MISC::br();
			$pString .= TABLE::tableStart();
			$pString .= TABLE::trStart();
			$previewStyle = MISC::a("link linkCiteHidden", "preview", 
				"javascript:openPopUpStylePreview('index.php?action=previewStyle', 
				'$keyName', '$formElementName')");
			$input = stripslashes(base64_decode($this->session->getVar($keyName)));
			$heading = MISC::b($this->messages->text("resourceType", $key)) . MISC::br() . 
				$this->messages->text("style", "bibTemplate") . $required;
			$pString .= TABLE::td(FORM::textareaInput($heading, 
				$keyName, $input, 80, 3) . $previewStyle . $footnoteTemplate . $partialTemplate . 
				$rewriteCreatorString . $citationString);
// List available fields for this type
			$availableFields = join(', ', array_values($this->map->$key));
// If 'pages' not in field list, add for field footnotes
			if(array_search('pages', $this->map->$key) === FALSE)
				$availableFields .= ', ' . $this->messages->text("style", "footnotePageField");
			$pString .= TABLE::td(MISC::p(MISC::i($this->messages->text("style", "availableFields")) . 
				MISC::br() . $availableFields . MISC::br() .  
				$this->messages->text("hint", "caseSensitive"), "small") . MISC::p($fallback));
			$pString .= TABLE::trEnd();
			$pString .= TABLE::tableEnd();
			$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		}
		if(($type == 'add') || ($type == 'copy'))
			$pString .= MISC::p(FORM::formSubmit('Add'));
		else
			$pString .= MISC::p(FORM::formSubmit('Edit'));
		$pString .= FORM::formEnd();
		return $pString;
	}
// display creator formatting options for bibliographies and footnotes
	function creatorFormatting($prefix, $footnote = FALSE)
	{
// Display general options for creator limits, formats etc.
// 1st., creator style
		$pString = TABLE::tableStart($prefix . "Table", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$exampleName = array("Joe Bloggs", "Bloggs, Joe", "Bloggs Joe", 
			$this->messages->text("cite", "lastName"));
		$exampleInitials = array("T. U. ", "T.U.", "T U ", "TU");
		$example = array($this->messages->text("style", "creatorFirstNameFull"), 
			$this->messages->text("style", "creatorFirstNameInitials"));
		$firstStyle = base64_decode($this->session->getVar($prefix . "_primaryCreatorFirstStyle"));
		$otherStyle = base64_decode($this->session->getVar($prefix . "_primaryCreatorOtherStyle"));
		$initials = base64_decode($this->session->getVar($prefix . "_primaryCreatorInitials"));
		$firstName = base64_decode($this->session->getVar($prefix . "_primaryCreatorFirstName"));
		$td = MISC::b($this->messages->text("style", "primaryCreatorStyle")) . MISC::br() . 
			FORM::selectedBoxValue($this->messages->text("style", "creatorFirstStyle"), 
			$prefix . "_primaryCreatorFirstStyle", $exampleName, $firstStyle, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorOthers"), 
			$prefix . "_primaryCreatorOtherStyle", $exampleName, $otherStyle, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorInitials"), 
			$prefix . "_primaryCreatorInitials", $exampleInitials, $initials, 4);
		$td .= MISC::br() . "&nbsp;" . MISC::br();
		$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorFirstName"),
			$prefix . "_primaryCreatorFirstName", $example, $firstName, 2);
		$uppercase = base64_decode($this->session->getVar($prefix . "_primaryCreatorUppercase")) ? 
			TRUE : FALSE;
		$td .= MISC::P(FORM::checkbox($this->messages->text("style", "uppercaseCreator"), 
			$prefix . "_primaryCreatorUppercase", $uppercase));
		$repeat = base64_decode($this->session->getVar($prefix . "_primaryCreatorRepeat"));
		$exampleRepeat = array($this->messages->text("style", "repeatCreators1"), 
			$this->messages->text("style", "repeatCreators2"), 
			$this->messages->text("style", "repeatCreators3"));
		$td .= FORM::selectedBoxValue($this->messages->text("style", "repeatCreators"), 
			$prefix . "_primaryCreatorRepeat", $exampleRepeat, $repeat, 3) . MISC::br();
		$repeatString = stripslashes(base64_decode(
			$this->session->getVar($prefix . "_primaryCreatorRepeatString")));
		$td .= FORM::textInput(FALSE, $prefix . "_primaryCreatorRepeatString", $repeatString, 15, 255);
		$pString .= TABLE::td($td);
//		if(!$footnote)
//		{
// Other creators (editors, translators etc.)
			$firstStyle = base64_decode($this->session->getVar($prefix . "_otherCreatorFirstStyle"));
			$otherStyle = base64_decode($this->session->getVar($prefix . "_otherCreatorOtherStyle"));
			$initials = base64_decode($this->session->getVar($prefix . "_otherCreatorInitials"));
			$firstName = base64_decode($this->session->getVar($prefix . "_otherCreatorFirstName"));
			$td = MISC::b($this->messages->text("style", "otherCreatorStyle")) . MISC::br() . 
				FORM::selectedBoxValue($this->messages->text("style", "creatorFirstStyle"), 
				$prefix . "_otherCreatorFirstStyle", $exampleName, $firstStyle, 4);
			$td .= MISC::br() . "&nbsp;" . MISC::br();
			$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorOthers"), 
				$prefix . "_otherCreatorOtherStyle", $exampleName, $otherStyle, 4);
			$td .= MISC::br() . "&nbsp;" . MISC::br();
			$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorInitials"), 
				$prefix . "_otherCreatorInitials", $exampleInitials, $initials, 4);
			$td .= MISC::br() . "&nbsp;" . MISC::br();
			$td .= FORM::selectedBoxValue($this->messages->text("style", "creatorFirstName"),
				$prefix . "_otherCreatorFirstName", $example, $firstName, 2);
			$uppercase = base64_decode($this->session->getVar($prefix . "_otherCreatorUppercase")) ? 
				TRUE : FALSE;
			$td .= MISC::P(FORM::checkbox($this->messages->text("style", "uppercaseCreator"), 
				$prefix . "_otherCreatorUppercase", $uppercase));
			$pString .= TABLE::td($td);
//		}
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		
// 2nd., creator delimiters
		$pString .= TABLE::tableStart($prefix . "Table", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$twoCreatorsSep = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_primaryTwoCreatorsSep")));
		$betweenFirst = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_primaryCreatorSepFirstBetween")));
		$betweenNext = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_primaryCreatorSepNextBetween")));
		$last = stripslashes(base64_decode($this->session->getVar($prefix . "_primaryCreatorSepNextLast")));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "primaryCreatorSep")) . 
			MISC::p($this->messages->text("style", "ifOnlyTwoCreators") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_primaryTwoCreatorsSep", $twoCreatorsSep, 7, 255)) . 
			$this->messages->text("style", "sepCreatorsFirst") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorSepFirstBetween", $betweenFirst, 7, 255) . 
			MISC::br() . MISC::p($this->messages->text("style", "sepCreatorsNext") . MISC::br() . 
			$this->messages->text("style", "creatorSepBetween") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorSepNextBetween", $betweenNext, 7, 255) . 
			$this->messages->text("style", "creatorSepLast") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorSepNextLast", $last, 7, 255)), 
			FALSE, FALSE, "bottom");
		$twoCreatorsSep = stripslashes(base64_decode($this->session->getVar($prefix . "_otherTwoCreatorsSep")));
		$betweenFirst = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_otherCreatorSepFirstBetween")));
		$betweenNext = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_otherCreatorSepNextBetween")));
		$last = stripslashes(base64_decode($this->session->getVar($prefix . "_otherCreatorSepNextLast")));
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "otherCreatorSep")) . 
			MISC::p($this->messages->text("style", "ifOnlyTwoCreators") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_otherTwoCreatorsSep", $twoCreatorsSep, 7, 255)) . 
			$this->messages->text("style", "sepCreatorsFirst") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorSepFirstBetween", $betweenFirst, 7, 255) .
			MISC::p($this->messages->text("style", "sepCreatorsNext") . MISC::br() . 
			$this->messages->text("style", "creatorSepBetween") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorSepNextBetween", $betweenNext, 7, 255) . 
			$this->messages->text("style", "creatorSepLast") . "&nbsp;" . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorSepNextLast", $last, 7, 255)), 
			FALSE, FALSE, "bottom");
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		
// 3rd., creator list limits
		$pString .= TABLE::tableStart($prefix . "Table", 1, FALSE, 5);
		$pString .= TABLE::trStart();
		$example = array($this->messages->text("style", "creatorListFull"), 
			$this->messages->text("style", "creatorListLimit"));
		$list = base64_decode($this->session->getVar($prefix . "_primaryCreatorList"));
		$listMore = stripslashes(base64_decode($this->session->getVar($prefix . "_primaryCreatorListMore")));
		$listLimit = stripslashes(base64_decode($this->session->getVar($prefix . "_primaryCreatorListLimit")));
		$listAbbreviation = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_primaryCreatorListAbbreviation")));
		$italic = base64_decode($this->session->getVar($prefix . "_primaryCreatorListAbbreviationItalic")) ? 
			TRUE : FALSE;
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "primaryCreatorList")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, 
			$prefix . "_primaryCreatorList", $example, $list, 2) . MISC::br() . 
			$this->messages->text("style", "creatorListIf") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorListMore", $listMore, 3) . 
			$this->messages->text("style", "creatorListOrMore") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorListLimit", $listLimit, 3) . MISC::br() . 
			$this->messages->text("style", "creatorListAbbreviation") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_primaryCreatorListAbbreviation", $listAbbreviation, 15) . ' ' . 
			FORM::checkbox(FALSE, $prefix . "_primaryCreatorListAbbreviationItalic", $italic) . ' ' . 
			$this->messages->text("style", "italics"));
		$list = base64_decode($this->session->getVar($prefix . "_otherCreatorList"));
		$listMore = stripslashes(base64_decode($this->session->getVar($prefix . "_otherCreatorListMore")));
		$listLimit = stripslashes(base64_decode($this->session->getVar($prefix . "_otherCreatorListLimit")));
		$listAbbreviation = stripslashes(base64_decode($this->session->getVar(
			$prefix . "_otherCreatorListAbbreviation")));
		$italic = base64_decode($this->session->getVar($prefix . "_otherCreatorListAbbreviationItalic")) ? 
			TRUE : FALSE;
		$pString .= TABLE::td(MISC::b($this->messages->text("style", "otherCreatorList")) . MISC::br() . 
			FORM::selectedBoxValue(FALSE, 
			$prefix . "_otherCreatorList", $example, $list, 2) . MISC::br() . 
			$this->messages->text("style", "creatorListIf") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorListMore", $listMore, 3) . 
			$this->messages->text("style", "creatorListOrMore") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorListLimit", $listLimit, 3) . MISC::br() . 
			$this->messages->text("style", "creatorListAbbreviation") . ' ' . 
			FORM::textInput(FALSE, $prefix . "_otherCreatorListAbbreviation", $listAbbreviation, 15) . ' ' . 
			FORM::checkbox(FALSE, $prefix . "_otherCreatorListAbbreviationItalic", $italic) . ' ' . 
			$this->messages->text("style", "italics"));
		$pString .= TABLE::trEnd();
		$pString .= TABLE::tableEnd();
		$pString .= TABLE::tdEnd() . TABLE::trEnd() . TABLE::trStart() . TABLE::tdStart();
		$pString .= MISC::br() . "&nbsp;" . MISC::br();
		return $pString;
	}
// Re-write creator(s) portion of templates to handle styles such as DIN 1505.
	function rewriteCreators($key, $availableFields)
	{
		$heading = MISC::p(MISC::b($this->messages->text("style", "rewriteCreator1")), "small");
		foreach($this->creators as $creatorField)
		{
			if(!array_key_exists($creatorField, $availableFields))
				continue;
			$fields[$creatorField] = $availableFields[$creatorField];
		}
		if(!isset($fields))
			return FALSE;
		$pString = FALSE;
		foreach($fields as $creatorField => $value)
		{
			$basicField = "style_" . $key . "_" . $creatorField;
			$field = TABLE::td(MISC::p(MISC::i($value), "small"), FALSE, FALSE, "middle");
			$formString = $basicField . "_firstString";
			$string = stripslashes(base64_decode($this->session->getVar($formString)));
			$formCheckbox = $basicField . "_firstString_before";
			$checkbox = base64_decode($this->session->getVar($formCheckbox)) ? TRUE : FALSE;
			$firstCheckbox = MISC::br() . $this->messages->text("style", "rewriteCreator4") . 
				"&nbsp;" . FORM::checkbox(FALSE, $formCheckbox, $checkbox);
			$first = TABLE::td(MISC::p(FORM::textInput($this->messages->text("style", "rewriteCreator2"), 
					$formString, $string, 20, 255) . $firstCheckbox, "small"), FALSE, FALSE, "bottom");
			$formString = $basicField . "_remainderString";
			$string = stripslashes(base64_decode($this->session->getVar($formString)));
			$formCheckbox = $basicField . "_remainderString_before";
			$checkbox = base64_decode($this->session->getVar($formCheckbox)) ? TRUE : FALSE;
			$remainderCheckbox = MISC::br() . $this->messages->text("style", "rewriteCreator4") . 
				"&nbsp;" . FORM::checkbox(FALSE, $formCheckbox, $checkbox);
			$formCheckbox = $basicField . "_remainderString_each";
			$checkbox = base64_decode($this->session->getVar($formCheckbox)) ? TRUE : FALSE;
			$remainderCheckbox .= ",&nbsp;&nbsp;&nbsp;" . $this->messages->text("style", "rewriteCreator5") . 
				"&nbsp;" . FORM::checkbox(FALSE, $formCheckbox, $checkbox);
			$remainder = TABLE::td(MISC::p(FORM::textInput($this->messages->text("style", "rewriteCreator3"), 
					$formString, $string, 20, 255) . $remainderCheckbox, "small"), FALSE, FALSE, "bottom");
			$pString .= TABLE::trStart() . $field . $first . $remainder . TABLE::trEnd();
		}
		return $heading . TABLE::tableStart("styleTable", 1, FALSE, 5) . $pString . TABLE::tableEnd();
	}
	function findAlternateFields($subjectArray, $search)
	{
		$index = 1;
		$lastIndex = sizeof($subjectArray) - 1;
		$alternates = array();
		foreach($subjectArray as $subject)
		{
			$subjectFieldIndex = $index;
// this pair depend on the preceding field
			if(($index > 1) && (substr_count($subject, "$") == 3) && (strpos($subject, "$") === 0))
			{
				$dollarSplit = explode("$", trim($subject));
				$temp = array();
				$elements = 0;
				if($dollarSplit[1])
				{
					preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $dollarSplit[1], $match);
					if(!empty($match))
					{
						$newSubjectArray[$index] = $dollarSplit[1];
						$temp[$match[2]] = 'first';
						++$index;
						++$lastIndex;
						++$elements;
						$temp['position'] = 'pre';
					}
					else
					{
						$newSubjectArray[$index] = $subject;
						++$index;
					}
				}
				if($dollarSplit[2])
				{
					preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $dollarSplit[2], $match);
					if(!empty($match))
					{
						$newSubjectArray[$index] = $dollarSplit[2];
						$temp[$match[2]] = 'second';
						++$index;
						++$lastIndex;
						++$elements;
						$temp['position'] = 'pre';
					}
					else
					{
						$newSubjectArray[$index] = $subject;
						++$index;
					}
				}
				if($elements)
					$alternates[][$subjectFieldIndex - 1] = $temp;
			}
// this pair depend on the following field
			else if((substr_count($subject, "#") == 3) && (strpos($subject, "#") === 0))
			{
				$hashSplit = explode("#", trim($subject));
				$temp = array();
				$elements = $subjectFieldIndex;
				if($hashSplit[1])
				{
					preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $hashSplit[1], $match);
					if(!empty($match))
					{
						$newSubjectArray[$index] = $hashSplit[1];
						$temp[$match[2]] = 'first';
						++$index;
						++$lastIndex;
						++$elements;
						$temp['position'] = 'post';
					}
					else
					{
						$newSubjectArray[$index] = $subject;
						++$index;
					}
				}
				if($hashSplit[2])
				{
					preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $hashSplit[2], $match);
					if(!empty($match))
					{
						$newSubjectArray[$index] = $hashSplit[2];
						$temp[$match[2]] = 'second';
						++$index;
						++$lastIndex;
						++$elements;
						$temp['position'] = 'post';
					}
					else
					{
						$newSubjectArray[$index] = $subject;
						++$index;
					}
				}
				if($elements > $subjectFieldIndex)
					$alternates[][$subjectFieldIndex + 1] = $temp;
			}
			else
			{
				$newSubjectArray[$index] = $subject;
				++$index;
			}
		}
//print "FINAL SUBJECTARRAY: "; print_r($newSubjectArray); print "<P>";
		return array($newSubjectArray, $alternates);
	}
// parse input into array
	function parseStringToArray($type, $subject, $map = FALSE)
	{
		if(!$subject)
			return array();
		if($map)
			$this->map = $map;
		$search = join('|', $this->map->$type);
// footnotes can have pages field
		if($this->footnotePages && !array_key_exists('pages', $this->map->$type))
			$search .= '|' . 'pages';
		$subjectArray = explode("\|", $subject);
		list($subjectArray, $alternates) = $this->findAlternateFields($subjectArray, $search);
		$sizeSubject = sizeof($subjectArray);
// Loop each field string
		$index = 0;
		$subjectIndex = 0;
		foreach($subjectArray as $subject)
		{
			++$subjectIndex;
			$dependentPre = $dependentPost = $dependentPreAlternative = 
				$dependentPostAlternative = $singular = $plural = FALSE;
// First grab fieldNames from the input string.
			preg_match("/(.*)(?<!`|[a-zA-Z])($search)(?!`|[a-zA-Z])(.*)/", $subject, $array);
			if(empty($array))
			{
				if(!$index)
				{
					$possiblePreliminaryText = $subject;
					continue;
				}
				if(isset($independent) && ($subjectIndex == $sizeSubject) && 
					array_key_exists('independent_' . $index, $independent))
					$ultimate = $subject;
				else
				{
					if(isset($independent) && (sizeof($independent) % 2))
						$independent['independent_' . ($index - 1)] = $subject;
					else
						$independent['independent_' . $index] = $subject;
				}
				continue;
			}
// At this stage, [2] is the fieldName, [1] is what comes before and [3] is what comes after.
			$pre = $array[1];
			$fieldName = $array[2];
			$post = $array[3];
// Anything in $pre enclosed in '%' characters is only to be printed if the resource has something in the 
// previous field -- replace with unique string for later preg_replace().
			if(preg_match("/%(.*)%(.*)%|%(.*)%/U", $pre, $dependent))
			{
// if sizeof == 4, we have simply %*% with the significant character in [3].
// if sizeof == 3, we have %*%*% with dependent in [1] and alternative in [2].
				$pre = str_replace($dependent[0], "__DEPENDENT_ON_PREVIOUS_FIELD__", $pre);
				if(sizeof($dependent) == 4)
				{
					$dependentPre = $dependent[3];
					$dependentPreAlternative = '';
				}
				else
				{
					$dependentPre = $dependent[1];
					$dependentPreAlternative = $dependent[2];
				}
			}
// Anything in $post enclosed in '%' characters is only to be printed if the resource has something in the 
// next field -- replace with unique string for later preg_replace().
			if(preg_match("/%(.*)%(.*)%|%(.*)%/U", $post, $dependent))
			{
				$post = str_replace($dependent[0], "__DEPENDENT_ON_NEXT_FIELD__", $post);
				if(sizeof($dependent) == 4)
				{
					$dependentPost = $dependent[3];
					$dependentPostAlternative = '';
				}
				else
				{
					$dependentPost = $dependent[1];
					$dependentPostAlternative = $dependent[2];
				}
			}
// find singular/plural alternatives in $pre and $post and replace with unique string for later preg_replace().
			if(preg_match("/\^(.*)\^(.*)\^/U", $pre, $matchCarat))
			{
				$pre = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $pre);
				$singular = $matchCarat[1];
				$plural = $matchCarat[2];
			}
			else if(preg_match("/\^(.*)\^(.*)\^/U", $post, $matchCarat))
			{
				$post = str_replace($matchCarat[0], "__SINGULAR_PLURAL__", $post);
				$singular = $matchCarat[1];
				$plural = $matchCarat[2];
			}
// Now dump into $final[$fieldName] stripping any backticks
			if($dependentPre)
				$final[$fieldName]['dependentPre'] = $dependentPre;
			else
				$final[$fieldName]['dependentPre'] = '';
			if($dependentPost)
				$final[$fieldName]['dependentPost'] = $dependentPost;
			else
				$final[$fieldName]['dependentPost'] = '';
			if($dependentPreAlternative)
				$final[$fieldName]['dependentPreAlternative'] = $dependentPreAlternative;
			else
				$final[$fieldName]['dependentPreAlternative'] = '';
			if($dependentPostAlternative)
				$final[$fieldName]['dependentPostAlternative'] = $dependentPostAlternative;
			else
				$final[$fieldName]['dependentPostAlternative'] = '';
			if($singular)
				$final[$fieldName]['singular'] = $singular;
			else
				$final[$fieldName]['singular'] = '';
			if($plural)
				$final[$fieldName]['plural'] = $plural;
			else
				$final[$fieldName]['plural'] = '';
			$final[$fieldName]['pre'] = $pre;
			$final[$fieldName]['post'] = $post;
// add any alternates (which are indexed from 1 to match $subjectIndex)
			if(array_key_exists(0, $alternates))
			{
				if(array_key_exists($subjectIndex, $alternates[0]))
				{
					if($alternates[0][$subjectIndex]['position'] == 'pre')
					{
						foreach($alternates[0][$subjectIndex] as $field => $position)
						{
							if($position == 'first')
								$final[$fieldName]['alternatePreFirst'] = $field;
							else if($position == 'second')
								$final[$fieldName]['alternatePreSecond'] = $field;
						}
// Write empty XML fields if required
						if(!array_key_exists('alternatePreFirst', $final[$fieldName]))
							$final[$fieldName]['alternatePreFirst'] = '';
						if(!array_key_exists('alternatePreSecond', $final[$fieldName]))
							$final[$fieldName]['alternatePreSecond'] = '';
					}
					else
					{
						foreach($alternates[0][$subjectIndex] as $field => $position)
						{
							if($position == 'first')
								$final[$fieldName]['alternatePostFirst'] = $field;
							else if($position == 'second')
								$final[$fieldName]['alternatePostSecond'] = $field;
						}
// Write empty XML fields if required
						if(!array_key_exists('alternatePostFirst', $final[$fieldName]))
							$final[$fieldName]['alternatePostFirst'] = '';
						if(!array_key_exists('alternatePostSecond', $final[$fieldName]))
							$final[$fieldName]['alternatePostSecond'] = '';
					}
				}
			}
			if(array_key_exists(1, $alternates))
			{
				if(array_key_exists($subjectIndex, $alternates[1]))
				{
					if($alternates[1][$subjectIndex]['position'] == 'pre')
					{
						foreach($alternates[1][$subjectIndex] as $field => $position)
						{
							if($position == 'first')
								$final[$fieldName]['alternatePreFirst'] = $field;
							else if($position == 'second')
								$final[$fieldName]['alternatePreSecond'] = $field;
						}
// Write empty XML fields if required
						if(!array_key_exists('alternatePreFirst', $final[$fieldName]))
							$final[$fieldName]['alternatePreFirst'] = '';
						if(!array_key_exists('alternatePreSecond', $final[$fieldName]))
							$final[$fieldName]['alternatePreSecond'] = '';
					}
					else
					{
						foreach($alternates[1][$subjectIndex] as $field => $position)
						{
							if($position == 'first')
								$final[$fieldName]['alternatePostFirst'] = $field;
							else if($position == 'second')
								$final[$fieldName]['alternatePostSecond'] = $field;
						}
// Write empty XML fields if required
						if(!array_key_exists('alternatePostFirst', $final[$fieldName]))
							$final[$fieldName]['alternatePostFirst'] = '';
						if(!array_key_exists('alternatePostSecond', $final[$fieldName]))
							$final[$fieldName]['alternatePostSecond'] = '';
					}
				}
			}
			$index++;
		}
		if(isset($possiblePreliminaryText))
		{
			if(isset($independent))
				$independent = array('independent_0' => $possiblePreliminaryText) + $independent;
			else
				$final['preliminaryText'] = $possiblePreliminaryText;
		}
		if(!isset($final)) // presumably no field names...
			$this->badInput($this->errors->text("inputError", "invalid"), $this->errorDisplay);
		if(isset($independent))
		{
			$size = sizeof($independent);
// If $size == 3 and exists 'independent_0', this is preliminaryText
// If $size == 3 and exists 'independent_' . $index, this is ultimate
// If $size % 2 == 0 and exists 'independent_0' and 'independent_' . $index, these are preliminaryText and ultimate
			if(($size == 3) && array_key_exists('independent_0', $independent))
				$final['preliminaryText'] = array_shift($independent);
			else if(($size == 3) && array_key_exists('independent_' . $index, $independent))
				$final['ultimate'] = array_pop($independent);
			else if(!($size % 2) && array_key_exists('independent_0', $independent)
			&& array_key_exists('independent_' . $index, $independent))
			{
				$final['preliminaryText'] = array_shift($independent);
				$final['ultimate'] = array_pop($independent);
			}
			$size = sizeof($independent);
// last element of odd number is actually ultimate punctuation or first element is preliminary if exists 'independent_0'
			if($size % 2)
			{
				if(array_key_exists('independent_0', $independent))
					$final['preliminaryText'] = array_shift($independent);
				else
					$final['ultimate'] = array_pop($independent);
			}
			if($size == 1)
			{
				if(array_key_exists('independent_0', $independent))
					$final['preliminaryText'] = array_shift($independent);
				if(array_key_exists('independent_' . $index, $independent))
					$final['ultimate'] = array_shift($independent);
			}
			if(isset($ultimate) && !array_key_exists('ultimate', $final))
				$final['ultimate'] = $ultimate;
			if(isset($preliminaryText) && !array_key_exists('preliminaryText', $final))
				$final['preliminaryText'] = $preliminaryText;
			if(!empty($independent))
				$final['independent'] = $independent;
		}
		return $final;
	}
// write the styles to file.
// If !$fileName, this is called from add() and we create folder/filename immediately before writing to file.
// If $fileName, this comes from edit()
	function writeFile($fileName = FALSE)
	{
		if($fileName)
			$this->errorDisplay = 'editInit';
		else
			$this->errorDisplay = 'addInit';
		include_once(OSBIB__TABLE);
		include_once(OSBIB__STYLEMAP);
		$this->map = new STYLEMAP();
		include_once(OSBIB__UTF8);
		$this->utf8 = new UTF8();
		$types = array_keys($this->map->types);
// Start XML
		$fileString = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$fileString .= "<style xml:lang=\"en\">";
// Main style information
		$fileString .= "<info>";
		$fileString .= "<name>" . trim(stripslashes($this->vars['styleShortName'])) . "</name>";
		$fileString .= "<description>" . htmlspecialchars(trim(stripslashes($this->vars['styleLongName'])))
			 . "</description>";
// Temporary place holder
		$fileString .= "<language>English</language>";
		$fileString .= "<osbibVersion>$this->osbibVersion</osbibVersion>";
		$fileString .= "</info>";
// Start citation definition
		$fileString .= "<citation>";
		$inputArray = array(
			"cite_creatorStyle", "cite_creatorOtherStyle", "cite_creatorInitials", 
			"cite_creatorFirstName", "cite_twoCreatorsSep", "cite_creatorSepFirstBetween", 
			"cite_creatorListSubsequentAbbreviation", "cite_creatorSepNextBetween", 
			"cite_creatorSepNextLast", "cite_creatorList", "cite_creatorListMore", 
			"cite_creatorListLimit", "cite_creatorListAbbreviation", "cite_creatorUppercase", 
			"cite_creatorListSubsequentAbbreviationItalic", "cite_creatorListAbbreviationItalic", 
			"cite_creatorListSubsequent", "cite_creatorListSubsequentMore", 
			"cite_creatorListSubsequentLimit", "cite_consecutiveCreatorTemplate", "cite_consecutiveCreatorSep", 
			"cite_template", "cite_useInitials", "cite_consecutiveCitationSep", "cite_yearFormat", 
			"cite_pageFormat", "cite_titleCapitalization", "cite_ibid", "cite_idem",
			"cite_opCit", "cite_followCreatorTemplate",
			"cite_firstChars", "cite_lastChars", "cite_citationStyle", "cite_templateEndnoteInText", 
			"cite_templateEndnote", "cite_consecutiveCitationEndnoteInTextSep", "cite_firstCharsEndnoteInText", 
			"cite_lastCharsEndnoteInText", "cite_formatEndnoteInText", "cite_endnoteStyle", 
			"cite_ambiguous", "cite_ambiguousTemplate", "cite_order1", "cite_order2", "cite_order3", 
			"cite_order1desc", "cite_order2desc", "cite_order3desc", "cite_sameIdOrderBib", 
			"cite_firstCharsEndnoteID", "cite_lastCharsEndnoteID", "cite_subsequentCreatorRange", 
			"cite_followCreatorPageSplit", "cite_subsequentCreatorTemplate", "cite_replaceYear", 
			"cite_titleSubtitleSeparator", "cite_formatEndnoteID", "cite_removeTitle", "cite_subsequentFields",
		);
		foreach($inputArray as $input)
		{
			if(isset($this->vars[$input]))
			{
				$split = explode("_", $input, 2);
				$elementName = $split[1];
				$fileString .= "<$elementName>" . 
					htmlspecialchars(stripslashes($this->vars[$input])) . "</$elementName>";
			}
		}
// Resource types replacing citation templates
		foreach($types as $key)
		{
			$citationStringName = "cite_" . $key . "Template";
			if(array_key_exists($citationStringName, $this->vars) && 
			($string = $this->vars[$citationStringName]))
				$fileString .= "<" . $key . "Template>" . htmlspecialchars(stripslashes($string)) . 
				"</" . $key . "Template>";
			$field = "cite_" . $key . "_notInBibliography";
			$element = $key . "_notInBibliography";
			if(isset($this->vars[$field]))
				$fileString .= "<$element>" . $this->vars[$field] . "</$element>";
		}
		$fileString .= "</citation>";
// Footnote creator formatting
		$fileString .= "<footnote>";
		$inputArray = array(
// foot note creator formatting
			"footnote_primaryCreatorFirstStyle", "footnote_primaryCreatorOtherStyle", 
			"footnote_primaryCreatorList", "footnote_primaryCreatorFirstName", 
			"footnote_primaryCreatorListAbbreviationItalic", "footnote_primaryCreatorInitials", 
			"footnote_primaryCreatorListMore", "footnote_primaryCreatorListLimit", 
			"footnote_primaryCreatorListAbbreviation", "footnote_primaryCreatorUppercase", 
			"footnote_primaryCreatorRepeatString", "footnote_primaryCreatorRepeat", 
			"footnote_primaryCreatorSepFirstBetween",  "footnote_primaryTwoCreatorsSep", 
			"footnote_primaryCreatorSepNextBetween", "footnote_primaryCreatorSepNextLast", 
			"footnote_otherCreatorFirstStyle", "footnote_otherCreatorListAbbreviationItalic", 
			"footnote_otherCreatorOtherStyle", "footnote_otherCreatorInitials", 
			"footnote_otherCreatorFirstName", "footnote_otherCreatorList", 
			"footnote_otherCreatorUppercase", "footnote_otherCreatorListMore", 
			"footnote_otherCreatorListLimit", "footnote_otherCreatorListAbbreviation", 
			"footnote_otherCreatorSepFirstBetween", "footnote_otherCreatorSepNextBetween", 
			"footnote_otherCreatorSepNextLast", "footnote_otherTwoCreatorsSep", 
		);
		foreach($inputArray as $input)
		{
			if(isset($this->vars[$input]))
			{
				$split = explode("_", $input, 2);
				$elementName = $split[1];
				$fileString .= "<$elementName>" . 
					htmlspecialchars(stripslashes($this->vars[$input])) . "</$elementName>";
			}
		}
		$this->footnotePages = TRUE;
// Footnote templates for each resource type
		foreach($types as $key)
		{
			$type = 'footnote_' . $key . 'Template';
			$name = 'footnote_' . $key;
			$input = trim(stripslashes($this->vars[$type]));
// remove newlines etc.
			$input = preg_replace("/\r|\n|\015|\012/", "", $input);
			$fileString .= "<resource name=\"$key\">";
			$fileString .= $this->arrayToXML($this->parseStringToArray($key, $input), $name, TRUE);
			$fileString .= "</resource>";
		}
		$fileString .= "</footnote>";
		$this->footnotePages = FALSE;
// Start bibliography
		$fileString .= "<bibliography>";
// Common section defining how authors, titles etc. are formatted
		$fileString .= "<common>";
		$inputArray = array(
// style
			"style_titleCapitalization", "style_monthFormat", "style_editionFormat", "style_dateFormat",
 			"style_titleSubtitleSeparator", 
			"style_primaryCreatorFirstStyle", "style_primaryCreatorOtherStyle", "style_primaryCreatorInitials", 
			"style_primaryCreatorFirstName", "style_otherCreatorFirstStyle", 
			"style_otherCreatorOtherStyle", "style_otherCreatorInitials", 
			"style_otherCreatorFirstName", "style_primaryCreatorList", "style_otherCreatorList",
			"style_primaryCreatorListAbbreviationItalic", "style_otherCreatorListAbbreviationItalic", 
			"style_primaryCreatorListMore", "style_primaryCreatorListLimit", 
			"style_primaryCreatorListAbbreviation", "style_otherCreatorListMore", 
			"style_primaryCreatorRepeatString", "style_primaryCreatorRepeat", 
			"style_otherCreatorListLimit", "style_otherCreatorListAbbreviation", 
			"style_primaryCreatorUppercase", 
			"style_otherCreatorUppercase", "style_primaryCreatorSepFirstBetween", 
			"style_primaryCreatorSepNextBetween", "style_primaryCreatorSepNextLast", 
			"style_otherCreatorSepFirstBetween", "style_otherCreatorSepNextBetween", 
			"style_otherCreatorSepNextLast", "style_primaryTwoCreatorsSep", "style_otherTwoCreatorsSep", 
			"style_userMonth_1", "style_userMonth_2", "style_userMonth_3", "style_userMonth_4", 
			"style_userMonth_5", "style_userMonth_6", "style_userMonth_7", "style_userMonth_8", 
			"style_userMonth_9", "style_userMonth_10", "style_userMonth_11", "style_userMonth_12", 
			"style_dateRangeDelimit1", "style_dateRangeDelimit2", "style_dateRangeSameMonth", 
			"style_dateMonthNoDay", "style_dateMonthNoDayString", "style_dayLeadingZero", "style_dayFormat", 
			"style_runningTimeFormat", "style_editorSwitch", "style_editorSwitchIfYes", 
			"style_pageFormat", 
		);
		foreach($inputArray as $input)
		{
			if(isset($this->vars[$input]))
			{
				$split = explode("_", $input, 2);
				$elementName = $split[1];
				$fileString .= "<$elementName>" . 
					htmlspecialchars(stripslashes($this->vars[$input])) . "</$elementName>";
			}
		}
		$fileString .= "</common>";
// Resource types
		foreach($types as $key)
		{
			$type = 'style_' . $key;
			$input = trim(stripslashes($this->vars[$type]));
// remove newlines etc.
			$input = preg_replace("/\r|\n|\015|\012/", "", $input);
// Rewrite creator strings
			$attributes = $this->creatorXMLAttributes($type);
			$fileString .= "<resource name=\"$key\" $attributes>";
			$fileString .= $this->arrayToXML($this->parseStringToArray($key, $input), $type);
			if(($key != 'genericBook') && ($key != 'genericArticle') && ($key != 'genericMisc'))
			{
				$name = $type . "_generic";
				if(!isset($this->vars[$name]))
					$name = "genericMisc";
				else
					$name = $this->vars[$name];
				$fileString .= "<fallbackstyle>$name</fallbackstyle>";
			}
// Partial templates for each resource type
			$fileString .= "<partial>";
			$type = 'partial_' . $key . 'Template';
			$input = stripslashes($this->vars[$type]);
// remove newlines etc.
			$fileString .= preg_replace("/\r|\n|\015|\012/", "", $input);
			$fileString .= "</partial>";
			$type = 'partial_' . $key . 'Replace';
			$fileString .= "<partialReplace>";
			if(array_key_exists($type, $this->vars))
				$fileString .= 1;
			else
				$fileString .= 0;
			$fileString .= "</partialReplace>";
// close resource node
			$fileString .= "</resource>";
		}
		$fileString .= "</bibliography>";
		$fileString .= "</style>";
		if(!$fileName) // called from add()
		{
// Create folder with lowercase styleShortName
			$dirName = OSBIB_STYLE_DIR . "/" . strtolower(trim($this->vars['styleShortName']));
			if(!mkdir($dirName))
				$this->badInput($error = $this->errors->text("file", "folder"), $this->errorDisplay);
			$fileName = $dirName . "/" . strtoupper(trim($this->vars['styleShortName'])) . ".xml";
		}
		if(!$fp = fopen("$fileName", "w"))
			$this->badInput($this->errors->text("file", "write", ": $fileName"), $this->errorDisplay);
		if(!fputs($fp, $this->utf8->encodeUtf8($fileString)))
			$this->badInput($this->errors->text("file", "write", ": $fileName"), $this->errorDisplay);
		fclose($fp);
// Remove sessionvars
		$this->session->clearArray("cite");
		$this->session->clearArray("style");
	}
// create attribute strings for XML <resource> element for creators
	function creatorXMLAttributes($type)
	{
		$attributes = FALSE;
		foreach($this->creators as $creatorField)
		{
			$basic = $type . "_" . $creatorField;
			$field = $basic . "_firstString";
			$name = $creatorField . "_firstString";
			if(array_key_exists($field, $this->vars) && trim($this->vars[$field]))
				$attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->vars[$field])) . "\" ";
			$field = $basic . "_firstString_before";
			$name = $creatorField . "_firstString_before";
			if(isset($this->vars[$field]))
				$attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->vars[$field])) . "\" ";
			$field = $basic . "_remainderString";
			$name = $creatorField . "_remainderString";
			if(array_key_exists($field, $this->vars) && trim($this->vars[$field]))
				$attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->vars[$field])) . "\" ";
			$field = $basic . "_remainderString_before";
			$name = $creatorField . "_remainderString_before";
			if(isset($this->vars[$field]))
				$attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->vars[$field])) . "\" ";
			$field = $basic . "_remainderString_each";
			$name = $creatorField . "_remainderString_each";
			if(isset($this->vars[$field]))
				$attributes .= "$name=\"" . htmlspecialchars(stripslashes($this->vars[$field])) . "\" ";
		}
		return $attributes;
	}
// Parse array to XML
	function arrayToXML($array, $type)
	{
		$fileString = '';
		foreach($array as $key => $value)
		{
			$fileString .= "<$key>";
			if(is_array($value))
				$fileString .= $this->arrayToXML($value, $type);
			else
				$fileString .= htmlspecialchars($value);
			$fileString .= "</$key>";
		}
		return $fileString;
	}
// validate input
	function validateInput($type)
	{
		$error = FALSE;
		if(($type == 'add') || ($type == 'edit'))
		{
			$array = array("style_titleCapitalization", "style_primaryCreatorFirstStyle", 
				"style_primaryCreatorOtherStyle", "style_primaryCreatorInitials", 
				"style_primaryCreatorFirstName", "style_otherCreatorFirstStyle", "style_dateFormat", 
				"style_otherCreatorOtherStyle", "style_otherCreatorInitials", "style_pageFormat", 
				"style_otherCreatorFirstName", "style_primaryCreatorList", "style_dayFormat", 
				"style_otherCreatorList", "style_monthFormat", "style_editionFormat",
				"style_runningTimeFormat", "style_editorSwitch", "style_primaryCreatorRepeat",  
				"style_dateRangeSameMonth", "style_dateMonthNoDay", 
		"cite_creatorStyle", "cite_creatorOtherStyle", "cite_creatorInitials", "cite_creatorFirstName", 
		"cite_twoCreatorsSep", "cite_creatorSepFirstBetween", "cite_creatorListSubsequentAbbreviation", 
		"cite_creatorSepNextBetween", "cite_creatorSepNextLast", 
		"cite_creatorList", "cite_creatorListMore", "cite_creatorListLimit", "cite_creatorListAbbreviation",  
		"cite_creatorListSubsequent", "cite_creatorListSubsequentMore", "cite_creatorListSubsequentLimit", 
		"cite_template", "cite_templateEndnoteInText", "cite_templateEndnote",
		"cite_consecutiveCitationSep", "cite_yearFormat", "cite_pageFormat", 
		"cite_titleCapitalization", "cite_citationStyle", "cite_formatEndnoteInText", "cite_ambiguous", 
		"cite_formatEndnoteID", "cite_subsequentCreatorRange", 
		
			"footnote_primaryCreatorFirstStyle",
			"footnote_primaryCreatorOtherStyle", "footnote_primaryCreatorInitials", 
			"footnote_primaryCreatorFirstName", 
			"footnote_primaryCreatorList",  "footnote_primaryCreatorRepeat",
/* Probably not required but code left here in case (see creatorsFormatting())
*/
			"footnote_otherCreatorFirstStyle", "footnote_otherCreatorFirstName", 
			"footnote_otherCreatorOtherStyle", "footnote_otherCreatorInitials", "footnote_otherCreatorList", 

		);
		
			$this->writeSession($array);
			if(!trim($this->vars['styleShortName']))
				$error = $this->errors->text("inputError", "missing");
			else
				$this->session->setVar("style_shortName", trim($this->vars['styleShortName']));
			if(preg_match("/\s/", trim($this->vars['styleShortName'])))
				$error = $this->errors->text("inputError", "invalid");
			else if(!trim($this->vars['styleLongName']))
				$error = $this->errors->text("inputError", "missing");
			else if(!trim($this->vars['style_genericBook']))
				$error = $this->errors->text("inputError", "missing");
			else if(!trim($this->vars['style_genericArticle']))
				$error = $this->errors->text("inputError", "missing");
			else if(!trim($this->vars['style_genericMisc']))
				$error = $this->errors->text("inputError", "missing");
			foreach($array as $input)
			{
				if(!isset($this->vars[$input]))
					return $this->errors->text("inputError", "missing");
			}
			if($this->vars['cite_citationStyle'] == 1) // endnotes
			{
// Must also have a bibliography template for the resource if a footnote template is defined
				if($this->vars['cite_endnoteStyle'] == 2) // footnotes
				{
					$types = array_keys($this->map->types);
					foreach($types as $key)
					{
						$type = 'footnote_' . $key . 'Template';
						$name = 'footnote_' . $key;
						$input = trim(stripslashes($this->vars[$type]));
						if($input && !$this->vars['style_' . $key])
							return $this->errors->text("inputError", "missing");
					}
					if(($this->vars['footnote_primaryCreatorList'] == 1) && 
						(!trim($this->vars['footnote_primaryCreatorListLimit']) || 
						(!$this->vars['footnote_primaryCreatorListMore'])))
							$error = $this->errors->text("inputError", "missing");
					else if(($this->vars['footnote_primaryCreatorList'] == 1) && 
						(!is_numeric($this->vars['footnote_primaryCreatorListLimit']) || 
						!is_numeric($this->vars['footnote_primaryCreatorListMore'])))
							$error = $this->errors->text("inputError", "nan");
					else if(($this->vars['footnote_otherCreatorList'] == 1) && 
						(!trim($this->vars['footnote_otherCreatorListLimit']) || 
						(!$this->vars['footnote_otherCreatorListMore'])))
							$error = $this->errors->text("inputError", "missing");
					else if(($this->vars['footnote_otherCreatorList'] == 1) && 
						(!is_numeric($this->vars['footnote_otherCreatorListLimit']) || 
						!is_numeric($this->vars['footnote_otherCreatorListMore'])))
							$error = $this->errors->text("inputError", "nan");
					else if(($this->vars['footnote_otherCreatorList'] == 1) && 
						(!is_numeric($this->vars['footnote_otherCreatorListLimit']) || 
						!is_numeric($this->vars['footnote_otherCreatorListMore'])))
							$error = $this->errors->text("inputError", "nan");
					else if(($this->vars['footnote_primaryCreatorRepeat'] == 2) && 
						!trim($this->vars['footnote_primaryCreatorRepeatString']))
							$error = $this->errors->text("inputError", "missing");
				}
				if(!trim($this->vars["cite_templateEndnoteInText"]))
						$error = $this->errors->text("inputError", "missing");
				else if(!trim($this->vars["cite_templateEndnote"]))
						$error = $this->errors->text("inputError", "missing");
			}
			else if(!trim($this->vars['cite_template']))
				$error = $this->errors->text("inputError", "missing");
// If xxx_creatorList set to 1 (limit), we must have style_xxxCreatorListMore and xxx_CreatorListLimit. The 
// latter two must be numeric.
			if(($this->vars['style_primaryCreatorList'] == 1) && 
				(!trim($this->vars['style_primaryCreatorListLimit']) || 
				(!$this->vars['style_primaryCreatorListMore'])))
					$error = $this->errors->text("inputError", "missing");
			else if(($this->vars['style_primaryCreatorList'] == 1) && 
				(!is_numeric($this->vars['style_primaryCreatorListLimit']) || 
				!is_numeric($this->vars['style_primaryCreatorListMore'])))
					$error = $this->errors->text("inputError", "nan");
			else if(($this->vars['style_otherCreatorList'] == 1) && 
				(!trim($this->vars['style_otherCreatorListLimit']) || 
				(!$this->vars['style_otherCreatorListMore'])))
					$error = $this->errors->text("inputError", "missing");
			else if(($this->vars['cite_creatorList'] == 1) && 
				(!trim($this->vars['cite_creatorListLimit']) || 
				(!$this->vars['cite_creatorListMore'])))
					$error = $this->errors->text("inputError", "missing");
			else if(($this->vars['cite_creatorList'] == 1) && 
				(!is_numeric($this->vars['cite_creatorListLimit']) || 
				!is_numeric($this->vars['cite_creatorListMore'])))
					$error = $this->errors->text("inputError", "nan");
			else if(($this->vars['cite_creatorListSubsequent'] == 1) && 
				(!trim($this->vars['cite_creatorListSubsequentLimit']) || 
				(!$this->vars['cite_creatorListSubsequentMore'])))
					$error = $this->errors->text("inputError", "missing");
			else if(($this->vars['cite_creatorListSubsequent'] == 1) && 
				(!is_numeric($this->vars['cite_creatorListSubsequentLimit']) || 
				!is_numeric($this->vars['cite_creatorListSubsequentMore'])))
					$error = $this->errors->text("inputError", "nan");
			else if(($this->vars['style_editorSwitch'] == 1) && 
				!trim($this->vars['style_editorSwitchIfYes']))
					$error = $this->errors->text("inputError", "missing");
			else if(($this->vars['style_primaryCreatorRepeat'] == 2) && 
				!trim($this->vars['style_primaryCreatorRepeatString']))
					$error = $this->errors->text("inputError", "missing");
			else if($this->vars['style_monthFormat'] == 2)
			{
				for($i = 1; $i <= 12; $i++)
				{
					if(!trim($this->vars["style_userMonth_$i"]))
						$error = $this->errors->text("inputError", "missing");
				}
			}
// If style_dateMonthNoDay, style_dateMonthNoDayString must have at least 'date' in it
			else if($this->vars['style_dateMonthNoDay'])
			{
				if(strstr($this->vars['style_dateMonthNoDayString'], "date") === FALSE)
					$error = $this->errors->text("inputError", "invalid");
			}
			if(($this->vars["cite_ambiguous"] == 2) && 
				!trim($this->vars["cite_ambiguousTemplate"]))
					$error = $this->errors->text("inputError", "missing");
		}
		if($type == 'add')
		{
			if(preg_match("/\s/", trim($this->vars['styleShortName'])))
				$error = $this->errors->text("inputError", "invalid");
			else if(array_key_exists(strtoupper(trim($this->vars['styleShortName'])), $this->styles))
				$error = $this->errors->text("inputError", "styleExists");
		}
		else if($type == 'editDisplay')
		{
			if(!array_key_exists('editStyleFile', $this->vars))
				$error = $this->errors->text("inputError", "missing");
		}
		if($error)
			return $error;
// FALSE means validated input
		return FALSE;
	}
// Write session
	function writeSession($array)
	{
		include_once(OSBIB__TABLE);
		include_once(OSBIB__STYLEMAP);
		$this->map = new STYLEMAP();
		$types = array_keys($this->map->types);
		if(trim($this->vars['styleLongName']))
			$this->session->setVar("style_longName", 
			base64_encode(trim(htmlspecialchars($this->vars['styleLongName']))));
// other resource types
		foreach($types as $key)
		{
// Footnote templates
			$array[] = 'footnote_' . $key . 'Template';
// Partial templates
			$array[] = 'partial_' . $key . 'Template';
			$type = 'style_' . $key;
			if(trim($this->vars[$type]))
				$this->session->setVar($type, base64_encode(trim(htmlspecialchars($this->vars[$type]))));
// Rewrite creator strings
			foreach($this->creators as $creatorField)
			{
				$basic = $type . "_" . $creatorField;
				$field = $basic . "_firstString";
				if(array_key_exists($field, $this->vars) && trim($this->vars[$field]))
					$this->session->setVar($field, base64_encode(htmlspecialchars($this->vars[$field])));
				$field = $basic . "_firstString_before";
				if(isset($this->vars[$field]))
					$this->session->setVar($field, base64_encode($this->vars[$field]));
				$field = $basic . "_remainderString";
				if(array_key_exists($field, $this->vars) && trim($this->vars[$field]))
					$this->session->setVar($field, base64_encode(htmlspecialchars($this->vars[$field])));
				$field = $basic . "_remainderString_before";
				if(isset($this->vars[$field]))
					$this->session->setVar($field, base64_encode($this->vars[$field]));
				$field = $basic . "_remainderString_each";
				if(isset($this->vars[$field]))
					$this->session->setVar($field, base64_encode($this->vars[$field]));
			}
			$field = "cite_" . $key . "_notInBibliography";
			if(isset($this->vars[$field]))
				$this->session->setVar($field, base64_encode(trim($this->vars[$field])));
			$citationStringName = 'cite_' . $key . "Template";
			if(array_key_exists($citationStringName, $this->vars) && 
			($input = $this->vars[$citationStringName]))
				$this->session->setVar($citationStringName, base64_encode(htmlspecialchars($input)));
// Fallback styles
			if(($key != 'genericBook') && ($key != 'genericArticle') && ($key != 'genericMisc'))
			{
				$name = $type . "_generic";
				$this->session->setVar($name, base64_encode(trim($this->vars[$name])));
			}
		}
// Other values. $array parameter is required, other optional input is added to the array
		$array[] = "style_primaryCreatorSepBetween";
		$array[] = "style_primaryCreatorSepLast";
		$array[] = "style_otherCreatorSepBetween";
		$array[] = "style_otherCreatorSepLast";
		$array[] = "style_primaryCreatorListMore";
		$array[] = "style_primaryCreatorListLimit";
		$array[] = "style_primaryCreatorListAbbreviation";
		$array[] = "style_otherCreatorListMore";
		$array[] = "style_otherCreatorListLimit";
		$array[] = "style_otherCreatorListAbbreviation";
		$array[] = "style_editorSwitchIfYes";
		$array[] = "style_primaryCreatorUppercase";
		$array[] = "style_otherCreatorUppercase";
		$array[] = "style_primaryTwoCreatorsSep";
		$array[] = "style_primaryCreatorSepFirstBetween";
		$array[] = "style_primaryCreatorSepNextBetween";
		$array[] = "style_primaryCreatorSepNextLast";
		$array[] = "style_otherTwoCreatorsSep";
		$array[] = "style_otherCreatorSepFirstBetween";
		$array[] = "style_otherCreatorSepNextBetween";
		$array[] = "style_otherCreatorSepNextLast";
		$array[] = "style_primaryCreatorRepeatString";
		$array[] = "style_primaryCreatorListAbbreviationItalic";
		$array[] = "style_otherCreatorListAbbreviationItalic";
		$array[] = "style_dateMonthNoDayString";
		$array[] = "style_userMonth_1";
		$array[] = "style_userMonth_2";
		$array[] = "style_userMonth_3";
		$array[] = "style_userMonth_4";
		$array[] = "style_userMonth_5";
		$array[] = "style_userMonth_6";
		$array[] = "style_userMonth_7";
		$array[] = "style_userMonth_8";
		$array[] = "style_userMonth_9";
		$array[] = "style_userMonth_10";
		$array[] = "style_userMonth_11";
		$array[] = "style_userMonth_12";
		$array[] = "style_dateRangeDelimit1";
		$array[] = "style_dateRangeDelimit2";
		$array[] = "style_dayLeadingZero";
		$array[] = "cite_useInitials";
		$array[] = "cite_creatorUppercase";
		$array[] = "cite_creatorListAbbreviationItalic";
		$array[] = "cite_creatorListSubsequentAbbreviationItalic";
		$array[] = "cite_ambiguousTemplate";
		$array[] = "cite_ibid";
		$array[] = "cite_idem";
		$array[] = "cite_opCit";
		$array[] = "cite_followCreatorTemplate";
		$array[] = "cite_consecutiveCreatorTemplate";
		$array[] = "cite_consecutiveCreatorSep";
		$array[] = "cite_firstChars";
		$array[] = "cite_lastChars";
		$array[] = "cite_consecutiveCitationEndnoteInTextSep";
		$array[] = "cite_firstCharsEndnoteInText";
		$array[] = "cite_lastCharsEndnoteInText";
		$array[] = "cite_endnoteStyle";
		$array[] = "cite_order1";
		$array[] = "cite_order2";
		$array[] = "cite_order3";
		$array[] = "cite_order1desc";
		$array[] = "cite_order2desc";
		$array[] = "cite_order3desc";
		$array[] = "cite_sameIdOrderBib";
		$array[] = "cite_firstCharsEndnoteID";
		$array[] = "cite_lastCharsEndnoteID";
		$array[] = "cite_followCreatorPageSplit";
		$array[] = "cite_subsequentCreatorTemplate";
		$array[] = "cite_replaceYear";
		$array[] = "cite_removeTitle";
		$array[] = "cite_subsequentFields";
		$array[] = "footnote_primaryCreatorSepBetween";
		$array[] = "footnote_primaryCreatorSepLast";
		$array[] = "footnote_primaryCreatorListMore";
		$array[] = "footnote_primaryCreatorListLimit";
		$array[] = "footnote_primaryCreatorListAbbreviation";
		$array[] = "footnote_primaryCreatorUppercase";
		$array[] = "footnote_primaryTwoCreatorsSep";
		$array[] = "footnote_primaryCreatorSepFirstBetween";
		$array[] = "footnote_primaryCreatorSepNextBetween";
		$array[] = "footnote_primaryCreatorSepNextLast";
		$array[] = "footnote_primaryCreatorRepeatString";
		$array[] = "footnote_primaryCreatorListAbbreviationItalic";
/* Probably not required but code left here in case (see creatorsFormatting())
*/
		$array[] = "footnote_otherCreatorListAbbreviationItalic";
		$array[] = "footnote_otherTwoCreatorsSep";
		$array[] = "footnote_otherCreatorSepFirstBetween";
		$array[] = "footnote_otherCreatorSepNextBetween";
		$array[] = "footnote_otherCreatorSepNextLast";
		$array[] = "footnote_otherCreatorUppercase";
		$array[] = "footnote_otherCreatorListMore";
		$array[] = "footnote_otherCreatorListLimit";
		$array[] = "footnote_otherCreatorListAbbreviation";
		$array[] = "footnote_otherCreatorSepBetween";
		$array[] = "footnote_otherCreatorSepLast";
		
		foreach($array as $input)
		{
			if(isset($this->vars[$input]))
				$this->session->setVar($input, base64_encode(htmlspecialchars($this->vars[$input])));
			else
				$this->session->delVar($input);
		}
	}
// bad Input function
	function badInput($error, $method)
	{
		include_once(OSBIB__CLOSE);
		new CLOSE($this->$method($error));
	}
}
?>