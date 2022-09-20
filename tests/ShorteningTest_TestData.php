<?php
namespace YOCLIB\OpenLocationCode\Tests;

use InvalidArgumentException;

class ShorteningTest_TestData{

    public $code;
    public $referenceLatitude;
    public $referenceLongitude;
    public $shortCode;
    public $testType;

    public function __construct($line){
        $parts = explode(',',trim($line));
        if(count($parts)!==5){
            throw new InvalidArgumentException('Wrong format of testing data.');
        }
        $this->code = $parts[0];
        $this->referenceLatitude = doubleval($parts[1]);
        $this->referenceLongitude = doubleval($parts[2]);
        $this->shortCode = $parts[3];
        $this->testType = $parts[4];
    }

}