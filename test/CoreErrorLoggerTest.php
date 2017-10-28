<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\ErrorLogger\Test;

use PHPUnit\Framework\TestCase;
use SetBased\Abc\ErrorLogger\CoreErrorLogger;

/**
 * Test cases for DevelopmentErrorLogger.
 */
class CoreErrorLoggerTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The error logger.
   *
   * @var CoreErrorLogger
   */
  protected $errorLogger;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function setUp()
  {
    parent::setUp();

    $this->errorLogger = new TestErrorLogger();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an object as argument is logged properly.
   */
  public function testArgumentClass()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="class">SetBased\Abc\ErrorLogger\Test\CoreErrorLoggerTest</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests false as argument is logged properly.
   */
  public function testArgumentFalse()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="keyword">false</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a float as argument is logged properly.
   */
  public function testArgumentFloat()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="number">3.14</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an integer as argument is logged properly.
   */
  public function testArgumentInt()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="number">123456</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests null as argument is logged properly.
   */
  public function testArgumentNull()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('(<span class="keyword">null</span>)', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a resource as argument is logged properly.
   */
  public function testArgumentResource()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('(<span class="keyword">stream</span>)', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a string as argument is logged properly.
   */
  public function testArgumentString()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="string">hello world</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests true as argument is logged properly.
   */
  public function testArgumentTrue()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);

    self::assertContains('<span class="keyword">true</span>', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an error is traced properly.
   */
  public function testDivideByZero()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an exception is traced properly.
   */
  public function testException()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests an error from a php function traced properly.
   */
  public function testInternal()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tests a call to an undefined method is traced properly.
   */
  public function testUndefinedMethod()
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

    $output = $this->getOutput();

    $this->defaultAssertions($output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Performs common assertions on the out put of the error logger.
   *
   * @param string $output The output of the error logger.
   */
  private function defaultAssertions($output)
  {
    self::assertContains('<html ', $output);
    self::assertContains('</html>', $output);

    self::assertRegExp('|<p class="file">.*/test/TestClassB\.php\(\d+\)</p>|', $output);
    self::assertRegExp('|<span class="file">.*/test/TestClassA\.php\(\d+\):</span>|', $output);
    self::assertRegExp('|<span class="file">.*/test/CoreErrorLoggerTest.php\(\d+\):</span>|', $output);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the output of the error logger.
   *
   * @return string
   */
  private function getOutput()
  {
    $output = file_get_contents(TestErrorLogger::$filename);

    unlink(TestErrorLogger::$filename);

    return $output;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
