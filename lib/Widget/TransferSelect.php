<?php
namespace Limbonia\Widget;

/**
 * Limbonia TransferSelect Widget
 *
 * This is a multi-level HTML + JavaScript construct that consists of two
 * multi-select boxes that allow the transfer of data from one to the other then
 * before they are submitted to the parent form.
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TransferSelect extends \Limbonia\Widget
{
  /**
   * @var \Limbonia\Widget\Select $oFrom - Select box that the select options come *from*.
   */
  protected $oFrom = NULL;

  /**
   * @var object $oTo - Select box that the select options go *to*.
   */
  protected $oTo = NULL;

  /**
   * Constructor
   *
   * Call the parent constructor and create the child select objects.
   *
   * @param type $sName
   * @param \Limbonia\Controller $oController
   */
  public function __construct($sName = null, \Limbonia\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->oFrom = $this->getController()->widgetFactory('Select', 'From_' . $this->sName);
    $this->sFrom = $this->oFrom->getID();
    $this->oFrom->isMultiple(TRUE);
    $this->oTo = $this->getController()->widgetFactory('Select', $this->sName);
    $this->oTo->isMultiple(TRUE);
    $this->sTo = $this->oTo->getID();
  }

   /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
 protected function init()
  {
    $this->sPreScript .= "<table class=\"LimboniaTransferSelectTable\">\n";
    $this->sPreScript .= "<tr>\n";
    $this->sPreScript .= "  <td class=\"LimboniaTransferSelectCell\">\n";
    $this->sPreScript .= $this->fromSelect();
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "  <td class=\"LimboniaTransferSelectCell\">\n";
    $this->sPreScript .= '    ' . $this->moveToButton() . "\n";
    $this->sPreScript .= "    <br>\n";
    $this->sPreScript .= '    ' . $this->moveFromButton() . "\n";
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "  <td class=\"LimboniaTransferSelectCell\">\n";
    $this->sPreScript .= $this->toSelect();
    $this->sPreScript .= "  </td>\n";
    $this->sPreScript .= "</tr>\n";
    $this->sPreScript .= "<tr>\n";
    $this->sPreScript .= "<table>\n";
    return TRUE;
  }

  /**
   * Generate and return the HTML of the "From" select
   *
   * @return string
   */
  public function fromSelect()
  {
    return $this->oFrom->__toString();
  }

  /**
   * Generate and return the HTML of the "To" select
   *
   * @return string
   */
  public function toSelect()
  {
    return $this->oTo->__toString();
  }

  /**
   * Generate and return an HTML button based on the specified parameters
   *
   * @param string $sValue (optional)
   * @param string $sOnClick (optional)
   * @return string
   */
  protected function button($sValue = '', $sOnClick = '')
  {
    return "<input type=\"button\" value=\"$sValue\" onClick=\"$sOnClick\">";
  }

  /**
   * Generate and return the HTML for the "Move to" button
   *
   * @param string $sValue (optional)
   * @return string
   */
  public function moveToButton($sValue = '')
  {
    $sValue = empty($sValue) ? 'Move ->' : $sValue;
    return $this->button($sValue, "Limbonia_MoveOptions('$this->sFrom', '$this->sTo');");
  }

  /**
   * Generate and return the HTML for the "Move from" button
   *
   * @param string $sValue (optional)
   * @return string
   */
  public function moveFromButton($sValue = '')
  {
    $sValue = empty($sValue) ? 'Move <-' : $sValue;
    return $this->button($sValue, "Limbonia_MoveOptions('$this->sTo', '$this->sFrom');");
  }

  /**
   * Generate and return the HTML needed to display the "Move All To" button
   *
   * @param string $sValue (optional)
   * @return string
   */
  public function moveAllToButton($sValue = '')
  {
    $sValue = empty($sValue) ? 'Move All ->' : $sValue;
    return $this->button($sValue, "Limbonia_SelectAll('$this->sFrom'); Limbonia_MoveOptions('$this->sFrom', '$this->sTo');");
  }

  /**
   * Generate and return the HTML needed to display the "Move All From" button
   *
   * @param string $sValue (optional)
   * @return string
   */
  public function moveAllFromButton($sValue = '')
  {
    $sValue = empty($sValue) ? 'Move All <-' : $sValue;
    return $this->button($sValue, "Limbonia_SelectAll('$this->sTo'); Limbonia_MoveOptions('$this->sTo', '$this->sFrom');");
  }

  /**
   * Generate and return the HTML needed for the "Submit" button
   *
   * @param string $sValue (optional)
   * @param string $sName (optional)
   * @return string
   */
  public function submitButton($sValue = '', $sName = '')
  {
    $sValue = empty($sValue) ? 'Submit' : $sValue;
    $sName = empty($sName) ? 'Submit' : $sName;
    return "<input type=\"submit\" name=\"$sName\" value=\"$sValue\" onClick=\"Limbonia_SelectAll('$this->sFrom'); Limbonia_SelectAll('$this->sTo');\"></td>\n";
  }

  /**
   * Add a new option to the "From" widget
   *
   * @param sting $sTitle
   * @param string $sValue (optional)
   */
  public function addFromOption($sTitle, $sValue = '')
  {
    $this->oFrom->addOption($sTitle, $sValue);
  }

  /**
   * Remove the specified option from the "From" widget
   *
   * @param string $sTitle
   */
  public function removeFromOption($sTitle)
  {
    $this->oFrom->removeOption($sTitle);
  }

  /**
   * Add a whole array of options to the "From" list, all at once
   *
   * @param array $hData
   * @param boolean $bHash (optional)
   */
  public function addFromArray($hData, $bHash = true)
  {
    $this->oFrom->addArray($hData, $bHash);
  }

  /**
   * Add the specified handler to the specified event on the "From" widget
   *
   * @param string $sEvent
   * @param string $sHandler
   */
  public function addFromEvent($sEvent, $sHandler)
  {
    $this->oFrom->addEvent($sEvent, $sHandler);
  }

  /**
   * Return the name of the "From" widget
   *
   * @return string
   */
  public function getFromName()
  {
    return $this->oFrom->getName();
  }

  /**
   * Add a new option to the "To" widget
   *
   * @param sting $sTitle
   * @param string $sValue (optional)
   */
  public function addToOption($sTitle, $sValue=NULL)
  {
    $this->oTo->addOption($sTitle, $sValue);
  }

  /**
   * Remove the specified option from the "To" widget
   *
   * @param string $sTitle
   */
  public function removeToOption($sTitle)
  {
    $this->oTo->removeOption($sTitle);
  }

  /**
   * Add a whole array of options to the "To" list, all at once
   *
   * @param array $hData
   * @param boolean $bHash (optional)
   */
  public function addToArray($hData, $bhash=TRUE)
  {
    $this->oTo->addArray($hData, $bhash);
  }

  /**
   * Add the specified handler to the specified event on the "To" widget
   *
   * @param string $sEvent
   * @param string $sHandler
   */
  public function addToEvent($sEvent, $sHandler)
  {
    $this->oTo->addEvent($sEvent, $sHandler);
  }

  /**
   * Return the name of the "From" widget
   *
   * @return string
   */
  public function getToName()
  {
    return $this->oTo->getName();
  }
}