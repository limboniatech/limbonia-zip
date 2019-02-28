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
    'user' => null,
    'pass' => null
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

  /**
   * Activate the specified module
   *
   * @param string $sModule the name of the module to activate
   * @throws Exception
   */
  public function activateModule($sModule)
  {
    parent::activateModule($sModule);
    $aBlackList = $this->moduleBlackList ?? [];
    $sDriver = \Limbonia\Module::driver($sModule);

    if (!in_array($sDriver, $aBlackList) && $this->user()->hasResource($sDriver))
    {
      $sTypeClass = '\\Limbonia\\Module\\' . $sDriver;
      $hComponent = $sTypeClass::getComponents();
      ksort($hComponent);
      reset($hComponent);
      $_SESSION['ResourceList'][$sDriver] = $hComponent;
      $_SESSION['ModuleGroups'][$sTypeClass::getGroup()][strtolower($sDriver)] = $sDriver;

      ksort($_SESSION['ResourceList']);
      reset($_SESSION['ResourceList']);

      ksort($_SESSION['ModuleGroups']);

      foreach (array_keys($_SESSION['ModuleGroups']) as $sKey)
      {
        ksort($_SESSION['ModuleGroups'][$sKey]);
      }
    }
  }

  /**
   * Deactivate the specified module
   *
   * @param string $sModule the name of the module to deactivate
   * @throws Exception
   */
  public function deactivateModule($sModule)
  {
    parent::deactivateModule($sModule);
    $sDriver = \Limbonia\Module::driver($sModule);
    $sTypeClass = '\\Limbonia\\Module\\' . $sDriver;
    unset($_SESSION['ResourceList'][$sDriver]);
    unset($_SESSION['ModuleGroups'][$sTypeClass::getGroup()][strtolower($sDriver)]);

    if (empty($_SESSION['ModuleGroups'][$sTypeClass::getGroup()]))
    {
      unset($_SESSION['ModuleGroups'][$sTypeClass::getGroup()]);
    }
  }

  /**
   * Return the default router
   *
   * @return \Limbonia\Router
   */
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
   *
   * @param string $sMessage - the message to display, if there is one
   */
  public function logOut($sMessage = '')
  {
    if (!empty($sMessage))
    {
      echo $sMessage . static::eol();
    }

    $this->oUser = $this->itemFactory('user');
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
      if ($_SESSION['LoggedInUser'] === 'master')
      {
        return $this->userAdmin();
      }

      return $this->itemFromId('user', $_SESSION['LoggedInUser']);
    }

    if (!is_null(self::$hLoginData['user']))
    {
      if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && self::$hLoginData['user'] === $this->hConfig['master']['User'] && !empty($this->hConfig['master']['Password']) && self::$hLoginData['pass'] === $this->hConfig['master']['Password'])
      {
        $_SESSION['LoggedInUser'] = 'master';
        return $this->userAdmin();
      }

      $oUser = $this->userByEmail(self::$hLoginData['user']);
      $oUser->authenticate(self::$hLoginData['pass']);
      $_SESSION['LoggedInUser'] = $oUser->id;
      return $oUser;
    }

    return $this->itemFactory('user');
  }

  /**
   * Render this controller instance for output and return that data
   *
   * @return string
   */
  protected function render()
  {
    $aTemplatePath = $this->oRouter->call;
    $sTemplate = '';

    while (count($aTemplatePath) > 0)
    {
      $sTemplate = $this->templateFile(implode('/', $aTemplatePath));
      array_pop($aTemplatePath);
    }

    if (isset($this->oRouter->ajax))
    {
      return  ['content' => $this->templateRender($sTemplate)];
    }

    $this->templateData('content', $this->templateRender($sTemplate));
    return $this->templateRender('index');
  }

  /**
   * Run everything needed to react to input and display data in the way this controller is intended
   */
  public function run()
  {
    $this->templateData('controller', $this);

    try
    {
      $this->oUser = $this->generateUser();
    }
    catch (\Exception $e)
    {
      $this->logOut($e->getMessage());
    }

    $this->templateData('currentUser', $this->oUser);

    try
    {
      $xOutput = $this->render();
    }
    catch (Exception $e)
    {
      $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());
      $xOutput = isset($this->oRouter->ajax) ? isset($this->oRouter->ajax) : $this->templateRender('error');
    }

    $sOutput = is_string($xOutput) ? $xOutput : self::outputJson($xOutput);
    die($sOutput);
  }
}