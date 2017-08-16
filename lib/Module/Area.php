<?php
namespace Omniverse\Module;

/**
 * Omniverse Area Module class
 *
 * Admin module for handling areas
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Area extends \Omniverse\Module
{
  protected static $count = 0;

  protected $aMenuItems = array('List', 'Create', 'QuickSearch_Name', 'QuickSearch_Zip');
  protected $aSubMenuItems = array('View', 'Edit',  'EditZip');
  protected $aAllowedMethods = array('List', 'Create', 'EditDialog', 'Edit', 'EditZip', 'View');

  public function prepareTemplate()
  {
    if ($this->sCurrentMethod == 'EditZip')
    {
      $oZipList = $this->oItem->getZipList();
      $aAreaZip = [];

      if (count($oZipList) > 0)
      {
        foreach ($oZipList as $oZip)
        {
          $aAreaZip[] = $oZip->Zip;
        }
      }
    }

    if ($this->sCurrentAction == 'Display' && $this->sCurrentMethod == 'EditZip')
    {
      $oStates = $this->getController()->widgetFactory('States', "$this->sModuleName[State]");
      $sStatesID = $oStates->getID();

      $oCities = $this->getController()->widgetFactory('Select', "$this->sModuleName[City]");
      $sCitiesID = $oCities->getID();

      $oTransferSelect = $this->getController()->widgetFactory('TransferSelect', 'Zip');
      $sZipID = 'From_' . $oTransferSelect->getID();

      $sGetCities = $oStates->addAjaxFunction('getCitiesByState', TRUE);
      $sGetZips = $oStates->addAjaxFunction('getZipsByCity', TRUE);

      $sStateScript = "var stateSelect = document.getElementById('$sStatesID');\n";
      $sStateScript .= "var stateName = '';\n";
      $sStateScript .= "var cityName = '';\n";
      $sStateScript .= "function setState(state)\n";
      $sStateScript .= "{\n";
      $sStateScript .= "  stateName = state;\n";
      $sStateScript .= "  stateSelect.value = state;\n";
      $sStateScript .= '  ' . $sGetCities . "(state, '$sCitiesID', cityName);\n";
      $sStateScript .= "}\n";

      $oStates->writeJavascript($sStateScript);

      $sCityScript = "var citySelect = document.getElementById('$sCitiesID');\n";
      $sCityScript .= "var zipNum = '';\n";
      $sCityScript .= "function setCity(city)\n";
      $sCityScript .= "{\n";
      $sCityScript .= "  cityName = city;\n";
      $sCityScript .= "  if (citySelect.options.length > 1)\n";
      $sCityScript .= "  {\n";
      $sCityScript .= "    for (i = 0; i < citySelect.options.length; i++)\n";
      $sCityScript .= "    {\n";
      $sCityScript .= "      if (citySelect.options[i].value == city)\n";
      $sCityScript .= "      {\n";
      $sCityScript .= "        citySelect.options[i].selected = true;\n";
      $sCityScript .= "        break;\n";
      $sCityScript .= "      }\n";
      $sCityScript .= "    }\n";
      $sCityScript .= "  }\n";
      $sCityScript .= "  else\n";
      $sCityScript .= "  {\n";
      $sCityScript .= '    ' . $sGetCities . "(stateName, '$sCitiesID', city);\n";
      $sCityScript .= "  }\n";
      $sCityScript .= '  ' . $sGetZips . "(cityName, stateName, '$sZipID', zipNum);\n";
      $sCityScript .= "}\n";

      $oCities->writeJavascript($sCityScript);

      $sZipScript = "var zipSelect = document.getElementById('$sZipID');\n";
      $sZipScript .= "function setZip(zip)\n";
      $sZipScript .= "{\n";
      $sZipScript .= "  zipNum = zip;\n";
      $sZipScript .= "  if (zipSelect.options.length > 1)\n";
      $sZipScript .= "  {\n";
      $sZipScript .= "    for (i = 0; i < zipSelect.options.length; i++)\n";
      $sZipScript .= "    {\n";
      $sZipScript .= "      if (zipSelect.options[i].value == city)\n";
      $sZipScript .= "      {\n";
      $sZipScript .= "        zipSelect.options[i].selected = true;\n";
      $sZipScript .= "        break;\n";
      $sZipScript .= "      }\n";
      $sZipScript .= "    }\n";
      $sZipScript .= "  }\n";
      $sZipScript .= "  else\n";
      $sZipScript .= "  {\n";
      $sZipScript .= '    ' . $sGetZips . "(cityName, stateName, '$sZipID', zipNum);\n";
      $sZipScript .= "  }\n";
      $sZipScript .= "}\n";

      $oTransferSelect->writeJavascript($sZipScript);

      $oCities->addOption('Select a city', '0');
      $oStates->addEvent('change', $sGetCities . "(this.options[this.selectedIndex].value, '$sCitiesID', cityName)");
      $oCities->addEvent('change', $sGetZips . "(this.options[this.selectedIndex].value, stateSelect.options[stateSelect.selectedIndex].value, '$sZipID', zipNum)");

      $oTransferSelect->addToArray($aAreaZip, FALSE);

      $this->getController()->templateData('aAreaZip', $aAreaZip);
      $this->getController()->templateData('oStates', $oStates);
      $this->getController()->templateData('sGetCities', $sGetCities);
      $this->getController()->templateData('oCities', $oCities);
      $this->getController()->templateData('sGetZips', $sGetZips);
      $this->getController()->templateData('oTransferSelect', $oTransferSelect);
    }

    if ($this->sCurrentAction == 'Process' && $this->sCurrentMethod == 'EditZip')
    {
      if (!isset($_POST['Zip']))
      {
        $_POST['Zip'] = array();
      }

      try
      {
        $aRemove = array_diff($aAreaZip, $_POST['Zip']);
        if (count($aRemove) > 0)
        {
          $this->oItem->removeZips($aRemove);
        }

        $aAdd = array_diff($_POST['Zip'], $aAreaZip);
        if (count($aAdd) > 0)
        {
          $this->oItem->addZips($aAdd);
        }

        $this->getController()->templateData('success', "This area's zipcode update has been successful.");
      }
      catch (\Exception $e)
      {
        $this->getController()->templateData('failure', "This area's zipcode update has failed.");
      }

      $this->sCurrentAction = 'Display';
      $this->sCurrentMethod = 'View';
    }

    return parent::prepareTemplate();
  }
}