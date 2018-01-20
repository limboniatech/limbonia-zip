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
  use \Omniverse\Traits\HasController;

  /**
   * List of fields used by module settings
   *
   * @var array
   */
  protected static $hSettingsFields = [];

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
   * The type of module this is
   *
   * @var string
   */
  protected $sType = null;

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
    'edit' => 'The ability to edit existing data.',
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
   * Has the "City / State / Zip" block been output yet?
   *
   * @var boolean
   */
  protected $bCityStateZipDone = false;

  /**
   * Generate and cache the driver list for the current object type
   */
  public static function overrideDriverList(\Omniverse\Controller $oController, \Omniverse\Item\User $oUser)
  {
    if (!isset($_SESSION['DriverList']))
    {
      $_SESSION['DriverList'] = [];
    }

    $_SESSION['ResourceList'] = [];
    $_SESSION['ModuleGroups'] = [];
    $_SESSION['DriverList'][__CLASS__] = [];
    $sClassDir = preg_replace("#\\\#", '/', preg_replace("#Omniverse\\\\#", '', __CLASS__));
    $aBlackList = $oController->moduleBlackList ?? [];

    foreach (\Omniverse\Controller::getLibs() as $sLib)
    {
      foreach (glob("$sLib/$sClassDir/*.php") as $sClassFile)
      {
        $sClasseName = basename($sClassFile, ".php");

        if (isset($_SESSION['DriverList'][__CLASS__][strtolower($sClasseName)]) || in_array($sClasseName, $aBlackList) || !$oUser->hasResource($sClasseName))
        {
          continue;
        }

        $sTypeClass = __CLASS__ . '\\' . $sClasseName;

        if (!class_exists($sTypeClass, true))
        {
          continue;
        }

        $oModule =  new $sTypeClass($oController);
        $hComponent = $oModule->getComponents();
        ksort($hComponent);
        reset($hComponent);
        $_SESSION['ResourceList'][$oModule->getType()] = $hComponent;
        $_SESSION['ModuleGroups'][$oModule->getGroup()][strtolower($oModule->getType())] = $oModule->getType();
        $_SESSION['DriverList'][__CLASS__][strtolower($sClasseName)] = $sClasseName;
      }
    }

    ksort($_SESSION['DriverList'][__CLASS__]);
    reset($_SESSION['DriverList'][__CLASS__]);

    ksort($_SESSION['ResourceList']);
    reset($_SESSION['ResourceList']);

    ksort($_SESSION['ModuleGroups']);

    foreach (array_keys($_SESSION['ModuleGroups']) as $sKey)
    {
      ksort($_SESSION['ModuleGroups'][$sKey]);
    }
  }

  /**
   * Module Factory
   *
   * @param string $sType - The type of module to create
   * @param \Omniverse\Controller $oController
   * @return \Omniverse\Module
   */
  public static function factory($sType, \Omniverse\Controller $oController)
  {
    $sTypeClass = __CLASS__ . '\\' . self::driver($sType);

    if (!class_exists($sTypeClass, true))
    {
      throw new \Omniverse\Exception\Object("No driver for module ($sType) exists!");
    }

    return new $sTypeClass($oController);
  }

  /**
   * Instantiate a module
   *
   * @param \Omniverse\Controller $oController
   */
  protected function __construct(\Omniverse\Controller $oController)
  {
    $this->oController = $oController;
    $this->sType = empty($this->sType) ? preg_replace("#.*Module\\\#", '', get_class($this)) : $this->sType;

    if (count(static::$hSettingsFields) > 0)
    {
      $this->hMenuItems['settings'] = 'Settings';
      $this->aAllowedActions[] = 'settings';
      $this->hComponent['configure'] = "The ability to alter the module's configuration.";

      $oStatement = $this->oController->getDB()->prepare('SELECT Data FROM Settings WHERE Type = :Type LIMIT 1');
      $oStatement->bindParam(':Type', $this->sType);
      $oStatement->execute();
      $sSettings = $oStatement->fetchOne();

      if (empty($sSettings))
      {
        $this->hSettings = $this->defaultSettings();
        $sSettings = addslashes(serialize($this->hSettings));
        $oStatement = $this->oController->getDB()->prepare('INSERT INTO Settings (Type, Data) values (:Type, :Data)');
        $oStatement->bindParam(':Type', $this->sType);
        $oStatement->bindParam(':Data', $sSettings);
        $oStatement->execute();
      }
      else
      {
        $this->hSettings = unserialize(stripslashes($sSettings));
      }
    }

    $this->init();
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
   * Initialize this module's custom data, if there is any
   */
  protected function init()
  {
  }

  /**
   * Remove any ignored fields of the specified type from the specified data then return it
   *
   * @param string $sIgnoreType
   * @param array $hData
   * @return array
   */
  protected function removeIgnoredFields($sIgnoreType, $hData)
  {
    if (empty($this->aIgnore[$sIgnoreType]))
    {
      return $hData;
    }

    foreach ($this->aIgnore[$sIgnoreType] as $sField)
    {
      if (isset($hData[$sField]))
      {
        unset($hData[$sField]);
      }
    }

    return $hData;
  }

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    throw new \Exception("Action (dispaly) not implemented by {$this->oController->api->module}", 404);
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    throw new \Exception("Action (dispaly) not implemented by {$this->oController->api->module}", 404);
  }

  /**
   * Run the default "PUT" code and return the updated data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPut()
  {
    throw new \Exception("Action (update) not implemented by {$this->oController->api->module}", 404);
  }

  /**
   * Run the default "POST" code and return the created data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPost()
  {
    throw new \Exception("Action (create) not implemented by {$this->oController->api->module}", 404);
  }

  /**
   * Run the default "DELETE" code and return true
   *
   * @return boolean - True on success
   * @throws \Exception
   */
  protected function processApiDelete()
  {
    throw new \Exception("Action (delete) not implemented by {$this->oController->api->module}", 404);
  }

  /**
   * Process the current API call and return the appropriate data
   *
   * @return mixed
   * @throws \Exception
   */
  public function processApi()
  {
    http_response_code(200);

    if (!in_array($this->oController->api->method, static::$hHttpMethods))
    {
      throw new \Exception("HTTP method ({$this->oController->api->method}) not allowed by {$this->oController->api->module}", 405);
    }

    switch ($this->oController->api->method)
    {
      case 'head':
        if (!$this->allow('search'))
        {
          throw new \Exception("Action (dispaly) not allowed by {$this->oController->api->module}", 405);
        }

        return $this->processApiHead();

      case 'get':
        if (!$this->allow('search'))
        {
          throw new \Exception("Action (dispaly) not allowed by {$this->oController->api->module}", 405);
        }

        return $this->processApiGet();

      case 'put':
        if (!$this->allow('edit'))
        {
          throw new \Exception("Action (update) not allowed by {$this->oController->api->module}", 405);
        }

        return $this->processApiPut();

      case 'post':
        if (!$this->allow('create'))
        {
          throw new \Exception("Action (create) not allowed by {$this->oController->api->module}", 405);
        }

        http_response_code(201);
        return $this->processApiPost();

      case 'delete':
        if (!$this->allow('delete'))
        {
          throw new \Exception("Action (delete) not allowed by {$this->oController->api->module}", 405);
        }

        http_response_code(204);
        return $this->processApiDelete();

      case 'options':
        $sMethods = implode(',', static::$hHttpMethods);
        header('Allow: ' . strtoupper($sMethods));
        return null;
    }

    throw new \Exception("HTTP method ({$this->oController->api->method}) not recognized by {$this->oController->api->module}", 405);
  }

  /**
   * Is this module currently performing a search?
   *
   * @return boolean
   */
  public function isSearch()
  {
    return in_array($this->oController->api->action, ['search', 'list']);
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
    return static::$hSettingsFields;
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

  public function getHttpMethods()
  {
    return static::$hHttpMethods;
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

  /**
   * Process the posted settings for this module ad save them
   *
   * @throws Exception
   */
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
    $aMethods = [];
    $aMethods[] = 'prepareTemplate' . ucfirst($this->sCurrentAction) . ucfirst($this->oController->api->subAction);
    $aMethods[] = 'prepareTemplate' . ucfirst($this->sCurrentAction);
    $aMethods[] = 'prepareTemplate' . ucfirst($this->oController->api->method) . ucfirst($this->sCurrentAction) . ucfirst($this->oController->api->subAction);
    $aMethods[] = 'prepareTemplate' . ucfirst($this->oController->api->method) . ucfirst($this->sCurrentAction);
    $aMethods = array_unique($aMethods);

    foreach ($aMethods as $sMethod)
    {
      //run every template method can be found
      if (method_exists($this, $sMethod))
      {
        try
        {
          $this->$sMethod();
        }
        catch (\Exception $e)
        {
          $this->oController->templateData('failure', "Method ($sMethod) failed: " . $e->getMessage());

          //stop looking for a template method after an error occurs
          break;
        }
      }
    }

    $this->oController->templateData('module', $this);
    $this->oController->templateData('method', $this->sCurrentAction);
  }

  /**
   * Generate and return the path of the template to display
   *
   * @return boolean|string
   */
  public function getTemplate()
  {
    if (!$this->allow($this->sCurrentAction))
    {
      return false;
    }

    $sModuleDir = strtolower($this->getType());
    $sActionTemplate = $this->sCurrentAction == 'list' ? 'search' : strtolower("{$this->sCurrentAction}");
    $sMethod = $this->oController->api->method == 'post' || $this->sCurrentAction == 'list' ? 'process' : 'display';
    $aTemplates =
    [
      $sModuleDir . '/' . $sActionTemplate,
      $sModuleDir . '/' . $sMethod . $sActionTemplate,
      $sActionTemplate,
      $sMethod . $sActionTemplate,
    ];

    foreach ($aTemplates as $sTemplateName)
    {
      $sTemplateFile = $this->oController->templateFile($sTemplateName);

      if (empty($sTemplateFile))
      {
        continue;
      }

      return preg_match("/\.php$/", $sTemplateFile) ? $sTemplateFile : preg_replace("#^.*/templates/#", '', $sTemplateFile);
    }

    throw new \Exception("The action \"{$this->sCurrentAction}\" does *not* exist in {$this->sType}!!!");
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
   * Return the default settings
   *
   * @return array
   */
  protected function defaultSettings()
  {
    return [];
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

    $sSettings = addslashes(serialize($this->hSettings));
    $oStatement = $this->oController->getDB()->prepare('UPDATE Settings SET Data = :Data WHERE Type = :Type');
    $oStatement->bindParam(':Data', $sSettings);
    $oStatement->bindParam(':Type', $this->sType);
    $oStatement->execute();
    $this->bChangedSettings = false;
  }

  /**
   * Return the specified setting, if it exists
   *
   * @param string $sName
   * @return mixed
   */
  public function getSetting($sName=null)
  {
    if (count($this->hSettings) == 0)
    {
      return null;
    }

    if (empty($sName))
    {
      return $this->hSettings;
    }

    return $this->hSettings[strtolower($sName)] ?? null;
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
    $sLowerName = strtolower($sName);

    if (!isset(static::$hSettingsFields[$sLowerName]))
    {
      return false;
    }

    $this->bChangedSettings = true;
    $this->hSettings[$sLowerName] = $xValue;
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
    if ($sMenuItem == 'list')
    {
      return 'search';
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
   * @param boolean $bOnlyUserAllowed (optional) - Should the returned array only contain items that the current user has access to?
   * @return array
   */
  public function getSubMenuItems($bOnlyUserAllowed = false)
  {
    if ($bOnlyUserAllowed)
    {
      $hSubMenuItems = [];

      foreach ($this->hSubMenuItems as $sMenuAction => $sMenuTitle)
      {
        if ($this->allow($sMenuAction))
        {
          $hSubMenuItems[$sMenuAction] = $sMenuTitle;
        }
      }

      return $hSubMenuItems;
    }

    return $this->hSubMenuItems;
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

  public function getCurrentAction()
  {
    return $this->sCurrentAction;
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
        $sDisplay .= "<span class=\"OmnisysSortGridEdit\" onClick=\"document.getElementById('Omnisys_SortGrid_Edit').value='$sColumn'; document.getElementById('EditColumn').submit();\">[Edit]</span>";
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
    $sURL = $this->generateUri($iID);
    return "<input type=\"checkbox\" class=\"OmnisysSortGridCellCheckbox\" name=\"{$sIDColumn}[$iID]\" id=\"{$sIDColumn}[$iID]\" value=\"1\"> [<a class=\"item\" href=\"$sURL\">View</a>]";
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
        $oDate = $this->oController->widgetFactory('Calendar', "$this->sType[$sName]");
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
      $sItemDriver = Item::driver($aMatch[1]);

      //if there is a driver, then use the match otherwise use the original column
      return empty($sItemDriver) ? $sColumn : $aMatch[1];
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
  public function getFormFields($hFields, $hValues = [])
  {
    if (!is_array($hFields))
    {
      return '';
    }

    $sFormFields = '';

    foreach ($hFields as $sName => $hData)
    {
      $sValue = $hValues[$sName] ?? null;
      $sFormFields .= $this->getFormField($sName, $sValue, $hData);
    }

    return $sFormFields;
  }

  /**
   * Echo the form generated by the specified data
   *
   * @param string $sType
   * @param array $hFields
   * @param array $hValues
   */
  public function getForm($sType, $hFields, $hValues)
  {
    $sButtonValue = ucwords($sType);
    $sType = preg_replace('/ /', '', $sType);
    return "<form name=\"$sType\" action=\"" . $this->generateUri($sType) . "\" method=\"post\">
" . $this->getFormFields($hFields, $hValues) . "
<div class=\"field\"><span class=\"blankLabel\"></span><span><button type=\"submit\">$sButtonValue</button></span></div>
</form>\n";
  }

  /**
   * Return the HTML needed to display the specified edit dialog box
   *
   * @param type $sText
   * @param type $sButtonName
   * @return string
   */
  protected function editDialog($sText, $sButtonName)
  {
    $sVerb = isset($_SESSION['EditData']['Delete']) ? 'Delete' : 'Edit Column';
    $sContent = "<form name=\"EditColumn\" action=\"" . $this->generateUri('editcolumn') . "\" method=\"post\">\n";
    $sContent .= $sText;
    $sContent .= "<button type=\"submit\" name=\"$sButtonName\">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type=\"submit\" name=\"No\">No</button>";
    $sContent .= "</form>\n";
    return \Omniverse\Controller\Admin::getMenu($sContent, $this->getTitle() . " :: $sVerb");
  }
}