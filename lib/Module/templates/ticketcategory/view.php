<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

echo $controller->dataField('Name', $module->getColumnValue($currentItem, 'Name'));
echo $controller->dataField('AssignmentMethod', $module->getColumnValue($currentItem, 'AssignmentMethod'));

if ($currentItem->assignmentMethod == 'leasttickets' || $currentItem->assignmentMethod == 'roundrobin')
{
  echo $controller->dataField('Key', $module->getColumnValue($currentItem, 'KeyID'));
  echo $controller->dataField('Level', $module->getColumnValue($currentItem, 'Level'));
}
elseif ($currentItem->assignmentMethod == 'direct')
{
  echo $controller->dataField('User', $module->getColumnValue($currentItem, 'UserID'));
}