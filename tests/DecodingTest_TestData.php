<?php
namespace YOCLIB\OpenLocationCode\Tests;

use InvalidArgumentException;

class DecodingTest_TestData{

    public $code;
    public $length;
    public $decodedLatitudeLo;
    public $decodedLatitudeHi;
    public $decodedLongitudeLo;
    public $decodedLongitudeHi;

    public function __construct($line){
        $parts = explode(',',trim($line));
        if(count($parts)!==6){
            throw new InvalidArgumentException('Wrong format of testing data.');
        }
        $this->code = $parts[0];
        $this->length = intval($parts[1]);
        $this->decodedLatitudeLo = doubleval($parts[2]);
        $this->decodedLongitudeLo = doubleval($parts[3]);
        $this->decodedLatitudeHi = doubleval($parts[4]);
        $this->decodedLongitudeHi = doubleval($parts[5]);
    }

}