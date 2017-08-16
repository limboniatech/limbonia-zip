<?php
namespace Omniverse\Item;

use Omniverse\Controller;
use Omniverse\Database;
use Omniverse\Email;
use Omniverse\File;
use Omniverse\Item;

/**
 * Omniverse Ticket Item Class
 *
 * Item based wrapper around the Ticket table and adds all the extra
 * functionality needed for a full ticket system
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Ticket extends Item
{
  protected $aNoUpdate = ['CreateTime', 'CompletionTime', 'LastUpdate'];
  protected $aHistory = [];
  protected $aSoftwareColumn = ['SoftwareID', 'ElementID', 'ReleaseID', 'Severity', 'Projection', 'DevStatus', 'QualityStatus', 'Description', 'StepsToReproduce'];
  protected $hExtra = null;
  protected $hAutoExpand =
  [
    'Parent' => 'Ticket',
    'Owner' => 'User',
    'Creator' => 'User',
    'Category' => 'TicketCategory',
    'Element' => 'SoftwareElement',
    'Release' => 'SoftwareRelease'
  ];
  protected $hAutoGetter =
  [
    'all' => 'getAll',
    'columns' => 'getColumns',
    'columnlist' => 'getColumnNames',
    'idcolumn' => 'getIDColumn',
    'table' => 'getTable',
    'contentlist' => 'getContentList',
    'totaltime' => 'getTotalTime',
    'watcherlist' => 'getWatcherList',
    'attachmentlist' => 'getAttachmentList',
    'children' => 'getChildren'
  ];

  public function __set($sName, $xValue)
  {
    //this object is not allowed to change either of these after it's created...
    if (in_array($sName, $this->aNoUpdate) && $this->isCreated())
    {
      return;
    }

    if ($sName == 'ParentID')
    {
      return parent::__set($sName, $xValue);
    }

    if (strtolower($this->hData['Type']) == 'software' || !in_array($sName, $this->aSoftwareColumn))
    {
      $xPrevious = $this->__get($sName);
      parent::__set($sName, $xValue);
      $xCurrent = $this->__get($sName);

      if (!empty($xPrevious) && !empty($xCurrent) && $xCurrent != $xPrevious)
      {
        //if they are closing the ticket then set the completion time to now...
        if ($sName == 'Status')
        {
          $sClosedTime = $xCurrent == 'closed' ? 'now' : null;
          $this->hData['CompletionTime'] = $this->formatInput('CompletionTime', $sClosedTime);
        }

        if (preg_match("/(.*?)ID$/i", $sName, $aMatch))
        {
          $sType = $aMatch[1];
          $sType = isset($this->hAutoExpand[$sType]) ? $this->hAutoExpand[$sType] : $sType;
          $sLabel = ucfirst($sType);

          try
          {
            $oPrevious = parent::fromId($sType, $xPrevious, $this->getDB());

            if ($sName == 'ReleaseID')
            {
              $sPrevious = $oPrevious->Version;
            }
            else
            {
              $sPrevious = $oPrevious->Name;
            }
          }
          catch (\Exception $e)
          {
            $sPrevious = 'None';
          }

          try
          {
            $oCurrent = parent::fromId($sType, $xCurrent, $this->getDB());

            if ($sName == 'ReleaseID')
            {
              parent::__set('ParentID', $oCurrent->TicketID);
              $sCurrent = $oCurrent->Version;
            }
            else
            {
              $sCurrent = $oCurrent->Name;
            }
          }
          catch (\Exception $e)
          {
            parent::__set($sName, null);
            $sCurrent = 'None';

            if ($sName == 'ReleaseID')
            {
              parent::__set('ParentID', 0);
            }
          }
        }
        else
        {
          $sLabel = ucfirst($sName);
          $sPrevious = (string)$xPrevious;
          $sCurrent = (string)$xCurrent;
        }

        if ($sCurrent != $sPrevious)
        {
          $this->aHistory[] = array($sName . 'From' => $sPrevious, $sName . 'To' => $sCurrent, 'Note' => "$sLabel changed from <b>$sPrevious</b> to <b>$sCurrent</b>.");
        }
      }
    }
  }

  public function __get($sName)
  {
    if ($sName == 'Creator')
    {
      if (!isset($this->hItemObjects[$sName]))
      {
        try
        {
          $this->hItemObjects[$sName] = parent::fromId('User', parent::__get('CreatorID'), $this->getDB());
        }
        catch (\Exception $e)
        {
          try
          {
            $this->hItemObjects[$sName] = parent::factory('User', $this->getDB());
          }
          catch (\Exception $e)
          {
            $this->hItemObjects[$sName] = null;
          }
        }
      }

      return $this->hItemObjects[$sName];
    }

    if (strtolower($this->hData['Type']) == 'software' || !in_array($sName, $this->aSoftwareColumn))
    {
      return parent::__get($sName);
    }
  }

  public function getAll($bFormatted = false)
  {
    $hData = parent::getAll();

    if (strtolower($this->hData['Type']) != 'software')
    {
      foreach ($this->aSoftwareColumn as $sColumn)
      {
        unset($hData[$sColumn]);
      }
    }

    return $hData;
  }

  public function setAll(array $hItem = [])
  {
    $hExtra = parent::setAll($hItem);

    //grab what we need for the content object and leave the rest...
    foreach (['UserID', 'UpdateText', 'UpdateType', 'TimeWorked'] as $sContentColumn)
    {
      if (isset($hExtra[$sContentColumn]))
      {
        $this->hExtra[$sContentColumn] = $hExtra[$sContentColumn];
        unset($hExtra[$sContentColumn]);
      }
    }

    return $hExtra;
  }

  protected function create()
  {
    parent::__set('CreateTime', 'now');
    parent::__set('LastUpdate', 'now');
    return parent::create();
  }

  protected function generateOwner()
  {
    if (!empty($this->hData['OwnerID']))
    {
      try
      {
        //if the user isn't active then reassign this ticket.
        if (!parent::fromId('User', $this->hData['OwnerID'], $this->getDB())->Active)
        {
          $this->hData['OwnerID'] = 0;
        }
      }
      catch (\Exception $e)
      {
        //if the user can't be instantiated then reassign this ticket.
        $this->hData['OwnerID'] = 0;
      }

      if (!empty($this->hData['OwnerID']))
      {
        //this ownerid must be ok, leave it...
        return;
      }
    }

    try
    {
      $oTicketCategory = parent::fromId('TicketCategory', $this->hData['CategoryID'], $this->getDB());
    }
    catch (\Exception $e)
    {
      $oTicketCategory = parent::factory('TicketCategory', $this->getDB());
    }

    switch ($oTicketCategory->AssignmentMethod)
    {
      case 'unassigned':
        $this->hData['OwnerID'] = 0;
        break;

      case 'roundrobin':
      case 'leasttickets':
        $oResult = $this->getDB()->prepare("SELECT U.UserID FROM User U, User_Key K WHERE U.Active = 1 AND U.Type = 'internal' AND U.UserID = K.UserID AND K.Level >= ? AND K.KeyID = ?");
        $oResult->execute([$oTicketCategory->Level, $oTicketCategory->KeyID]);
        $aUserList = $oResult->getColumn();

        //if there is no one in this list then there is no one to assign the ticket to...
        if (empty($aUserList))
        {
          $this->hData['OwnerID'] = 0;
        }
        elseif ($oTicketCategory->AssignmentMethod == 'leasttickets')
        {
          $hUserWeights = array();

          foreach ($aUserList as $iUser)
          {
            $oResult = $this->getDB()->prepare("SELECT Priority, COUNT(1) FROM Ticket WHERE UserID = ? AND Status = 'open' GROUP BY Priority");
            $oResult->execute([$iUser]);
            $hPriority = $oResult->getAssoc();
            $hUserWeights[$iUser] = $hPriority['low'] + $hPriority['normal'] * 2 + $hPriority['high'] * 4 + $hPriority['critical'] * 8;
          }

          $aCandidateList = array_keys($hUserWeights, min($hUserWeights));
          $this->hData['OwnerID'] = (integer)$aCandidateList[array_rand($aCandidateList)];
        }
        elseif ($oTicketCategory->AssignmentMethod == 'roundrobin')
        {
          //get the id of the user that most recently got a ticket in the same category as this ticket
          $sUserList = implode(', ', $aUserList);
          $oResult = $this->getDB()->prepare("SELECT UserID FROM Ticket WHERE UserID IN ($sUserList) AND CategoryID = ? ORDER BY TicketID DESC LIMIT 1");
          $oResult->execute([$this->hData['CategoryID']]);
          $iMostRecentUser = $oResult->getOne();

          //find the position of the most recent user to have a ticket
          $iCurrentPosition = array_search($iMostRecentUser, $aUserList);

          //if the current position is false use 0 otherwise use the current position + 1
          $iNextPosition = $iCurrentPosition === false ? 0 : $iCurrentPosition + 1;

          //if the next position is in the list use it if it off the edge start at the top
          $this->hData['OwnerID'] = $iNextPosition < count($aUserList) ? $aUserList[$iNextPosition] : $aUserList[0];
        }
        else
        {
          $this->hData['OwnerID'] = 0;
        }
        break;

      case 'direct':
        $this->hData['OwnerID'] = $oTicketCategory->UserID;
        break;
    }
  }

  protected function update()
  {
    $hExtra = $this->hExtra;
    $this->hExtra = null;
    $this->generateOwner();
    $this->hData['LastUpdate'] = $this->formatInput('LastUpdate', 'now');

    $iTicket = parent::update();

    if (!$iTicket)
    {
      return false;
    }

    $iUser = isset($hExtra['UserID']) ? (integer)$hExtra['UserID'] : 0;

    $hContent =
    [
      'TicketID' => $this->id,
      'UserID' => $iUser,
      'UpdateTime' => 'now',
      'UpdateText' => empty($hExtra['UpdateText']) ? null : $hExtra['UpdateText'],
      'UpdateType' => isset($hExtra['UpdateType']) ? trim(strtolower($hExtra['UpdateType'])) : ($iUser == 0 ? 'system' : 'private'),
      'TimeWorked' => isset($hExtra['TimeWorked']) ? (integer)$hExtra['TimeWorked'] : 0,
    ];

    $oContent = parent::fromArray('TicketContent', $hContent, $this->getDB());
    $oContent->setHistory($this->aHistory);
    $oContent->save();

    $sOriginatorEmail = strtolower($oContent->user->email);
    $oEmail = new Email();

    //don't send an email to the owner if they are making the changes
    if (strtolower($this->owner->email) != $sOriginatorEmail)
    {
      $oEmail->addTo($this->owner->email);
    }

    foreach ($this->getWatcherList() as $oWatcher)
    {
      //don't send an email to the watcher if they are making the changes
      //also, don't send them 'private' updates unless the watcher is an 'internal' or 'system' type user
      if (strtolower($oWatcher->email) != $sOriginatorEmail && ($oContent->updateType != 'private' || in_array($oWatcher->type, array('internal', 'system'))))
      {
        $oEmail->addTo($oWatcher->email);
      }
    }

    $sDomain = Controller::getDefault()->getDomain()->name;
    $oEmail->setFrom("ticket_system@{$sDomain}");
    $oEmail->setSubject("$this->subject [{$sDomain} Ticket #{$this->id}: $this->status]");
    $sBody = "The ticket has the follwing updates: \n\n";
    $aHistory = $oContent->getHistory();

    foreach ($aHistory as $hHistory)
    {
      if (!empty($hHistory['Note']))
      {
        $sBody .= "* {$hHistory['Note']}\n";
      }
    }

    if ($oContent->updateText)
    {
      $sBody .= "\n{$oContent->updateText}\n\n";
    }

    $sBody .= "To see this ticket click <a href=\"http://{$sDomain}/?Admin=Process&Module=Ticket&Process=View&TicketID={$this->id}\">here</a>.";
    $oEmail->addBody($sBody);
    $oEmail->send();
    return $iTicket;
  }

  public function getContentList()
  {
    return parent::getList('TicketContent', "SELECT DISTINCT C.* FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = $this->id ORDER BY ContentID DESC", $this->getDB());
  }

  public function getTotalTime()
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT C.ContentID, C.TimeWorked FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = ?");
    $oResult->execute([$this->id]);
    $hTime = $oResult->fetchAssoc();
    return array_sum($hTime);
  }

  public function getWatcherList()
  {
    return parent::getList('User', "SELECT DISTINCT U.* FROM User U NATURAL JOIN Ticket_User TU WHERE TU.TicketID = $this->id", $this->getDB());
  }

  public function addWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->ID : (integer)$xUser;
    return $this->getDB()->exec("INSERT INTO Ticket_User (TicketID, UserID) VALUES ($this->id, $iUser)");
  }

  public function removeWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->ID : (integer)$xUser;
    return $this->getDB()->exec("DELETE FROM Ticket_User WHERE TicketID = $this->id AND UserID = $iUser");
  }

  protected function generateAttachmentDir($bWeb = false)
  {
    $sTicketNumber = $this->id < 10 ? '0' . (string)$this->id : (string)$this->id;
    $sDir = "/.ticket_attachments/{$sTicketNumber[0]}/{$sTicketNumber[1]}/{$this->id}";
    return $bWeb ? $sDir : Controller::getDefault()->getDomain()->path . $sDir;
  }

  public function getAttachmentList()
  {
    $sAttachmentDir = $this->generateAttachmentDir();
    $sBaseWebDir = $this->generateAttachmentDir(true);
    $aAttachments = [];

    if (!\is_readable($sAttachmentDir))
    {
      return [];
    }

    foreach (glob("$sAttachmentDir/*") as $sFile)
    {
      $sBaseName = \basename($sFile);
      $aAttachments[] =
      [
        'name' => $sBaseName,
        'path' => $sFile,
        'link' => "$sBaseWebDir/" . \rawurlencode($sBaseName),
        'time' => date("Y-j-n h:i:s p", \filemtime($sFile))
      ];
    }

    return $aAttachments;
  }

  public function addAttachment($sFile, $sName = null)
  {
    if ($this->id == 0)
    {
      throw new \Exception("This is not a valid ticket, so no attachments can be saved.");
    }

    if (!\is_file($sFile))
    {
      throw new \Exception("The specified file does not exist.");
    }

    $sAttachmentDir = $this->generateAttachmentDir();

    File::makeDir($sAttachmentDir);

    if (empty($sName))
    {
      $sName = \basename($sFile);
    }

    if (\is_uploaded_file($sFile))
    {
      ob_start();
      $bSuccess = \move_uploaded_file($sFile, "$sAttachmentDir/$sName");
      $sError = \ob_get_clean();
    }
    else
    {
      ob_start();
      $bSuccess = \rename($sFile, "$sAttachmentDir/$sName");
      $sError = \ob_get_clean();
    }

    if (!$bSuccess)
    {
      throw new \Exception($sError);
    }

    return true;
  }

  public function removeAttachment($sFileName)
  {
    $sFilePath = $this->generateAttachmentDir() . '/' . $sFileName;

    if (!\file_exists($sFilePath))
    {
      return true;
    }

    ob_start();
    $bSuccess = \unlink($sFilePath);
    $sError = \ob_get_clean();

    if (!$bSuccess)
    {
      throw new \Exception($sError);
    }

    return true;
  }

  public function getParent()
  {
    return $this->id > 0 ? parent::fromId('Ticket', $this->ParentID, $this->getDB()) : null;
  }

  public function getChildren()
  {
    return $this->id > 0 ? parent::search('Ticket', ['ParentID' => $this->id], ['LastUpdate'], $this->getDB()) : [];
  }

  public function addChild($xChild)
  {
    $oChild = $xChild instanceof Ticket ? $xChild : parent::fromId('Ticket', $xChild, $this->getDB());
    $oChild->ParentID = $this->id;
    return $oChild->save();
  }

  public function removeChild($xChild)
  {
    $oChild = $xChild instanceof Ticket ? $xChild : parent::fromId('Ticket', $xChild);

    if ($oChild->ParentID != $this->id)
    {
      return true;
    }

    $oChild->ParentID = 0;
    return $oChild->save();
  }
}