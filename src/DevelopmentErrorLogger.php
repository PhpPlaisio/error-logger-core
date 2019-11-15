<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger;

/**
 * An error logger for development purposes. It will show all information of the error on the user's screen. Use this
 * error logger on development environments only.
 */
class DevelopmentErrorLogger extends CoreErrorLogger
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Opens output.
   */
  protected function openStream(): void
  {
    $this->handle = fopen('php://output', 'wb');
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
