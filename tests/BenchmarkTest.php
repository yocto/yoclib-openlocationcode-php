<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class BenchmarkTest extends TestCase{

//    public const LOOPS = 1000000;
    public const LOOPS = 100;

    /**
     * @var BenchmarkTest_TestData[]
     */
    private $testDataList = [];

    /**
     * @return void
     */
    public function setUp(): void{
        $testDataList = [];
        for($i=0;$i<self::LOOPS;$i++){
            $this->testDataList[] = new BenchmarkTest_TestData;
        }
    }

    public function testBenchmarkEncode(){
        $start = microtime(true);
        foreach($this->testDataList AS $testData){
            OpenLocationCode::encode($testData->latitude,$testData->longitude,$testData->length);
        }
        $microsecs = intval(microtime(true)-$start);

        printf('Encode %d loops in %d usecs, %.3f usec per call'."\n",self::LOOPS,$microsecs,$microsecs/self::LOOPS);
        $this->assertTrue(true);
    }

    public function testBenchmarkDecode(){
        $start = microtime(true);
        foreach($this->testDataList AS $testData){
            OpenLocationCode::decodeByCode($testData->code);
        }
        $microsecs = intval(microtime(true) - $start);

        printf('Decode %d loops in %d usecs, %.3f usec per call'."\n",self::LOOPS,$microsecs,$microsecs/self::LOOPS);
        $this->assertTrue(true);
    }

}