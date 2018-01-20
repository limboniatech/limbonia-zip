<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

foreach ($module->getColumns('view') as $sColumnName)
{
  echo $controller->dataField($module->getColumnTitle($sColumnName), $module->getColumnValue($currentItem, $sColumnName));
}
