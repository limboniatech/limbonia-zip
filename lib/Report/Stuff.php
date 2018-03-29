<?php
namespace Limbonia\Report;

class Stuff extends \Limbonia\Report
{
  protected $hHeaders =
  [
    'userid' => 'User ID',
    'first' => 'First Name',
    'last' => 'Last Name',
    'address' => 'Address',
    'city' => 'City',
    'state' => 'State',
    'zip' => 'Zip Code',
    'cell' => 'Cell Phone'
  ];

  protected $aData =
  [
    [
      'userid' => 1,
      'first' => 'Lonnie',
      'last' => 'Blansett',
      'address' => '1407 Golden Valley Dr',
      'city' => 'Bettendorf',
      'state' => 'IA',
      'zip' => '52722',
      'cell' => '563-940-8184'
    ],
    [
      'userid' => 2,
      'first' => 'Test',
      'last' => 'Person',
      'address' => '1234 56th St',
      'city' => 'Moline',
      'state' => 'IL',
      'zip' => '61265',
      'cell' => ''
    ],
    [
      'userid' => 7,
      'first' => 'John',
      'last' => 'Smith',
      'address' => 'Type 40',
      'city' => 'Gallifrey',
      'state' => 'IA',
      'zip' => '3.1415',
      'cell' => ''
    ],
    [
      'userid' => 42,
      'first' => 'Douglas',
      'last' => 'Adams',
      'address' => '456 7th Ave',
      'city' => 'London',
      'state' => 'UK',
      'zip' => '098765',
      'cell' => '21-654-765-8967'
    ],
  ];

  protected static $aOptions =
  [
    'Subject' =>
    [
      'Type' => 'varchar(255)',
      'Default' => ''
    ],
    'CategoryID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => ''
    ],
    'Type' =>
    [
      'Type' => "enum('internal','contact','system','software')",
      'Default' => 'internal'
    ],
    'CreatorID' =>
    [
      'Type' => 'int(10) unsigned',
      'Default' => ''
    ],
    'StartDate' =>
    [
      'Type' => 'date',
      'Default' => ''
    ]
  ];
}
