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
   * The default action for this module
   *
   * @var string
   */
  protected $sDefaultAction = 'Display';

  /**
   * The default method for this module
   *
   * @var string
   */
  protected $sDefaultMethod = 'Reset';

  /**
   * List of components that this module contains along with thier descriptions
   *
   * @var array
   */
  protected $hComponent =
  [
    'Reset' => "Reset all the cache for this site."
  ];

  /**
   * List of menu items that this module shoud display
   *
   * @var array
   */
  protected $aMenuItems = ['Reset'];

  /**
   * List of methods that are allowed to run
   *
   * @var array
   */
  protected $aAllowedMethods = ['Reset'];

  /**
   * The name of the module
   *
   * @var string
   */
  protected $sModuleName = 'Cache';

  /**
   * The type of module this is
   *
   * @var string
   */
  protected $sType = 'Cache';

  /**
   * Prepare the template for display based on the current action and current method
   */
  public function prepareTemplate()
  {
    if ($this->sCurrentAction == 'Process')
    {
      if ($this->sCurrentMethod == 'Reset')
      {
        if (!$this->getController()->user()->hasResource('Site', 'Reset'))
        {
          return '';
        }

        $oCache = \Omniverse\Cache::factory();
        $this->getController()->templateData('success', $oCache->clear());
      }
    }
  }
}