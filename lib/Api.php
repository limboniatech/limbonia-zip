<?php
namespace Omniverse;

/**
 * Omniverse API input class
 *
 * This defines all the basic parts of Omniverse API
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Api
{
  use \Omniverse\Traits\Hash;

  /**
   * The single instance allowed for the API object
   *
   * @var \Omniverse\Api
   */
  protected static $oInstance = null;

  protected static $aWebTypes =
  [
    'admin',
    'ajax',
    'api',
    'web'
  ];

  protected $hData = [];

  /**
   * Instantiate and return a single version of this class to all callers
   *
   * @return \Omniverse\Api
   */
  public static function singleton()
  {
    if (is_null(self::$oInstance))
    {
      self::$oInstance = new self();
    }

    return self::$oInstance;
  }

  /**
   * The constructor
   */
  protected function __construct()
  {
    $oServer = Input::singleton('server');
    $this->hData['method'] = strtolower($oServer['request_method']);
    $this->hData['baseurl'] = rtrim(dirname($oServer['php_self']), '/') . '/';
    $this->hData['rawpath'] = rtrim(preg_replace("#\?.*#", '', preg_replace("#^" . $this->hData['baseurl'] . "#",  '', $oServer['request_uri'])), '/');
    $this->hData['rawcall'] = explode('/', $this->hData['rawpath']);
    $aCall = explode('/', strtolower($this->hData['rawpath']));

    if (isset($aCall[0]) && in_array($aCall[0], self::$aWebTypes))
    {
      $this->hData['controller'] = $aCall[0];
    }
    else
    {
      $this->hData['controller'] = 'web';
      array_unshift($aCall, 'web');
    }

    $this->hData['module'] = $aCall[1] ?? null;
    $this->hData['id'] = null;

    if (isset($aCall[2]) && is_numeric($aCall[2]))
    {
      $this->hData['id'] = $aCall[2];
      $this->hData['action'] = $aCall[3] ?? 'view';
      $this->hData['subid'] = null;

      if (isset($aCall[4]) && is_numeric($aCall[4]))
      {
        $this->hData['subid'] = $aCall[4];
        $this->hData['subaction'] = $aCall[5] ?? null;
      }
      else
      {
        $this->hData['subaction'] = $aCall[4] ?? null;
      }
    }
    else
    {
      $this->hData['action'] = $aCall[2] ?? 'list';
      $this->hData['subaction'] = $aCall[3] ?? null;
    }
  }

  /**
   * Sets the specified values if possible
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
  }

  /**
   * Get the specified data
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    return $this->hData[strtolower($sName)] ?? null;
  }

  /**
   * Determine if the specified value is set (exists) or not...
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    return isset($this->hData[strtolower($sName)]);
  }

  /**
   * Unset the specified value
   *
   * @param string $sName
   */
  public function __unset($sName)
  {
  }

  /**
   * Is this API call using GET?
   *
   * @return boolean
   */
  public function isGet()
  {
    return $this->method == 'get';
  }

  /**
   * Is this API call using POST?
   *
   * @return boolean
   */
  public function isPost()
  {
    return $this->method == 'post';
  }
}