<?php
namespace Omniverse\Widget;

class TransferSelect extends \Omniverse\Widget
{
  /**
  * @var object $oFrom - Select box that the select options come *from*.
  * @access protected
  */
  protected $oFrom = NULL;

  /**
  * @var object $oTo - Select box that the select options go *to*.
  * @access protected
  */
  protected $oTo = NULL;

  /**
  * Constructor
  *
  * Call the parent constructor and create the child select objects.
  *
  * @access public
  */
  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->oFrom = $this->getController()->widgetFactory('Select', 'From_' . $this->sName);
    $this->sFrom = $this->oFrom->getID();
    $this->oFrom->IsMultiple(TRUE);
    $this->oTo = $this->getController()->widgetFactory('Select', $this->sName);
    $this->oTo->IsMultiple(TRUE);
    $this->sTo = $this->oTo->getID();
  }

  protected function init()
  {
    $this->sPreScript .= "<table class=\"OmnisysTransferSelectTable\">\n";
    $this->sPreScript .= "<tr>\n";
    $this->sPreScript .= "  <td class=\"OmnisysTransferSelectCell\">\n";
    $this->sPreScript .= $this->fromSelect();
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "  <td class=\"OmnisysTransferSelectCell\">\n";
    $this->sPreScript .= '    ' . $this->moveToButton() . "\n";
    $this->sPreScript .= "    <br>\n";
    $this->sPreScript .= '    ' . $this->moveFromButton() . "\n";
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "  <td class=\"OmnisysTransferSelectCell\">\n";
    $this->sPreScript .= $this->toSelect();
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "</tr>\n";
    $this->sPreScript .= "<tr>\n";
    $this->sPreScript .= "<table>\n";
    return TRUE;
  }

  public function fromSelect()
  {
    return $this->oFrom->__toString();
  }

  public function toSelect()
  {
    return $this->oTo->__toString();
  }

  protected function button($sValue=NULL, $sOnClick=NULL)
  {
    return "<input type=\"button\" value=\"$sValue\" onClick=\"$sOnClick\">";
  }

  public function moveToButton($sValue=NULL)
  {
    $sValue = empty($sValue) ? 'Move ->' : $sValue;
    return $this->button($sValue, "Omnisys_MoveOptions('$this->sFrom', '$this->sTo');");
  }

  public function moveFromButton($sValue=NULL)
  {
    $sValue = empty($sValue) ? 'Move <-' : $sValue;
    return $this->button($sValue, "Omnisys_MoveOptions('$this->sTo', '$this->sFrom');");
  }

  public function moveAllToButton($sValue=NULL)
  {
    $sValue = empty($sValue) ? 'Move All ->' : $sValue;
    return $this->button($sValue, "Omnisys_SelectAll('$this->sFrom'); Omnisys_MoveOptions('$this->sFrom', '$this->sTo');");
  }

  public function moveAllFromButton($sValue=NULL)
  {
    $sValue = empty($sValue) ? 'Move All <-' : $sValue;
    return $this->button($sValue, "Omnisys_SelectAll('$this->sTo'); Omnisys_MoveOptions('$this->sTo', '$this->sFrom');");
  }

  public function submitButton($sValue=NULL, $sName=NULL)
  {
    $sValue = empty($sValue) ? 'Submit' : $sValue;
    $sName = empty($sName) ? 'Submit' : $sName;
    return "<input type=\"submit\" name=\"$sName\" value=\"$sValue\" onClick=\"Omnisys_SelectAll('$this->sFrom'); Omnisys_SelectAll('$this->sTo');\"></td>\n";
  }

  public function addFromOption($sTitle, $sValue=NULL)
  {
    $this->oFrom->addOption($sTitle, $sValue);
  }

  public function removeFromOption($sTitle)
  {
    $this->oFrom->removeOption($sTitle);
  }

  public function addFromArray($hData, $bhash=TRUE)
  {
    $this->oFrom->addArray($hData, $bhash);
  }

  public function addFromEvent($sEvent, $sHandler)
  {
    $this->oFrom->addEvent($sEvent, $sHandler);
  }

  public function getFromName()
  {
    return $this->oFrom->getName();
  }

  public function addToOption($sTitle, $sValue=NULL)
  {
    $this->oTo->addOption($sTitle, $sValue);
  }

  public function removeToOption($sTitle)
  {
    $this->oTo->removeOption($sTitle);
  }

  public function addToArray($hData, $bhash=TRUE)
  {
    $this->oTo->addArray($hData, $bhash);
  }

  public function addToEvent($sEvent, $sHandler)
  {
    $this->oTo->addEvent($sEvent, $sHandler);
  }

  public function getToName()
  {
    return $this->oTo->getName();
  }
}