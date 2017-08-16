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
  public function getZipFromProximity($iZip, $iMiles)
  {
    if (!$hCenter = parent::fromId('ZipCode', $iZip, $this->getDB()))
    {
      return FALSE;
    }

    if (!$aTemp = parent::search('ZipCode', ['Distance' => "<:$iMiles"], ['*', "truncate((degrees(acos(sin(radians(Latitude)) * sin(radians({$hCenter['Latitude']})) + cos(radians(latitude)) * cos( radians({$hCenter['Latitude']})) * cos(radians(longitude - {$hCenter['Longitude']})))) * 69.09), 1) AS Distance"], 'Distance', $this->getDB()))
    {
      return false;
    }

    $hZip = [];

    foreach ($aTemp as $hTemp)
    {
      $hZip[array_shift($hTemp)] = $hTemp;
    }

    return $hZip;
  }

  public function getCitiesByState($sState)
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT City FROM ZipCode WHERE State = :State ORDER BY City");
    $oResult->execute([':State' => $sState]);
    return $oResult;
  }

  public function getZipsByCity($sCity, $sState)
  {
    $oResult = $this->getDB()->prepare("SELECT DISTINCT Zip FROM ZipCode WHERE City = :City AND State = :State ORDER BY Zip");
    $oResult->execute([':City' => $sCity, ':State' => $sState]);
    return $oResult;
  }

  public function getCityByZip($iZip)
  {
    return parent::search('ZipCode', ['Zip' => $iZip], 'City');
  }
}