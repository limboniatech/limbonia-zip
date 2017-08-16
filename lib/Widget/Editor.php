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
  protected static $iMinMoz = 20030210;
  protected static $iMinIE = 5.5;
  protected $sWidth = '100%';
  protected $sHeight = '200';
  protected $sToolbarSet = 'Default';
  protected $sValue = '';
  protected $hConfig = [];

  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->hConfig['customconfigurationspath'] = $this->sWebShareDir . '/WebEditConfig.js';
  }

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

  function isCompatible()
  {
    if (($iIEPos = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) !== FALSE && strpos($_SERVER['HTTP_USER_AGENT'], 'mac') === FALSE && strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') === FALSE)
    {
      return (float)substr($_SERVER['HTTP_USER_AGENT'], $iIEPos + 5, 3) >= self::$iMinIE;
    }
    elseif (($iMozPos = strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko/')) !== FALSE)
    {
      return (int)substr($_SERVER['HTTP_USER_AGENT'], $iMozPos + 6, 8) >= self::$iMinMoz;
    }
    return FALSE;
  }

  public function setText($sText)
  {
    if (!is_string($sText) || $this->bInit)
    {
      return FALSE;
    }

    $this->sValue = $sText;
  }

  public function setConfig($hConfig=array())
  {
    if (!is_array($hConfig))
    {
      $hConfig = array();
    }
    $this->hConfig = $hConfig;
  }

  public function addConfig($sKey, $xValue)
  {
    if (!is_string($sKey) && (is_array($xValue) || is_object($xValue)))
    {
      return FALSE;
    }
    $this->hConfig[$sKey] = $xValue;
    return TRUE;
  }

  public function setToolbar($sToolbar=NULL)
  {
    if (empty($sToolbar))
    {
      $sToolbar = 'Default';
    }
    $this->sToolbarSet = $sToolbar;
  }

  protected function encode($xValue)
  {
    if ($xValue === TRUE)
    {
      return 'true';
    }
    elseif ($xValue === FALSE)
    {
      return 'false';
    }
    return strtr($xValue, array('&' => '%26', '=' => '%3D', '"' => '%22'));
  }
}