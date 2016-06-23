<?php

namespace AppBundle\Entity;

    /********************** PAGE DETAILS ********************/
    /* @Programmer  : Kemal Sami KARACA
     * @Maintainer  : Kemal Sami KARACA
     * @Created     : 2016
     * @Modified    :
     * @Description : This is the API result object
     ********************************************************/

use AppBundle\Consts\ResultConst;

class Result
{
    public static $SUCCESS;
    public static $SUCCESS_EMPTY;
    public static $FAILURE_AUTH;
    public static $FAILURE_PARAM_MISMATCH;
    public static $FAILURE_EXCEPTION;
    public static $FAILURE_PERMISSION;

    public $resultText;
    public $resultCode;
    public $content;

    /*******************************************
     ************** CONSTRUCTOR ****************
     ******************************************/
    function __construct() {
        // allocate multiple constructor
    }

    /*******************************************
     ********* CONSTRUCTOR  METHODs ************
     ******************************************/
    public static function constructor_from_string($resultString){
        $result = Result::$FAILURE_EXCEPTION;
        try{
            $resultJson = json_decode($resultString);
            $result->resultText = $resultJson->resultText;
            $result->resultCode = $resultJson->resultCode;
            $result->content = isset($resultJson->content) ? $resultJson->content : null;
            return $result;
        }catch (Exception $ex){
            $result->content = "Result -> constructor_from_string :: " . $ex->getMessage();
        }
        return $result;
    }

    private static function constructor_default($resultCode,$resultText){
        $result = new Result();
        $result->resultCode = $resultCode;
        $result->resultText = $resultText;
        return $result;
    }


    /*******************************************
     ************* INITIALIZE ******************
     ******************************************/
    static function initializeStaticObjects(){
        Result::$SUCCESS                    = Result::$SUCCESS ? Result::$SUCCESS->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy001Code , ResultConst::ResultGuppy001Status);
        Result::$SUCCESS_EMPTY              = Result::$SUCCESS_EMPTY ? Result::$SUCCESS_EMPTY->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy010Code , ResultConst::ResultGuppy010Status);
        Result::$FAILURE_AUTH               = Result::$FAILURE_AUTH ? Result::$FAILURE_AUTH->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy101Code , ResultConst::ResultGuppy101Status);
        Result::$FAILURE_PARAM_MISMATCH     = Result::$FAILURE_PARAM_MISMATCH ? Result::$FAILURE_PARAM_MISMATCH->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy511Code , ResultConst::ResultGuppy511Status);
        Result::$FAILURE_EXCEPTION          = Result::$FAILURE_EXCEPTION ? Result::$FAILURE_EXCEPTION->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy521Code , ResultConst::ResultGuppy521Status);
        Result::$FAILURE_PERMISSION         = Result::$FAILURE_PERMISSION ? Result::$FAILURE_PERMISSION->setContent(null) : Result::constructor_default(ResultConst::ResultGuppy531Code , ResultConst::ResultGuppy531Status);
    }



    public function getContent(){
        return $this->content;
    }
    public function setResultText($resultText) {
        $this->resultText = $resultText;
    }
    public function setResultCode($resultCode) {
        $this->resultCode = $resultCode;
    }

    /**
     * @param $content
     * @return Result
     * @description Bu methodun Result objesi dönmesinin sebebi static Result objelerini ($SUCCESS, $SUCCESS_EMPTY)
     *              herkes tarafından kullanılmasıdır. Eğer static objenin kendisi herhangi bir istek tarafından
     *              set edilirse başka bir isteğe bu yansıyabilir. Bu sebepten return eden Result objesi yaratılmaktadır.
     */
    public function setContent($content){
        $resultObj = Result::constructor_default($this->resultCode, $this->resultText);
        $resultObj->content = $content;
        return $resultObj;
    }


    function checkResult($res){
        if(!is_null($res)){
            return (strcmp($this->resultCode, $res->resultCode)==0);
        }else{
            return false;
        }
    }

    public function isSuccess(){
        return (strcmp($this->resultCode, ResultConst::ResultGuppy001Code)==0);
    }




    /**
     *
     * @param type $obj
     * @return type
     *
     * This function removes null values from object
     */
    static function object_unset_nulls($obj)
    {
        $arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach($arrObj as $key => $val)
        {
            $val = (is_array($val) || is_object($val)) ? Result::object_unset_nulls($val) : $val;
            if (is_array($obj))
                $obj[$key] = $val;
            else
                $obj->$key = $val;
            if($val == null)
                unset($obj->$key);
        }
        return $obj;
    }
}

    Result::initializeStaticObjects();

?>