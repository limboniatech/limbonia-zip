<?php
foreach ($currentItem->getReleaseList('changelog') as $oRelease)
{
  $sTime = $oRelease->ticket->status == 'closed' ? $oRelease->ticket->completionTime : 'None';
  echo "    <div>\n";
  echo "      <h5><a class=\"item\" href=\"" . $controller->generateUri('ticket', $oRelease->ticketID) . "\">$currentItem->name - $oRelease->version</a> ($sTime)</h5>\n";
  echo "      <hr>\n";
  $oBugList = $oRelease->getTicketList('complete');

  if (count($oBugList) > 0)
  {
    foreach ($oBugList as $oBug)
    {
        echo "      <div>" . ucwords($oBug->severity) . " <a class=\"item\" href=\"" . $controller->generateUri('ticket', $oBug->id) . "\" title=\"[$oBug->devStatus] " . htmlentities($oBug->subject) . "\">#$oBug->id</a>: {$oBug->element->name} - " . htmlentities($oBug->subject) . " ({$oBug->owner->name})</div>\n";
    }
  }
  else
  {
    echo "      <h5>Sorry no software tickets are assigned to this release.</h5>\n";
  }

  echo "    </div>\n";
}