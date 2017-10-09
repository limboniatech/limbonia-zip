<?php
namespace Omniverse\Module;

/**
 * Omniverse Cache Module class
 *
 * Admin module for handling the site's cache
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Cache extends \Omniverse\Module
{
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'Site';

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'reset';

  /**
   * List of components that this module contains along with their descriptions
   *
   * @var array
   */
  protected $hComponent =
  [
    'reset' => "Reset all the cache for this site."
  ];

  /**
   * List of menu items that this module should display
   *
   * @var array
   */
  protected $hMenuItems = ['reset' => 'Reset'];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['reset'];

  /**
   * List of valid HTTP methods
   *
   * @var array
   */
  protected static $hHttpMethods =
  [
    'head',
    'get',
    'delete',
    'options'
  ];

  /**
   * Perform the base "GET" code then return null on success
   *
   * @return null
   * @throws \Exception
   */
  protected function processApiHead()
  {
    if (!is_dir($this->oController->cacheDir) || !is_readable($this->oController->cacheDir) || !is_writable($this->oController->cacheDir))
    {
      throw new \Exception('Valid cache director not found');
    }

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
    $oCache = $this->oController->cacheFactory();
    return $oCache->files();
  }

  /**
   * Run the default "DELETE" code and return true
   *
   * @return boolean - True on success
   * @throws \Exception
   */
  protected function processApiDelete()
  {
    if ($this->oController->user()->hasResource('Site', 'Reset'))
    {
      $oCache = $this->oController->cacheFactory();
      $oCache->clear();
    }
  }

  /**
   * Run the reset code and display the results
   */
  protected function prepareTemplatePostReset()
  {
    if ($this->oController->user()->hasResource('Site', 'Reset'))
    {
      $oCache = $this->oController->cacheFactory();
      $this->oController->templateData('success', $oCache->clear());
    }
  }
}