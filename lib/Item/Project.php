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
   * Return the list of configured project
   *
   * @return \Limbonia\ItemList
   */
  public static function getProjectList()
  {
    return parent::search('Project', [], ['Name']);
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
      return parent::getList('ProjectRelease', $sSQL, $this->getDatabase());
    }

    if ($sLowerType == 'roadmap')
    {
      $sSQL = "SELECT DISTINCT R.* FROM ProjectRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.ProjectID = $this->id ORDER BY R.Major ASC, R.Minor ASC, R.Patch ASC";
      return parent::getList('ProjectRelease', $sSQL, $this->getDatabase());
    }

    if ($sLowerType == 'active')
    {
      $sSQL = "SELECT R.* from ProjectRelease AS R, Ticket AS T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.ProjectID = $this->id ORDER BY Major, Minor, Patch";
      return parent::getList('ProjectRelease', $sSQL, $this->getDatabase());
    }

    return parent::search('ProjectRelease', ['ProjectID' => $this->id], ['Major', 'Minor', 'Patch'], $this->getDatabase());
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

    $oRelease = parent::fromArray('ProjectRelease', $hRelease, $this->getDatabase());
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
    $oRelease = parent::fromId('ProjectRelease', $iRelease, $this->getDatabase());
    return $oRelease->delete();
  }

  /**
   * Return the list of elements related to this project
   *
   * @return \Limbonia\ItemList
   */
  public function getElementList()
  {
    return parent::search('ProjectElement', ['ProjectID' => $this->id], ['Name'], $this->getDatabase());
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

    $oElement = parent::fromArray('ProjectElement', $hElement, $this->getDatabase());
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
    $oElement = parent::fromId('ProjectElement', $iElement, $this->getDatabase());
    return $oElement->delete();
  }

  /**
   * Generate and return a list of ticket related to this project but is not associated with any releases
   *
   * @return \Limbonia\ItemList
   */
  public function getUnversionedTikets()
  {
    return parent::getList('Ticket', "SELECT * FROM Ticket WHERE Status != 'closed' AND ProjectID = $this->projectId AND (ReleaseID IS NULL OR ReleaseID = 0) ORDER BY Priority, CreateTime", $this->getDatabase());
  }
}