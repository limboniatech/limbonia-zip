<?php
namespace Limbonia\Widget;

/**
 * Limbonia Input Widget
 *
 * A wrapper around an HTML input tag
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Input extends \Limbonia\Widget
{
  /**
   * @var string $sType -
   */
  protected $sType = '';

  /**
   * Constructor
   *
   * It increments the widget counter and generates a unique (but human readable) name.
   *
   * @param string $sName (optional)
   * @param \Limbonia\Controller $oController (optional)
   * @throws Limbonia\Exception\Object
   */
  public function __construct($sName = null, \Limbonia\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sScript .= "var $this->sName = document.getElementById(\"$this->sName\");\n";
    $this->setParam('type', $this->sType);
    $this->setValue('');
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    //just to make sure the type hasn't changed...
    $this->setParam('type', $this->sType);

    return parent::init();
  }

  /**
   * Set the this input value to the specified string
   *
   * @param string $sValue
   */
  public function setValue($sValue = '')
  {
    $sValue = (string)$sValue;
    $this->setParam('value', $sValue);

    if ($this->bInit)
    {
      $this->writeJavascript("{$this->sName}.value = '$sValue';");
    }
  }

  /**
   * Return the current value of this input
   *
   * @return string
   */
  public function getValue()
  {
    return $this->getParam('value');
  }
}