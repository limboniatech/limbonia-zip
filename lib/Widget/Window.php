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
   * The window width
   *
   * @var integer
   */
  protected $iWidth = 500;

  /**
   * The window height
   *
   * @var integer
   */
  protected $iHeight = 300;

  /**
   * The y-coordinate of the top-left hand corner of the window
   *
   * @var integer
   */
  protected $iTop = 100;

  /**
   * The x-coordinate of the top-left hand corner of the window
   *
   * @var integer
   */
  protected $iLeft = 100;

  /**
   * The URL to be display in the window, if there is one
   *
   * @var string
   */
  protected $sURL = '';

  /**
   *
   *
   * @var string
   */
  protected $sStatus = '0';

  protected $sResizable = '0';

  protected $sScrollBars = '0';

  protected $sToolbar = '0';

  protected $sMenubar = '0';

  protected $sLocation = '0';

  protected $sScriptName = null;

  protected $sContent = null;

  protected $sOnClick = null;

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
    $this->sOnClick = " onClick=\"show{$this->sID}()\"";
    //$this->aScript = [$this->sWebShareDir . '/window.js'];
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    $this->sScript .= "var {$this->sID}Target = document.getElementById('{$this->sID}');\n";
    $this->sScript .= "function show{$this->sID}(sURL)\n";
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

    $this->sScript .= "  sURL = (arguments.length > 0) ? sURL : '$sURL';\n";
    $this->sScript .= "  var {$this->sID} = window.open(sURL, '{$this->sID}', 'top={$this->iTop},left={$this->iLeft},width={$this->iWidth},height={$this->iHeight},toolbar={$this->sToolbar},menubar={$this->sMenubar},location={$this->sLocation},status={$this->sStatus},scrollbars={$this->sScrollBars},resizable={$this->sResizable}');\n";
    $this->sScript .= "  ".$this->sID.".opener = self;\n";

    if (!empty($this->sContent))
    {
      $this->sScript .= '  '.$this->sName.".document.write(unescape('".rawurlencode($this->sContent)."'));\n";
    }

    $this->sScript .= "  if (window.focus)\n";
    $this->sScript .= "  {\n";
    $this->sScript .= "    {$this->sID}.focus();\n";
    $this->sScript .= "  }\n";
    $this->sScript .= "}\n";
    return TRUE;
  }

  protected function _create_BETA()
  {
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

    $this->sScript .= "var {$this->sID}Target = document.getElementById('{$this->sID}');\n";
    $this->sScript .= "function show{$this->sID}()\n";
    $this->sScript .= "{\n";
    $this->sScript .= "  {$this->sID}Target = showLimboniawindow('$sURL', $this->iTop, $this->iLeft, $this->iWidth, $this->iHeight, $this->sToolbar, $this->sMenubar, $this->sLocation, $this->sStatus, $this->sScrollBars, $this->sResizable);\n";
    $this->sScript .= "}\n";
    return TRUE;
  }

  public function setURL($sURL=null)
  {
    $this->sURL = $sURL;
  }

  public function button($sText)
  {
    static $iButtonCount = 0;
    $sButton = "<input type=\"Button\" name=\"".$this->sName."Button$iButtonCount\" id=\"".$this->sID."Button$iButtonCount\" value=\"$sText\"$this->sOnClick>";
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

  public function image($sSrc)
  {
    static $iImageCount = 0;
    $sImage = "<img src=\"$sSrc\" name=\"" . $this->sName . "Image$iImageCount\" id=\"" .$this->sID. "Image$iImageCount\"$this->sOnClick>";
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

  public function text($sText)
  {
    $sText = "<a href=\"javascript:show" . $this->sID . "();\">$sText</a>";

    if ($this->bInit)
    {
      echo $sText;
    }
    else
    {
      $this->sPostScript .= $sText;
    }
  }

  public function setConfig($sName, $sValue)
  {
    if (!empty($sName) && !empty($sValue))
    {
      $this->hConfig[$sName] = $sValue;
    }
  }

  public function setWidth($iWidth)
  {
    if (is_numeric($iWidth))
    {
      $this->iWidth = (integer)$iWidth;
    }
  }

  public function setHeight($iHeight)
  {
    if (is_numeric($iHeight))
    {
      $this->iHeight = (integer)$iHeight;
    }
  }

  public function setTop($iTop)
  {
    if (is_numeric($iTop))
    {
      $this->iTop = (integer)$iTop;
    }
  }

  public function setLeft($iLeft)
  {
    if (is_numeric($iLeft))
    {
      $this->iLeft = (integer)$iLeft;
    }
  }

  protected static function scriptBoolean($bOption)
  {
    return $bOption ? '1' : '0';
  }

  public function hasStatus($bOption=TRUE)
  {
    $this->sStatus = self::scriptBoolean($bOption);
  }

  public function hasMenubar($bOption=TRUE)
  {
    $this->sMenubar = self::scriptBoolean($bOption);
  }

  public function hasLocation($bOption=TRUE)
  {
    $this->sLocation = self::scriptBoolean($bOption);
  }

  public function hasToolbar($bOption=TRUE)
  {
    $this->sToolbar = self::scriptBoolean($bOption);
  }

  public function hasScrollBars($bOption=TRUE)
  {
    $this->sScrollBars = self::scriptBoolean($bOption);
  }

  public function allowResize($bOption=TRUE)
  {
    $this->sResizable = self::scriptBoolean($bOption);
  }
}