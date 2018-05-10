<?php
namespace Limbonia\Widget;

/**
 * Limbonia Window Widget
 *
 * A wrapper around an new window
 *
 * @todo This class needs to be reworked to be more modern and further DocBlocks will wait until *after* the rewrite
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Window extends \Limbonia\Widget
{
  /**
   * The configuration data
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * The javascript window parameters
   *
   * @var array
   */
  protected  $hWindowParam =
  [
     //The y-coordinate of the top-left hand corner of the window
    'top' => 100,

    //The x-coordinate of the top-left hand corner of the window
    'left' => 100,

    //The window height
    'height' => 300,

    //The window width
    'width' => 500,

    //Should the window tool bar be displayed?
    'toolbar' => false,

    //Should the window menu bar be displayed?
    'menubar' => false,

    //Should the window location line be displayed?
    'location' => false,

    //Should the window status bar be displayed?
    'status' => false,

    //Should the window scroll bars be displayed?
    'scrollbars' => false,

    //Should the window be resizable?
    'resizable' => false
  ];

  /**
   * The URL to be display in the window, if there is one
   *
   * @var string
   */
  protected $sURL = '';

  protected $sScriptName = '';

  /**
   * The content to display, if not displaying a URL
   *
   * @var string
   */
  protected $sContent = '';

  /**
   * The click handler for displaying this window
   *
   * @var string
   */
  protected $sOnClick = '';

  /**
   * Return the stringified numeric representation of the specified boolean variable
   *
   * @param boolean $bOption
   * @return string
   */
  protected static function scriptBoolean($bOption)
  {
    return (boolean)$bOption ? '1' : '0';
  }

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
    $this->sOnClick = " onClick=\"show{$this->sId}()\"";
    //$this->aScript = [$this->sWebShareDir . '/window.js'];
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    $this->sScript .= "var {$this->sId}Target = document.getElementById('{$this->sId}');\n";
    $this->sScript .= "function show{$this->sId}(sURL)\n";
    $this->sScript .= "{\n";
    $sURL = null;

    if (!empty($this->sURL))
    {
      $sURL = $this->sURL;

      if (count($this->hConfig) > 0)
      {
        $sURL .= (strrpos($this->sURL, "?") === FALSE ? '?' : '&');
        $sURL .= 'Config='.rawurlencode(gzdeflate(serialize($this->hConfig)));
      }
    }

    $sToolbar = self::scriptBoolean($this->hWindowParam['toolbar']);
    $sMenubar = self::scriptBoolean($this->hWindowParam['menubar']);
    $sLocation = self::scriptBoolean($this->hWindowParam['location']);
    $sStatus = self::scriptBoolean($this->hWindowParam['status']);
    $sScrollBars = self::scriptBoolean($this->hWindowParam['scrollbars']);
    $sResizable = self::scriptBoolean($this->hWindowParam['resizable']);

    $this->sScript .= "  sURL = (arguments.length > 0) ? sURL : '$sURL';\n";
    $this->sScript .= "  var {$this->sId} = window.open(sURL, '{$this->sId}', 'top={$this->hWindowParam['top']},left={$this->hWindowParam['left']},width={$this->hWindowParam['width']},height={$this->hWindowParam['height']},toolbar={$sToolbar},menubar={$sMenubar},location={$sLocation},status={$sStatus},scrollbars={$sScrollBars},resizable={$sResizable}');\n";
    $this->sScript .= "  ".$this->sId.".opener = self;\n";

    if (!empty($this->sContent))
    {
      $this->sScript .= '  '.$this->sName.".document.write(unescape('".rawurlencode($this->sContent)."'));\n";
    }

    $this->sScript .= "  if (window.focus)\n";
    $this->sScript .= "  {\n";
    $this->sScript .= "    {$this->sId}.focus();\n";
    $this->sScript .= "  }\n";
    $this->sScript .= "}\n";
    return true;
  }

  /**
   * This is a new version of the init method that uses the "new" showLimboniaWindow JavaScript function...
   *
   * @todo This needs to be worked on... <b>or decide if it (and the showLimboniaWindow function) are even needed at all!</b>
   *
   * @return boolean
   */
  protected function initBeta()
  {
    $hWindowParam = $this->hWindowParam;

    if (!empty($this->sURL))
    {
      $hWindowParam['url'] = $this->sURL;

      if (count($this->hConfig) > 0)
      {
        $hWindowParam['url'] .= (strrpos($this->sURL, "?") === FALSE ? '?' : '&');
        $hWindowParam['url'] .= 'Config='.rawurlencode(gzdeflate(serialize($this->hConfig)));
      }
    }

    $this->sScript .= "var {$this->sId}Target = document.getElementById('{$this->sId}');\n";
    $this->sScript .= "function show{$this->sId}()\n";
    $this->sScript .= "{\n";
    $this->sScript .= "  {$this->sId}Target = showLimboniaWindow(" . json_encode($hWindowParam) . ");\n";
    $this->sScript .= "}\n";
    return true;
  }

  /**
   * The URL to display in the window
   *
   * @param string $sURL
   */
  public function setURL($sURL = '')
  {
    $this->sURL = (string)$sURL;
  }

  /**
   * Add a button that will run the click handler
   *
   * @staticvar int $iButtonCount
   * @param string $sText
   */
  public function button($sText)
  {
    static $iButtonCount = 0;
    $sButton = "<input type=\"Button\" name=\"".$this->sName."Button$iButtonCount\" id=\"".$this->sId."Button$iButtonCount\" value=\"$sText\"$this->sOnClick>";
    $iButtonCount++;

    if ($this->bInit)
    {
      echo $sButton;
    }
    else
    {
      $this->sPostScript .= $sButton;
    }
  }

  /**
   * Add a clickable image that will run the click handler
   *
   * @staticvar int $iImageCount
   * @param string $sSrc
   */
  public function image($sSrc)
  {
    static $iImageCount = 0;
    $sImage = "<img src=\"$sSrc\" name=\"" . $this->sName . "Image$iImageCount\" id=\"" .$this->sId. "Image$iImageCount\"$this->sOnClick>";
    $iImageCount++;

    if ($this->bInit)
    {
      echo $sImage;
    }
    else
    {
      $this->sPostScript .= $sImage;
    }
  }

  /**
   * Generate text that will open the window
   *
   * @param string $sText
   */
  public function text($sText)
  {
    $sText = "<a href=\"javascript:show" . $this->sId . "();\">$sText</a>";

    if ($this->bInit)
    {
      echo $sText;
    }
    else
    {
      $this->sPostScript .= $sText;
    }
  }

  /**
   * Set the named config variable with the specified value
   *
   * @param string $sName
   * @param string $sValue
   */
  public function setConfig($sName, $sValue)
  {
    if (!empty($sName) && !empty($sValue))
    {
      $this->hConfig[$sName] = $sValue;
    }
  }

  /**
   * Set the width of the window
   *
   * @param integer $iWidth
   */
  public function setWidth($iWidth)
  {
    if (is_numeric($iWidth))
    {
      $this->$this->hWindowParam['width'] = (integer)$iWidth;
    }
  }

  /**
   * Set the height of the window
   *
   * @param integer $iHeight
   */
  public function setHeight($iHeight)
  {
    if (is_numeric($iHeight))
    {
      $this->$this->hWindowParam['height'] = (integer)$iHeight;
    }
  }

  /**
   * Set the top edge of the window
   *
   * @param integer $iTop
   */
  public function setTop($iTop)
  {
    if (is_numeric($iTop))
    {
      $this->$this->hWindowParam['top'] = (integer)$iTop;
    }
  }

  /**
   * Set the left edge of the window
   *
   * @param integer $iLeft
   */
  public function setLeft($iLeft)
  {
    if (is_numeric($iLeft))
    {
      $this->$this->hWindowParam['left'] = (integer)$iLeft;
    }
  }

  /**
   * Should the status line be displayed?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function hasStatus($bOption = true)
  {
    $this->$this->hWindowParam['status'] = (boolean)$bOption;
  }

  /**
   * Should the menu bar be displayed?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function hasMenubar($bOption = true)
  {
    $this->$this->hWindowParam['menubar'] = (boolean)$bOption;
  }

  /**
   * Should the location bar be displayed?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function hasLocation($bOption = true)
  {
    $this->$this->hWindowParam['location'] = (boolean)$bOption;
  }

  /**
   * Should the tool bar be displayed?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function hasToolbar($bOption = true)
  {
    $this->$this->hWindowParam['toolbar'] = (boolean)$bOption;
  }

  /**
   * Should the scroll bars be displayed?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function hasScrollBars($bOption = true)
  {
    $this->$this->hWindowParam['scrollbars'] = (boolean)$bOption;
  }

  /**
   * Should the window allow resizing?
   *
   * @param boolean $bOption (optional) - defaults to true
   */
  public function allowResize($bOption = true)
  {
    $this->$this->hWindowParam['resizable'] = (boolean)$bOption;
  }
}