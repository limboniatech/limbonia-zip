<?php
namespace Omniverse\Lib;

/**
 * Omniverse Domain Class
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.3 $
 * @package Omniverse\Lib
 */
class Domain
{
  static protected $hDomainList = [];
  protected static $sDomainDirTemplate = '/home3/lonnie/__DOMAIN__/__SUB__/html';
  protected $sDomain = '';
  protected $sDomainRoot = '';

  /**
   * Return a hash of domain data based on the specified domain name
   *
   * @param string $sDomain
   * @throws Exception
   * @return array
   */
  protected static function generateDomainHash($sDomain)
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
        return array
        (
          'sub' => empty($aMatch[2]) || $aMatch[2] == 'www' ? 'root' : $aMatch[2],
          'baseDomain' => $aMatch[3]
        );
      }
    }

    throw new Exception("The domain specified domain ($sDomain) is not valid!");
  }

  /**
   * Generate and return a domain object base on the specified domain name
   *
   * @param string $sDomain
   * @return \Omniverse\Lib\Domain
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
   * @throws Exception
   * @return \Omniverse\Lib\Domain
   */
  public static function getByDirectory($sDomainRoot, $bValidatePath = false)
  {
    $sExpression = '#^' . preg_replace("#__DOMAIN__#", '(.*?)', preg_replace("#__SUB__#", '(.*?)', self::$sDomainDirTemplate)) . '#';

    if (!preg_match($sExpression, $sDomainRoot, $aMatch))
    {
      throw new Exception("The directory ($sDomainRoot) is not valid!");
    }

    if ($bValidatePath && !is_dir($sDomainRoot))
    {
      throw new Exception("The directory ($sDomainRoot) does not exist!");
    }

    $sDomain = $aMatch[2] == 'root' ? $aMatch[1] : $aMatch[2] . '.' . $aMatch[1];
    return self::factory($sDomain);
  }

  public static function setDirTemplate($sDirTemplate)
  {
    self::$sDomainDirTemplate = $sDirTemplate;
  }

  /**
   * The domain constructor
   *
   * @param string $sDomain
   */
  public function __construct($sDomain)
  {
    $hDomain = self::generateDomainHash($sDomain);
    $this->sDomain = $sDomain;
    $this->sDomainRoot = preg_replace("#__DOMAIN__#", $hDomain['baseDomain'], preg_replace("#__SUB__#", $hDomain['sub'], self::$sDomainDirTemplate));
  }

  public function __get($sName)
  {
    $sName = strtolower(trim($sName));

    if ($sName == 'name')
    {
      return $this->sDomain;
    }

    if ($sName == 'path')
    {
      return $this->sDomainRoot;
    }

    $sDir = $this->sDomainRoot . '/' . $sName;

    if (is_dir($sDir))
    {
      return $sDir;
    }
  }
}