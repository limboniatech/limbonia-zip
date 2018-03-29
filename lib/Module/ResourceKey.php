<?php
namespace Limbonia\Module;

/**
 * Limbonia Resource Module class
 *
 * Admin module for handling site resource keys
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ResourceKey extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule;

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