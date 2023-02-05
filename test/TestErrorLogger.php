<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger\Test;

use Plaisio\ErrorLogger\CoreErrorLogger;

/**
 * An error logger for testing purposes.
 */
class TestErrorLogger extends CoreErrorLogger
{
  //--------------------------------------------------------------------------------------------------------------------
  public static string $filename = 'test.html';

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
