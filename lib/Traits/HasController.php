<?php
namespace Omniverse\Traits;

/**
 * Omniverse HasController Trait
 *
 * This trait allows an inheriting class to have a controller
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait HasController
{
  /**
   * The controller for this object
   *
   * @var \Omniverse\Controller
   */
  protected $oController = null;

  /**
   * Set this object's controller
   *
   * @param \Omniverse\Controller $oController
   */
  public function setController(\Omniverse\Controller $oController)
  {
    $this->oController = $oController;
  }

  /**
   * Return this object's controller
   *
   * @return \Omniverse\Controller
   */
  public function getController(): \Omniverse\Controller
  {
    if (is_null($this->oController))
    {
      return \Omniverse\Controller::getDefault();
    }

    return $this->oController;
  }
}
