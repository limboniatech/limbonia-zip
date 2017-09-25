<?php
namespace Omniverse;

/**
 * Omniverse Module base class
 *
 * This defines all the basic parts of an Omniverse module
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Module
{
  use \Omniverse\Traits\DriverList;

  /**
   * The controller for this module
   *
   * @var \Omniverse\Controller
   */
  protected $oController = null;

  /**
   * The type of module this is
   *
   * @var string
   */
  protected $sType = null;

  /**
   * The item object associated with this module
   *
   * @var \Omniverse\Item
   */
  protected $oItem = null;

  /**
   * List of fields used by module settings
   *
   * @var array
   */
  protected $hFields = [];

  /**
   * A list of the actual module settings
   *
   * @var array
   */
  protected $hSettings = [];

  /**
   * Has this module been initialized
   *
   * @var boolean
   */
  protected $bInit = false;

  /**
   * Have this module's settings been changed since the last save?
   *
   * @var boolean
   */
  protected $bChangedSettings = false;

  /**
   * Lists of columns to ignore when filling template data
   *
   * @var array
   */
  protected $aIgnore =
  [
    'edit' => [],
    'create' => [],
    'search' => [],
    'view' => [],
    'boolean' => []
  ];

  /**
   * List of column names in the order required
   *
   * @var array
   */
  protected $aColumnOrder =[];

  /**
   * List of column names that are allowed to generate "edit" links
   *
   * @var array
   */
  protected $aEditColumn = [];

  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'Admin';

  /**
   * Should this module's name appear in the menu?
   *
   * @var boolean
   */
  protected $bVisibleInMenu = true;

  /**
   * List of column names that should remain static
   *
   * @var array
   */
  protected $aStaticColumn = ['Name'];

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'list';

  /**
   * The current method being used by this module
   *
   * @var string
   */
  protected $sCurrentAction = 'list';

  /**
   * List of components that this module contains along with their descriptions
   *
   * @var array
   */
  protected $hComponent =
  [
    'search' => 'This is the ability to search and display data.',
    'edit' => '"The ability to edit existing data.',
    'create' => 'The ability to create new data.',
    'delete' => 'The ability to delete existing data.'
  ];

  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems =
  [
    'list' => 'List',
    'search' => 'Search',
    'create' => 'Create'
  ];

  /**
   * List of quick search items to display
   *
   * @var array
   */
  protected $hQuickSearch = [];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
    'view' => 'View',
    'edit' => 'Edit'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editcolumn', 'edit', 'list', 'view'];

  /**
   * A list of components the current user is allowed to use
   *
   * @var array
   */
  protected $hAllow = [];

  /**
   * Have the settings been loaded yet?
   *
   * @var boolean
   */
  protected $bSettingsLoaded = false;

  /**
   * Has the "City / State / Zip" block been output yet?
   *
   * @var boolean
   */
  protected $bCityStateZipDone = false;

  /**
   * Module Factory
   *
   * @param string $sType - The type of module to create
   * @param \Omniverse\Controller $oController
   * @return \Omniverse\Module
   */
  public static function factory($sType, Controller $oController)
  {
    $sTypeClass = __CLASS__ . '\\' . self::driver($sType);

    if (\class_exists($sTypeClass, true))
    {
      return new $sTypeClass($oController);
    }

    return new self($oController);
  }

  /**
   * Instantiate a module
   *
   * @param \Omniverse\Controller $oController
   */
  public function __construct(\Omniverse\Controller $oController)
  {
    $this->oController = $oController;
    $this->sType = empty($this->sType) ? preg_replace("#.*Module\\\#", '', get_class($this)) : $this->sType;

    if (count($this->hSettings) > 0)
    {
      $this->hMenuItems['settings'] = 'Settings';
      $this->aAllowedActions[] = 'settings';
      $this->hComponent['configure'] = "The ability to alter the module's configuration.";
    }

    try
    {
      $this->oItem = $this->oController->itemFactory($this->sType);

      if (isset($this->oController->api->id))
      {
        $this->oItem->load($this->oController->api->id);
      }

      if ($this->oItem->id > 0)
      {
        $this->hMenuItems['item'] = 'Item';
        $this->aAllowedActions[] = 'item';
      }
    }
    catch (\Exception $e)
    {
    }

    $this->sCurrentAction = in_array($oController->api->action, $this->aAllowedActions) ? $oController->api->action : $this->sDefaultAction;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->saveSettings();
  }

  /**
   * Is this module currently performing a search?
   *
   * @return boolean
   */
  public function isSearch()
  {
    return in_array($this->oController->api->call[1], ['search', 'list']);
  }

  /**
   * Return the parent admin object
   *
   * @return \Omniverse\Controller
   * @throws \Exception
   */
  public function getController()
  {
    return $this->oController;
  }

  /**
   * Return the type of module that this object represents
   *
   * @return string
   */
  public function getType()
  {
    return $this->sType;
  }

  /**
   * Return the list of fields used by this module's settings
   *
   * @return array
   */
  public function getSettingsFields()
  {
    return $this->hFields;
  }

  /**
   * Return the list of this module's components
   *
   * @return array
   */
  public function getComponents()
  {
    return $this->hComponent;
  }

  /**
   * Should the specified component type be allowed to be used by the current user of this module?
   *
   * @param string $sComponent
   * @return boolean
   */
  public function allow($sComponent)
  {
    if (!isset($this->hAllow[$sComponent]))
    {
      $this->hAllow[$sComponent] = $this->oController->user()->hasResource($this->sType, $this->getComponent($sComponent));
    }

    return $this->hAllow[$sComponent];
  }

  /**
   * Return this module's admin group
   *
   * @return string
   */
  public function getGroup()
  {
    return $this->sGroup;
  }

  /**
   * Is this module visible in the menu?
   *
   * @return boolean
   */
  public function visibleInMenu()
  {
    return $this->bVisibleInMenu;
  }

  /**
   * Generate and return the URI for the specified parameters
   *
   * @param string ...$aParam (optional) - List of parameters to place in the URI
   * @return string
   */
  public function generateUri(string ...$aParam): string
  {
    array_unshift($aParam, $this->sType);
    return $this->oController->generateUri(...$aParam);
  }

  protected function prepareTemplateList()
  {
    $this->prepareTemplatePostSearch();
  }

  protected function prepareTemplateGetCreate()
  {
    $sIDColumn = $this->oItem->getIDColumn();
    $hTemp = $this->oItem->getColumns();

    if (isset($hTemp[$sIDColumn]))
    {
      unset($hTemp[$sIDColumn]);
    }

    $hColumn = [];

    foreach ($hTemp as $sKey => $hValue)
    {
      if ((in_array($sKey, $this->aIgnore['create'])) || (isset($hValue['Key']) && preg_match("/Primary/", $hValue['Key'])))
      {
        continue;
      }

      $hColumn[preg_replace("/^.*?\./", "", $sKey)] = $hValue;
    }

    $this->oController->templateData('createColumns', $hColumn);
  }

  protected function prepareTemplateGetEdit()
  {
    if (!$this->allow('edit') || isset($this->oController->post['No']))
    {
      $this->oController->templateData('close', true);
      return null;
    }

    $hTemp = $this->oItem->getColumns();
    $aColumn = $this->getColumns('Edit');
    $hColumn = [];

    foreach ($aColumn as $sColumnName)
    {
      if (isset($hTemp[$sColumnName]))
      {
        $hColumn[$sColumnName] = $hTemp[$sColumnName];
      }
    }

    $sIDColumn = preg_replace("/.*?\./", "", $this->oItem->getIDColumn());
    $this->oController->templateData('idColumn', $sIDColumn);
    $this->oController->templateData('noID', $this->oItem->id == 0);
    $this->oController->templateData('editColumns', $hColumn);
  }

  protected function prepareTemplateGetSearch()
  {
    $hTemp = $this->oItem->getColumns();
    $aColumn = $this->getColumns('search');
    $hColumn = [];

    foreach ($aColumn as $sColumnName)
    {
      if (isset($hTemp[$sColumnName]) && $hTemp[$sColumnName] != 'password')
      {
        $hColumn[$sColumnName] = $hTemp[$sColumnName];

        if ($hColumn[$sColumnName]['Type'] == 'text')
        {
          $hColumn[$sColumnName]['Type'] = 'varchar';
        }

        if ($hColumn[$sColumnName]['Type'] == 'date')
        {
          $hColumn[$sColumnName]['Type'] = 'searchdate';
        }
      }
    }

    $this->oController->templateData('searchColumns', $hColumn);
  }

  protected function prepareTemplatePostCreate()
  {
    $this->oItem->setAll($this->processCreateGetData());
    $this->oItem->save();
  }

  protected function prepareTemplatePostEdit()
  {
    $this->oItem->setAll($this->editGetData());

    if ($this->oItem->save())
    {
      $this->oController->templateData('success', "This " . $this->getType() . " update has been successful.");
    }
    else
    {
      $this->oController->templateData('failure', "This " . $this->getType() . " update has failed.");
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $this->oController->api->method = 'get';
    $this->sCurrentAction = 'view';
  }

  /**
   * Run the specified search and set
   */
  protected function prepareTemplatePostSearch()
  {
    $xSearch = $this->processSearchGetCriteria();

    if (is_array($xSearch))
    {
      foreach (array_keys($xSearch) as $sKey)
      {
        $this->processSearchTerm($xSearch, $sKey);
      }
    }

    $oData = $this->processSearchGetData($xSearch);

    if (isset($this->oController->api->call[2]) && $this->oController->api->call[2] == 'quick' && $oData->count() == 1)
    {
      $oItem = $oData[0];
      header('Location: '. $this->generateUri($oItem->id));
    }

    $this->oController->templateData('data', $oData);
    $this->oController->templateData('idColumn', preg_replace("/.*?\./", '', $this->oItem->getIDColumn()));
    $hColumns = $this->getColumns('Search');

    foreach (array_keys($hColumns) as $sKey)
    {
      $this->processSearchColumnHeader($hColumns, $sKey);
    }

    $this->oController->templateData('dataColumns', $hColumns);
    $this->oController->templateData('table', $this->oController->widgetFactory('Table'));
  }

  protected function prepareTemplatePostSettings()
  {
    if (!isset($this->oController->post[$this->sType]))
    {
      throw new Exception('Nothing to save!');
    }

    foreach ($this->oController->post[$this->sType] as $sKey => $sData)
    {
      $this->setSetting($sKey, $sData);
    }

    $this->saveSettings();
  }

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    $sPrepareMethod = '';
    $sActionSubMethod = 'prepareTemplate' . ucfirst($this->sCurrentAction) . ucfirst($this->oController->api->subAction);
    $sActionMethod = 'prepareTemplate' . ucfirst($this->sCurrentAction);
    $sMethodActionSubMethod = 'prepareTemplate' . ucfirst($this->oController->api->method) . ucfirst($this->sCurrentAction) . ucfirst($this->oController->api->subAction);
    $sMethodActionMethod = 'prepareTemplate' . ucfirst($this->oController->api->method) . ucfirst($this->sCurrentAction);

    if (method_exists($this, $sActionSubMethod))
    {
      $sPrepareMethod = $sActionSubMethod;
    }
    elseif (method_exists($this, $sActionMethod))
    {
      $sPrepareMethod = $sActionMethod;
    }
    elseif (method_exists($this, $sMethodActionSubMethod))
    {
      $sPrepareMethod = $sMethodActionSubMethod;
    }
    elseif (method_exists($this, $sMethodActionMethod))
    {
      $sPrepareMethod = $sMethodActionMethod;
    }

    if (!empty($sPrepareMethod))
    {
      try
      {
        $this->$sPrepareMethod();
      }
      catch (\Exception $e)
      {
        $this->oController->templateData('error', $e->getMessage());
      }
    }

    $this->oController->templateData('method', $this->sCurrentAction);
    $this->oController->templateData('module', $this);
    $this->oController->templateData('currentItem', $this->oItem);
  }

  /**
   * Display the template
   */
  public function showTemplate()
  {
    if (!$this->allow($this->sCurrentAction))
    {
      echo '';
      return;
    }

    $sTemplate = null;
    $sModuleDir = strtolower($this->getType());
    $sActionTemplate = $this->sCurrentAction == 'list' ? 'search.html' : strtolower("{$this->sCurrentAction}.html");
    $sMethod = $this->oController->api->isPost()|| $this->sCurrentAction == 'list' ? 'process' : 'display';
    $sMethodTemplate = $sMethod . $sActionTemplate;

    foreach ($_SESSION['ModuleDirs'] as $sDir)
    {
      if (is_readable("$sDir/$sModuleDir/$sActionTemplate"))
      {
        $sTemplate = "$sModuleDir/$sActionTemplate";
        break;
      }
      elseif (is_readable("$sDir/$sModuleDir/$sMethodTemplate"))
      {
        $sTemplate = "$sModuleDir/$sMethodTemplate";
        break;
      }
      elseif (is_readable("$sDir/$sActionTemplate"))
      {
        $sTemplate = $sActionTemplate;
        break;
      }
      elseif (is_readable("$sDir/$sMethodTemplate"))
      {
        $sTemplate = $sMethodTemplate;
        break;
      }
    }

    if (empty($sTemplate))
    {
      $sTemplate = 'error.html';
      $this->oController->templateData('error', "The action \"{$this->sCurrentAction}\" does *not* exist in {$this->sType}!!!");
    }

    $this->oController->templateDisplay($sTemplate);
  }

  /**
   * Return the list of static columns, if there are any
   *
   * @return array
   */
  protected function getStaticColumn()
  {
    return is_array($this->aStaticColumn) ? $this->aStaticColumn : [];
  }

  /**
   * Load this module's settings from the database, if there are any
   */
  protected function loadSettings()
  {
    $oStatement = $this->oController->getDB()->prepare('SELECT Data FROM Settings WHERE Type = :Type LIMIT 1');
    $oStatement->bindColumn(':Type', $this->sType, \PDO::PARAM_STR);
    $sSettings = $oStatement->fetchOne();

    if (!is_null($sSettings))
    {
      $this->hSettings = unserialize($sSettings);
    }
    elseif (count($this->hSettings) > 0)
    {
      $oStatement = $this->oController->getDB()->prepare('INSERT INTO Settings (Type, Data) values (:Type, :Data)');
      $oStatement->bindColumn(':Type', $this->sType, \PDO::PARAM_STR);
      $oStatement->bindColumn(':Data', addslashes(serialize($this->hSettings)), \PDO::PARAM_STR);
      $oStatement->execute();
    }
  }

  /**
   * Save the current settings, if any to the database
   *
   * @return boolean - True on success or false on failure
   */
  protected function saveSettings()
  {
    if (!$this->bChangedSettings)
    {
      return true;
    }

    $oStatement = $this->oController->getDB()->prepare('UPDATE Settings SET Data = :Data WHERE Type = :Type');
    $oStatement->bindColumn(':Type', $this->sType, \PDO::PARAM_STR);
    $oStatement->bindColumn(':Data', addslashes(serialize($this->hSettings)), \PDO::PARAM_STR);
    $oStatement->execute();
    $this->bChangedSettings = false;
  }

  /**
   * Return the specified setting, if it exists
   *
   * @param string $sName
   * @return mixed
   */
  protected function getSetting($sName=null)
  {
    if (count($this->hSettings) == 0)
    {
      return false;
    }

    //if this is the first time try to load the settings...
    if (!$this->bSettingsLoaded)
    {
      $this->loadSettings();
      $this->bSettingsLoaded = true;
    }

    if (empty($sName))
    {
      return $this->hSettings;
    }

    return isset($this->hSettings[$sName]) ? $this->hSettings[$sName] : null;
  }

  /**
   * Set the specified setting to the specified value
   *
   * @param string $sName
   * @param mixed $xValue
   * @return boolean
   */
  protected function setSetting($sName, $xValue)
  {
    if (!isset($this->hFields[$sName]))
    {
      return false;
    }

    $this->bChangedSettings = true;
    $this->hSettings[$sName] = $xValue;
    return true;
  }

  /**
   * Return an array of height and width for a popup based on the specified name, if there is one
   *
   * @param string $sName
   * @return array
   */
  public function getPopupSize($sName)
  {
    return isset($this->hPopupSize[$sName]) ? $this->hPopupSize[$sName] : $this->hPopupSize['default'];
  }

  /**
   * Return a valid component name from the specified menu item
   *
   * @param string $sMenuItem
   * @return string
   */
  protected function getComponent($sMenuItem)
  {
    if (strpos($sMenuItem, 'quicksearch_') === 0 || $sMenuItem == 'list')
    {
      return "search";
    }

    if ($sMenuItem == 'editcolumn')
    {
      return 'edit';
    }

    return $sMenuItem;
  }

  /**
   * Return this module's list of menu items
   *
   * @return array
   */
  public function getMenuItems()
  {
    return $this->hMenuItems;
  }

  /**
   * Return this module's list of quick search items
   *
   * @return array
   */
  public function getQuickSearch()
  {
    return $this->hQuickSearch;
  }

  /**
   * Return this module's list of sub-menu items
   *
   * @return array
   */
  public function getSubMenuItems()
  {
    return $this->hSubMenuItems;
  }

  /**
   * Return the name / title of this module's current item, if there is one
   *
   * @return string
   */
  public function getCurrentItemTitle()
  {
    return $this->oItem->name;
  }

  /**
   * Generate and return the title for this module
   *
   * @return string
   */
  public function getTitle()
  {
    return ucwords(trim(preg_replace("/([A-Z])/", " $1", str_replace("_", " ", $this->sType))));
  }

  /**
   * Generate and return a list of columns based on the specified type
   *
   * @param string $sType (optional)
   * @return array
   */
  public function getColumns($sType = null)
  {
    $sLowerType = strtolower($sType);
    $hColumn = $this->oItem->getColumns();
    $sIDColumn = $this->oItem->getIDColumn();

    //remove the id column
    if (isset($hColumn[$sIDColumn]))
    {
      unset($hColumn[$sIDColumn]);
    }

    if (empty($sLowerType) || !isset($this->aIgnore[$sLowerType]))
    {
      return array_keys($hColumn);
    }

    //get the column names and remove the ignored columns
    $aColumn = array_diff(array_keys($hColumn), $this->aIgnore[$sLowerType]);

    //reorder the columns
    return array_unique(array_merge($this->aColumnOrder, $aColumn));
  }

  /**
   * Generate and return the data for the "Create" process
   *
   * @return array
   */
  protected function processCreateGetData()
  {
    $hData = isset($this->oController->post[$this->sType]) ? $this->oController->post[$this->sType] : [];

    foreach (array_keys($hData) as $sKey)
    {
      if (empty($hData[$sKey]))
      {
        unset($hData[$sKey]);
      }
    }

    foreach ($this->oItem->getColumns() as $sName => $hColumnData)
    {
      if (strtolower($hColumnData['Type']) == 'tinyint(1)')
      {
        $hData[$sName] = isset($hData[$sName]);
      }
    }

    return $hData;
  }

  /**
   * Prepare the search term array
   *
   * @param array $hArray
   * @param string $sKey
   * @return boolean
   */
  protected function processSearchTerm(&$hArray, $sKey)
  {
    if (empty($hArray[$sKey]))
    {
      unset($hArray[$sKey]);
      return true;
    }
  }

  /**
   * Generate and return the column headers for the "Search" process
   *
   * @param array $hArray
   * @param string $sKey
   */
  protected function processSearchColumnHeader(array &$hArray, $sKey)
  {
    $hArray[$sKey] = preg_replace("/^.*?\./", "", $hArray[$sKey]);
  }

  /**
   * Generate the search results table headers in the specified grid object
   *
   * @param \Omniverse\Widget\Table $oSortGrid
   * @param string $sColumn
   */
  public function processSearchGridHeader(\Omniverse\Widget\Table $oSortGrid, $sColumn)
  {
    //any columns that need to be static can be set in the aStaticColumn array...
    if (in_array($sColumn, $this->getStaticColumn()) || !$this->allow('Edit'))
    {
      $oSortGrid->addCell(\Omniverse\Widget\Table::generateSortHeader($sColumn), false);
    }
    else
    {
      $sDisplay = \Omniverse\Widget\Table::generateSortHeader($this->getColumnTitle($sColumn));

      if (in_array($sColumn, $this->aEditColumn))
      {
        $sDisplay .= "<span class=\"OmnisysSortGridEdit\" onClick=\"" . (self::$oOwner->usePopups() ? 'showOmnisys_Popup();' : '') . "document.getElementById('Omnisys_SortGrid_Edit').value='$sColumn'; document.getElementById('EditColumn').submit();\">[Edit]</span>";
      }

      $oSortGrid->addCell($sDisplay);
    }
  }

  /**
   * Generate and return the HTML needed to control the row specified by the id
   *
   * @param string $sIDColumn
   * @param integer $iID
   * @return string
   */
  public function processSearchGridRowControl($sIDColumn, $iID)
  {
    $sURL = $this->oController->baseUrl . '/' . strtolower($this->sType) . "/$iID";
    return "<input type=\"checkbox\" class=\"OmnisysSortGridCellCheckbox\" name=\"{$sIDColumn}[$iID]\" id=\"{$sIDColumn}[$iID]\" value=\"1\"> [<a href=\"$sURL\">View</a>]";
  }

  /**
   * Return the module criteria
   *
   * @return array
   */
  protected function processSearchGetCriteria()
  {
    //unless overridden by a descendant form data will allways take precendence over URL data
    return isset($this->oController->post[$this->sType]) ? $this->oController->post[$this->sType] : (isset($this->oController->get[$this->sType]) ? $this->oController->get[$this->sType] : null);
  }

  /**
   * Return the name of the ID column to use in the search
   *
   * @return string
   */
  protected function processSearchGetSortColumn()
  {
    return $this->oItem->getIDColumn();
  }

  /**
   * Perform the search based on the specified criteria and return the result
   *
   * @param string|array $xSearch
   * @return \Omniverse\ItemList
   */
  protected function processSearchGetData($xSearch)
  {
    return $this->oController->itemSearch($this->oItem->getTable(), $xSearch, $this->processSearchGetSortColumn());
  }

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [])
  {
    $sLabel = preg_replace("/([A-Z])/", "$1", $sName);

    if (is_null($sValue) && isset($hData['Default']) && !$this->isSearch())
    {
      $sValue = $hData['Default'];
    }

    if ($sName == 'State' || $sName == 'City' || $sName == 'Zip')
    {
      if ($this->bCityStateZipDone)
      {
        if ($sName == 'State' && !empty($sValue))
        {
          return "<script type=\"text/javascript\" language=\"javascript\">setState('$sValue');</script>\n";
        }

        if ($sName == 'City' && !empty($sValue))
        {
          return "<script type=\"text/javascript\" language=\"javascript\">setCity('$sValue');</script>\n";
        }

        if ($sName == 'Zip' && !empty($sValue))
        {
          return "<script type=\"text/javascript\" language=\"javascript\">setZip('$sValue');</script>\n";
        }

        return null;
      }

      $oStates = $this->oController->widgetFactory('States', "$this->sType[State]");
      $sStatesID = $oStates->getID();

      $oCities = $this->oController->widgetFactory('Select', "$this->sType[City]");
      $sCitiesID = $oCities->getID();

      $oZips = $this->oController->widgetFactory('Select', "$this->sType[Zip]");
      $sZipID = $oZips->getID();

      $sGetCities = $oStates->addAjaxFunction('getCitiesByState', true);
      $sGetZips = $oStates->addAjaxFunction('getZipsByCity', true);

      $sStateScript = "var stateSelect = document.getElementById('$sStatesID');\n";
      $sStateScript .= "var stateName = '';\n";
      $sStateScript .= "var cityName = '';\n";
      $sStateScript .= "function setState(state)\n";
      $sStateScript .= "{\n";
      $sStateScript .= "  stateName = state;\n";
      $sStateScript .= "  stateSelect.value = state;\n";
      $sStateScript .= '  ' . $sGetCities . "(state, '$sCitiesID', cityName);\n";
      $sStateScript .= "}\n";

      if ($sName == 'State')
      {
        $sStateScript .= "setState('" . $sValue . "');\n";
      }

      $oStates->writeJavascript($sStateScript);

      $sCityScript = "var citySelect = document.getElementById('$sCitiesID');\n";
      $sCityScript .= "var zipNum = '';\n";
      $sCityScript .= "function setCity(city)\n";
      $sCityScript .= "{\n";
      $sCityScript .= "  cityName = city;\n";
      $sCityScript .= "  if (citySelect.options.length > 1)\n";
      $sCityScript .= "  {\n";
      $sCityScript .= "    for (i = 0; i < citySelect.options.length; i++)\n";
      $sCityScript .= "    {\n";
      $sCityScript .= "      if (citySelect.options[i].value == city)\n";
      $sCityScript .= "      {\n";
      $sCityScript .= "        citySelect.options[i].selected = true;\n";
      $sCityScript .= "        break;\n";
      $sCityScript .= "      }\n";
      $sCityScript .= "    }\n";
      $sCityScript .= "  }\n";
      $sCityScript .= "  else\n";
      $sCityScript .= "  {\n";
      $sCityScript .= '    ' . $sGetCities . "(stateName, '$sCitiesID', city);\n";
      $sCityScript .= "  }\n";
      $sCityScript .= "  citySelect.options[1] = new Option(city, city, true);\n";
      $sCityScript .= '  ' . $sGetZips . "(cityName, stateName, '$sZipID', zipNum);\n";
      $sCityScript .= "}\n";

      if ($sName == 'City')
      {
        $sCityScript .= "setCity('" . $sValue . "');\n";
      }

      $oCities->writeJavascript($sCityScript);

      $sZipScript = "var zipSelect = document.getElementById('$sZipID');\n";
      $sZipScript .= "function setZip(zip)\n";
      $sZipScript .= "{\n";
      $sZipScript .= "  zipNum = zip;\n";
      $sZipScript .= "  if (zipSelect.options.length > 1)\n";
      $sZipScript .= "  {\n";
      $sZipScript .= "    for (i = 0; i < zipSelect.options.length; i++)\n";
      $sZipScript .= "    {\n";
      $sZipScript .= "      if (zipSelect.options[i].value == zip)\n";
      $sZipScript .= "      {\n";
      $sZipScript .= "        zipSelect.options[i].selected = true;\n";
      $sZipScript .= "        break;\n";
      $sZipScript .= "      }\n";
      $sZipScript .= "    }\n";
      $sZipScript .= "  }\n";
      $sZipScript .= "  else\n";
      $sZipScript .= "  {\n";
      $sZipScript .= "  zipSelect.options[1] = new Option(zip, zip, true);\n";
      $sZipScript .= '    ' . $sGetZips . "(cityName, stateName, '$sZipID', zipNum);\n";
      $sZipScript .= "  }\n";
      $sZipScript .= "}\n";

      if ($sName == 'Zip')
      {
        $sZipScript .= "setZip('" . $sValue . "');\n";
      }

      $oZips->writeJavascript($sZipScript);

      $oStates->addEvent('change', $sGetCities."(this.options[this.selectedIndex].value, '$sCitiesID', cityName)");

      $sFormField = "<div class=\"field\"><span class=\"label\">State</span><span class=\"data\">" . $oStates . "</span></div>";

      $oCities->addOption('Select a city', '0');
      $oCities->addEvent('change', $sGetZips."(this.options[this.selectedIndex].value, stateSelect.options[stateSelect.selectedIndex].value, '$sZipID', zipNum)");

      $sFormField .= "<div class=\"field\"><span class=\"label\">City</span><span class=\"data\">" . $oCities . "</span></div>";

      $oZips->addOption('Select a zip', '0');

      $sFormField .= "<div class=\"field\"><span class=\"label\">Zip</span><span class=\"data\">" . $oZips . "</span></div>";

      $this->bCityStateZipDone = true;
      return $sFormField;
    }

    if ($sName == 'UserID')
    {
      $oUsers = Item::search('User', ['Visible' => true, 'Active' => true]);
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[UserID]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : 'Select a user';
      $oSelect->addOption($sEmptyItemLabel, '');

      foreach ($oUsers as $hUser)
      {
        $oSelect->addOption($hUser['Name'], $hUser['ID']);
      }

      $oSelect->setSelected($sValue);
      return "<div class=\"field\"><span class=\"label\">User</span><span class=\"data\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'KeyID')
    {
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[KeyID]");
      $sEmptyItemLabel = $this->isSearch() ? 'None' : 'Select a resource name';
      $oSelect->addOption($sEmptyItemLabel, '');
      $oKeys = Item::search('ResourceKey', null, 'Name');

      foreach ($oKeys as $hKey)
      {
        if ($sValue == $hKey['KeyID'])
        {
          $oSelect->setSelected($hKey['KeyID']);
        }

        $oSelect->addOption($hKey['Name'], $hKey['KeyID']);
      }

      return "<div class=\"field\"><span class=\"label\">Required resource</span><span class=\"data\">" . $oSelect . "</span></div>";
    }

    if (preg_match('/(.+?)id$/i', $sName, $aMatch))
    {
      try
      {
        $oTest = Item::factory($aMatch[1]);

        if (isset($oTest->name))
        {
          $oList = Item::search($aMatch[1]);

          $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");
          $sEmptyItemLabel = $this->isSearch() ? 'None' : "Select {$aMatch[1]}";
          $oSelect->addOption($sEmptyItemLabel, '');

          foreach ($oList as $oTempItem)
          {
            $oSelect->addOption($oTempItem->name, $oTempItem->id);
          }

          $oSelect->addArray($hElements);

          if (!empty($sValue))
          {
            $oSelect->setSelected($sValue);
          }

          return "<div class=\"field\"><span class=\"label\">{$aMatch[1]}</span><span class=\"data\">" . $oSelect . "</span></div>";
        }
      }
      catch (\Exception $e)
      {
      }
    }

    if ($sName == 'FileName')
    {
      $oFile = $this->oController->widgetFactory('Input', "$this->sType[FileName]");
      $oFile->setParam('type', 'file');
      return "<div class=\"field\"><span class=\"label\">File Name</span><span class=\"data\">" . $oFile . "</span></div>";
    }

    $sType = strtolower(preg_replace("/( |\().*/", "", $hData['Type']));

    switch ($sType)
    {
      case 'hidden':
        $oHidden = \Omniverse\Tag::factory('hidden');
        $oHidden->setParam('name', "$this->sType[$sName]");
        $oHidden->setParam('id', $this->sType . $sName);
        $oHidden->setParam('value', $sValue);
        return $oHidden->__toString();

      case 'enum':
        $sElements = preg_replace("/enum\((.*?)\)/", "$1", $hData['Type']);
        $sElements = str_replace("'", '"', $sElements);
        $sElements = str_replace('""', "'", $sElements);
        $sElements = str_replace('"', '', $sElements);
        $aElements = explode(",", $sElements);
        $aTitle = array_map('ucwords', $aElements);
        $hElements = array_combine($aElements, $aTitle);
        $oSelect = $this->oController->widgetFactory('Select', "$this->sType[$sName]");

        $sEmptyItemLabel = $this->isSearch() ? 'None' : "Select $sLabel";
        $oSelect->addOption($sEmptyItemLabel, '');
        $oSelect->addArray($hElements);

        if (!empty($sValue))
        {
          $oSelect->setSelected($sValue);
        }

        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\">" . $oSelect . "</span></div>";

      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'textarea':
        $oText = $this->oController->widgetFactory('Editor', "$this->sType[$sName]");
        $oText->setToolBar('Basic');
        $oText->setText($sValue);
        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\">" . $oText . "</span></div>";

      case 'radio':
        $sFormField = '';

        foreach ($hData as $sKey => $sButtonValue)
        {
          if (preg_match("/^Value/", $sKey))
          {
            $sChecked = ($sButtonValue == $sValue ? ' checked' : null);
            $sFormField .= "$sButtonValue:  <input type=\"radio\" name=\"$this->sType[$sName]\" id=\"$this->sType[$sName]\"value=\"$sButtonValue\"$sChecked><br />";
          }
        }

        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\">$sFormField</span></div>\n";

      case 'float':
      case 'int':
      case 'varchar':
      case 'char':
        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\"><input type=\"text\" name=\"$this->sType[$sName]\" id=\"$this->sType[$sName]\" value=\"" . htmlentities($sValue) . "\"></span></div>";

      case 'timestamp':
      case 'date':
      case 'searchdate':
        $sSearchDate = $sType == 'searchdate' ? "<select name=\"{$sName}Operator\"><option> < </option><option selected> = </option><option> > </option></select>\n" : '';
        $oDate = $this->oController->widgetFactory('Window\Calendar', "$this->sType[$sName]");
        $oDate->button('Change');

        if (!empty($sValue))
        {
          $oDate->setStartDate($sValue);
        }

        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\">$sSearchDate" . $oDate . "</span></div>";

      case 'password':
        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\"><input type=\"password\" name=\"$this->sType[$sName]\" id=\"$this->sType[$sName]\" value=\"$sValue\"></span></div>
<div class=\"field\"><span class=\"label\">$sLabel(double check)</span><span class=\"data\"><input type=\"password\" name=\"$this->sType[{$sName}2]\" id=\"$this->sType[{$sName}2]\" value=\"$sValue\"></span></div>";

      case 'swing':
        return null;

      case 'tinyint':
        $sChecked = $sValue ? ' checked="checked"' : '';
        return "<div class=\"field\"><span class=\"label\">$sLabel</span><span class=\"data\"><input type=\"checkbox\" name=\"$this->sType[$sName]\" id=\"$this->sType[$sName]\" value=\"1\"$sChecked></span></div>";

      default:
        return "<div class=\"field\"><span class=\"label\">Not valid</span><span class=\"data\">$sName :: $sType</span></div>";
    }

    return '';
  }

  /**
   * Generate and return the column title from the specified column name
   *
   * @param string $sColumn
   * @return string
   */
  public function getColumnTitle($sColumn)
  {
    //if this is an ID column
    if (preg_match("/^(.+?)ID$/", $sColumn, $aMatch))
    {
      try
      {
        //and there is an item type to match
        Item::factory($aMatch[1]);

        //then make that the new column name
        return $aMatch[1];
      }
      catch (\Exception $e)
      {
        return $sColumn;
      }
    }

    return preg_replace("/([A-Z])/", " $1", $sColumn);
  }

  /**
   * Generate and return the value of the specified column
   *
   * @param \Omniverse\Item $oItem
   * @param string $sColumn
   * @return mixed
   */
  public function getColumnValue(Item $oItem, $sColumn)
  {
    if (preg_match("/(^.*?)id$/i", $sColumn, $aMatch))
    {
      try
      {
        $sType = $aMatch[1];

        if ($oItem->__isset($sType))
        {
          $oColumnItem = $oItem->__get($sType);

          if ($oColumnItem instanceof Item && $oColumnItem->__isset('name'))
          {
            return $oColumnItem->id == 0 ? 'None' : $oColumnItem->name;
          }
        }
      }
      catch (\Exception $e) { }
    }

    return $oItem->__get($sColumn);
  }

  /**
   * Generate and return the HTML for all the specified form fields
   *
   * @param array $hFields - List of the fields to generate HTML for
   * @param array $hValues (optional) - List of field data, if there is any
   * @return string
   */
  protected function getFormFields($hFields, $hValues = [])
  {
    if (!is_array($hFields))
    {
      return null;
    }

    $sFormFields = '';

    foreach ($hFields as $sName => $hData)
    {
      $sValue = isset($hValues[$sName]) ? $hValues[$sName] : null;
      $sFormFields .= $this->getFormField($sName, $sValue, $hData);
    }

    return $sFormFields;
  }

  /**
   *
   * @param type $sType
   * @param type $sText
   * @param type $sButtonName
   * @return string
   */
  protected function editDialog($sType, $sText, $sButtonName)
  {
    $sVerb = isset($_SESSION['EditData']['Delete']) ? 'Delete' : 'Edit Column';
    $sContent = "<form name=\"EditColumn\" action=\"" . $this->generateUri('editcolumn') . "\" method=\"post\">\n";
    $sContent .= $sText;
    $sContent .= "<input type=\"submit\" name=\"$sButtonName\" value=\"Yes\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"No\" value=\"No\">";
    $sContent .= "</form>\n";
    return \Omniverse\Controller\Admin::getMenu($sContent, $this->getTitle() . " :: $sVerb");
  }

  /**
   * Generate and return the HTML displayed after the edit has finished
   *
   * @param string $sType
   * @param string $sText
   * @param boolean $bReload
   * @return string
   */
  protected function editFinish($sType, $sText, $bReload=false)
  {
    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $sReload = $bReload ? 'javascript:opener.location.reload(); ' : '';
    $sURL = $sType == 'Popup' ? "javascript:{$sReload}window.close();" : $this->generateUri('search');
    return "<center><h1>$sText</h1> Click <a href=\"$sURL\">here</a> to continue.</center>";
  }

  /**
   * Generate and return the HTML for dealing with updates to rows of data
   *
   * @param string $sType
   * @return string
   */
  public function editColumn($sType)
  {
    if (!$this->allow('Edit') || isset($this->oController->post['No']))
    {
      if (isset($_SESSION['EditData']))
      {
        unset($_SESSION['EditData']);
      }

      $sJSCommand = $sType == 'Popup' ? 'window.close();' : 'history.go(-2);';
      return "<script type=\"text/javascript\" language=\"javascript\">$sJSCommand</script>";
    }

    $sFullIDColumn = $this->oItem->getIDColumn();
    $sIDColumn = preg_replace("/.*?\./", "", $sFullIDColumn);

    if (isset($this->oController->post[$sIDColumn]))
    {
      $_SESSION['EditData'][$sIDColumn] = $this->oController->post[$sIDColumn];
    }

    if (isset($this->oController->post['Delete']))
    {
      $_SESSION['EditData']['Delete'] = $this->oController->post['Delete'];
    }

    if (isset($this->oController->post['All']))
    {
      $_SESSION['EditData']['All'] = $this->oController->post['All'];
    }

    if (isset($this->oController->post['Column']))
    {
      $_SESSION['EditData']['Column'] = $this->oController->post['Column'];
    }

    if (!isset($_SESSION['EditData'][$sIDColumn]) && !isset($_SESSION['EditData']['All']))
    {
      $sUse = isset($_SESSION['EditData']['Delete']) ? 'delete' : 'edit';
      //for now we are going to fail insted of asking to use all items...
      //return $this->editDialog($sType, "No IDs were checked!  Did you want to $sUse all of them?<br />\n", 'All');
      return $this->editFinish($sType, "No IDs were checked, $sUse has failed.  Please check some items and try again!<br />\n", false);
    }

    if (isset($_SESSION['EditData']['Delete']))
    {
      if (!isset($this->oController->post['Check']))
      {
        return $this->editDialog($sType, "Once deleted these items can <b>not</b> restored!  Continue anyway?\n", 'Check');
      }

      $bSuccess = false;

      $hWhere = isset($_SESSION['EditData']['All']) ? [] : [$sFullIDColumn => array_keys($_SESSION['EditData'][$sIDColumn])];
      $oItemList = \Omniverse\Item::search($this->getType(), $hWhere);

      if (isset($oItemList))
      {
        foreach ($oItemList as $oItem)
        {
          $oItem->delete();
        }

        $bSuccess = true;
      }

      $sSuccess = $bSuccess ? 'complete' : 'failed';
      return $this->editFinish($sType, "Deletion $sSuccess!", $bSuccess);
    }

    if (!$sFullColumn = $_SESSION['EditData']['Column'])
    {
      return $this->editFinish($sType, "The column \"{$_SESSION['EditData']['Column']}\" does not exist!");
    }

    if (!isset($this->oController->post['Update']))
    {
      $hColumn = $this->oItem->getColumn($sFullColumn);
      return $this->editDialog($sType, $this->getFormFields([$_SESSION['EditData']['Column'] => $hColumn]), 'Update');
    }

    //the first item in the _POST array will be our data
    $sData = array_shift($this->oController->post);

    foreach ($_SESSION['EditData']['AdList'] as $oItem)
    {
      $oItem->setAll($sData);
      $oItem->save();
    }

    return $this->editFinish($sType, "Update complete!", true);
  }

  /**
   * Return the appropriate data for the current edit
   *
   * @return array
   */
  protected function editGetData()
  {
    $hFullPost = $this->oController->post;
    $hPost = isset($hFullPost[$this->sType]) ? $hFullPost[$this->sType] : $hFullPost;
    $hTemp = $this->oItem->getColumns();
    $aIgnore = isset($this->aIgnore['boolean']) ? $this->aIgnore['boolean'] : [];

    foreach ($hTemp as $sName => $hColumnData)
    {
      if (!in_array($sName, $aIgnore) && strtolower($hColumnData['Type']) == 'tinyint(1)')
      {
        $hPost[$sName] = isset($hPost[$sName]);
      }
    }

    return $hPost;
  }
}