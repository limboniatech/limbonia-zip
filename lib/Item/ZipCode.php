<?php
namespace Omniverse\Item;

/**
 * Omniverse Zip Code Item Class
 *
 * Item based wrapper around the ZipCode table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class ZipCode extends \Omniverse\Item
{
  /**
   * Get a list of all the zips in the specified radius of miles from the specified zip code
   *
   * @param integer $iZip
   * @param integer $iMiles
   * @return array
   */
  public function getZipFromProximity($iZip, $iMiles)
  {
    $oCenter = parent::fromId('ZipCode', $iZip, $this->getDB());

    if ($oCenter->id == 0)
    {
      return [];
    }

    $oZipList = parent::search('ZipCode', ['Distance' => "<:$iMiles"], ['*', "truncate((degrees(acos(sin(radians(Latitude)) * sin(radians($oCenter->latitude)) + cos(radians(latitude)) * cos( radians($oCenter->latitude)) * cos(radians(longitude - {$hCenter['Longitude']})))) * 69.09), 1) AS Distance"], 'Distance', $this->getDB());

    if ($oZipList->count() == 0)
    {
      return [];
    }

    $hZip = [];

    foreach ($oZipList as $hTemp)
    {
      $hZip[array_shift($hTemp)] = $hTemp;
    }

    return $hZip;
  }

  /**
   * Return a list of all the cities in the specified state
   *
   * @param string $sState
   * @return array
   */
  public function getCitiesByState($sState)
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT City FROM ZipCode WHERE State = :State ORDER BY City");
    return $oResult->execute([':State' => $sState]) ? $oResult->fetchAll() : [];
  }

  /**
   * Return the list of zips in the specified city/state
   *
   * @param string $sCity
   * @param string $sState
   * @return array
   */
  public function getZipsByCity($sCity, $sState)
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT Zip FROM ZipCode WHERE City = :City AND State = :State ORDER BY Zip");
    return $oResult->execute([':City' => $sCity, ':State' => $sState]) ? $oResult->fetchAll() : [];
  }

  /**
   * Get the
   *
   * @param integer $iZip
   * @return \Omniverse\Item
   */
  public function getCityByZip($iZip)
  {
    $oCityList = parent::search('ZipCode', ['Zip' => $iZip], 'City');
    return $oCityList->count() > 0 ? $oCityList[0] : false;
  }
}