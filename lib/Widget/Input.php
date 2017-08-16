<?php
namespace Omniverse\Widget;

class Input extends \Omniverse\Widget
{
  /**
  * @var string $sType -
  * @access protected
  */
  protected $sType = '';

  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sScript .= "var $this->sName = document.getElementById(\"$this->sName\");\n";
    $this->setParam('type', $this->sType);
    $this->setValue('');
  }

  protected function init()
  {
    //just to make sure the type hasn't changed...
    $this->setParam('type', $this->sType);

    return parent::init();
  }

  public function getType()
  {
    return $this->sType;
  }

  public function setValue($sValue='')
  {
    $sValue = (string)$sValue;
    $this->setParam('value', $sValue);

    if ($this->bInit)
    {
      $this->writeJavascript("{$this->sName}.value = '$sValue';");
    }
  }

  public function getValue()
  {
    return $this->getParam('value');
  }
}