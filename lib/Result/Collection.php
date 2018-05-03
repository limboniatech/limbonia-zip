<?php
namespace Limbonia\Result;

/**
 * Limbonia Database Result Class
 *
 * This is an extension to PHP's PDOStatement system for accessing database results
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Collection implements \Limbonia\Interfaces\Result, \ArrayAccess, \Countable, \Iterator
{
  use \Limbonia\Traits\Result;

  /**
   * An array of the data from the results
   *
   * @var array
   */
  protected $aData = [];

  /**
   * Generate and return a result object based on the specified array
   *
   * @param array $aData
   * @return \Limbonia\Result
   */
  public function __construct(array $aData = [])
  {
    $this->aData = array_values($aData);
    $this->iRowCount = \count($this->aData);

    //if the data is an array of arrays
    if (isset($this->aData[0]) && is_array($this->aData[0]))
    {
      //then the fields are the keys of the sub rows
      $this->aFields = array_keys($this->aData[0]);
    }
  }

  /**
   * Return an array of all the data in the result set
   *
   * @return array on success or false on failure
   */
  public function getData()
  {
    return $this->aData;
  }

  /**
   * Return the value stored at the specified array offset
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @return mixed
   */
  public function offsetGet($xOffset)
  {
    return \is_array($this->aData) && isset($this->aData[$xOffset]) ? $this->aData[$xOffset] : false;
  }
}
