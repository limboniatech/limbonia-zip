<?php
$unversionedList = $currentItem->getUnversionedTikets();
echo "  <div class=\"release\">
    <div class=\"title\" id=\"progress\">Unversioned Tickets (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=software&SoftwareID=$currentItem->id\">New Ticket</a>) (<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, 'releases') . "\">New Version</a>)</div>\n";

if (count($unversionedList) > 0)
{
  echo "<ul>\n";

  foreach ($unversionedList as $unversionedBug)
  {
    echo "    <li>[" . ucwords($unversionedBug->qualityStatus) . "] " . ucwords($unversionedBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $unversionedBug->id) . "\" title=\"[$unversionedBug->devStatus] " . htmlentities($unversionedBug->subject) . "\">#$unversionedBug->id</a>: <span class=\"incomplete\">{$unversionedBug->element->name} - " . htmlentities($unversionedBug->subject) . "</span> ({$unversionedBug->owner->name}) - $unversionedBug->devStatus</li>\n";
  }

  echo "</ul>\n";
}

echo "  </div>\n";

foreach ($currentItem->getReleaseList('roadmap') as $release)
{
  $completedList = $release->getTicketList('complete');
  $incompleteList = $release->getTicketList('incomplete');
  $incompleteCount = 0;

  foreach ($incompleteList as $ticketList)
  {
    $incompleteCount += count($ticketList);
  }

  $completedCount = count($completedList);
  $bugCount = $completedCount + $incompleteCount;

  if ($bugCount > 0)
  {
    $sDueDate = empty($release->ticket->dueDate) ? 'No Due Date' : $release->ticket->dueDate;
    $finishedPercent = number_format(100 * $completedCount / $bugCount);
    echo "  <div class=\"release\">
    <div class=\"title\" id=\"$release->version\"><a class=\"item\" href=\"" . $controller->generateUri('ticket', $release->ticketID) . "\">$currentItem->name - $release->version</a> ($sDueDate) (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=software&SoftwareID=$currentItem->id&ReleaseID=$release->id\">New Ticket</a>)</div>
    <div class=\"note\">$release->note</div>
    <div class=\"progressWrapper\"><div class=\"progress\"><span class=\"bar\" style=\"width: {$finishedPercent}%;\">{$finishedPercent}%</span></div></div>\n";

    foreach ($incompleteList as $priority => $ticketList)
    {
      if (count($ticketList) > 0)
      {
        echo "    <div class=\"priority\">" . ucwords($priority) . "</div>
      <ul>\n";

        foreach ($ticketList as $incompleteBug)
        {
          echo "    <li class=\"ticket\">[" . ucwords($incompleteBug->qualityStatus) . "] " . ucwords($incompleteBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $incompleteBug->id) . "\" title=\"[$incompleteBug->devStatus] " . htmlentities($incompleteBug->subject) . "\">#$incompleteBug->id</a>: <span class=\"incomplete\">{$incompleteBug->element->name} - " . htmlentities($incompleteBug->subject) . "</span> ({$incompleteBug->owner->name}) - $incompleteBug->devStatus</div>\n";
        }

        echo "</ul>\n";
      }
    }

    if (count($completedList) > 0)
    {
      echo "    <div class=\"priority\">Complete</div>
      <ul>\n";

      foreach ($completedList as $completedBug)
      {
        echo "    <li class=\"ticket\">[" . ucwords($completedBug->qualityStatus) . "] " . ucwords($completedBug->sevesity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $completedBug->id) . "\" title=\"[$completedBug->devStatus] " . htmlentities($completedBug->subject) . "\">#$completedBug->id</a>: <span class=\"completed\">{$completedBug->element->name} - " . htmlentities($completedBug->subject). "</span> ({$completedBug->owner->name})</li>\n";
      }

      echo "</ul>\n";
    }

    echo "<div class=\"count\">$completedCount of $bugCount issue(s) resolved. Progress ($finishedPercent%).</div>
  </div>\n";
  }
  else
  {
    $sDueDate = empty($release->ticket->dueDate) ? 'No Due Date' : $release->ticket->dueDate;
    echo "  <div class=\"release\">
    <div class=\"title\" id=\"$release->version\"><a class=\"item\" href=\"" . $controller->generateUri('ticket', $release->ticketID) . "\">$currentItem->name - $release->version</a> ($sDueDate) (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=software&SoftwareID=$currentItem->id&ReleaseID=$release->id\">New Ticket</a>)</div>
    <div class=\"note\">$release->note</div>
  </div>\n";
  }
}