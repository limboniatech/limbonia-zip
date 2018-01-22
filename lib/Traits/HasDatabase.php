<?php
namespace Omniverse\Traits;

/**
 * Omniverse HasController Trait
 *
 * This trait allows an inheriting class to have a database
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait HasDatabase
{
  /**
   * The controller for this object
   *
   * @var \Omniverse\Database
   */
  protected $oDatabase = null;

  /**
   * Set this object's database
   *
   * @param \Omniverse\Database $oDatabase
   */
  public function setDatabase(\Omniverse\Database $oDatabase = null)
  {
    $this->oDatabase = $oDatabase;
  }

  /**
   * Return this object's controller
   *
   * @return \Omniverse\Database
   */
  public function getDatabase(): \Omniverse\Database
  {
    if (is_null($this->oDatabase))
    {
      return \Omniverse\Controller::getDefault()->getDB();
    }

    return $this->oDatabase;
  }
}
