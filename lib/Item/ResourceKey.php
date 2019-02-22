<?php
namespace Limbonia\Item;

/**
 * Limbonia Resource Item Class
 *
 * Item based wrapper around the ResourceLock table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ResourceKey extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`KeyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`Name` varchar(25) NOT NULL,
PRIMARY KEY (`KeyID`),
UNIQUE KEY `Unique_ResourceName` (`Name`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [
    'KeyID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'Name' =>
    [
      'Type' => 'varchar(25)',
      'Key' => 'UNI',
      'Default' => ''
    ]
  ];

  /**
   * The aliases for this item's columns
   *
   * @var array
   */
  protected static $hColumnAlias =
  [
    'keyid' => 'KeyID',
    'id' => 'KeyID',
    'name' => 'Name'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'KeyID' => 0,
    'Name' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'KeyID' => 0,
    'Name' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['KeyID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'ResourceKey';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'KeyID';
}