<?php
namespace Omniverse\Controller;

/**
 * Omniverse Admin Controller Class
 *
 * This extends the basic controller with the ability to display and react to
 * domain administration pages
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Admin extends \Omniverse\Controller\Web
{
  /**
   * The Twig object needed to process the templates
   *
   * @var \Twig_Environment
   */
  protected static $oTemplateGenerator = null;

  /**
   * All the data that will be used by the templates
   *
   * @var array
   */
  protected $hTemplateData = [];

  /**
   * List of configuration data
   *
   * @var array
   */
  protected $hConfig =
  [
    'defaulttemplate' => 'default.template'
  ];

  /**
   * Template proxy function for PHP's preg_replace
   *
   * @param string $sText
   * @param string $sRegExpression
   * @param string $sValue
   * @return string
   */
  public static function replace($sText, $sRegExpression, $sValue)
  {
    return preg_replace($sRegExpression, $sValue, $sText);
  }

  /**
   * Template proxy function for PHP's preg_match
   *
   * @param string $sText
   * @param string $sRegExpression
   * @return boolean
   */
  public static function match($sText, $sRegExpression)
  {
    return preg_match($sRegExpression, $sText);
  }

  /**
   * The controller constructor
   *
   * @param array $hConfig - A hash of configuration data
   */
  public function __construct(array $hConfig = [])
  {
    parent::__construct($hConfig);
    \Twig_autoloader::register();
    $this->templateData('controller', $this);
  }

  /**
   * Generate and return the Twig Template generator object
   *
   * @return \Twig_Environment
   */
  protected function getTemplateGenerator()
  {
    if (is_null(self::$oTemplateGenerator))
    {
      if (!isset($_SESSION['ModuleDirs']))
      {
        $_SESSION['ModuleDirs'] = [];

        foreach (parent::getLibs() as $sLibDir)
        {
          $_SESSION['ModuleDirs'][] = "$sLibDir/Module/templates";
        }
      }

      $oLoader = new \Twig_Loader_Filesystem($_SESSION['ModuleDirs']);
      self::$oTemplateGenerator = new \Twig_Environment($oLoader, ['trim_blocks' => true, 'cache' => $this->cacheDir, 'auto_reload' => true, 'autoescape' => false]);
      self::$oTemplateGenerator->addFilter('replace', new \Twig_Filter_Function('\Omniverse\Controller\Admin::replace'));
      self::$oTemplateGenerator->addFilter('match', new \Twig_Filter_Function('\Omniverse\Controller\Admin::match'));
    }

    return self::$oTemplateGenerator;
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
   * Display the specified template
   *
   * @param string $sTemplateName
   * @param array $hData (optional)
   */
  public function templateDisplay($sTemplateName, $hData = null)
  {
    if (!empty($sTemplateName))
    {
      $oTemplate = $this->getTemplateGenerator()->loadTemplate($sTemplateName);
      $hTemplateData = is_null($hData) ? $this->hTemplateData : $hData;
      $oTemplate->display($hTemplateData);
    }
  }

  /**
   * Process and cache the module data
   */
  protected function processModules()
  {
    if (empty($_SESSION['ModuleList']))
    {
      $aTemp = [];

      foreach (get_class_methods($this) as $sMethod)
      {
        if (preg_match("/^System(.+)/i", $sMethod, $aMatch))
        {
          $aTemp[] = $aMatch[1];
        }
      }

      sort($aTemp);
      reset($aTemp);

      $_SESSION['ResourceList'] = ['System' => $aTemp];
      $_SESSION['ModuleList'] = [];
      $_SESSION['ModuleDirs'] = [];
      $_SESSION['ModuleGroups'] = [];
      $aBlackList = isset($this->hConfig['moduleblacklist']) ? $this->hConfig['moduleblacklist'] : [];

      foreach (parent::getLibs() as $sLibDir)
      {
        foreach (glob("$sLibDir/Module/*.php") as $sModule)
        {
          if (in_array($sModule, $_SESSION['ModuleList']) || in_array($sModule, $aBlackList))
          {
            continue;
          }

          $sModuleName = basename($sModule, ".php");

          try
          {
            $oModule = $this->moduleFactory($sModuleName);
          }
          catch (\Exception $e)
          {
            echo $e->getMessage();
            continue;
          }

          $_SESSION['ModuleDirs'][] = "$sLibDir/Module/templates";
          $hComponent = $oModule->getComponents();
          ksort($hComponent);
          reset($hComponent);
          $_SESSION['ResourceList'][$oModule->getType()] = $hComponent;

          if ($this->oUser->hasResource($oModule->getType()))
          {
            $_SESSION['ModuleList'][strtolower($oModule->getType())] = $oModule->getType();

            if ($oModule->visibleInMenu())
            {
              $_SESSION['ModuleGroups'][$oModule->getGroup()][strtolower($oModule->getType())] = $oModule->getType();
            }
          }
        }
      }

      $_SESSION['ModuleDirs'] = array_unique($_SESSION['ModuleDirs']);

      if (isset($_SESSION['ModuleList']) && is_array($_SESSION['ModuleList']))
      {
        ksort($_SESSION['ModuleList']);
        reset($_SESSION['ModuleList']);
      }

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
   * Handle any Exceptions thrown while generating the current user
   *
   * @param \Exception $oException
   */
  protected function handleGenerateUserException(\Exception $oException)
  {
    $this->printPasswordForm($oException->getMessage());
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    parent::run();

    if ($this->oUser->id == 0)
    {
      $this->printPasswordForm();
    }
    else
    {
      $_SESSION['LoggedInUser'] = $this->oUser->id;
      $this->processModules();
    }

    $this->templateData('currentUser', $this->oUser);
    $this->templateData('moduleGroups', $_SESSION['ModuleGroups']);
    $sModuleTemplate = null;

    if (!empty($_SESSION['ModuleList'][$this->oApi->module]))
    {
      try
      {
        $sModuleTemplate = 'error.html';
        $oCurrentModule = $this->moduleFactory($_SESSION['ModuleList'][$this->oApi->module]);
        $oCurrentModule->prepareTemplate();
        $sModuleTemplate = $oCurrentModule->getTemplate();
      }
      catch (\Omniverse\Exception\Object $e)
      {
        $this->templateData('failure', "The module {$this->oApi->module} could not be instaniated!!!<br />");
      }
      catch (\Exception $e)
      {
        $this->templateData('failure', $e->getMessage());
      }
    }

    ob_start();
    $this->templateDisplay('admin-top.html');
    $this->templateDisplay($sModuleTemplate);
    $this->templateDisplay('admin-bottom.html');
    die(ob_get_clean());
  }

  /**
   * Display the password form on the login page displaying the specified error, if there is one
   *
   * @param string $sError
   */
  protected function printPasswordForm($sError = '')
  {
    $sAction = empty($this->server['QUERY_STRING']) ? $this->server['request_uri'] : $this->server['request_uri'] . '?' . $this->server['QUERY_STRING'];
    $sFailure = empty($sError) ? '' : "  <h1>$sError</h1>\n";

    foreach (self::getLibs() as $sLib)
    {
      if (is_file($sLib . '/Module/templates/admin_login.html'))
      {
        include $sLib . '/Module/templates/admin_login.html';
        die();
      }
    }

    //if no login page is found, then use the default version...
    die("<!DOCTYPE html>
<html>
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <title>Omniverse Password Check</title>
  <link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->domain->uri}/" . $this->getDir('share') . "/login.css\" />
</head>
<body onLoad=\"document.passCheck.email.focus();\">
$sFailure
<form action=\"$sAction\" method=\"post\" name=\"passCheck\">
    <div class=\"field\"><span class=\"name\">Email:</span><span class=\"value\"><input type=\"text\" name=\"email\"></span></div>
    <div class=\"field\"><span class=\"name\">Password:</span><span class=\"value\"><input type=\"password\" name=\"password\"></span></div>
    <div class=\"field\"><span class=\"name\"></span><span class=\"value\"><input type=\"submit\" name=\"submit\" value=\"Authorization\"></span></div>
  </form>
</body>
</html>");
  }

  /**
   * Generate and return the admin menu
   *
   * @param string $sContent
   * @param string $sHeader (optional)
   * @param string $sFooter (optional)
   * @return string
   */
  public static function getMenu($sContent, $sHeader = '', $sFooter = '')
  {
    $sMenu = '';

    if (!empty($sContent))
    {
      $sMenu .= "<section class=\"moduleMenu\">\n";

      if (!empty($sHeader))
      {
        $sMenu .= "<header>$sHeader</header>\n";
      }

      $sMenu .= "<main class=\"content\">$sContent</main>\n";

      if (!empty($sFooter))
      {
        $sMenu .= "<footer>$sFooter</footer>\n";
      }

      $sMenu .= "</section>\n";
    }

    return $sMenu;
  }
}