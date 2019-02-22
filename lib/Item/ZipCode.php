<?php
namespace Limbonia\Item;

/**
 * Limbonia Zip Code Item Class
 *
 * Item based wrapper around the ZipCode table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ZipCode extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`Zip` int(5) unsigned zerofill NOT NULL,
`Latitude` float NOT NULL,
`Longitude` float NOT NULL,
`City` varchar(30) NOT NULL,
`State` char(2) NOT NULL,
`County` varchar(30) NOT NULL,
`ZipClass` varchar(15) DEFAULT 'STANDARD',
`LocationType` varchar(15) DEFAULT 'PRIMARY',
`Decommisioned` INT(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`Zip`),
KEY `Index_State` (`State`),
KEY `Index_Latitude_Longitude` (`Latitude`,`Longitude`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'Zip' =>
    [
      'Type' => 'int(5) unsigned zerofill',
      'Key' => 'Primary',
      'Default' => 0
    ],
    'Latitude' =>
    [
      'Type' => 'float',
      'Key' => 'Multi',
      'Default' => null
    ],
    'Longitude' =>
    [
      'Type' => 'float',
      'Default' => null
    ],
    'City' =>
    [
      'Type' => 'varchar(30)',
      'Default' => ''
    ],
    'State' =>
    [
      'Type' => 'char(2)',
      'Key' => 'Multi',
      'Default' => ''
    ],
    'County' =>
    [
      'Type' => 'varchar(30)',
      'Default' => ''
    ],
    'ZipClass' =>
    [
      'Type' => 'varchar(15)',
      'Default' => 'STANDARD'
    ],
    'LocationType' =>
    [
      'Type' => 'varchar(15)',
      'Default' => 'PRIMARY'
    ],
    'Decommisioned' =>
    [
      'Type' => 'int(1)',
      'Default' => 0
    ]
  ];

  /**
   * The aliases for this item's columns
   *
   * @var array
   */
  protected static $hColumnAlias =
  [
    'zip' => 'Zip',
    'id' => 'Zip',
    'latitude' => 'Latitude',
    'longitude' => 'Longitude',
    'city' => 'City',
    'state' => 'State',
    'county' => 'County',
    'zipclass' => 'ZipClass',
    'locationtype' => 'LocationType',
    'decommisioned' => 'Decommisioned'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'Zip' => 0,
    'Latitude' => null,
    'Longitude' => null,
    'City' => '',
    'State' => '',
    'County' => '',
    'ZipClass' => 'STANDARD',
    'LocationType' => 'PRIMARY',
    'Decommisioned' => 0
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'Zip' => 0,
    'Latitude' => null,
    'Longitude' => null,
    'City' => '',
    'State' => '',
    'County' => '',
    'ZipClass' => 'STANDARD',
    'LocationType' => 'PRIMARY',
    'Decommisioned' => 0
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['Zip'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'ZipCode';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'Zip';

  public function setup()
  {
    parent::setup();
    $oCount = $this->getDatabase()->query("SELECT COUNT(Zip) FROM ZipCode");

    if ($oCount->fetchOne() == 0)
    {
      $rZipFile = fopen('../../config/free-zipcode-database.csv', 'r');

      if ($rZipFile === false)
      {
        throw new Exception("Failed to open zipcode data file");
      }

      $oZipInsert = $this->getDatabase()->prepare("INSERT INTO ZipCode (Zip, Longitude, Longitude, City, State, ZipClass, LocationType, Decommisioned) VALUES (:Zip, :Longitude, :Longitude, :City, :State, :ZipClass, :LocationType, :Decommisioned)");
      $aHeader = fgetcsv($rZipFile);

      while (($aZip = fgetcsv($rZipFile)) !== false)
      {
        $hZip = array_combine($aHeader, $aZip);
        $oZipInsert->execute
        ([
          ':Zip' => $hZip['Zipcode'],
          ':Longitude' => $hZip['Lat'],
          ':Longitude' => $hZip['Long'],
          ':City' => $hZip['City'],
          ':State' => $hZip['State'],
          ':ZipClass' => $hZip['ZipCodeType'],
          ':LocationType' => $hZip['LocationType'],
          ':Decommisioned' => $hZip['Decommisioned']
        ]);
      }

      fclose($rZipFile);
    }
  }

  /**
   * Get a list of all the zips in the specified radius of miles from the specified zip code
   *
   * @param integer $iZip
   * @param integer $iMiles
   * @return array
   */
  public function getZipFromProximity($iZip, $iMiles)
  {
    $oCenter = parent::fromId('ZipCode', $iZip, $this->getDatabase());

    if ($oCenter->id == 0)
    {
      return [];
    }

    $oZipList = parent::search('ZipCode', ['Distance' => "<:$iMiles"], ['*', "truncate((degrees(acos(sin(radians(Latitude)) * sin(radians($oCenter->latitude)) + cos(radians(latitude)) * cos( radians($oCenter->latitude)) * cos(radians(longitude - {$oCenter->longitude})))) * 69.09), 1) AS Distance"], 'Distance', $this->getDatabase());

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
    $oResult = $this->getDatabase()->prepare("SELECT DISTINCT City FROM ZipCode WHERE State = :State ORDER BY City");
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
    $oResult = $this->getDatabase()->prepare("SELECT DISTINCT Zip FROM ZipCode WHERE City = :City AND State = :State ORDER BY Zip");
    return $oResult->execute([':City' => $sCity, ':State' => $sState]) ? $oResult->fetchAll() : [];
  }

  /**
   * Get the
   *
   * @param integer $iZip
   * @return \Limbonia\Item
   */
  public function getCityByZip($iZip)
  {
    $oCityList = parent::search('ZipCode', ['Zip' => $iZip], 'City');
    return $oCityList->count() > 0 ? $oCityList[0] : false;
  }
}