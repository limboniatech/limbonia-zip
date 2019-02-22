<?php
namespace Limbonia\Item;

/**
 * Limbonia Project Item Class
 *
 * Item based wrapper around the Project table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Project extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`ProjectID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`TopCategoryID` int(10) unsigned NOT NULL DEFAULT '0',
`Name` varchar(255) NOT NULL,
`Repository` varchar(255) NOT NULL DEFAULT '',
`Description` text,
PRIMARY KEY (`ProjectID`),
UNIQUE KEY `Unique_ProjectName` (`Name`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'ProjectID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'TopCategoryID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'Name' =>
    [
      'Type' => 'varchar(255)',
      'Key' => 'UNI',
      'Default' => ''
    ],
    'Repository' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'Description' =>
    [
      'Type' => 'text',
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
    'projectid' => 'ProjectID',
    'id' => 'ProjectID',
    'topcategoryid' => 'TopCategoryID',
    'name' => 'Name',
    'repository' => 'Repository',
    'description' => 'Description'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'ProjectID' => 0,
    'TopCategoryID' => 0,
    'Name' => '',
    'Repository' => '',
    'Description' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'ProjectID' => 0,
    'TopCategoryID' => 0,
    'Name' => '',
    'Repository' => '',
    'Description' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['ProjectID', 'TopCategoryID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'Project';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'ProjectID';

  /**
   * List of names and their associated types, used by __get to generate item objects
   *
   * @var array
   */
  protected $hAutoExpand = ['topcategory' => 'TicketCategory'];

  /**
   * Return the list of configured project
   *
   * @return \Limbonia\ItemList
   */
  public static function getProjectList(\Limbonia\Controller $oController = null)
  {
    if (empty($oController))
    {
      $oController = \Limbonia\Controller::getDefault();
    }

    return $oController->itemSearch('Project', [], ['Name']);
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

    if ($sLowerName == 'name')
    {
      $this->topCategory->name = $xValue;
    }

    if ($sLowerName == 'topcategoryid' && isset($this->hItemObjects['topcategory']))
    {
      unset($this->hItemObjects['topcategory']);
    }

    parent::__set($sName, $xValue);
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

    if ($sLowerName == 'topcategory' && !isset($this->hItemObjects[$sLowerName]))
    {
      try
      {
        if ($this->hData['TopCategoryID'] == 0)
        {
          throw new \Exception('Top Category not set!');
        }

        $this->hItemObjects[$sLowerName] = $this->getController()->itemFromId('TicketCategory', $this->hData['TopCategoryID']);

      }
      catch (\Exception $e)
      {
        $this->hItemObjects[$sLowerName] = $this->getController()->itemFromArray('TicketCategory',
        [
          'name' => $this->hData['Name'],
          'projectid' => $this->hData['ProjectID']
        ]);
      }
    }

    return parent::__get($sName);
  }

  /**
   * Created a row for this object's data in the database
   *
   * @return integer The ID of the row created on success or false on failure
   */
  protected function create()
  {
    $this->topCategory->name = $this->name;

    //if that fails
    if ($this->topCategory->save() === false)
    {
      //then complain
      throw new \Exception('Failed to create top category');
    }

    //set the topCategoryId to the new category ID
    $this->topCategoryId = $this->topCategory->id;

    //create the base project
    $iProject = parent::create();

    if ($iProject === false)
    {
      //if that fails then delete the new category
      $this->topCategory->delete();
      return false;
    }

    //if it works
    try
    {
      $this->topCategory->projectId = $this->id;

      //attempt to update the category with the new project ID
      if (!$this->topCategory->save())
      {
        //if that fails then delete the new category
        $this->topCategory->delete();

        //and complain
        throw new \Exception('Failed to add top category');
      }
    }
    catch (\Exception $e)
    {
      //if anything fails, delete this item
      $this->delete();

      //then rethrow the exception
      throw new \Exception('Post-creation data failed: ' . $e->getMessage());
    }

    return $iProject;
  }

  /**
   * Update this object's data in the data base with current data
   *
   * @return integer The ID of this object on success or false on failure
   */
  protected function update()
  {
    $this->topCategory->name = $this->name;
    $this->topCategory->projectId = $this->id;

    //if that fails
    if ($this->topCategory->save() === false)
    {
      //then complain
      throw new \Exception('Failed to update top category');
    }

    return parent::update();
  }

  /**
   * Delete the row representing this object from the database
   *
   * @return boolean
   * @throws \Limbonia\Exception\DBResult
   */
  public function delete()
  {
    if (!parent::delete())
    {
      return false;
    }

    $this->topCategory->delete();
    return true;
  }

  /**
   * Return a list of releases for this project
   *
   * @param string $sType (optional) The type of list to return
   * @return \Limbonia\ItemList
   */
  public function getReleaseList($sType = '')
  {
    if (empty($this->id))
    {
      return $this->oController->itemList('ProjectRelease', 'SELECT * FROM ProjectRelease WHERE 1 = 0');
    }

    $sLowerType = strtolower($sType);

    if ($sLowerType == 'changelog')
    {
      $sSQL = "SELECT DISTINCT R.* FROM ProjectRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status = 'closed' AND R.ProjectID = $this->id ORDER BY R.Major DESC, R.Minor DESC, R.Patch DESC";
      return $this->oController->itemList('ProjectRelease', $sSQL);
    }

    if ($sLowerType == 'roadmap')
    {
      $sSQL = "SELECT DISTINCT R.* FROM ProjectRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.ProjectID = $this->id ORDER BY R.Major ASC, R.Minor ASC, R.Patch ASC";
      return $this->oController->itemList('ProjectRelease', $sSQL);
    }

    if ($sLowerType == 'active')
    {
      $sSQL = "SELECT R.* from ProjectRelease AS R, Ticket AS T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.ProjectID = $this->id ORDER BY Major, Minor, Patch";
      return $this->oController->itemList('ProjectRelease', $sSQL);
    }

    return $this->oController->itemSearch('ProjectRelease', ['ProjectID' => $this->id], ['Major', 'Minor', 'Patch']);
  }

  /**
   * Add a new release to this project
   *
   * @param string $sVersion
   * @param string $sNote (optional)
   * @return integer The ID of the new release object on success or false on failure
   */
  public function addRelease($sVersion, $sNote = '')
  {
    $hRelease =
    [
      'ProjectID' => $this->id,
      'Version' => $sVersion,
      'Note' => trim((string)$sNote)
    ];

    $oRelease = $this->oController->itemFromArray('ProjectRelease', $hRelease);
    return $oRelease->save();
  }

  /**
   * Remove the specified release from this project
   *
   * @param integer $iRelease
   * @return boolean
   */
  public function removeRelease($iRelease)
  {
    $oRelease = $this->oController->itemFromId('ProjectRelease', $iRelease);
    return $oRelease->delete();
  }

  /**
   * Generate and return a list of ticket related to this project but is not associated with any releases
   *
   * @return \Limbonia\ItemList
   */
  public function getUnversionedTikets()
  {
    return $this->oController->itemList('Ticket', "SELECT * FROM Ticket WHERE Status != 'closed' AND ProjectID = $this->projectId AND (ReleaseID IS NULL OR ReleaseID = 0) ORDER BY Priority, CreateTime");
  }

  /**
   * Return the list of categories related to this project
   *
   * @return \Limbonia\ItemList
   */
  public function getCategoryList()
  {
    return $this->oController->itemSearch('TicketCategory', ['ProjectID' => $this->id], ['ParentID', 'Name']);
  }

  /**
   * Add a new category to this project
   *
   * @param array $hCategory - the category data to save
   * @return \Limbonia\Item\TicketCategory The new category object on success or false on failure
   * @throws \Exception
   */
  public function addCategory(array $hCategory = [])
  {
    $hLowerCategory = \array_change_key_case($hCategory, CASE_LOWER);

    if (!isset($hLowerCategory['name']))
    {
      throw new \Exception('Category name is required!');
    }

    $hCategory['projectid'] = $this->id;
    $hCategory['parentid'] = $this->topCategoryId;

    if (isset($hCategory['userid']))
    {
      $hCategory['AssignmentMethod'] = 'direct';
    }

    $oCategory = $this->oController->itemFromArray('TicketCategory', $hCategory);
    return $oCategory->save() ? $oCategory : false;
  }

  /**
   * Remove the specified category from this project
   *
   * @param integer $iCategory
   * @return boolean
   * @throws \Exception
   */
  public function removeCategory($iCategory)
  {
    $oCategory = $this->oController->itemFromId('TicketCategory', $iCategory);

    //if the name of the category matches the project name
    if ($oCategory->name == $this->name)
    {
      //then this is the top category for this project, do not remove it!
      throw new \Exception('The top project category can not be removed');
    }

    //if the ProjectID of the category doesn't match the project ID
    if ($oCategory->name == $this->name || $oCategory->projectId != $this->id)
    {
      //then this category is not controlled by this project, do not remove it!
      throw new \Exception('The specified category is not controlled by this project!');
    }

    return $oCategory->delete();
  }
}