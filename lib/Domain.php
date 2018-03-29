<?php
namespace Limbonia;

/**
 * Limbonia Domain Class
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @version $Revision: 1.3 $
 * @package Limbonia
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
   * The base URI for this domain, if there is one...
   *
   * @var string
   */
  protected $sBaseUri = '';

  /**
   * The path to the domain root directory
   *
   * @var string
   */
  protected $sPath = '';

  /**
   * Generate and return the document root of the specified domain
   *
   * @param string $sDomain
   * @return type
   * @throws \Exception
   * @return string The generated document root
   */
  public static function generatePath($sDomain)
  {
    //if the template doesn't contain a sub domain section
    if (!preg_match("#__SUB__#", self::$sDomainDirTemplate))
    {
      //then use the whole thing all at once!
      return preg_replace('#__DOMAIN__#', $sDomain, self::$sDomainDirTemplate);
    }

    //otherwise try to detect the sub-domain as well at the base domain
    $aDomainDirTest = array
    (
      '#((.*)\.)?(.*?\.[a-z]{3,})$#',
      '#((.*)\.)?(.*?\.[a-z]*?\.[a-z]{2})$#'
    );

    foreach ($aDomainDirTest as $sExpressions)
    {
      if (preg_match($sExpressions, $sDomain, $aMatch))
      {
        $sSub = empty($aMatch[2]) ? 'www' : $aMatch[2];
        return preg_replace("#__DOMAIN__#", $aMatch[3], preg_replace("#__SUB__#", $sSub, stripslashes(self::$sDomainDirTemplate)));
      }
    }

    throw new \Exception("The domain specified ($sDomain) is not valid!");
  }

  /**
   * Generate and return the domain of the specified directory
   *
   * @param string $sDomainPath
   * @return string
   * @throws \Exception
   * @return string The generated domain name
   */
  public static function generateName($sDomainPath)
  {
    //if the template doesn't contain a sub domain section
    if (!preg_match("#__SUB__#", self::$sDomainDirTemplate))
    {
      //then use the whole thing all at once!
      $sExpression = '#^' . preg_replace("#__DOMAIN__#", '(.*?)', self::$sDomainDirTemplate) . '#';

      if (preg_match($sExpression, $sDomainPath, $aMatch))
      {
        return $aMatch[1];
      }

      throw new \Exception("The domain path specified ($sDomainPath) is not valid!");
    }

    $sExpression = '#^' . preg_replace("#__DOMAIN__#", '(.*?)', preg_replace("#__SUB__#", '(.*?)', self::$sDomainDirTemplate)) . '#';

    if (!preg_match($sExpression, $sDomainPath, $aMatch) || count($aMatch) == 1)
    {
      throw new \Exception("The domain path specified ($sDomainPath) is not valid!");
    }

    return empty($aMatch[2]) || $aMatch[2] == 'www' ? $aMatch[1] : $aMatch[2] . '.' . $aMatch[1];
  }

  /**
   * Generate and return a domain object base on the specified domain name
   *
   * @param string $sDomain
   * @return \Limbonia\Domain
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

    if (!isset(self::$hDomainList[$sDomain]))
    {
      if ($bValidatePath && $sDomain == 'localhost')
      {
        throw new \Exception("The directory ($sDomainRoot) is not valid!");
      }

      self::$hDomainList[$sDomain] = new self($sDomain, $sDomainRoot);
    }

    return self::$hDomainList[$sDomain];
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
    if (preg_match('#(.*?)(/.*)#', $sName, $aMatch))
    {
      $this->sName = $aMatch[1];
      $this->sBaseUri = $aMatch[2];
    }
    else
    {
      $this->sName = $sName;
    }

    $this->sPath = empty($sPath) || !is_dir($sPath) ? self::generatePath($this->sName) : $sPath;
  }

  /**
   * Return the value specified by the specified name
   *
   * @param string $sName
   * @return string
   */
  public function __get($sName)
  {
    switch (strtolower($sName))
    {
      case 'name':
        return $this->sName;

      case 'path':
        return $this->sPath;

      case 'uri':
        return $this->sBaseUri;

      case 'url':
        return '//' . $this->sName . $this->sBaseUri;
    }
  }

  /**
   * Return the value specified by the specified name
   *
   * @param string $sName
   * @return string
   */
  public function __isset($sName)
  {
    switch (strtolower($sName))
    {
      case 'name':
      case 'path':
      case 'uri':
      case 'url':
        return true;
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