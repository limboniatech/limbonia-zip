<?php
namespace Omniverse\Item;

/**
 * Omniverse Resource Item Class
 *
 * Item based wrapper around the ResourceLock table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Resource extends \Omniverse\Item
{
  /**
   * The name of the table to wrap the item around
   *
   * @var string
   */
  protected $sTable = 'ResourceLock';

  /**
   * The name of the "ID" field
   *
   * @var string
   */
  protected $sIdColumn = 'KeyID';
}