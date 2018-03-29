<?php
namespace Limbonia\Interfaces;

/**
 * Limbonia Result Interface
 *
 * This interface is a combination of both the ArrayAccess and Countable interfaces.
 * It allows the user to interact with a given result set in a consistent way array-like way.

 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
interface Result
{
  /**
   * Return an array of all the fields in this dataset
   *
   * @return array on success or null on failure
   */
  public function getFields();

  /**
   * Return an array of all the data in the result set
   *
   * @return array on success or null on failure
   */
  public function getData();

  /**
   * Set the specified array offset with the specified value
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @param mixed $xValue
   */
  public function offsetSet($xOffset, $xValue);

  /**
   * Unset the specified array offset
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   */
  public function offsetUnset($xOffset);

  /**
   * Does the specified array offset exist?
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @return boolean
   */
  public function offsetExists($xOffset);

  /**
   * Return the value stored at the specified array offset
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @return mixed
   */
  public function offsetGet($xOffset);

  /**
   * Return the number of columns represented by this object
   *
   * @note This is an implementation detail of the Countable Interface
   *
   * @return integer
   */
  public function count();

  /**
   * Move the value to the data represented by the specified key
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @param mixed $iRow
   * @throws OutOfBoundsException
   */
  public function seek($iRow);

  /**
   * Return the current row of data from the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return type
   */
  public function current();

  /**
   * Return the current internal index of the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return integer
   */
  public function key();

  /**
   * Return the next row of data from the result set
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return array
   */
  public function next();

  /**
   * Rewind the internal index of the result set back to the begining
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   */
  public function rewind();

  /**
   * Is the row of data at the current internal index valid?
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @return type
   */
  public function valid();
}
