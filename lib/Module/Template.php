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
   * List of fields used by module settings
   *
   * @var array
   */
  protected static $hSettingsFields =
  [
    'baseuri' => ['Type' => 'char'],
    'basedir' => ['Type' => 'char'],
    'templatedir' => ['Type' => 'char'],
    'defaulttemplate' => ['Type' => 'char']
  ];

  /**
   * Return the default settings
   *
   * @return array
   */
  protected function defaultSettings()
  {
    return
    [
      'baseuri' => $this->oController->domain->uri,
      'basedir' => $this->oController->domain->path,
      'templatedir' => $this->oController->getDir('templates'),
      'defaulttemplate' => $this->oController->defaultTemplate
    ];
  }
}