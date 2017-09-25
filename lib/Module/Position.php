<?php
namespace Omniverse\Module;

/**
 * Omniverse Position Module class
 *
 * Admin module for handling page positions
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Position extends \Omniverse\Module
{
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'Site';

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @param boolean $bInTable - Is the returned HTML part a table?
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [])
  {
    if ($sName !== 'Module')
    {
      return parent::getFormField($sName, $sValue, $hData, $bInTable);
    }

    $sLabel = preg_replace("/([A-Z])/", " $1", $sName);
    $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
    $oSelect->addOption("Select $sLabel", '');
    $aModule = $_SESSION['ModuleList'];

    $iAdminKey = array_search('Admin', $aModule);

    if ($iAdminKey)
    {
      unset($aModule[$iAdminKey]);
    }

    $iPositionKey = array_search('Position', $aModule);

    if ($iPositionKey)
    {
      unset($aModule[$iPositionKey]);
    }

    $oSelect->addArray($aModule, FALSE);

    if (!empty($sValue))
    {
      $oSelect->setSelected($sValue);
    }

    return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\">" . $oSelect . "</span></div>";
  }
}