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
    $sModuleDriver = isset($this->oApi->module) ? \Limbonia\Module::driver($this->oApi->module) : '';

    if (empty($sModuleDriver))
    {
      return $this->templateRender('index');
    }

    try
    {
      $oCurrentModule = $this->moduleFactory($sModuleDriver);
      $oCurrentModule->prepareTemplate();
      $sModuleTemplate = $oCurrentModule->getTemplate();

      if (isset($this->oApi->ajax))
      {
         return parent::outputJson(array_merge(['moduleOutput' => $this->templateRender($sModuleTemplate)], $oCurrentModule->getAdminOutput()));
      }

      $this->templateData('moduleOutput', $this->templateRender($sModuleTemplate));
      return $this->templateRender('index');
    }
    catch (\Exception $e)
    {
      $this->templateData('failure', "The module {$this->oApi->module} could not be instaniated: " . $e->getMessage());

      if (isset($this->oApi->search['click']))
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
      $this->printPasswordForm($e->getMessage());
    }

    if ($oUser->id == 0)
    {
      $this->printPasswordForm();
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

    if (isset($this->oApi->ajax))
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