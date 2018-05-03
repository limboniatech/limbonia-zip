<?php
if (isset($options['c']) || isset($options['display-config']))
{
  echo "Ticket Settings:\n";

  foreach ($module->getSetting() as $sName => $sValue)
  {
    echo "\t" . str_pad($sName, 8) . " => $sValue\n";
  }

  die("\n");
}

$bTestMode = false;

if (isset($options['t']) || isset($options['test']))
{
  $bTestMode = true;
  $cOutput = function($sData)
  {
    echo $sData . "\n";
    flush();
  };
}
else
{
  $rLog = fopen('ticket_processemail.log', 'a');
  $cOutput = function($sData) use ($rLog)
  {
    fwrite($rLog, $sData . "\n");
  };
}

$cOutput('Begin processing emails (' . \Limbonia\Controller::timeStamp() . ')');

try
{
  $cOutput('Connect to email server');
  $oImap = \Limbonia\Imap::connection($settings);
  $cOutput('Gather emails');
  $aEmail = $oImap->search('ALL');

  foreach ($aEmail as $sId)
  {
    $cOutput("Process message $sId");

    try
    {
      if ($bTestMode)
      {
        if ($hData = \Limbonia\Item\Ticket::generateTicketContentFromEmail($oImap->fetchEmail($sId), $controller, $cOutput))
        {
          if ($hData['ticketid'] == 0)
          {
            $cOutput("Create ticket for: {$hData['user']->name} <{$hData['from']}> - {$hData['subject']}");
          }
          else
          {
            $cOutput("Update ticket #{$hData['ticketid']} by: {$hData['user']->name} <{$hData['from']}> - {$hData['ticket']->subject}");
            unset($hData['ticket']);
          }
        }
      }
      else
      {
        if (\Limbonia\Item\Ticket::processEmail($oImap->fetchEmail($sId), $controller, $cOutput))
        {
          $cOutput("\tSuccess #$sId");
          //$oImap->delete($sId);
        }
      }
    }
    catch (\Exception $e)
    {
      $cOutput("Email message #$sId failed: " . $e->getMessage());
    }
  }

  $oImap->disconnect();
}
catch (\Exception $e)
{
  $cOutput('Failed to process emails: ' . $e->getMessage());
}

$cOutput('End processing emails (' . \Limbonia\Controller::timeStamp() . ')');