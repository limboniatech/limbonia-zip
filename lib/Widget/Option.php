<?php
namespace Omniverse\Widget;

/**
 * Omniverse Option Widget
 *
 * A wrapper around an HTML option tag
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Option extends \Omniverse\Tag
{
  /**
   * Return the HTML representation of this tag
   *
   * @return string
   */
  protected function toString()
  {
    $sContent = $this->getContent();
    $sParam = $this->getParam();
    return "<option$sParam>$sContent</option>\n";
  }
}
