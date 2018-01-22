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
class DBResult extends \PDOStatement implements \Omniverse\Interfaces\Result, \ArrayAccess, \Countable
{
  use \Omniverse\Traits\Result
  {
    \Omniverse\Traits\Result::offsetExists as originalOffsetExists;
    \Omniverse\Traits\Result::offsetGet as originaloffsetGet;
    \Omniverse\Traits\Result::seek as originalSeek;
    \Omniverse\Traits\Result::next as originalNext;
    \Omniverse\Traits\Result::valid as originalValid;
  }

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
  public function getData()
  {
    if (\is_null($this->aData))
    {
      $this->setData($this->fetchAll());
    }

    return $this->aData;
  }

  /**
   * Return the parent database object
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
      $this->getData();
    }

    return $this->iRowCount;
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
    $this->getData();
    return $this->originalOffsetExists($xOffset);
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
    $this->getData();
    return $this->originalOffsetGet($xOffset);
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
    $this->getData();
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
    $this->getData();
    $this->originalSeek($iRow);
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
    $this->getData();
    return $this->originalNext();
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
    $this->getData();
    return $this->originalValid();
  }
}