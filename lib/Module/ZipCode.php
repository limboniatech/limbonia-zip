<?php
namespace Limbonia\Module;

/**
 * Limbonia Role Module class
 *
 * Admin module for handling groups
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class ZipCode extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule;

  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected static $sGroup = 'Hidden';

  /**
   * Activate this module and any required dependencies then return a list of types that were activated
   *
   * @param array $hActiveModule - the active module list
   * @return array
   * @throws Exception on failure
   */
  public function activate(array $hActiveModule)
  {
    $oState = $this->oController->itemFactory('states');
    $oState->setup();
    return parent::activate($hActiveModule);
  }

  /**
   * Deactivate this module then return a list of types that were deactivated
   *
   * @param array $hActiveModule - the active module list
   * @return array
   * @throws Exception on failure
   */
  public function deactivate(array $hActiveModule)
  {
    throw new \Limbonia\Exception('The ZipCode module can not be deactivated');
  }
}