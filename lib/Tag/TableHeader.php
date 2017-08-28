<?php
namespace Omniverse\Tag;

/**
 * Omniverse Table Header Class
 *
 * This is a light wrapper around an HTML table header
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */class TableHeader extends \Omniverse\Tag
{
  /**
   * Constructor
   *
   * Construct a "th" tag
   */
  public function __construct()
  {
    parent::__construct('th');
  }
}