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
class Controller
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
   * @var \Omniverse\Domain - The default domain for this controller instance
   */
  protected $oDomain = null;

  /**
   * List of database objects
   *
   * @var array
   */
  protected $hDatabaseList = [];

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

  protected $hDirectories =
  [
    'root' => '',
    'libs' => []
  ];

  protected $hConfig = [];

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
    return \preg_match("/cli/i", PHP_SAPI);
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
  public static function EOL()
  {
    return self::isCLI() ? "\n" : "<br />\n";
  }

  /**
   * Return the build date of the current release of Omniverse.
   *
   * @param string $sFormat
   */
  public static function buildDate($sFormat = '')
  {
    self::generateBuildData();
    $sFormat = empty($sFormat) ? 'r' : $sFormat;
    return self::$oBuildDate->format($sFormat);
  }

  public static function setTimeStampFormat($sNewFormat = NULL)
  {
    self::$sTimeStampFormat = empty($sNewFormat) ? 'r' : $sNewFormat;
  }

  public static function formatTime($iTimeStamp, $sFormat = NULL)
  {
    $oTime = new \DateTime('@' . (integer)$iTimeStamp);
    $sFormat = empty($sFormat) ? self::$sTimeStampFormat : $sFormat;
    return $oTime->format($sFormat);
  }

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

  public static function flatten($data)
  {
    ob_start();
    var_dump($data);
    return ob_get_flush();
  }

  public static function addLib($sLibDir)
  {
    if (is_dir($sLibDir) && !in_array($sLibDir, self::$aLibList))
    {
      self::$aLibList[] = $sLibDir;
    }
  }

  public static function getLibs()
  {
    return self::$aLibList;
  }

  /**
   * Generate and return a valid, configured controller
   *
   * @param string $sType
   * @param array $hConfig
   * @return \Omniverse\Controller
   * @throws Exception
   */
  public static function factory($sType, array $hConfig = [])
  {
    $sTypeClass = __NAMESPACE__ . '\\Controller\\' . $sType;

    if (!\class_exists($sTypeClass, true))
    {
      throw new Exception("Controller type '$sType' not found");
    }

    return new $sTypeClass($hConfig);
  }

  /**
   * The controller constructor
   *
   * @param array $hConfig - A hash of configuration data
   */
  public function __construct(array $hConfig = [])
  {
    $hLowerConfig = \array_change_key_case($hConfig, CASE_LOWER);

    if (isset($hLowerConfig['sessionname']))
    {
      SessionManager::sessionName($hLowerConfig['sessionname']);
      unset($hLowerConfig['sessionname']);
    }

    SessionManager::start();

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

    $this->hConfig = array_merge($this->hConfig, $hLowerConfig);

    if (\is_null(self::$oDefaultController))
    {
      self::setDefault($this);
    }
  }

  public function __set($sName, $xValue)
  {
    //don't allow public setting of anything
  }

  public function __get($sName)
  {
    $sLowerName = strtolower($sName);

    if (preg_match("#^(.+?)dir$#", $sLowerName, $aMatch))
    {
    	return $this->getDir($aMatch[1]);
    }

    if ($sLowerName == 'domain')
    {
      return $this->oDomain;
    }

    if (isset($this->hConfig[$sLowerName]))
    {
      return $this->hConfig[$sLowerName];
    }
  }

  public function __isset($sName)
  {
    $sLowerName = strtolower($sName);

    if (preg_match("#^(.+?)dir$#", $sLowerName))
    {
      return true;
    }

    if ($sLowerName == 'domain')
    {
      return !empty($this->oDomain);
    }

    return isset($this->hConfig[$sLowerName]);
  }

  public function __unset($sName)
  {
    //don't allow public unsetting of anything
  }

  /**
   * Genertate and return a database object based on the specifed ini section
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
      $this->hDatabaseList[$sSection] = Database::factory($this->hDatabaseConfig[$sSection]);
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
   * @param string $sName (optional) - The name to give the widget when it is instanciated
   * @return \Omniverse\Widget - The requested \Omniverse\Widget on success, otherwise FALSE.
   */
  public function widgetFactory($sType, $sName = null)
  {
    return Widget::factory($sType, $sName, $this);
  }

  /**
   * Generate and return a module of the specified type
   *
   * @param string $sType
   * @return \Omniverse\Module
   */
  public function moduleFactory($sType)
  {
    return Module::factory($sType, $this);
  }

  public function run()
  {

  }
}