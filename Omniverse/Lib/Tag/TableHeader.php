<?php
/**
* Omnisys Table Header Class
*
* This is a light wrapper an HTML table and some javascript for sorting
*
* @author Lonnie Blansett <lonnie@omniverserpg.com>
* @version $Revision: 1.4 $
* @package OmniLib
*/
namespace Omniverse\Lib\Tag;

class Omnisys_Tag_TableHeader extends TableCell
{
  public function __construct()
  {
    Omnisys_Tag::__construct('th');
  }
}