<?php
/**
* Omnisys Table Row Class
*
* This is a light wrapper an HTML table and some javascript for sorting
*
* @author Lonnie Blansett <lonnie@omniverserpg.com>
* @version $Revision: 1.4 $
* @package OmniLib
*/
namespace Omniverse\Lib\Tag;
use Omniverse\Lib\Tag;

class TableRow extends Tag
{
  public function __construct()
  {
    parent::__construct('tr');
  }

  public function toString()
  {
    $sParamList = $this->getParam();
    $sRow  = "  <$this->sType$sParamList>\n";

    foreach ($this->aContent as $oCell)
    {
      $sRow .= $oCell->toString() . "\n";
    }

    $sRow .= "  </$this->sType>\n";
    return $sRow;
  }
}