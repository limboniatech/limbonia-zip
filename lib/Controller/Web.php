<?php
namespace Limbonia\Controller;

/**
 * Limbonia API Controller Class
 *
 * This allows the basic controller retrieve data base on the API URL and return
 * that data in JSON format
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
   * @param \Limbonia\Api $oApi
   * @param array $hConfig - A hash of configuration data
   */
  public function __construct(\Limbonia\Api $oApi, array $hConfig = [])
  {
    parent::__construct($oApi, $hConfig);

    if (isset($this->hConfig['sessionname']))
    {
      \Limbonia\SessionManager::sessionName($this->hConfig['sessionname']);
      unset($this->hConfig['sessionname']);
    }

    \Limbonia\SessionManager::start();
    $oServer = \Limbonia\Input::singleton('server');

    if (empty($this->oDomain))
    {
      if (isset($oServer['context_prefix']) && isset($oServer['context_document_root']))
      {
         $this->oDomain = new \Limbonia\Domain($oServer . $oServer['context_prefix'], $oServer['context_document_root']);
      }
      else
      {
        $this->oDomain = \Limbonia\Domain::getByDirectory($this->server['document_root']);
      }

      $this->hConfig['baseuri'] = $this->oDomain->uri;
    }

    //if the controller is a sub class
    if (is_subclass_of($this, __CLASS__))
    {
      //then we need to append the controller type to the baseuri
      $this->hConfig['baseuri'] .= '/' . strtolower(preg_replace("#.*\\\#", '', get_class($this)));
    }

    //if the requiest is coming from a URI
    if (!empty($this->oDomain->uri))
    {
      //then override the default API object
      $this->oApi = \Limbonia\Api::fromArray
      ([
        'uri' => $this->server['request_uri'],
        'baseurl' => $this->oDomain->uri,
        'method' => strtolower($oServer['request_method'])
      ]);
    }
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

    if (!isset($this->oApi->user) || !isset($this->oApi->pass))
    {
      return $this->itemFactory('user');
    }

    if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && $this->oApi->user === $this->hConfig['master']['User'] && !empty($this->hConfig['master']['Password']) && $this->oApi->pass === $this->hConfig['master']['Password'])
    {
      $oUser = parent::generateUser();

      if (!isset($_SESSION['LoggedInUser']))
      {
        $_SESSION['LoggedInUser'] = $oUser->id;
        \Limbonia\Module::overrideDriverList($this, $oUser);
      }

      return $oUser;
    }

    $oUser = $this->userByEmail($this->oApi->user);
    $oUser->authenticate($this->oApi->pass);

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
    $sTemplate = $this->templateFile($this->oApi->module);

    if (empty($sTemplate))
    {
      return $this->templateRender('index');
    }

    try
    {
      if (isset($this->oApi->ajax))
      {
        return self::outputJson
        ([
          'pageTitle' => '???',
          'main' => $this->templateRender($sTemplate)
        ]);
      }

      return $this->templateRender($sTemplate);
    }
    catch (Exception $e)
    {
      $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());

      if (isset($this->oApi->search['click']))
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
    if ($this->oApi->module == 'logout')
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