<?php
/**
 * Omniverse Table Header Class
 *
 * This is a light wrapper around an HTML table header
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
namespace Omniverse\Tag;

class TableHeader extends \Omniverse\Tag
{
  public function __construct()
  {
    \Omniverse\Tag::__construct('th');
  }
}