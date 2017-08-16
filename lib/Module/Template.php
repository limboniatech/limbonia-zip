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
  protected static $hModule = [];
  protected $sGroup = 'Site';
  protected $hSettings =
  [
    'baseuri' => '',
    'basedir' => '',
    'templatedir' => '',
    'defaulttemplate' => ''
  ];
  protected $hFields =
  [
    'baseuri' => ['Type' => 'char'],
    'basedir' => ['Type' => 'char'],
    'templatedir' => ['Type' => 'char'],
    'defaulttemplate' => ['Type' => 'char']
  ];

  public function __construct($sType = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sType, $oController);

    $this->hSettings['baseuri'] = dirname($_SERVER['PHP_SELF']);
    $this->hSettings['basedir'] = dirname($_SERVER['SCRIPT_FILENAME']);
    $this->hSettings['templatedir'] = $this->getController()->getDir('templates');
    $this->hSettings['defaulttemplate'] = $this->getController()->defaultTemplate;
  }
}