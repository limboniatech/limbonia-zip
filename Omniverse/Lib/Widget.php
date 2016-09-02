<?php
/**
* Omnisys Widget base class
*
* This defines all the
*
* @author Lonnie Blansett <lonnie@omniverserpg.com>
* @version $Revision: 1.11 $
* @package OmniLib
*/
namespace Omniverse\Lib;

class Widget extends Tag
{
  /**
  * @var integer $iCount - number of existing widgets.
  * @access private
  */
  private static $iCount = 0;

  /**
  * @var array $aIncludedScript - the list of already included scripts.
  * @access private
  */
  private static $aIncludedScript = array();

  /**
  * @var string $sName - unique object name for use in submitting form data.
  * @access protected
  */
  protected $sName = 'OmnisysWidget';

  /**
  * @var string $sID - unique object id for use in the scripts.
  * @access protected
  */
  protected $sID = 'OmnisysWidget';

  /**
  * @var string $sType -
  * @access protected
  */
  protected $sType = '';

  /**
  * @var string $sPreScript - HTML to be written out *before* the javascript stuff...
  * @access protected
  */
  protected $sPreScript = '';

  /**
  * @var string $sScript - javascript that forms the main functionality of the object.
  * @access protected
  */
  protected $sScript = '';

  /**
  * @var string $sPostScript - HTML to be written out *after* the javascript stuff...
  * @access protected
  */
  protected $sPostScript = '';

  /**
  * @var array $aScript - a list of required javascripts to included.
  * @access protected
  */
  protected $aScript = array();

  /**
  * @var string $sWarningColor - color to be used for HTML warning messages
  * @access protected
  */
  protected static $sWarningColor = 'orange';

  /**
  * @var string $sErrorColor - color to be used for HTML error messages
  * @access protected
  */
  protected static $sErrorColor = 'red';

  /**
  * @var boolean $bAjaxStatus - update status bar in AJAX functions?
  * @access protected
  */
  protected static $bAjaxStatus = FALSE;

  /**
  * @var boolean $bAjaxDebug - use debug mode for AJAX functions?
  * @access protected
  */
  protected static $bAjaxDebug = FALSE;

  /**
  * @var array $aAjaxFunction - list of registerd AJAX functions
  * @access protected
  */
  protected static $aAjaxFunction = array();

  protected $IsCachable = TRUE;

  /**
  * Constructor
  *
  * It increments the widget counter and generates a unique (but human readable) name.
  *
  * @access public
  */
  public function __construct($sName=NULL, $sType=NULL)
  {
    $this->sType = strtolower(empty($sType) ? str_replace(__CLASS__.'_', '', get_class($this)) : $sType);
    if (empty($this->sType))
    {
      throw new Omnisys_Exception_Object(__CLASS__ . " couldn't find a valid type!");
    }

    self::$iCount++;
    $this->sName = empty($sName) ? 'Omnisys' . $this->sType . self::widgetCount() : $sName;
    $this->setParam('name', $this->sName);
    $this->sID = preg_replace("/\[|\]/", "", $this->sName);
    $this->setParam('id', $this->sID);
  }

  /**
  * factory method that creates an instance of a specific type of widget.
  * It must be called statically.
  *
  * @param string $sType - The type of widget to instanciate
  * @return "mixed" - The object requested on success, otherwise FALSE.
  * @access public
  */
  static public function factory($sType, $sName=NULL)
  {
    try
    {
      return Omnisys_API::factory(__CLASS__, $sType, $sName);
    }
    catch (Exception $o)
    {
      return new Omnisys_Widget($sName, $sType);
    }
  }

  public function isCachable()
  {
    return $this->IsCachable;
  }

  public function isSessionCachable()
  {
    return (!$this->IsCachable && isset($_SESSION));
  }

  //for this function we use pass by value so that we
  // don't even have to pass the array back...
  public function expandParameters(&$hParam)
  {
    if (isset($hParam['expand']))
    {
      $aExpansions = explode(",", $hParam['expand']);
      unset($hParam['expand']);
      foreach ($aExpansions as $sExpansion)
      {
        list ($sLocation, $sName) = explode(":", $sExpansion);
        $sLocation = strtoupper($sLocation);
        $sValue = NULL;
        switch ($sLocation)
        {
          case 'SESSION':
            if (isset($_SESSION[$sName]))
            {
              $sValue = $_SESSION[$sName];
            }
            break;

          case 'POST':
            if (isset($_POST[$sName]))
            {
              $sValue = $_POST[$sName];
            }
            break;

          case 'GET':
            if (isset($_GET[$sName]))
            {
              $sValue = $_GET[$sName];
            }
            break;

          case 'MODULE':
            if (isset($this->$sName))
            {
              $sValue = $this->$sName;
            }
            break;
        }

        foreach ($hParam as $sParamName => $sParamValue)
        {
          $hParam[$sParamName] = str_replace($sName, $sValue, $sParamValue);
        }
      }
    }
  }

  protected function setSessionCache($sName, $xData)
  {
    $_SESSION[$this->sModuleName][$sName] = $xData;
  }

  protected function getSessionCache($sName)
  {
    return isset($_SESSION[$this->sModuleName][$sName]) ? $_SESSION[$this->sModuleName][$sName] : NULL;
  }

  public function _processTarget()
  {
    return $this->toString();
  }

  public function processTarget()
  {
    if (isset($this->hParam['component']))
    {
      $sComponent = 'Component_' . $this->hParam['component'];
      if (method_exists($this, $sComponent))
      {
        unset($this->hParam['component']);
        return $this->$sComponent();
      }
    }

    return $this->_processTarget();
  }

  public function setAll($hParam=array())
  {
    $this->expandParameters($hParam);

    if (isset($hParam['html_text_to_filter']))
    {
      $this->addText($hParam['html_text_to_filter']);
      unset($hParam['html_text_to_filter']);
    }

    parent::setAll($hParam);
  }

  /**
  * Returns the current number of widgets that have been created.
  * It must be called statically.
  * It is final and may not be overridden by a child class.
  *
  * @return integer
  * @access protected
  */
  final static protected function widgetCount()
  {
    return self::$iCount;
  }

  /**
  * Stub create method that will be overridden by a child class.
  *
  * @return boolean
  * @access protected
  */
  protected function _create()
  {
    $this->sPreScript .= parent::toString();
    return TRUE;
  }

  /**
  * Does all redundant bits of creating the actual HTML / javascript widget.
  * It is final and may not be overridden by a child class.
  *
  * @return boolean
  * @access public
  */
  final public function create()
  {
    if ($this->bInit)
    {
      return TRUE;
    }

    if (!$this->_create())
    {
      $this->errorText("Widget \"$this->sName\" failed to initialize!");
      return FALSE;
    }

    self::ajaxClass($this, self::$bAjaxStatus);
    $this->bInit = TRUE;

    echo $this->sPreScript;

    if (count($this->aScript) > 0)
    {
      foreach ($this->aScript as $sScript)
      {
        self::includeScript($sScript);
      }
    }

    if (!empty($this->sScript))
    {
      $this->writeJavascript("\n".$this->sScript);
    }

    echo $this->sPostScript;

    return TRUE;
  }

  final public function toString()
  {
    ob_start();
    $this->create();
    return ob_get_clean();
  }

  /**
  * Include a javascript file for later use, while insuring that it hasn't already been included.
  *
  * @param string $sScript - Javascript file name.
  * @access public
  */
  public static function includeScript($sScript)
  {
    if (array_search($sScript, self::$aIncludedScript) === FALSE)
    {
      echo "\n<script type=\"text/javascript\" language=\"javascript\" src=\"$sScript\"></script>\n";
      self::$aIncludedScript[] = $sScript;
    }
  }

  /**
  * Write a chunk of javascript to HTML page if the widget has already been created
  * otherwise save it for writing when that happens.
  *
  * @param string $sCommand - Javascript to write.
  * @access public
  */
  public function writeJavascript($sCommand)
  {
    if ($this->bInit)
    {
      echo "\n<script type=\"text/javascript\" language=\"javascript\">$sCommand</script>\n";
    }
    else
    {
      $this->sScript .= $sCommand."\n";
    }
  }

  public function write($sData, $bPre=TRUE)
  {
    if ($this->bInit)
    {
      echo "\n$sData";
    }
    else
    {
      if ($bPre)
      {
        $this->sPreScript .= $sData;
      }
      else
      {
        $this->sPostScript .= $sData;
      }
    }
  }

  public function writeLn($sData, $bPre=TRUE)
  {
    $this->write($sData."\n", $bPre);
  }

  /**
  * This "setter" changes the color of HTML warning messages created by this class.
  *
  * @param string $sColor - The new warning color.
  * @access public
  */
  public static function warningColor($sColor='orange')
  {
    self::$sWarningColor = $sColor;
  }

  /**
  * This "setter" changes the color of HTML error messages created by this class.
  *
  * @param string $sColor - The new error color.
  * @access public
  */
  public static function errorColor($sColor='red')
  {
    self::$sErrorColor = $sColor;
  }

  /**
  * Write an HTML error.
  *
  * @param string $sText - Text of the error to write.
  * @access public
  */
  public static function errorText($sText)
  {
    echo "<br><font color=\"" . self::$sErrorColor . "\">Error: </font>$sText<br>\n";
  }

  /**
  * Write an HTML warning.
  *
  * @param string $sText - Text of the warning to write.
  * @access public
  */
  public static function warningText($sText)
  {
    echo "<br><font color=\"" . self::$sWarningColor . "\">Warning: </font>$sText<br>\n";
  }

  public function getName()
  {
    return $this->sName;
  }

  public function getID()
  {
    return $this->sID;
  }

  public static function isWidget($oWidget)
  {
    return ($oWidget instanceof Omnisys_Widget);
  }

  public function addWidget($oWidget)
  {
    if (self::IsWidget($oWidget))
    {
      $this->addContent($oWidget);
    }
  }

  public function removeWidget($oWidget)
  {
    if (self::IsWidget($oWidget))
    {
      $this->removeContent($oWidget);
    }
  }

  //AJAX functionality

  public function addAjaxFunction($sFunction, $bReportStatus=NULL, $sClassName=NULL)
  {
    $sClassName = empty($sClassName) ? get_class($this) : $sClassName;
    ob_start();
    $sAjaxFunction = self::ajaxFunction($sClassName, $sFunction, $bReportStatus);
    $sJavascript = ob_get_contents();
    ob_end_clean();
    $this->write($sJavascript);
    return $sAjaxFunction;
  }

  public static function ajaxFunction($sClass, $sFunction, $bReportStatus=NULL)
  {
    $sClass = preg_replace("#^Omnisys_#", '', $sClass);
    $sAjaxFunction = "Omnisys_$sFunction";
    if (!isset(self::$aAjaxFunction[$sAjaxFunction]))
    {
      self::includeScript(Omnisys_API::getValue('WebShare') . "/ajax.js");
      $sReportStatus = $bReportStatus === TRUE ? 'true' : 'false';
      $sDebug = self::$bAjaxDebug === TRUE ? 'true' : 'false';

      echo "\n<script type=\"text/javascript\" language=\"javascript\">function $sAjaxFunction(){Omnisys_HttpRequest('$sClass', '$sFunction', arguments, $sReportStatus, $sDebug);}</script>\n";
      self::$aAjaxFunction[] = $sAjaxFunction;
    }
    return $sAjaxFunction;
  }

  public static function ajaxClass($oClass, $bReportStatus=FALSE)
  {
    $sClass = get_class($oClass);
    $aMethod = get_class_methods($oClass);
    foreach ($aMethod as $sMethod)
    {
      if (preg_match("/^ajax_/", $sMethod))
      {
        self::ajaxFunction($sClass, $sMethod, $bReportStatus);
      }
    }
  }

  public static function ajaxDebug($bDebug=TRUE)
  {
    if (!empty($bDebug) && is_bool($bDebug))
    {
      self::$bAjaxDebug = $bDebug;
    }
    else
    {
      return self::$bAjaxDebug;
    }
  }

  public static function ajaxStatus($bReportStatus=TRUE)
  {
    if (!empty($bReportStatus) && is_bool($bReportStatus))
    {
      self::$bAjaxStatus = $bReportStatus;
    }
    else
    {
      return self::$bAjaxStatus;
    }
  }

  public static function ajaxList()
  {
    return self::$aAjaxFunction;
  }
}
?>