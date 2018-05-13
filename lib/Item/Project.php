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
   * Created a row for this object's data in the database
   *
   * @return integer The ID of the row created on success or false on failure
   */
  protected function create()
  {
    //create the base project
    $iProject = parent::create();

    //if it works
    if ($iProject)
    {
      try
      {
        //try creating a top category
        $oCategory = $this->addCategory(['name' => $this->name]);

        //if that fails
        if ($oCategory === false)
        {
          //then complain
          throw new \Exception('Failed to create project top category');
        }

        //set the topCategoryId to the new category ID
        $this->topCategoryId = $oCategory->id;

        //attempt to update the base project
        if (!$this->update())
        {
          //if that fails then delete the new category
          $oCategory->delete();

          //and complain
          throw new \Exception('Failed to add project top category');
        }
      }
      catch (\Exception $e)
      {
        //if anything fails, delete this item
        $this->delete();

        //then rethrow the exception
        throw $e;
      }
    }

    return $iProject;
  }

  /**
   * Return a list of releases for this project
   *
   * @param string $sType (optional) The type of list to return
   * @return \Limbonia\ItemList
   */
  public function getReleaseList($sType = '')
  {
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
   * Return the list of elements related to this project
   *
   * @return \Limbonia\ItemList
   */
  public function getElementList()
  {
    return $this->oController->itemSearch('ProjectElement', ['ProjectID' => $this->id], ['Name']);
  }

  /**
   * Add a new element to this project
   *
   * @param string $sName
   * @param integer $iUser (optional)
   * @return integer The ID of the new element object on success or false on failure
   */
  public function addElement($sName, $iUser = 0)
  {
    $hElement =
    [
      'ProjectID' => $this->id,
      'Name' => trim($sName),
      'UserID' => empty($iUser) ? 0 : $iUser
    ];

    $oElement = $this->oController->itemFromArray('ProjectElement', $hElement);
    return $oElement->save();
  }

  /**
   * Remove the specified element from this project
   *
   * @param integer $iElement
   * @return boolean
   */
  public function removeElement($iElement)
  {
    $oElement = $this->oController->itemFromId('ProjectElement', $iElement);
    return $oElement->delete();
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