<?php
namespace Omniverse\Controller;

/**
 * Omniverse Admin Controller Class
 *
 * This extends the basic controller with the ability to display and react to
 * site administration pages
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Admin extends \Omniverse\Controller\Web
{
  /**
   * Echo the data field generated from the specified data
   *
   * @param string $sLabel
   * @param string $sValue
   */
  public function dataField($sLabel, $sValue)
  {
    return "        <div class=\"field\">
              <span class=\"label\">$sLabel</span>
              <span class=\"data\">$sValue</span>
            </div>\n";
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

  protected function renderPage()
  {
    $sModuleDriver = isset($this->oApi->module) ? \Omniverse\Module::driver($this->oApi->module) : '';

    if (!empty($sModuleDriver))
    {
      try
      {
        $oCurrentModule = $this->moduleFactory($sModuleDriver);
        $oCurrentModule->prepareTemplate();
        $sModuleTemplate = $oCurrentModule->getTemplate();

        if (isset($this->oApi->ajax))
        {
          if (isset($this->hTemplateData['currentItem']) && $this->hTemplateData['currentItem']->id > 0)
          {
            parent::outputJson
            ([
              'moduleType' => $oCurrentModule->getType(),
              'moduleOutput' => $this->templateRender($sModuleTemplate),
              'action' => $oCurrentModule->getCurrentAction(),
              'itemTitle' => $oCurrentModule->getCurrentItemTitle(),
              'subMenu' => $oCurrentModule->getSubMenuItems(true),
              'id' => $this->hTemplateData['currentItem']->id,
              'itemUri' => $oCurrentModule->generateUri($this->hTemplateData['currentItem']->id)
            ]);
          }

          parent::outputJson
          ([
            'moduleType' => $oCurrentModule->getType(),
            'moduleOutput' => $this->templateRender($sModuleTemplate),
            'action' => $oCurrentModule->getCurrentAction()
          ]);
        }

        $this->templateData('moduleOutput', $this->templateRender($sModuleTemplate));
      }
      catch (\Exception $e)
      {
        $this->templateData('failure', "The module {$this->oApi->module} could not be instaniated: " . $e->getMessage());

        if (isset($this->oApi->search['click']))
        {
          parent::outputJson
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
   * Generate and return the current user
   *
   * @return \Omniverse\Item\User
   * @throws \Exception
   */
  protected function generateUser()
  {
    $oUser = parent::generateUser();

    if ($oUser->id == 0)
    {
      $this->printPasswordForm();
    }

    if (!isset($_SESSION['LoggedInUser']))
    {
      $_SESSION['LoggedInUser'] = $oUser->id;
      \Omniverse\Module::overrideDriverList($this, $oUser);
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