<?php
namespace Limbonia\Controller;

/**
 * Limbonia Admin Controller Class
 *
 * This extends the basic controller with the ability to display and react to
 * site administration pages
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Admin extends \Limbonia\Controller\Web
{
  /**
   * Render this controller instance for output and return that data
   *
   * @return string
   */
  protected function render()
  {
    $sModuleDriver = \Limbonia\Module::driver((string)$this->oRouter->module);

    if (empty($sModuleDriver))
    {
      return parent::render();
    }

    $oCurrentModule = $this->moduleFactory($sModuleDriver);
    $oCurrentModule->prepareTemplate();
    $sModuleTemplate = $oCurrentModule->getTemplate();

    if (isset($this->oRouter->ajax))
    {
       return array_merge(['content' => $this->templateRender($sModuleTemplate)], $oCurrentModule->getAdminOutput());
    }

    $this->templateData('content', $this->templateRender($sModuleTemplate));
    return $this->templateRender('index');
  }

  /**
   * Generate and return the current user
   *
   * @return \Limbonia\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    try
    {
      $oUser = parent::generateUser();

      if ($oUser->id == 0 && !$oUser->isAdmin())
      {
        $this->printPasswordForm();
      }

      $hModuleList = $this->activeModules();
      $_SESSION['ResourceList'] = [];
      $_SESSION['ModuleGroups'] = [];
      $aBlackList = $this->moduleBlackList ?? [];

      foreach ($hModuleList as $sModule)
      {
        $sDriver = \Limbonia\Module::driver($sModule);

        if (empty($sDriver) || in_array($sDriver, $aBlackList) || !$oUser->hasResource($sDriver))
        {
          continue;
        }

        $sTypeClass = '\\Limbonia\\Module\\' . $sDriver;
        $hComponent = $sTypeClass::getComponents();
        ksort($hComponent);
        reset($hComponent);
        $_SESSION['ResourceList'][$sDriver] = $hComponent;
        $_SESSION['ModuleGroups'][$sTypeClass::getGroup()][strtolower($sDriver)] = $sDriver;
      }

      ksort($_SESSION['ResourceList']);
      reset($_SESSION['ResourceList']);

      ksort($_SESSION['ModuleGroups']);

      foreach (array_keys($_SESSION['ModuleGroups']) as $sKey)
      {
        ksort($_SESSION['ModuleGroups'][$sKey]);
      }
    }
    catch (\Exception $e)
    {
      $this->printPasswordForm($e->getMessage());
    }

    return $oUser;
  }

  /**
   * Display the password form on the login page displaying the specified error, if there is one
   *
   * @param string $sError
   */
  protected function printPasswordForm($sError = '')
  {
    $sFailure = empty($sError) ? '' : "  <h1>$sError</h1>\n";
    $this->templateData('sFailure', $sFailure);
    $sLogin = $this->templateRender('login');

    if (empty($sLogin))
    {
      $sLogin = 'Login page not found';
    }

    if (isset($this->oRouter->ajax))
    {
      parent::outputJson(['content' => $sLogin]);
    }

    die($sLogin);
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