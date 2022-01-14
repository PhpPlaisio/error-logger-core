<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger;

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
  private string $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $path The path to the directory for storing error log files.
   */
  public function __construct(string $path)
  {
    $this->path = $path;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  protected function closeStream(): void
  {
    fclose($this->handle);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  protected function openStream(): void
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
  private function getFilename(): string
  {
    $dateTime = new \DateTime();

    return $this->path.'/error-'.$dateTime->format('Ymd-His-u').'-'.getmypid().'.html';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
