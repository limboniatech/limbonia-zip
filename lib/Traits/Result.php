<?php
namespace Omniverse\Traits;

/**
 * Omniverse Result Trait
 *
 * This trait is a basic implementation of the \Omniverse\Interfaces\Result interface
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait Result
{
  /**
   * The current row of the result set
   *
   * @var integer
   */
  protected $iCurrentRow = 0;

  /**
   * The number of results in the set
   *
   * @var integer
   */
  protected $iRowCount = false;

  /**
   * An array of the data from the results
   *
   * @var array
   */
  protected $aData = null;

  /**
   * Fill the data array with the specified data
   *
   * @param array $aData
   */
  protected function setData(array $aData = [])
  {
    $this->aData = $aData;
    $this->iRowCount = \count($this->aData);
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
   * Set the specified array offset with the specified value
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @param mixed $xValue
   */
  public function offsetSet($xOffset, $xValue)
  {
    //nothing happens because you are not allowed to change the data here,
    //this function exists only to satisfy the interface...
  }

  /**
   * Unset the specified array offset
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   */
  public function offsetUnset($xOffset)
  {
    //nothing happens because you are not allowed to change the data here,
    //this function exists only to satisfy the interface...
  }

  /**
   * Does the specified array offset exist?
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @return boolean
   */
  public function offsetExists($xOffset)
  {
    return \is_array($this->aData) && isset($this->aData[$xOffset]);
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

  /**
   * Return the number of columns represented by this object
   *
   * @note This is an implementation detail of the Countable Interface
   *
   * @return integer
   */
  public function count()
  {
    return $this->iRowCount;
  }

  /**
   * Move the value to the data represented by the specified key
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @param mixed $iRow
   * @throws OutOfBoundsException
   */
  public function seek($iRow)
  {
    if (!isset($this->aData[$iRow]))
    {
      throw new OutOfBoundsException("Invalid seek position ($iRow)");
    }

    $this->iCurrentRow = $iRow;
  }

  /**
   * Return the current row of data from the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return type
   */
  public function current()
  {
    return $this->offsetGet($this->iCurrentRow);
  }

  /**
   * Return the current internal index of the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return integer
   */
  public function key()
  {
    return $this->iCurrentRow;
  }

  /**
   * Return the next row of data from the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return array
   */
  public function next()
  {
    $this->iCurrentRow++;
    return $this->current();
  }

  /**
   * Rewind the internal index of the result set back to the begining
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   */
  public function rewind()
  {
    $this->iCurrentRow = 0;
  }

  /**
   * Is the row of data at the current internal index valid?
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return type
   */
  public function valid()
  {
    return isset($this->aData[$this->iCurrentRow]);
  }
}
