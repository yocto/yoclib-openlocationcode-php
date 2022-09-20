<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class RecoverTest extends TestCase{

    public function testRecoveryNearSouthPole(){
        $olc = new OpenLocationCode('XXXXXX+XX');
        $this->assertEquals('2CXXXXXX+XX',$olc->recover(-81.0,0.0)->getCode());
    }

    public function testRecoveryNearNorthPole(){
        $olc = new OpenLocationCode('2222+22');
        $this->assertEquals('CFX22222+22',$olc->recover(89.6,0.0)->getCode());
    }

}