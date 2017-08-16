<?php
namespace Omniverse\Item;

/**
 * Omniverse Area Item Class
 *
 * Item based wrapper around the Area table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Area extends \Omniverse\Item
{
  /**
   * Return the entire list if zip codes associeated with this area
   *
   * @return Omniverse\ItemList
   */
  public function getZipList()
  {
    return parent::search('Area_Zip', ['AreaID' => $this->ID], null, $this->getDB());
  }

  public function removeZips($aZip)
  {
    if (!is_array($aZip))
    {
      $aZip = (array)$aZip;
    }

    $sZipList = "'" . implode("', '", $aZip) . "'";
    return $this->getDB()->exec("DELETE FROM Area_Zip WHERE AreaID = {$this->ID} AND Zip IN ($sZipList)");
  }

  public function addZips($aZip)
  {
    if (!is_array($aZip))
    {
      $aZip = (array)$aZip;
    }

    foreach ($aZip as $sZip)
    {
      $oZip = parent::fromArray('Area_Zip', ['AreaID' => $this->ID, 'Zip' => $sZip], $this->getDB());
      $oZip->save();
    }

    return true;
  }
}