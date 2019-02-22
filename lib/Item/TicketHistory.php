<?php
namespace Limbonia\Item;

/**
 * Limbonia Ticket History Item Class
 *
 * Item based wrapper around the TicketHistory table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TicketHistory extends \Limbonia\Item
{
  /**
   * The database schema for creating this item's table in the database
   *
   * @var string
   */
  protected static $sSchema = "`HistoryID` int(10) unsigned NOT NULL AUTO_INCREMENT,
`ContentID` int(10) unsigned NOT NULL DEFAULT '0',
`CarbonCopied` varchar(255) DEFAULT NULL,
`CarbonCopyFailed` varchar(255) DEFAULT NULL,
`UserIDFrom` int(10) unsigned NOT NULL DEFAULT '0',
`UserIDTo` int(10) unsigned NOT NULL DEFAULT '0',
`CategoryIDFrom` int(10) unsigned DEFAULT '0',
`CategoryIDTo` int(10) unsigned DEFAULT '0',
`ParentIDFrom` int(10) unsigned DEFAULT '0',
`ParentIDTo` int(10) unsigned DEFAULT '0',
`CustomerIDFrom` int(10) unsigned DEFAULT NULL,
`CustomerIDTo` int(10) unsigned DEFAULT NULL,
`TypeFrom` enum('internal','contact','system','software') NOT NULL DEFAULT 'internal',
`TypeTo` enum('internal','contact','system','software') NOT NULL DEFAULT 'internal',
`SubjectFrom` varchar(255) DEFAULT NULL,
`SubjectTo` varchar(255) DEFAULT NULL,
`StartDateFrom` date DEFAULT NULL,
`StartDateTo` date DEFAULT NULL,
`DueDateFrom` date DEFAULT NULL,
`DueDateTo` date DEFAULT NULL,
`StatusFrom` enum('new','active','pending','closed') NOT NULL DEFAULT 'new',
`StatusTo` enum('new','active','pending','closed') NOT NULL DEFAULT 'new',
`PriorityFrom` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
`PriorityTo` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
`ProjectIDFrom` int(10) unsigned NOT NULL,
`ProjectIDTo` int(10) unsigned NOT NULL,
`ReleaseIDFrom` int(10) unsigned NOT NULL DEFAULT '0',
`ReleaseIDTo` int(10) unsigned NOT NULL DEFAULT '0',
`SeverityFrom` enum('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
`SeverityTo` enum('wish list','feature','change','performance','minor bug','major bug','crash') NOT NULL DEFAULT 'feature',
`ProjectionFrom` enum('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
`ProjectionTo` enum('unknown','very minor','minor','average','major','very major','redesign') NOT NULL DEFAULT 'unknown',
`DevStatusFrom` enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','fixed') NOT NULL DEFAULT 'review',
`DevStatusTo` enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','fixed') NOT NULL DEFAULT 'review',
`QualityStatusFrom` enum('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
`QualityStatusTo` enum('failed','passed','untested','retest','in progress','pending developer response') NOT NULL DEFAULT 'untested',
`DescriptionChanged` tinyint(1) DEFAULT NULL,
`StepsToReproduceChanged` tinyint(1) DEFAULT NULL,
`Note` text,
PRIMARY KEY (`HistoryID`),
KEY `Index_Content` (`ContentID`)";

  /**
   * The columns for this item's tables
   *
   * @var array
   */
  protected static $hColumns =
  [

    'HistoryID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Primary',
      'Default' => 0,
      'Extra' => 'auto_increment'
    ],
    'ContentID' =>
    [
      'Type' => 'int(10) unsigned',
      'Key' => 'Multi',
      'Default' => 0
    ],
    'CarbonCopied' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'CarbonCopyFailed' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'UserIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'UserIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'CategoryIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'CategoryIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ParentIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ParentIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'CustomerIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'CustomerIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'TypeFrom' =>
    [
      'Type' => "enum('internal','contact','system','software')",
      'Default' => 'internal'
    ],
    'TypeTo' =>
    [
      'Type' => "enum('internal','contact','system','software')",
      'Default' => 'internal'
    ],
    'SubjectFrom' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'SubjectTo' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'StartDateFrom' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'StartDateTo' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'DueDateFrom' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'DueDateTo' =>
    [
      'Type' => 'date',
      'Default' => ''
    ],
    'StatusFrom' =>
    [
      'Type' => "enum('new','active','pending','closed')",
      'Default' => 'new'
    ],
    'StatusTo' =>
    [
      'Type' => "enum('new','active','pending','closed')",
      'Default' => 'new'
    ],
    'PriorityFrom' =>
    [
      'Type' => "enum('low','normal','high','critical')",
      'Default' => 'normal'
    ],
    'PriorityTo' =>
    [
      'Type' => "enum('low','normal','high','critical')",
      'Default' => 'normal'
    ],
    'ProjectIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ProjectIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ReleaseIDFrom' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'ReleaseIDTo' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => 0
    ],
    'SeverityFrom' =>
    [
      'Type' => "enum('wish list','feature','change','performance','minor bug','major bug','crash')",
      'Default' => 'feature'
    ],
    'SeverityTo' =>
    [
      'Type' => "enum('wish list','feature','change','performance','minor bug','major bug','crash')",
      'Default' => 'feature'
    ],
    'ProjectionFrom' =>
    [
      'Type' => "enum('unknown','very minor','minor','average','major','very major','redesign')",
      'Default' => 'unknown'
    ],
    'ProjectionTo' =>
    [
      'Type' => "enum('unknown','very minor','minor','average','major','very major','redesign')",
      'Default' => 'unknown'
    ],
    'DevStatusFrom' =>
    [
      'Type' => "enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','fixed')",
      'Default' => 'review'
    ],
    'DevStatusTo' =>
    [
      'Type' => "enum('review','verified','unable to reproduce','not fixable','duplicate','no change required','won''t fix','in progress','fixed')",
      'Default' => 'review'
    ],
    'QualityStatusFrom' =>
    [
      'Type' => "enum('failed','passed','untested','retest','in progress','pending developer response')",
      'Default' => 'untested'
    ],
    'QualityStatusTo' =>
    [
      'Type' => "enum('failed','passed','untested','retest','in progress','pending developer response')",
      'Default' => 'untested'
    ],
    'DescriptionChanged' =>
    [
      'Type' => 'tinyint(1)',
      'Default' => 0
    ],
    'StepsToReproduceChanged' =>
    [
      'Type' => 'tinyint(1)',
      'Default' => 0
    ],
    'Note' =>
    [
      'Type' => 'text',
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
    'historyid' => 'HistoryID',
    'id' => 'HistoryID',
    'contentid' => 'ContentID',
    'carboncopied' => 'CarbonCopied',
    'carboncopyfailed' => 'CarbonCopyFailed',
    'useridfrom' => 'UserIDFrom',
    'useridto' => 'UserIDTo',
    'categoryidfrom' => 'CategoryIDFrom',
    'categoryidto' => 'CategoryIDTo',
    'parentidfrom' => 'ParentIDFrom',
    'parentidto' => 'ParentIDTo',
    'customeridfrom' => 'CustomerIDFrom',
    'customeridto' => 'CustomerIDTo',
    'typefrom' => 'TypeFrom',
    'typeto' => 'TypeTo',
    'subjectfrom' => 'SubjectFrom',
    'subjectto' => 'SubjectTo',
    'startdatefrom' => 'StartDateFrom',
    'startdateto' => 'StartDateTo',
    'duedatefrom' => 'DueDateFrom',
    'duedateto' => 'DueDateTo',
    'statusfrom' => 'StatusFrom',
    'statusto' => 'StatusTo',
    'priorityfrom' => 'PriorityFrom',
    'priorityto' => 'PriorityTo',
    'projectidfrom' => 'ProjectIDFrom',
    'projectidto' => 'ProjectIDTo',
    'releaseidfrom' => 'ReleaseIDFrom',
    'releaseidto' => 'ReleaseIDTo',
    'severityfrom' => 'SeverityFrom',
    'severityto' => 'SeverityTo',
    'projectionfrom' => 'ProjectionFrom',
    'projectionto' => 'ProjectionTo',
    'devstatusfrom' => 'DevStatusFrom',
    'devstatusto' => 'DevStatusTo',
    'qualitystatusfrom' => 'QualityStatusFrom',
    'qualitystatusto' => 'QualityStatusTo',
    'descriptionchanged' => 'DescriptionChanged',
    'stepstoreproducechanged' => 'StepsToReproduceChanged',
    'note' => 'Note'
  ];

  /**
   * The default data used for "blank" or "empty" items
   *
   * @var array
   */
  protected static $hDefaultData =
  [
    'HistoryID' => 0,
    'ContentID' => 0,
    'CarbonCopied' => '',
    'CarbonCopyFailed' => '',
    'UserIDFrom' => 0,
    'UserIDTo' => 0,
    'CategoryIDFrom' => 0,
    'CategoryIDTo' => 0,
    'ParentIDFrom' => 0,
    'ParentIDTo' => 0,
    'CustomerIDFrom' => 0,
    'CustomerIDTo' => 0,
    'TypeFrom' => 'internal',
    'TypeTo' => 'internal',
    'SubjectFrom' => '',
    'SubjectTo' => '',
    'StartDateFrom' => '',
    'StartDateTo' => '',
    'DueDateFrom' => '',
    'DueDateTo' => '',
    'StatusFrom' => 'new',
    'StatusTo' => 'new',
    'PriorityFrom' => 'normal',
    'PriorityTo' => 'normal',
    'ProjectIDFrom' => 0,
    'ProjectIDTo' => 0,
    'ReleaseIDFrom' => 0,
    'ReleaseIDTo' => 0,
    'SeverityFrom' => 'feature',
    'SeverityTo' => 'feature',
    'ProjectionFrom' => 'unknown',
    'ProjectionTo' => 'unknown',
    'DevStatusFrom' => 'review',
    'DevStatusTo' => 'review',
    'QualityStatusFrom' => 'untested',
    'QualityStatusTo' => 'untested',
    'DescriptionChanged' => 0,
    'StepsToReproduceChanged' => 0,
    'Note' => ''
  ];

  /**
   * This object's data
   *
   * @var array
   */
  protected $hData =
  [
    'HistoryID' => 0,
    'ContentID' => 0,
    'CarbonCopied' => '',
    'CarbonCopyFailed' => '',
    'UserIDFrom' => 0,
    'UserIDTo' => 0,
    'CategoryIDFrom' => 0,
    'CategoryIDTo' => 0,
    'ParentIDFrom' => 0,
    'ParentIDTo' => 0,
    'CustomerIDFrom' => 0,
    'CustomerIDTo' => 0,
    'TypeFrom' => 'internal',
    'TypeTo' => 'internal',
    'SubjectFrom' => '',
    'SubjectTo' => '',
    'StartDateFrom' => '',
    'StartDateTo' => '',
    'DueDateFrom' => '',
    'DueDateTo' => '',
    'StatusFrom' => 'new',
    'StatusTo' => 'new',
    'PriorityFrom' => 'normal',
    'PriorityTo' => 'normal',
    'ProjectIDFrom' => 0,
    'ProjectIDTo' => 0,
    'ReleaseIDFrom' => 0,
    'ReleaseIDTo' => 0,
    'SeverityFrom' => 'feature',
    'SeverityTo' => 'feature',
    'ProjectionFrom' => 'unknown',
    'ProjectionTo' => 'unknown',
    'DevStatusFrom' => 'review',
    'DevStatusTo' => 'review',
    'QualityStatusFrom' => 'untested',
    'QualityStatusTo' => 'untested',
    'DescriptionChanged' => 0,
    'StepsToReproduceChanged' => 0,
    'Note' => ''
  ];

  /**
   * List of columns that shouldn't be updated after the data has been created
   *
   * @var array
   */
  protected $aNoUpdate = ['HistoryID'];

  /**
   * The table that this object is referencing
   *
   * @var string
   */
  protected $sTable = 'TicketHistory';

  /**
   * The name of the "ID" column associated with this object's table
   *
   * @var string
   */
  protected $sIdColumn = 'HistoryID';
}