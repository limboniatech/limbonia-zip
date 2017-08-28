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

  /**
   * Remove the specified zip(s) from this Area
   *
   * @param mixed $xZip Either an array of zips or a single zip
   * @return boolean True on success and false on failure
   */
  public function removeZips($xZip)
  {
    if (empty($xZip))
    {
      return true;
    }

    $aZip = is_array($xZip) ? $xZip : (array)$xZip;
    $iAffected = $this->getDB()->exec("DELETE FROM Area_Zip WHERE AreaID = {$this->id} AND Zip IN ('" . implode("', '", $aZip) . "')");
    return $iAffected > 0;
  }


  /**
   * Add the specified zip(s) to this Area
   *
   * @param mixed $xZip Either an array of zips or a single zip
   * @return boolean True on success and false on failure
   */
  public function addZips($xZip)
  {
    if (empty($xZip))
    {
      return true;
    }

    $aZip = is_array($xZip) ? $xZip : (array)$xZip;
    $iCount = 0;

    foreach ($aZip as $sZip)
    {
      $oZip = parent::fromArray('Area_Zip', ['AreaID' => $this->id, 'Zip' => $sZip], $this->getDB());

      if ($oZip->save())
      {
        $iCount++;
      }
    }

    return $iCount > 0;
  }
}