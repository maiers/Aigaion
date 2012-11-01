<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("tbs_class.php");

class Tbswrapper{

    /**
     * TinyButStrong instance
     *
     * @var object
     */
    private static $TBS = null;

    /**
     * default constructor
     *
     */
    public function __construct(){
        if(self::$TBS == null) $this->TBS = new clsTinyButStrong();
    }

    public function tbsLoadTemplate($File, $HtmlCharSet=''){
        return $this->TBS->LoadTemplate($File, $HtmlCharSet);
    }

    public function tbsMergeBlock($BlockName, $Source){
        return $this->TBS->MergeBlock($BlockName, $Source);
    }

    public function tbsMergeField($BaseName, $X){
        return $this->TBS->MergeField($BaseName, $X);
    }

    public function tbsRender(){
        $this->TBS->Show(TBS_NOTHING);
        return $this->TBS->Source;
    }

}

?> 