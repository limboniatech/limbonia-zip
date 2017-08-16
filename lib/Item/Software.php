<?php
namespace Omniverse\Item;

/**
 * Omniverse Software Item Class
 *
 * Item based wrapper around the Software table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Software extends \Omniverse\Item
{
  public static function getSoftwareList()
  {
    return parent::search('Software', [], ['Name']);
  }

  public function getReleaseList($sType='')
  {
    $sType = strtolower(trim($sType));

    if ($sType == 'changelog')
    {
      $sSQL = "SELECT DISTINCT R.* FROM SoftwareRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status = 'closed' AND R.SoftwareID = $this->ID ORDER BY R.Major DESC, R.Minor DESC, R.Patch DESC";
      return parent::getList('SoftwareRelease', $sSQL, $this->getDB());
    }

    if ($sType == 'roadmap')
    {
      $sSQL = "SELECT DISTINCT R.* FROM SoftwareRelease R, Ticket T WHERE R.TicketID = T.TicketID AND T.Status != 'closed' AND R.SoftwareID = $this->ID ORDER BY R.Major ASC, R.Minor ASC, R.Patch ASC";
      return parent::getList('SoftwareRelease', $sSQL, $this->getDB());
    }

    return parent::search('SoftwareRelease', ['SoftwareID' => $this->ID], ['Major', 'Minor', 'Patch'], $this->getDB());
  }

  public function getCurrentRelease()
  {
  }

  public function addRelease($sVersion, $sNote='')
  {
    $hRelease =
    [
      'SoftwareID' => $this->ID,
      'Version' => $sVersion,
      'Note' => trim((string)$sNote)
    ];

    $oRelease = parent::fromArray('SoftwareRelease', $hRelease, $this->getDB());
    return $oRelease->save();
  }

  public function removeRelease($iRelease)
  {
    $oRelease = parent::fromId('SoftwareRelease', $iRelease, $this->getDB());
    return $oRelease->delete();
  }

  public function getElementList()
  {
    return parent::search('SoftwareElement', ['SoftwareID' => $this->ID], ['Name'], $this->getDB());
  }

  public function addElement($sName, $iUser=0)
  {
    $iUser = empty($iUser) ? 0 : (integer)$iUser;
    $hElement =
    [
      'SoftwareID' => $this->ID,
      'Name' => trim($sName),
      'UserID' => empty($iUser) ? 0 : $iUser
    ];

    $oElement = parent::fromArray('SoftwareElement', $hElement, $this->getDB());
    return $oElement->save();
  }

  public function removeElement($iElement)
  {
    $oElement = parent::fromId('SoftwareElement', $iElement, $this->getDB());
    return $oElement->delete();
  }
}