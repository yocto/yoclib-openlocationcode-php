<?php
namespace YOCLIB\OpenLocationCode;

use Exception;
use InvalidArgumentException;

class OpenLocationCode{

    public const CODE_PRECISION_NORMAL = 10;

    public const CODE_ALPHABET = "23456789CFGHJMPQRVWX";

    public const SEPARATOR = '+';

    public const PADDING_CHARACTER = '0';

    private const SEPARATOR_POSITION = 8;

    public const MAX_DIGIT_COUNT = 15;

    private const PAIR_CODE_LENGTH = 10;

    private const GRID_CODE_LENGTH = 15-10;//MAX_DIGIT_COUNT - PAIR_CODE_LENGTH

    private const ENCODING_BASE = 20;//strlen(CODE_ALPHABET)

    private const LATITUDE_MAX = 90;

    private const LONGITUDE_MAX = 180;

    private const GRID_COLUMNS = 4;

    private const GRID_ROWS = 5;

    private const LAT_INTEGER_MULTIPLIER = 8000 * 3125;

    private const LNG_INTEGER_MULTIPLIER = 8000 * 1024;

    private const LAT_MSP_VALUE = (8000 * 3125) * 20 * 20;//LAT_INTEGER_MULTIPLIER * ENCODING_BASE * ENCODING_BASE

    private const LNG_MSP_VALUE = (8000 * 1024) * 20 * 20;//LNG_INTEGER_MULTIPLIER * ENCODING_BASE * ENCODING_BASE

    private $code;

    /**
     * @param double|string $latitudeOrCode
     * @param ?double $longitude
     * @param ?int $codeLength
     */
    public function __construct($latitudeOrCode,$longitude=null,$codeLength=self::CODE_PRECISION_NORMAL){
        if(func_num_args()===1 && is_string($latitudeOrCode)){
            $code = $latitudeOrCode;
            if(!self::isValidCode(strtoupper($code))){
                throw new InvalidArgumentException('The provided code \''.$code.'\' is not a valid Open Location Code.');
            }
            $this->code = strtoupper($code);
        }elseif((func_num_args()==2 || func_num_args()==3) && is_numeric($latitudeOrCode) && is_numeric($longitude) && is_int($codeLength)){
            $latitude = $latitudeOrCode;

            $codeLength = min($codeLength,self::MAX_DIGIT_COUNT);
            if($codeLength<self::PAIR_CODE_LENGTH && $codeLength%2==1 || $codeLength<4){
                throw new InvalidArgumentException('Illegal code length '.$codeLength);
            }
            $latitude = self::clipLatitude($latitude);
            $longitude = self::normalizeLongitude($longitude);

            if($latitude==self::LATITUDE_MAX){
                $latitude = $latitude-0.9*self::computeLatitudePrecision($codeLength);
            }

            $revCode = '';

            $latVal = round(($latitude+self::LATITUDE_MAX)*self::LAT_INTEGER_MULTIPLIER*1e6)/1e6;
            $lngVal = round(($longitude+self::LONGITUDE_MAX)*self::LNG_INTEGER_MULTIPLIER*1e6)/1e6;

            if($codeLength>self::PAIR_CODE_LENGTH){
                for($i=0;$i<self::GRID_CODE_LENGTH;$i++){
                    $latDigit = $latVal%self::GRID_ROWS;
                    $lngDigit = $lngVal%self::GRID_COLUMNS;
                    $ndx = (int) ($latDigit*self::GRID_COLUMNS+$lngDigit);
                    $revCode .= substr(self::CODE_ALPHABET,$ndx,1);
                    $latVal /= self::GRID_ROWS;
                    $lngVal /= self::GRID_COLUMNS;
                }
            }else{
                $latVal = $latVal/pow(self::GRID_ROWS,self::GRID_CODE_LENGTH);
                $lngVal = $lngVal/pow(self::GRID_COLUMNS,self::GRID_CODE_LENGTH);
            }

            for($i=0;$i<self::PAIR_CODE_LENGTH/2;$i++){
                $revCode .= substr(self::CODE_ALPHABET,$lngVal%self::ENCODING_BASE,1);
                $revCode .= substr(self::CODE_ALPHABET,$latVal%self::ENCODING_BASE,1);
                $latVal /= self::ENCODING_BASE;
                $lngVal /= self::ENCODING_BASE;
                if($i===0){
                    $revCode .= self::SEPARATOR;
                }
            }

            $code = strrev($revCode);

            if($codeLength<self::SEPARATOR_POSITION){
                for($i=$codeLength;$i<self::SEPARATOR_POSITION;$i++){
                    $code[$i] = self::PADDING_CHARACTER;
                }
            }

            $this->code = substr($code,0,max(self::SEPARATOR_POSITION+1,$codeLength+1));
        }else{
            throw new InvalidArgumentException('Wrong arguments passed to constructor.'.var_export(func_get_args(),true));
        }
    }

    /**
     * @return string
     */
    public function getCode(){
        return $this->code;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param ?int $codeLength
     * @return string
     */
    public static function encode($latitude,$longitude,$codeLength=self::CODE_PRECISION_NORMAL){
        return (new self($latitude,$longitude,$codeLength))->getCode();
    }

    /**
     * @return CodeArea
     * @throws Exception
     */
    public function decode(){
        if(!self::isFullCode($this->code)){
            throw new Exception('Method decode() could only be called on valid full codes, code was '.$this->code.'.');
        }
        $clean = str_replace(self::PADDING_CHARACTER,'',str_replace(self::SEPARATOR,'',$this->code));

        $latVal = -self::LATITUDE_MAX * self::LAT_INTEGER_MULTIPLIER;
        $lngVal = -self::LONGITUDE_MAX * self::LNG_INTEGER_MULTIPLIER;

        $latPlaceVal = self::LAT_MSP_VALUE;
        $lngPlaceVal = self::LNG_MSP_VALUE;
        for($i=0;$i<min(strlen($clean),self::PAIR_CODE_LENGTH);$i+=2){
            $latPlaceVal /= self::ENCODING_BASE;
            $lngPlaceVal /= self::ENCODING_BASE;
            $latVal += strpos(self::CODE_ALPHABET,substr($clean,$i,1)) * $latPlaceVal;
            $latVal += strpos(self::CODE_ALPHABET,substr($clean,$i+1,1)) * $lngPlaceVal;
        }
        for($i=self::PAIR_CODE_LENGTH;$i<min(strlen($clean),self::MAX_DIGIT_COUNT);$i++){
            $latPlaceVal /= self::GRID_ROWS;
            $lngPlaceVal /= self::GRID_COLUMNS;
            $digit = strpos(self::CODE_ALPHABET,substr($clean,$i,1));
            $row = $digit/self::GRID_COLUMNS;
            $col = $digit%self::GRID_COLUMNS;
            $latVal += $row*$latPlaceVal;
            $lngVal += $col*$lngPlaceVal;
        }
        $latitudeLo = $latVal/self::LAT_INTEGER_MULTIPLIER;
        $longitudeLo = $lngVal/self::LNG_INTEGER_MULTIPLIER;
        $latitudeHi = ($latVal+$latPlaceVal)/self::LAT_INTEGER_MULTIPLIER;
        $longitudeHi = ($lngVal+$lngPlaceVal)/self::LNG_INTEGER_MULTIPLIER;

        return new CodeArea($latitudeLo,$longitudeLo,$latitudeHi,$longitudeHi,min(strlen($clean),self::MAX_DIGIT_COUNT));
    }

    /**
     * @param $code
     * @return CodeArea
     * @throws InvalidArgumentException
     */
    public static function decodeByCode($code){
        return (new self($code))->decode();
    }

    /**
     * @return bool
     */
    public function isFull(){
        return strpos($this->code,self::SEPARATOR)===self::SEPARATOR_POSITION;
    }

    /**
     * @param string $code
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function isFullByCode($code){
        return (new self($code))->isFull();
    }

    /**
     * @return bool
     */
    public function isShort(){
        return strpos($this->code,self::SEPARATOR)>=0 && strpos($this->code,self::SEPARATOR)<self::SEPARATOR_POSITION;
    }

    /**
     * @param string $code
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function isShortByCode($code){
        return (new self($code))->isShort();
    }

    /**
     * @return bool
     */
    public function isPadded(){
        return strpos($this->code,self::PADDING_CHARACTER)>=0;
    }

    /**
     * @param $code
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function isPaddedByCode($code){
        return (new self($code))->isPadded();
    }

    public function shorten($referenceLatitude,$referenceLongitude){
        if(!$this->isFull()){
            throw new InvalidArgumentException('shorten() method could only be called on a full code.');
        }
        if($this->isPadded()){
            throw  new InvalidArgumentException('shorten() method can not be called on a padded code.');
        }

        $codeArea = $this->decode();
        $range = max(abs($referenceLatitude - $codeArea->getCenterLatitude()),$referenceLongitude-$codeArea->getCenterLongitude());

        for($i=4;$i>=1;$i--){
            if($range<(self::computeLatitudePrecision($i*2)*0.3)){
                return new self(substr($this->code,$i*2));
            }
        }
        throw new InvalidArgumentException('Reference location is too far from the Open Location Code center.');
    }

    /**
     * @param double $referenceLatitude
     * @param double $referenceLongitude
     * @return self
     */
    public function recover($referenceLatitude,$referenceLongitude){
        if($this->isFull()){
            return $this;
        }
        $referenceLatitude = self::clipLatitude($referenceLatitude);
        $referenceLongitude = self::normalizeLongitude($referenceLongitude);

        $digitsToRecover = self::SEPARATOR_POSITION - strpos($this->code,self::SEPARATOR);
        $prefixPrecision = pow(self::ENCODING_BASE,2-($digitsToRecover/2));

        $recoveredPrefix = substr((new self($referenceLatitude,$referenceLongitude))->getCode(),0,$digitsToRecover);
        $recovered = new self($recoveredPrefix.$this->code);
        $recoveredCodeArea = $recovered->decode();

        $recoveredLatitude = $recoveredCodeArea->getCenterLatitude();
        $recoveredLongitude = $recoveredCodeArea->getCenterLongitude();

        $latitudeDiff = $recoveredLatitude - $referenceLatitude;
        if($latitudeDiff>$prefixPrecision/2 && $recoveredLatitude-$prefixPrecision>-self::LATITUDE_MAX){
            $recoveredLatitude -= $prefixPrecision;
        }elseif($latitudeDiff<-$prefixPrecision/2 && $recoveredLatitude+$prefixPrecision<self::LATITUDE_MAX){
            $recoveredLatitude += $prefixPrecision;
        }

        $longitudeDiff = $recoveredCodeArea->getCenterLongitude() - $referenceLongitude;
        if($longitudeDiff>$prefixPrecision/2){
            $recoveredLongitude -= $prefixPrecision;
        }elseif($longitudeDiff<-$prefixPrecision/2){
            $recoveredLongitude += $prefixPrecision;
        }

        return new self($recoveredLatitude,$recoveredLongitude,strlen($recovered->getCode())-1);
    }

    /**
     * @param double $latitude
     * @param double $longitude
     * @return bool
     * @throws Exception
     */
    public function contains($latitude,$longitude){
        $codeArea = $this->decode();

        return $codeArea->getSouthLatitude()<=$latitude && $latitude<$codeArea->getNorthLatitude() && $codeArea->getWestLongitude()<=$longitude && $longitude<$codeArea->getEastLongitude();
    }

    //TODO equals

    public function __toString(){
        return $this->getCode();
    }

    public static function isValidCode($code){
        if($code===null || strlen($code)<2){
            return false;
        }
        $code = strtoupper($code);

        $separatorPosition = strpos($code,self::SEPARATOR);
        if($separatorPosition===false){
            return false;
        }
        if($separatorPosition!=strpos($code,self::SEPARATOR)){
            return false;
        }
        if($separatorPosition%2!==0 || $separatorPosition>self::SEPARATOR_POSITION){
            return false;
        }

        if($separatorPosition==self::SEPARATOR_POSITION){
            if(strpos(self::CODE_ALPHABET,substr($code,0,1))>8){
                return false;
            }

            if(strpos(self::CODE_ALPHABET,substr($code,1,1))>17){
                return false;
            }
        }

        $paddingStarted = false;
        for($i=0;$i<$separatorPosition;$i++){
            if(strpos(self::CODE_ALPHABET,substr($code,$i,1))===false && substr($code,$i,1)!==self::PADDING_CHARACTER){
                return false;
            }
            if($paddingStarted){
                if(substr($code,$i,1)!==self::PADDING_CHARACTER){
                    return false;
                }
            }elseif(substr($code,$i,1)==self::PADDING_CHARACTER){
                $paddingStarted = true;

                if($separatorPosition<self::SEPARATOR_POSITION){
                    return false;
                }
                if($i!=2 && $i!=4 && $i!=6){
                    return false;
                }
            }
        }

        if(strlen($code)>$separatorPosition+1){
            if($paddingStarted){
                return false;
            }
            if(strlen($code)==$separatorPosition+2){
                return false;
            }
            for($i=$separatorPosition+1;$i<strlen($code);$i++){
                if(strpos(self::CODE_ALPHABET,substr($code,$i,1))===false){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $code
     * @return boolean
     */
    public static function isFullCode($code){
        try{
            return (new self($code))->isFull();
        }catch(InvalidArgumentException $e){
            return false;
        }
    }

    /**
     * @param string $code
     * @return boolean
     */
    public static function isShortCode($code){
        try{
            return (new self($code))->isShort();
        }catch(InvalidArgumentException $e){
            return false;
        }
    }

    /**
     * @param double $latitude
     * @return double
     */
    private static function clipLatitude($latitude){
        return min(max($latitude,-self::LATITUDE_MAX),self::LATITUDE_MAX);
    }

    /**
     * @param double $longitude
     * @return double
     */
    private static function normalizeLongitude($longitude){
        if($longitude>=-self::LONGITUDE_MAX && $longitude<self::LONGITUDE_MAX){
            return $longitude;
        }
        $CIRCLE_DEG = 2 * self::LONGITUDE_MAX;
        return ($longitude % $CIRCLE_DEG + $CIRCLE_DEG + self::LONGITUDE_MAX) % $CIRCLE_DEG - self::LONGITUDE_MAX;
    }

    /**
     * @param int $codeLength
     * @return double
     */
    private static function computeLatitudePrecision($codeLength){
        if($codeLength<=self::CODE_PRECISION_NORMAL){
            return pow(self::ENCODING_BASE,($codeLength / -2 + 2));
        }
        return pow(self::ENCODING_BASE,-3) / pow(self::GRID_ROWS,$codeLength - self::PAIR_CODE_LENGTH);
    }

}