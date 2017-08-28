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
   * @param string $sType (optional) - The type of module this should become
   * @param \Omniverse\Controller $oController
   */
  public function __construct($sType = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sType, $oController);

    $this->hSettings['baseuri'] = dirname($_SERVER['PHP_SELF']);
    $this->hSettings['basedir'] = dirname($_SERVER['SCRIPT_FILENAME']);
    $this->hSettings['templatedir'] = $this->getController()->getDir('templates');
    $this->hSettings['defaulttemplate'] = $this->getController()->defaultTemplate;
  }
}