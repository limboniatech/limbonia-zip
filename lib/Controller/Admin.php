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
class Admin extends \Omniverse\Controller
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
   * Should popups be used? (Instead of replacing each page)
   *
   * @var boolean
   */
  protected $bUsePopups = false;

  /**
   * The currently logged in user
   *
   * @var \Omniverse\Item\User
   */
  protected $oUser = null;

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
   * Template stand-in function for PHP's preg_replace
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
   * Template stand-in function for PHP's preg_match
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

    if (empty($this->oDomain))
    {
      throw new \Exception('Domain not found');
    }

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
          $_SESSION['ModuleList'][] = $sModuleName;
          $hComponent = $oModule->getComponents();
          ksort($hComponent);
          reset($hComponent);
          $_SESSION['ResourceList'][$oModule->getType()] = $hComponent;
        }

        $_SESSION['ModuleDirs'] = array_unique($_SESSION['ModuleDirs']);

        if (isset($_SESSION['ModuleList']) && is_array($_SESSION['ModuleList']))
        {
          sort($_SESSION['ModuleList']);
          reset($_SESSION['ModuleList']);
        }

        ksort($_SESSION['ResourceList']);
        reset($_SESSION['ResourceList']);
      }
    }

    \Twig_autoloader::register();
    $oLoader = new \Twig_Loader_Filesystem($_SESSION['ModuleDirs']);
    self::$oTemplateGenerator = new \Twig_Environment($oLoader, array('trim_blocks' => true, 'cache' => $this->CacheDir, 'auto_reload' => true, 'autoescape' => false));
    $this->templateData('api', $this);
    self::$oTemplateGenerator->addFilter('replace', new \Twig_Filter_Function('\Omniverse\Controller\Admin::replace'));
    self::$oTemplateGenerator->addFilter('match', new \Twig_Filter_Function('\Omniverse\Controller\Admin::match'));
  }

  /**
   * Return the currently logged in user
   *
   * @return \Omniverse\Item\User
   */
  public function user()
  {
    return $this->oUser;
  }

  /**
   * Should popups be used? (Instead of replacing each page)
   *
   * @return boolean
   */
  public function usePopups()
  {
    return $this->bUsePopups;
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
    if (!is_null(self::$oTemplateGenerator))
    {
      $oTemplate = self::$oTemplateGenerator->loadTemplate($sTemplateName);
      $hTemplateData = is_null($hData) ? $this->hTemplateData : $hData;
      $oTemplate->display($hTemplateData);
    }
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    $this->login();
    $this->templateData('get', filter_input_array(INPUT_GET));
    $sModule = filter_input(INPUT_GET, 'Module');

    if (empty($sModule))
    {
      $sMessage = '';
    }
    else
    {
      try
      {
        $oCurrentModule = $this->moduleFactory($sModule);
        $oCurrentModule->prepareTemplate();
      }
      catch (\Exception $e)
      {
        $sMessage = "The module {$sModule} could not be instaniated!!!<br />";
        error_log($e->getMessage());
      }
    }

    $sAdmin = filter_input(INPUT_GET, 'Admin');

    if ($sAdmin == 'Popup')
    {
      if ($this->bUsePopups)
      {
        $_SESSION['Popup']['AdminMethod'] = 'Popup' . filter_input(INPUT_GET, 'Popup');
        $this->templateDisplay('admin_popup-top.html');

        if (isset($sMessage))
        {
          echo $sMessage;
        }
        else
        {
          $oCurrentModule->showTemplate();
        }

        $this->templateDisplay('admin_popup-bottom.html');
        die();
      }

      //convert the popup to a process
      $_GET['Admin'] = 'Process';
      $_GET['Process'] = filter_input(INPUT_GET, 'Popup');
      unset($_GET['Popup']);
    }

    if ($this->bUsePopups)
    {
      $oPopup = $this->widgetFactory('Window', 'Omniverse_Popup');
      $oPopup->hasScrollBars();
      $oPopup->allowResize();
      $this->templateData('admin_popup', $oPopup->__toString());
    }

    if (count($_SESSION['ModuleList']) > 0)
    {
      $hGroup = [];

      foreach ($_SESSION['ModuleList'] as $sModuleName)
      {
        if (isset($oCurrentModule) && $sModule === $sModuleName)
        {
          $oModule = $oCurrentModule;
        }
        else
        {
          try
          {
            $oModule = $this->moduleFactory($sModuleName);
          }
          catch (\Exception $e)
          {
            echo "Failed to get group for: $sModuleName" . $e->getMessage() . "<br>\n";
            continue;
          }
        }

        if ($this->oUser->hasResource($oModule->getType()) && $oModule->visibleInMenu())
        {
          $hGroup[$oModule->getGroup()][$sModuleName] = $oModule;
        }
      }

      ksort($hGroup);

      foreach (array_keys($hGroup) as $sKey)
      {
        ksort($hGroup[$sKey]);
      }

      $this->templateData('moduleGroups', $hGroup);
    }

    $this->templateDisplay('admin-top.html');

    if (isset($sMessage))
    {
      echo $sMessage;
    }
    else
    {
      $oCurrentModule->showTemplate();
    }

    $this->templateDisplay('admin-bottom.html');
  }

  /**
   * Figure out if there is a valid current user or if the login screen should be displayed
   *
   * @return boolean
   * @throws \Exception
   */
  protected function login()
  {
    if (filter_input(INPUT_GET, 'Admin') == 'Logout')
    {
      $_SESSION = [];
      session_destroy();
      header('Location: ' . filter_input(INPUT_SERVER, 'PHP_SELF') . '?Admin');
    }

    $sEmail = filter_input(INPUT_POST, 'Email');
    $sPassword = trim(filter_input(INPUT_POST, 'Password'));

    try
    {
      if (isset($_SESSION['Email']))
      {
        try
        {
          //A Email stored in the session data shouldn't ever be NULL so we use === for the comparison...
          if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && $_SESSION['Email'] === $this->hConfig['master']['User'])
          {
            $oUserList = $this->itemSearch('User', array('Email' => 'MasterAdmin'));
            $this->oUser = count($oUserList) == 0 ? false : $oUserList[0];
          }
          else
          {
            $this->oUser = \Omniverse\Item\User::getByEmail($_SESSION['Email']);
          }
        }
        catch (\Exception $e)
        {
          $_SESSION = [];
          session_destroy();
          throw $e;
        }
      }

      elseif (!empty($sEmail) && !empty($sPassword))
      {
        //A Email and password submitted through post or get shouldn't ever be NULL so we use === for the comparison...
        if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && $sEmail === $this->hConfig['master']['User'] && !empty($this->hConfig['master']['Password']) && $sPassword === $this->hConfig['master']['Password'])
        {
          $oUserList = $this->itemSearch('User', array('Email' => 'MasterAdmin'));
          $_SESSION['Email'] = $sEmail;
          $this->oUser = count($oUserList) == 0 ? false : $oUserList[0];
        }
        else
        {
          $this->oUser = \Omniverse\Item\User::login($sEmail, $sPassword);
        }
      }

      if ($this->oUser)
      {
        $this->templateData('currentUser', $this->oUser);

        if (!empty($sEmail))
        {
          $_SESSION['Email'] = $sEmail;
        }

        return true;
      }
    }
    catch (\Exception $e)
    {
      $this->printPasswordForm($e->getMessage());
    }

    $this->printPasswordForm();
  }

  /**
   * Display the password form on the login page displaying the specified error, if there is one
   *
   * @param string $sError
   */
  protected function printPasswordForm($sError = '')
  {
    $sAction = empty($_SERVER['QUERY_STRING']) ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    $this->templateData('action', $sAction);
    $this->templateData('error', $sError);
    $this->templateDisplay('admin_login.html');
    die();
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
      $sMenu .= "<table class=\"OmnisysAdminModuleMenu\">\n";

      if (!empty($sHeader))
      {
        $sMenu .= "<tr><td class=\"OmnisysAdminModuleMenuHeader\">$sHeader</td></tr>\n";
        $sMenu .= "<tr class=\"OmnisysAdminModuleMenuDivider\"><td></td></tr>\n";
      }

      $sMenu .= "<tr><td class=\"OmnisysAdminModuleMenuContent\">$sContent</td></tr>\n";

      if (!empty($Footer))
      {
        $sMenu .= "<tr class=\"OmnisysAdminModuleMenuDivider\"><td></td></tr>\n";
        $sMenu .= "<tr><td class=\"OmnisysAdminModuleMenuFooter\">$sFooter</td></tr>\n";
      }

      $sMenu .= "</table>\n<br />\n";
    }

    return $sMenu;
  }
}