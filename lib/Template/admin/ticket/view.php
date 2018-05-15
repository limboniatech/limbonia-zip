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
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'CreatorID') . ' on ' . $currentItem->createTime, 'Created By');
}

echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Status'), 'Status');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Priority'), 'Priority');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'OwnerID'), 'Owner');
echo $module->getFormField('Watchers');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Subject'), 'Subject');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'CategoryID'), 'Category');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'ProjectID'), 'Project');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'ReleaseID'), 'Version');
echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Type'), 'Type');

if ($currentItem->timeSpent > 0)
{
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'TimeSpent'), 'Time Spent');
}

echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'LastUpdate'), 'Last Update');

if ($currentItem->dueDate)
{
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'DueDate'), 'Due Date');
}

if ($currentItem->completionTime)
{
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'CompletionTime'), 'Completion Time');
}

if (!empty($currentItem->totalTime))
{
  echo \Limbonia\Module::field(\Limbonia\Item::outputTimeInterval($currentItem->totalTime), 'Total Time Worked');
}

if ($currentItem->type == 'software')
{
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Severity'), 'Severity');
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Projection'), 'Projection');
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'DevStatus'), 'Devopment Status');
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'QualityStatus'), 'Quality Status');
  echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'StepsToReproduce'), 'Steps To Reproduce');
}

echo \Limbonia\Module::field($module->getColumnValue($currentItem, 'Description'), 'Description');

foreach ($currentItem->contentList as $content)
{
  echo "<div class=\"field ticketContent\">\n";
  echo "  <span class=\"label\">\n";

  if ($content->user->id > 0)
  {
    echo "<a class=\"item\" href=\"" . $controller->generateUri('user', $content->user->id) . "\">{$content->user->name}</a>\n";
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
    echo '<br>' . \Limbonia\Item::outputTimeInterval($content->timeWorked);
  }

  echo "</span>\n";
  echo "<span class=\"$content->updateType data\">\n";
  echo preg_replace("/\n/", "<br>\n", $content->updateText) . "\n";
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