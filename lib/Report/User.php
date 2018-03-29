<?php
namespace Limbonia\Report;

class User extends \Limbonia\Report
{
  use \Limbonia\Traits\ItemReport;

  protected static $hOptions =
  [
    'type' =>
    [
      'Type' => "enum('internal', 'contact', 'system')",
      'Default' => 'internal'
    ],
    'active' =>
    [
      'Type' => 'hash',
      'Extra' => [0 => 'Not Active', 1 => 'Active'],
      'Default' => 1
    ],
    'visible' =>
    [
      'Type' => 'hash',
      'Extra' => [0 => 'Not Visible', 1 => 'Visible'],
      'Default' => 0
    ]
  ];

  protected $hHeaders =
  [
    'userid' => 'ID',
    'type' => 'Type',
    'email' => 'Email',
    'firstname' => 'First Name',
    'lastname' => 'Last Name',
    'position' => 'Position',
    'notes' => 'Notes',
    'streetaddress' => 'Street Address',
    'shippingaddress' => 'Shipping Address',
    'city' => 'City',
    'state' => 'State',
    'zip' => 'Zip',
    'country' => 'Country',
    'workphone' => 'Work Phone',
    'homephone' => 'Home Phone',
    'cellphone' => 'Cell Phone',
    'active' => 'Active',
    'visible' => 'Visible'
  ];
}
