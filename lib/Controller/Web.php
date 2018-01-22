<?php
namespace Omniverse\Controller;

/**
 * Omniverse API Controller Class
 *
 * This allows the basic controller retrieve data base on the API URL and return
 * that data in JSON format
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Web extends \Omniverse\Controller
{
  /**
   * All the data that will be used by the templates
   *
   * @var array
   */
  protected $hTemplateData = [];

  /**
   * Data to be appended to the HTML header before display
   *
   * @var string
   */
  protected $sHtmlHeader = '';

  /**
   * Output the specified data as JSON
   *
   * @param type $xData
   */
  protected static function outputJson($xData)
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Content-Type: application/json");
    die(json_encode($xData));
  }

  public static function templateDirs()
  {
    if (!isset($_SESSION['TemplateDirs']))
    {
      $_SESSION['TemplateDirs'] = [];

      foreach (parent::getLibs() as $sLibDir)
      {
        $sTemplateDir = "$sLibDir/Template";

        if (is_readable($sTemplateDir))
        {
          $_SESSION['TemplateDirs'][] = $sTemplateDir;
        }
      }
    }

    return $_SESSION['TemplateDirs'];
  }

  /**
   * The controller constructor
   *
   * NOTE: This constructor should only be used by the factory and *never* directly
   *
   * @param array $hConfig - A hash of configuration data
   */
  public function __construct(array $hConfig = [])
  {
    parent::__construct($hConfig);

    if (isset($this->hConfig['sessionname']))
    {
      \Omniverse\SessionManager::sessionName($this->hConfig['sessionname']);
      unset($this->hConfig['sessionname']);
    }

    \Omniverse\SessionManager::start();

    if (empty($this->oDomain))
    {
      $this->oDomain = \Omniverse\Domain::getByDirectory($this->server['document_root']);
      $this->hConfig['baseuri'] = $this->oDomain->uri;
    }

    //if the controller is a sub class
    if (is_subclass_of($this, __CLASS__))
    {
      //then we need to append the controller type to the baseuri
      $this->hConfig['baseuri'] .= '/' . strtolower(preg_replace("#.*\\\#", '', get_class($this)));
    }

    $this->oApi = \Omniverse\Api::singleton();
  }

  /**
   * Add the specified data to the template under the specified name
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function templateData($sName, $xValue)
  {
    $this->hTemplateData[$sName] = $xValue;
  }

  /**
   * Render and return specified template
   *
   * @param string $sTemplateName
   * @return string The rendered template
   */
  public function templateRender($sTemplateName)
  {
    $sTemplateFile = $this->templateFile($sTemplateName);

    if (empty($sTemplateFile))
    {
      return '';
    }

    ob_start();
    $this->templateInclude($sTemplateFile);
    return ob_get_clean();
  }

  /**
   * Return the full file path of the specified template, if it exists
   *
   * @param string $sTemplateName
   * @return string
   */
  public function templateFile($sTemplateName)
  {
    if (empty($sTemplateName))
    {
      return '';
    }

    if (is_readable($sTemplateName))
    {
      return $sTemplateName;
    }

    foreach (self::templateDirs() as $sLib)
    {
      $sFilePath = $sLib . '/' . $this->oApi->controller . '/' .$sTemplateName;

      if (is_readable($sFilePath))
      {
        return $sFilePath;
      }

      if (is_readable("$sFilePath.php"))
      {
        return "$sFilePath.php";
      }

      if (is_readable("$sFilePath.html"))
      {
        return "$sFilePath.html";
      }
    }

    return '';
  }

  /**
   * Find then include the specified template if it's found
   *
   * @param srtring $sTemplateName
   */
  protected function templateInclude($sTemplateName)
  {
    $sTemplateFile = $this->templateFile($sTemplateName);

    if ($sTemplateFile)
    {
      extract($this->hTemplateData);
      include $sTemplateFile;
    }
  }

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
   * @return \Omniverse\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    try
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
        return parent::generateUser();
      }

      $oUser = \Omniverse\Item\User::getByEmail($this->oApi->user, $this->getDB());
      $oUser->setController($this);
      $oUser->authenticate($this->oApi->pass);
      return $oUser;
    }
    catch (\Exception $e)
    {
      throw new \Exception('Invalid Login:  ' . $e->getMessage(), null, $e);
    }
  }

  /**
   * Handle any Exceptions thrown while generating the current user
   *
   * @param \Exception $oException
   */
  protected function handleGenerateUserException(\Exception $oException)
  {
    echo $oException->getMessage();
  }

  protected function renderPage()
  {
    $sTemplate = $this->templateFile($this->oApi->module);

    if (!empty($sTemplate))
    {
      try
      {
        if (isset($this->oApi->ajax))
        {
          self::outputJson
          ([
            'pageTitle' => '???',
            'main' => $this->templateRender($sTemplate)
          ]);
        }

        $this->templateData('main', $this->templateRender($sTemplate));
      }
      catch (Exception $e)
      {
        $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());

        if (isset($this->oApi->search['click']))
        {
          self::outputJson
          ([
            'error' => $this->templateRender('error'),
          ]);
        }

        die($this->templateRender('error'));
      }
    }

    die($this->templateRender('index'));
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
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
      $this->templateData('currentUser', $this->oUser);
      $this->renderPage();
    }
    catch (\Exception $e)
    {
      $this->logOut();
      $this->handleGenerateUserException($e);
    }
  }
}