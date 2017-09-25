<?php
namespace Omniverse\Widget;

/**
 * Omniverse Editor Widget
 *
 * A wrapper around an HTML editor
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Editor extends \Omniverse\Widget
{
  /**
   * The minimum version of Mozilla that this code will work with
   *
   * @var integer
   */
  protected static $iMinMoz = 20030210;

  /**
   * The minimum version of IE that this code will work with
   *
   * @var float
   */
  protected static $fMinIE = 5.5;

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
   * The name of the toolset that the the editor will use
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
   * @param \Omniverse\Controller $oController (optional)
   * @throws Omniverse\Exception\Object
   */
  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->hConfig['customconfigurationspath'] = $this->sWebShareDir . '/WebEditConfig.js';
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    $sConfig = NULL;
    $bFirst = TRUE;

    foreach ($this->hConfig as $sKey => $xValue)
    {
      if ($bFirst)
      {
        $bFirst = FALSE;
      }
      else
      {
        $sConfig .= '&amp;';
      }

      $sConfig .= $this->encode($sKey) . '=' . $this->encode($xValue);
    }

    $sValue = htmlspecialchars($this->sValue);
    $this->sPreScript = "<div>\n";

    if ($this->IsCompatible())
    {
      $sLink = $this->sWebShareDir . "/WebEdit/editor/fckeditor.html?InstanceName=$this->sName";

      if (!empty($this->sToolbarSet))
      {
        $sLink .= "&amp;Toolbar=$this->sToolbarSet";
      }

      // Render the linked hidden field.
      $this->sPreScript .= "  <input type=\"hidden\" id=\"$this->sName\" name=\"$this->sName\" value=\"$sValue\" />\n";

      // Render the configurations hidden field.
      $this->sPreScript .= "  <input type=\"hidden\" id=\"{$this->sName}___Config\" value=\"$sConfig\" />\n";

      // Render the editor IFRAME.
      $this->sPreScript .= "  <iframe id=\"{$this->sName}___Frame\" src=\"$sLink\" width=\"$this->sWidth\" height=\"$this->sHeight\" frameborder=\"no\" scrolling=\"no\"></iframe>\n";
    }
    else
    {
      $sWidth = $this->sWidth . ((strpos($this->sWidth, '%') === FALSE) ? 'px' : NULL);
      $sHeight = $this->sHeight . ((strpos($this->sHeight, '%') === FALSE) ? 'px' : NULL);
      $this->sPreScript .= "  <textarea name=\"$this->sName\" rows=\"4\" cols=\"40\" style=\"width: $sWidth; height: $sHeight\" wrap=\"virtual\">{$sValue}</textarea>\n";
    }

    $this->sPreScript .= "</div>\n";
    return TRUE;
  }

  /**
   * Is the browser that is currently running compatable with this editor?
   *
   * @return boolean
   */
  function isCompatible()
  {
    $sUserAgent = $this->getController->server['HTTP_USER_AGENT'];
    $iIEPos = strpos($sUserAgent, 'MSIE');

    if ($iIEPos !== false && strpos($sUserAgent, 'mac') === FALSE && strpos($sUserAgent, 'Opera') === FALSE)
    {
      return (float)substr($sUserAgent, $iIEPos + 5, 3) >= self::$fMinIE;
    }

    $iMozPos = strpos($sUserAgent, 'Gecko/');

    if ($iMozPos !== false)
    {
      return (int)substr($sUserAgent, $iMozPos + 6, 8) >= self::$iMinMoz;
    }

    return false;
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