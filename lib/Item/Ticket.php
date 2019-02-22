<?php
namespace Limbonia\Item;

/**
 * Limbonia Ticket Item Class
 *
 * Item based wrapper around the Ticket table and adds all the extra
 * functionality needed for a full ticket system
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Ticket extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`TicketID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`OwnerID` int(10) unsigned NOT NULL,
`CategoryID` int(10) unsigned NOT NULL,
`ParentID` int(10) unsigned NOT NULL DEFAULT '0',
`Type` enum('internal','contact','system','software') NOT NULL DEFAULT 'internal',
`Subject` varchar(255) NOT NULL,
`CreateTime` timestamp NULL DEFAULT NULL,
`CreatorID` int(10) unsigned NOT NULL,
`StartDate` date DEFAULT NULL,
`LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`DueDate` date DEFAULT NULL,
`CompletionTime` timestamp NULL DEFAULT NULL,
`Status` enum('new','active','pending','closed') NOT NULL DEFAULT 'new',
`ProjectID` int(10) unsigned NOT NULL DEFAULT '0',
`Priority` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
`ReleaseID` int(10) unsigned NOT NULL,
`Severity` enum('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
`Projection` enum('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
`DevStatus` enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','complete') NOT NULL DEFAULT 'review',
`QualityStatus` enum('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
`Description` text,
`StepsToReproduce` text,
PRIMARY KEY (`TicketID`),
FULLTEXT KEY `Fulltext_Ticket_Description` (`Description`),
FULLTEXT KEY `Fulltext_Ticket_StepsToReproduce` (`StepsToReproduce`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'TicketID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'OwnerID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'CategoryID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ParentID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Type' =>
    [
      'Type' => "enum('internal','contact','system','software')",
      'Default' => 'internal'
    ],
    'Subject' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'CreateTime' =>
    [
      'Type' => 'timestamp',
      'Default' => ''
    ],
    'CreatorID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'StartDate' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'LastUpdate' =>
    [
      'Type' => 'timestamp',
      'Default' => 'CURRENT_TIMESTAMP',
      'Extra' => 'on update CURRENT_TIMESTAMP'
    ],
    'DueDate' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'CompletionTime' =>
    [
      'Type' => 'timestamp',
      'Default' => ''
    ],
    'Status' =>
    [
      'Type' => "enum('new','active','pending','closed')",
      'Default' => 'new'
    ],
    'ProjectID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Priority' =>
    [
      'Type' => "enum('low','normal','high','critical')",
      'Default' => 'normal'
    ],
    'ReleaseID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Severity' =>
    [
      'Type' => "enum('wish list','feature','change','performance','minor bug','major bug','crash')",
      'Default' => 'feature'
    ],
    'Projection' =>
    [
      'Type' => "enum('unknown','very minor','minor','average','major','very major','redesign')",
      'Default' => 'unknown'
    ],
    'DevStatus' =>
    [
      'Type' => "enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','complete')",
      'Default' => 'review'
    ],
    'QualityStatus' =>
    [
      'Type' => "enum('failed','passed','untested','retest','in progress','pending developer response')",
      'Default' => 'untested'
    ],
    'Description' =>
    [
      'Type' => 'text',
      'Key' => 'Multi',
      'Default' => ''
    ],
    'StepsToReproduce' =>
    [
      'Type' => 'text',
      'Key' => 'Multi',
      'Default' => ''
    ]
  ];

  /**
   * The aliases for this item's columns
   *
   * @var array
   */
  protected static $hColumnAlias =
  [
    'ticketid' => 'TicketID',
    'id' => 'TicketID',
    'ownerid' => 'OwnerID',
    'categoryid' => 'CategoryID',
    'parentid' => 'ParentID',
    'type' => 'Type',
    'subject' => 'Subject',
    'createtime' => 'CreateTime',
    'creatorid' => 'CreatorID',
    'startdate' => 'StartDate',
    'lastupdate' => 'LastUpdate',
    'duedate' => 'DueDate',
    'completiontime' => 'CompletionTime',
    'status' => 'Status',
    'projectid' => 'ProjectID',
    'priority' => 'Priority',
    'releaseid' => 'ReleaseID',
    'severity' => 'Severity',
    'projection' => 'Projection',
    'devstatus' => 'DevStatus',
    'qualitystatus' => 'QualityStatus',
    'description' => 'Description',
    'stepstoreproduce' => 'StepsToReproduce'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'TicketID' => 0,
    'OwnerID' => 0,
    'CategoryID' => 0,
    'ParentID' => 0,
    'Type' => 'internal',
    'Subject' => '',
    'CreateTime' => '',
    'CreatorID' => 0,
    'StartDate' => '',
    'LastUpdate' => 'CURRENT_TIMESTAMP',
    'DueDate' => '',
    'CompletionTime' => '',
    'Status' => 'new',
    'ProjectID' => 0,
    'Priority' => 'normal',
    'ReleaseID' => 0,
    'Severity' => 'feature',
    'Projection' => 'unknown',
    'DevStatus' => 'review',
    'QualityStatus' => 'untested',
    'Description' => '',
    'StepsToReproduce' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'TicketID' => 0,
    'OwnerID' => 0,
    'CategoryID' => 0,
    'ParentID' => 0,
    'Type' => 'internal',
    'Subject' => '',
    'CreateTime' => '',
    'CreatorID' => 0,
    'StartDate' => '',
    'LastUpdate' => 'CURRENT_TIMESTAMP',
    'DueDate' => '',
    'CompletionTime' => '',
    'Status' => 'new',
    'ProjectID' => 0,
    'Priority' => 'normal',
    'ReleaseID' => 0,
    'Severity' => 'feature',
    'Projection' => 'unknown',
    'DevStatus' => 'review',
    'QualityStatus' => 'untested',
    'Description' => '',
    'StepsToReproduce' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['TicketID', 'CreateTime', 'CompletionTime', 'LastUpdate'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'Ticket';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'TicketID';

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
  protected $aSoftwareColumn = ['Severity', 'Projection', 'DevStatus', 'QualityStatus', 'StepsToReproduce'];

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
    'release' => 'ProjectRelease'
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
   * Process a raw email into ticket data based on the email content
   *
   * @param string $sEmail - The raw email to process
   * @param \Limbonia\Controller $oController (optional) - the controller running this process
   * @param callable $cOutput (optional) - a callback used to output any data generated by this function
   * @return array - hash of ticket data that can be used to manipulate tickets
   * @throws \Exception
   */
  public static function generateTicketContentFromEmail($sEmail, \Limbonia\Controller $oController = null, callable $cOutput = null)
  {
    if (!($oController instanceof \Limbonia\Controller))
    {
      $oController = \Limbonia\Controller::getDefault();
    }

    if (!is_callable($cOutput))
    {
      $cOutput = function($sData)
      {
        //throw any data away...
      };
    }

    $hEmail = \Limbonia\Email::processMessage($sEmail);
    $sDomain = $oController->getDomain()->name;
    $hData = [];

    if (!isset($hEmail['headers']['subject']))
    {
      throw new \Exception('Subject not found');
    }

    if (!isset($hEmail['headers']['from']))
    {
      throw new \Exception('Email address not found');
    }

    $hData['from'] = preg_match("/<(.+?)>/i", $hEmail['headers']['from'], $aMatch) ? trim($aMatch[1]) : trim($hEmail['headers']['from']);
    $sText = isset($hEmail['text']) ? trim($hEmail['text']) : trim(strip_tags($hEmail['html']));

    //Remove email signitures
    $sText = trim(preg_replace("/-- \n.*/s", '', $sText));

    //remove "On" line
    $sText = trim(preg_replace("/On .*? wrote:\n/", '', $sText));

    //remove reply lines
    $sText = trim(preg_replace("/> ?.*?(\n|$)/", '', $sText));

    if (empty($sText))
    {
      throw new \Exception("Valid ticket text not found in an email from {$hData['from']}.");
    }

    $hData['user'] = $oController->userByEmail($hData['from']);

    if (preg_match("/\[$sDomain Ticket #(\d+)/i", $hEmail['headers']['subject'], $aMatch))
    {
      $hData['ticket'] = $oController->itemFromId('ticket', $aMatch[1]);

      if (!$hData['user']->canAccessTicket($hData['ticket']->id))
      {
        throw new \Exception("Contact '{$hData['from']}' does not have access to the ticket");
      }

      $hData['ticketid'] = $hData['ticket']->id;
      $hData['userid'] = $hData['user']->id;
      $hData['updatetype'] = 'public';
      $hData['updatetext'] = $sText;
    }
    else
    {
      $hData['ticketid'] = 0;
      $hData['subject'] = $hEmail['headers']['subject'];
      $hData['ownerid'] = $hData['user']->id;
      $hData['type'] = $hData['user']->type;
      $hData['description'] = $sText;
      //parse the subject to get the CategoryID
      //parse the first line for hashtag controls
    }

    return $hData;
  }

  /**
   * Process a raw email and attempt to either generate a new ticket or update an existing one based on the email content
   *
   * @param string $sEmail - The raw email to process
   * @param \Limbonia\Controller $oController (optional) - the controller running this process
   * @param callable $cOutput (optional) - a callback used to output any data generated by this function
   * @return integer - the ID of the affected ticket
   */
  public static function processEmail($sEmail, \Limbonia\Controller $oController = null, callable $cOutput = null)
  {
    if (!($oController instanceof \Limbonia\Controller))
    {
      $oController = \Limbonia\Controller::getDefault();
    }

    if (!is_callable($cOutput))
    {
      $cOutput = function($sData)
      {
        //throw any data away...
      };
    }

    $hData = self::generateTicketContentFromEmail($sEmail, $oController, $cOutput);

    $oUser = $hData['user'];
    unset($hData['user']);
    unset($hData['from']);

    if (isset($hData['ticket']))
    {
      $oTicket = $hData['ticket'];
      unset($hData['ticket']);

      //if a contact respinds to a pending ticket
      if ($oUser->isContact() && $oTicket->status == 'pending')
      {
        //assume it should be re-opened...
        $hData['status'] = 'open';
      }

      $oTicket->setAll($hData);
    }
    else
    {
      $oTicket = $oController->itemFromArray('ticket', $hData);
    }

    return $oTicket->save();
  }

  /**
   * Does the specified column exist in TicketContent?
   *
   * @param string $sColumn
   * @return mixed Returns the real column name if it exists or false if it doesn't
   */
  public function contentColumn($sColumn)
  {
    return $this->getDatabase()->hasColumn('TicketContent', $sColumn);
  }

  /**
   * Sets the specified values if possible
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
    $sContentName = $this->contentColumn($sName);

    if (!empty($sContentName) && $sContentName !== 'TicketID')
    {
      $this->hContent[$sContentName] = $xValue;
      return;
    }

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

    //if the ticket is *not* software but the column *is* a software column
    if ($this->hData['Type'] != 'software' && in_array($sRealName, $this->aSoftwareColumn))
    {
      //then skip it...
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
          $oPrevious = parent::fromId($sType, $xPrevious, $this->getDatabase());

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
          $oCurrent = parent::fromId($sType, $xCurrent, $this->getDatabase());

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

    if (!empty($this->hContent))
    {
      $hData['content'] = $this->hContent;
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
    foreach (array_keys($hExtra) as $sColumn)
    {
      if ($sContentColumn = $this->contentColumn($sColumn))
      {
        $this->hContent[$sContentColumn] = $hExtra[$sColumn];
        unset($hExtra[$sColumn]);
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
    parent::__set('CreatorID', $this->oDatabase->getController()->user()->id);
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
   * Generate and return the list of valid potential ticket owners by resource, even if that list is empty
   *
   * @return array List of potential valid ticket owners on success or false on failure
   */
  protected function getPotentialOwnerListByResource()
  {
    $oResult = $this->getDatabase()->prepare("SELECT U.UserID FROM User U, User_Key K WHERE U.Active = 1 AND U.Type = 'internal' AND U.UserID = K.UserID AND K.Level >= ? AND K.KeyID = ?");
    $bSuccess = $oResult->execute([$this->category->level, $this->category->keyId]);

    if (!$bSuccess)
    {
      return false;
    }

    return $oResult->fetchColumn();
  }

  /**
   * Generate and return the list of valid potential ticket owners by resource, even if that list is empty
   *
   * @return array List of potential valid ticket owners on success or false on failure
   */
  protected function getPotentialOwnerListByRole()
  {
    $oResult = $this->getDatabase()->prepare("SELECT u.UserID FROM User u, User_Role u_r WHERE u.Active = 1 AND u.Type = 'internal' AND u.UserID = u_r.UserID AND u_r.RoleID = ?");
    $bSuccess = $oResult->execute([$this->category->roleid]);

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

      case 'round robin by resource':
      case 'round robin by role':
        $aUserList = $this->category->assignmentMethod == 'round robin by role' ? $this->getPotentialOwnerListByRole() : $this->getPotentialOwnerListByResource();

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
        $oResult = $this->getDatabase()->prepare("SELECT UserID FROM Ticket WHERE UserID IN ($sUserList) AND CategoryID = ? ORDER BY TicketID DESC LIMIT 1");
        $oResult->execute([$this->hData['CategoryID']]);
        $hTicket = $oResult->fetchColumn();

        //find the position of the most recent user to have a ticket
        $iCurrentPosition = array_search($hTicket['UserID'], $aUserList);

        //if the current position is false use 0 otherwise use the current position + 1
        $iNextPosition = $iCurrentPosition === false ? 0 : $iCurrentPosition + 1;

        //if the next position is in the list use it if it off the edge start at the top
        $this->hData['OwnerID'] = $iNextPosition < count($aUserList) ? $aUserList[$iNextPosition] : $aUserList[0];
        break;

      case 'least tickets by resource':
      case 'least tickets by role':
        $aUserList = $this->category->assignmentMethod == 'least tickets by role' ? $this->getPotentialOwnerListByRole() : $this->getPotentialOwnerListByResource();

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
        $oResult = $this->getDatabase()->prepare("SELECT Priority, COUNT(1) FROM Ticket WHERE UserID = ? AND Status = 'open' GROUP BY Priority");

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

    $hContent['TicketID'] = $this->id;
    $hContent['UpdateTime'] = 'now';

    if (empty($hContent['UserID']))
    {
      $hContent['UserID'] = 0;
    }

    if (!isset($hContent['UpdateType']))
    {
      $hContent['UpdateType'] = $hContent['UserID'] === 0 ? 'system' : 'private';
    }

    $oContent = $this->oController->itemFromArray('TicketContent', $hContent);
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
    $oEmail = new \Limbonia\Email();

    //don't send an email to the owner if they are making the changes
    if (\strtolower($this->owner->email) != $sOriginatorEmail)
    {
      $oEmail->addTo($this->owner->email);
    }

    foreach ($this->getWatcherList() as $oWatcher)
    {
      //don't send an email to the watcher if they are making the changes
      //also, don't send them 'private' updates unless the watcher is an 'internal' or 'system' type user
      if (\strtolower($oWatcher->email) != $sOriginatorEmail && ($oContent->updateType != 'private' || in_array($oWatcher->type, ['internal', 'system'])))
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

    $oDomain = $this->getController()->getDomain();
    $hTicketSettings = $this->getController()->getSettings('ticket');
    $sEmail = isset($hTicketSettings['user']) ? $hTicketSettings['user'] : "ticket_system@{$oDomain->name}";
    $oEmail->setFrom($sEmail);
    $oEmail->setSubject("$this->subject [{$oDomain->name} Ticket #{$this->id}: $this->status]");
    $oEmail->isText();
    $sBody = "The ticket has the following updates: \n\n";

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

    $sBody .= "To see this ticket click <a href=\"{$oDomain->url}" . $this->getController()->generateUri('ticket', $this->id) . "\">here</a><br>\n";
    $oEmail->addBody($sBody);
    $oEmail->send();
    return $iTicket;
  }

  /**
   * Return the list of content for this ticket
   *
   * @return \Limbonia\ItemList
   */
  public function getContentList()
  {
    return parent::getList('TicketContent', "SELECT DISTINCT C.* FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = $this->id ORDER BY ContentID DESC", $this->getDatabase());
  }

  /**
   * Return the total number of minutes this ticket has been worked on
   *
   * @return integer
   */
  public function getTotalTime()
  {
    $oResult = $this->getDatabase()->prepare("SELECT DISTINCT C.ContentID, C.TimeWorked FROM TicketContent C LEFT JOIN TicketHistory H ON C.ContentID = H.ContentID WHERE (C.UpdateText > '' OR H.Note > '') AND TicketID = ?");
    $oResult->execute([$this->id]);
    $hTime = $oResult->fetchAssoc();
    return array_sum($hTime);
  }

  /**
   * Return a list of watchers of this ticket
   *
   * @return \Limbonia\ItemList
   */
  public function getWatcherList()
  {
    return parent::getList('User', "SELECT DISTINCT U.* FROM User U NATURAL JOIN Ticket_User TU WHERE TU.TicketID = $this->id", $this->getDatabase());
  }

  /**
   * Add the specified watcher to this ticket
   *
   * @param integer|\Limbonia\Item\User $xUser Either the userID or a User object
   * @return boolean
   */
  public function addWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->id : (integer)$xUser;
    $bSuccess = $this->getDatabase()->exec("INSERT INTO Ticket_User (TicketID, UserID) VALUES ($this->id, $iUser)");
    return $bSuccess !== false;
  }

  /**
   * Remove the specified watcher from this ticket
   *
   * @param integer|\Limbonia\Item\User $xUser Either the userID or a User object
   * @return boolean
   */
  public function removeWatcher($xUser)
  {
    $iUser = $xUser instanceof User ? $xUser->id : (integer)$xUser;
    $bSuccess = $this->getDatabase()->exec("DELETE FROM Ticket_User WHERE TicketID = $this->id AND UserID = $iUser");
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
    return $bWeb ? $this->getDatabase()->getController()->domain->url . $sDir : $this->getDatabase()->getController()->domain->path . $sDir;
  }

  /**
   * Return the list of attachments that this ticket has , sorted from oldest to newest
   *
   * @return array
   */
  public function getAttachmentList()
  {
    if ($this->id == 0)
    {
      throw new \Exception("This is not a valid ticket, so attachments are not valid");
    }

    $sAttachmentDir = $this->generateAttachmentDir();
    $sBaseWebDir = $this->generateAttachmentDir(true);
    $aAttachments = [];

    if (!is_readable($sAttachmentDir))
    {
      return [];
    }

    $aFiles = glob("$sAttachmentDir/*");
    array_multisort
    (
      array_map('filemtime', $aFiles ),
      SORT_NUMERIC,
      SORT_ASC,
      $aFiles
    );

    foreach ($aFiles as $iKey => $sFile)
    {
      $iId = $iKey + 1;
      $sBaseName = basename($sFile);
      $aAttachments[$iId] =
      [
        'id' => $iId,
        'name' => $sBaseName,
        'path' => $sFile,
        'link' => "$sBaseWebDir/" . rawurlencode($sBaseName),
        'time' => date("Y-j-n h:i:s p", filemtime($sFile))
      ];
    }

    return $aAttachments;
  }

  /**
   * Return the data for the specified attachment, if it exists
   *
   * @param int $iId
   * @return array
   * @throws \Exception
   */
  public function getAttachmentById(int $iId): array
  {
    $aList = $this->getAttachmentList();

    if (!isset($aList[$iId]))
    {
      throw new \Exception("The id ($iId) was not found");
    }

    return $aList[$iId];
  }

  /**
   * Delete the specified attachment, if it exists
   *
   * @param int $iId
   * @throws \Exception
   */
  public function removeAttachmentById(int $iId)
  {
    $hAttachment = $this->getAttachmentById($iId);
    $this->removeAttachment($hAttachment['name']);
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
      throw new \Exception("This is not a valid ticket, so attachments are not valid");
    }

    if (!\is_file($sFile))
    {
      throw new \Exception("The specified file does not exist.");
    }

    $sAttachmentDir = $this->generateAttachmentDir();

    \Limbonia\File::makeDir($sAttachmentDir);

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
   * @throws \Exception
   */
  public function removeAttachment($sFileName)
  {
    $sFilePath = $this->generateAttachmentDir() . '/' . $sFileName;

    if (!file_exists($sFilePath))
    {
      throw new \Exception("File not found: $sFilePath");
    }

    ob_start();
    $bSuccess = unlink($sFilePath);
    $sError = ob_get_clean();

    if (!$bSuccess)
    {
      throw new \Exception($sError);
    }
  }

  /**
   * Return a list of the child tickets
   *
   * @return \Limbonia\ItemList
   */
  public function getChildren()
  {
    return parent::search('Ticket', ['ParentID' => $this->id], ['LastUpdate'], $this->getDatabase());
  }

  /**
   * Add the specified ticket as a child of this ticket
   *
   * @param integer|\Limbonia\Item\Ticket $xChild Either the ID of a ticket or a ticket object
   * @return boolean
   */
  public function addChild($xChild)
  {
    $oChild = $xChild instanceof \Limbonia\Item\Ticket ? $xChild : parent::fromId('Ticket', $xChild, $this->getDatabase());
    $oChild->parentId = $this->id;
    return (boolean)$oChild->save();
  }

  /**
   * Remove the specified ticket as a child of this ticket
   *
   * @param integer|\Limbonia\Item\Ticket $xChild Either the ID of a ticket or a ticket object
   * @return boolean
   */
  public function removeChild($xChild)
  {
    $oChild = $xChild instanceof \Limbonia\Item\Ticket ? $xChild : parent::fromId('Ticket', $xChild, $this->getDatabase());

    if ($oChild->parentId != $this->id)
    {
      return true;
    }

    $oChild->parentId = 0;
    return (boolean)$oChild->save();
  }
}