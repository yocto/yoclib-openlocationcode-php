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
        $this->isValid = boolval($parts[1]);
        $this->isShort = boolval($parts[2]);
        $this->isFull = boolval($parts[3]);
    }

}