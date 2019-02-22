<?php
namespace Limbonia;

/**
 * Limbonia Router input class
 *
 * This defines all the basic parts of Limbonia Router
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Router
{
  use \Limbonia\Traits\Hash;

  /**
   * The single instance allowed for the Router object
   *
   * @var \Limbonia\Router
   */
  protected static $oInstance = null;

  /**
   * The list of possible keys in the data array
   *
   * @var array
   */
  protected static $aDataKeys =
  [
    'method',
    'baseurl',
    'rawpath',
    'path',
    'controller',
    'module',
    'id',
    'action',
    'subid',
    'subid',
    'subaction',
    'sort',
    'fields',
    'offset',
    'limit',
    'ajax',
    'search'
  ];

  /**
   * List of methods that should supply JSON data to process
   *
   * @var array
   */
  protected static $aJsonMethods = ['put', 'post'];

  /**
   * Default Router data
   *
   * @var array
   */
  protected $hDefault =
  [
    'method' => 'get',
    'controller' => 'web'
  ];

  /**
   * The Router data
   *
   * @var array
   */
  protected $hData = [];

  /**
   * Instantiate and return a single version of this class to all callers
   *
   * @return \Limbonia\Router
   */
  public static function singleton()
  {
    if (is_null(self::$oInstance))
    {
      self::$oInstance = new self();
      self::$oInstance->generate();
    }

    return self::$oInstance;
  }

  /**
   * Generate an Router object from the specified URI then return it
   *
   * @param string $sUri - The URI to extract the Router data from
   * @return \Limbonia\Router
   */
  public static function fromUri(string $sUri)
  {
    return self::fromArray(['uri' => $sUri]);
  }

  /**
   * Generate an Router object from the specified URI then return it
   *
   * @param array $hRouter - The array to extract the Router data from
   * @return \Limbonia\Router
   */
  public static function fromArray(array $hRouter)
  {
    $oRouter = new self();
    $oRouter->setAll($hRouter);
    return $oRouter;
  }

  /**
   * Set the specified data into the the current Router object
   *
   * @param array $hData
   */
  protected function setAll(array $hData)
  {
    $this->hData['method'] = isset($hData['method']) ? (string)$hData['method'] : $this->hDefault['method'];

    if (isset($hData['baseurl']))
    {
      $this->hData['baseurl'] = $hData['baseurl'];
    }

    if (isset($hData['uri']))
    {
      $hUri = parse_url($hData['uri']);
      $sWebTypes = implode('|', Controller::WEB_TYPES);

      if (isset($this->hData['baseurl']) && preg_match("#{$this->hData['baseurl']}/(.*$)#", $hUri['path'], $aMatch))
      {
        $this->hData['rawpath'] = $aMatch[1];
      }
      elseif (preg_match("#(.*?)(($sWebTypes).*$)#i", $hUri['path'], $aMatch))
      {
        $this->hData['baseurl'] = $aMatch[1];
        $this->hData['rawpath'] = $aMatch[2];
      }
      else
      {
        $this->hData['baseurl'] = '/';
        $this->hData['rawpath'] = preg_replace("#^/#", '', $hUri['path']);
      }
    }

    if (isset($this->hData['baseurl']) && isset($this->hData['rawpath']))
    {
      $this->processRawPath();

      if (isset($hUri['query']))
      {
        $hQuery = null;
        parse_str($hUri['query'], $hQuery);
        $this->processGet($hQuery);
      }
    }

    foreach ($hData as $sKey => $sValue)
    {
      if (in_array(strtolower($sKey), static::$aDataKeys))
      {
        $this->hData[strtolower($sKey)] = strtolower($sValue);
      }
    }
  }

  /**
   * Extract all the information we can from the existing rawpath data
   */
  public function processRawPath()
  {
    $this->hData['rawcall'] = explode('/', $this->hData['rawpath']);
    $this->hData['path'] = strtolower($this->hData['rawpath']);
    $this->hData['call'] = explode('/', $this->hData['path']);

    if (isset($this->hData['call'][0]) && in_array($this->hData['call'][0], Controller::WEB_TYPES))
    {
      array_shift($this->hData['call']);
    }

    $this->hData['module'] = $this->hData['call'][0] ?? null;
    $this->hData['id'] = null;

    if (isset($this->hData['call'][1]) && is_numeric($this->hData['call'][1]))
    {
      $this->hData['id'] = $this->hData['call'][1];
      $this->hData['action'] = $this->hData['call'][2] ?? 'view';
      $this->hData['subid'] = null;

      if (isset($this->hData['call'][3]) && is_numeric($this->hData['call'][3]))
      {
        $this->hData['subid'] = $this->hData['call'][3];
        $this->hData['subaction'] = $this->hData['call'][4] ?? null;
      }
      else
      {
        $this->hData['subaction'] = $this->hData['call'][3] ?? null;
      }
    }
    else
    {
      $this->hData['action'] = $this->hData['call'][1] ?? 'list';
      $this->hData['subaction'] = $this->hData['call'][2] ?? null;
    }
  }

  /**
   * Process the specified array of "get" data into Router data
   *
   * @param array $hGet
   */
  protected function processGet($hGet)
  {
    if (isset($hGet['sort']))
    {
      $this->hData['sort'] = [];

      foreach (explode(',', $hGet['sort']) as $sSort)
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

      unset($hGet['sort']);
    }

    if (isset($hGet['fields']))
    {
      $this->hData['fields'] = explode(',', $hGet['fields']);
      unset($hGet['fields']);
    }

    if (isset($hGet['offset']))
    {
      $this->hData['offset'] = $hGet['offset'];
      unset($hGet['offset']);
    }

    if (isset($hGet['limit']))
    {
      $this->hData['limit'] = $hGet['limit'];
      unset($hGet['limit']);
    }

    if (isset($hGet['ajax']))
    {
      $this->hData['ajax'] = $hGet['ajax'];
      unset($hGet['ajax']);
    }

    if (count($hGet) > 0)
    {
      $this->hData['search'] = [];
      $hTemp = is_array($hGet) ? $hGet : $hGet->getRaw();

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

        unset($hGet[$sKey]);
      }
    }
  }

  /**
   * Generate all the default information from existing data
   */
  protected function generate()
  {
    $oServer = Input::singleton('server');
    $this->hData['method'] = isset($oServer['http_x_http_method_override']) ? strtolower($oServer['http_x_http_method_override']) : strtolower($oServer['request_method']);

    $oGet = Input::singleton('get');
    $this->processGet($oGet->getRaw());

    $this->hData['baseurl'] = rtrim(dirname($oServer['php_self']), '/') . '/';
    $this->hData['rawpath'] = rtrim(preg_replace("#\?.*#", '', preg_replace("#^" . $this->hData['baseurl'] . "#",  '', $oServer['request_uri'])), '/');
    $this->processRawPath();
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
        $oServer = Input::singleton('server');

        if ($oServer['content_type'] != 'application/json')
        {
          throw new \Limbonia\Exception\Web("Unsupported Media Type: {$oServer['content_type']}", null, 415);
        }

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