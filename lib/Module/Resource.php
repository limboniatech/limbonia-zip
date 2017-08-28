<?php
namespace Omniverse\Module;

/**
 * Omniverse Resource Module class
 *
 * Admin module for handling site resource keys
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Resource extends \Omniverse\Module
{
  /**
   * List of menu items that this module shoud display
   *
   * @var array
   */
  protected $aMenuItems = array('List', 'Create');

    /**
   * Instantiate the resource module
   *
   * @param string $sType (optional) - The type of module this should become
   * @param \Omniverse\Controller $oController
   */
public function __construct($sType = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct('ResourceKey', $oController);
  }
}