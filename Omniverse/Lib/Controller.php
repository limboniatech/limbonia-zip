<?php
namespace Omniverse\Lib;

class Controller
{
  use Traits\Config
  {
    __get as __configGet;
  }

  /**
   * The current default Controller
   *
   * @var \Omniverse\Lib\Controller
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
   * @var \Omniverse\Lib\Domain - The default domain for this controller instance
   */
  protected $oDomain = null;

  /**
   * List of database objects
   *
   * @var array
   */
  protected $hDatabaseList = [];

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
    return \preg_match("\cli\i", PHP_SAPI);
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
   * Return the build date of the current release of Omniverse\Lib.
   *
   * @param string $sFormat
   */
  public static function buildDate($sFormat = '')
  {
    self::generateBuildData();
    $sFormat = empty($sFormat) ? 'r' : $sFormat;
    return self::$oBuildDate->format($sFormat);
  }

  /**
   * Return the version number of the current release of Omniverse\Lib.
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
   * The controller constructor
   *
   * @param array|string $xIni Either an array of ini files or a single ini file
   */
  public function __construct($xIni)
  {
    $this->readIni($xIni);

    if (isset($this->SessionName))
    {
      SessionManager::sessionName($this->SessionName);
    }

    SessionManager::start();

    if (isset($this->DomainDirTemplate))
    {
      Domain::setDirTemplate($this->DomainDirTemplate);
    }

    if (isset($this->DomainName))
    {
      $this->oDomain = Domain::factory($this->DomainName);
    }
    else
    {
      $this->oDomain = Domain::getByDirectory($_SERVER['DOCUMENT_ROOT']);
    }

    date_default_timezone_set($this->hasValue('TimeZone') ? $this->getValue('TimeZone') : 'UTC');

    if (\is_null(self::$oDefaultController))
    {
      self::setDefault($this);
    }
  }

  public function __get($sName)
  {
    if (preg_match("#^(.+?)Dir#", $sName))
    {
    	$sDir = $this->getDir($sName);

    	if (!empty($sDir))
    	{
    		return $sDir;
    	}
    }

    if ($this->__isset($sName))
    {
    	return $this->__configGet($sName);
    }
  }

  /**
   * Genertate and return a database object based on the specifed ini section
   *
   * @param string $sSection (optional)
   * @return \Omniverse\Lib\Database
   */
  public function getDB($sSection = 'Database')
  {
    if (empty($sSection) || !$this->hasSection($sSection))
    {
      $sSection = 'Database';
    }

    if (!isset($this->hDatabaseList[$sSection]))
    {
      $this->hDatabaseList[$sSection] = Database::factory($this->getSection('Database'));
    }

    return $this->hDatabaseList[$sSection];
  }

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
    //Check to see if it's specifiacally configured
    if ($this->hasValue($sDirName))
    {
      return $this->getValue($sDirName);
    }

    //if it's in an ini file it may be name a specific way...
    $sTempDir = ucfirst(strtolower($sDirName)) . 'Dir';

    //Check to see if $sTempDir is specifiacally configured
    if ($this->hasValue($sTempDir))
    {
      return $this->getValue($sTempDir);
    }

    //Check to see if it exists in the configured "Custom" directory
    if ($this->hasValue('CustomDir'))
    {
      $sCustomDir = $this->getValue('CustomDir');

      if (is_dir($sCustomDir . DIRECTORY_SEPARATOR . $sDirName))
      {
        return $sCustomDir . DIRECTORY_SEPARATOR . $sDirName;
      }
    }

    //Check to see if it exists relative to the current path
    if ($sTemp = realpath($sDirName))
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
   * @return \Omniverse\Lib\Item
   */
  public function itemFactory($sTable)
  {
    return Item::factory($this->getDB(), $sTable);
  }

  /**
   * Generate and return an item object filled with data from the specified table id
   *
   * @param string $sTable
   * @param integer $iItem
   * @return \Omniverse\Lib\Item
   */
  public function itemFromId($sTable, $iItem)
  {
    return Item::fromId($this->getDB(), $sTable, $iItem);
  }

  /**
   * Generate and return an item object filled with data from the specified array
   *
   * @param string $sTable
   * @param array $hItem
   * @return \Omniverse\Lib\Item
   */
  public function itemFromArray($sTable, $hItem)
  {
    return Item::fromArray($this->getDB(), $sTable, $hItem);
  }

  /**
   * Generate an item list based on the specified type and SQL query
   *
   * @param string $sType
   * @param string $sQuery
   * @param array $aData
   * @return \Omniverse\Lib\ItemList
   */
  public function itemList($sType, $sQuery, $aData = null)
  {
    return Item::getList($this->getDB(), $sType, $sQuery);
  }

  /**
   * Generate an item list based on the specified type and search criteria
   *
   * @param string $sType
   * @param array $hWhere
   * @param mixed $xOrder
   * @return \Omniverse\Lib\ItemList
   */
  public function itemSearch($sType, $hWhere = null, $xOrder = null)
  {
    return Item::search($this->getDB(), $sType, $hWhere, $xOrder);
  }
}