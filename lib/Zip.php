<?php
namespace Limbonia;

/**
 * Limbonia zip creation class
 *
 * Official ZIP file format: http://www.info-zip.org/pub/infozip/doc/appnote-iz-latest.zip
 * Based on the code found here: http://www.planet-source-code.com/vb/scripts/ShowCode.asp?txtCodeId=957&lngWId=8
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @version $Revision: 1.11 $
 * @package Limbonia
 */
class Zip
{
  /**
   * A 4 byte empty string
   *
   * @var string
   */
  private static $sEmptyUnsignedLong = null;

  /**
   * A 2 byte empty string
   *
   * @var string
   */
  private static $sEmptyUnsignedShort = null;

  /**
   * The unix timestamp for 12:00 AM 01-01-1980
   *
   * @note due to being based on DOS timestamps this is the lowest date that zip allows
   *
   * @var integer
   */
  static $iMinTimeStamp = 315561600;

  /**
   * The compressed data
   *
   * @var array
   */
  private $aCompressedData = [];

  /**
   * The central directory record
   *
   * @var array
   */
  private $aCentralRecord = [];

  /**
   * The current data offset
   *
   * @var integer
   */
  private $iOffset = 0;

  /**
   * List of directories that have already been added to the archive
   *
   * @note - The current directory "./" doesn't need to be added...
   *
   * @var array
   */
  private $aAddedDirs = ['./'];

  /**
   * List of files that have already been added to the archive
   *
   * @var array
   */
  private $aAddedFiles = [];

  /**
   * Return a string version if the specified integer packed into a 4 byte string
   *
   * @param integer $iArg
   * @return string
   */
  protected static function longPack($iArg)
  {
    return pack('V', $iArg);
  }

  /**
   * Return a string version if the specified integer packed into a 2 byte string
   *
   * @param integer $iArg
   * @return string
   */
  protected static function shortPack($iArg)
  {
    return pack('v', $iArg);
  }

  /**
   * Return an long packed 0
   *
   * @return string
   */
  protected static function emptyUnsignedLong()
  {
    if (\is_null(self::$sEmptyUnsignedLong))
    {
      self::$sEmptyUnsignedLong = self::longPack(0);
    }

    return self::$sEmptyUnsignedLong;
  }

  /**
   * Return an short packed 0
   *
   * @return string
   */
  protected static function emptyUnsignedShort()
  {
    if (\is_null(self::$sEmptyUnsignedShort))
    {
      self::$sEmptyUnsignedShort = self::shortPack(0);
    }

    return self::$sEmptyUnsignedShort;
  }

  /**
   * Return the minimum time stamp allowed by the zip protocol
   *
   * @return integer
   */
  protected static function minTimeStamp()
  {
    return self::$iMinTimeStamp;
  }

  /**
   * Translate the specified UNIX timestamp into a DOS timestamp and return it
   *
   * @param integer $iTimeStamp
   * @return integer
   */
  protected function dosTime($iTimeStamp = null)
  {
    $aTime = empty($iTimeStamp) ? getdate() : getdate($iTimeStamp < self::minTimeStamp() ? self::minTimeStamp() : $iTimeStamp);
    return self::longPack(($aTime['seconds'] >> 1) | ($aTime['minutes'] << 5) | ($aTime['hours'] << 11) | ($aTime['mday'] << 16) | ($aTime['mon'] << 21) | (($aTime['year'] - 1980) << 25));
  }

  /**
   * Generate and return the actual zip content
   *
   * @return binary
   */
  protected function create()
  {
    $zCompressedData = implode('', $this->aCompressedData);
    $sCentralRecord = implode('', $this->aCentralRecord);
    $nRecordCount = self::shortPack(count($this->aCentralRecord));

    return $zCompressedData . $sCentralRecord .
      "\x50\x4b\x05\x06\x00\x00\x00\x00" . //end of central record
      //unless the zip spans disks, the next two numbers should be identical...
      $nRecordCount . //total # of entries "on this disk"
      $nRecordCount . //total # of entries overall
      self::longPack(strlen($sCentralRecord)) . //size of central directory record
      self::longPack(strlen($zCompressedData)) . //offset to start of central dir
      "\x00\x00"; //.zip file comment length
  }

  /**
   * Add the specified directory to the zip archive
   *
   * @note Do this before putting any files in to the specified directory! ...then you can add files using addFile with names like "path/file.txt"
   *
   * @param string $sName - Name of directory... like this: "path/"
   * @param integer $iUnixTimeStamp (optional) - The UNIX timestamp for the directory's "last modified" time
   * @return boolean
   */
  public function addDir($sName, $iUnixTimeStamp = null)
  {
    $sFilteredName = str_replace("\\", "/", $sName);

    if (in_array($sFilteredName, $this->aAddedDirs))
    {
      return true;
    }

    $iTimeStamp = $this->dosTime($iUnixTimeStamp);

    // add this entry to array
    $this->aCompressedData[] = "\x50\x4b\x03\x04" .
      "\x0a\x00" . //ver needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x00\x00" . //compression method
      $iTimeStamp .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong() . //uncompressed filesize
      self::shortPack(strlen($sFilteredName)) . //length of pathname
      self::emptyUnsignedShort() . //extra field length
      $sFilteredName .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong(); //uncompressed filesize

    // now add to central record
    $this->aCentralRecord[] = "\x50\x4b\x01\x02" .
      "\x00\x00" . //version made by
      "\x0a\x00" . //version needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x00\x00" . //compression method
      $iTimeStamp .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong() . //uncompressed filesize
      self::shortPack(strlen($sFilteredName)) . //length of filename
      self::emptyUnsignedShort() . //extra field length
      self::emptyUnsignedShort() . //file comment length
      self::emptyUnsignedShort() . //disk number start
      self::emptyUnsignedShort() . //internal file attributes
      self::longPack(16) . //external file attributes  - 'directory' bit set
      self::longPack($this->iOffset) . //relative offset of local header
      $sFilteredName;

    //now that it's been used we can update the offset to the current location
    $this->iOffset = strlen(implode('', $this->aCompressedData));
    $this->aAddedDirs[] = $sFilteredName;
  }

  /**
   * Add a file as specified by its name and data to the zip archive
   *
   * @param string $sName
   * @param string $sData
   * @param integer $iUnixTimeStamp (optional) - The file's "last modified" time
   * @return boolean
   */
  public function addFile($sName, $sData, $iUnixTimeStamp = null)
  {
    $sFilteredName = str_replace("\\", "/", $sName);

    if (in_array($sFilteredName, $this->aAddedFiles))
    {
      return true;
    }

    $nTimeStamp = $this->dosTime($iUnixTimeStamp);

    $zTemp = gzcompress($sData);
    $zCompressedData = substr(substr($zTemp, 0, strlen($zTemp) - 4), 2); //fix crc bug

    $nDataInfo = self::longPack(crc32($sData)) .
      self::longPack(strlen($zCompressedData)) . //compressed file size
      self::longPack(strlen($sData)); //uncompressed file size
    $nNameLenth = self::shortPack(strlen($sFilteredName)); //length of filename

    // add this entry to the compressed data array
    $this->aCompressedData[] = "\x50\x4b\x03\x04" .
      "\x14\x00" . //ver needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x08\x00" . //compression method
      $nTimeStamp . $nDataInfo . $nNameLenth .
      self::emptyUnsignedShort() . //extra field length
      $sFilteredName . $zCompressedData . $nDataInfo;

    // now add to central directory record array
    $this->aCentralRecord[] = "\x50\x4b\x01\x02" .
      "\x00\x00" . //version made by
      "\x14\x00" . //version needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x08\x00" . //compression method
      $nTimeStamp . $nDataInfo . $nNameLenth .
      self::emptyUnsignedShort() . //extra field length
      self::emptyUnsignedShort() . //file comment length
      self::emptyUnsignedShort() . //disk number start
      self::emptyUnsignedShort() . //internal file attributes
      self::longPack(32) . //external file attributes - 'archive' bit set
      self::longPack($this->iOffset) . //relative offset of local header
      $sFilteredName;

    //now that it's been used we can update the offset to the current location
    $this->iOffset = strlen(implode('', $this->aCompressedData));
    $this->aAddedFiles[] = $sFilteredName;
  }

  /**
   * Add an existing file to the archive
   *
   * @param string $sFileName
   * @param string $sDir (optional)
   * @throws Exception
   */
  public function addRealFile($sFileName, $sDir = '')
  {
    if (!is_file($sFileName))
    {
      throw new Exception("The file \"$sFileName\" does *not* exist.");
    }

    $aFileInfo = pathinfo($sFileName);

    if (empty($sDir))
    {
      $sDir = $aFileInfo['dirname'] . '/';
    }

    $nDirTime = is_dir($sDir) ? filemtime($sDir) : time();
    $this->addDir($sDir, $nDirTime);
    $rFile = fopen($sFileName, "r");
    $this->addFile($sDir . $aFileInfo['basename'], fread($rFile, filesize($sFileName)), filemtime($sFileName));
    fclose($rFile);
  }

  /**
   * Save the current archive to the specified filename
   *
   * @param string $sFileName
   */
  public function save($sFileName)
  {
    $rFile = fopen($sFileName, 'w');
    fwrite($rFile, $this->create());
    fclose($rFile);
  }

  /**
   * Initiate a download of the current archive to the specified filename
   *
   * @param string $sFileName
   * @return boolean
   */
  public function download($sFileName)
  {
    if (headers_sent($sFile, $iLine))
    {
      echo "\n<!-- Headers have already been sent by \"$sFile\" on line $iLine, so the zip file \"$sFileName\" can't be sent! -->\n<h3>Download failed, please try again!</h3>";
      return false;
    }

    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment; filename=$sFileName");
    echo $this->create();
  }
}