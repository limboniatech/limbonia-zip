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
  protected $sGroup = 'Site';
  protected $sDefaultAction = 'Display';
  protected $sDefaultMethod = 'Reset';
  protected $hComponent = array
  (
    'Reset' => "Reset all the cache for this site."
  );
  protected $aMenuItems = array('Reset');
  protected $aAllowedMethods = array('Reset');
  protected $sModuleName = 'Cache';
  protected $sType = 'Cache';

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