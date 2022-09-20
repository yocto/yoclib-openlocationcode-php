# yocLib - Open Location Code (PHP)

This yocLibrary enables your project to read and write MPEGURL files in PHP.

## Status

[![Build Status](https://travis-ci.com/yocto/yoclib-mpegurl-php.svg?branch=master)](https://travis-ci.com/yocto/yoclib-mpegurl-php)

## Installation

`composer require yocto/yoclib-mpegurl`

## Use

Read:
```php
use YOCLIB\MPEGURL\MPEGURL;

$fileContent = '';
$fileContent .= '#EXTM3U'."\r\n";
$fileContent .= '#EXTINF:123,The example file'."\r\n";
$fileContent .= '/home/user/test.mp3'."\r\n";

$mpegurl = MPEGURL::read($fileContent);
```

Write:
```php
use YOCLIB\MPEGURL\MPEGURL;
use YOCLIB\MPEGURL\Lines\Location;
use YOCLIB\MPEGURL\Lines\Tags\EXTINF;
use YOCLIB\MPEGURL\Lines\Tags\EXTMxU;

$mpegurl = new MPEGURL();
$mpegurl->addLine(new EXTMxU());
$mpegurl->addLine(new EXTINF('123,The example file'));
$mpegurl->addLine(new Location('/home/user/test.mp3'));

$fileContent = MPEGURL::write($mpegurl);
```