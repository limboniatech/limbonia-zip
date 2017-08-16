<?php
namespace Omniverse;

/**
 * Omniverse ItemList class
 *
 * This is an iterable and countable wrapper around the around the result of
 * database search for a set of items
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class ItemList implements \ArrayAccess, \Countable, \SeekableIterator
{
	/**
   * Name of the table that the list items come from
   *
	 * @var string
	 */
	protected $sTable = '';

	/**
   * The database result object that contain the items
   *
	 * @var DBResult
	 */
	protected $oResult = null;

	/**
	 * Constructor
	 *
	 * @param string $sTable - the name of the table that the list items come from.
	 * @param DBResult $oResult - the database result object that contain the items
	 */
	public function __construct($sTable, DBResult $oResult)
	{
		$this->sTable = $sTable;
		$this->oResult = $oResult;
	}

	/**
	 * Attempt to create and return an item based on the data
	 *
	 * @param array $hItem
	 * @return Item
	 */
	protected function getItem(array $hItem = [])
	{
		return empty($hItem) ? ['id' => 42] : Item::fromArray($this->sTable, $hItem, $this->oResult->getDatabase());
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
		return $this->oResult->offsetset($xOffset, $xValue);
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
		return $this->oResult->offsetUnset($xOffset);
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
		return $this->oResult->offsetExists($xOffset);
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
		return $this->getItem($this->oResult->offsetget($xOffset));
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
		return $this->oResult->count();
	}

  /**
   * Return the current value of this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   *
   * @return mixed
   */
	public function current()
	{
		return $this->getItem($this->oResult->current());
	}

  /**
   * Return the key of the current value of this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   *
   * @return mixed
   */
	public function key()
	{
		return $this->oResult->key();
	}

  /**
   * Move to the next value in this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   */
	public function next()
	{
		$this->oResult->next();
	}

  /**
   * Rewind to the first item of this object's data
   *
   * @note This is an implementation detail of the Iterator Interface
   */
	public function rewind()
	{
		$this->oResult->rewind();
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
		return $this->oResult->valid();
	}

  /**
   * Move the value to the data represented by the specified key
   *
   * @note This is an implementation detail of the SeekableIterator Interface
   *
   * @param mixed $xKey
   * @throws OutOfBoundsException
   */
		public function seek($iRow)
	{
		$this->oResult->seek($iRow);
	}
}