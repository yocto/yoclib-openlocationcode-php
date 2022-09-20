<?php
namespace YOCLIB\OpenLocationCode;

class CodeArea{

    /**@var double $southLatitude*/
    private $southLatitude;
    /**@var double $westLongitude*/
    private $westLongitude;
    /**@var double $northLatitude*/
    private $northLatitude;
    /**@var double $eastLongitude*/
    private $eastLongitude;
    /**@var int $length*/
    private $length;

    /**
     * @param double $southLatitude
     * @param double $westLongitude
     * @param double $northLatitude
     * @param double $eastLongitude
     * @param int $length
     */
    public function __construct($southLatitude,$westLongitude,$northLatitude,$eastLongitude,$length){
        $this->southLatitude = $southLatitude;
        $this->westLongitude = $westLongitude;
        $this->northLatitude = $northLatitude;
        $this->eastLongitude = $eastLongitude;
        $this->length = $length;
    }

    /**
     * @return double
     */
    public function getSouthLatitude(){
        return $this->southLatitude;
    }

    /**
     * @return double
     */
    public function getWestLongitude(){
        return $this->westLongitude;
    }

    /**
     * @return double
     */
    public function getLatitudeHeight(){
        return $this->northLatitude - $this->southLatitude;
    }

    /**
     * @return double
     */
    public function getLongitudeWidth(){
        return $this->eastLongitude - $this->westLongitude;
    }

    /**
     * @return double
     */
    public function getCenterLatitude(){
        return ($this->southLatitude + $this->northLatitude)/2;
    }

    /**
     * @return double
     */
    public function getCenterLongitude(){
        return ($this->westLongitude + $this->eastLongitude)/2;
    }

    /**
     * @return double
     */
    public function getNorthLatitude(){
        return $this->northLatitude;
    }

    /**
     * @return double
     */
    public function getEastLongitude(){
        return $this->eastLongitude;
    }

    /**
     * @return int
     */
    public function getLength(){
        return $this->length;
    }

}