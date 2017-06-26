<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\ErrorLogger;

/**
 * An error logger that can be safely used on production environments. All data is of the error log is written to a
 * file.
 */
class ProductionErrorLogger extends CoreErrorLogger
{
  /**
   * The path to the directory for storing error log files.
   *
   * @var string
   */
  private $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $path The path to the directory for storing error log files.
   */
  public function __construct($path)
  {
    $this->path = $path;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Opens the stream to were the error log must be written.
   */
  protected function openStream()
  {
    $filename     = $this->getFilename();
    $this->handle = fopen($filename, 'wb');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the filename of the error log file.
   *
   * @return string
   */
  private function getFilename()
  {
    $dateTime = new \DateTime();

    return $this->path.'/error-'.$dateTime->format('Ymd-His-u').'-'.getmypid().'.html';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
