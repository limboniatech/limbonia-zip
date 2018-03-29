<?php
namespace Limbonia\Traits;

/**
 * Limbonia HasController Trait
 *
 * This trait allows an inheriting class to have a controller
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
trait HasController
{
  /**
   * The controller for this object
   *
   * @var \Limbonia\Controller
   */
  protected $oController = null;

  /**
   * Set this object's controller
   *
   * @param \Limbonia\Controller $oController
   */
  public function setController(\Limbonia\Controller $oController)
  {
    $this->oController = $oController;
  }

  /**
   * Return this object's controller
   *
   * @return \Limbonia\Controller
   */
  public function getController(): \Limbonia\Controller
  {
    if (is_null($this->oController))
    {
      return \Limbonia\Controller::getDefault();
    }

    return $this->oController;
  }
}
