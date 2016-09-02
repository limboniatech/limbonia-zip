<?php
/**
* Omnisys Table Class
*
* This is a light wrapper an HTML table and some javascript for sorting
*
* @author Lonnie Blansett <lonnie@omniverserpg.com>
* @version $Revision: 1.4 $
* @package OmniLib
*/
namespace Omniverse\Lib\Tag;
use Omniverse\Lib\Tag;

class TableCell extends Tag
{
  public function __construct()
  {
    parent::__construct('td');
  }

  public function toString()
  {
    $sParamList = $this->getParam();
    $sRow  = "    <$this->sType$sParamList>";

    foreach ($this->aContent as $sContent)
    {
      $sRow .= trim($sContent);
    }

    $sRow .= "</$this->sType>";
    return $sRow;
  }
}