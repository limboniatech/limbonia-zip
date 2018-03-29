<?php
namespace Limbonia\Traits;

/**
 * Limbonia Hash Trait
 *
 * This trait allows an inheriting class to read, access and save configuration
 * data to and from an INI file
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
trait Hash
{
  /**
   * The input data
   *
   * @var array
   */
  protected $hData = [];

  public function getRaw()
  {
    return $this->hData;
  }

  /**
   * Sets the specified values if possible
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
    $this->hData[\strtolower($sName)] = $xValue;
  }

  /**
   * Get the specified data
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    return $this->hData[\strtolower($sName)] ?? null;
  }

  /**
   * Determine if the specified value is set (exists) or not...
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    return isset($this->hData[\strtolower($sName)]);
  }

  /**
   * Unset the specified value
   *
   * @param string $sName
   */
  public function __unset($sName)
  {
    $sLowerName = \strtolower($sName);

    if (isset($this->hData[$sLowerName]))
    {
      unset($this->hData[$sLowerName]);
    }
  }

  /**
   * Set the specified array offset with the specified value
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @param mixed $xValue
   */
  public function offsetset($xOffset, $xValue)
  {
    $this->__set($xOffset, $xValue);
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
    $this->__unset($xOffset);
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
    return $this->__isset($xOffset);
  }

  /**
   * Return the value stored at the specified array offset
   *
   * @note This is an implementation detail of the ArrayAccess Interface
   *
   * @param mixed $xOffset
   * @return mixed
   */
  public function offsetget($xOffset)
  {
    return $this->__get($xOffset);
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
    return count($this->hData);
  }

  /**
   * Return the current value of this object's data array
   *
   * @note This is an implementation detail of the Iterator Interface
   *
   * @return mixed
   */
  public function current()
  {
    return $this->__get(key($this->hData));
  }

  /**
   * Return the key of the current value of this object's data array
   *
   * @note This is an implementation detail of the Iterator Interface
   *
   * @return mixed
   */
  public function key()
  {
    return key($this->hData);
  }

  /**
   * Move to the next value in this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   */
  public function next()
  {
    next($this->hData);
  }

  /**
   * Rewind to the first item of this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   */
  public function rewind()
  {
    reset($this->hData);
  }

  /**
   * Is the current value valid?
   *
   * @note This is an implementation detail of the Iterator Interface
   *
   * @return boolean
   */
  public function valid()
  {
    return $this->key() !== null;
  }

  /**
   * Move the value to the data represented by the specified key
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @param mixed $xKey
   * @throws \OutOfBoundsException
   */
  public function seek($xKey)
  {
    $this->rewind();

    while ($this->key() != $xKey)
    {
      $this->next();
    }

    if ($this->key() != $xKey)
    {
      throw new \OutOfBoundsException("Invalid seek position ($xKey)");
    }
  }
}
