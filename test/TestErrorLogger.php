<?php
declare(strict_types=1);

namespace SetBased\Abc\ErrorLogger\Test;

use SetBased\Abc\ErrorLogger\CoreErrorLogger;

/**
 * An error logger for testing purposes.
 */
class TestErrorLogger extends CoreErrorLogger
{
  //--------------------------------------------------------------------------------------------------------------------
  public static $filename = 'test.html';

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  protected function openStream(): void
  {
    $this->handle = fopen(self::$filename, 'wb');
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
}

//----------------------------------------------------------------------------------------------------------------------
