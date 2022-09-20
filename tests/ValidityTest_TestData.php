<?php
namespace YOCLIB\OpenLocationCode\Tests;

use InvalidArgumentException;

class ValidityTest_TestData{

    public $code;
    public $isValid;
    public $isShort;
    public $isFull;

    public function __construct($line){
        $parts = explode(',',trim($line));
        if(count($parts)!==4){
            throw new InvalidArgumentException('Wrong format of testing data.');
        }
        $this->code = $parts[0];
        $this->isValid = filter_var($parts[1],FILTER_VALIDATE_BOOLEAN);
        $this->isShort = filter_var($parts[2],FILTER_VALIDATE_BOOLEAN);
        $this->isFull = filter_var($parts[3],FILTER_VALIDATE_BOOLEAN);
//        var_dump($this);
    }

}