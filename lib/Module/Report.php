<?php
namespace Limbonia\Module;

/**
 * Limbonia Report Module class
 *
 * Admin module for manipulating and outputting reports
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Report extends \Limbonia\Module
{
  /**
   * The current report, if there is one...
   *
   * @var \Limbonia\Report
   */
  protected $oReport = null;

  /**
   * List of fields used by module settings
   *
   * @var array
   */
  protected static $hSettingsFields =
  [
  ];

  /**
   * List of valid HTTP methods
   *
   * @var array
   */
  protected static $hHttpMethods =
  [
    'head',
    'get',
    'post',
    'put',
    'delete',
    'options'
  ];

  /**
   * List of components that this module contains along with their descriptions
   *
   * @var array
   */
  protected $hComponent =
  [
    'search' => 'This is the ability to search and display data.',
    'schedule' => 'The ability to schedule an email report.'
  ];

  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems =
  [
    'list' => 'List'
  ];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
    'view' => 'View',
    'schedule' => 'Schedule'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['list'];

  protected $hReportList = [];

  /**
   * Initialize this module's custom data
   */
  protected function init()
  {
    $this->hReportList = \Limbonia\Report::driverList();

    if (isset($this->oApi->call[2], $this->hReportList[$this->oApi->call[2]]))
    {
      $this->oReport = $this->oController->reportFactory($this->oApi->call[2], $this->generateReportParams());
    }

    if (!empty($this->oReport))
    {
      $this->aAllowedActions[] = 'view';
      $this->aAllowedActions[] = 'schedule';
    }

    $this->sDefaultAction = in_array($this->oApi->subaction, $this->aAllowedActions) ? $this->oApi->subaction : 'view';
  }

  public function getReport()
  {
    return $this->oReport;
  }

  /**
   * Is this module currently performing a search?
   *
   * @return boolean
   */
  public function isSearch()
  {
    //reporting is basically all searches....
    return true;
  }

  protected function generateReportParams()
  {
    $hParam = [];

    if (isset($this->oApi->fields))
    {
      $hParam['fields'] = $this->oApi->fields;
    }

    if (isset($this->oApi->fields))
    {
      $hParam['fields'] = $this->oApi->fields;
    }

    if (isset($this->oApi->fields))
    {
      $hParam['fields'] = $this->oApi->fields;
    }

    return $hParam;
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    //if there is no report then check to see if the driver list exists
    if (is_null($this->oReport))
    {
      if (empty($this->hReportList))
      {
        throw new \Exception('Report list not found', 404);
      }

      return null;
    }

    return null;
  }

  protected function processApiGetReport()
  {
    $aReport = [];
    $aReport[] = array_values($this->oReport->getHeaders());
    $this->oReport->setParam($this->generateReportParams());
    return $this->oReport->run();
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    //if there is no report then check to see if the driver list exists
    if (is_null($this->oReport))
    {
      if (empty($this->hReportList))
      {
        throw new \Exception('Report list not found', 404);
      }

      return $this->hReportList;
    }

    return $this->processApiGetReport();
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->oReport instanceof \Limbonia\Report)
    {
      $this->oController->templateData('report', $this->oReport);
    }

    parent::prepareTemplate();
  }

  /**
   * Return an array of data that is needed to display the module's admin output
   *
   * @return array
   */
  public function getAdminOutput()
  {
    if ($this->oReport instanceof \Limbonia\Report)
    {
      return array_merge(parent::getAdminOutput(),
      [
        'itemTitle' => $this->oReport->getType(),
        'subMenu' => $this->getSubMenuItems(true),
        'id' => strtolower($this->oReport->getType()),
        'itemUri' => $this->generateUri(strtolower($this->oReport->getType()))
      ]);
    }

    return parent::getAdminOutput();
  }

  /**
   * Process the default "search" code then display the results
   */
  protected function prepareTemplateList()
  {
    $this->oController->templateData('reportList', $this->hReportList);
    $this->oController->templateData('table', $this->oController->widgetFactory('Table'));
  }

  /**
   * Process the default "search" code then display the results
   */
  protected function prepareTemplateView()
  {
    $hOptions = $this->oReport->getOptionList();

    if (isset($this->oController->post[$this->sType]))
    {
      $hFormValues = $this->processSearchTerms(array_change_key_case($this->oController->post[$this->sType], CASE_LOWER));
    }
    else
    {
      $hFormValues = [];

      foreach ($hOptions as $sKey => $hData)
      {
        if (isset($hData['Default']))
        {
          $hFormValues[$sKey] = $hData['Default'];
        }
      }
    }

    $this->oReport->setParam($hFormValues);
    $this->oController->templateData('formValues', $hFormValues);
    $this->oController->templateData('table', $this->oController->widgetFactory('Table'));
  }
}