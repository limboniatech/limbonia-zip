<?php
namespace Omniverse\Item;

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
class Ticket extends \Omniverse\Item
{
  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['CreateTime', 'CompletionTime', 'LastUpdate'];

  /**
   * List of history hashes for this ticket
   *
   * @var array
   */
  protected $aHistory = [];

  /**
   * List of ticket columns that relate to software only
   *
   * @var aray
   */
  protected $aSoftwareColumn = ['SoftwareID', 'ElementID', 'ReleaseID', 'Severity', 'Projection', 'DevStatus', 'QualityStatus', 'Description', 'StepsToReproduce'];

  /**
   * List of extra data passed to this ticket, stored here so that it can be passed on to a content object
   *
   * @var array
   */
  protected $hContent = [];

  /**
   * List of names and their associated types, used by __get to generate item objects
   *
   * @var array
   */
  protected $hAutoExpand =
  [
    'parent' => 'Ticket',
    'owner' => 'User',
    'creator' => 'User',
    'category' => 'TicketCategory',
    'element' => 'SoftwareElement',
    'release' => 'SoftwareRelease'
  ];

    /**
   * List of names and their associated methods, used by __get to generate data
   *
   * @var array
   */
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

  /**
   * The list od columns in the TicketContent table
   *
   * @var array
   */
  protected static $aContentColumns = [];

  /**
   * The ticket constructor
   *
   * @param string $sType (optional)
   * @param \Omniverse\Database $oDatabase (optional)
   */
  public function __construct($sType = null, \Omniverse\Database $oDatabase = null)
  {
    parent::__construct($sType, $oDatabase);

    if (empty(self::$aContentColumns))
    {
      self::$aContentColumns = \array_keys(\array_change_key_case($this->getDB()->getColumns('TicketContent'), CASE_LOWER));
    }
  }

  /**
   * Sets the specified values if possible
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
    $sRealName = $this->hasColumn($sName);

    //this object is not allowed to change either of these after it's created...
    if (in_array($sRealName, $this->aNoUpdate) && $this->isCreated())
    {
      return;
    }

    if ($sRealName == 'ParentID')
    {
      return parent::__set($sName, $xValue);
    }

    if ($this->hData['Type'] == 'software' || !in_array($sRealName, $this->aSoftwareColumn))
    {
      if ($this->bSkipHistory)
      {
        parent::__set($sName, $xValue);
        return;
      }

      $xPrevious = $this->__get($sName);
      parent::__set($sName, $xValue);
      $xCurrent = $this->__get($sName);

      if (!empty($xPrevious) && !empty($xCurrent) && $xCurrent != $xPrevious)
      {
        //if they are closing the ticket then set the completion time to now...
        if ($sRealName == 'Status')
        {
          $sClosedTime = $xCurrent == 'closed' ? 'now' : null;
          $this->hData['CompletionTime'] = $this->formatInput('CompletionTime', $sClosedTime);
        }

        if (preg_match("/(.*?)ID$/i", $sRealName, $aMatch))
        {
          $sLowerMatch = strtolower($aMatch[1]);
          $sType = isset($this->hAutoExpand[$sLowerMatch]) ? $this->hAutoExpand[$sLowerMatch] : $aMatch[1];
          $sLabel = ucfirst($aMatch[1]);

          try
          {
            $oPrevious = parent::fromId($sType, $xPrevious, $this->getDB());

            if ($sRealName == 'ReleaseID')
            {
              $sPrevious = $oPrevious->version;
            }
            else
            {
              $sPrevious = $oPrevious->name;
            }
          }
          catch (\Exception $e)
          {
            $sPrevious = 'None';
          }

          try
          {
            $oCurrent = parent::fromId($sType, $xCurrent, $this->getDB());

            if ($sRealName == 'ReleaseID')
            {
              parent::__set('ParentID', $oCurrent->ticketId);
              $sCurrent = $oCurrent->version;
            }
            else
            {
              $sCurrent = $oCurrent->name;
            }
          }
          catch (\Exception $e)
          {
            parent::__set($sName, null);
            $sCurrent = 'None';

            if ($sRealName == 'ReleaseID')
            {
              parent::__set('ParentID', 0);
            }
          }
        }
        else
        {
          $sLabel = ucfirst($sRealName);
          $sPrevious = (string)$xPrevious;
          $sCurrent = (string)$xCurrent;
        }

        if ($sCurrent != $sPrevious)
        {
          $this->aHistory[] = [$sRealName . 'From' => $sPrevious, $sRealName . 'To' => $sCurrent, 'Note' => "$sLabel changed from <b>$sPrevious</b> to <b>$sCurrent</b>."];
        }
      }
    }
  }

  /**
   * Get the specified data
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    if ($this->hData['Type'] == 'software' || !in_array($this->hasColumn($sName), $this->aSoftwareColumn))
    {
      return parent::__get($sName);
    }
  }

  /**
   * Get a copy of all the data this object contains
   *
   * @param boolean $bFormatted Format the returned data?
   * @return array
   */
  public function getAll($bFormatted = false)
  {
    $hData = parent::getAll($bFormatted);

    if ($this->hData['Type'] != 'software')
    {
      foreach ($this->aSoftwareColumn as $sColumn)
      {
        unset($hData[$sColumn]);
      }
    }

    return $hData;
  }

  /**
   * Loop through the specified array looking for keys that match column names.  For each match
   * set that column to the value for that key in the array then unset that value in the array.
   * After each matching key has been used return the remainder of the array.
   *
   * @param array $hItem
   * @return array
   */
  public function setAll(array $hItem = [])
  {
    $hExtra = parent::setAll($hItem);
    $this->hContent = [];

    //grab what we need for the content object and leave the rest...
    foreach (self::$aContentColumns as $sContentColumn)
    {
      if (isset($hExtra[$sContentColumn]))
      {
        $this->hContent[$sContentColumn] = $hExtra[$sContentColumn];
        unset($hExtra[$sContentColumn]);
      }
    }

    return $hExtra;
  }

  /**
   * Created a row for this object's data in the database
   *
   * @return integer The ID of the row created on success or false on failure
   */
  protected function create()
  {
    parent::__set('CreateTime', 'now');
    parent::__set('LastUpdate', 'now');
    return parent::create();
  }

  /**
   * Set the data for this object to the row of data specified by the given item id.
   *
   * @param integer $iItemID
   * @throws Exception
   */
  public function load($iItemID)
  {
    parent::load($iItemID);
    $this->aHistory = [];
    $this->hContent = [];
  }

  /**
   * Generate and return the list of valid potential ticket owners, even if that list is empty
   *
   * @return array List of potential valid ticket owners on success or false on failure
   */
  protected function getPotentialOwnerList()
  {
    $oResult = $this->getDB()->prepare("SELECT U.UserID FROM User U, User_Key K WHERE U.Active = 1 AND U.Type = 'internal' AND U.UserID = K.UserID AND K.Level >= ? AND K.KeyID = ?");
    $bSuccess = $oResult->execute([$this->category->level, $this->category->keyId]);

    if (!$bSuccess)
    {
      return false;
    }

    return $oResult->fetchColumn();
  }

  /**
   * Set the owner of the ticket according to the rules set for it
   */
  protected function setOwner()
  {
    if (!empty($this->hData['OwnerID']))
    {
      try
      {
        //if the user isn't active then reassign this ticket.
        if (!$this->owner->active)
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

    switch ($this->category->assignmentMethod)
    {
      case 'direct':
        $this->hData['OwnerID'] = $this->category->userId;
        break;

      case 'unassigned':
        $this->hData['OwnerID'] = 0;
        break;

      case 'roundrobin':
        $aUserList = $this->getPotentialOwnerList();

        //if there is an error getting the list
        if ($aUserList === false)
        {
          //then don't change the owner, for now...
          break;
        }

        //if there is no one in this list
        if (empty($aUserList))
        {
          //then there is no one to assign the ticket to...
          $this->hData['OwnerID'] = 0;
          break;
        }

        //get the id of the user that most recently got a ticket in the same category as this ticket
        $sUserList = implode(', ', $aUserList);
        $oResult = $this->getDB()->prepare("SELECT UserID FROM Ticket WHERE UserID IN ($sUserList) AND CategoryID = ? ORDER BY TicketID DESC LIMIT 1");
        $oResult->execute([$this->hData['CategoryID']]);
        $hTicket = $oResult-fetchOne();

        //find the position of the most recent user to have a ticket
        $iCurrentPosition = array_search($hTicket['UserID'], $aUserList);

        //if the current position is false use 0 otherwise use the current position + 1
        $iNextPosition = $iCurrentPosition === false ? 0 : $iCurrentPosition + 1;

        //if the next position is in the list use it if it off the edge start at the top
        $this->hData['OwnerID'] = $iNextPosition < count($aUserList) ? $aUserList[$iNextPosition] : $aUserList[0];
        break;

      case 'leasttickets':
        $aUserList = $this->getPotentialOwnerList();

        //if there is an error getting the list
        if ($aUserList === false)
        {
          //then don't change the owner, for now...
          break;
        }

        //if there is no one in this list
        if (empty($aUserList))
        {
          //then there is no one to assign the ticket to...
          $this->hData['OwnerID'] = 0;
          break;
        }

        $hUserWeights = [];
        $oResult = $this->getDB()->prepare("SELECT Priority, COUNT(1) FROM Ticket WHERE UserID = ? AND Status = 'open' GROUP BY Priority");

        foreach ($aUserList as $iUser)
        {
          $oResult->execute([$iUser]);
          $hPriority = $oResult->getAssoc();
          $hUserWeights[$iUser] = $hPriority['low'] + $hPriority['normal'] * 2 + $hPriority['high'] * 4 + $hPriority['critical'] * 8;
        }

        $aCandidateList = array_keys($hUserWeights, min($hUserWeights));
        $this->hData['OwnerID'] = (integer)$aCandidateList[array_rand($aCandidateList)];
        break;
    }
  }

  /**
   * Update this object's data in the data base with current data
   *
   * @return integer The ID of this object on success or false on failure
   */
  protected function update()
  {
    $hContent = $this->hContent;
    $this->hContent = [];
    $this->setOwner();
    $this->hData['LastUpdate'] = $this->formatInput('LastUpdate', 'now');
    $iTicket = parent::update();

    if (empty($iTicket))
    {
      return false;
    }

    $hContent['ticketid'] = $this->id;
    $hContent['updatetime'] = 'now';

    if (!isset($hContent['userid']))
    {
      $hContent['userid'] = 0;
    }

    if (!isset($hContent['updatetype']))
    {
      $hContent['updatetype'] = empty($hContent['userid']) == 0 ? 'system' : 'private';
    }

    $oContent = parent::fromArray('TicketContent', $hContent, $this->getDB());
    $oContent->setHistory($this->aHistory);
    $oContent->save();
    $aHistory = $oContent->getHistory();

    //if there is no history
    if (empty($aHistory))
    {
      //then skip the email and return the ticket id directly
      return $iTicket;
    }

    $sOriginatorEmail = \strtolower($oContent->user->email);
    $oEmail = new \Omniverse\Email();

    //don't send an email to the owner if they are making the changes
    if (\strtolower($this->owner->email) != $sOriginatorEmail)
    {
      $oEmail->addTo($this->owner->email);
    }

    foreach ($this->getWatcherList() as $oWatcher)
    {
      //don't send an email to the watcher if they are making the changes
      //also, don't send them 'private' updates unless the watcher is an 'internal' or 'system' type user
      if (\strtolower($oWatcher->email) != $sOriginatorEmail && ($oContent->updateType != 'private' || in_array($oWatcher->type, array('internal', 'system'))))
      {
        $oEmail->addTo($oWatcher->email);
      }
    }

    //If there is no one to send the email
    if (empty($oEmail->getTo()))
    {
      //then skip the email and return the ticket id directly
      return $iTicket;
    }

    $sDomain = \Omniverse\Controller::getDefault()->getDomain()->name;
    $oEmail->setFrom("ticket_system@{$sDomain}");
    $oEmail->setSubject("$this->subject [{$sDomain} Ticket #{$this->id}: $this->status]");
    $sBody = "The ticket has the follwing updates: \n\n";

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

  /**
   * Return the list of content for this ticket
   *
   * @return \Omniverse\ItemList
   */
  public function getContentList()
  {
    return parent::getList('TicketContent', "SELECT DISTINCT C.* FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = $this->id ORDER BY ContentID DESC", $this->getDB());
  }

  /**
   * Return the total number of minutes this ticket has been worked on
   *
   * @return integer
   */
  public function getTotalTime()
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT C.ContentID, C.TimeWorked FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = ?");
    $oResult->execute([$this->id]);
    $hTime = $oResult->fetchAssoc();
    return array_sum($hTime);
  }

  /**
   * Return a list of watchers of this ticket
   *
   * @return \Omniverse\ItemList
   */
  public function getWatcherList()
  {
    return parent::getList('User', "SELECT DISTINCT U.* FROM User U NATURAL JOIN Ticket_User TU WHERE TU.TicketID = $this->id", $this->getDB());
  }

  /**
   * Add the specified watcher to this ticet
   *
   * @param integer|\Omniverse\Item\User $xUser Either the userID or a User object
   * @return boolean
   */
  public function addWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->id : (integer)$xUser;
    $bSuccess = $this->getDB()->exec("INSERT INTO Ticket_User (TicketID, UserID) VALUES ($this->id, $iUser)");
    return $bSuccess !== false;
  }

  /**
   * Remove the specified watcher from this ticet
   *
   * @param integer|\Omniverse\Item\User $xUser Either the userID or a User object
   * @return boolean
   */
  public function removeWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->id : (integer)$xUser;
    $bSuccess = $this->getDB()->exec("DELETE FROM Ticket_User WHERE TicketID = $this->id AND UserID = $iUser");
    return $bSuccess !== false;
  }

  /**
   * Generate and return the attachment directory
   *
   * @param boolean $bWeb (optional) Return the web directory?
   * @return string
   */
  protected function generateAttachmentDir($bWeb = false)
  {
    $sTicketNumber = $this->id < 10 ? '0' . (string)$this->id : (string)$this->id;
    $sDir = "/.ticket_attachments/{$sTicketNumber[0]}/{$sTicketNumber[1]}/{$this->id}";
    return $bWeb ? $sDir : \Omniverse\Controller::getDefault()->getDomain()->path . $sDir;
  }

  /**
   * Return the list of attachments that this ticket has
   *
   * @return array
   */
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

  /**
   * Add the specified file as an attachment to this ticket
   *
   * @param string $sFile
   * @param string $sName (optional) Override the existing file name with this
   * @throws \Exception
   */
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

    \Omniverse\File::makeDir($sAttachmentDir);

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
  }

  /**
   * Remove the specified file attachment from the this ticket
   *
   * @param string $sFileName
   * @return boolean
   * @throws \Exception
   */
  public function removeAttachment($sFileName)
  {
    $sFilePath = $this->generateAttachmentDir() . '/' . $sFileName;

    if (\file_exists($sFilePath))
    {
      ob_start();
      $bSuccess = \unlink($sFilePath);
      $sError = \ob_get_clean();

      if (!$bSuccess)
      {
        throw new \Exception($sError);
      }
    }
  }

  /**
   * Return a list of the child tickets
   *
   * @return \Omniverse\ItemList
   */
  public function getChildren()
  {
    return parent::search('Ticket', ['ParentID' => $this->id], ['LastUpdate'], $this->getDB());
  }

  /**
   * Add the specified ticket as a child of this ticket
   *
   * @param integer|\Omniverse\Item\Ticket $xChild Either the ID of a ticket or a ticket object
   * @return boolean
   */
  public function addChild($xChild)
  {
    $oChild = $xChild instanceof \Omniverse\Item\Ticket ? $xChild : parent::fromId('Ticket', $xChild, $this->getDB());
    $oChild->parentId = $this->id;
    return (boolean)$oChild->save();
  }

  /**
   * Remove the specified ticket as a child of this ticket
   *
   * @param integer|\Omniverse\Item\Ticket $xChild Either the ID of a ticket or a ticket object
   * @return boolean
   */
  public function removeChild($xChild)
  {
    $oChild = $xChild instanceof \Omniverse\Item\Ticket ? $xChild : parent::fromId('Ticket', $xChild, $this->getDB());

    if ($oChild->parentId != $this->id)
    {
      return true;
    }

    $oChild->parentId = 0;
    return (boolean)$oChild->save();
  }
}