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
  protected $aMenuItems = array('List', 'Create');

  public function __construct($sType=null, \Omniverse\Controller $oController = null)
  {
    parent::__construct('ResourceKey', $oController);
  }

}