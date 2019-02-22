<?php
namespace Limbonia;

/**
 * Limbonia base Controller Class
 *
 * The controller
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
abstract class Controller
{
  const SETTINGS_NAME_ACTIVE_MODULES = 'ActiveModules';

  /**
   * The config that comes from external sources
   *
   * @var array
   */
  protected static $hAutoConfig = [];

    /**
   * The current default Controller
   *
   * @var \Limbonia\Controller
   */
  protected static $oDefaultController = null;

  /**
   * @var \DateTime $oBuildDate -
   */
  protected static $oBuildDate = null;

  /**
   * @var string $sBuildVersion -
   */
  protected static $sBuildVersion = '0.0.0';

  /**
   * The format for timestamps
   *
   * @var string
   */
  protected static $sTimeStampFormat = "G:i:s M j Y";

  /**
   * List of Limbonia lib directories
   *
   * @var array
   */
  protected static $aLibList = [__DIR__];

  /**
   * List of Limbonia lib directories
   *
   * @var array
   */
  protected static $aTemplateDir = [__DIR__ . '/Template'];

  /**
   * The list of input types that are allowed to be auto generated
   *
   * @var array
   */
  protected static $aAutoInput = ['get', 'post', 'server'];

  /**
   * List of currently instantiated modules
   *
   * @var array
   */
  protected static $hModuleList = [];

  /**
   * The list of currently available modules
   *
   * @var array
   */
  protected static $hAvailableModule = null;

  /**
   * The list of currently active modules
   *
   * @var array
   */
  protected static $hActiveModule = null;

  /**
   * The list of modules allowed for the current user
   *
   * @var array
   */
  protected static $hAllowedModule = null;

  /**
   * List of controller types that are based on the web controller
   *
   * @var array
   */
  const WEB_TYPES =
  [
    'admin',
    'ajax',
    'api',
    'web'
  ];

  /**
   * All the data that will be used by the templates
   *
   * @var array
   */
  protected $hTemplateData = [];

  /**
   * @var \Limbonia\Domain - The default domain for this controller instance
   */
  protected $oDomain = null;

  /**
   * List of database objects
   *
   * @var array
   */
  protected $hDatabaseList = [];

  /**
   * List of database configuration settings
   *
   * @var array
   */
  protected $hDatabaseConfig = [];

  /**
   * List of configured directories
   *
   * @var array
   */
  protected $hDirectories =
  [
    'root' => '',
    'libs' => []
  ];

  /**
   * List of default Item type names and what they should default to
   *
   * @var array
   */
  protected $hItemTypeDefaults = [];

  protected $aDefaultActiveModules =
  [
    'system',
    'zipcode',
    'resourcekey',
    'resourcelock',
    'role',
    'user',
    'auth',
    'profile'
  ];
  /**
   * List of configuration data
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * The logged in user
   *
   * @var \Limbonia\Item\User
   */
  protected $oUser = null;

  /**
   * The type of controller that has been instantiated
   *
   * @var string
   */
  protected $sType = '';

  /**
   * Is this controller running in debug mode?
   *
   * @var boolean
   */
  protected $bDebug = false;

  /**
   * This Controller's router, if there is one
   *
   * @var \Limbonia\Router
   */
  protected $oRouter = null;

  /**
   * Generate the build data so it can be used in other places
   */
  protected static function generateBuildData()
  {
    if (\is_null(self::$oBuildDate))
    {
      $sVersionFile = __DIR__ . DIRECTORY_SEPARATOR . 'version';

      if (is_file($sVersionFile))
      {
        self::$oBuildDate = new \DateTime('@' . filemtime($sVersionFile));
        self::$sBuildVersion = trim(file_get_contents($sVersionFile));
      }
    }
  }

  /**
   * Is the CLI running?
   *
   * @return boolean
   */
  public static function isCLI()
  {
    return preg_match("/cli/i", PHP_SAPI);
  }

  /**
   * Is this running from the web?
   *
   * @return boolean
   */
  public static function isWeb()
  {
    return !self::isCLI();
  }

  /**
   * return the correct EOL for the current environment.
   *
   * @return string
   */
  public static function eol()
  {
    return self::isCLI() ? "\n" : "<br>\n";
  }

  /**
   * Return the build date of the current release of Limbonia.
   *
   * @param string $sFormat (optional) - Override the default format with this one, if it's is used
   */
  public static function buildDate($sFormat = '')
  {
    self::generateBuildData();
    return self::$oBuildDate->format(empty($sFormat) ? 'r' : $sFormat);
  }

  /**
   * Set all controllers to use the specified format as the default format for timestamps
   *
   * @param string $sNewFormat
   */
  public static function setTimeStampFormat($sNewFormat = NULL)
  {
    self::$sTimeStampFormat = empty($sNewFormat) ? 'r' : $sNewFormat;
  }

  /**
   * Format and return the specified UNIX timestamp using the default format
   *
   * @param integer $iTimeStamp
   * @param string $sFormat (optional) - Override the default format with this one, if it's is used
   * @return string
   */
  public static function formatTime($iTimeStamp, $sFormat = '')
  {
    $oTime = new \DateTime('@' . (integer)$iTimeStamp);
    $sFormat = empty($sFormat) ? self::$sTimeStampFormat : $sFormat;
    return $oTime->format($sFormat);
  }

  /**
   * Generate and return the current time in the default format
   *
   * @param string $sFormat (optional) - Override the default format with this one, if it's is used
   * @return string
   */
  public static function timeStamp($sFormat = NULL)
  {
    return self::formatTime(time(), $sFormat);
  }

  /**
   * Return the version number of the current release of Limbonia.
   *
   * @return string
   */
  public static function version()
  {
    self::generateBuildData();
    return self::$sBuildVersion;
  }

  /**
   * Set the default controller for this PHP instance
   *
   * @param Controller $oController
   */
  public static function setDefault(self $oController)
  {
    self::$oDefaultController = $oController;
  }

  /**
   * Return the default controller for this PHP instance
   *
   * @return Controller
   */
  public static function getDefault()
  {
    return self::$oDefaultController;
  }

  /**
   * Flatten the specified variable into a string and return it...
   *
   * @param mixed $xData
   * @return string
   */
  public static function flatten($xData)
  {
    ob_start();
    var_dump($xData);
    return ob_get_flush();
  }

  /**
   * Add a new Limbonia library to the current list
   *
   * @param string $sLibDir - The root directory to the Limbonia library to add
   */
  public static function addLib($sLibDir)
  {
    if (is_dir($sLibDir) && !in_array($sLibDir, self::$aLibList))
    {
      array_unshift(self::$aLibList, $sLibDir);
      array_unshift(self::$aTemplateDir, "$sLibDir/Template");
    }
  }

  /**
   * Return the list of Limbonia libraries
   *
   * @return array
   */
  public static function getLibs()
  {
    return self::$aLibList;
  }

  /**
   * Return the list of template directories
   *
   * @return string
   */
  public static function templateDirs()
  {
    return self::$aTemplateDir;
  }

  /**
   * Find and return the home directory of the current user
   *
   * @return string
   */
  public static function getHomeDir()
  {
    if (isset($_SERVER['HOME']))
    {
      return $_SERVER['HOME'];
    }

    $sHome = getenv('HOME');

    if (!empty($sHome))
    {
      return $sHome;
    }

    $hUser = posix_getpwuid(posix_getuid());
    return $hUser['dir'];
  }

  /**
   * Merge two arrays recursively and return it
   *
   * @param array $hOriginal
   * @param array $hOverride
   * @return array
   */
  public static function mergeArray(array $hOriginal, array $hOverride)
  {
    $hMerge = $hOriginal;

    foreach ($hOverride as $sKey => $xValue)
    {
      if (isset($hOriginal[$sKey]))
      {
        if (is_array($xValue) && is_array($hOriginal[$sKey]))
        {
          $hMerge[$sKey] = self::mergeArray($hOriginal[$sKey], $xValue);
        }
        else
        {
          $hMerge[$sKey] = $hOverride[$sKey];
        }
      }
      else
      {
        $hMerge[$sKey] = $xValue;
      }
    }

    return $hMerge;
  }

  /**
   * Add a new hash to the default config
   *
   * @param array $hNewConfig
   */
  public static function addAutoConfig(array $hNewConfig = [])
  {
    self::$hAutoConfig = self::mergeArray(self::$hAutoConfig, $hNewConfig);
  }

  /**
   * Generate and return a valid, configured controller
   *
   * @param array $hConfig
   * @return \Limbonia\Controller
   * @throws \Exception
   */
  public static function factory(array $hConfig = [])
  {
    if (is_file('/etc/limbonia/config.php'))
    {
      require_once '/etc/limbonia/config.php';
    }

    $sHome = \Limbonia\Controller::getHomeDir();
    $sConfigFile = "$sHome/.limbonia/config.php";

    if (is_file($sConfigFile))
    {
      require_once $sConfigFile;
    }

    $hConfig = self::mergeArray(self::$hAutoConfig, $hConfig);
    $hLowerConfig = \array_change_key_case($hConfig, CASE_LOWER);
    $sControllerType = null;

    if (isset($hLowerConfig['controller_type']))
    {
      $sControllerType = strtolower($hLowerConfig['controller_type']);
      unset($hLowerConfig['controller_type']);
    }

    if (self::isCLI() || $sControllerType == 'cli')
    {
      return new Controller\Cli($hLowerConfig);
    }

    if (!in_array($sControllerType, self::WEB_TYPES))
    {
      $oServer = Input::singleton('server');
      $sBaseUrl = rtrim(dirname($oServer['php_self']), '/') . '/';
      $sRawPath = rtrim(preg_replace("#\?.*#", '', preg_replace("#^" . $sBaseUrl . "#",  '', $oServer['request_uri'])), '/');
      $aCall = explode('/', strtolower($sRawPath));
      $sControllerType = isset($aCall[0]) && in_array($aCall[0], self::WEB_TYPES) ? $aCall[0] : 'web';
    }

    $sWebControllerClass = __CLASS__ . '\\' . ucfirst($sControllerType);
    return new $sWebControllerClass($hLowerConfig);
  }

  /**
   * The controller constructor
   *
   * NOTE: This constructor should only be used by the factory and *never* directly
   *
   * @param array $hConfig - A hash of configuration data
   */
  protected function __construct(array $hConfig = [])
  {
    if (isset($hConfig['debug']))
    {
      $this->bDebug = (boolean)$hConfig['debug'];
    }

    $this->sType = strtolower(str_replace(__CLASS__ . "\\", '', get_class($this)));

    if (isset($hConfig['domaindirtemplate']))
    {
      Domain::setDirTemplate($hConfig['domaindirtemplate']);
      unset($hConfig['domaindirtemplate']);
    }

    if (isset($hConfig['domain']))
    {
      if ($hConfig['domain'] instanceof \Limbonia\Domain)
      {
        $this->oDomain = $hConfig['domain'];
      }
      elseif (is_string($hConfig['domain']))
      {
        $this->oDomain = \Limbonia\Domain::factory($hConfig['domain']);
      }

      unset($hConfig['domain']);
    }

    $this->hConfig['baseuri'] = $this->oDomain ? $this->oDomain->uri : '';
    $this->hDirectories['root'] = \dirname(__DIR__);

    if (isset($hConfig['directories']))
    {
      foreach ($hConfig['directories'] as $sName => $sDir)
      {
        $this->hDirectories[\strtolower($sName)] = $sDir;
      }

      unset($hConfig['directories']);
    }

    $sTemplateDir = $this->getDir('template');

    if (is_readable($sTemplateDir) && !in_array($sTemplateDir, self::$aTemplateDir))
    {
      array_unshift(self::$aTemplateDir, $sTemplateDir);
    }

    $sTimeZone = 'UTC';

    if (isset($hConfig['timezone']))
    {
      $sTimeZone = $hConfig['timezone'];
      unset($hConfig['timezone']);
    }

    date_default_timezone_set($sTimeZone);

    if (isset($hConfig['database']) && count($hConfig['database']) > 0)
    {
      foreach ($hConfig['database'] as $sName => $hDatabase)
      {
        $this->hDatabaseConfig[\strtolower($sName)] = array_change_key_case($hDatabase, CASE_LOWER);
      }

      unset($hConfig['database']);
    }

    if (isset($hConfig['itemtypedefaults']))
    {
      $this->hItemTypeDefaults = $hConfig['itemtypedefaults'];
      unset($hConfig['itemtypedefaults']);
    }

    if (isset($hConfig['defaultactivemodules']))
    {
      $this->aDefaultActiveModules = $hConfig['defaultactivemodules'];
      unset($hConfig['defaultactivemodules']);
    }

    $this->hConfig = array_merge($this->hConfig, $hConfig);

    if (\is_null(self::$oDefaultController))
    {
      self::setDefault($this);
    }
  }

  /**
   * Magic method used to set the specified property to the specified value
   *
   * @note Settings should not be changed so this method does nothing...
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
    //don't allow public setting of anything
  }

  /**
   * Magic method used to generate and return the specified property
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    $sLowerName = strtolower($sName);

    if (in_array($sLowerName, self::$aAutoInput))
    {
      return \Limbonia\Input::singleton($sLowerName);
    }

    if ($sLowerName == 'domain')
    {
      return $this->oDomain;
    }

    if ($sLowerName == 'type')
    {
      return $this->sType;
    }

    if ($sLowerName == 'debug')
    {
      return $this->bDebug;
    }

    if (preg_match("#^(.+?)dir$#", $sLowerName, $aMatch))
    {
      return $this->getDir($aMatch[1]);
    }

    if (isset($this->hConfig[$sLowerName]))
    {
      return $this->hConfig[$sLowerName];
    }
  }

  /**
   * Magic method used to determine if the specified property is set
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    $sLowerName = strtolower($sName);

    if (in_array($sLowerName, self::$aAutoInput))
    {
      return true;
    }

    if ($sLowerName === 'domain')
    {
      return !empty($this->oDomain);
    }

    if ($sLowerName == 'type' || $sLowerName == 'debug')
    {
      return true;
    }

    if (preg_match("#^(.+?)dir$#", $sLowerName))
    {
      return true;
    }

    return isset($this->hConfig[$sLowerName]);
  }

  /**
   * Magic method used to remove the specified property
   *
   * @note Settings should not be unset so this method does nothing...
   *
   * @param string $sName
   */
  public function __unset($sName)
  {
    //don't allow public unsetting of anything
  }

  /**
   * Run basic system setup
   */
  public function setup()
  {
    $oDatabase = $this->getDB();

    //create the settings table

    echo "Initialize Settings:";
    $oDatabase->createTable('Settings', "Type VARCHAR(255) NOT NULL,
Data TEXT NULL,
PRIMARY KEY(Type)");

    //activate the default modules
    echo "Initialize Default Modules:";

    foreach ($this->aDefaultActiveModules as $sModule)
    {

    }

    $this->activateModule('system');
    $this->activateModule('zipcode');
    $this->activateModule('resourcekey');
    $this->activateModule('resourcelock');
    $this->activateModule('role');
    $this->activateModule('user');
    $this->activateModule('auth');
    $this->activateModule('profile');
  }

  /**
   * Generate and return a database object based on the specified database config section
   *
   * @param string $sSection (optional)
   * @throws \Limbonia\Exception\Database
   * @return \Limbonia\Database
   */
  public function getDB($sSection = 'default')
  {
    if (empty($this->hDatabaseConfig))
    {
      throw new Exception\Database("Database not configured");
    }

    if (empty($sSection) || !isset($this->hDatabaseConfig[$sSection]))
    {
      $sSection = 'default';
    }

    if (!isset($this->hDatabaseList[$sSection]))
    {
      if (!isset($this->hDatabaseConfig[$sSection]))
      {
        throw new Exception\Database("Database default not configured");
      }

      $this->hDatabaseList[$sSection] = Database::factory($this->hDatabaseConfig[$sSection], $this);
    }

    return $this->hDatabaseList[$sSection];
  }

  /**
   * Return the Domain object that is associated with this Controller, if there is one
   *
   * @return \Limbonia\Domain
   */
  public function getDomain()
  {
    return $this->oDomain;
  }

  /**
   * Get the specified directory via several different means
   *
   * @param string $sDirName
   * @return string
   */
  public function getDir($sDirName)
  {
    $sDirName = \strtolower($sDirName);

    //Check to see if it's specifiacally configured
    if (isset($this->hDirectories[$sDirName]))
    {
      return $this->hDirectories[$sDirName];
    }

    //Check to see if it exists in the configured "Custom" directory
    if (isset($this->hDirectories['custom']))
    {
      if (is_dir($this->hDirectories['custom'] . DIRECTORY_SEPARATOR . $sDirName))
      {
        return $this->hDirectories['custom'] . DIRECTORY_SEPARATOR . $sDirName;
      }
    }

    //Check to see if it exists relative to the current path
    $sTemp = realpath($sDirName);

    if ($sTemp)
    {
      return $sTemp;
    }

    //is this the temp directory
    if ($sDirName == 'temp')
    {
      return '/tmp';
    }

    //if all else fails then use the current directory
    return '';
  }

  /**
   * Generate and return the URI for the specified parameters
   *
   * @param string ...$aParam (optional)
   * @return string
   */
  public function generateUri(string ...$aParam): string
  {
    $aUri = array_merge([$this->baseUri], $aParam);
    return strtolower(implode('/', $aUri));
  }

  /**
   * Save the specified settings for the specified type to the database
   *
   * @param string $sType
   * @param array $hSettings
   * @return boolean - True on success or false on failure
   */
  public function saveSettings($sType, array $hSettings = [])
  {
    $oStatement = $this->getDB()->prepare('INSERT INTO Settings (Type, Data) VALUES (:Type, :Data) ON DUPLICATE KEY UPDATE Data = :Data');
    return $oStatement->execute
    ([
      ':Type' => $sType,
      ':Data' => addslashes(serialize($hSettings))
    ]);
  }

  /**
   * Return settings of the specified type
   *
   * @param string $sType
   * @return array
   * @throws \Exception
   */
  public function getSettings($sType)
  {
    $oStatement = $this->getDB()->prepare('SELECT Data FROM Settings WHERE Type = :Type LIMIT 1');
    $oStatement->bindParam(':Type', $sType);

    if (!$oStatement->execute())
    {
      $aError = $oStatement->errorInfo();
      throw new \Exception("Failed to get settings for $sType: " . $aError[2]);
    }

    $sSettings = $oStatement->fetchColumn();
    return empty($sSettings) ? [] : unserialize(stripslashes($sSettings));
  }

  /**
   * Return the default for the specified type
   *
   * @param string $sType
   * @return striing
   */
  public function defaultItemType($sType)
  {
    $sLowerType = strtolower($sType);
    return isset($this->hItemTypeDefaults[$sLowerType]) ? $this->hItemTypeDefaults[$sLowerType] : $sType;
  }

  /**
   * Add the specified data to the template under the specified name
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function templateData($sName, $xValue)
  {
    $this->hTemplateData[$sName] = $xValue;
  }

  /**
   * Render and return specified template
   *
   * @param string $sTemplateName
   * @return string The rendered template
   */
  public function templateRender($sTemplateName)
  {
    $sTemplateFile = $this->templateFile($sTemplateName);

    if (empty($sTemplateFile))
    {
      return '';
    }

    ob_start();
    $this->templateInclude($sTemplateFile);
    return ob_get_clean();
  }

  /**
   * Return the full file path of the specified template, if it exists
   *
   * @param string $sTemplateName
   * @return string
   */
  public function templateFile($sTemplateName)
  {
    if (empty($sTemplateName))
    {
      return '';
    }

    if (is_readable($sTemplateName))
    {
      return $sTemplateName;
    }

    foreach (self::templateDirs() as $sLib)
    {
      $sFilePath = $sLib . '/' . $this->sType . '/' .$sTemplateName;

      if (is_readable($sFilePath))
      {
        return $sFilePath;
      }

      if (is_readable("$sFilePath.php"))
      {
        return "$sFilePath.php";
      }

      if (is_readable("$sFilePath.html"))
      {
        return "$sFilePath.html";
      }
    }

    return '';
  }

  /**
   * Find then include the specified template if it's found
   *
   * @param srtring $sTemplateName
   */
  protected function templateInclude($sTemplateName)
  {
    $sTemplateFile = $this->templateFile($sTemplateName);

    if ($sTemplateFile)
    {
      extract($this->hTemplateData);
      include $sTemplateFile;
    }
  }

  /**
   * Generate and return a cache object
   *
   * @param string $sCacheDir (optional)- The directory the cache object will use, if empty it will default to the controller's cache directory
   * @return \Limbonia\Cache
   */
  public function cacheFactory($sCacheDir = null)
  {
    $sCacheDir = $sCacheDir ?? $this->cacheDir;
    return \Limbonia\Cache::factory($sCacheDir);
  }

  /**
   * Generate and return an empty item object based on the specified table.
   *
   * @param string $sType
   * @return \Limbonia\Item
   */
  public function itemFactory($sType): \Limbonia\Item
  {
    $oItem = Item::factory($this->defaultItemType($sType), $this->getDB());
    $oItem->setController($this);
    return $oItem;
  }

  /**
   * Generate and return an item object filled with data from the specified table id
   *
   * @param string $sType
   * @param integer $iItem
   * @throws \Limbonia\Exception\Database
   * @return \Limbonia\Item
   */
  public function itemFromId($sType, $iItem): \Limbonia\Item
  {
    $oItem = Item::fromId($this->defaultItemType($sType), $iItem, $this->getDB());
    $oItem->setController($this);
    return $oItem;
  }

  /**
   * Generate and return an item object filled with data from the specified array
   *
   * @param string $sType
   * @param array $hItem
   * @return \Limbonia\Item
   * @throws \Limbonia\Exception\Object
   */
  public function itemFromArray($sType, $hItem): \Limbonia\Item
  {
    $oItem = Item::fromArray($this->defaultItemType($sType), $hItem, $this->getDB());
    $oItem->setController($this);
    return $oItem;
  }

  /**
   * Generate an item list based on the specified type and SQL query
   *
   * @param string $sType
   * @param string $sQuery
   * @return \Limbonia\ItemList
   */
  public function itemList($sType, $sQuery): \Limbonia\ItemList
  {
    $oList = Item::getList($this->defaultItemType($sType), $sQuery, $this->getDB());
    $oList->setController($this);
    return $oList;
  }

  /**
   * Generate an item list based on the specified type and search criteria
   *
   * @param string $sType
   * @param array $hWhere
   * @param mixed $xOrder
   * @return \Limbonia\ItemList
   */
  public function itemSearch($sType, $hWhere = null, $xOrder = null)
  {
    $oList = Item::search($this->defaultItemType($sType), $hWhere, $xOrder, $this->getDB());
    $oList->setController($this);
    return $oList;
  }

  /**
   * Generate and return an empty item object based on the specified table.
   *
   * @param string $sType
   * @param string $sName (optional) - The name to give the widget when it is instantiated
   * @return \Limbonia\Widget - The requested \Limbonia\Widget on success, otherwise FALSE.
   */
  public function widgetFactory($sType, $sName = null)
  {
    return Widget::factory($sType, $sName, $this);
  }

  /**
   * Generate and return the module of the specified type
   *
   * @param string $sType
   * @return \Limbonia\Module
   */
  public function moduleFactory($sType)
  {
    $sDriver = Module::driver($sType);

    if (!isset(self::$hModuleList[$sDriver]))
    {
      self::$hModuleList[$sDriver] = Module::factory($sType, $this);
    }

    return self::$hModuleList[$sDriver];
  }

  /**
   * Generate and return a Report object of the specified type
   *
   * @param string $sType
   * @param array $hParam (optional)
   * @return \Limbonia\Report
   */
  public function reportFactory($sType, array $hParam = []): \Limbonia\Report
  {
    return \Limbonia\Report::factory($sType, $hParam, $this);
  }

  /**
   * Generate a report, run it then return the result
   *
   * @param string $sType The type of report to get a result from
   * @param array $hParam (optional) List of report parameters to set before running the report
   * @return \Limbonia\Interfaces\Result
   * @throws \Limbonia\Exception\Object
   */
  public function reportResultFactory($sType, array $hParam = [])
  {
    return \Limbonia\Report::resultFactory($sType, $hParam, $this);
  }

  /**
   * Return the default router
   *
   * @return \Limbonia\Router
   */
  public function getRouter()
  {
    if (empty($this->oRouter))
    {
      $this->oRouter = \Limbonia\Router::singleton();
    }

    return $this->oRouter;
  }

  public function availableModules()
  {
    if (is_null(self::$hAvailableModule))
    {
      $hDriverList = \Limbonia\Module::driverList();
      $aBlackList = $this->moduleBlackList ?? [];

      foreach ($hDriverList as $sDriver)
      {
        if (in_array($sDriver, $aBlackList))
        {
          continue;
        }

        self::$hAvailableModule[strtolower($sDriver)] = $sDriver;
      }

      ksort(self::$hAvailableModule);
      reset(self::$hAvailableModule);
    }

    return self::$hAvailableModule;
  }

  public function activeModules()
  {
    if (is_null(self::$hActiveModule))
    {
      self::$hActiveModule = $this->getSettings(self::SETTINGS_NAME_ACTIVE_MODULES);
    }

    return self::$hActiveModule;
  }

  public function activateModule($sModule)
  {
    if (empty($sModule))
    {
      throw new Exception("Module driver not specified");
    }

    $sDriver = Module::driver($sModule);

    if (empty($sDriver))
    {
      throw new Exception("Module driver not found: $sModule");
    }

    $sLowerDriver = strtolower($sDriver);
    $hActiveModule = $this->activeModules();

    //if this module type is already one of the active modules
    if (isset($hActiveModule[$sLowerDriver]))
    {
      //then fail
      throw new Exception("That module is already active");
    }

    $oModule = $this->moduleFactory($sDriver);

    foreach ($oModule->activate($hActiveModule) as $sActivedDriver)
    {
      self::$hActiveModule[strtolower($sActivedDriver)] = $sActivedDriver;
    }

    if (!$this->saveSettings(self::SETTINGS_NAME_ACTIVE_MODULES, self::$hActiveModule))
    {
      throw new Exception("Failed to save new acitive module list");
    }
  }

  public function deactivateModule($sModule)
  {
    if (empty($sModule))
    {
      throw new Exception("Module driver not specified");
    }

    $sDriver = Module::driver($sModule);

    if (empty($sDriver))
    {
      throw new Exception("Module driver not found: $sModule");
    }

    $sLowerDriver = strtolower($sDriver);
    $hActiveModule = $this->activeModules();

    //if this module type is not one of the active modules
    if (!isset($hActiveModule[$sLowerDriver]))
    {
      //then fail
      throw new Exception("The $sDriver module is already inactive");
    }

    $oModule = $this->moduleFactory($sDriver);

    foreach ($oModule->deactivate($hActiveModule) as $sDeactivedDriver)
    {
      unset(self::$hActiveModule[strtolower($sDeactivedDriver)]);
    }

    if (!$this->saveSettings(self::SETTINGS_NAME_ACTIVE_MODULES, self::$hActiveModule))
    {
      throw new Exception("Failed to save new acitive module list");
    }
  }

  public function allowedModules()
  {
    if (is_null(self::$hAllowedModule))
    {
      $hDriverList = \Limbonia\Module::driverList();

      foreach ($hDriverList as $sDriver)
      {
        if (!$this->oUser->hasResource($sDriver))
        {
          continue;
        }

        self::$hAllowedModule[strtolower($sDriver)] = $sDriver;
      }

      ksort(self::$hAllowedModule);
      reset(self::$hAllowedModule);
    }

    return self::$hAllowedModule;
  }

  /**
   * Return the user represented by the specified email, if there is one
   *
   * @param string $sEmail
   * @return \Limbonia\Item\User
   */
  public function userByEmail($sEmail)
  {
    $oUser = \Limbonia\Item\User::getByEmail($sEmail, $this->getDB());
    $oUser->setController($this);
    return $oUser;
  }

  /**
   * Return a default admin User object
   *
   * @return \Limbonia\Item\User
   */
  public function userAdmin()
  {
    $oUser = \Limbonia\Item\User::getAdmin();
    $oUser->setController($this);
    return $oUser;
  }

  /**
   * Return the currently logged in user
   *
   * @return \Limbonia\Item\User
   */
  public function user()
  {
    return $this->oUser;
  }

  /**
   * Generate and return the current user
   *
   * @return \Limbonia\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    return $this->userAdmin();
  }

  /**
   * Run everything needed to react to input and display data in the way this controller is intended
   */
  public function run()
  {
    $this->oUser = $this->generateUser();
  }
}
