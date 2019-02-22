<?php
namespace Limbonia\Tag;

/**
 * Limbonia Table Cell Class
 *
 * This is a light wrapper around an HTML table cell
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TableCell extends \Limbonia\Tag
{
  /**
   * Name of the basic HTML tag represented by the widget
   *
   * @var string $sType
   */
  protected $sType = 'td';


  /**
   * Return the HTML representation of this tag
   *
   * @return string
   */
  protected function toString()
  {
    $sParamList = $this->getParam();
    $sRow  = "    <$this->sType$sParamList>";

    foreach ($this->aContent as $sContent)
    {
      $sRow .= trim($sContent);
    }

    return $sRow . "</$this->sType>";
  }
}