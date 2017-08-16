<?php
/**
 * Omniverse Table Cell Class
 *
 * This is a light wrapper around an HTML table cell
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
namespace Omniverse\Tag;
use Omniverse\Tag;

class TableCell extends Tag
{
  public function __construct()
  {
    parent::__construct('td');
  }

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