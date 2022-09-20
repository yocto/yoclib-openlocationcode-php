<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class ValidityTest extends TestCase{

    /**
     * @var ValidityTest_TestData[]
     */
    private $testDataList = [];

    public function setUp(): void{
        $lines = TestUtils::getTestFileLines('validityTests.csv');
        foreach($lines AS $line){
            if(substr($line, 0, 1)=='#'){
                continue;
            }
            $this->testDataList[] = new ValidityTest_TestData($line);
        }
    }

    public function testIsValid(){
        foreach($this->testDataList AS $testData){
            $this->assertEquals($testData->isValid,OpenLocationCode::isValidCode($testData->code),'Validity of code "'.$testData->code.'" is wrong.');
        }
    }

    public function testIsShort(){
        foreach($this->testDataList AS $testData){
            $this->assertEquals($testData->isShort,OpenLocationCode::isShortCode($testData->code),'Shortness of code "'.$testData->code.'" is wrong.');
        }
    }

    public function testIsFull(){
        foreach($this->testDataList AS $testData){
            $this->assertEquals($testData->isFull,OpenLocationCode::isFullCode($testData->code),'Fullness of code "'.$testData->code.'" is wrong.');
        }
    }

}