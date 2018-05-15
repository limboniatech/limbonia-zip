<?php
foreach ($currentItem->getReleaseList('changelog') as $oRelease)
{
  $sTime = $oRelease->ticket->status == 'closed' ? $oRelease->ticket->completionTime : 'None';
  echo "    <div class=\"release\">\n";
  echo "      <div class=\"title\"><a class=\"item\" href=\"" . $controller->generateUri('ticket', $oRelease->ticketID) . "\">$currentItem->name - $oRelease->version</a> ($sTime)</div>\n";
  echo "      <div class=\"note\">$oRelease->note</div>\n";
  $oBugList = $oRelease->getTicketList('complete');

  if (count($oBugList) > 0)
  {
    echo "<ul>\n";

    foreach ($oBugList as $oBug)
    {
        echo "      <li class=\"ticket\">" . ucwords($oBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $oBug->id) . "\" title=\"[$oBug->devStatus] " . htmlentities($oBug->subject) . "\">#$oBug->id</a>: {$oBug->category->name} - " . htmlentities($oBug->subject) . " ({$oBug->owner->name})</li>\n";
    }

    echo "</ul>\n";
  }
  else
  {
    echo "      <div class=\"title\">Sorry no project tickets are assigned to this release.</div>\n";
  }

  echo "    </div>\n";
}