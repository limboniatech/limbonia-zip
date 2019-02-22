<?php
namespace Limbonia\Item;

/**
 * Limbonia State Item Class
 *
 * Item based wrapper around the States table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class States extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`State` varchar(20) NOT NULL,
`PostalCode` char(2) NOT NULL,
`Actual` int(2) DEFAULT '0',
PRIMARY KEY (`PostalCode`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'State' =>
    [
      'Type' => 'varchar(20)',
      'Default' => ''
    ],
    'PostalCode' =>
    [
      'Type' => 'char(2)',
      'Key' => 'Primary',
      'Default' => ''
    ],
    'Actual' =>
    [
      'Type' => 'int(2)',
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
    'state' => 'State',
    'postalcode' => 'PostalCode',
    'id' => 'PostalCode',
    'actual' => 'Actual'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'State' => '',
    'PostalCode' => '',
    'Actual' => 0
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'State' => '',
    'PostalCode' => '',
    'Actual' => 0
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['PostalCode'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'States';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'PostalCode';

  /**
   * List of states by zip code
   *
   * @var array
   */
  protected static $hState = [];

  public function setup()
  {
    parent::setup();
    $oCount = $this->getDatabase()->query("SELECT COUNT(PostalCode) FROM States");

    if ($oCount->fetchOne() == 0)
    {
      $rStateFile = fopen('../../config/States.csv', 'r');

      if ($rStateFile === false)
      {
        throw new \Limbonia\Exception("Failed to open state data file");
      }

      $oStateInsert = $this->getDatabase()->prepare("INSERT INTO States (State, PostalCode, Actual) VALUES (:State, :PostalCode, :Actual)");
      $aHeader = fgetcsv($rStateFile);

      while (($aState = fgetcsv($rStateFile)) !== false)
      {
        $hState = array_combine($aHeader, $aState);
        $oStateInsert->execute
        ([
          ':State' => $hState['State'],
          ':PostalCode' => $hState['PostalCode'],
          ':Actual' => $hState['Actual']
        ]);
      }

      fclose($rStateFile);
    }
  }

    /**
   * Return the list of states
   *
   * @return array
   */
  public static function getStateList()
  {
    if (empty(self::$hState))
    {
      $oStates = parent::search('States', ['Actual' => 1], 'State');

      if (count($oStates) > 0)
      {
        foreach ($oStates as $hTemp)
        {
          self::$hState[$hTemp['PostalCode']] = $hTemp['State'];
        }
      }
    }

    return self::$hState;
  }
}