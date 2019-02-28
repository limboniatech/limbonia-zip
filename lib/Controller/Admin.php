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
    if ($this->oUser->id == 0 && !$this->oUser->isAdmin())
    {
      if (isset($this->oRouter->ajax))
      {
         return ['content' => $this->templateRender('login')];
      }

      return $this->templateRender('login');
    }

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
    $oUser = parent::generateUser();
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

    return $oUser;
  }

  /**
   * Process the basic logout
   *
   * @param string $sMessage - the message to display, if there is one
   */
  public function logOut($sMessage = '')
  {
    parent::logOut();
    $this->templateData('failure', "<h1>$sMessage</h1>\n");
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