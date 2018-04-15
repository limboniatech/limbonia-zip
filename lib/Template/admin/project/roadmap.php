<?php
$unversionedList = $currentItem->getUnversionedTikets();
echo "  <h5 id=\"progress\">Unversioned Tickets (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=project&ProjectID=$currentItem->id\">New Ticket</a>) (<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, 'releases') . "\">New Version</a>)</h5>\n";

if (count($unversionedList) > 0)
{
  foreach ($unversionedList as $unversionedBug)
  {
    echo "  <div class=\"incomplete\">- [" . ucwords($unversionedBug->qualityStatus) . "] " . ucwords($unversionedBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $unversionedBug->id) . "\" title=\"[$unversionedBug->devStatus] " . htmlentities($unversionedBug->subject) . "\">#$unversionedBug->id</a>: {$unversionedBug->element->name} - " . htmlentities($unversionedBug->subject) . " ({$unversionedBug->owner->name}) - $unversionedBug->devStatus</div>\n";
  }
}
echo "  <hr>\n";

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
    echo "  <h5 id=\"$release->version\"><a class=\"item\" href=\"" . $controller->generateUri('ticket', $release->ticketID) . "\">$currentItem->name - $release->version</a> ($sDueDate) (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=project&ProjectID=$currentItem->id&ReleaseID=$release->id\">New Ticket</a>)</h5>
    <div class=\"progress\"><span class=\"bar\" style=\"width: {$finishedPercent}%;\">{$finishedPercent}%</span></div>
      <hr>\n";

    foreach ($incompleteList as $priority => $ticketList)
    {
      if (count($ticketList) > 0)
      {
        echo "<h3>" . ucwords($priority) . "</h3>\n";

        foreach ($ticketList as $incompleteBug)
        {
          echo "<div class=\"incomplete\">- [" . ucwords($incompleteBug->qualityStatus) . "] " . ucwords($incompleteBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $incompleteBug->id) . "\" title=\"[$incompleteBug->devStatus] " . htmlentities($incompleteBug->subject) . "\">#$incompleteBug->id</a>: {$incompleteBug->element->name} - " . htmlentities($incompleteBug->subject) . " ({$incompleteBug->owner->name}) - $incompleteBug->devStatus</div>\n";
        }
      }
    }

    if (count($completedList) > 0)
    {
      echo "<h3>Complete</h3>\n";

      foreach ($completedList as $completedBug)
      {
        echo "<div class=\"completed\">- [" . ucwords($completedBug->qualityStatus) . "] " . ucwords($completedBug->sevesity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $completedBug->id) . "\" title=\"[$completedBug->devStatus] " . htmlentities($completedBug->subject) . "\">#$completedBug->id</a>: {$completedBug->element->name} - " . htmlentities($completedBug->subject). " ({$completedBug->owner->name})</div>\n";
      }
    }

    echo "<br>
  $completedCount of $bugCount issue(s) resolved. Progress ($finishedPercent%).
      <br />\n";
  }
  else
  {
    $sDueDate = empty($release->ticket->dueDate) ? 'No Due Date' : $release->ticket->dueDate;
    echo "  <h5 id=\"$release->version\"><a class=\"item\" href=\"" . $controller->generateUri('ticket', $release->ticketID) . "\">$currentItem->name - $release->version</a> ($sDueDate) (<a class=\"item\" href=\"" . $controller->generateUri('ticket', 'create') . "?Type=project&ProjectID=$currentItem->id&ReleaseID=$release->id\">New Ticket</a>)</h5>
      <hr />\n";
  }
}