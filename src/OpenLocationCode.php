<?php
namespace YOCLIB\OpenLocationCode;

use InvalidArgumentException;
use RuntimeException;

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
     * @throws InvalidArgumentException
     */
    public function __construct($latitudeOrCode,$longitude=null,$codeLength=self::CODE_PRECISION_NORMAL){
        if(func_num_args()===1 && is_string($latitudeOrCode)){
            $code = (string) $latitudeOrCode;
            if(!self::isValidCode(strtoupper($code))){
                throw new InvalidArgumentException('The provided code \''.$code.'\' is not a valid Open Location Code.');
            }
            $this->code = strtoupper($code);
        }elseif((func_num_args()==2 || func_num_args()==3) && is_numeric($latitudeOrCode) && is_numeric($longitude) && is_int($codeLength)){
            $latitude = (double) $latitudeOrCode;
            $longitude = (double) $longitude;
            $codeLength = $codeLength ?? self::PAIR_CODE_LENGTH;

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

            $latVal = self::floatToInt(round(($latitude+self::LATITUDE_MAX)*self::LAT_INTEGER_MULTIPLIER,6));
            $lngVal = self::floatToInt(round(($longitude+self::LONGITUDE_MAX)*self::LNG_INTEGER_MULTIPLIER,6));

            if($codeLength>self::PAIR_CODE_LENGTH){
                for($i=0;$i<self::GRID_CODE_LENGTH;$i++){
                    $latDigit = $latVal%self::GRID_ROWS;
                    $lngDigit = $lngVal%self::GRID_COLUMNS;
                    $ndx = self::floatToInt($latDigit*self::GRID_COLUMNS+$lngDigit);
                    $revCode .= substr(self::CODE_ALPHABET,$ndx,1);
                    $latVal = self::floatToInt($latVal/self::GRID_ROWS);
                    $lngVal = self::floatToInt($lngVal/self::GRID_COLUMNS);
                }
            }else{
                $latVal = self::floatToInt($latVal/(self::GRID_ROWS ** self::GRID_CODE_LENGTH));
                $lngVal = self::floatToInt($lngVal/(self::GRID_COLUMNS ** self::GRID_CODE_LENGTH));
            }

            for($i=0;$i<self::PAIR_CODE_LENGTH/2;$i++){
                $revCode .= substr(self::CODE_ALPHABET,$lngVal%self::ENCODING_BASE,1);
                $revCode .= substr(self::CODE_ALPHABET,$latVal%self::ENCODING_BASE,1);
                $latVal = self::floatToInt($latVal/self::ENCODING_BASE);
                $lngVal = self::floatToInt($lngVal/self::ENCODING_BASE);
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
     */
    public function decode(){
        if(!self::isFullCode($this->code)){
            throw new RuntimeException('Method decode() could only be called on valid full codes, code was '.$this->code.'.');
        }
        $clean = str_replace(self::PADDING_CHARACTER,'',str_replace(self::SEPARATOR,'',$this->code));

        $latVal = self::floatToInt(-self::LATITUDE_MAX * self::LAT_INTEGER_MULTIPLIER);
        $lngVal = self::floatToInt(-self::LONGITUDE_MAX * self::LNG_INTEGER_MULTIPLIER);

        $latPlaceVal = self::floatToInt(self::LAT_MSP_VALUE);
        $lngPlaceVal = self::floatToInt(self::LNG_MSP_VALUE);
        for($i=0;$i<min(strlen($clean),self::PAIR_CODE_LENGTH);$i+=2){
            $latPlaceVal = self::floatToInt($latPlaceVal/self::ENCODING_BASE);
            $lngPlaceVal = self::floatToInt($lngPlaceVal/self::ENCODING_BASE);
            $latVal += self::floatToInt(self::indexOf(self::CODE_ALPHABET,substr($clean,$i,1)) * $latPlaceVal);
            $lngVal += self::floatToInt(self::indexOf(self::CODE_ALPHABET,substr($clean,$i+1,1)) * $lngPlaceVal);
        }
        for($i=self::PAIR_CODE_LENGTH;$i<min(strlen($clean),self::MAX_DIGIT_COUNT);$i++){
            $latPlaceVal = self::floatToInt($latPlaceVal/self::GRID_ROWS);
            $lngPlaceVal = self::floatToInt($lngPlaceVal/self::GRID_COLUMNS);
            $digit = self::indexOf(self::CODE_ALPHABET,substr($clean,$i,1));
            $row = self::floatToInt($digit/self::GRID_COLUMNS);
            $col = $digit%self::GRID_COLUMNS;
            $latVal += self::floatToInt($row*$latPlaceVal);
            $lngVal += self::floatToInt($col*$lngPlaceVal);
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
        return self::indexOf($this->code,self::SEPARATOR)==self::SEPARATOR_POSITION;
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
        return self::indexOf($this->code,self::SEPARATOR)>=0 && self::indexOf($this->code,self::SEPARATOR)<self::SEPARATOR_POSITION;
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
        return self::indexOf($this->code,self::PADDING_CHARACTER)>=0;
    }

    /**
     * @param $code
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function isPaddedByCode($code){
        return (new self($code))->isPadded();
    }

    /**
     * @param double $referenceLatitude
     * @param double $referenceLongitude
     * @return OpenLocationCode
     */
    public function shorten($referenceLatitude,$referenceLongitude){
        if(!$this->isFull()){
            throw new InvalidArgumentException('shorten() method could only be called on a full code.');
        }
        if($this->isPadded()){
            throw new InvalidArgumentException('shorten() method can not be called on a padded code.');
        }

        $codeArea = $this->decode();
        $range = max(abs($referenceLatitude - $codeArea->getCenterLatitude()),abs($referenceLongitude - $codeArea->getCenterLongitude()));

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

        $digitsToRecover = self::SEPARATOR_POSITION - self::indexOf($this->code,self::SEPARATOR);
        $prefixPrecision = self::ENCODING_BASE ** (2-($digitsToRecover/2));

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
     */
    public function contains($latitude,$longitude){
        $codeArea = $this->decode();

        return $codeArea->getSouthLatitude()<=$latitude && $latitude<$codeArea->getNorthLatitude() && $codeArea->getWestLongitude()<=$longitude && $longitude<$codeArea->getEastLongitude();
    }

    public function __toString(){
        return $this->getCode();
    }

    public static function isValidCode($code){
        if($code===null || strlen($code)<2){
            return false;
        }
        $code = strtoupper($code);

        $separatorPosition = self::indexOf($code,self::SEPARATOR);
        if($separatorPosition==-1){
            return false;
        }
        if($separatorPosition!==self::indexOf($code,self::SEPARATOR)){
            return false;
        }
        if($separatorPosition%2!==0 || $separatorPosition>self::SEPARATOR_POSITION){
            return false;
        }

        if($separatorPosition==self::SEPARATOR_POSITION){
            if(self::indexOf(self::CODE_ALPHABET,substr($code,0,1))>8){
                return false;
            }

            if(self::indexOf(self::CODE_ALPHABET,substr($code,1,1))>17){
                return false;
            }
        }

        $paddingStarted = false;
        for($i=0;$i<$separatorPosition;$i++){
            if(self::indexOf(self::CODE_ALPHABET,substr($code,$i,1))==-1 && substr($code,$i,1)!==self::PADDING_CHARACTER){
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
                if(self::indexOf(self::CODE_ALPHABET,substr($code,$i,1))==-1){
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
        return fmod((fmod($longitude,$CIRCLE_DEG) + $CIRCLE_DEG + self::LONGITUDE_MAX),$CIRCLE_DEG) - self::LONGITUDE_MAX;
    }

    /**
     * @param int $codeLength
     * @return double
     */
    private static function computeLatitudePrecision($codeLength){
        if($codeLength<=self::CODE_PRECISION_NORMAL){
            return self::ENCODING_BASE ** ($codeLength / -2 + 2);
        }
        return (self::ENCODING_BASE ** -3) / (self::GRID_ROWS ** ($codeLength - self::PAIR_CODE_LENGTH));
    }

    private static function indexOf($haystack,$needle,$offset=0){
        $pos = strpos($haystack,$needle,$offset);
        if($pos===false){
            return -1;
        }
        return $pos;
    }

    private static function floatToInt($float){
        return (intval(($float+0).''));
    }

}