<?php
namespace Omniverse\Widget;

class Select extends \Omniverse\Widget
{
  protected $bMultiple = false;

  /**
  * @var string $aSelected -
  * @access protected
  */
  protected $aSelected = [];

  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->aScript = [$this->sWebShareDir . "/select.js"];
  }

  protected function init()
  {
    if (count($this->aSelected) > 0)
    {
      foreach ($this->aContent as $iKey => $xData)
      {
        $sValue = $xData->getRawParam('value');
        if (self::IsOption($xData) && in_array($sValue, $this->aSelected))
        {
          $this->aContent[$iKey]->setParam('selected', 'selected');
        }
      }
    }

    return parent::init();
  }

  public function setSelected($xSelected=NULL)
  {
    if (is_null($xSelected))
    {
      $this->aSelected = array();
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

  protected function isOption($xData)
  {
    return ($xData instanceof \Omniverse\Widget\Option);
  }

  public function addOption($sTitle, $sValue=NULL)
  {
    if (is_null($sValue))
    {
      $sValue = $sTitle;
    }

    //We keep track of all additions, even if they happen *after* bInit == TRUE.
    $oOption = \Omniverse\Widget::factory('Option');
    $oOption->setParam('value', $sValue);
    $oOption->addText((string)$sTitle);
    $this->addTag($oOption);

    if ($this->bInit)
    {
      $this->writeJavascript("Omnisys_addOption('$this->sName', '$sTitle', '$sValue');");
    }
  }

  public function removeOption($sTitle)
  {
    foreach ($this->aRegisteredData as $iKey => $xData)
    {
      if ($xData instanceof \Omniverse\Widget\Option)
      {
        if ($xData->getTagContent() == $sTitle)
        {
          $this->removeWidget($xData);

          if ($this->bInit)
          {
            $this->writeJavascript("Omnisys_removeOption('$this->sName', $iIndex);");
          }

          return TRUE;
        }
      }
    }
    return FALSE;
  }

  public function addArray($hData, $bHash=TRUE)
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

  public function isMultiple($bNew=NULL)
  {
    if (func_num_args() == 0)
    {
      return $this->bMultiple;
    }

    if (!is_bool($bNew))
    {
      trigger_error("The passed parameter was *not* boolean and therefore will be typecast before use.");
      $bNew = (boolean)$bNew;
    }

    $this->bMultiple = $bNew;

    $this->setParam('multiple', ($this->bMultiple ? "multiple" : ''));
    $this->setParam('name', ($this->bMultiple ? $this->sName . '[]' : $this->sName));
  }
}