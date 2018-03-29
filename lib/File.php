<?php
namespace Limbonia;

/**
 * Limbonia Programming API Class
 *
 * It defines a host of needed functionality including a standard method for
 * writing that allows for web and cli environments, opening and closing files,
 * timestamps as well as the version information.
 *
 * NOTE: All properties and methods are static.
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @version $Revision: 1.3 $
 * @package Limbonia
 */
class File
{
  /**
   * Make a directory structure from the given path.
   *
   * @param string $sPath - the path of the directory to make
   * @param number $nMode
   * @throws Exception on failure
   */
  static public function makeDir($sPath, $nMode = 0777)
  {
    if (is_dir($sPath))
    {
      return true;
    }

    $OldMask = umask(0000);

    try
    {
      self::makeDir(dirname($sPath), $nMode);
    }
    catch (\Exception $e)
    {
      umask($OldMask);
      throw $e;
    }

    $sNewDir = basename($sPath);
    $sDir = dirname($sPath);
    $sCurrentDir = getcwd();
    chdir($sDir);

    ob_start();
    $bSuccess = mkdir($sNewDir, $nMode);
    $sError = ob_get_clean();

    chdir($sCurrentDir);
    umask($OldMask);

    if (!$bSuccess)
    {
      throw new \Exception("Error creating $sNewDir: $sError");
    }

    return true;
  }

  /**
   * Remove the specified directory.
   *
   * @param string $sPath
   * @return boolean
   */
  static public function removeDir($sPath)
  {
    if (!empty($sPath))
    {
      if (is_file($sPath))
      {
        unlink($sPath);
      }
      else
      {
        $sTempPath = is_dir($sPath) ? "$sPath/*" : $sPath;
        $aPath = glob($sTempPath);

        if ($aPath && $aPath[0] != $sPath)
        {
          foreach ($aPath as $sFile)
          {
            self::removeDir($sFile);
          }
        }

        if (is_dir($sPath))
        {
          rmdir($sPath);
        }
      }
    }

    return true;
  }

  /**
   * Open the specified file and return a file resource based on that file.
   *
   * @param string $sFilePath - the path to the file to open
   * @param string $sMode (optional) - the mode to open the file in
   * @throws Exception
   * @return resource
   */
  static public function openFile($sFilePath, $sMode = 'a')
  {
    if (Controller::isCli() && preg_match("#php://(std(in|out|err))#", $sFilePath, $aMatch))
    {
      return constant(strtoupper($aMatch[1]));
    }

    ob_start();
    $rFilePath = fopen($sFilePath, $sMode);
    $sError = trim(ob_get_clean());

    if ($rFilePath == false)
    {
      throw new Exception($sError);
    }

    return $rFilePath;
  }

  /**
   * Close the specified file resource.
   *
   * @param resource $rFilePath
   * @return boolean
   */
  static public function closeFile($rFilePath)
  {
    if (!is_resource($rFilePath))
    {
      return true;
    }

    // if this is the CLI and the file resource is one of the standard ones
    // don't even *try* to close it, just return true and let PHP handle it
    // when the script ends...
    if (Controller::isCli() && ($rFilePath == STDIN || $rFilePath == STDOUT || $rFilePath == STDERR))
    {
      return true;
    }

    return fclose($rFilePath);
  }

  /**
   * Open and lock the specified file then return a handle to the openend file
   *
   * @param type $sFilePath
   * @param type $sMode
   * @return resource - A file resource on success and false on failure
   */
  static public function lock($sFilePath, $sMode = 'a')
  {
    if ($rFile = self::openFile($sFilePath, $sMode . 'b'))
    {
      if (flock($rFile, $sMode == 'r' ? LOCK_SH : LOCK_EX))
      {
        return $rFile;
      }

      self::closeFile($rFile);
    }

    return false;
  }

  /**
   * Unlock a file so it can be accessed freely
   *
   * @param resource $rFile - the file resource to unlock
   * @return boolean
   */
  static public function unlock($rFile)
  {
    if (!flock($rFile, LOCK_UN))
    {
      return false;
    }

    // even if closing the file fails, we'll return true
    self::closeFile($rFile);
    return true;
  }

  /**
   * Prints the the passed data
   *
   * @param string $sData - The string to be printed
   */
  static public function write($sData, $sFilePath = null)
  {
    if (empty($sFilePath))
    {
      echo $sData;
      return strlen($sData);
    }

    return file_put_contents($sFilePath, $sData);
  }

  /**
   * Prints the the passed data with the Limbonia EOL string appended to it
   *
   * @param string $sData - The string will be printed
   * @param string $sFilePath - the file to be printed to (optional)
   * @return boolean
   */
  static public function writeLn($sData, $sFilePath = null)
  {
    return self::write($sData . Controller::eol(), $sFilePath);
  }
}