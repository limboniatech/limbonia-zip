<?php
namespace Limbonia;

/**
 * Limbonia Widget base class
 *
 * This defines all the basic parts of an HTML widget
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Widget extends Tag
{
  use \Limbonia\Traits\DriverList;
  use \Limbonia\Traits\HasController;

  /**
   * @var integer $iCount - number of existing widgets.
   */
  private static $iCount = 0;

  /**
   * @var array $aIncludedScript - the list of already included scripts.
   */
  private static $aIncludedScript = [];

  /**
   * @var string $sName - unique object name for use in submitting form data.
   */
  protected $sName = 'LimboniaWidget';

  /**
   * @var string $sID - unique object id for use in the scripts.
   */
  protected $sId = 'LimboniaWidget';

  /**
   * @var string $sType - The type of widget this object represents
   */
  protected $sType = '';

  /**
   * @var string $sPreScript - HTML to be written out *before* the JavaScript stuff...
   */
  protected $sPreScript = '';

  /**
   * @var string $sScript - JavaScript that forms the main functionality of the object.
   */
  protected $sScript = '';

  /**
   * @var string $sPostScript - HTML to be written out *after* the JavaScript stuff...
   */
  protected $sPostScript = '';

  /**
   * @var array $aScript - a list of required JavaScripts to included.
   */
  protected $aScript = [];

  /**
   * @var string $sWarningColor - color to be used for HTML warning messages
   */
  protected static $sWarningColor = 'orange';

  /**
   * @var string $sErrorColor - color to be used for HTML error messages
   */
  protected static $sErrorColor = 'red';

  /**
   * @var boolean $bAjaxStatus - update status bar in AJAX functions?
   */
  protected static $bAjaxStatus = false;

  /**
   * @var boolean $bAjaxDebug - use debug mode for AJAX functions?
   */
  protected static $bAjaxDebug = false;

  /**
   * @var array $aAjaxFunction - list of registered AJAX functions
   */
  protected static $aAjaxFunction = [];

  /**
   * Is this widget allowed to be cached?
   *
   * @var boolean
   */
  protected $bIsCachable = true;

  /**
   * The share directory to use for resources
   *
   * @var string
   */
  protected $sWebShareDir = '';

  /**
   * Will this widget inject any thing into the HTML head via its controller
   *
   * @TODO Either implement this feature fully or remove its unneeded detritus, like this variable...
   *
   * @var boolean
   */
  protected $bUseControllerHtmlHeader = false;

  /**
   * Factory method that creates an instance of a specific type of widget.
   *
   * @param string $sType - The type of widget to instantiate
   * @param string $sName (optional) - The name to give the widget when it is instantiated
   * @param \Limbonia\Controller $oController (optional)
   * @return \Limbonia\Widget - The object requested on success, otherwise false.
   */
  public static function factory($sType, $sName = null, \Limbonia\Controller $oController = null)
  {
    return self::driverFactory($sType, $sName, $oController);
  }

    /**
   * This "setter" changes the color of HTML warning messages created by this class.
   *
   * @param string $sColor - The new warning color.
   */
  public static function warningColor($sColor='orange')
  {
    self::$sWarningColor = $sColor;
  }

  /**
   * This "setter" changes the color of HTML error messages created by this class.
   *
   * @param string $sColor - The new error color.
   */
  public static function errorColor($sColor='red')
  {
    self::$sErrorColor = $sColor;
  }

  /**
   * Write an HTML error.
   *
   * @param string $sText - Text of the error to write.
   */
  public static function errorText($sText)
  {
    echo "<br><font color=\"" . self::$sErrorColor . "\">Error: </font>$sText<br>\n";
  }

  /**
   * Write an HTML warning.
   *
   * @param string $sText - Text of the warning to write.
   */
  public static function warningText($sText)
  {
    echo "<br><font color=\"" . self::$sWarningColor . "\">Warning: </font>$sText<br>\n";
  }


  /**
   * Set the Ajax Debug to the specified value
   *
   * @param boolean $bDebug
   */
  public static function setAjaxDebug($bDebug = true)
  {
    self::$bAjaxDebug = (boolean)$bDebug;
  }

  /**
   * Return the Ajax Debug value
   *
   * @return boolean
   */
  public static function getAjaxDebug()
  {
    return self::$bAjaxDebug;
  }

  /**
   * Set the Ajax Status to the specified value
   *
   * @param type $bReportStatus
   */
  public static function setAjaxStatus($bReportStatus = true)
  {
    self::$bAjaxStatus = (boolean)$bReportStatus;
  }

  /**
   * Return the Ajax Status value
   *
   * @return boolean
   */
  public static function getAjaxStatus()
  {
    return self::$bAjaxStatus;
  }

  /**
   * Return the current list of AJAX functions
   *
   * @return array
   */
  public static function ajaxList()
  {
    return self::$aAjaxFunction;
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
  public function __construct($sName=null, \Limbonia\Controller $oController = null)
  {
    $this->sType = strtolower(str_replace(__CLASS__ . '\\', '', get_class($this)));

    if (empty($this->sType))
    {
      throw new Limbonia\Exception\Object(__CLASS__ . " couldn't find a valid type!");
    }

    if ($oController instanceof \Limbonia\Controller)
    {
/*
      if ($oController instanceof \Limbonia\Controller\Web)
      {
        If it's a web type then allow scripts to be placed in the head instead of the body?
      }
*/
      $this->oController = $oController;
    }

    $this->sWebShareDir = $this->getController()->domain->uri . '/' . $this->getController()->getDir('share');
    self::$iCount++;
    $this->sName = empty($sName) ? 'Limbonia' . $this->sType . self::widgetCount() : $sName;
    $this->setParam('name', $this->sName);
    $this->sId = preg_replace("/\[|\]/", "", $this->sName);
    $this->setParam('id', $this->sId);
  }

  /**
   * Is this widget cachable?
   *
   * @return boolean
   */
  public function isCachable()
  {
    return $this->bIsCachable;
  }

  /**
   * Should this widget's data be cached in session data?
   *
   * @return boolean
   */
  public function isSessionCachable()
  {
    return !$this->bIsCachable && SessionManager::isStarted();
  }

  /**
   * Update the rest of the parameter array using the data stored in the "expand" parameter
   *
   * @param type $hParam
   * @return array List of expanded parameters
   */
  public function expandParameters($hParam)
  {
    if (isset($hParam['expand']))
    {
      $aExpansions = explode(',', $hParam['expand']);
      unset($hParam['expand']);

      foreach ($aExpansions as $sExpansion)
      {
        list ($sLocation, $sName) = explode(':', $sExpansion);
        $sValue = null;

        switch (strtoupper($sLocation))
        {
          case 'SESSION':
            if (isset($_SESSION[$sName]))
            {
              $sValue = $_SESSION[$sName];
            }
            break;

          case 'POST':
            $sValue = $this->getController()->post[$sName];
            break;

          case 'GET':
            $sValue = $this->getController()->get[$sName];
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

  /**
   * Cache the specified data to the current session
   *
   * @todo This was imported from "Module" and needs to be worked on before it is usable
   *
   * @param string $sName
   * @param mixed $xData
   */
  protected function setSessionCache($sName, $xData)
  {
    $_SESSION[$this->sType][$sName] = $xData;
  }

  /**
   * Return the specified data stored in the current session
   *
   * @todo This was imported from "Module" and needs to be worked on before it is usable
   *
   * @param type $sName
   * @return type
   */
  protected function getSessionCache($sName)
  {
    return isset($_SESSION[$this->sType][$sName]) ? $_SESSION[$this->sType][$sName] : null;
  }

  /**
   * Process and return the specified target data
   *
   * @return string
   */
  public function processTarget()
  {
    if (isset($this->hParam['component']))
    {
      $sComponent = 'component' . $this->hParam['component'];

      if (method_exists($this, $sComponent))
      {
        unset($this->hParam['component']);
        return $this->$sComponent();
      }
    }

    return $this->__toString();
  }

  /**
   * Set all the internal configuration with the specified
   *
   * @param array $hParam
   */
  public function setAll(array $hParam = [])
  {
    $hExpandedParam = $this->expandParameters($hParam);

    if (isset($hExpandedParam['html_text_to_filter']))
    {
      $this->addText($hExpandedParam['html_text_to_filter']);
      unset($hExpandedParam['html_text_to_filter']);
    }

    parent::setAll($hExpandedParam);
  }

  /**
   * Returns the current number of widgets that have been created.
   * It must be called statically.
   * It is final and may not be overridden by a child class.
   *
   * @return integer
   */
  final static protected function widgetCount()
  {
    return self::$iCount;
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
  protected function init()
  {
    $this->sPreScript .= parent::toString();
    return true;
  }

  /**
   * Does all redundant bits of creating the actual HTML / JavaScript widget.
   * It is final and may not be overridden by a child class.
   *
   * @return boolean
   */
  final public function create()
  {
    if ($this->bInit)
    {
      return true;
    }

    $this->bInit = true;

    if (!$this->init())
    {
      $this->errorText("Widget \"$this->sName\" failed to initialize!");
      return false;
    }

    $this->addAjaxClass(self::$bAjaxStatus);
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
      $this->writeJavascript("\n" . $this->sScript);
    }

    echo $this->sPostScript;
    return true;
  }

  /**
   * Return an HTML representation of this widget object
   *
   * @return string
   */
  public function toString()
  {
    ob_start();
    $this->create();
    return ob_get_clean();
  }

  /**
   * Include a JavaScript file for later use, while insuring that it hasn't already been included.
   *
   * @param string $sScript - JavaScript file name.
   */
  public static function includeScript($sScript)
  {
    if (array_search($sScript, self::$aIncludedScript) === false)
    {
      echo "\n<script type=\"text/javascript\" src=\"$sScript\"></script>\n";
      self::$aIncludedScript[] = $sScript;
    }
  }

  /**
   * Write a chunk of JavaScript to HTML page if the widget has already been created
   * otherwise save it for writing when that happens.
   *
   * @param string $sCommand - JavaScript to write.
   */
  public function writeJavascript($sCommand)
  {
    if ($this->bInit)
    {
      echo "\n<script type=\"text/javascript\">$sCommand</script>\n";
    }
    else
    {
      $this->sScript .= $sCommand."\n";
    }
  }

  /**
   * Write out the specified data
   *
   * @param string $sData
   * @param boolean $bPre (optional) - Should the data be written pre-script? (defaults to true)
   */
  public function write($sData, $bPre = true)
  {
    echo $sData;

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

  /**
   * Write out the specified data with a line break added
   *
   * @param string $sData
   * @param boolean $bPre (optional) - Should the data be written pre-script? (defaults to true)
   */
  public function writeLn($sData, $bPre=true)
  {
    $this->write($sData."\n", $bPre);
  }

  /**
   * Return this widget's name
   *
   * @return string
   */
  public function getName()
  {
    return $this->sName;
  }

  /**
   * Return this widget's ID
   *
   * @return string
   */
  public function getId()
  {
    return $this->sId;
  }

  /**
   * Add the specified widget to the content
   *
   * @param Widget $oWidget
   */
  public function addWidget($oWidget)
  {
    if ($oWidget instanceof Widget)
    {
      $this->addContent($oWidget);
    }
  }

  /**
   * Remove the specified widget from the content
   *
   * @param Widget $oWidget
   * @return integer The index of the removed widget or false if there is none
   */
  public function removeWidget($oWidget)
  {
    if ($oWidget instanceof Widget)
    {
      return $this->removeContent($oWidget);
    }

    return false;
  }

  //AJAX functionality

  /**
   * Register a class method for use in an AJAX call and output it directly into HTML
   *
   * @param string $sFunction - The body of the function
   * @param boolean $bReportStatus
   * @return string The name of the JavaScript function
   */
  public function addAjaxFunction($sFunction, $bReportStatus = false)
  {
    $sAjaxFunction = "Limbonia_$sFunction";

    if (!in_array($sAjaxFunction, self::$aAjaxFunction))
    {
      $sClassName = str_replace('\\', '/', preg_replace("#^Limbonia\\\#", '', get_class($this)));
//      self::includeScript($this->getController()->domain->uri . '/' . $this->getController()->getDir('share') . "/ajax.js");
      $sReportStatus = $bReportStatus === true ? 'true' : 'false';
      $sDebug = self::$bAjaxDebug === true ? 'true' : 'false';
      $sBaseUrl = "'" . $this->getController()->domain->url . '/ajax' ."'";

      $sJavascript =  "\n<script type=\"text/javascript\">function $sAjaxFunction(){Limbonia_HttpRequest('$sClassName', '$sFunction', arguments, $sReportStatus, $sDebug, $sBaseUrl);}</script>\n";
      $this->write($sJavascript);
      self::$aAjaxFunction[] = $sAjaxFunction;
    }

    return $sAjaxFunction;
  }

  /**
   * Register all Ajax methods in the specified widget object
   *
   * @param Widget $oClass
   * @param boolean $bReportStatus (optional)
   */
  public function addAjaxClass($bReportStatus = false)
  {
    foreach (get_class_methods($this) as $sMethod)
    {
      if (preg_match("/^ajax_(.*)/", $sMethod, $aMatch))
      {
        $this->addAjaxFunction($aMatch[1], $bReportStatus);
      }
    }
  }
}