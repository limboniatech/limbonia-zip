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

  protected function prepareTemplatePostReset()
  {
    if ($this->oController->user()->hasResource('Site', 'Reset'))
    {
      $oCache = \Omniverse\Cache::factory();
      $this->oController->templateData('success', $oCache->clear());
    }
  }
}