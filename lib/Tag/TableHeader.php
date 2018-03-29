<?php
namespace Limbonia\Tag;

/**
 * Limbonia Table Header Class
 *
 * This is a light wrapper around an HTML table header
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */class TableHeader extends \Limbonia\Tag
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