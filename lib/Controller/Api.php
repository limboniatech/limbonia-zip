<?php
namespace Omniverse\Controller;

/**
 * Omniverse API Controller Class
 *
 * This allows the basic controller retrieve data base on the API URL and return
 * that data in JSON format
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Api extends \Omniverse\Controller
{
  protected $sApi = '';
  protected $sRequestMethod = '';

  /**
   * The API controller constructor
   *
   * @param array $hConfig - A hash of configuration data
   */
  public function __construct(array $hConfig = [])
  {
    parent::__construct($hConfig);

    $this->sApi = $this->get['api'];
    $this->sRequestMethod = $this->server['REQUEST_METHOD'];
  }

  /**
   * Run everything needed to react and display data in the way this controller is intended
   */
  public function run()
  {
    ob_start();

    ob_end_clean();
    die($sReslult);
  }
}