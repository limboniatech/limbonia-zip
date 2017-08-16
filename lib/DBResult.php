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
class DBResult extends \PDOStatement implements \ArrayAccess, \Countable
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
   * The parent database object
   *
   * @var \Omniverse\Database
   */
  protected $oDatabase = null;

  /**
   * Instantiate the the result set
   *
   * @param \Omniverse\Database $oDatabase
   */
  protected function __construct(Database $oDatabase)
  {
    $this->oDatabase = $oDatabase;
    $this->setFetchMode(\PDO::FETCH_ASSOC);
  }

  /**
   * Return an array of all the data in the result set
   *
   * @return array on success or false on failure
   */
  protected function getAllAssoc()
  {
    if (\is_null($this->aData))
    {
      $this->aData = $this->fetchAll();
    }

    return $this->aData;
  }

  /**
   * Returnn the parent database object
   *
   * @return \Omniverse\Database
   */
  public function getDatabase()
  {
    return $this->oDatabase;
  }

  /**
   * Return the next row of data from the result set as an associative array
   *
   * @return array
   * @throws \Omniverse\Exception\Database
   */
  public function fetchAssoc()
  {
    $hData = [];

    while ($hRow = $this->fetch())
    {
      $xFirst = array_shift($hRow);
      $iRemainingColumns = count($hRow);

      if ($iRemainingColumns == 0)
      {
        throw new \Omniverse\Exception\Database(__METHOD__ . " at least 2 columns!", $this->sSQLType);
      }

      $hData[$xFirst] = $iRemainingColumns == 1 ? array_shift($hRow) : $hRow;
    }

    return $hData;
  }

  /**
   * Return the first (only) row of data in the result set
   *
   * @return array
   */
  public function fetchOne()
  {
    //get the first (and hopefully only) row
    $hRow = $this->fetch();

    //if it's an array return the first item...
    return is_array($hRow) ? array_shift($hRow) : null;
  }

  /**
   * Return the next row of data from the result set
   *
   * @return array
   */
  public function fetchRow()
  {
    return $this->fetch();
  }

  /**
   * Return the number of rows in the result set
   *
   * @return integer
   */
  public function rowCount()
  {
    if (!preg_match("/^select/i", $this->queryString))
    {
      return parent::rowCount();
    }

    if ($this->iRowCount === false)
    {
      //I'm not working with large data sets, so this should be fine for now...
      $this->getAllAssoc();
      $this->iRowCount = \count($this->aData);
    }

    return $this->iRowCount;
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
    $this->getAllAssoc();
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
    $this->getAllAssoc();
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
    return $this->rowCount();
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
    $this->getAllAssoc();

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
    $this->getAllAssoc();
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
    $this->getAllAssoc();
    return isset($this->aData[$this->iCurrentRow]);
  }
}