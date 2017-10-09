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

    if (!$oUser->active)
    {
      throw new \Exception("Invalid user/password");
    }

    if (!password_verify($this->oApi->pass, $oUser->password))
    {
      throw new \Exception("Invalid user/password");
    }

    return $oUser;
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

    try
    {
      $this->oUser = $this->generateUser();
    }
    catch (\Exception $e)
    {
      $this->logOut();
      $this->handleGenerateUserException($e);
    }
  }
}