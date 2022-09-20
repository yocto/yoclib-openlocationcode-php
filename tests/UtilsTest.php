<?php
namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class UtilsTest extends TestCase{

    public const PRECISION = 1e-10;

    public function testClipping(){
       $this->assertEquals(OpenLocationCode::encode(-90,5),OpenLocationCode::encode(-91,5),'Clipping of negative latitude doesn\'t work.');
       $this->assertEquals(OpenLocationCode::encode(90,5),OpenLocationCode::encode(91,5),'Clipping of positive latitude doesn\'t work.');
       $this->assertEquals(OpenLocationCode::encode(5,175),OpenLocationCode::encode(5,-185),'Clipping of negative longitude doesn\'t work.');
       $this->assertEquals(OpenLocationCode::encode(5,175),OpenLocationCode::encode(5,-905),'Clipping of very long negative longitude doesn\'t work.');
       $this->assertEquals(OpenLocationCode::encode(5,-175),OpenLocationCode::encode(5,905),'Clipping of very long positive longitude doesn\'t work.');
    }

    public function testMaxCodeLength(){
        $code = OpenLocationCode::encode(51.3701125,-10.202665625,1000000);
        $this->assertEquals(OpenLocationCode::MAX_DIGIT_COUNT+1,strlen($code),'Encoded code should have a length of MAX_DIGIT_COUNT + 1 for the plus symbol');
        $this->assertTrue(OpenLocationCode::isValidCode($code),true);

        $tooLongCode = $code.'W';
        $this->assertTrue(OpenLocationCode::isValidCode($tooLongCode),'Too long code with all valid characters should be valid.');

        $tooLongCode = $code.'U';
        $this->assertFalse(OpenLocationCode::isValidCode($tooLongCode),'Too long code with invalid character should be invalid.');
    }

}