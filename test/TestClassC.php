<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger\Test;

/**
 * A class with a parent without a comment.
 */
class TestClassC implements \Countable
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Does not count.
   */
  public function count(): int
  {
    throw new \RuntimeException();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
