<?php
namespace Limbonia\Module;

/**
 * Limbonia Cache Module class
 *
 * Admin module for handling the site's cache
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Auth extends \Limbonia\Module
{
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'Hidden';

  /**
   * List of valid HTTP methods
   *
   * @var array
   */
  protected static $hHttpMethods =
  [
    'head',
    'get',
    'post',
    'delete',
    'options'
  ];

  /**
   * A list of components the current user is allowed to use
   *
   * @var array
   */
  protected $hAllow =
  [
    'create' => true,
    'delete' => true
  ];

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    $this->oController->getDB();
    return null;
  }

  /**
   * Perform and return the default "GET" code
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGet()
  {
    return $this->oController->getDB()->query("SELECT * FROM UserAuth");
  }

  /**
   * Run the default "POST" code and return the created data
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiPost()
  {
    $hData = $this->oRouter->data;

    if (empty($hData['email']) || empty($hData['password']))
    {
    }

      if ($hData['email'] === $this->oController->master['User'] && $hData['password'] === $this->oController->master['Password'])
      {
        $oUser = \Limbonia\Controller::generateUser();
      }
      else
      {
        $oUser = $this->oController->userByEmail($hData['email']);
        $oUser->authenticate($hData['password']);
      }

    return
    [
      'auth_token' => $oUser->generateAuthToken(),
      'user' => $oUser->getAll()
    ];
  }

  /**
   * Run the default "DELETE" code and return true
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiDelete()
  {
    $this->oController->user()->deleteAuthToken($this->oRouter->action);
    return null;
  }
}