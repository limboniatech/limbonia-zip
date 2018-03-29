<?php
namespace Limbonia\Item;

/**
 * Limbonia Software Item Class
 *
 * Item based wrapper around the Software table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Software extends \Limbonia\Item
{
  /**
   * Return the list of configured software
   *
   * @return \Limbonia\ItemList
   */
  public static function getSoftwareList()
  {
    return parent::search('Software', [], ['Name']);
  }

  /**
   * Return a list of releases for this software
   *
   * @param string $sType (optional) The type of list to return
   * @return \Limbonia\ItemList
   */
  public function getReleaseList($sType = '')
  {
    $sLowerType = strtolower($sType);

    if ($sLowerType == 'changelog')
    {
      $sSQL = "SELECT DISTINCT R.* FROM SoftwareRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status = 'closed' AND R.SoftwareID = $this->id ORDER BY R.Major DESC, R.Minor DESC, R.Patch DESC";
      return parent::getList('SoftwareRelease', $sSQL, $this->getDatabase());
    }

    if ($sLowerType == 'roadmap')
    {
      $sSQL = "SELECT DISTINCT R.* FROM SoftwareRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.SoftwareID = $this->id ORDER BY R.Major ASC, R.Minor ASC, R.Patch ASC";
      return parent::getList('SoftwareRelease', $sSQL, $this->getDatabase());
    }

    if ($sLowerType == 'active')
    {
      $sSQL = "SELECT R.* from SoftwareRelease AS R, Ticket AS T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.SoftwareID = $this->id ORDER BY Major, Minor, Patch";
      return parent::getList('SoftwareRelease', $sSQL, $this->getDatabase());
    }

    return parent::search('SoftwareRelease', ['SoftwareID' => $this->id], ['Major', 'Minor', 'Patch'], $this->getDatabase());
  }

  /**
   * Add a new release to this software
   *
   * @param string $sVersion
   * @param string $sNote (optional)
   * @return integer The ID of the new release object on success or false on failure
   */
  public function addRelease($sVersion, $sNote = '')
  {
    $hRelease =
    [
      'SoftwareID' => $this->id,
      'Version' => $sVersion,
      'Note' => trim((string)$sNote)
    ];

    $oRelease = parent::fromArray('SoftwareRelease', $hRelease, $this->getDatabase());
    return $oRelease->save();
  }

  /**
   * Remove the specified release from this software
   *
   * @param integer $iRelease
   * @return boolean
   */
  public function removeRelease($iRelease)
  {
    $oRelease = parent::fromId('SoftwareRelease', $iRelease, $this->getDatabase());
    return $oRelease->delete();
  }

  /**
   * Return the list of elements related to this software
   *
   * @return \Limbonia\ItemList
   */
  public function getElementList()
  {
    return parent::search('SoftwareElement', ['SoftwareID' => $this->id], ['Name'], $this->getDatabase());
  }

  /**
   * Add a new element to this software
   *
   * @param string $sName
   * @param integer $iUser (optional)
   * @return integer The ID of the new element object on success or false on failure
   */
  public function addElement($sName, $iUser = 0)
  {
    $hElement =
    [
      'SoftwareID' => $this->id,
      'Name' => trim($sName),
      'UserID' => empty($iUser) ? 0 : $iUser
    ];

    $oElement = parent::fromArray('SoftwareElement', $hElement, $this->getDatabase());
    return $oElement->save();
  }

  /**
   * Remove the specified element from this software
   *
   * @param integer $iElement
   * @return boolean
   */
  public function removeElement($iElement)
  {
    $oElement = parent::fromId('SoftwareElement', $iElement, $this->getDatabase());
    return $oElement->delete();
  }

  /**
   * Generate and return a list of ticket related to this software but is not associated with any releases
   *
   * @return \Limbonia\ItemList
   */
  public function getUnversionedTikets()
  {
    return parent::getList('Ticket', "SELECT * FROM Ticket WHERE Status != 'closed' AND SoftwareID = $this->softwareId AND (ReleaseID IS NULL OR ReleaseID = 0) ORDER BY Priority, CreateTime", $this->getDatabase());
  }
}