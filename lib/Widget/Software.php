<?php
namespace Omniverse\Widget;

class Software extends \Omniverse\Widget\Select
{
  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sType = 'select';
    $this->addOption('Select a Software Project', '0');
    $aSoftware = \Omniverse\Item\Software::getSoftwareList();

    foreach ($aSoftware as $oSoftware)
    {
      $this->addOption($oSoftware->Name, $oSoftware->ID);
    }
  }

  public function ajax_getReleasesBySoftware($iSoftware, $sWidget, $iSelectedRelease='')
  {
    $sVersions = '';
    $sVersions .= "var c=document.getElementById('$sWidget');";
    $sVersions .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}";

    if ($iSoftware != '0' && !empty($iSoftware))
    {
      $oSoftware = \Omniverse\Item::fromId('software', $iSoftware);
      $oReleaseList = $oSoftware->getReleaseList();

      foreach ($oReleaseList as $iKey => $oRelease)
      {
        $iScriptCount = $iKey + 1;
        $sVersions .= "c.options[$iScriptCount] = new Option('" . $oRelease->Version . "', '" . $oRelease->ID . "');";

        if ($iSelectedRelease == $oRelease->ID)
        {
          $sVersions .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sVersions;
  }

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
        $sElements .= "c.options[$iScriptCount] = new Option('" . $oElement->Name . "', '" . $oElement->ID . "');";

        if ($iSelectedElement == $oElement->ID)
        {
          $sElements .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sElements;
  }
}