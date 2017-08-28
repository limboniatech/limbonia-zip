<?php
namespace Omniverse\Widget;

/**
 * Omniverse Software Widget
 *
 * The methods needed to load software names, releases and elements
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Software extends \Omniverse\Widget\Select
{
  /**
   * Constructor
   *
   * It increments the widget counter and generates a unique (but human readable) name.
   *
   * @param string $sName (optional)
   * @param \Omniverse\Controller $oController (optional)
   * @throws Omniverse\Exception\Object
   */
  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sType = 'select';
    $this->addOption('Select a Software Project', '0');
    $aSoftware = \Omniverse\Item\Software::getSoftwareList();

    foreach ($aSoftware as $oSoftware)
    {
      $this->addOption($oSoftware->name, $oSoftware->id);
    }
  }

  /**
   * Generate and return the release list associated with the specified software ID
   *
   * @param integer $iSoftware
   * @param string $sWidgetId
   * @param integer $iSelectedRelease
   * @return string
   */
  public function ajax_getReleasesBySoftware($iSoftware, $sWidgetId, $iSelectedRelease='')
  {
    $sVersions = '';
    $sVersions .= "var c=document.getElementById('$sWidgetId');";
    $sVersions .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}";

    if ($iSoftware != '0' && !empty($iSoftware))
    {
      $oSoftware = \Omniverse\Item::fromId('software', $iSoftware);
      $oReleaseList = $oSoftware->getReleaseList();

      foreach ($oReleaseList as $iKey => $oRelease)
      {
        $iScriptCount = $iKey + 1;
        $sVersions .= "c.options[$iScriptCount] = new Option('" . $oRelease->version . "', '" . $oRelease->id . "');";

        if ($iSelectedRelease == $oRelease->id)
        {
          $sVersions .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sVersions;
  }

  /**
   * Generate and return the element list associated with the specified software ID
   *
   * @param integer $iSoftware
   * @param string $sWidget
   * @param integer $iSelectedElement
   * @return string
   */
  public function ajax_getElementsBySoftware($iSoftware, $sWidget, $iSelectedElement='')
  {
    $sElements = '';
    $sElements .= "var c=document.getElementById('$sWidget');";
    $sElements .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}";

    if ($iSoftware != '0' && !empty($iSoftware))
    {
      $oSoftware = \Omniverse\Item::fromId('software', $iSoftware);
      $oElementList = $oSoftware->getElementList();

      foreach ($oElementList as $iKey => $oElement)
      {
        $iScriptCount = $iKey + 1;
        $sElements .= "c.options[$iScriptCount] = new Option('" . $oElement->name . "', '" . $oElement->id . "');";

        if ($iSelectedElement == $oElement->id)
        {
          $sElements .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sElements;
  }
}