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
    $this->hData['rawpath'] = rtrim(preg_replace("#\?.*#", '', str_replace($this->hData['baseurl'], '', $oServer['request_uri'])), '/');
    $this->hData['rawcall'] = explode('/', $this->hData['rawpath']);
    $this->hData['call'] = explode('/', strtolower($this->hData['rawpath']));

    switch ($this->hData['call'][0])
    {
      case 'admin':
      case 'ajax':
      case 'api':
        $this->hData['controllertype'] = ucfirst($this->hData['call'][0]);
        $this->hData['baseurl'] .= $this->hData['call'][0];
        unset($this->hData['call'][0]);
        $this->hData['call'] = array_values($this->hData['call']);
        break;

      default:
        $this->hData['controllertype'] = 'Web';
        break;
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
    $sLowerName = \strtolower($sName);

    switch ($sLowerName)
    {
      case 'module':
        return $this->hData['call'][0] ?? '';

      case 'id':
        return is_numeric($this->hData['call'][1]) ? $this->hData['call'][1] : null;

      case 'action':
        if (isset($this->hData['call'][1]) && is_numeric($this->hData['call'][1]))
        {
          return $this->hData['call'][2] ?? 'view';
        }

        return $this->hData['call'][1] ?? 'list';

      case 'subid':
        return is_numeric($this->hData['call'][3]) ? $this->hData['call'][3] : null;

      case 'subaction':
        if (isset($this->hData['call'][3]) && is_numeric($this->hData['call'][3]))
        {
          return $this->hData['call'][4] ?? null;
        }

        return $this->hData['call'][3] ?? null;

      case 'subobject':
        //Since this is commenly an object name outside the scpope of the code the letter case
        //may be important, so we'll preserv it by using the raw data
        if (isset($this->hData['call'][3]) && is_numeric($this->hData['call'][3]))
        {
          return $this->hData['rawcall'][5] ?? null;
        }

        return $this->hData['rawcall'][4] ?? null;
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
    $sLowerName = \strtolower($sName);

    switch ($sLowerName)
    {
      case 'module':
        return isset($this->hData['call'][0]);

      case 'action':
        return true;

      case 'id':
        return isset($this->hData['call'][1]) && is_numeric($this->hData['call'][1]);

      case 'subaction':
        if (isset($this->hData['call'][3]) && is_numeric($this->hData['call'][3]))
        {
          return isset($this->hData['call'][4]);
        }

        return isset($this->hData['call'][3]);

      case 'subid':
        return isset($this->hData['call'][3]) && is_numeric($this->hData['call'][3]);
    }

    return isset($this->hData[$sLowerName]);
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