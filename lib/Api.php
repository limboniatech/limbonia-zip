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

  /**
   * List of controller types that are based on the web controller
   *
   * @var array
   */
  protected static $aWebTypes =
  [
    'admin',
    'ajax',
    'api',
    'web'
  ];

  /**
   * List of methods that should supply JSON data to process
   *
   * @var array
   */
  protected static $aJsonMethods = ['put', 'post'];

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
    $this->hData['method'] = isset($oServer['http_x_http_method_override']) ? strtolower($oServer['http_x_http_method_override']) : strtolower($oServer['request_method']);

    if (isset($oServer['PHP_AUTH_USER']) && isset($oServer['PHP_AUTH_PW']))
    {
      $this->hData['user'] = $oServer['PHP_AUTH_USER'];
      unset($oServer['PHP_AUTH_USER']);

      $this->hData['pass'] = $oServer['PHP_AUTH_PW'];
      unset($oServer['PHP_AUTH_PW']);
    }
    else
    {
      $oPost = Input::singleton('post');

      if (isset($oPost['email']) && isset($oPost['password']))
      {
        $this->hData['user'] = $oPost['email'];
        unset($oPost['email']);

        $this->hData['pass'] = $oPost['password'];
        unset($oPost['password']);
      }
    }

    $oGet = Input::singleton('get');

    if (isset($oGet['sort']))
    {
      $this->hData['sort'] = [];

      foreach (explode(',', $oGet['sort']) as $sSort)
      {
        if (preg_match("/(-|\+)(.*)/", $sSort, $aMatch))
        {
          $this->hData['sort'][] = $aMatch[1] === '-' ? trim($aMatch[2]) . ' DESC' : trim($aMatch[2]) . ' ASC';
        }
        else
        {
          $this->hData['sort'][] = trim($sSort) . ' ASC';
        }
      }

      unset($oGet['sort']);
    }

    if (isset($oGet['fields']))
    {
      $this->hData['fields'] = explode(',', $oGet['fields']);
      unset($oGet['fields']);
    }

    if (isset($oGet['offset']))
    {
      $this->hData['offset'] = $oGet['offset'];
      unset($oGet['offset']);
    }

    if (isset($oGet['limit']))
    {
      $this->hData['limit'] = $oGet['limit'];
      unset($oGet['limit']);
    }

    if (isset($oGet['ajax']))
    {
      $this->hData['ajax'] = $oGet['ajax'];
      unset($oGet['ajax']);
    }

    if (count($oGet) > 0)
    {
      $this->hData['search'] = [];
      $hTemp = $oGet->getRaw();

      foreach ($hTemp as $sKey => $sValue)
      {
        if (preg_match("/,/", $sValue))
        {
          $this->hData['search'][$sKey] = explode(',', $sValue);
        }
        else
        {
          $this->hData['search'][$sKey] = $sValue;
        }

        unset($oGet[$sKey]);
      }
    }

    $this->hData['baseurl'] = rtrim(dirname($oServer['php_self']), '/') . '/';
    $this->hData['rawpath'] = rtrim(preg_replace("#\?.*#", '', preg_replace("#^" . $this->hData['baseurl'] . "#",  '', $oServer['request_uri'])), '/');
    $this->hData['path'] = strtolower($this->hData['rawpath']);
    $this->hData['rawcall'] = explode('/', $this->hData['rawpath']);
    $aCall = explode('/', $this->hData['path']);

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
    $sLowerName = strtolower($sName);

    if ($sLowerName == 'data')
    {
      if (in_array($this->hData['method'], self::$aJsonMethods))
      {
        $this->hData['data'] = json_decode(file_get_contents("php://input"), true);
      }
    }

    return $this->hData[$sLowerName] ?? null;
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
}