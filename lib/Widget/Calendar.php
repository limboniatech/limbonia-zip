<?php
namespace Limbonia\Widget;

/**
 * Limbonia Calendar Widget
 *
 * A wrapper around a calendar window
 *
 * @todo This class needs to be reworked to be more modern and further DocBlocks will wait until *after* the rewrite
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Calendar extends \Limbonia\Widget\Window
{
  /**
   * The start date for this date field to display
   *
   * @var string
   */
  protected $sStartDate = '';

  /**
   * The width of the date field
   *
   * @var type
   */
  protected $iFieldSize = 25;

  /**
   * Is the date field currently active?
   *
   * @var type
   */
  protected $bActive = TRUE;

  /**
   * Is the date field clickable?
   *
   * @var boolean
   */
  protected $bClickable = FALSE;

  /**
   * Should the date field only be updated with results from the calendar popup?
   *
   * @var boolean
   */
  protected $bReadOnly = FALSE;

  /**
   * Constructor
   *
   * It increments the widget counter and generates a unique (but human readable) name.
   *
   * @param string $sName (optional)
   * @param \Limbonia\Controller $oController (optional)
   * @throws Limbonia\Exception\Object
   */
  public function __construct($sName = null, \Limbonia\Controller $oController = null)
  {
    $this->hWindowParam['top'] = 200;
    $this->hWindowParam['left'] = 200;
    $this->hWindowParam['height'] = 195;
    $this->hWindowParam['width'] = 200;
    parent::__construct($sName, $oController);
    $this->setConfig('Target', $this->sId);
    $this->setURL($this->sWebShareDir . "/calendar.php?Date='+" . $this->sId . "Target.value+'");
  }

  protected function init()
  {
    $sReadOnly = $this->bReadOnly ? ' readonly' : NULL;
    $sOnClick = $this->bClickable ? $this->sOnClick : NULL;
    $sDisabled = $this->bActive ? NULL : " disabled";
    $this->sPreScript .= "<input type=\"text\" name=\"$this->sName\" id=\"$this->sId\" value=\"$this->sStartDate\" size=\"$this->iFieldSize\"$sReadOnly$sDisabled$sOnClick>\n";
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
