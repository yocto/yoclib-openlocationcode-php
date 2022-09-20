<?php
namespace YOCLIB\OpenLocationCode\Tests;

use InvalidArgumentException;

class EncodingTest_TestData{

    public $latitude;
    public $longitude;
    public $length;
    public $code;

    public function __construct($line){
        $parts = explode(',',trim($line));
        if(count($parts)!==4){
            throw new InvalidArgumentException('Wrong format of testing data.');
        }
        $this->latitude = doubleval($parts[0]);
        $this->longitude = doubleval($parts[1]);
        $this->length = intval($parts[2]);
        $this->code = $parts[3];
    }

}