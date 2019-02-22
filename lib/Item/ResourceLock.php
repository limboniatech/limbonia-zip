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
class ResourceLock extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`LockID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`KeyID` int(10) unsigned NOT NULL,
`MinKey` int(10) unsigned NOT NULL DEFAULT '1000',
`Resource` varchar(255) DEFAULT NULL,
`Component` varchar(255) DEFAULT NULL,
PRIMARY KEY (`LockID`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [
    'LockID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'KeyID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'MinKey' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 1000
    ],
    'Resource' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'Component' =>
    [
      'Type' => 'varchar(255)',
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
    'lockid' => 'LockID',
    'id' => 'LockID',
    'keyid' => 'KeyID',
    'minkey' => 'MinKey',
    'resource' => 'Resource',
    'component' => 'Component'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'LockID' => 0,
    'KeyID' => 0,
    'MinKey' => 1000,
    'Resource' => '',
    'Component' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'LockID' => 0,
    'KeyID' => 0,
    'MinKey' => 1000,
    'Resource' => '',
    'Component' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['LockID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'ResourceLock';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'LockID';
}