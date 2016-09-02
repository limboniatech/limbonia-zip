<?php
namespace Omniverse\Lib;
// official ZIP file format: http://www.info-zip.org/pub/infozip/doc/appnote-iz-latest.zip
//based on the code found here: http://www.planet-source-code.com/vb/scripts/ShowCode.asp?txtCodeId=957&lngWId=8

class Zip
{
  static $sEmptyUnsignedLong = null;

  static $sEmptyUnsignedShort = null;

  //due to being based on DOS timestamps this is the lowest date that zip allows
  static $iMinTimeStamp = 315561600; //unix timestamp for 12:00 AM 01-01-1980

  private $aCompressedData = []; //array to store compressed data
  private $aCentralRecord = []; //central directory record
  private $nOffset = 0;

  protected static function longPack($iArg)
  {
    return pack('V', $iArg);
  }

  protected static function shortPack($iArg)
  {
    return pack('v', $iArg);
  }

  protected static function emptyUnsignedLong()
  {
    if (\is_null(self::$sEmptyUnsignedLong))
    {
      self::$sEmptyUnsignedLong = self::longPack(0);
    }

    return self::$sEmptyUnsignedLong;
  }

  protected static function emptyUnsignedShort()
  {
    if (\is_null(self::$sEmptyUnsignedShort))
    {
      self::$sEmptyUnsignedShort = self::shortPack(0);
    }

    return self::$sEmptyUnsignedShort;
  }

  protected static function minTimeStamp()
  {
    return self::$iMinTimeStamp;
  }

  protected function DosTime($nTimeStamp = null)
  {
    $aTime = empty($nTimeStamp) ? getdate() : getdate($nTimeStamp < self::minTimeStamp() ? self::minTimeStamp() : $nTimeStamp);
    return self::longPack(($aTime['seconds'] >> 1) | ($aTime['minutes'] << 5) | ($aTime['hours'] << 11) | ($aTime['mday'] << 16) | ($aTime['mon'] << 21) | (($aTime['year'] - 1980) << 25));
  }

  public function AddDir($sName, $nUnixTimeStamp=null)
  // adds "directory" to archive - do this before putting any files in directory!
  // $name - name of directory... like this: "path/"
  // ...then you can add files using add_file with names like "path/file.txt"
  {
    $sName = str_replace("\\", "/", $sName);

    //the current directory "./" doesn't need to be added...
    static $aAddedDirs = array("./");

    if (in_array($sName, $aAddedDirs))
    {
      return true;
    }

    $nTimeStamp = $this->DosTime($nUnixTimeStamp); //directory "last modified" timestamp

    // add this entry to array
    $this->aCompressedData[] = "\x50\x4b\x03\x04" .
      "\x0a\x00" . //ver needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x00\x00" . //compression method
      $nTimeStamp .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong() . //uncompressed filesize
      self::shortPack(strlen($sName)) . //length of pathname
      self::emptyUnsignedShort() . //extra field length
      $sName .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong(); //uncompressed filesize

    // now add to central record
    $this->aCentralRecord[] = "\x50\x4b\x01\x02" .
      "\x00\x00" . //version made by
      "\x0a\x00" . //version needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x00\x00" . //compression method
      $nTimeStamp .
      self::emptyUnsignedLong() . //crc32
      self::emptyUnsignedLong() . //compressed filesize
      self::emptyUnsignedLong() . //uncompressed filesize
      self::shortPack(strlen($sName)) . //length of filename
      self::emptyUnsignedShort() . //extra field length
      self::emptyUnsignedShort() . //file comment length
      self::emptyUnsignedShort() . //disk number start
      self::emptyUnsignedShort() . //internal file attributes
      self::longPack(16) . //external file attributes  - 'directory' bit set
      self::longPack($this->nOffset) . //relative offset of local header
      $sName;

    //now that it's been used we can update the offset to the current location
    $this->nOffset = strlen(implode('', $this->aCompressedData));
    $aAddedDirs[] = $sName;
  }

  public function AddFile($sName, $sData, $nUnixTimeStamp=null)
  {
    $sName = str_replace("\\", "/", $sName);
    static $aAddedFiles = [];

    if (in_array($sName, $aAddedFiles))
    {
      return true;
    }

    $nTimeStamp = $this->DosTime($nUnixTimeStamp); //file's "last modified" timestamp

    $zCompressedData = gzcompress($sData);
    $zCompressedData = substr(substr($zCompressedData, 0, strlen($zCompressedData) - 4), 2); //fix crc bug

    $nDataInfo = self::longPack(crc32($sData)) .
      self::longPack(strlen($zCompressedData)) . //compressed file size
      self::longPack(strlen($sData)); //uncompressed file size
    $nNameLenth = self::shortPack(strlen($sName)); //length of filename

    // add this entry to the compressed data array
    $this->aCompressedData[] = "\x50\x4b\x03\x04" . //
      "\x14\x00" . //ver needed to extract
      "\x00\x00" . //gen purpose bit flag
      "\x08\x00" . //compression method
      $nTimeStamp . $nDataInfo . $nNameLenth .
      self::emptyUnsignedShort() . //extra field length
      $sName . $zCompressedData . $nDataInfo;

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
      self::longPack($this->nOffset) . //relative offset of local header
      $sName;

    //now that it's been used we can update the offset to the current location
    $this->nOffset = strlen(implode('', $this->aCompressedData));
    $aAddedFiles[] = $sName;
  }

  public function AddRealFile($sFileName, $sDir=null)
  {
    if (!is_file($sFileName))
    {
      trigger_error("The file \"$sFileName\" does *not* exist.");
      return false;
    }

    $aFileInfo = pathinfo($sFileName);

    if (empty($sDir))
    {
      $sDir = $aFileInfo['dirname'] . '/';
    }

    $nDirTime = is_dir($sDir) ? filemtime($sDir) : time();
    $this->AddDir($sDir, $nDirTime);
    $rFile = fopen($sFileName, "r");
    $this->AddFile($sDir . $aFileInfo['basename'], fread($rFile, filesize($sFileName)), filemtime($sFileName));
    fclose($rFile);
  }

  public function save($sFileName)
  {
    $rFile = fopen($sFileName, 'w');
    fwrite($rFile, $this->create());
    fclose($rFile);
  }

  public function Download($sFileName)
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
}
?>