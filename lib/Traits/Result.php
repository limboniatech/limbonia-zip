<?php
namespace Limbonia\Traits;

/**
 * Limbonia Result Trait
 *
 * This trait is a basic implementation of the \Limbonia\Interfaces\Result interface
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
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
   * The fields that are in the current data
   *
   * @var array
   */
  protected $aFields = null;

  /**
   * Return all the fields this result set uses
   *
   * @return array
   */
  public function getFields()
  {
    if (is_null($this->aFields))
    {
      $this->aFields = [];
      $hRow = $this->offsetGet(0);

      //if the 0 offset doesn't exist
      if (is_array($hRow))
      {
        $this->aFields = array_keys($hRow);
      }
    }

    return $this->aFields;
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
    return $this->offsetGet($xOffset) === false ? false : true;
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
    if (!$this->offsetExists($iRow))
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
   * @return mixed
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
    return $this->offsetExists($this->iCurrentRow);
  }
}
