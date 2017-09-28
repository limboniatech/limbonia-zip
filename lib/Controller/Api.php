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
class Api extends \Omniverse\Controller
{
  /**
   * The logged in user
   *
   * @var \Omniverse\Item\User
   */
  protected $oUser = null;

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
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header("Content-Type: application/json");

    ob_start();
    try
    {
      $this->login();

      if (is_null($this->api->module))
      {
        throw new \Exception('No module found');
      }

      $oModule = $this->moduleFactory($this->api->module);

      ob_end_clean();
      http_response_code(200);
      die(json_encode($oModule->processApi()));
    }
    catch (\Exception $e)
    {
      http_response_code($e->getCode());
      ob_end_clean();
      die(json_encode($e->getMessage()));
    }
  }

  /**
   * Figure out if there is a valid current user or if the login screen should be displayed
   *
   * @return boolean
   * @throws \Exception
   */
  protected function login()
  {
    if ($this->oApi->module == 'logout')
    {
      $_SESSION = [];
      session_destroy();
      header('Location: ' . $this->baseUrl);
    }

    if (!isset($_SESSION['Email']))
    {
      throw new \Exception('Current login not found', 401);
    }

    try
    {
      //A Email stored in the session data shouldn't ever be NULL so we use === for the comparison...
      if (isset($this->hConfig['master']) && !empty($this->hConfig['master']['User']) && $_SESSION['Email'] === $this->hConfig['master']['User'])
      {
        $oUserList = $this->itemSearch('User', ['Email' => 'MasterAdmin']);
        $this->oUser = count($oUserList) == 0 ? false : $oUserList[0];
      }
      else
      {
        $this->oUser = \Omniverse\Item\User::getByEmail($_SESSION['Email'], $this->getDB());
      }

      if (empty($this->oUser))
      {
        throw new \Exception("User ({$_SESSION['Email']}) not found");
      }
    }
    catch (\Exception $e)
    {
      $_SESSION = [];
      session_destroy();
      throw new \Exception($e->getMessage(), 401);
    }
  }
}