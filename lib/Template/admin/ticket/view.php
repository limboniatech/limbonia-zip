<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

if ($currentItem->creatorID > 0)
{
  echo $controller->dataField('Created By', $module->getColumnValue($currentItem, 'CreatorID') . ' on ' . $currentItem->createTime);
}

echo $controller->dataField('Status', $module->getColumnValue($currentItem, 'Status'));
echo $controller->dataField('Priority', $module->getColumnValue($currentItem, 'Priority'));
echo $controller->dataField('Owner', $module->getColumnValue($currentItem, 'OwnerID'));
echo $module->getFormField('Watchers', '', [], false);
echo $controller->dataField('Subject', $module->getColumnValue($currentItem, 'Subject'));
echo $controller->dataField('Category', $module->getColumnValue($currentItem, 'CategoryID'));
echo $controller->dataField('Type', $module->getColumnValue($currentItem, 'Type'));

if ($currentItem->timeSpent > 0)
{
  echo $controller->dataField('Time Spent', $module->getColumnValue($currentItem, 'TimeSpent'));
}

echo $controller->dataField('Last Update', $module->getColumnValue($currentItem, 'LastUpdate'));

if ($currentItem->dueDate)
{
  echo $controller->dataField('Due Date', $module->getColumnValue($currentItem, 'DueDate'));
}

if ($currentItem->completionTime)
{
  echo $controller->dataField('Completion Time', $module->getColumnValue($currentItem, 'CompletionTime'));
}

if (!empty($currentItem->totalTime))
{
  echo $controller->dataField('Total Time Worked', $currentItem->totalTime . ' minutes');
}

if ($currentItem->type == 'software')
{
  echo $controller->dataField('Software', $module->getColumnValue($currentItem, 'SoftwareID'));
  echo $controller->dataField('Software Element', $module->getColumnValue($currentItem, 'ElementID'));
  echo $controller->dataField('Software Version', $module->getColumnValue($currentItem, 'ReleaseID'));
  echo $controller->dataField('Severity', $module->getColumnValue($currentItem, 'Severity'));
  echo $controller->dataField('Projection', $module->getColumnValue($currentItem, 'Projection'));
  echo $controller->dataField('Devopment Status', $module->getColumnValue($currentItem, 'DevStatus'));
  echo $controller->dataField('Quality Status', $module->getColumnValue($currentItem, 'QualityStatus'));
  echo $controller->dataField('Description', $module->getColumnValue($currentItem, 'Description'));
  echo $controller->dataField('Steps To Reproduce', $module->getColumnValue($currentItem, 'StepsToReproduce'));
}

foreach ($currentItem->contentList as $content)
{
  echo "<div class=\"field ticketContent\">\n";
  echo "  <span class=\"label\">\n";

  if ($content->userID > 0)
  {
    echo "<a class=\"item\" href=\"" . $controller->generateUri('user', $content->userID) . "\">{$content->user->name}</a>\n";
  }
  else
  {
    echo "Auto Created";
  }
  echo ' [' . ucwords($content->updateType) . ']';
  echo "  <br>\n";
  echo $content->updateTime;

  if (!empty($content->timeWorked))
  {
    echo "<br>$content->timeWorked minutes";
  }
  echo "</span>\n";
  echo "<span class=\"$content->updateType data\">\n";
  echo $content->updateText . "\n";
  $historyList = $content->getHistory();

  if (count($historyList) > 0)
  {
    echo "<div class=\"history\">\n";

    foreach ($historyList as $history)
    {
      if (!empty($history->note))
      {
        echo "<div class=\"note\">$history->note</div>\n";
      }
    }

    echo "</div>\n";
  }

  echo "  </span>\n";
  echo "</div>\n";
}