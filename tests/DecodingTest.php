<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class DecodingTest extends TestCase{

    public const PRECISION = 1e-10;

    /**
     * @var DecodingTest_TestData[]
     */
    private $testDataList = [];

    public function setUp(): void{
        $lines = TestUtils::getTestFileLines('decoding.csv');
        foreach($lines AS $line){
            if(substr($line, 0, 1)=='#'){
                continue;
            }
            $this->testDataList[] = new DecodingTest_TestData($line);
        }
    }

    public function testDecode(){
        foreach($this->testDataList AS $testData){
            $decoded = (new OpenLocationCode($testData->code))->decode();

            $this->assertEquals($testData->length,$decoded->getLength(),'Wrong length for code '.$testData->code);
            $this->assertEquals($testData->decodedLatitudeLo,$decoded->getSouthLatitude(),'Wrong low latitude for code '.$testData->code);
            $this->assertEquals($testData->decodedLatitudeHi,$decoded->getNorthLatitude(),'Wrong high latitude for code '.$testData->code);
            $this->assertEquals($testData->decodedLongitudeLo,$decoded->getWestLongitude(),'Wrong low longitude for code '.$testData->code);
            $this->assertEquals($testData->decodedLongitudeHi,$decoded->getEastLongitude(),'Wrong high longitude for code '.$testData->code);
        }
    }

    public function testContains(){
        foreach($this->testDataList AS $testData){
            $olc = new OpenLocationCode($testData->code);
            $decoded = $olc->decode();

            $this->assertTrue($olc->contains($decoded->getCenterLatitude(),$decoded->getCenterLongitude()),'Containment relation is broken for the decoded middle point of code '.$testData->code);
            $this->assertTrue($olc->contains($decoded->getSouthLatitude(),$decoded->getWestLongitude()),'Containment relation is broken for the decoded bottom left corner of code '.$testData->code);
            $this->assertFalse($olc->contains($decoded->getNorthLatitude(),$decoded->getEastLongitude()),'Containment relation is broken for the decoded top right corner of code '.$testData->code);
            $this->assertFalse($olc->contains($decoded->getSouthLatitude(),$decoded->getEastLongitude()),'Containment relation is broken for the decoded bottom right corner of code '.$testData->code);
            $this->assertFalse($olc->contains($decoded->getNorthLatitude(),$decoded->getWestLongitude()),'Containment relation is broken for the decoded top left corner of code '.$testData->code);
        }
    }

}