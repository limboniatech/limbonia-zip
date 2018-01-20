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
  $sDelete = $module->allow('Delete') ? '<span class="OmnisysSortGridDelete" onClick="document.getElementById(\'Omnisys_SortGrid_Edit\').name=\'Delete\';document.getElementById(\'EditColumn\').submit();">Delete</span>' : '';
  $table->addCell($sDelete, false);

  $module->processSearchGridHeader($table, 'Name');
  $module->processSearchGridHeader($table, 'Assignment Method');
  $table->endRow();

  foreach ($data as $item)
  {
    $table->startRow();
    $table->addCell($module->processSearchGridRowControl($idColumn, $item->id));
    $table->addCell($item->name);

    if ($item->assignmentMethod == 'direct')
    {
      $table->addCell('Direct to ' . $item->user->name);
    }
    elseif ($item->assignmentMethod == 'unassigned')
    {
      $table->addCell('Leave Unassigned');
    }
    elseif ($item->assignmentMethod == 'roundrobin')
    {
      if ($item->keyID == 0)
      {
        $table->addCell('Round Robin between all internal users');
      }
      else
      {
        $resourceKey = $controller->itemFromId('ResourceKey', $item->keyID);
        $table->addCell('Round Robin between internal users with ' . $resourceKey->name . ' access ' . ($item->level > 0 ? ' at level ' . $item->level . ' or above' : ''));
      }
    }
    elseif ($item->assignmentMethod == 'leasttickets')
    {
      if ($item->keyID == 0)
      {
        $table->addCell('Least Tickets between all internal users');
      }
      else
      {
        $resourceKey = $controller->itemFromId('ResourceKey', $item->keyID);
        $table->addCell('Least Tickets between internal users with ' . $resourceKey->name . ' access ' . ($item->level > 0 ? ' at level ' . $item->level . ' or above' : ''));
      }
    }

    $table->endRow();
  }

  if ($module->allow('edit'))
  {
    echo "<form name=\"Edit\" id=\"Edit\" action=\"" . $module->generateUri('editcolumn') . "\" method=\"post\">\n";
    echo "<input type=\"hidden\" name=\"Column\" id=\"Omnisys_SortGrid_Edit\" value=\"\">\n";
  }

  echo $table->toString();

  if ($module->allow('edit'))
  {
    echo "</form>\n";
  }
}