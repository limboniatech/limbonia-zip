<?php
namespace Omniverse\Module;

/**
 * Omniverse Resource Module class
 *
 * Admin module for handling site resource keys
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class ResourceKey extends \Omniverse\Module
{
  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems =
  [
    'list' => 'List',
    'create' => 'Create'
  ];
}