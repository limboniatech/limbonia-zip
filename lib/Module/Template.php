<?php
namespace Omniverse\Module;

/**
 * Omniverse Template Module class
 *
 * Admin module for handling site templates
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Template extends \Omniverse\Module
{
  /**
   * The admin group that this module belongs to
   *
   * @var string
   */
  protected $sGroup = 'Site';

  /**
   * A list of the actual module settings
   *
   * @var array
   */
  protected $hSettings =
  [
    'baseuri' => '',
    'basedir' => '',
    'templatedir' => '',
    'defaulttemplate' => ''
  ];

  /**
   * List of fields used by module settings
   *
   * @var array
   */
  protected $hFields =
  [
    'baseuri' => ['Type' => 'char'],
    'basedir' => ['Type' => 'char'],
    'templatedir' => ['Type' => 'char'],
    'defaulttemplate' => ['Type' => 'char']
  ];

  /**
   * Instantiate the template module
   *
   * @param \Omniverse\Controller $oController
   */
  public function __construct(\Omniverse\Controller $oController)
  {
    parent::__construct($oController);

    $this->hSettings['baseuri'] = $this->oController->baseUrl;
    $this->hSettings['basedir'] = dirname($this->oController->server['SCRIPT_FILENAME']);
    $this->hSettings['templatedir'] = $this->oController->getDir('templates');
    $this->hSettings['defaulttemplate'] = $this->oController->defaultTemplate;
  }
}