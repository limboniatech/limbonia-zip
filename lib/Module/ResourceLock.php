<?php
namespace Omniverse\Module;

/**
 * Omniverse Resource Lock Module class
 *
 * Admin module for handling site resource locks
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class ResourceLock extends \Omniverse\Module
{
  protected $aStaticColumn = array();

  public function getFormField($sName, $sValue=NULL, $hData=array(), $bInTable=false)
  {
    if ($sName == 'Resource')
    {
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[Resource]");
      $oSelect->addOption('Select a resource', '');

      foreach ($_SESSION['ResourceList'] as $sResource => $hComponent)
      {
        if ($sValue == $sResource)
        {
          $oSelect->setSelected($sResource);
        }

        $oSelect->addOption($sResource);
      }

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Resource:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }
      else
      {
        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Resource:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
      }
    }

    if ($sName == 'Component')
    {
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[Component]");
      $oSelect->addOption('Select a component', '');

      //since I'm setting the name for the Resource and Component objects above, I can depend on their ids below
      $sScript = "var resource = document.getElementById('{$this->sModuleName}Resource');\n";
      $sScript .= "var component = document.getElementById('{$this->sModuleName}Component');\n";
      $sScript .= "\nfunction updateComponent()\n";
      $sScript .= "{\n";
      $sScript .= "  currentResource = resource.options[resource.selectedIndex].value\n";
      $sScript .= "\n";
      $sScript .= "  with (component)\n";
      $sScript .= "  {\n";
      $sScript .= "    for (i = options.length - 1; i > 0; i--) { options[i] = null; }\n";
      $sScript .= "    switch (currentResource)\n";
      $sScript .= "    {\n";

      foreach ($_SESSION['ResourceList'] as $sResource => $hComponent)
      {
        $sScript .= "      case '".str_replace("'", "\'", $sResource)."':\n";
        $i = 1;

        foreach ($hComponent as $sName => $sDescription)
        {
          $sScript .= "        options[$i] = new Option('" . str_replace("'", "\'", $sName) . ":  " . str_replace("'", "\'", $sDescription) . "', '" . str_replace("'", "\'", $sName) . "');\n";
          $i++;
        }

        $sScript .= "        break;\n";
      }

      $sScript .= "    }\n";
      $sScript .= "  }\n";
      $sScript .= "}\n";
      $sScript .= "\n";
      $sScript .= "resource.onchange = updateComponent\n";
      $sScript .= "updateComponent();\n";
      $sScript .= "\n";
      $sScript .= "for (i = component.options.length - 1; i > 0; i--)\n";
      $sScript .= "{\n";
      $sScript .= "  if (component.options[i].value == '" . str_replace("'", "\'", $sValue) . "')\n";
      $sScript .= "  {\n";
      $sScript .= "    component.selectedIndex = i;\n";
      $sScript .= "  }\n";
      $sScript .= "}\n";

      $oSelect->writeJavascript($sScript);

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Component:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }
      else
      {
        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Component:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
      }
    }

    return parent::getFormField($sName, $sValue, $hData, $bInTable);
  }

  public function processSearch_GridHeader(\Omniverse\Widget\Table $oSortGrid, $sColumn)
  {
    return parent::processSearch_GridHeader($oSortGrid, ($sColumn == 'KeyID' ? 'Name' : $sColumn));
  }

  public function getColumnValue(\Omniverse\Item $oItem, $sColumn)
  {
    return $sColumn == 'KeyID' ? $this->getController()->itemFromId('ResourceKey', $oItem->KeyID)->Name : parent::getColumnValue($oItem, $sColumn);
  }
}