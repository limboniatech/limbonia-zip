<?php
use PHPUnit\Framework\TestCase;

class ConfigObj
{
  use \Limbonia\Traits\Config;

  public function __construct($xIni)
  {
    $this->readIni($xIni);
  }
}

class ConfigTest extends TestCase
{
  public function testConstructor()
  {
    $oConfig = new ConfigObj(__DIR__ . '/config.ini');
    $this->assertEquals('world', $oConfig->getValue('hello'));
  }
}
