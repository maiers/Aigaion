<?php
/*
Helper functions for processing months...
*/

/* In: month field from database
out: month field in bibtex format, assuming that it will be exported with additional braces around it! (that means that the export may look like this: }#nov#{    ) */
function formatMonthBibtex($month)
{
    $output = $month;
    //replace braced quotes by AIGSTR
    $output = preg_replace("/\\{\\\"\\}/",AIGSTR,$output);
    //replace remaining quotes "..." by }#...#{ 
    $output = preg_replace("/\\\"([^\\\"]*)\\\"/","}#$1#{",$output);
    //replace AIGSTR by unbraced quotes
    $output = preg_replace("/".AIGSTR."/","\"",$output);
    return $output;
}

/* In: month field from database
out: month field in bibtex format, assuming that it will be shown in an edit form */
function formatMonthBibtexForEdit($month)
{
    $output = formatMonthBibtex("{".$month."}");
//    appendMessage($output."<br>");
    //remove intial }# if any
    $output = preg_replace("/^\\{\\}\\#/","",$output);
//    appendMessage($output."<br>");
    //remove sufgfix #{ if any
    $output = preg_replace("/\\#\\{\\}\z/","",$output);
//    appendMessage($output."<br>");
    if ($output=="{}")$output="";
    return $output;
}
/* In: month field from database.
Out: month field formatted in text format, for display on screen or for export to RIS / RTF / etc */
function formatMonthText($month) 
{
    $output = $month;
    //replace braced quotes by AIGSTR
    $output = preg_replace("/\\{\\\"\\}/",AIGSTR,$output);
    //replace month quotes "..." by month names
    foreach (getMonthsInternalNoQuotes() as $abbrv=>$full)
    {
        $output = preg_replace("/\\\"".$abbrv."\\\"/",$full,$output);
    }
    //replace REMAINOING (UNKNOWN MACROS) by the macro name if it is an unknown macro...
    $output = preg_replace("/\\\"([^\\\"]*)\\\"/","$1",$output);
    //replace AIGSTR by unbraced quotes
    $output = preg_replace("/".AIGSTR."/","\"",$output);
    return $output;
}

//move to somewhere else?
function getMonthsEng() 
{
    return array("","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
}
function getMonthsInternal() 
{
    return array(""=>"","\"jan\""=>__("January"), "\"feb\""=>__("February"), "\"mar\""=>__("March"), "\"apr\""=>__("April"), "\"may\""=>__("May"), "\"jun\""=>__("June"), "\"jul\""=>__("July"), "\"aug\""=>__("August"), "\"sep\""=>__("September"), "\"oct\""=>__("October"), "\"nov\""=>__("November"), "\"dec\""=>__("December"));
}
function getMonthsInternalHtmlQuotes() 
{
    return array(""=>"","&quot;jan&quot;"=>__("January"), "&quot;feb&quot;"=>__("February"), "&quot;mar&quot;"=>__("March"), "&quot;apr&quot;"=>__("April"), "&quot;may&quot;"=>__("May"), "&quot;jun&quot;"=>__("June"), "&quot;jul&quot;"=>__("July"), "&quot;aug&quot;"=>__("August"), "&quot;sep&quot;"=>__("September"), "&quot;oct&quot;"=>__("October"), "&quot;nov&quot;"=>__("November"), "&quot;dec&quot;"=>__("December"));
}
function getMonthsInternalNoQuotes() 
{
    return array(""=>"","jan"=>__("January"), "feb"=>__("February"), "mar"=>__("March"), "apr"=>__("April"), "may"=>__("May"), "jun"=>__("June"), "jul"=>__("July"), "aug"=>__("August"), "sep"=>__("September"), "oct"=>__("October"), "nov"=>__("November"), "dec"=>__("December"));
}
function getMonthsArray() {
  return array( '0'  => '',
                '1'  => __('January'),
                '2'  => __('February'),
                '3'  => __('March'),
                '4'  => __('April'),
                '5'  => __('May'),
                '6'  => __('June'),
                '7'  => __('July'),
                '8'  => __('August'),
                '9'  => __('September'),
                '10' => __('October'),
                '11' => __('November'),
                '12' => __('December'));
}
?>