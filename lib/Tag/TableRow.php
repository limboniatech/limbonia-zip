<?php
/**
 * Omniverse Table Row Class
 *
 * This is a light wrapper around an HTML table row
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
namespace Omniverse\Tag;

class TableRow extends \Omniverse\Tag
{
  public function __construct()
  {
    parent::__construct('tr');
  }

  protected function toString()
  {
    $sParamList = $this->getParam();
    $sRow  = "  <$this->sType$sParamList>\n";

    foreach ($this->aContent as $oCell)
    {
      $sRow .= $oCell . "\n";
    }

    return $sRow . "  </$this->sType>\n";
  }
}