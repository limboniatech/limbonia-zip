<?php
namespace Limbonia\Widget;

/**
 * Limbonia Editor Widget
 *
 * A wrapper around an HTML editor
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Editor extends \Limbonia\Widget
{
  /**
   * The CSS width of this widget
   *
   * @var string
   */
  protected $sWidth = '100%';

  /**
   * The CSS height of this widget
   *
   * @var string
   */
  protected $sHeight = '200';

  /**
   * The name of the tool set that the the editor will use
   *
   * @var string
   */
  protected $sToolbarSet = 'Default';

  /**
   * The default value of the editor
   *
   * @var string
   */
  protected $sValue = '';

  /**
   * The configuration data for this editor
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * Constructor
   *
   * It increments the widget counter and generates a unique (but human readable) name.
   *
   * @param string $sName (optional)
   * @param \Limbonia\Controller $oController (optional)
   * @throws Limbonia\Exception\Object
   */
  public function __construct($sName = null, \Limbonia\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->aScript[] = $this->sWebShareDir . '/ckeditor/ckeditor.js';
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    $sConfig = null;
    $bFirst = true;

    foreach ($this->hConfig as $sKey => $xValue)
    {
      if ($bFirst)
      {
        $bFirst = false;
      }
      else
      {
        $sConfig .= '&amp;';
      }

      $sConfig .= $this->encode($sKey) . '=' . $this->encode($xValue);
    }

    $sWidth = $this->sWidth . ((strpos($this->sWidth, '%') === false) ? 'px' : null);
    $sHeight = $this->sHeight . ((strpos($this->sHeight, '%') === false) ? 'px' : null);
    $sValue = htmlspecialchars($this->sValue);
    $this->sPreScript = "<div>\n  <textarea name=\"$this->sName\" id=\"$this->sName\" rows=\"4\" cols=\"40\" style=\"width: $sWidth; height: $sHeight\" wrap=\"virtual\">{$sValue}</textarea>\n</div>\n";
    $this->sScript = "CKEDITOR.replace('$this->sName');";
    return true;
  }


  /**
   * Set the text for the editor
   *
   * @param string $sText
   * @return boolean
   */
  public function setText($sText)
  {
    if (is_string($sText) && !$this->bInit)
    {
      $this->sValue = $sText;
    }
  }

  /**
   * Set the specified array as the configuration for this editor
   *
   * @param array $hConfig
   */
  public function setConfig(array $hConfig)
  {
    $this->hConfig = $hConfig;
  }

  /**
   * Set the specified config key to the specified value
   *
   * @param string $sKey
   * @param mixed $xValue
   * @return boolean
   */
  public function addConfig($sKey, $xValue)
  {
    if (!is_string($sKey) && (is_array($xValue) || is_object($xValue)))
    {
      return false;
    }

    $this->hConfig[$sKey] = $xValue;
    return true;
  }

  /**
   * Set the
   *
   * @param string $sToolbar
   */
  public function setToolbar($sToolbar = 'Default')
  {
    $this->sToolbarSet = empty($sToolbar) ? 'Default' : $sToolbar;
  }

  /**
   * Return the encoded version of the specified value
   *
   * @param type $xValue
   * @return string
   */
  protected function encode($xValue)
  {
    if ($xValue === true)
    {
      return 'true';
    }

    if ($xValue === true)
    {
      return 'false';
    }

    return strtr($xValue, ['&' => '%26', '=' => '%3D', '"' => '%22']);
  }
}