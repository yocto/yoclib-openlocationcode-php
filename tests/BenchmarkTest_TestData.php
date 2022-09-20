<?php
namespace YOCLIB\OpenLocationCode\Tests;

use YOCLIB\OpenLocationCode\OpenLocationCode;

class BenchmarkTest_TestData{

    public $latitude;
    public $longitude;
    public $length;
    public $code;

    public function __construct(){
        $this->latitude = (rand()/getrandmax())*180-90;
        $this->longitude = (rand()/getrandmax())*360-180;
        $length = rand(0,11)+4;
        if($length<10 && $length%2==1){
            $length += 1;
        }
        $this->length = $length;
        $this->code = OpenLocationCode::encode($this->latitude,$this->longitude,$this->length);
    }

}