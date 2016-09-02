<?php
namespace Omniverse\Lib;

class DBResult extends \PDOStatement implements \ArrayAccess, \Countable
{
  /**
   *
   * @var integer
   */
  protected $iCurrentRow = 0;

  /**
   *
   * @var integer
   */
  protected $iRowCount = null;

  /**
   *
   * @var array
   */
  protected $aData = false;

  /**
   *
   *
   * @var \Omniverse\Lib\Database
   */
  protected $oDatabase = null;

  protected function __construct(Database $oDatabase)
  {
    $this->oDatabase = $oDatabase;
    $this->setFetchMode(PDO::FETCH_ASSOC);
  }

  /**
   *
   *
   * @return array or false
   */
  protected function getAllAssoc()
  {
    if (\is_null($this->aData))
    {
      $this->aData = $this->fetchAll();
    }

    return $this->aData;
  }

  public function getDatabase()
  {
    return $this->oDatabase;
  }

  public function fetchAssoc()
  {
    $hData = array();

    while ($hRow = $this->fetch())
    {
      $xFirst = array_shift($hRow);
      $iRemainingColumns = count($hRow);

      if ($iRemainingColumns == 0)
      {
        throw new Omnisys_Exception_Database(__METHOD__ . " at least 2 columns!", $this->sSQLType);
      }

      $hData[$xFirst] = $iRemainingColumns == 1 ? array_shift($hRow) : $hRow;
    }

    return $hData;
  }

  public function fetchOne()
  {
    //get the first (and hopefully only) row
    $hRow = $this->fetch();

    //if it's an array return the first item...
    return !is_array($hRow) ? null : array_shift($hRow);
  }

  public function getRow()
  {
    //return the first (and hopefully only) row
    return $this->fetch();
  }

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
}