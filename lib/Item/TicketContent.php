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
  protected $aHistory = null;
  protected static $aHistoryColumns = null;

  public function save()
  {
    if (!$iSave = parent::save())
    {
      return false;
    }

    if (!is_null($this->aHistory))
    {
      $this->getDB()->exec('DELETE FROM TicketHistory WHERE ContentID = ?' . $this->ID);

      foreach ($this->aHistory as $hHistory)
      {
        $hHistory['ContentID'] = $this->ID;
        $this->getDB()->insert('TicketHistory', $hHistory);
      }
    }

    return $iSave;
  }

  protected function cleanHistory($hData)
  {
    if (is_null(self::$aHistoryColumns))
    {
      $oHistory = parent::factory('tickethistory', $this->getDB());
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

  function getHistory()
  {
    if (count($this->aHistory) == 0)
    {
      $oResult = $this->getDB()->query('SELECT * FROM TicketHistory WHERE ContentID = ' . $this->ID);
      $aHistory = $oResult->fetchAll();
      $this->setHistory($aHistory);
    }

    return $this->aHistory;
  }

  function setHistory($aHistory)
  {
    if (!is_array($aHistory))
    {
      return false;
    }

    foreach ($aHistory as $iKey => $hData)
    {
      if ($hClean = $this->cleanHistory($hData))
      {
        $aHistory[$iKey] = $hClean;
      }
      else
      {
        unset($aHistory[$iKey]);
      }
    }

    $this->aHistory = $aHistory;
    return true;
  }

  function addHistory($hNew)
  {
    $aHistory = $this->getHistory();

    if (empty($aHistory))
    {
      $aHistory = array();
    }

    $hNew = $this->cleanHistory($hNew);
    $this->removeHistory($hNew);
    $aHistory[] = $hNew;
    return $this->setHistory($aHistory);
  }

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