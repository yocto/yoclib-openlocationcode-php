<?php
namespace YOCLIB\OpenLocationCode\Tests;

class TestUtils{

    /**
     * @param string $testFile
     * @return array
     */
    public static function getTestFileLines($testFile){
        return file(__DIR__.'\\'.$testFile);
    }

}