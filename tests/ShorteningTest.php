<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class ShorteningTest extends TestCase{

    /**
     * @var ShorteningTest_TestData[]
     */
    private $testDataList = [];

    public function setUp(): void{
        $lines = TestUtils::getTestFileLines('shortCodeTests.csv');
        foreach($lines AS $line){
            if(substr($line, 0, 1)=='#'){
                continue;
            }
            $this->testDataList[] = new ShorteningTest_TestData($line);
        }
    }

    public function testShortening(){
        foreach($this->testDataList AS $testData){
            if('B'!==$testData->testType && 'S'!==$testData->testType){
                continue;
            }
            var_dump($testData);
            $olc = new OpenLocationCode($testData->code);
            $shortened = $olc->shorten($testData->referenceLatitude,$testData->referenceLongitude);
            $this->assertEquals($testData->shortCode,$shortened->getCode(),'Wrong shortening of code '.$testData->code);
        }
    }

    public function testRecovering(){
        foreach($this->testDataList AS $testData){
            if('B'!==$testData->testType && 'R'!==$testData->testType){
                continue;
            }
            $olc = new OpenLocationCode($testData->shortCode);
            $recovered  = $olc->recover($testData->referenceLatitude,$testData->referenceLongitude);
            $this->assertEquals($testData->code,$recovered->getCode());
        }
    }

}