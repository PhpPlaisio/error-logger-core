<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger\Test;

/**
 * Just a class.
 */
class TestClassB
{
  //--------------------------------------------------------------------------------------------------------------------
  public static $type;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Just a method.
   *
   * @param mixed $arg Just a argument.
   */
  public function methodB(mixed $arg): void
  {
    switch (self::$type)
    {
      case 'divide-by-zero':
        $x = 1 / 0;
        break;

      case 'undefined-method':
        $this->methodNoSuchMethod();
        break;

      case 'exception':
        throw new \RuntimeException();

      case 'internal-error':
        new \mysqli('no-such-host', 'no-such-user', 'qwerty', 'no-such-database');
        break;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
