<?php
namespace Limbonia;

/**
 * Limbonia Cache Class
 *
 * This allows caching of bits and pieces that can benefit from being kept track
 * of long term
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Cache
{
  /**
   * The directory where cache files are stored.
   *
   * @var string
   */
  private $sCacheDir = null;

  /**
   * A hash of cache objects.
   *
   * @var array
   */
  private static $hObject = [];

  /**
   * Factory method for creating cache objects
   *
   * @param string $sCacheDir - The directory where cache files will be stored.
   */
  public static function factory($sCacheDir = NULL)
  {
    if (!isset(self::$hObject[$sCacheDir]))
    {
      self::$hObject[$sCacheDir] = new self($sCacheDir);
    }

    return self::$hObject[$sCacheDir];
  }

  /**
   * Constructor
   *
   * @param string $sCacheDir - The directory where cache files will be stored.
   * @throws Limbonia\Exception\Object
   */
  protected function __construct($sCacheDir)
  {
    if (empty($sCacheDir))
    {
      throw new Exception\Object("Valid cache directory not specified", E_NOTICE);
    }

    if (!is_writeable($sCacheDir))
    {
      throw new Exception\Object("Can't write to $sCacheDir!", E_NOTICE);
    }

    $this->sCacheDir = $sCacheDir;
  }

  /**
   * Read the cached data from a cache file and return it if it exists.
   *
   * @param string $sFile -
   * @return mixed - the data from the cache file on success or false on failure
   */
  public function read($sFile)
  {
    $sCacheFile = $this->sCacheDir . "/" . $sFile;
    return is_file($sCacheFile) ? file_get_contents($sCacheFile) : false;
  }

  /**
   * Write data to a cache file for later use.
   *
   * @param string $sFile - the name of the file to store the data in
   * @param string $sData - the data to store for later use
   * @return boolean
   */
  public function write($sFile, $sData)
  {
    $sCacheFile = $this->sCacheDir . "/" . $sFile;

    // if $sFile has a directory component, make sure it exists...
    if (!File::makeDir(dirname($sCacheFile)))
    {
      return false;
    }

    if (!File::write($sData, $sCacheFile))
    {
      return false;
    }

    @chmod($sCacheFile, 0664);

    //even if the chmod fails, since the Write succeeded we'll return true
    return true;
  }

  /**
   * This function deletes all the cache files for the specified file mask
   *
   * @param string $sFileMask (optional) - the file mask to use for
   */
  public function clear($sFileMask = "*")
  {
    return File::removeDir("$this->sCacheDir/$sFileMask");
  }

  /**
   * Move a cache file from one file name to another
   *
   * @param string $sCurrent - the name of the file to move from
   * @param string $sNew - the name of file to move to
   * @return boolean
   */
  public function move($sCurrent, $sNew)
  {
    $sCurrentDir = getcwd();

    try
    {
      chdir($this->sCacheDir);

      if (!is_file($sCurrent))
      {
        throw new Exception("Cache file ($sCurrent) does *not* exist!");
      }

      $sNewDir = dirname($sNew);

      if (!empty($sNewDir) && !File::makeDir($sNewDir))
      {
        throw new Exception("Can't create ($sNewDir) directory!");
      }

      if (!@rename($sCurrent, $sNew))
      {
        throw new Exception("Can't move cache file ($sCurrent) to ($sNew)!\n");
      }

      // clean up any orphaned directories
      $sCurrentDir = dirname($sCurrent);

      while ($sCurrentDir != '.')
      {
        if (count(glob("$sCurrentDir/*")) == 0)
        {
          rmdir($sCurrentDir);
        }

        $sCurrentDir = dirname($sCurrentDir);
      }

      chdir($sCurrentDir);
      return true;
    }
    catch (\Exception $e)
    {
      chdir($sCurrentDir);
      throw $e;
    }
  }

  /**
   * Generate and return a list of files in the cache directory
   *
   * @return array
   */
  public function files()
  {
    $oDirIterator = new \RecursiveDirectoryIterator($this->sCacheDir);
    $oIterator = new \RecursiveIteratorIterator($oDirIterator, \RecursiveIteratorIterator::SELF_FIRST);
    $aFile = [];

    foreach ($oIterator as $oFile)
    {
      if ($oFile->isFile())
      {
        $aFile[] =
        [
          'name' => $oFile->getPathname(),
          'size' => $oFile->getSize(),
          'modified' => date('Y-m-d', $oFile->getMTime())
        ];
      }
    }

    return $aFile;
  }
}