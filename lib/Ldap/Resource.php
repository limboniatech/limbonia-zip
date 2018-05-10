<?php
namespace Limbonia\Ldap;

/**
 * Limbonia LDAP Resource Class
 *
 * Object wrapper for the LDAP functions
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Resource
{
  /**
   * List of LDAP Resource objects keyed by the original LDAP URL
   *
   * @var array
   */
  protected static $hUrlList = [];

  /**
   * LDAP Resource
   *
   * @var resource
   */
  protected $rLdapLink = null;

  /**
   * The key used for this object in the self::$hUrlList
   *
   * @var string
   */
  protected $sUrlListKey = '';

  /**
   * Throw the appropriate Exception if an LDAP error has occurred
   *
   * @param resource $rLdapLink
   * @throws \Exception
   */
  public static function throwOnLdapError($rLdapLink)
  {
    if (get_resource_type($rLdapLink) !== 'ldap link')
    {
      throw new \Exception('LDAP Error: Invalid resource');
    }

    $iCode = ldap_errno($rLdapLink);

    switch ($iCode)
    {
      //These are not fail conditions
      case 0:
      case 5:
      case 6:
        //So do nothong
        break;

      default:
        throw new \Exception('LDAP Error: ' . ldap_error($rLdapLink), $iCode);
    }
  }

  /**
   * Convert DN to User Friendly Naming format
   *
   * @param string $sDn - The distinguished name of an LDAP entity.
   * @return string - The user friendly name
   */
  public static function dnToUfn(string $sDn): string
  {
    return ldap_dn2ufn($sDn);
  }

  /**
   * Splits DN into its component parts
   *
   * @param string $sDn - The distinguished name of an LDAP entity.
   * @param boolean $bWithAttrib  - Used to request if the RDNs are returned with only values or their
   * attributes as well. To get RDNs with the attributes (i.e. in attribute=value format) set <i>with_attrib</i> to 0
   * and to get only values set it to 1.
   * @return array an array of all DN components.
   */
  public static function explodeDn($sDn, bool $bWithAttrib = false)
  {
    return ldap_explode_dn($sDn, (integer)$bWithAttrib);
  }

  /**
   * Escape a string for use in an LDAP filter or DN
   *
   * @param string $sValue - The value to escape.
   * @param string $sIgnore (optional) - Characters to ignore when escaping.
   * @param int $iFlags (optional) - The context the escaped string will be used in:
   * LDAP_ESCAPE_FILTER for filters to be used with
   * ldap_search, or
   * LDAP_ESCAPE_DN for DNs.
   * @return string the escaped string.
   */
  public static function escape($sValue, $sIgnore = null, $iFlags = null)
  {
    return ldap_escape($sValue, $sIgnore, $iFlags);
  }

  /**
   * Instantiate a new LDAP Resource object
   *
   * @param string $sLdapUrl
   * @throws \Exception
   */
  public function __construct(string $sLdapUrl)
  {
    $rLdap = ldap_connect($sLdapUrl);

    if (!is_resource($rLdap))
    {
      throw new \Exception("Unable to connect to LDAP server: $sLdapUrl");
    }

    $this->sUrlListKey = strtolower($sLdapUrl);
    $this->rLdapLink = $rLdap;
  }

  /**
   * Destroy the current LDAP Resource object
   */
  public function __destruct()
  {
    unset(self::$hUrlList[$this->sUrlListKey]);
    ldap_unbind($this->rLdapLink);
  }

  /**
   * Return the string representation of this object
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getOption(LDAP_OPT_HOST_NAME);
  }

  /**
   * Throw the appropriate Exception if an LDAP error has occurred
   *
   * @throws \Exception
   */
  protected function throwOnError()
  {
    self::throwOnLdapError($this->rLdapLink);
  }

  /**
   * Attempt to generate an exception, if needed otherwise just return the passed in success variable untouched
   *
   * @param boolean $bSuccess
   * @return boolean - true on success or false on failure
   * @throws \Exception on error
   */
  protected function processReturn(bool $bSuccess)
  {
    if (!$bSuccess)
    {
      $this->throwOnError();
    }

    return $bSuccess;
  }

  /**
   * Add entries to LDAP directory
   *
   * @param string $sDn - The distinguished name of an LDAP entity.
   * @param array $aEntry -  An array that specifies the information about the entry.
   *
   * The values in the entries are indexed by individual attributes. In case
   * of multiple values for an attribute, they are indexed using integers starting with 0.
   * <code>
   * $entry["attribute1"] = "value";
   * $entry["attribute2"][0] = "value1";
   * $entry["attribute2"][1] = "value2";
   * </code>
   *
   * @return boolean - true on success or false on failure
   * @throws \Exception on error
   */
  public function add(string $sDn, array $aEntry)
  {
    return $this->processReturn(ldap_add($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Compare value of attribute found in entry specified with DN
   *
   * @param string $sDn - The distinguished name of an LDAP entity.
   * @param string $sAttribute - The attribute name.
   * @param string $sValue - The compared value.
   *
   * @return boolean - true if $sValue matches otherwise returns false.
   * @throws \Exception - on error
   */
  public function compare($sDn, $sAttribute, $sValue)
  {
    $bSuccess = ldap_compare($this->rLdapLink, $sDn, $sAttribute, $sValue);

    if ($bSuccess === -1)
    {
      $this->throwOnError();
    }

    return $bSuccess;
  }

  public function delete($sDn)
  {
    return $this->processReturn(ldap_delete($this->rLdapLink, $sDn));
  }

  /**
   * Bind to LDAP directory
   *
   * @param string $sDn (optional)
   * @param string $sPassword (optional)
   * @return bool TRUE on success or FALSE on failure.
   * @throws \Exception on error
   */
  public function bind($sDn = null, $sPassword = null)
  {
    return $this->processReturn(ldap_bind($this->rLdapLink, $sDn, $sPassword));
  }

  /**
   * Get the current value for given option
   *
   * @param int $iOption - The parameter to get
   * @return mixed The value of the selected option
   * @throws \Exception
   * @link http://php.net/manual/en/function.ldap-get-option.php
   */
  public function getOption($iOption)
  {
    $xVal = null;

    if (!ldap_get_option($this->rLdapLink, $iOption, $xVal))
    {
      $this->throwOnError();
    }

    return $xVal;
  }

  /**
   * Set the specified option to specified value
   *
   * @param integer $iOption
   * @param mixed $xVal
   * @return boolean
   * @throws \Exception
   */
  public function setOption($iOption, $xVal)
  {
    return $this->processReturn(ldap_set_option($this->rLdapLink, $iOption, $xVal));
  }

  /**
   * Add attribute values to current attributes
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function modAdd($sDn, array $aEntry)
  {
    return $this->processReturn(ldap_mod_add($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Remove attribute values from current attributes
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function modDelete($sDn, array $aEntry)
  {
    return $this->processReturn(ldap_mod_del($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Replace attribute values with new ones
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function modReplace($sDn, array $aEntry)
  {
    return $this->processReturn(ldap_mod_replace($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Modify and existing entry
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function modify($sDn, array $aEntry)
  {
    return $this->processReturn(ldap_modify($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Replace attribute values with new ones
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function modifyBatch($sDn, array $aEntry)
  {
    return $this->processReturn(ldap_modify_batch($this->rLdapLink, $sDn, $aEntry));
  }

  /**
   * Modify the name of an entry
   *
   * @param string $sDn - The distinguished name of an LDAP entity
   * @param array $aEntry
   * @return boolean
   * @throws \Exception
   */
  public function rename($sDn, $sNewRdn, $sNewParent, $bDeleteOldRdn)
  {
    return $this->processReturn(ldap_rename($this->rLdapLink, $sDn, $sNewRdn, $sNewParent, $bDeleteOldRdn));
  }

  /**
   * Search LDAP tree
   *
   * @param string $sBaseDn - The base DN for the directory.
   * @param string $sFilter - The search filter can be simple or advanced, using boolean operators in
   * the format described in the LDAP documentation (see the Netscape Directory SDK or RFC4515 for full
   * information on filters).
   * @param array $aAttributes (optional) - An array of the required attributes, e.g. array("mail", "sn", "cn").
   * Note that the "dn" is always returned irrespective of which attributes types are requested.
   * <p>
   * Using this parameter is much more efficient than the default action
   * (which is to return all attributes and their associated values).
   * The use of this parameter should therefore be considered good
   * practice.
   * </p>
   * @param bool $bAttrsOnly (optional) - Should be set to 1 if only attribute types are wanted. If set to 0
   * both attributes types and attribute values are fetched which is the default behaviour.
   * @param int $iSizeLimit (optional) - Enables you to limit the count of entries fetched. Setting this to 0 means no limit.
   * <p>
   * This parameter can NOT override server-side preset sizelimit. You can
   * set it lower though.
   * </p>
   * <p>
   * Some directory server hosts will be configured to return no more than
   * a preset number of entries. If this occurs, the server will indicate
   * that it has only returned a partial results set. This also occurs if
   * you use this parameter to limit the count of fetched entries.
   * </p>
   * @param int $iTimeLimit (optional) - Sets the number of seconds how long is spend on the search. Setting this to 0 means no limit.
   * <p>
   * This parameter can NOT override server-side preset timelimit. You can set it lower though.
   * </p>
   * @param int $iDerefOption (optional) - Specifies how aliases should be handled during the search. It can be one of the following:
   * LDAP_DEREF_NEVER - (default) aliases are never dereferenced.
   * @return \Limbonia\Ldap\Result
   * @throws \Exception on error
   */
  public function search(
    $sBaseDn,
    $sFilter,
    array $aAttributes = [],
    $bAttrsOnly = false,
    $iSizeLimit = 0,
    $iTimeLimit = 0,
    $iDerefOption = LDAP_DEREF_NEVER
  )
  {
    $rLdapResult = ldap_search(
      $this->rLdapLink,
      $sBaseDn,
      $sFilter,
      $aAttributes,
      (integer)$bAttrsOnly,
      $iSizeLimit,
      $iTimeLimit,
      $iDerefOption
    );
    $this->throwOnError();
    return new Result($this->rLdapLink, $rLdapResult);
  }

  public function read(
    $sBaseDn,
    $sFilter,
    array $aAttributes = [],
    $bAttrsOnly = false,
    $iSizeLimit = 0,
    $iTimeLimit = 0,
    $iDerefOption = LDAP_DEREF_NEVER
  )
  {
    $hResiltList = ldap_read(
      $this->rLdapLink,
      $sBaseDn,
      $sFilter,
      $aAttributes,
      (integer)$bAttrsOnly,
      $iSizeLimit,
      $iTimeLimit,
      $iDerefOption
    );
    $this->throwOnError();
    return Result::resultList($hResiltList, $this->rLdapLink);
  }

  public function getList(
    $sBaseDn,
    $sFilter,
    array $aAttributes = [],
    $bAttrsOnly = false,
    $iSizeLimit = 0,
    $iTimeLimit = 0,
    $iDerefOption = LDAP_DEREF_NEVER
  )
  {
    $hResiltList = ldap_list(
      $this->rLdapLink,
      $sBaseDn,
      $sFilter,
      $aAttributes,
      (integer)$bAttrsOnly,
      $iSizeLimit,
      $iTimeLimit,
      $iDerefOption
    );
    $this->throwOnError();
    return LdapResult::resultList($hResiltList, $this->rLdapLink);
  }

  /**
   * Set a callback function to do re-binds on referral chasing
   *
   * @param callable $cCallback - the callback to use
   * @return boolean
   * @throws \Exception
   */
  public function setRebindProcedure(callable $cCallback)
  {
    return $this->processReturn(ldap_set_rebind_proc($this->rLdapLink, $cCallback));
  }

  /**
   * Start TLS
   *
   * @return boolean
   * @throws \Exception
   */
  public function startTls()
  {
    return $this->processReturn(ldap_start_tls($this->rLdapLink));
  }
}