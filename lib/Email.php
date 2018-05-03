<?php
namespace Limbonia;

/**
 * Limbonia Email Class
 *
 * This is a wrapper around the PHP mail command that allows for object oriented usage
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
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
   * The path of the attachment
   *
   * @var string
   */
  protected $sAttachment = '';

  /**
   * The mime boundary used  attachments
   *
   * @var string
   */
  protected $sMimeBoundary = '';

  public static function decodeBody($hHeaders, $sBody)
  {
    if (empty($hHeaders['content-transfer-encoding']))
    {
      return $sBody;
    }

    switch (strtolower($hHeaders['content-transfer-encoding']))
    {
      case '7bit':
      case '8bit':
        return $sBody;

      case 'base64':
        return base64_decode($sBody);

      case 'quoted_printable':
      case 'quoted-printable':
        return quoted_printable_decode($sBody);
    }

    echo "\nInvalid transfer-encoding found: {$hHeaders['content-transfer-encoding']}\n";
    return $sBody;
  }

  public static function isText(array $hHeaders = [])
  {
    if (isset($hHeaders['content-type']) && preg_match("#text/(plain|html)#", $hHeaders['content-type'], $aMatch))
    {
      return $aMatch[1] == 'html' ? 'html' : 'text';
    }

    return false;
  }

  public static function isAttachment($hHeaders)
  {
    if (isset($hHeaders['content-disposition']) && preg_match("#attachment; filename=\"(.*?)\"#", $hHeaders['content-disposition'], $aMatch))
    {
      return $aMatch[1];
    }

    return false;
  }

  public static function isMultiPart($hHeaders)
  {
    if (isset($hHeaders['content-type']) && preg_match("#multipart/.*?; boundary=\"(.*?)\"#", $hHeaders['content-type'], $aMatch))
    {
      return $aMatch[1];
    }

    return false;
  }

  public static function processHeaders($xRawHeader)
  {
    $aRawHeader = is_array($xRawHeader) ? $xRawHeader : explode("\n", $xRawHeader);
    $aHeaders = [];
    $sPrevMatch = '';

    foreach ($aRawHeader as $sLine)
    {
      if (preg_match('/^([a-z][a-z0-9-_]+):/is', $sLine, $aMatch))
      {
        $sHeaderName = strtolower($aMatch[1]);
        $sPrevMatch = $sHeaderName;
        $aHeaders[$sHeaderName] = trim(substr($sLine, strlen($sHeaderName) + 1));
      }
      else
      {
        if (!empty($sPrevMatch))
        {
          $aHeaders[$sPrevMatch] .= ' ' . $sLine;
        }
      }
    }

    return $aHeaders;
  }

  public static function breakMessage($xMessage)
  {
    $aBody = is_array($xMessage) ? $xMessage : explode("\n", $xMessage);
    $aHeaders = [];

    while ($sLine = array_shift($aBody))
    {
      if (empty($sLine))
      {
        break;
      }

      $aHeaders[] = $sLine;
    }

    return
    [
      'headers' => self::processHeaders($aHeaders),
      'body' => implode("\n", $aBody)
    ];
  }

  public static function processMessage($xEmail)
  {
    $hEmail = self::breakMessage($xEmail);

    $sBody = trim($hEmail['body']);
    unset($hEmail['body']);

    if ($sFileName = self::isAttachment($hEmail['headers']))
    {
      $hEmail['attachment'] =
      [
        'filename' => $sFileName,
        'data' => self::decodeBody($hEmail['headers'], $sBody)
      ];

      if (isset($hEmail['headers']['content-type']) && preg_match("#^(.*?);#", $hEmail['headers']['content-type'], $aMatch))
      {
        $hEmail['content-type'] = $aMatch[1];
      }

      $hEmail['type'] = 'attachment';
      return $hEmail;
    }

    if ($sBodyType = self::isText($hEmail['headers']))
    {
      $hEmail[$sBodyType] = self::decodeBody($hEmail['headers'], $sBody);
      $hEmail['type'] = $sBodyType;
      return $hEmail;
    }

    if ($sBoundary = self::isMultiPart($hEmail['headers']))
    {
      $aPartList = explode('--' . $sBoundary, $sBody);
      array_shift($aPartList);

      foreach ($aPartList as $sPart)
      {
        $hProcessedMessage = self::processMessage(trim($sPart));

        if (!isset($hProcessedMessage['type']))
        {

          if (count($hProcessedMessage) == 1 && isset($hProcessedMessage['headers']))
          {
            continue;
          }

          unset($hProcessedMessage['headers']);

          if (!isset($hEmail['part']))
          {
            $hEmail['part'] = [];
          }

          $hEmail['part'][] = $hProcessedMessage;
          continue;
        }

        $sType = $hProcessedMessage['type'];
        unset($hProcessedMessage['type']);

        if (empty($hProcessedMessage))
        {
          continue;
        }

        if (isset($hEmail[$sType]))
        {
          if (!is_array($hEmail[$sType]) || !isset($hEmail[$sType][0]))
          {
            $xTemp = $hEmail[$sType];
            unset($hEmail[$sType]);
            $hEmail[$sType] = [$xTemp];
          }

          $hEmail[$sType][] = $hProcessedMessage[$sType];
        }
        else
        {
          $hEmail[$sType] = $hProcessedMessage[$sType];
        }
      }

      if (!isset($hEmail['text']) && !isset($hEmail['email']) && isset($hEmail['part'][0]))
      {
        if (isset($hEmail['part'][0]['text']))
        {
          $hEmail['text'] = $hEmail['part'][0]['text'];
          unset($hEmail['part'][0]['text']);
        }

        if (isset($hEmail['part'][0]['html']))
        {
          $hEmail['html'] = $hEmail['part'][0]['html'];
          unset($hEmail['part'][0]['html']);
        }

        if (empty($hEmail['part'][0]))
        {
          unset($hEmail['part'][0]);
        }

        if (empty($hEmail['part']))
        {
          unset($hEmail['part']);
        }
      }

      return $hEmail;
    }

    if (!empty($sBody))
    {
      $hEmail['body'] = $sBody;
      $hEmail['type'] = 'body';
    }

    return $hEmail;
  }

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

    if ($bUseDNS)
    {
      if (!checkdnsrr($sDomain, "MX") && !checkdnsrr($sDomain, "A"))
      {
        throw new \Exception("The 'domain' part of the email address has no valid DNS");
      }
    }
  }

  /**
   * Constructor
   */
  public function __construct(array $hConfig = [])
  {
    if (isset($hConfig['from']))
    {
      $this->setFrom($hConfig['from']);
    }

    if (isset($hConfig['to']))
    {
      $this->addTo($hConfig['to']);
    }

    if (isset($hConfig['cc']))
    {
      $this->addCC($hConfig['cc']);
    }

    if (isset($hConfig['bcc']))
    {
      $this->addBCC($hConfig['bcc']);
    }

    if (isset($hConfig['subject']))
    {
      $this->setSubject($hConfig['subject']);
    }

    if (isset($hConfig['body']))
    {
      $this->addBody($hConfig['body']);
    }

    $this->sMimeBoundary = isset($hConfig['mimeboundary']) ? $hConfig['mimeboundary'] : "::[" . md5(time()) . "]::";

    if (isset($hConfig['body']))
    {
      $this->addBody($hConfig['body']);
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
   * Return a comma separated list of to email addresses
   *
   * @return string
   */
  public function getTo()
  {
    return implode(', ', $this->aTo);
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
   *
   * @return string
   */
  public function getBody()
  {
    if (is_readable($this->sAttachment))
    {
      $sFileName = basename($this->sAttachment);
      $rFile = fopen($this->sAttachment, 'r');
      $sAttachmentRaw = fread($rFile, filesize($this->sAttachment));
      $sAttachment = chunk_split(base64_encode($sAttachmentRaw));
      fclose($rFile);

      $sBody  = "";
      $sBody .= "--" . $this->sMimeBoundary . "\r\n";
      $sBody .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
      $sBody .= "Content-Transfer-Encoding: 7bit\r\n";
      $sBody .= "\r\n";
      $sBody .= $this->sBody;
      $sBody .= "\r\n";
      $sBody .= "--" . $this->sMimeBoundary . "\r\n";
      $sBody .= "Content-Type: application/octet-stream;";
      $sBody .= "name=\"$sFileName\"\r\n";
      $sBody .= "Content-Transfer-Encoding: base64\r\n";
      $sBody .= "Content-Disposition: attachment;";
      $sBody .= " filename=\"$sFileName\"\r\n";
      $sBody .= "\r\n";
      $sBody .= $sAttachment;
      $sBody .= "\r\n";
      $sBody .= "--" . $this->sMimeBoundary . "--\r\n";
      return $sBody;
    }

    return $this->sBody;
  }

  /**
   * Set the path to the specified attachment
   *
   * @param string $sAttachment
   */
  public function setAttachment($sAttachment)
  {
    $this->sAttachment = trim($sAttachment);
  }

  public function getHeaders()
  {
    $sHeader = 'From: ' . $this->sFrom . "\r\n";

    if (!empty($this->aCC))
    {
      $sHeader .= 'Cc: ' . implode(', ', $this->aCC) . "\r\n";
    }

    if (!empty($this->aBCC))
    {
      $sHeader .= 'Bcc: ' . implode(', ', $this->aBCC) . "\r\n";
    }

    if (is_readable($this->sAttachment))
    {
      $sHeaders .= "MIME-Version: 1.0\r\n";
      $sHeaders .= "Content-Type: multipart/mixed; boundary=\"" . $this->sMimeBoundary . "\";\r\n";
    }
    else
    {
      $sHeader .= "Content-type: text/html; charset=utf8\r\n";
    }

    $sHeader .= "X-Mailer: Limbonia\r\n";
    return $sHeader;
  }

  /**
   * Send the currently configured email
   *
   * @return boolean - true on success and false on failure
   */
  public function send()
  {
    if (empty($this->aTo))
    {
      return false;
    }

    return mail($this->getTo(), $this->sSubject, $this->getBody(), $this->getHeaders());
  }
}