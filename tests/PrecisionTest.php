<?php

namespace YOCLIB\OpenLocationCode\Tests;

use PHPUnit\Framework\TestCase;
use YOCLIB\OpenLocationCode\OpenLocationCode;

class PrecisionTest extends TestCase{

    private const EPSILON = 1e-10;

    public function testWidthInDegrees(){
        $this->assertEqualsWithDelta((new OpenLocationCode("67000000+"))->decode()->getLongitudeWidth(),20.,self::EPSILON);
        $this->assertEqualsWithDelta((new OpenLocationCode("67890000+"))->decode()->getLongitudeWidth(),1.,self::EPSILON);
        $this->assertEqualsWithDelta((new OpenLocationCode("6789CF00+"))->decode()->getLongitudeWidth(),0.05,self::EPSILON);
        $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+"))->decode()->getLongitudeWidth(),0.0025,self::EPSILON);
        $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+JM"))->decode()->getLongitudeWidth(),0.000125,self::EPSILON);
        $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+JMP"))->decode()->getLongitudeWidth(),0.00003125,self::EPSILON);
  }

  public function testHeightInDegrees(){
      $this->assertEqualsWithDelta((new OpenLocationCode("67000000+"))->decode()->getLatitudeHeight(),20.,self::EPSILON);
      $this->assertEqualsWithDelta((new OpenLocationCode("67890000+"))->decode()->getLatitudeHeight(),1.,self::EPSILON);
      $this->assertEqualsWithDelta((new OpenLocationCode("6789CF00+"))->decode()->getLatitudeHeight(),0.05,self::EPSILON);
      $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+"))->decode()->getLatitudeHeight(),0.0025,self::EPSILON);
      $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+JM"))->decode()->getLatitudeHeight(),0.000125,self::EPSILON);
      $this->assertEqualsWithDelta((new OpenLocationCode("6789CFGH+JMP"))->decode()->getLatitudeHeight(), 0.000025,self::EPSILON);
  }

}