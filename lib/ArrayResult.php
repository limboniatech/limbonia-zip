<?php
namespace Omniverse;

/**
 * Omniverse Database Result Class
 *
 * This is an extension to PHP's PDOStatement system for accessing database results
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class ArrayResult implements Interfaces\Result, \ArrayAccess, \Countable
{
  use \Omniverse\Traits\Result;

  /**
   * Generate and return a result object based on the specified array
   *
   * @param array $aData
   * @return \Omniverse\Result
   */
  public function __construct(array $aData = [])
  {
    $this->setData($aData);
  }
}
