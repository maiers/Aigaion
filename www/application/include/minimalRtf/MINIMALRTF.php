<?php
/*
MINIMALRTF - A minimal set of RTF coding methods to produce Rich Text Format documents on the fly.
v1.5

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net so that your improvements can be added to the release package.

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
*/

// COMMAND LINE TESTS:
// For a quick command-line test (php -f MINIMALRTF.php) after installation, uncomment the following:
/**************************************************
$centred = "This is some centred text.";
$full = "This is some full justified and italicized text.";
$weird = "Indented UNICODE:  ¿ßŽŒ‰ﬂ™ŁÞßØ€∑≠◊∝∞∅Ωπ¿";
$largeText = "Here's some large text (font size 20)";
$urlText = "Here's a URL: ";
$url = "http://bibliophile.sourceforge.net";
$urlDisplayText = "Bibliophile's home page!";
$emailText = "Here's an email: ";
$email = "billgates@microsoft.com";
$emailDisplayText = "I love SPAM";
$colouredText = "This text is red text";
$backToBlackText = "This text is black text again";
$rtf = new MINIMALRTF();
$string = $rtf->openRtf();
$rtf->createFontBlock(0, "Arial");
$rtf->createFontBlock(1, "Times New Roman");
$string .= $rtf->setFontBlock();
$string .= $rtf->justify("centre");
$string .= $rtf->textBlock(0, 12, $centred);
$string .= $rtf->justify("full");
$string .= $rtf->paragraph();
$string .= $rtf->textBlock(1, 12, $rtf->italics($full));
$string .= $rtf->justify("full", 2, 2);
$string .= $rtf->paragraph();
// Depending on your character set, you may need to encode $weird as UTF-8 first using PHP's inbuilt utf8_encode() function:
// $weird = $rtf->utf8_2_unicode(utf8_encode($weird));
$weird = $rtf->utf8_2_unicode($weird);
$string .= $rtf->textBlock(1, 12, $weird);
$string .= $rtf->justify("full");
$string .= $rtf->paragraph();
$string .= $rtf->textBlock(1, 20, $largeText);
$string .= $rtf->paragraph();
$string .= $rtf->textBlock(1, 12, $urlText . $rtf->urlText($url, $urlDisplayText));
$string .= $rtf->paragraph();
$string .= $rtf->textBlock(1, 12, $emailText . $rtf->emailText($email, $emailDisplayText));
$string .= $rtf->paragraph();
$string .= $rtf->setFontColour('red');
$string .= $rtf->textBlock(1, 12, $colouredText);
$string .= $rtf->paragraph();
$string .= $rtf->setFontColour(); // i.e. set it back to black
$string .= $rtf->textBlock(1, 12, $backToBlackText);
$string .= $rtf->closeRtf();

// Copy and paste the commandline output to a text editor, save with a .rtf extension and load in a word processor.
print $string . "\n\n";

**************************************************/

class MINIMALRTF
{
	/**
	* Constructor method called by user.
	*/
	function MINIMALRTF()
	{
		/**
		 * some defaults
		 */
		$this->justify = array(
					"centre"	=>	"qc",
					"left"		=>	"ql",
					"right"		=>	"qr",
					"full"		=>	"qj",
				);
		$this->colourTable = array(
					'black' => "\\red0\\green0\\blue0;",
					'maroon' => "\\red128\\green0\\blue0;",
					'green' => "\\red0\\green128\\blue0;",
					'olive' => "\\red128\\green128\\blue0;",
					'navy' => "\\red0\\green0\\blue128;",
					'purple' => "\\red128\\green0\\blue128;",
					'teal' => "\\red0\\green128\\blue128;",
					'gray' => "\\red128\\green128\\blue128;",
					'silver' => "\\red192\\green192\\blue192;",
					'red' => "\\red255\\green0\\blue0;",
					'lime' => "\\red0\\green255\\blue0;",
					'yellow' => "\\red255\\green255\\blue0;",
					'blue' => "\\red0\\green0\\blue255;",
					'fuchsia' => "\\red255\\green0\\blue255;",
					'aqua' => "\\red0\\green255\\blue255;",
					'white' => "\\red255\\green255\\blue255;",
				);
	}
	/**
	* Create the RTF opening tag and the colorTable
	* @return string
	*/
	function openRtf()
	{
		$text = "{\\rtf1\\ansi\\ansicpg1252\n\n";
		$text .= "{\\colortbl;";
		$index = 1;
		foreach($this->colourTable as $colour => $colourCode)
		{
			$text .= $colourCode;
			$this->colours[$colour] = "\\s1\\cf$index";
			++$index;
		}
		$text .= "}\n\n";
		unset($this->colourTable);
		return $text . "\n\n";
	}
	/**
	* Create the RTF closing tag
	* @return string
	*/
	function closeRtf()
	{
		return "\n}\n\n";
	}
	/**
	* Convert input text to bold text
	* @parameter string $input - text to be converted
	*/
	function bold($input = "")
	{
		return "{\b $input}";
	}
	/**
	* Convert input text to italics text
	* @parameter string $input - text to be converted
	*/
	function italics($input = "")
	{
		return "{\i $input}";
	}
	/**
	* Convert input text to underline text
	* @parameter string $input - text to be converted
	*/
	function underline($input = "")
	{
		return "{\ul $input}";
	}
	/**
	* Convert input text to superscript text
	* @parameter string $input - text to be converted
	*/
	function superscript($input = "")
	{
		return "{\super $input}";
	}
	/**
	* Convert input text to subscript text
	* @parameter string $input - text to be converted
	*/
	function subscript($input = "")
	{
		return "{\sub $input}";
	}
	/**
	* Set font size for each paragraph
	* @parameter integer $fontBlock - number of this fontblock
	* @parameter string $font - required font
	*/
	function createFontBlock($fontBlock = FALSE, $font = FALSE)
	{
		if(($fontBlock === FALSE) || ($font === FALSE))
			return FALSE;
		$this->fontBlocks[] = "{\\f$fontBlock\\fcharset0 $font;}\n";
		return TRUE;
	}
	/**
	* Set font blocks
	* @return string fontblock string
	*/
	function setFontBlock()
	{
		if(!isset($this->fontBlocks))
			return FALSE;
		$string = "{\\fonttbl\n";
		foreach($this->fontBlocks as $fontBlock)
			$string .= $fontBlock;
		$string .= "}\n\n";
		return $string;
	}
	/**
	* Justify and indent
	* Each TAB is equivalent to 720 units of indent
	* @parameter string $justify - either "centre", "left", "right" or "full"
	* @parameter integer $indentL - no. TABs to indent from the left
	* @parameter integer $indentR - no. TABs to indent from the right
	* @parameter integer $indentF - no. TABs to indent first line
	*/
	function justify($justify = "full", $indentL = 0, $indentR = 0, $indentF = 0)
	{
		if(!array_key_exists($justify, $this->justify))
			$justifyC = "qj";
		else
			$justifyC = $this->justify[$justify];
		$indentL *= 720;
		$indentR *= 720;
		$indentF *= 720;
		return "\\$justifyC\\li$indentL\\ri$indentR\\fi$indentF\n";
	}
	/**
	* Create empty paragraph
	* Font Size is twice what is shown in a word processor
	* @return string 
	*/
	function paragraph($fontBlock = 0, $fontSize = 12)
	{
		$fontSize *= 2;
		return "{\\f$fontBlock\\fs$fontSize \\par}\n";
	}
	/**
	* Create text block
	* @parameter string $input - input string
	* @return string 
	*/
	function textBlock($fontBlock = FALSE, $fontSize = FALSE, $input = FALSE)
	{
		if(($fontBlock === FALSE) || ($fontSize === FALSE) || ($input === FALSE))
			return FALSE;
		$fontSize *= 2;
		return "{\\f$fontBlock\\fs$fontSize $input\\par}\n";
	}
	/**
	* Create email link
	* @parameter string $email - email address
	* @return string 
	*/
	function emailText($email, $displayText = FALSE)
	{
		if(!$displayText)
			$displayText = $email;
		return "{\\field{\\fldinst { HYPERLINK \"mailto:$email\" }}{\\fldrslt {\\cs1\\ul\\cf13 $displayText}}}";
	}
	/**
	* Create URL link
	* @parameter string $url - URL
	* @return string 
	*/
	function urlText($url, $displayText = FALSE)
	{
		if(!$displayText)
			$displayText = $url;
		return "{\\field{\\fldinst { HYPERLINK \"$url\" }}{\\fldrslt {\\cs1\\ul\\cf13 $displayText}}}";
	}
	/**
	* Set font color
	* @parameter string - colour
	* @return string 
	*/
	function setFontColour($colour = 'black')
	{
		if(!array_key_exists($colour, $this->colours))
			$colour = $this->colours['black'];
		else
			$colour = $this->colours[$colour];
		return "\n$colour\n";
	}
	/**
	* UTF-8 to unicode
	* returns an array of unicode character codes 
	* Code adapted from opensource PHP code by Scott Reynen at:
	* http://www.randomchaos.com/document.php?source=php_and_unicode
	*
	* @parameter string $string UTF-8 encoded string
	* @return string unicode character code
	*/
	function utf8_2_unicode($string)
	{
		$unicode = array();        
		$values = array();
		$lookingFor = 1;
		for($i = 0; $i < strlen($string); $i++)
		{
			$thisValue = ord($string[$i]);
			if($thisValue < 128)
				$unicode[] = $string[$i];
			else
			{
				if(count($values) == 0)
					$lookingFor = ($thisValue < 224) ? 2 : 3;
				$values[] = $thisValue;
				if(count($values) == $lookingFor)
				{
					$number = ($lookingFor == 3) ?
						(($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64) :
						(($values[0] % 32) * 64) + ($values[1] % 64);
//					$unicode[] = '\u' . $number . " ?";
// A better unicode function?
					$decModulus = $number % 256;
					$modulus = dechex($number % 256);
					if($decModulus < 16)
					  $modulus = '0' . $modulus;
					$unicode[] = '\u' . $number . "\'$modulus";
					$values = array();
					$lookingFor = 1;
				}
			}
		}
		return join('', $unicode);
	}
}
?>