<?php
namespace Limbonia\Item;

/**
 * Limbonia Resource Item Class
 *
 * Item based wrapper around the Template table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Template extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`TemplateID` int(10) unsigned NOT NULL,
`Uri` varchar(255) NOT NULL,
`TemplateText` text,
PRIMARY KEY (`TemplateID`),
UNIQUE KEY `Unique_Uri` (`Uri`),
FULLTEXT KEY `Fulltext_Template_TemplateText` (`TemplateText`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'TemplateID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0
    ],
    'Uri' =>
    [
      'Type' => 'varchar(255)',
      'Key' => 'UNI',
      'Default' => ''
    ],
    'TemplateText' =>
    [
      'Type' => 'text',
      'Key' => 'Multi',
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
    'templateid' => 'TemplateID',
    'id' => 'TemplateID',
    'uri' => 'Uri',
    'templatetext' => 'TemplateText'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'TemplateID' => 0,
    'Uri' => '',
    'TemplateText' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'TemplateID' => 0,
    'Uri' => '',
    'TemplateText' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['TemplateID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'Template';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'TemplateID';
}