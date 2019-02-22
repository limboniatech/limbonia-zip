<?php
if (isset($error))
{
  foreach ($error as $i => $sError)
  {
    \Limbonia\Widget::warningText($sError);
  }
}

$hAvailableModuleList = $controller->availableModules();
$hActiveModules = $controller->activeModules();

$table = $controller->widgetFactory('table');
$table->makeSortable();
$table->startHeader();
$table->addCell('Active');
$table->addCell('Name');
$table->endRow();

foreach ($hAvailableModuleList as $sAvailableDriver => $sAvailableName)
{
  $sDriver = \Limbonia\Module::driver($sAvailableName);
  $sTypeClass = '\\Limbonia\\Module\\' . $sDriver;
  $sChecked = isset($hActiveModules[$sAvailableDriver]) ? ' checked' : '';
  $table->startRow();
  $table->addCell("<input type=\"checkbox\" class=\"LimboniaSortGridCellCheckbox\" name=\"ActiveModule[$sAvailableDriver]\" id=\"ActiveModule[$sAvailableDriver]\" value=\"1\"$sChecked>");
  $table->addCell($sTypeClass::getGroup() . ' :: ' . $sAvailableName);
  $table->endRow();
}

echo "<form name=\"ManageModules\" id=\"ManageModules\" action=\"" . $module->generateUri('managemodules') . "\" method=\"post\">\n";
echo "<input type=\"hidden\" name=\"Column\" id=\"Limbonia_SortGrid_Edit\" value=\"\">\n";
echo $table->toString();
echo "<button type=\"submit\">Update Active Modules</button>\n";
echo "</form>";