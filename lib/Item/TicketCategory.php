<?php
namespace Limbonia\Item;

/**
 * Limbonia Ticket Category Item Class
 *
 * Item based wrapper around the TicketCategory table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TicketCategory extends \Limbonia\Item
{
  /**
   * List of names and their associated types, used by __get to generate item objects
   *
   * @var array
   */
  protected $hAutoExpand =
  [
    'key' => 'ResourceKey'
  ];
}