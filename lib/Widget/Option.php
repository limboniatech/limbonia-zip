<?php
namespace Limbonia\Widget;

/**
 * Limbonia Option Widget
 *
 * A wrapper around an HTML option tag
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Option extends \Limbonia\Widget
{
  /**
   * Return the HTML representation of this tag
   *
   * @return string
   */
  public function toString()
  {
    $sContent = $this->getContent();
    $sParam = $this->getParam();
    return "<option$sParam>$sContent</option>\n";
  }
}
