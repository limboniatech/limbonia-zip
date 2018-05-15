<?php
if (empty($data))
{
  $sDifferent = $method != 'list' ? ' different' : '';
  echo "Sorry!  Your " . strtolower($module->getType()) . " " . strtolower($method) . " has no results at this time!<br>\n";
  echo "Try a$sDifferent <a class=\"item\" href=\"" . $module->generateUri('search') . "\">search</a>?\n";
}
else
{
  $table->makeSortable();
  $table->startHeader();
  $sDelete = $module->allow('delete') ? '<span class="LimboniaSortGridDelete" onClick="document.getElementById(\'Limbonia_SortGrid_Edit\').name=\'Delete\';document.getElementById(\'EditColumn\').submit();">Delete</span>' : '';
  $table->addCell($sDelete, false);

  foreach ($dataColumns as $column)
  {
    $module->processSearchGridHeader($table, $column);
  }

  $table->endRow();

  foreach ($data as $item)
  {
    $table->startRow();
    $table->addCell($module->processSearchGridRowControl($item->getIDColumn(), $item->id));

    foreach ($dataColumns as $column)
    {
      $table->addCell($module->getColumnValue($item, $column));
    }

    $table->endRow();
  }

  if ($module->allow('edit'))
  {
    echo "<form name=\"EditColumn\" id=\"EditColumn\" action=\"" . $module->generateUri('editcolumn') . "\" method=\"post\">\n";
    echo "<input type=\"hidden\" name=\"Column\" id=\"Limbonia_SortGrid_Edit\" value=\"\">\n";
  }

  echo $table->toString();

  if ($module->allow('edit'))
  {
    echo "</form>\n";
  }
}