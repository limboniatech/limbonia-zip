<?php
namespace Limbonia\Controller;

/**
 * Limbonia CLI Controller Class
 *
 * This allows the basic controller to run in the command line environment
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Cli extends \Limbonia\Controller
{
  /**
   * This constant tells processOptions that this option may *not* have a value associated with it
   */
  const OPTION_VALUE_NONE = 0;

  /**
   * This constant tells processOptions that this option may have a value associated with it but is not required
   */
  const OPTION_VALUE_ALLOW = 1;

  /**
   * This constant tells processOptions that this option must have a value associated with it
   */
  const OPTION_VALUE_REQUIRE = 2;

  /**
   * The name of the template to display
   *
   * @var string
   */
  protected $sTemplateName = '';

  /**
   * The description of the template to display in the help
   *
   * @var string
   */
  protected $sTempalteDesc = 'This utility does nothing but display this help including available modes';

  /**
   * List of command line options that should be processed and what they do
   *
   * @var array
   */
  protected $hOptionList =
  [
    [
      'short' => 'h',
      'long' => 'help',
      'desc' => 'Print this help screen',
      'value' => self::OPTION_VALUE_NONE
    ],
    [
      'long' => 'debug',
      'desc' => 'Set the debug level of this utility, if no value is specified then it defaults to highest debug level available.',
      'value' => self::OPTION_VALUE_ALLOW
    ]
  ];

  protected $sMode = '';

  protected $sCliName = '';

  /**
   * The CLI controller constructor
   *
   * NOTE: This constructor should only be used by the factory and *never* directly
   *
   * @param array $hConfig - A hash of configuration data
   */
  protected function __construct(array $hConfig = [])
  {
    $oServer = \Limbonia\Input::singleton('server');
    $this->sCliName = preg_replace("/^limbonia_/", '', basename($oServer['argv'][0]));

    $hOptions = getopt('', ['mode::']);
    $this->sMode = empty($hOptions) ? $this->sCliName : $hOptions['mode'];

    $this->oRouter = \Limbonia\Router::fromUri(strtolower(preg_replace("#_#", '/', $this->sMode)));
    parent::__construct($hConfig);
  }

  /**
   * Display the help information
   */
  public function displayHelp()
  {
    $sHelp = "
Usage:
$this->sTemplateName [options]

$this->sTempalteDesc

Options:\n";

    $iMaxLength = 2;

    foreach ($this->hOptionList as $hOption)
    {
      if (isset($hOption['long']))
      {
        $iTemp = strlen($hOption['long']);

        if ($iTemp > $iMaxLength)
        {
          $iMaxLength = $iTemp;
        }
      }
    }

    foreach ($this->hOptionList as $hOption)
    {
      $bShort = isset($hOption['short']);
      $bLong = isset($hOption['long']);

      if (!$bShort && !$bLong)
      {
        continue;
      }

      if ($bShort && $bLong)
      {
        $sOpt = '-' . $hOption['short'] . ', --' . $hOption['long'];
      }
      elseif ($bShort && !$bLong)
      {
        $sOpt = '-' . $hOption['short'] . "\t";
      }
      elseif (!$bShort && $bLong)
      {
        $sOpt = '    --' . $hOption['long'];
      }

      $sOpt = str_pad($sOpt, $iMaxLength + 7);
      $sHelp .= "\t$sOpt\t\t{$hOption['desc']}\n\n";
    }

    die($sHelp . "\n");
  }

  /**
   * Update the template description to the specified value
   *
   * @param string $sDesc
   */
  public function setDescription($sDesc)
  {
    $this->sTempalteDesc = $sDesc;
  }

  /**
   * Process the specified command line options against the internal option list and return the list of active options
   *
   * @return array
   */
  public function processOptions()
  {
    $sShortOptions = '';
    $aLongOptions = [];

    foreach ($this->hOptionList as $hOption)
    {
      $sOptionValueMod = '';

      if (isset($hOption['value']))
      {
        if ($hOption['value'] == self::OPTION_VALUE_ALLOW)
        {
          $sOptionValueMod = '::';
        }
        elseif ($hOption['value'] == self::OPTION_VALUE_REQUIRE)
        {
          $sOptionValueMod = ':';
        }
      }

      if (isset($hOption['short']))
      {
        $sShortOptions .= $hOption['short'] . $sOptionValueMod;
      }

      if (isset($hOption['long']))
      {
        $aLongOptions[] = $hOption['long'] . $sOptionValueMod;
      }
    }

    $hActiveOptions = getopt($sShortOptions, $aLongOptions);

    if (isset($hActiveOptions['h']) || isset($hActiveOptions['help']))
    {
      $this->displayHelp();
    }

    return $hActiveOptions;
  }

  /**
   * Add a new option to the internal option list
   *
   * @param array $hOption
   */
  public function addOption($hOption)
  {
    $this->hOptionList[] = $hOption;
  }

  /**
   * Determine the template that should be used at this time and return it
   *
   * @return string
   * @throws \Exception
   */
  protected function generateTemplateFile()
  {
    $sTemplateFile = $this->templateFile($this->sCliName);

    //if the template file is not empty and not the current running file
    if (!empty($sTemplateFile) && $sTemplateFile !== $this->sCliName)
    {
      //then return it as is...
      $this->sTemplateName = $this->sCliName;
      return $sTemplateFile;
    }

    //attempt to use the mode for the template
    $sTemplateFile = $this->templateFile($this->sMode);

    if (!empty($sTemplateFile))
    {
      $this->sTemplateName = $this->sMode;
      return $sTemplateFile;
    }

    return $this->templateFile('default');
  }

  /**
   * Render this controller instance for output and return that data
   *
   * @return string
   */
  protected function render()
  {
    $sModuleDriver = \Limbonia\Module::driver($this->oRouter->module);

    if (empty($sModuleDriver))
    {
      try
      {
        $aAvailableModes = [];

        foreach (\Limbonia\Controller::templateDirs() as $sDir)
        {
          foreach (glob($sDir . '/' . $this->type . '/*.php') as $sFileName)
          {
            $aAvailableModes[] = basename($sFileName, '.php');
          }

          foreach (glob($sDir . '/' . $this->type . '/*/*.php') as $sFileName)
          {
            $aAvailableModes[] = basename(dirname($sFileName)) . '_' . basename($sFileName, '.php');
          }
        }

        $aAvailableModes = array_unique($aAvailableModes);
        sort($aAvailableModes);
        $iPos = array_search('default', $aAvailableModes);

        if (false !== $iPos)
        {
          unset($aAvailableModes[$iPos]);
        }

        $iPos = array_search('error', $aAvailableModes);

        if (false !== $iPos)
        {
          unset($aAvailableModes[$iPos]);
        }

        if (count($aAvailableModes) > 0)
        {
          $this->addOption
          ([
            'long' => 'mode',
            'value' => \Limbonia\Controller\Cli::OPTION_VALUE_REQUIRE,
            'desc' => "This utility has the following built-in modes:\n\t\t\t\t" . implode("\n\t\t\t\t", $aAvailableModes)
          ]);
        }

        $this->processOptions();
        return $this->templateRender($this->generateTemplateFile());
      }
      catch (Exception $e)
      {
        $this->templateData('failure', 'Failed to generate the requested data: ' . $e->getMessage());
        return $this->templateRender('error');
      }
    }

    try
    {
      $oCurrentModule = $this->moduleFactory($sModuleDriver);
      $this->sTemplateName = strtolower($sModuleDriver) . '_' . $this->oRouter->action;
      $oCurrentModule->prepareTemplate();
      $this->templateData('options', $this->processOptions());
      $sModuleTemplate = $oCurrentModule->getTemplate();
      return $this->templateRender($sModuleTemplate);
    }
    catch (\Exception $e)
    {
      $this->templateData('failure', "The module {$this->oRouter->module} could not be instaniated: " . $e->getMessage());
      return $this->templateRender('error');
    }
  }

  /**
   * Run everything needed to react to input and display data in the way this controller is intended
   */
  public function run()
  {
    try
    {
      $this->templateData('controller', $this);
      $this->oUser = $this->generateUser();
    }
    catch (\Exception $e)
    {
      echo $e->getMessage() . "\n";
    }

    $this->templateData('currentUser', $this->oUser);
    die($this->render());
  }
}