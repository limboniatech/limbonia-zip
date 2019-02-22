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
    $sModuleDriver = isset($this->oRouter->module) ? \Limbonia\Module::driver($this->oRouter->module) : '';

    if (empty($sModuleDriver))
    {
      return $this->templateRender('index');
    }

    try
    {
      $oCurrentModule = $this->moduleFactory($sModuleDriver);
      $oCurrentModule->prepareTemplate();
      $sModuleTemplate = $oCurrentModule->getTemplate();

      if (isset($this->oRouter->ajax))
      {
         return parent::outputJson(array_merge(['moduleOutput' => $this->templateRender($sModuleTemplate)], $oCurrentModule->getAdminOutput()));
      }

      $this->templateData('moduleOutput', $this->templateRender($sModuleTemplate));
      return $this->templateRender('index');
    }
    catch (\Exception $e)
    {
      $sModuleName = isset($oCurrentModule) ? $oCurrentModule->getTitle() : $sModuleDriver;
      $sMessage = isset($sModuleTemplate) ? "The $sModuleName module could not render the $sModuleTemplate template" : "The $sModuleName module's action could not be rendered";

      $this->templateData('failure', "$sMessage: " . $e->getMessage());

      if (isset($this->oRouter->ajax))
      {
        return parent::outputJson
        ([
          'error' => $this->templateRender('error'),
        ]);
      }

      return $this->templateRender('error');
    }
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
    }
    catch (\Exception $e)
    {
      if ($e->getCode() == 1000)
      {
        $this->printPasswordForm();
      }

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
      header("Cache-Control: no-cache, must-revalidate");
      header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
      header("Content-Type: application/json");
      die(json_encode
      ([
        'replacePage' => $sLogin
      ]));
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