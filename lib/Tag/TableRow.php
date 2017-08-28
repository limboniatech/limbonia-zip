<?php
namespace Omniverse\Tag;

/**
 * Omniverse Table Row Class
 *
 * This is a light wrapper around an HTML table row
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class TableRow extends \Omniverse\Tag
{
  /**
   * Constructor
   *
   * Construct a "tr" tag
   */
  public function __construct()
  {
    parent::__construct('tr');
  }

  /**
   * Return the HTML representation of this tag
   *
   * @return string
   */
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