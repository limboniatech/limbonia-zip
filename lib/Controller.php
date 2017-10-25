<?php
namespace Omniverse;

/**
 * Omniverse base Controller Class
 *
 * The controller
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
abstract class Controller
{
  /**
   * The current default Controller
   *
   * @var \Omniverse\Controller
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
   * List of Omniverse lib directories
   *
   * @var array
   */
  protected static $aLibList = [__DIR__];

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
   * @var \Omniverse\Domain - The default domain for this controller instance
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
  protected $hDatabaseConfig =
  [
    'default' =>
    [
      'driver' => '',
      'database' => '',
      'user' => '',
      'password' => ''
    ]
  ];

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
   * List of configuration data
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * This controller's API
   *
   * @var \Omniverse\Api
   */
  protected $oApi = null;

  /**
   * The logged in user
   *
   * @var \Omniverse\Item\User
   */
  protected $oUser = null;

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
        self::$iBuildDate = new \DateTime('@' . filemtime($sVersionFile));
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
   * Return the build date of the current release of Omniverse.
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
   * Return the version number of the current release of Omniverse.
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
   * Add a new Omniverse library to the current list
   *
   * @param string $sLibDir - The root directory to the Omniverse library to add
   */
  public static function addLib($sLibDir)
  {
    if (is_dir($sLibDir) && !in_array($sLibDir, self::$aLibList))
    {
      self::$aLibList[] = $sLibDir;
    }
  }

  /**
   * Return the list of Omniverse libraries
   *
   * @return array
   */
  public static function getLibs()
  {
    return self::$aLibList;
  }

  /**
   * Generate and return a valid, configured controller
   *
   * @param array $hConfig
   * @return \Omniverse\Controller
   * @throws \Exception
   */
  public static function factory(array $hConfig = [])
  {
    if (self::isCLI())
    {
      return new \Omniverse\Controller\Cli($hConfig);
    }

    $oApi = \Omniverse\Api::singleton();
    $sControllerClass = __CLASS__ . '\\' . ucfirst($oApi->controller);
    return new $sControllerClass($hConfig);
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
    $hLowerConfig = \array_change_key_case($hConfig, CASE_LOWER);

    if (isset($hLowerConfig['domaindirtemplate']))
    {
      Domain::setDirTemplate($hLowerConfig['domaindirtemplate']);
      unset($hLowerConfig['domaindirtemplate']);
    }

    if (isset($hLowerConfig['domain']))
    {
      if ($hLowerConfig['domain'] instanceof \Omniverse\Domain)
      {
        $this->oDomain = $hLowerConfig['domain'];
      }
      elseif (is_string($hLowerConfig['domain']))
      {
        $this->oDomain = \Omniverse\Domain::factory($hLowerConfig['domain']);
      }

      unset($hLowerConfig['domain']);
    }

    $this->hConfig['baseuri'] = $this->oDomain ? $this->oDomain->uri : '';
    $this->hDirectories['root'] = \dirname(__DIR__);

    if (isset($hLowerConfig['directories']))
    {
      foreach ($hLowerConfig['directories'] as $sName => $sDir)
      {
        $this->hDirectories[\strtolower($sName)] = $sDir;
      }

      unset($hLowerConfig['directories']);
    }

    $this->hDirectories['libs'] = \array_unique($this->hDirectories['libs']);

    $sTimeZone = 'UTC';

    if (isset($hLowerConfig['timezone']))
    {
      $sTimeZone = $hLowerConfig['timezone'];
      unset($hLowerConfig['timezone']);
    }

    date_default_timezone_set($sTimeZone);

    if (isset($hLowerConfig['database']) && count($hLowerConfig['database']) > 0)
    {
      foreach ($hLowerConfig['database'] as $sName => $hDatabase)
      {
        $this->hDatabaseConfig[\strtolower($sName)] = array_change_key_case($hDatabase, CASE_LOWER);
      }

      unset($hLowerConfig['database']);
    }

    if (isset($hLowerConfig['moduleblacklist']) && is_array($hLowerConfig['moduleblacklist']))
    {
      \Omniverse\Module::generateDriverList($hLowerConfig['moduleblacklist']);
    }

    $this->hConfig = array_merge($this->hConfig, $hLowerConfig);

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
      return \Omniverse\Input::singleton($sLowerName);
    }

    if ($sLowerName == 'api')
    {
      return $this->oApi;
    }

    if ($sLowerName == 'domain')
    {
      return $this->oDomain;
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

    if ($sLowerName === 'api')
    {
      return !empty($this->oApi);
    }

    if ($sLowerName === 'domain')
    {
      return !empty($this->oDomain);
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
   * Generate and return a database object based on the specified database config section
   *
   * @param string $sSection (optional)
   * @return \Omniverse\Database
   */
  public function getDB($sSection = 'default')
  {
    if (empty($sSection) || !isset($this->hDatabaseConfig[$sSection]))
    {
      $sSection = 'default';
    }

    if (!isset($this->hDatabaseList[$sSection]))
    {
      $this->hDatabaseList[$sSection] = Database::factory($this->hDatabaseConfig[$sSection], $this);
    }

    return $this->hDatabaseList[$sSection];
  }

  /**
   * Return the Domain object that is associated with this Controller, if there is one
   *
   * @return Domain
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
    return '.';
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
   * Generate and return a cache object
   *
   * @param string $sCacheDir (optional)- The directory the cache object will use, if empty it will default to the controller's cache directory
   * @return \Omniverse\Cache
   */
  public function cacheFactory($sCacheDir = null)
  {
    $sCacheDir = $sCacheDir ?? $this->cacheDir;
    return \Omniverse\Cache::factory($sCacheDir);
  }

  /**
   * Generate and return an empty item object based on the specified table.
   *
   * @param string $sTable
   * @return \Omniverse\Item
   */
  public function itemFactory($sTable)
  {
    return Item::factory($sTable, $this->getDB());
  }

  /**
   * Generate and return an item object filled with data from the specified table id
   *
   * @param string $sTable
   * @param integer $iItem
   * @return \Omniverse\Item
   */
  public function itemFromId($sTable, $iItem)
  {
    return Item::fromId($sTable, $iItem, $this->getDB());
  }

  /**
   * Generate and return an item object filled with data from the specified array
   *
   * @param string $sTable
   * @param array $hItem
   * @return \Omniverse\Item
   */
  public function itemFromArray($sTable, $hItem)
  {
    return Item::fromArray($sTable, $hItem, $this->getDB());
  }

  /**
   * Generate an item list based on the specified type and SQL query
   *
   * @param string $sType
   * @param string $sQuery
   * @return \Omniverse\ItemList
   */
  public function itemList($sType, $sQuery)
  {
    return Item::getList($sType, $sQuery, $this->getDB());
  }

  /**
   * Generate an item list based on the specified type and search criteria
   *
   * @param string $sType
   * @param array $hWhere
   * @param mixed $xOrder
   * @return \Omniverse\ItemList
   */
  public function itemSearch($sType, $hWhere = null, $xOrder = null)
  {
    return Item::search($sType, $hWhere, $xOrder, $this->getDB());
  }

  /**
   * Generate and return an empty item object based on the specified table.
   *
   * @param string $sType
   * @param string $sName (optional) - The name to give the widget when it is instantiated
   * @return \Omniverse\Widget - The requested \Omniverse\Widget on success, otherwise FALSE.
   */
  public function widgetFactory($sType, $sName = null)
  {
    return Widget::factory($sType, $sName, $this);
  }

  /**
   * Generate and return the module of the specified type
   *
   * @param string $sType
   * @return \Omniverse\Module
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
   * Return the currently logged in user
   *
   * @return \Omniverse\Item\User
   */
  public function user()
  {
    return $this->oUser;
  }

  /**
   * Generate and return the current user
   *
   * @return \Omniverse\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    $oUserList = $this->itemSearch('User', ['Email' => 'MasterAdmin']);

    if (count($oUserList) == 0)
    {
      throw new \Exception('Master user not found!');
    }

    return $oUserList[0];
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    $this->oUser = $this->generateUser();
  }
}
