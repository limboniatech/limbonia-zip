<?php
namespace Limbonia\Controller;

/**
 * Limbonia Router Controller Class
 *
 * This allows the basic controller retrieve data base on the Router URL and return
 * that data in either HTML or JSON format
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Web extends \Limbonia\Controller
{
  /**
   * Data to be appended to the HTML header before display
   *
   * @TODO Either implement this feature fully or remove its unneeded detritus, like this variable...
   *
   * @var string
   */
  protected $sHtmlHeader = '';

  /**
   * Cached login data...
   *
   * @var array
   */
  protected static $hLoginData =
  [
    'user' => '',
    'pass' => ''
  ];

  /**
   * This web controller's router
   *
   * @var \Limbonia\Router
   */
  protected $oRouter = null;

  /**
   * Output the specified data as JSON
   *
   * @param type $xData
   */
  public static function outputJson($xData)
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Content-Type: application/json");
    return json_encode($xData);
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
    parent::__construct($hConfig);

    if (isset($this->hConfig['sessionname']))
    {
      \Limbonia\SessionManager::sessionName($this->hConfig['sessionname']);
      unset($this->hConfig['sessionname']);
    }

    \Limbonia\SessionManager::start();

    $oServer = \Limbonia\Input::singleton('server');

    if (isset($oServer['PHP_AUTH_USER']) && isset($oServer['PHP_AUTH_PW']))
    {
      self::$hLoginData['user'] = $oServer['PHP_AUTH_USER'];
      unset($oServer['PHP_AUTH_USER']);

      self::$hLoginData['pass'] = $oServer['PHP_AUTH_PW'];
      unset($oServer['PHP_AUTH_PW']);
    }
    else
    {
      $oPost = \Limbonia\Input::singleton('post');

      if (isset($oPost['email']) && isset($oPost['password']))
      {
        self::$hLoginData['user'] = $oPost['email'];
        unset($oPost['email']);

        self::$hLoginData['pass'] = $oPost['password'];
        unset($oPost['password']);
      }
    }

    if (empty($this->oDomain))
    {
      if (!empty($oServer['context_prefix']) && !empty($oServer['context_document_root']))
      {
         $this->oDomain = new \Limbonia\Domain($oServer['server_name'] . $oServer['context_prefix'], $oServer['context_document_root']);
      }
      else
      {
        $this->oDomain = \Limbonia\Domain::getByDirectory($oServer['document_root']);
      }

      $this->hConfig['baseuri'] = $this->oDomain->uri;
    }

    //if the controller is a sub class
    if (is_subclass_of($this, __CLASS__))
    {
      //then we need to append the controller type to the baseuri
      $this->hConfig['baseuri'] .= '/' . strtolower(preg_replace("#.*\\\#", '', get_class($this)));
    }

    if (empty($this->oDomain->uri))
    {
      $this->oRouter = \Limbonia\Router::singleton();
    }
    //if the request is coming from a URI
    else
    {
      //then override the default Router object
      $this->oRouter = \Limbonia\Router::fromArray
      ([
        'uri' => $this->server['request_uri'],
        'baseurl' => $this->oDomain->uri,
        'method' => strtolower($oServer['request_method'])
      ]);
    }
  }

  public function getRouter()
  {
    return $this->oRouter;
  }

  /**
   * Data that should be injected into the HTML head
   *
   * @TODO Either implement this feature fully or remove its unneeded detritus, like this method...
   *
   * @param string $xData
   */
  public function addToHtmlHeader($xData)
  {
    $this->sHtmlHeader .= $xData;
  }

  /**
   * Process the basic logout
   */
  public function logOut()
  {
    $_SESSION = [];
    session_destroy();
  }

  /**
   * Generate and return the current user
   *
   * @return \Limbonia\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    if (isset($_SESSION['LoggedInUser']))
    {
      return $this->itemFromId('user', $_SESSION['LoggedInUser']);
    }

    if (!isset(self::$hLoginData['user']) || !isset(self::$hLoginData['pass']))
    {
      return $this->itemFactory('user');
    }

    if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && self::$hLoginData['user'] === $this->hConfig['master']['User'] && !empty($this->hConfig['master']['Password']) && self::$hLoginData['pass'] === $this->hConfig['master']['Password'])
    {
      $oUser = parent::generateUser();

      if (!isset($_SESSION['LoggedInUser']))
      {
        $_SESSION['LoggedInUser'] = $oUser->id;
        \Limbonia\Module::overrideDriverList($this, $oUser);
      }

      return $oUser;
    }

    $oUser = $this->userByEmail(self::$hLoginData['user']);
    $oUser->authenticate(self::$hLoginData['pass']);

    if (!isset($_SESSION['LoggedInUser']))
    {
      $_SESSION['LoggedInUser'] = $oUser->id;
      \Limbonia\Module::overrideDriverList($this, $oUser);
    }

    return $oUser;
  }

  /**
   * Render this controller instance for output and return that data
   *
   * @return string
   */
  protected function render()
  {
    $sTemplate = $this->templateFile($this->oRouter->module);

    if (empty($sTemplate))
    {
      return $this->templateRender('index');
    }

    try
    {
      if (isset($this->oRouter->ajax))
      {
        return self::outputJson
        ([
          'main' => $this->templateRender($sTemplate)
        ]);
      }

      return $this->templateRender($sTemplate);
    }
    catch (Exception $e)
    {
      $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());

      if (isset($this->oRouter->search['click']))
      {
        return self::outputJson
        ([
          'error' => $this->templateRender('error'),
        ]);
      }

      return $this->templateRender('error');
    }
  }

  /**
   * Run everything needed to react to input and display data in the way this controller is intended
   */
  public function run()
  {
    if ($this->oRouter->module == 'logout')
    {
      $this->logOut();
      header('Location: ' . $this->baseUri);
    }

    $this->templateData('controller', $this);

    try
    {
      $this->oUser = $this->generateUser();
    }
    catch (\Exception $e)
    {
      $this->logOut();
      echo 'Invalid Login:  ' . $e->getMessage();
    }


    $this->templateData('currentUser', $this->oUser);
    die($this->render());
  }
}