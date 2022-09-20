<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class EncodingTest extends TestCase{

    public const PRECISION = 1e-10;

    /**
     * @var EncodingTest_TestData[]
     */
    private $testDataList = [];

    public function setUp(): void{
        $lines = TestUtils::getTestFileLines('encoding.csv');
        foreach($lines AS $line){
            if(substr($line, 0, 1)=='#' || strlen(trim($line))==0){
                continue;
            }
            $this->testDataList[] = new EncodingTest_TestData($line);
        }
    }

    public function testEncodeFromLatLong(){
        foreach($this->testDataList AS $testData){
            $this->assertEquals($testData->code,OpenLocationCode::encode($testData->latitude,$testData->longitude,$testData->length),sprintf('Latitude %f, longitude %f and length %d were wrongly encoded.',$testData->latitude,$testData->longitude,$testData->length));
        }
    }

}