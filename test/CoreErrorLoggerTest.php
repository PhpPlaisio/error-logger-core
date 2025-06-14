<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger\Test;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for DevelopmentErrorLogger.
 */
class CoreErrorLoggerTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The error logger.
   *
   * @var TestErrorLogger
   */
  protected TestErrorLogger $errorLogger;

  /**
   * Whether to debug the output.
   *
   * @var bool
   */
  private bool $debug = false;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function setUp(): void
  {
    parent::setUp();

    ini_set('zend.exception_ignore_args', false);

    $this->errorLogger = new TestErrorLogger();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an object as argument is logged properly.
   */
  public function testArgumentClass(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA($this);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);
    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="class">Plaisio\ErrorLogger\Test\CoreErrorLoggerTest</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests false as argument is logged properly.
   */
  public function testArgumentFalse(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA(false);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="keyword">false</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a float as argument is logged properly.
   */
  public function testArgumentFloat(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA(3.14);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="number">3.14</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an integer as argument is logged properly.
   */
  public function testArgumentInt(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA(123456);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="number">123456</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests null as argument is logged properly.
   */
  public function testArgumentNull(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA(null);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('(<span class="keyword">null</span>)', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a resource as argument is logged properly.
   */
  public function testArgumentResource(): void
  {
    try
    {
      $resource = fopen('php://stdin', 'rb');

      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA($resource);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('(<span class="keyword">stream</span>)', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a string as argument is logged properly.
   */
  public function testArgumentString(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA('hello world');
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="string">hello world</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests true as argument is logged properly.
   */
  public function testArgumentTrue(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA(true);
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);

    self::assertStringContainsString('<span class="keyword">true</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an object as argument is logged properly.
   */
  public function testClassWithoutComment(): void
  {
    try
    {
      $c = new TestClassC();
      $c->count();
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    self::assertStringContainsString('</html>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an error is traced properly.
   */
  public function testDivideByZero(): void
  {
    try
    {
      TestClassB::$type = 'divide-by-zero';
      $a                = new TestClassA();
      $a->methodA();
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an exception is traced properly.
   */
  public function testException(): void
  {
    try
    {
      TestClassB::$type = 'exception';
      $a                = new TestClassA();
      $a->methodA();
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an error from a php function traced properly.
   */
  public function testInternal(): void
  {
    try
    {
      TestClassB::$type = 'internal-error';
      $a                = new TestClassA();
      $a->methodA();
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a call to an undefined method is traced properly.
   */
  public function testStdClass(): void
  {
    $std       = new \stdClass();
    $std->foo  = 'bar'.'bar';
    $std->spam = 'eggs';

    try
    {
      throw new \LogicException('No problem');
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->dumpVars(['std' => $std]);
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    self::assertStringContainsString('<html ', $output);
    self::assertStringContainsString('</html>', $output);

    self::assertMatchesRegularExpression('/th.*foo.*th.*td.*barbar.*td/', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a call to an undefined method is traced properly.
   */
  public function testUndefinedMethod(): void
  {
    try
    {
      TestClassB::$type = 'undefined-method';
      $a                = new TestClassA();
      $a->methodA();
    }
    catch (\Throwable $throwable)
    {
      $this->errorLogger->logError($throwable);
    }

    $output = $this->getOutput(__LINE__);

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an object with uninitialized non-nullable type property.
   */
  public function testUninitializedTypeProperty(): void
  {
    if (PHP_VERSION_ID<=70400)
    {
      static::markTestSkipped('No typed properties.');
    }
    else
    {
      $d = null;
      try
      {
        $d = new TestClassD();
        $d->exception();
      }
      catch (\Throwable $throwable)
      {
        $this->errorLogger->dumpVars([$GLOBALS, $this, $d]);
        $this->errorLogger->logError($throwable);
      }

      $output = $this->getOutput(__LINE__);

      self::assertStringContainsString('<th class="string">qwerty</th>', $output);
      self::assertStringContainsString('<span class="uninitialized">uninitialized</span>', $output);
      self::assertStringContainsString('</html>', $output);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Performs common assertions on the output of the error logger.
   *
   * @param string $output The output of the error logger.
   */
  private function defaultAssertions(string $output): void
  {
    self::assertStringContainsString('<html ', $output);
    self::assertStringContainsString('</html>', $output);

    self::assertMatchesRegularExpression('|<p class="file">.*/test/TestClassB\.php\(\d+\)</p>|', $output);
    self::assertMatchesRegularExpression('|<span class="file">.*/test/TestClassA\.php\(\d+\):</span>|', $output);
    self::assertMatchesRegularExpression('|<span class="file">.*/test/CoreErrorLoggerTest.php\(\d+\):</span>|', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the output of the error logger.
   *
   * @param int $line The line number from where this method has been called.
   *
   * @return string
   */
  private function getOutput(int $line): string
  {
    $output = file_get_contents(TestErrorLogger::$filename);

    if ($this->debug)
    {
      copy(TestErrorLogger::$filename, sprintf('test-%d.html', $line));
    }

    unlink(TestErrorLogger::$filename);

    return $output;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
