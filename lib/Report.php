<?php
namespace Limbonia;

/**
 * Limbonia Report Class
 *
 * This represents the base report type that all other reports will
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Report
{
  use \Limbonia\Traits\DriverList;
  use \Limbonia\Traits\HasController;

  /**
   * List of options that can be used to customize the run
   *
   * @var array
   */
  protected static $hOptions =
  [
    'start' =>
    [
      'Type' => 'date',
      'Default' => '1 month ago'
    ],
    'end' =>
    [
      'Type' => 'date',
      'Default' => 'yesterday'
    ]
  ];

  /**
   * The hash of header names and titles
   *
   * @var array
   */
  protected $hHeaders = [];

  /**
   * The current configuration data for this report
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * Specifies the sort order of the returned data
   *
   * @var array
   */
  protected $aSort = [];

  /**
   * Specifies the fields that should appear in the returned data
   *
   * @var array
   */
  protected $aFields = [];

  /**
   * Specifies what row number to start with in the returned data
   *
   * @var integer
   */
  protected $iOffset = 0;

  /**
   * Specifies how many rows should be in the returned data
   *
   * @var integer
   */
  protected $iLimit = 0;

  /**
   * Hash of fields and values used to narrow the returned data
   *
   * @var array
   */
  protected $hSearch = [];

  /**
   * The original data
   *
   * @var array
   */
  protected $aData = [];

  /**
   * The current report result
   *
   * @var \Limbonia\Interface\Result
   */
  protected $oResult = null;

  /**
   * Have the report parameters changed?
   *
   * @var bool
   */
  protected $bChanged = false;

  /**
   * Generate and return a Report object of the specified type
   *
   * @param string $sType The type of report to return
   * @param array $hParam (optional) List of report parameters to set before running the report
   * @param \Limbonia\Controller $oController (optional)
   * @return \Limbonia\Report
   * @throws \Limbonia\Exception\Object
   */
  public static function factory($sType, array $hParam = [], \Limbonia\Controller $oController = null): \Limbonia\Report
  {
    $oReport = self::driverFactory($sType, $oController);
    $oReport->setParam($hParam);
    return $oReport;
  }

  /**
   * Generate a report, run it then return the result
   *
   * @param string $sType The type of report to get a result from
   * @param array $hParam (optional) List of report parameters to set before running the report
   * @param \Limbonia\Controller $oController (optional)
   * @return \Limbonia\Interfaces\Result
   * @throws \Limbonia\Exception\Object
   */
  public static function resultFactory($sType, array $hParam = [], \Limbonia\Controller $oController = null): \Limbonia\Interfaces\Result
  {
    return self::factory($sType, $hParam, $oController)->run();
  }

  /**
   * Constructor
   */
  public function __construct(\Limbonia\Controller $oController = null)
  {
    $this->oController = $oController;

    $hParam = [];

    foreach (static::$hOptions as $sName => $hDef)
    {
      $hParam[$sName] = $hDef['Default'];
    }

    $this->setParam($hParam);
    $this->init();
  }

  /**
   * Run any needed code to make the current report work
   */
  protected function init()
  {
    //default init does nothing....
  }

  /**
   * Set the report parameters based on the specified hash
   *
   * @param array $hParam the parameters to set
   * @return array - any unused parameters from the specified $hParam list
   */
  public function setParam(array $hParam = []): array
  {
    $this->bChanged = false;

    if (isset($hParam['headers']))
    {
      $this->setHeaders($hParam['headers']);
      unset($hParam['headers']);
    }

    if (isset($hParam['sort']))
    {
      $this->aSort = $hParam['sort'];
      unset($hParam['sort']);
      $this->bChanged = true;
    }

    if (isset($hParam['fields']))
    {
      $this->aFields = $hParam['fields'];
      unset($hParam['fields']);
      $this->bChanged = true;
    }

    if (isset($hParam['search']))
    {
      $this->hSearch = $hParam['search'];
      unset($hParam['search']);
      $this->bChanged = true;
    }

    if (isset($hParam['offset']))
    {
      $this->iOffset = $hParam['offset'];
      unset($hParam['offset']);
      $this->bChanged = true;
    }

    if (isset($hParam['limit']))
    {
      $this->iLimit = $hParam['limit'];
      unset($hParam['limit']);
      $this->bChanged = true;
    }

    if (isset($hParam['data']))
    {
      $this->aData = array_values($hParam['data']);
      unset($hParam['data']);
      $this->bChanged = true;
    }

    foreach (static::$hOptions as $sName => $hDef)
    {
      if (isset($hParam[$sName]))
      {
        switch ($hDef['Type'])
        {
          case 'timestamp':
          case 'date':
          case 'searchdate':
            $this->hSearch[$sName] = new \DateTime($hParam[$sName]);

          default:
            $this->hSearch[$sName] = $hParam[$sName];
        }

        unset($hParam[$sName]);
        $this->bChanged = true;
      }
    }

    if ($this->bChanged)
    {
      $this->oResult = null;
    }

    return $hParam;
  }

  /**
   * Set this instance to use the specified headers
   *
   * @param array $hHeaders
   */
  protected function setHeaders(array $hHeaders)
  {
    //only set new headers if they haven't already been set
    if (empty($this->hHeaders))
    {
      $this->hHeaders = $hHeaders;
    }
  }

  /**
   * Return the current list of valid headers
   *
   * @return array
   */
  public function getHeaders(): array
  {
    if (empty($this->aFields))
    {
      return $this->hHeaders;
    }

    $hHeaders = [];

    foreach ($this->aFields as $sField)
    {
      if (isset($this->hHeaders[$sField]))
      {
        $hHeaders[$sField] = $this->hHeaders[$sField];
      }
    }

    return $hHeaders;
  }

  /**
   * Return the available options for this report
   *
   * @return array
   */
  public function getOptionList(): array
  {
    return static::$hOptions;
  }

  /**
   * Generate and return the result data for the current configuration of this report
   *
   * @return \Limbonia\Interfaces\Result
   */
  protected function generateResult(): \Limbonia\Interfaces\Result
  {
    //work on a local copy of the data...
    $aData = $this->aData;

    //get the list of fields in the data
    $aFieldList = isset($aData[0]) ? array_keys($aData[0]) : [];

    $aFields = [];

    if (count($this->aFields) > 0 && count($aFieldList) > 0)
    {
      //only use fields that exist and are specified
      $aFields = array_intersect($aFieldList, $this->aFields);

      //if there aren't any fields left
      if (empty($aFields))
      {
        $aData = [];
      }
    }

    $hSearch = [];
    $hSort = [];

    //start from the end of the array and work forwards
    $aSort = array_reverse($this->aSort);

    foreach ($aFieldList as $sField)
    {
      //only keap search parameters that exsits in the data...
      if (isset($this->hSearch[$sField]))
      {
        $hSearch[$sField] = $this->hSearch[$sField];
      }

      foreach ($aSort as $iKey => $sSort)
      {
        //only keap sort parameters that exsits in the data...
        if (is_string($sSort) && preg_match("/^$sField (.*)/", $sSort, $aMatch))
        {
          $hSort[$sField] = $aMatch[1];

          //since it's been used remove it from the list
          unset($aSort[$iKey]);

          //fields can only be sorted on once so stop looking
          break;
        }
      }
    }

    //if there are any search parameters then use them
    if (!empty($hSearch))
    {
      $aTemp = [];

      foreach ($aData as $hRow)
      {
        foreach ($hSearch as $sField => $sValue)
        {
          if (isset($hRow[$sField]) && $hRow[$sField] == $sValue)
          {
            $aTemp[] = $hRow;
            break;
          }
        }
      }

      $aData = $aTemp;
    }

    if (!empty($aFields))
    {
      $aTemp = [];

      foreach ($aData as $hRow)
      {
        $hTemp = [];

        foreach ($aFields as $sField)
        {
          if (isset($hRow[$sField]))
          {
            $hTemp[$sField] = $hRow[$sField];
          }
        }

        $aTemp[] = $hTemp;
      }

      $aData = $aTemp;
    }

    if (!empty($hSort))
    {
      foreach ($hSort as $sField => $sDirection)
      {
        $aTemp = $aData;
        $aData = [];
        $aSortTemp = [];

        foreach ($aTemp as $hRow)
        {
          if (!isset($aSortTemp[$hRow[$sField]]))
          {
            $aSortTemp[$hRow[$sField]] = [];
          }

          $aSortTemp[$hRow[$sField]][] = $hRow;
        }

        if (strtolower($sDirection) == 'desc')
        {
          krsort($aSortTemp);
        }
        else
        {
          ksort($aSortTemp);
        }

        reset($aSortTemp);

        foreach ($aSortTemp as $aList)
        {
          $aData = array_merge($aData, $aList);
        }
      }
    }

    return new \Limbonia\Result\Collection($aData);
  }

  /**
   * Generate, cache and return the result of the current configuration of this report
   *
   * @return \Limbonia\Interfaces\Result
   */
  public function run(): \Limbonia\Interfaces\Result
  {
    if (is_null($this->oResult))
    {
      $this->oResult = $this->generateResult();

      if (empty($this->hHeaders))
      {
        $aFieldList = isset($this->oResult[0]) ? array_keys($this->oResult[0]) : [];
        $this->setHeaders(array_combine($aFieldList, array_map('ucwords', $aFieldList)));
      }
    }

    return $this->oResult;
  }
}