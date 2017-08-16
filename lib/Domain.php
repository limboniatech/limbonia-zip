<?php
namespace Omniverse;

/**
 * Omniverse Domain Class
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.3 $
 * @package Omniverse
 */
class Domain
{
  /**
   * List of singleton domain objects
   *
   * @var array
   */
  protected static $hDomainList = [];

  /**
   * The template used to generate the domain directory from the domain name, if needed
   *
   * @var string
   */
  protected static $sDomainDirTemplate = '/home/lonnie/__DOMAIN__/__SUB__/html';

  /**
   * The domain name
   *
   * @var string
   */
  protected $sName = '';

  /**
   * The path to the domain root directory
   *
   * @var string
   */
  protected $sPath = '';

  /**
   * Return a hash of domain data based on the specified domain name
   *
   * @param string $sDomain
   * @throws Exception
   * @return array
   */
  public static function generatePath($sDomain)
  {
    $aDomainDirTest = array
    (
      '#((.*)\.)?(.*?\.[a-z]{3,})$#',
      '#((.*)\.)?(.*?\.[a-z]*?\.[a-z]{2})$#'
    );

    foreach ($aDomainDirTest as $sExpressions)
    {
      if (preg_match($sExpressions, $sDomain, $aMatch))
      {
        $sSub = empty($aMatch[2]) || $aMatch[2] == 'www' ? 'root' : $aMatch[2];
        $sDomain = $aMatch[3];
        return preg_replace("#__DOMAIN__#", $sDomain, preg_replace("#__SUB__#", $sSub, self::$sDomainDirTemplate));
      }
    }

    throw new \Exception("The domain specified ($sDomain) is not valid!");
  }

  public static function generateName($sDomainPath)
  {
    $sExpression = '#^' . preg_replace("#__DOMAIN__#", '(.*?)', preg_replace("#__SUB__#", '(.*?)', self::$sDomainDirTemplate)) . '#';

    if (!preg_match($sExpression, $sDomainPath, $aMatch) || count($aMatch) == 1)
    {
      throw new \Exception("The domain path specified ($sDomainPath) is not valid!");
    }

    return $aMatch[2] == 'root' ? $aMatch[1] : $aMatch[2] . '.' . $aMatch[1];
  }

  /**
   * Generate and return a domain object base on the specified domain name
   *
   * @param string $sDomain
   * @return \Omniverse\Domain
   */
  public static function factory($sDomain)
  {
    if (!isset(self::$hDomainList[$sDomain]))
    {
      self::$hDomainList[$sDomain] = new self($sDomain);
    }

    return self::$hDomainList[$sDomain];
  }

  /**
   * Generate and return a domain object from the specified directory
   *
   * @param string $sDomainRoot
   * @param boolean $bValidatePath
   * @throws \Exception
   * @return Domain
   */
  public static function getByDirectory($sDomainRoot, $bValidatePath = false)
  {
    if ($bValidatePath && !is_dir($sDomainRoot))
    {
      throw new \Exception("The directory ($sDomainRoot) does not exist!");
    }

    $sDomain = self::generateName($sDomainRoot);

    if ($bValidatePath && $sDomain == 'localhost')
    {
      throw new \Exception("The directory ($sDomainRoot) is not valid!");
    }

   return self::factory($sDomain);
  }

  /**
   * Update the master domain directory template with the specified string
   *
   * @param string $sDirTemplate
   */
  public static function setDirTemplate($sDirTemplate)
  {
    self::$sDomainDirTemplate = $sDirTemplate;
  }

  /**
   * The domain constructor
   *
   * @param string $sName
   * @param string $sPath (optional)
   */
  public function __construct($sName, $sPath = '')
  {
    $this->sName = $sName;
    $this->sPath = empty($sPath) ? self::generatePath($sName) : $sPath;
  }

  /**
   * Return the value specified by the specifid name
   *
   * @param string $sName
   * @return string
   */
  public function __get($sName)
  {
    $sName = strtolower(trim($sName));

    if ($sName == 'name')
    {
      return $this->sName;
    }

    if ($sName == 'path')
    {
      return $this->sPath;
    }

    $sDir = $this->sPath . '/' . $sName;

    if (is_dir($sDir))
    {
      return $sDir;
    }
  }

  /**
   * Return the string representation of the domain
   *
   * @return string
   */
  public function __toString()
  {
    return $this->sName;
  }
}