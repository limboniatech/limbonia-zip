<?php
namespace Omniverse\Item;

/**
 * Omniverse Ticket Content Item Class
 *
 * Item based wrapper around the TicketContent table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class TicketContent extends \Omniverse\Item
{
  /**
   * List of this content's history data
   *
   * @var array
   */
  protected $aHistory = null;

  /**
   * List of columns that history objects contain
   *
   * @var array
   */
  protected static $aHistoryColumns = null;

  /**
   * Either create or update this object depending on if it's already been created or not
   *
   * @return integer The ID of this content object on success or false on failure
   */
  public function save()
  {
    $iSave = parent::save();

    if (empty($iSave))
    {
      return false;
    }

    if (!is_null($this->aHistory))
    {
      $this->getDB()->exec('DELETE FROM TicketHistory WHERE ContentID = ' . $this->id);

      foreach ($this->aHistory as $hHistory)
      {
        $hHistory['ContentID'] = $this->id;
        $this->getDB()->insert('TicketHistory', $hHistory);
      }
    }

    return $iSave;
  }

  /**
   * Remove any null data from the given history data
   *
   * @param array $hData
   * @return boolean
   */
  protected function cleanHistory($hData)
  {
    if (is_null(self::$aHistoryColumns))
    {
      $oHistory = parent::factory('TicketHistory', $this->getDB());
      self::$aHistoryColumns = $oHistory->columnList;
    }

    if (!is_array($hData))
    {
      return false;
    }

    $aKey = array_keys($hData);
    $aDiff = array_diff($aKey, self::$aHistoryColumns);

    foreach ($aDiff as $sColumn)
    {
      unset($hData[$sColumn]);
    }

    foreach ($hData as $sColumn => $sValue)
    {
      if (is_null($sValue))
      {
        unset($hData[$sColumn]);
      }
    }

    return count($hData) == 0 ? false : $hData;
  }

  /**
   * Get and return the existing history for the current ticket content
   *
   * @return array
   */
  function getHistory()
  {
    if (count($this->aHistory) == 0)
    {
      $oResult = $this->getDB()->query('SELECT * FROM TicketHistory WHERE ContentID = ' . $this->id);
      $aHistory = $oResult->fetchAll();
      $this->setHistory($aHistory);
    }

    return $this->aHistory;
  }

  /**
   * Clean the specified history data and then set that result as the history for this ticket content
   *
   * @param array $aHistory
   * @return boolean
   */
  function setHistory($aHistory)
  {
    if (!is_array($aHistory))
    {
      return false;
    }

    foreach ($aHistory as $iKey => $hData)
    {
      $hClean = $this->cleanHistory($hData);

      if (empty($hClean))
      {
        unset($aHistory[$iKey]);
      }
      else
      {
        $aHistory[$iKey] = parent::fromArray('TicketHistory', $hClean, $this->getDB())->getAll();
      }
    }

    $this->aHistory = $aHistory;
    return true;
  }

  /**
   * Add the specified data to this content's history
   *
   * @param array $hNew
   * @return boolean
   */
  function addHistory($hNew)
  {
    $aHistory = $this->getHistory();

    if (empty($aHistory))
    {
      $aHistory = [];
    }

    $hClean = $this->cleanHistory($hNew);
    $this->removeHistory($hClean);
    $aHistory[] = $hClean;
    return $this->setHistory($aHistory);
  }

  /**
   * Remove the specified data from the history of this ticket content
   *
   * @param array $hNew
   * @return boolean
   */
  function removeHistory($hNew)
  {
    if (!is_array($hNew))
    {
      return true;
    }

    $aHistory = $this->getHistory();

    if (empty($aHistory))
    {
      return true;
    }

    if (isset($hNew['Note']))
    {
      unset($hNew['Note']);
    }

    $aNewKeys = array_keys($hNew);

    foreach ($aHistory as $iKey => $hData)
    {
      unset($hData['note']);
      $aKeys = array_keys($hData);

      //if there is any intersection then this is the row we want to remove
      if (count(array_intersect($aKeys, $aNewKeys)) > 0)
      {
        unset($aHistory[$iKey]);
        break;
      }
    }

    return $this->setHistory($aHistory);
  }
}