<?php
namespace Limbonia\Item;

/**
 * Limbonia Ticket Category Item Class
 *
 * Item based wrapper around the TicketCategory table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TicketCategory extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`CategoryID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`ParentID` int(10) unsigned NOT NULL DEFAULT '0',
`ProjectID` int(10) unsigned NOT NULL DEFAULT '0',
`Name` varchar(255) NOT NULL DEFAULT '',
`UserID` int(10) unsigned NOT NULL DEFAULT '0',
`RoleID` int(10) unsigned NOT NULL DEFAULT '0',
`KeyID` int(10) unsigned NOT NULL DEFAULT '0',
`Level` int(10) unsigned NOT NULL DEFAULT '0',
`AssignmentMethod` enum('unassigned','direct','least tickets by role','round robin by role','least tickets by resource','round robin by resource') NOT NULL DEFAULT 'unassigned',
PRIMARY KEY (`CategoryID`),
KEY `Index_CategoryName` (`Name`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'CategoryID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'ParentID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ProjectID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Name' =>
    [
      'Type' => 'varchar(255)',
      'Key' => 'Multi',
      'Default' => ''
    ],
    'UserID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'RoleID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'KeyID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Level' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'AssignmentMethod' =>
    [
      'Type' => "enum('unassigned','direct','least tickets by role','round robin by role','least tickets by resource','round robin by resource')",
      'Default' => 'unassigned'
    ]
  ];

  /**
   * The aliases for this item's columns
   *
   * @var array
   */
  protected static $hColumnAlias =
  [
    'categoryid' => 'CategoryID',
    'id' => 'CategoryID',
    'parentid' => 'ParentID',
    'projectid' => 'ProjectID',
    'name' => 'Name',
    'userid' => 'UserID',
    'roleid' => 'RoleID',
    'keyid' => 'KeyID',
    'level' => 'Level',
    'assignmentmethod' => 'AssignmentMethod'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'CategoryID' => 0,
    'ParentID' => 0,
    'ProjectID' => 0,
    'Name' => '',
    'UserID' => 0,
    'RoleID' => 0,
    'KeyID' => 0,
    'Level' => 0,
    'AssignmentMethod' => 'unassigned'
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'CategoryID' => 0,
    'ParentID' => 0,
    'ProjectID' => 0,
    'Name' => '',
    'UserID' => 0,
    'RoleID' => 0,
    'KeyID' => 0,
    'Level' => 0,
    'AssignmentMethod' => 'unassigned'
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['CategoryID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'TicketCategory';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'CategoryID';

  /**
   * Hash of all categories stored by ID
   *
   * @var array
   */
  protected static $hCache = [];

  /**
   * List of names and their associated types, used by __get to generate item objects
   *
   * @var array
   */
  protected $hAutoExpand =
  [
    'key' => 'ResourceKey',
    'parent' => 'TicketCategory'
  ];

  public static function invalidateCache()
  {
    self::$hCache = [];
    unset($_SESSION['TicketCategoryCache']);
  }

  public static function fillCache(\Limbonia\Database $oDatabase)
  {
    if (!empty(self::$hCache))
    {
      return;
    }

    if (\Limbonia\SessionManager::isStarted() && isset($_SESSION['TicketCategoryCache']))
    {
      self::$hCache = $_SESSION['TicketCategoryCache'];
      return;
    }

    $oList = $oDatabase->query("SELECT * FROM TicketCategory ORDER BY ParentID ASC, CategoryID ASC");

    if (empty($oList))
    {
      return;
    }

    $hNoPath = [];

    foreach ($oList->getData() as $hCategory)
    {
      if ($hCategory['ParentID'] == 0)
      {
        $hCategory['Path'] = [];
      }
      elseif (isset(self::$hCache[$hCategory['ParentID']]))
      {
        $hCategory['Path'] = self::$hCache[$hCategory['ParentID']]['Path'];
        $hCategory['Path'][] = self::$hCache[$hCategory['ParentID']]['Name'];
      }
      else
      {
        $hNoPath[$hCategory['CategoryID']] = $hCategory;
        continue;
      }

      self::$hCache[$hCategory['CategoryID']] = $hCategory;
    }

    while (count($hNoPath) > 0)
    {
      foreach (array_keys($hNoPath) as $iCategory)
      {
        if (isset(self::$hCache[$hNoPath[$iCategory]['ParentID']]))
        {
          $hNoPath[$iCategory]['Path'] = self::$hCache[$hNoPath[$iCategory]['ParentID']]['Path'];
          $hNoPath[$iCategory]['Path'][] = self::$hCache[$hNoPath[$iCategory]['ParentID']]['Name'];
          self::$hCache[$iCategory] = $hNoPath[$iCategory];
          unset($hNoPath[$iCategory]);
        }
      }
    }

    if (\Limbonia\SessionManager::isStarted())
    {
      $_SESSION['TicketCategoryCache'] = self::$hCache;
    }
  }

  /**
   * Sets the specified values if possible
   *
   * @param string $sName - the name of the field to set
   * @param mixed $xValue - the value to set the field to
   */
  public function __set($sName, $xValue)
  {
    $sLowerName = strtolower($sName);

    if ($sLowerName == 'parentid')
    {
      self::fillCache($this->getDatabase());
      $iCategory = (integer)$xValue;

      if (!isset(self::$hCache[$iCategory]))
      {
        throw new \Exception('Invalid ParentID: Parent category does not exist');
      }

      if (empty($iCategory))
      {
        $this->hData['Path'] =  [];
      }
      else
      {
        $this->hData['Path'] = self::$hCache[$iCategory]['Path'];
        $this->hData['Path'][] = self::$hCache[$iCategory]['Name'];
      }
    }

    if ($sLowerName == 'path')
    {
      //do nothing.... the user can not set this!
    }
    else
    {
      parent::__set($sName, $xValue);
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
    $sLowerName = strtolower($sName);

    if ($sLowerName == 'path')
    {
      self::fillCache($this->getDatabase());

      if (!isset($this->hData['path']) && isset(self::$hCache[$this->hData['CategoryID']]))
      {
        $this->hData['Path'] = self::$hCache[$this->hData['CategoryID']]['Path'];
      }

      return !empty($this->hData['Path']) ? implode(' > ', $this->hData['Path']) : '';
    }

    if ($sLowerName == 'fullname')
    {
      if (empty($this->hData['ParentID']))
      {
        return $this->hData['Name'];
      }

      return $this->__get('path') . " > {$this->hData['Name']}";
    }

    return parent::__get($sName);
  }

  /**
   * Determine if the specified value is set (exists) or not...
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    $sLowerName = strtolower($sName);

    if ($sLowerName == 'path')
    {
      return true;
    }

    return parent::__isset($sName);
  }

  /**
   * Unset the specified value
   *
   * @param string $sName
   */
  public function __unset($sName)
  {
    $sLowerName = strtolower($sName);

    if ($sLowerName == 'path')
    {
      //do nothing, the user can not change this field!
    }
    else
    {
      parent::__unset($sName);
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
    $hData['Path'] = $this->__get('path');
    $hData['FullName'] = $this->__get('fullname');
    return $hData;
  }

  /**
   * Either create or update this object depending on if it's already been created or not
   *
   * @return integer The ID of this object on success or false on failure
   */
  public function save()
  {
    $iCategory = parent::save();

    //if a category was created or updated
    if (!empty($iCategory))
    {
      //then invalidate the cached data, if any
      self::invalidateCache();
    }

    return $iCategory;
  }

  /**
   * Set the data for this object to the row of data specified by the given item id.
   *
   * @param integer $iItemID
   * @throws Exception
   */
  public function load($iItemID)
  {
    self::fillCache($this->getDatabase());

    if (!isset(self::$hCache[$iItemID]))
    {
      throw new \Exception("The table $this->sTable does not contain the $this->sIdColumn $iItemID!");
    }

    $this->setAll(self::$hCache[$iItemID]);
  }

  /**
   * Delete the row representing this object from the database
   *
   * @return boolean
   * @throws \Limbonia\Exception\DBResult
   */
  public function delete()
  {
    if (!$this->isCreated())
    {
      return true;
    }

    $iId = $this->id;
    $bDeleted = parent::delete();

    //if the category has been deleted (and it had been created)
    if ($bDeleted)
    {
      $this->getDatabase()->query("UPDATE TicketCategory SET ParentID = 0 WHERE ParentID = $iId");

      //then invalidate the cached data, if any
      self::invalidateCache();
    }

    return $bDeleted;
  }
}