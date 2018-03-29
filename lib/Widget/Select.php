<?php
namespace Limbonia\Widget;

/**
 * Limbonia Select Widget
 *
 * A wrapper around an HTML select tag
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Select extends \Limbonia\Widget
{
  protected $bMultiple = false;

  /**
   * @var string $aSelected -
   */
  protected $aSelected = [];

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
//    $this->aScript = [$this->sWebShareDir . "/select.js"];
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    if (count($this->aSelected) > 0)
    {
      foreach ($this->aContent as $iKey => $xData)
      {
        $sValue = $xData->getRawParam('value');

        if (self::isOption($xData) && in_array($sValue, $this->aSelected))
        {
          $this->aContent[$iKey]->setParam('selected', 'selected');
        }
      }
    }

    return parent::init();
  }

  /**
   * Set the selected field of this select tag
   *
   * @param mixed $xSelected
   */
  public function setSelected($xSelected = null)
  {
    if (is_null($xSelected))
    {
      $this->aSelected = [];
    }
    elseif (is_array($xSelected))
    {
      $this->aSelected = $xSelected;
    }
    else
    {
      $this->aSelected = [(string)$xSelected];
    }
  }

  /**
   * Is the specified data is an option object
   *
   * @param \Limbonia\Widget\Option $xData
   * @return boolean
   */
  protected function isOption($xData)
  {
    return ($xData instanceof \Limbonia\Widget\Option);
  }

  /**
   * Add a new option to this select object
   *
   * @param string $sTitle
   * @param string $sValue (optional)
   */
  public function addOption($sTitle, $sValue = null)
  {
    if (is_null($sValue))
    {
      $sValue = $sTitle;
    }

    //We keep track of all additions, even if they happen *after* bInit == TRUE.
    $oOption = \Limbonia\Widget::factory('Option');
    $oOption->setParam('value', $sValue);
    $oOption->addText((string)$sTitle);
    $this->addTag($oOption);

    if ($this->bInit)
    {
      $this->writeJavascript("Limbonia_addOption('$this->sName', '$sTitle', '$sValue');");
    }
  }

  /**
   * Remove the specified option from this select
   *
   * @param string $sTitle
   * @return boolean
   */
  public function removeOption($sTitle)
  {
    foreach (array_values($this->aRegisteredData) as $xData)
    {
      if ($this->isOption($xData))
      {
        continue;
      }

      if ($xData->getTagContent() !== $sTitle)
      {
        continue;
      }

      $iIndex = $this->removeWidget($xData);

      if ($this->bInit && $iIndex !== false)
      {
        $this->writeJavascript("Limbonia_removeOption('$this->sName', $iIndex);");
      }

      return true;
    }

    return false;
  }

  /**
   * Add an entire array of options all at once
   *
   * @param array $hData
   * @param boolean $bHash
   */
  public function addArray($hData, $bHash = true)
  {
    if (!is_bool($bHash))
    {
      $bHash = (boolean)$bHash;
    }

    if (is_array($hData) && count($hData) > 0)
    {
      foreach ($hData as $sValue => $sTitle)
      {
        $sTemp = $bHash ? $sValue : $sTitle;
        $this->addOption($sTitle, $sTemp);
      }
    }
  }

  /**
   * Either return the current multiple setting, or change it if one is specified
   *
   * @param boolean $bMultiple (optional)
   * @return boolean
   */
  public function isMultiple($bMultiple = null)
  {
    if (!is_null($bMultiple))
    {
      $this->bMultiple = (boolean)$bMultiple;
      $this->setParam('multiple', ($this->bMultiple ? "multiple" : ''));
      $this->setParam('name', ($this->bMultiple ? $this->sName . '[]' : $this->sName));
    }

    return $this->bMultiple;
  }
}