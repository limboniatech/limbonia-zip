<?php
namespace Omniverse\Tag;

/**
 * Omniverse Table Cell Class
 *
 * This is a light wrapper around an HTML table cell
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class TableCell extends \Omniverse\Tag
{
  /**
   * Constructor
   *
   * Construct a "td" tag
   */
  public function __construct()
  {
    parent::__construct('td');
  }

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