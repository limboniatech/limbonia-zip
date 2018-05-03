<?php
namespace Limbonia;

class Imap
{
  const DEFAULT_FOLDER = 'INBOX';
  const DEFAULT_TIMEOUT = 15;
  const SECURE_PORT = 993;
  const INSECURE_PORT = 143;
  protected $iCommandCounter = 0;
  protected $rMailServer = null;
  protected $sCurrentFolder = '';
  protected $bHasDeleted = false;
  protected $sServer = '';
  protected $sUser = '';
  protected $sPassword = '';
  protected $iTimeOut = self::DEFAULT_TIMEOUT;
  protected $sSecureModeMode = 'ssl://';
  protected $iPort = self::SECURE_PORT;
  protected $sFolder = self::DEFAULT_FOLDER;
  protected $bLoggedIn = false;

  public static function connection(array $hConfig = [])
  {
    $oImap = new self($hConfig);
    $oImap->connect();
    $oImap->login();
    return $oImap;
  }

  /**
   * Constructor
   */
  public function __construct(array $hConfig = [])
  {
    if (empty($hConfig['server']))
    {
      throw new \Exception('Server not set');
    }

    if (empty($hConfig['user']))
    {
      throw new \Exception('User not set');
    }

    if (empty($hConfig['password']))
    {
      throw new \Exception('Password not set');
    }

    $this->sServer = (string)$hConfig['server'];
    $this->sUser = (string)$hConfig['user'];
    $this->sPassword = (string)$hConfig['password'];

    if (isset($hConfig['timeout']))
    {
      $this->iTimeOut = (integer)$hConfig['timeout'];
    }

    if (isset($hConfig['folder']))
    {
      $this->sFolder = (string)$hConfig['folder'];
    }

    if (isset($hConfig['secure']))
    {
      if (false === $hConfig['secure'])
      {
        //then turn SSL off
        $this->sSecureMode = '';
        $this->iPort = isset($hConfig['port']) ? (integer)$hConfig['port'] : self::INSECURE_PORT;
      }
      else
      {
        //otherwise SSL is on!
        $this->sSecureMode = 'tls' === $hConfig['secure'] ? 'tls://' : 'ssl://';
        $this->iPort = isset($hConfig['port']) ? (integer)$hConfig['port'] : self::SECURE_PORT;
      }
    }
  }

  public function __destruct()
  {
    try
    {
      $this->disconnect();
    }
    catch (\Exception $e) {}
  }

  protected function command($sCommand)
  {
    if (!is_resource($this->rMailServer))
    {
      throw new \Exception("Command failed ($sCommand): not connected");
    }

    $aResult = [];
    $sEndline  = '';
    $sCounter = 'Limbonia' . sprintf('%08d', $this->iCommandCounter++);
    fwrite($this->rMailServer, "$sCounter $sCommand\r\n");

    while ($sLine = fgets($this->rMailServer))
    {
      $sLine = trim($sLine); // do not combine with the line above in while loop, because sometimes valid response maybe \n

      if (preg_match("/^$sCounter\s+(.*)/", $sLine, $aMatch))
      {
        $sEndline = $aMatch[1];
        break;
      }
      else
      {
        $aResult[] = $sLine;
      }
    }

    if (!preg_match('/^OK/', $sEndline))
    {
      $sError = trim(join(', ', $aResult));

      if (empty($sError))
      {
        $sError = 'Unknown Error';
      }

      throw new \Exception($sError);
    }

    return $aResult;
  }

  public function connect()
  {
    if (!is_resource($this->rMailServer))
    {
      $iError = 0;
      $sError = '';
      $rMailServer = fsockopen($this->sSecureMode . $this->sServer, $this->iPort, $iError, $sError, $this->iTimeOut);

      if (false === $rMailServer)
      {
        throw new \Exception("Faild to connect to $this->sServer: $sError", $iError);
      }

      if (!stream_set_timeout($rMailServer, $this->iTimeOut))
      {
        throw new \Exception("Faild to set timeout");
      }

      $this->rMailServer = $rMailServer;

      //remove the first line...  This may not be needed
      fgets($this->rMailServer);
    }
  }

  public function login()
  {
    if (!$this->bLoggedIn)
    {
      $this->iCommandCounter = 1;
      $this->command("LOGIN $this->sUser $this->sPassword");
      $this->bLoggedIn = true;
      $this->setFolder($this->sFolder);
    }
  }

  public function logout()
  {
    $this->bHasDeleted = false;
    $this->bLoggedIn = false;

    if (is_resource($this->rMailServer))
    {
      if ($this->bHasDeleted)
      {
        $this->command('CLOSE');
      }

      if ($this->bLoggedIn)
      {
        $this->command('LOGOUT');
      }
    }
  }

  public function disconnect()
  {
    if ($this->bLoggedIn)
    {
      $this->logout();
    }

    if (is_resource($this->rMailServer))
    {
      fclose($this->rMailServer);
      $this->rMailServer = null;
    }
  }

  public function setFolder($sFolder)
  {
    $this->command("SELECT $sFolder");
    $this->sCurrentFolder = $sFolder;
  }

  public function getFolder()
  {
    return $this->sCurrentFolder;
  }

  public function search($sCriteria)
  {
    $aResult = $this->command("SEARCH $sCriteria");

    if (count($aResult) > 1)
    {
      $sError = trim(join(', ', $aResult));

      if (empty($sError))
      {
        $sError = 'Unknown Error';
      }

      throw new \Exception($sError);
    }

    $aSplitResult = explode(' ', $aResult[0]);
    $aId = [];

    foreach ($aSplitResult as $sItem)
    {
      if (preg_match('/^\d+$/', $sItem))
      {
        $aId[] = $sItem;
      }
    }

    return $aId;
  }

  public function fetch($sId, $sItem)
  {
    $aResult = $this->command("FETCH $sId $sItem");
    array_shift($aResult); // skip first line
    array_pop($aResult); // skip last line
    return $aResult;
  }

  public function fetchEmail($sId)
  {
    return implode("\n", $this->fetch($sId, 'BODY[]'));
  }

  public function fetchText($sId)
  {
    return implode("\n", $this->fetch($sId, 'BODY[TEXT]'));
  }

  public function fetchHeaders($sId)
  {
    $aResult = $this->fetch($sId, 'BODY.PEEK[HEADER]');
    return \Limbonia\Email::processHeaders($aResult);
  }

  public function delete($sId)
  {
    $this->command("STORE $sId +FLAGS.SILENT (\Deleted)");
    $this->bHasDeleted = true;
  }
}
