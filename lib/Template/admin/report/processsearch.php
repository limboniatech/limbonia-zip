<?php
if (empty($reportList))
{
  echo "Sorry!  No reports hve been found at this time!<br>\n";
}
else
{
  $table->makeSortable();
  $table->startHeader();
  $module->processSearchGridHeader($table, 'Report');
  $table->endRow();

  foreach ($reportList as $driver => $name)
  {
    $table->startRow();
    $table->addCell("<a class=\"item\" href=\"" . $module->generateUri($driver, 'view') . "\">$name</a>\n");
    $table->endRow();
  }

  echo $table->toString();
}