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
  /**
   * The controller for this module
   *
   * @var \Omniverse\Controller
   */
  protected $oController = null;

  /**
   * The name of the module
   *
   * @var string
   */
  protected $sModuleName = '';

  /**
   * The type of module this is
   *
   * @var string
   */
  protected $sType = '';

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
    'Edit' => [],
    'Create' => [],
    'Search' => [],
    'View' => [],
    'Boolean' => []
  ];

  /**
   * List of column names in nthe order required
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
   * The default action for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'Process';

  /**
   * The current action being taken by this module
   *
   * @var string
   */
  protected $sCurrentAction = 'Process';

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultMethod = 'List';

  /**
   * The current method being used by this module
   *
   * @var string
   */
  protected $sCurrentMethod = 'List';

  /**
   * List of components that this module contains along with thier descriptions
   *
   * @var array
   */
  protected $hComponent =
  [
    'Search' => 'This is the ability to search and display data.',
    'Edit' => '"The ability to edit existing data.',
    'Create' => 'The ability to create new data.',
    'Delete' => 'The ability to delete existing data.'
  ];

  /**
   * List of menu items that this module shoud display
   *
   * @var array
   */
  protected $aMenuItems = ['List', 'Search', 'Create'];

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $aSubMenuItems = ['View', 'Edit'];

  /**
   * List of methods that are allowed to run
   *
   * @var array
   */
  protected $aAllowedMethods = ['Search', 'Create', 'EditDialog', 'EditColumn', 'Edit', 'List', 'View'];

  /**
   * A list of popup window sizes
   *
   * @var array
   */
  protected $hPopupSize = ['default' => [400, 300]];

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
  public static function factory($sType, Controller $oController = null)
  {
    $sTypeClass = __NAMESPACE__ . '\\Module\\' . $sType;

    if (\class_exists($sTypeClass, true))
    {
      return new $sTypeClass($sType, $oController);
    }

    return new self($sType, $oController);
  }

  /**
   * Instantiate a module
   *
   * @param string $sType (optional) - The type of module this should become
   * @param \Omniverse\Controller $oController
   */
  public function __construct($sType = null, Controller $oController = null)
  {
    $sAdmin = filter_input(INPUT_GET, 'Admin');
    $sAdminSub = filter_input(INPUT_GET, $sAdmin);

    $this->sModuleName = preg_replace("#.*Module\\\#", '', get_class($this));
    $this->sType = empty($sType) ? $this->sModuleName : $sType;
    $this->oController = empty($oController) ? Controller::getDefault() : $oController;

    try
    {
      $this->oItem = $this->getController()->itemFactory($this->sType);
      $sIDColumn = $this->oItem->getIDColumn();
      $sIDValue = filter_input(INPUT_GET, $sIDColumn);

      if (!empty($sIDValue))
      {
        $this->oItem->load($sIDValue);
      }
    }
    catch (\Exception $e)
    {
    }

    if ($this->oItem && $this->oItem->id > 0)
    {
      $iPosition = false;

      if ($iPosition = array_search('Search', $this->aMenuItems))
      {
        $iPosition++;
      }
      elseif ($iPosition = array_search('List', $this->aMenuItems))
      {
        $iPosition++;
      }
      else
      {
        $iPosition = array_search('Create', $this->aMenuItems);
      }

      if ($iPosition === false)
      {
        $this->aMenuItems[] = 'Item';
      }
      else
      {
        $aRemaining = array_splice($this->aMenuItems, $iPosition, count($this->aMenuItems), 'Item');
        $this->aMenuItems = array_merge($this->aMenuItems, $aRemaining);
      }

      $this->aAllowedMethods[] = 'Item';
    }

    if (count($this->hSettings) > 0)
    {
      $this->aMenuItems[] = 'Settings';
      $this->aAllowedMethods[] = 'Settings';
      $this->hComponent['Configure'] = "The ability to alter the module's configuration.";
    }

    $this->sCurrentAction = empty($sAdmin) || $sAdmin == 'Menu' ? $this->sDefaultAction : $sAdmin;
    $this->sCurrentMethod = empty($sAdminSub) || $sAdmin == 'Menu' || !in_array($sAdminSub, $this->aAllowedMethods) ? $this->sDefaultMethod : $sAdminSub;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->saveSettings();
  }

  /**
   * Return the parent admin object
   *
   * @return \Omniverse\Controller
   * @throws \Exception
   */
  public function getController()
  {
    if ($this->oController instanceof \Omniverse\Controller)
    {
      return $this->oController;
    }

    throw new \Exception('No valid Controller found!');
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
   * Return the name of this module
   *
   * @return string
   */
  public function getName()
  {
    return $this->sModuleName;
  }

  /**
   * Return the list of fields used by this module's settings
   *
   * @return array
   */
  public function getsettingsFields()
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
      $this->hAllow[$sComponent] = $this->getController()->user()->hasResource($this->sType, $this->getComponent($sComponent));
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
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Display')
    {
      if ($this->sCurrentMethod == 'Create')
      {
        $sIDColumn = $this->oItem->getIDColumn();
        $hTemp = $this->oItem->getColumns();

        if (isset($hTemp[$sIDColumn]))
        {
          unset($hTemp[$sIDColumn]);
        }

        $hColumn = array();

        foreach ($hTemp as $sKey => $hValue)
        {
          if ((in_array($sKey, $this->aIgnore['Create'])) || (isset($hValue['Key']) && preg_match("/Primary/", $hValue['Key'])))
          {
            continue;
          }

          $hColumn[preg_replace("/^.*?\./", "", $sKey)] = $hValue;
        }

        $this->getController()->templateData('createColumns', $hColumn);
      }
      elseif ($this->sCurrentMethod == 'Edit')
      {
        if (!$this->allow('Edit') || isset($_POST['No']))
        {
          $this->getController()->templateData('close', true);
          return null;
        }

        $hTemp = $this->oItem->getColumns();
        $aColumn = $this->getColumns('Edit');
        $hColumn = array();

        foreach ($aColumn as $sColumnName)
        {
          if (isset($hTemp[$sColumnName]))
          {
            $hColumn[$sColumnName] = $hTemp[$sColumnName];
          }
        }

        $sIDColumn = preg_replace("/.*?\./", "", $this->oItem->getIDColumn());
        $this->getController()->templateData('idColumn', $sIDColumn);
        $this->getController()->templateData('post', $_POST);
        $this->getController()->templateData('noID', !isset($_GET[$sIDColumn]));
        $this->getController()->templateData('editColumns', $hColumn);
      }
      elseif ($this->sCurrentMethod == 'Search')
      {
        $hTemp = $this->oItem->getColumns();
        $aColumn = $this->getColumns('Search');
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

        $this->getController()->templateData('searchColumns', $hColumn);
        $this->getController()->templateData('post', $_POST);
      }
    }
    elseif ($this->sCurrentAction == 'Process')
    {
      if ($this->sCurrentMethod == 'Create')
      {
        $this->getController()->templateData('post', $_POST);

        try
        {
          $this->oItem->setAll($this->processCreateGetData());
          $iModule = $this->oItem->save();
          $this->getController()->templateData('currentItem', $this->oItem);
        }
        catch (\Exception $e)
        {
          $this->getController()->templateData('error', $e->getMessage());
        }
      }
      elseif ($this->sCurrentMethod == 'Edit')
      {
        $this->oItem->setAll($this->editGetData());

        if ($this->oItem->save())
        {
          $this->getController()->templateData('success', "This " . $this->getType() . " update has been successful.");
        }
        else
        {
          $this->getController()->templateData('failure', "This " . $this->getType() . " update has failed.");
        }

        if (isset($_SESSION['EditData']))
        {
          unset($_SESSION['EditData']);
        }

        $this->sCurrentAction = 'Display';
        $this->sCurrentMethod = 'View';
      }
      elseif ($this->sCurrentMethod == 'Search' || $this->sCurrentMethod == 'List')
      {
        $xSearch = $this->processSearchGetCriteria();

        if (is_array($xSearch))
        {
          foreach ($xSearch as $sKey => $sValue)
          {
            $this->processSearchTerm($xSearch, $sKey);
          }
        }

        $this->getController()->templateData('data', $this->processSearchGetData($xSearch));
        $this->getController()->templateData('idColumn', preg_replace("/.*?\./", '', $this->oItem->getIDColumn()));
        $aColumns = $this->getColumns('Search');

        foreach ($aColumns as $sKey => $sColumn)
        {
          $this->processSearchColumnHeader($aColumns, $sKey);
        }

        $this->getController()->templateData('dataColumns', $aColumns);
        $this->getController()->templateData('table', $this->getController()->widgetFactory('Table'));
      }
      elseif ($this->sCurrentMethod == 'Settings')
      {
        if (!isset($_POST[$this->sModuleName]))
        {
          $this->getController()->templateData('error', "Nothing to save!");
        }
        else
        {
          try
          {
            foreach ($_POST[$this->sModuleName] as $sKey => $sData)
            {
              $this->setSetting($sKey, $sData);
            }

            $this->saveSettings();
          }
          catch (\Exception $e)
          {
            $this->getController()->templateData('error', $e->getMessage());
          }
        }
      }
    }

    $this->getController()->templateData('method', $this->sCurrentMethod);
    $this->getController()->templateData('action', $this->sCurrentAction);
    $this->getController()->templateData('module', $this);
    $this->getController()->templateData('currentItem', $this->oItem);
  }

  /**
   * Display the template
   */
  public function showTemplate()
  {
    if (!$this->allow($this->sCurrentMethod))
    {
      echo '';
      return;
    }

    $sTemplate = null;
    $sModuleDir = strtolower($_GET['Module']);
    $sMethodTemplate = $this->sCurrentMethod == 'List' ? 'search.html' : strtolower("{$this->sCurrentMethod}.html");
    $sActionTemplate = strtolower($this->sCurrentAction) . $sMethodTemplate;

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
      $this->getController()->templateData('error', "The {$this->sCurrentAction} method ({$this->sCurrentMethod}) does *not* exist in {$this->sModuleName}!!!");
    }

    $this->getController()->templateDisplay($sTemplate);
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
    $oStatement = $this->getController()->getDB()->prepare('SELECT Data FROM Settings WHERE Type = :Type LIMIT 1');
    $oStatement->bindColumn(':Type', $this->sType, \PDO::PARAM_STR);
    $sSettings = $oStatement->fetchOne();

    if (!is_null($sSettings))
    {
      $this->hSettings = unserialize($sSettings);
    }
    elseif (count($this->hSettings) > 0)
    {
      $oStatement = $this->getController()->getDB()->prepare('INSERT INTO Settings (Type, Data) values (:Type, :Data)');
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

    $oStatement = $this->getController()->getDB()->prepare('UPDATE Settings SET Data = :Data WHERE Type = :Type');
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
    if (strpos($sMenuItem, 'QuickSearch_') === 0 || $sMenuItem == 'List')
    {
      return "Search";
    }

    if ($sMenuItem == 'EditColumn')
    {
      return 'Edit';
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
    return $this->aMenuItems;
  }

  /**
   * Return this module's list of sub-menu items
   *
   * @return array
   */
  public function getSubMenuItems()
  {
    return $this->aSubMenuItems;
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
  public static function getTitle()
  {
    return ucwords(trim(preg_replace("/([A-Z])/", " $1", str_replace("_", " ", $_GET['Module']))));
  }

  /**
   * Generate and return a list of columns based on the specified type
   *
   * @param string $sType (optional)
   * @return array
   */
  public function getColumns($sType=null)
  {
    $sType = ucfirst(strtolower(trim($sType)));
    $hColumn = $this->oItem->getColumns();
    $sIDColumn = $this->oItem->getIDColumn();

    //remove the id column
    if (isset($hColumn[$sIDColumn]))
    {
      unset($hColumn[$sIDColumn]);
    }

    $aColumn = array_keys($hColumn);

    if (empty($sType) || !isset($this->aIgnore[$sType]))
    {
      return $aColumn;
    }

    //get the column names and remove the ignored columns
    $aColumn = array_diff(array_keys($hColumn), $this->aIgnore[$sType]);

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
    $hPost = filter_input_array(INPUT_POST);
    $hData = isset($hPost[$this->sModuleName]) ? $hPost[$this->sModuleName] : [];

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
    $sURL = "?Admin=Process&Module=$this->sModuleName&Process=View&$sIDColumn=$iID";
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
    return isset($_POST[$this->sModuleName]) ? $_POST[$this->sModuleName] : (isset($_GET[$this->sModuleName]) ? $_GET[$this->sModuleName] : null);
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
    return $this->getController()->itemSearch($this->oItem->getTable(), $xSearch, $this->processSearchGetSortColumn());
  }

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @param boolean $bInTable - Should the returned HTML use a table to contain the data
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [], $bInTable = false)
  {
    $sLabel = preg_replace("/([A-Z])/", " $1", $sName);

    if (is_null($sValue) && isset($hData['Default']))
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

      $oStates = $this->getController()->widgetFactory('States', "$this->sModuleName[State]");
      $sStatesID = $oStates->getID();

      $oCities = $this->getController()->widgetFactory('Select', "$this->sModuleName[City]");
      $sCitiesID = $oCities->getID();

      $oZips = $this->getController()->widgetFactory('Select', "$this->sModuleName[Zip]");
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

      if ($bInTable)
      {
        $sFormField = "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">State:</th><td class=\"OmnisysFieldValue\">" . $oStates . "</td></tr>";
      }
      else
      {
        $sFormField = "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">State:</span><span class=\"OmnisysFieldValue\">" . $oStates . "</span></div>";
      }

      $oCities->addOption('Select a city', '0');
      $oCities->addEvent('change', $sGetZips."(this.options[this.selectedIndex].value, stateSelect.options[stateSelect.selectedIndex].value, '$sZipID', zipNum)");

      if ($bInTable)
      {
        $sFormField .= "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">City:</th><td class=\"OmnisysFieldValue\">" . $oCities . "</td></tr>";
      }
      else
      {
        $sFormField .= "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">City:</span><span class=\"OmnisysFieldValue\">" . $oCities . "</span></div>";
      }

      $oZips->addOption('Select a zip', '0');

      if ($bInTable)
      {
        $sFormField .= "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Zip:</th><td class=\"OmnisysFieldValue\">" . $oZips . "</td></tr>";
      }
      else
      {
        $sFormField .= "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Zip:</span><span class=\"OmnisysFieldValue\">" . $oZips . "</span></div>";
      }

      $this->bCityStateZipDone = true;
      return $sFormField;
    }

    if ($sName == 'UserID')
    {
      $oUsers = Item::search('User', array('Visible' => true, 'Active' => true));
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[UserID]");
      $oSelect->addOption('Select a user', '');

      foreach ($oUsers as $hUser)
      {
        $oSelect->addOption($hUser['Name'], $hUser['ID']);
      }

      $oSelect->setSelected($sValue);

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">User:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">User:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
    }

    if ($sName == 'KeyID')
    {
      $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[KeyID]");
      $oSelect->addOption('Select a resource name', '');
      $oKeys = Item::search('ResourceKey', null, 'Name');

      foreach ($oKeys as $hKey)
      {
        if ($sValue == $hKey['KeyID'])
        {
          $oSelect->setSelected($hKey['KeyID']);
        }

        $oSelect->addOption($hKey['Name'], $hKey['KeyID']);
      }

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Required resource:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Required resource:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
    }

    if (preg_match('/(.+?)id$/i', $sName, $aMatch))
    {
      try
      {
        $oTest = Item::factory($aMatch[1]);

        if (isset($oTest->name))
        {
          $oList = Item::search($aMatch[1]);

          $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[$sName]");
          $oSelect->addOption("Select {$aMatch[1]}", '');

          foreach ($oList as $oTempItem)
          {
            $oSelect->addOption($oTempItem->name, $oTempItem->id);
          }

          $oSelect->addArray($hElements);

          if (!empty($sValue))
          {
            $oSelect->setSelected($sValue);
          }

          if ($bInTable)
          {
            return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">{$aMatch[1]}:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
          }

          return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">{$aMatch[1]}:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";
        }
      }
      catch (\Exception $e)
      {
      }
    }

    if ($sName == 'FileName')
    {
      $oFile = $this->getController()->widgetFactory('Input', "$this->sModuleName[FileName]");
      $oFile->setParam('type', 'file');

      if ($bInTable)
      {
        return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">File Name:</th><td class=\"OmnisysFieldValue\">" . $oFile . "</td></tr>";
      }

      return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">File Name:</span><span class=\"OmnisysFieldValue\">" . $oFile . "</span></div>";
    }

    $sType = strtolower(preg_replace("/( |\().*/", "", $hData['Type']));

    switch ($sType)
    {
      case 'hidden':
        $oHidden = \Omniverse\Tag::factory('hidden');
        $oHidden->setParam('name', "$this->sModuleName[$sName]");
        $oHidden->setParam('id', $this->sModuleName . $sName);
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
        $oSelect = $this->getController()->widgetFactory('Select', "$this->sModuleName[$sName]");
        $oSelect->addOption("Select $sLabel", '');
        $oSelect->addArray($hElements);

        if (!empty($sValue))
        {
          $oSelect->setSelected($sValue);
        }

        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\">" . $oSelect . "</td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\">" . $oSelect . "</span></div>";

      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'textarea':
        $oText = $this->getController()->widgetFactory('Editor', "$this->sModuleName[$sName]");
        $oText->setToolBar('Basic');
        $oText->setText($sValue);

        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\">" . $oText . "</td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\">" . $oText . "</span></div>";

      case 'radio':
        $sFormField = '';

        foreach ($hData as $sKey => $sButtonValue)
        {
          if (preg_match("/^Value/", $sKey))
          {
            $sChecked = ($sButtonValue == $sValue ? ' checked' : null);
            $sFormField .= "$sButtonValue:  <input type=\"radio\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\"value=\"$sButtonValue\"$sChecked><br />";
          }
        }

        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\">$sFormField</td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\">$sFormField</span></div>\n";

      case 'float':
      case 'int':
      case 'varchar':
      case 'char':
        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\"><input type=\"text\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\"><input type=\"text\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></span></div>";

      case 'timestamp':
      case 'date':
      case 'searchdate':
        $sSearchDate = $sType == 'searchdate' ? "<select name=\"{$sName}Operator\"><option> < </option><option selected> = </option><option> > </option></select>\n" : '';
        $oDate = $this->getController()->widgetFactory('Window\Calendar', "$this->sModuleName[$sName]");
        $oDate->button('Change');

        if (!empty($sValue))
        {
          $oDate->setStartDate($sValue);
        }

        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\">$sSearchDate" . $oDate . "</td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\">$sSearchDate" . $oDate . "</span></div>";

      case 'password':
        if ($bInTable)
        {
          $sField  = "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\"><input type=\"password\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></td></tr>\n";
          $sField .= "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel(double check):</th><td class=\"OmnisysFieldValue\"><input type=\"password\" name=\"$this->sModuleName[{$sName}2]\" id=\"$this->sModuleName[{$sName}2]\" value=\"$sValue\"></td></tr>";
        }
        else
        {
          $sField  = "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\"><input type=\"password\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"$sValue\"></span></div>\n";
          $sField .= "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel(double check):</span><span class=\"OmnisysFieldValue\"><input type=\"password\" name=\"$this->sModuleName[{$sName}2]\" id=\"$this->sModuleName[{$sName}2]\" value=\"$sValue\"></span></div>";
        }

        return $sField;

      case 'swing':
        return null;

      case 'tinyint':
        $sChecked = $sValue ? ' checked="checked"' : '';

        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">$sLabel:</th><td class=\"OmnisysFieldValue\"><input type=\"checkbox\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"1\"$sChecked></td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">$sLabel:</span><span class=\"OmnisysFieldValue\"><input type=\"checkbox\" name=\"$this->sModuleName[$sName]\" id=\"$this->sModuleName[$sName]\" value=\"1\"$sChecked></span></div>";

      default:
        if ($bInTable)
        {
          return "<tr class=\"OmnisysField\"><th class=\"OmnisysFieldName\">Not valid:</th><td class=\"OmnisysFieldValue\">$sName :: $sType</td></tr>";
        }

        return "<div class=\"OmnisysField\"><span class=\"OmnisysFieldName\">Not valid:</span><span class=\"OmnisysFieldValue\">$sName :: $sType</span></div>";
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
    $sContent = "<form name=\"EditColumn\" action=\"?Admin=$sType&Module=$this->sModuleName&$sType=EditColumn\" method=\"post\">\n";
    $sContent .= $sText;
    $sContent .= "<input type=\"submit\" name=\"$sButtonName\" value=\"Yes\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"No\" value=\"No\">";
    $sContent .= "</form>\n";
    return \Omniverse\Controller\Admin::getMenu($sContent, self::getTitle() . " :: $sVerb");
  }

  /**
   * Generate and return the HTML displyed after the edit has finished
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
    $sURL = $sType == 'Popup' ? "javascript:{$sReload}window.close();" : "?Admin=$sType&Module=$this->sModuleName&$sType=Search";
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
    if (!$this-> allow('Edit') || isset($_POST['No']))
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

    if (isset($_POST[$sIDColumn]))
    {
      $_SESSION['EditData'][$sIDColumn] = $_POST[$sIDColumn];
    }

    if (isset($_POST['Delete']))
    {
      $_SESSION['EditData']['Delete'] = $_POST['Delete'];
    }

    if (isset($_POST['All']))
    {
      $_SESSION['EditData']['All'] = $_POST['All'];
    }

    if (isset($_POST['Column']))
    {
      $_SESSION['EditData']['Column'] = $_POST['Column'];
    }

    if (!isset($_SESSION['EditData'][$sIDColumn]) && !isset($_SESSION['EditData']['All']))
    {
      $sUse = isset($_SESSION['EditData']['Delete']) ? 'delete' : 'edit';
      //for now we are going to fail insted of asking to use all items...
      //return $this->editDialog($sType, "No IDs were checked!  Did you want to $sUse all of them?<br />\n", 'All');
      return $this->editFinish($sType, "No IDs were checked, $sUse has failed.  Please check some items and try again!<br />\n", false);
    }

    $hOrder = isset($_SESSION['EditData']['All']) ? [] : [$sFullIDColumn => array_keys($_SESSION['EditData'][$sIDColumn])];
    $_SESSION['EditData']['AdList'] = Item::search($this->getType(), $hOrder);

    if (isset($_SESSION['EditData']['Delete']))
    {
      if (!isset($_POST['Check']))
      {
        return $this->editDialog($sType, "Once deleted these items can <b>not</b> restored!  Continue anyway?\n", 'Check');
      }

      $bSuccess = false;

      if (isset($_SESSION['EditData']['AdList']))
      {
        foreach ($_SESSION['EditData']['AdList'] as $oItem)
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

    if (!isset($_POST['Update']))
    {
      $hColumn = $this->oItem->getColumn($sFullColumn);
      return $this->editDialog($sType, $this->getFormFields(array($_SESSION['EditData']['Column'] => $hColumn)), 'Update');
    }

    //the first item in the _POST array will be our data
    $sData = array_shift($_POST);

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
    $hFullPost = filter_input_array(INPUT_POST);
    $hPost = isset($hFullPost[$this->sModuleName]) ? $hFullPost[$this->sModuleName] : $hFullPost;
    $hTemp = $this->oItem->getColumns();
    $aIgnore = isset($this->aIgnore['Boolean']) ? $this->aIgnore['Boolean'] : [];

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