<?php
namespace Omniverse\Widget;

/**
 * Omniverse Calendar Widget
 *
 * A wrapper around a calendar window
 *
 * @todo This class needs to be reworked to be more modern and further DocBlocks will wait until *after* the rewrite
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Calendar extends \Omniverse\Widget\Window
{
  protected $iWidth = 200;
  protected $iHeight = 195;
  protected $iTop = 200;
  protected $iLeft = 200;
  protected $sStartDate = '';
  protected $iFieldSize = 25;
  protected $bActive = TRUE;
  protected $bClickable = FALSE;
  protected $bReadOnly = FALSE;

  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->setConfig('Target', $this->sID);
    $this->setURL($this->sWebShareDir . "/calendar.php?Date='+" . $this->sID . "Target.value+'");
  }

  protected function init()
  {
    $sReadOnly = $this->bReadOnly ? ' readonly' : NULL;
    $sOnClick = $this->bClickable ? $this->sOnClick : NULL;
    $sDisabled = $this->bActive ? NULL : " disabled";
    $this->sPreScript .= "<input type=\"text\" name=\"$this->sName\" id=\"$this->sID\" value=\"$this->sStartDate\" size=\"$this->iFieldSize\"$sReadOnly$sDisabled$sOnClick>\n";
    return parent::init();
  }

  public function setClickable($bClickable=TRUE)
  {
    $this->bClickable = $bClickable;
  }

  public function setReadOnly($bReadOnly=TRUE)
  {
    $this->bReadOnly = $bReadOnly;
  }

  public function setFieldSize($iSize)
  {
    if (!empty($iSize) && is_int($iSize) && $iSize != $this->iFieldSize)
    {
      $this->iFieldSize = $iSize;
    }
  }

  public function setName($sName)
  {
    if (!empty($sName))
    {
      $this->Name = $sName;
    }
  }

  public function setStartDate($xDate='now')
  {
    $xDate = trim($xDate);

    if (empty($xDate) || in_array($xDate, ['0000-00-00 00:00:00', '0000-00-00']))
    {
      $this->sStartDate = '';
      return true;
    }

    if ($xDate == 'CURRENT_TIMESTAMP')
    {
      $xDate = 'now';
    }

    if (is_string($xDate))
    {
      $iStartDate = strtotime($xDate);
    }
    elseif (is_numeric($xDate))
    {
      $iStartDate = $xDate;
    }

    $sDateFormat = empty($this->hConfig['dateformat']) ? "%F" : $this->hConfig['dateformat'];
    $this->sStartDate = strftime($sDateFormat, $iStartDate);
  }

  public function setStartDay($iDay=0)
  {
    if (is_int($iDay))
    {
      $this->hConfig['startday'] = $iDay % 7;
    }
  }

  public function setDateFormat($sDateFormat)
  {
    if (is_string($sDateFormat))
    {
      $this->hConfig['dateformat'] = $sDateFormat;
    }
  }

  public function setBorderColor($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('BorderColor', $sColor);
    }
  }

  public function setHeaderBackground($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('HeaderBackground', $sColor);
    }
  }

  public function setHeaderColor($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('HeaderColor', $sColor);
    }
  }

  public function setWeekBackground($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('WeekBackground', $sColor);
    }
  }

  public function setWeekColor($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('WeekColor', $sColor);
    }
  }

  public function setInMonthColor($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('InMonth_DayColor', $sColor);
    }
  }

  public function setOutMonthColor($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('OutMonth_DayColor', $sColor);
    }
  }

  public function setWeekendBackground($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('WeekendBackground', $sColor);
    }
  }

  public function setCurrentDayBackground($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('CurrentDayBackground', $sColor);
    }
  }

  public function setDefaultDayBackground($sColor)
  {
    if (is_string($sColor))
    {
      $this->setConfig('DefaultDayBackground', $sColor);
    }
  }

  public function disable()
  {
    $this->bActive = FALSE;
  }
}
