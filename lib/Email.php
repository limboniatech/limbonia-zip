<?php
namespace Omniverse;

/**
 * Omniverse Email Class
 *
 * This is a wrapper around the PHP mail command that allows for object oriented usage
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Email
{
  /**
   * List of addresses to send the email to
   *
   * @var array
   */
  protected $aTo = [];

  /**
   * List of addresses to CC the email to
   *
   * @var array
   */
  protected $aCC = [];

  /**
   * List of addresses to BCC the email to
   *
   * @var array
   */
  protected $aBCC = [];

  /**
   * The subject of the email being sent
   *
   * @var string
   */
  protected $sSubject = '';

  /**
   * The from string of the email being sent
   *
   * @var string
   */
  protected $sFrom = '';

  /**
   * The body of the email being sent
   *
   * @var string
   */
  protected $sBody = '';

  /**
   * The path of any attachments
   *
   * @var string
   */
  protected $sAttachmentPath = '';

  /**
   * Validate the specified email address
   *
   * @param string $sEmailAddress
   * @param boolean $bUseDNS (optional) - Use DNS to validate the email's domain? (defaults to true)
   * @throws \Exception
   */
  public static function validate($sEmailAddress, $bUseDNS = true)
  {
    if (preg_match("/.*?<(.*?)>/", $sEmailAddress, $aMatch))
    {
      $sEmailAddress = $aMatch[1];
    }

    if (empty($sEmailAddress))
    {
      throw new \Exception('Email address is empty');
    }

    if (strpos($sEmailAddress, ' ') !== false)
    {
      throw new \Exception('Email address is *not* allowed to have spaces in it');
    }

    $iAtIndex = strrpos($sEmailAddress, "@");

    if (false === $iAtIndex)
    {
      throw new \Exception("Email address does not contain an 'at sign' (@)");
    }

    $sLocal = substr($sEmailAddress, 0, $iAtIndex);
    $sLocalLen = strlen($sLocal);

    if ($sLocalLen < 1)
    {
      throw new \Exception("The 'local' part of the email address is empty");
    }

    if ($sLocalLen > 64)
    {
      throw new \Exception("The 'local' part of the email address is too long");
    }

    $sDomain = substr($sEmailAddress, $iAtIndex + 1);
    $sDomainLen = strlen($sDomain);

    if ($sDomainLen < 1)
    {
      throw new \Exception("The 'domain' part of the email address is empty");
    }

    if ($sDomainLen > 255)
    {
      throw new \Exception("The 'domain' part of the email address is too long");
    }

    if ($sLocal[0] == '.')
    {
      throw new \Exception("The 'local' part of the email address starts with a 'dot' (.)");
    }

    if ($sLocal[$sLocalLen - 1] == '.')
    {
      throw new \Exception("The 'local' part of the email address ends with a 'dot' (.)");
    }

    if (preg_match('/\\.\\./', $sLocal))
    {
      throw new \Exception("The 'local' part of the email address has two consecutive dots (..)");
    }

    if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $sDomain))
    {
      throw new \Exception("The 'domain' part of the email address contains invalid characters");
    }

    if (preg_match('/\\.\\./', $sDomain))
    {
      throw new \Exception("The 'domain' part of the email address has two consecutive dots (..)");
    }

    $sSlashLight = str_replace("\\\\", "", $sLocal);

    //these characters are invalid
    if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', $sSlashLight))
    {
      //unless the whole thing is quoted
      if (!preg_match('/^"(\\\\"|[^"])+"$/', $sSlashLight))
      {
        throw new \Exception("The 'local' part of the email address contains invalid characters");
      }
    }

    //this filter doesn't seem to work well at this time...
//    if (!filter_var($sDomain, FILTER_VALIDATE_URL))
//    {
//      throw new \Exception("The 'domain' part ($sDomain) of the email address is invalid");
//    }

    if (!checkdnsrr($sDomain, "MX") && !checkdnsrr($sDomain, "A"))
    {
      throw new \Exception("The 'domain' part of the email address has no valid DNS");
    }
  }

  /**
   * Add one or more email addresses to the "To" array
   *
   * @param string|array $xEmailAddress - Either a single address or an array of addresses
   */
  public function addTo($xEmailAddress)
  {
    $aEmailAddress = (array)$xEmailAddress;

    foreach ($aEmailAddress as $sEmailAddress)
    {
      $sEmailAddress = trim($sEmailAddress);

      try
      {
        self::validate($sEmailAddress, false);
        $this->aTo[] = $sEmailAddress;
      }
      catch (\Exception $e) {}
    }

    $this->aTo = array_unique($this->aTo);
  }

  /**
   * Add one or more email addresses to the "CC" array
   *
   * @param string|array $xEmailAddress - Either a single address or an array of addresses
   */
  public function addCC($xEmailAddress)
  {
    $aEmailAddress = (array)$xEmailAddress;

    foreach ($aEmailAddress as $sEmailAddress)
    {
      $sEmailAddress = trim($sEmailAddress);

      try
      {
        self::validate($sEmailAddress, false);
        $this->aCC[] = $sEmailAddress;
      }
      catch (\Exception $e) {}
    }

    $this->aCC = array_unique($this->aCC);
  }

  /**
   * Add one or more email addresses to the "BCC" array
   *
   * @param string|array $xEmailAddress - Either a single address or an array of addresses
   */
  public function addBCC($xEmailAddress)
  {
    $aEmailAddress = (array)$xEmailAddress;

    foreach ($aEmailAddress as $sEmailAddress)
    {
      $sEmailAddress = trim($sEmailAddress);

      try
      {
        self::validate($sEmailAddress, false);
        $this->aBCC[] = $sEmailAddress;
      }
      catch (\Exception $e) {}
    }

    $this->aBCC = array_unique($this->aBCC);
  }

  /**
   * Set the "From" address to the specified address
   *
   * @param string $sEmailAddress
   */
  public function setFrom($sEmailAddress)
  {
    $this->sFrom = trim($sEmailAddress);
  }

  /**
   * Set the "Subject" to the specified subject
   *
   * @param string $sSubject
   */
  public function setSubject($sSubject)
  {
    $this->sSubject = trim(preg_replace('/\n|\r/', ' ', $sSubject));
  }

  /**
   * Set the "Body" to the specified body
   *
   * @param string $sText
   */
  public function addBody($sText)
  {
    $this->sBody .= $sText;
  }

  /**
   * Set the attachment patch to the specified path
   *
   * @param string $sAttachmentPath
   */
  public function setAttachmentPath($sAttachmentPath)
  {
    $this->sAttachmentPath = trim($sAttachmentPath);
  }

  /**
   * Send the currerntly configured email
   *
   * @return boolean - true on success and false on failure
   */
  function send()
  {
    if (empty($this->aTo))
    {
      return false;
    }

    $sHeader = 'From: ' . $this->sFrom . "\r\n";

    if (!empty($this->aCC))
    {
      $sHeader .= 'Cc: ' . implode(', ', $this->aCC) . "\r\n";
    }

    if (!empty($this->aBCC))
    {
      $sHeader .= 'Bcc: ' . implode(', ', $this->aBCC) . "\r\n";
    }

    if (is_readable($this->sAttachmentPath))
    {
      $sFileName = basename($this->sAttachmentPath);
      $rFile = fopen($this->sAttachmentPath, "r");
      $sAttachment = fread($rFile, filesize($this->sAttachmentPath));
      $sAttachment = chunk_split(base64_encode($sAttachment));
      fclose($rFile);

      $sMimeBoundary = "::[" . md5(time()) . "]::";

      $sHeaders .= "MIME-Version: 1.0\r\n";
      $sHeaders .= "Content-Type: multipart/mixed; boundary=\"" . $sMimeBoundary . "\";\r\n";

      $sBody  = "";
      $sBody .= "--" . $sMimeBoundary . "\r\n";
      $sBody .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
      $sBody .= "Content-Transfer-Encoding: 7bit\r\n";
      $sBody .= "\r\n";
      $sBody .= $this->sBody;
      $sBody .= "\r\n";
      $sBody .= "--" . $sMimeBoundary . "\r\n";
      $sBody .= "Content-Type: application/octet-stream;";
      $sBody .= "name=\"$sFileName\"\r\n";
      $sBody .= "Content-Transfer-Encoding: base64\r\n";
      $sBody .= "Content-Disposition: attachment;";
      $sBody .= " filename=\"$sFileName\"\r\n";
      $sBody .= "\r\n";
      $sBody .= $sAttachment;
      $sBody .= "\r\n";
      $sBody .= "--" . $sMimeBoundary . "--\r\n";
    }
    else
    {
      $sHeader .= "Content-type: text/html; charset=utf8\r\n";
      $sBody = $this->sBody;
    }

    $sHeader .= "X-Mailer: Omnisys\r\n";
    return mail(implode(', ', $this->aTo), $this->sSubject, $sBody, $sHeader);
  }
}