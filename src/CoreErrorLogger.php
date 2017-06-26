<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\ErrorLogger;

use SetBased\Abc\Helper\Html;

/**
 * An abstract error logger that writes the error log in HTML format to a stream and any errors and exception during
 * the error logging itself are suppressed.
 */
abstract class CoreErrorLogger implements ErrorLogger
{
  protected static $errorNames = [E_COMPILE_ERROR     => 'PHP Compile Error',
                                  E_COMPILE_WARNING   => 'PHP Compile Warning',
                                  E_CORE_ERROR        => 'PHP Core Error',
                                  E_CORE_WARNING      => 'PHP Core Warning',
                                  E_DEPRECATED        => 'PHP Deprecated Warning',
                                  E_ERROR             => 'PHP Fatal Error',
                                  E_NOTICE            => 'PHP Notice',
                                  E_PARSE             => 'PHP Parse Error',
                                  E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
                                  E_STRICT            => 'PHP Strict Warning',
                                  E_USER_DEPRECATED   => 'PHP User Deprecated Warning',
                                  E_USER_ERROR        => 'PHP User Error',
                                  E_USER_NOTICE       => 'PHP User Notice',
                                  E_USER_WARNING      => 'PHP User Warning',
                                  E_WARNING           => 'PHP Warning'];

  /**
   * The output handle.
   *
   * @var resource
   */
  protected $handle;

  /**
   * The number of source lines shown.
   *
   * @var int
   */
  private $numberOfSourceLines = 24;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Logs an error.
   *
   * @param \Throwable $throwable The error to be logged.
   */
  public function logError($throwable)
  {
    try
    {
      $this->openStream();

      $this->echoPageLeader();

      $this->echoErrorLog($throwable);

      $this->echoPageTrailer();
    }
    catch(\Throwable $throwable)
    {
      // Nothing to do.
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the log of an error.
   *
   * @param \Throwable|null $throwable  The error.
   * @param bool            $isPrevious If true the exception is a previous exception.
   */
  protected function echoErrorLog($throwable, $isPrevious = false)
  {
    // Return immediately if there is not throwable.
    if ($throwable===null) return;

    if (!$isPrevious)
    {
      fwrite($this->handle, Html::generateElement('h1', [], get_class($throwable)));
    }
    else
    {
      fwrite($this->handle, Html::generateElement('h2', [], 'Previous Exception: '.get_class($throwable)));
    }

    if (isset(self::$errorNames[$throwable->getCode()]))
    {
      fwrite($this->handle, Html::generateElement('p', ['class' => 'code'], self::$errorNames[$throwable->getCode()]));
    }

    $message = str_replace("\n", '<br/>', Html::txt2Html($throwable->getMessage()));
    fwrite($this->handle, Html::generateElement('p', ['class' => 'message'], $message, true));

    fwrite($this->handle, Html::generateElement('p', ['class' => 'file'], $throwable->getFile().'('.$throwable->getLine().')'));

    $this->echoFileSnippet($throwable->getFile(), $throwable->getLine());

    $this->echoTraceStack($throwable);

    $this->echoErrorLog($throwable->getPrevious(), true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document leader, i.e. the start html tag, the head element, and start body tag.
   */
  protected function echoPageLeader()
  {
    fwrite($this->handle, '<!DOCTYPE html>');
    fwrite($this->handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">');
    fwrite($this->handle, '<head>');
    fwrite($this->handle, '<title>Exception</title>');

    fwrite($this->handle, '<style>');
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets'.'/css/reset.css'));
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets'.'/css/error.css'));
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets'.'/css/dracula.css'));
    fwrite($this->handle, '</style>');

    fwrite($this->handle, '<script>');
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets'.'/js/highlight.pack.js'));
    fwrite($this->handle, '</script>');
    fwrite($this->handle, '<script>hljs.initHighlightingOnLoad();</script>');
    fwrite($this->handle, '</head><body>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document trailer.
   */
  protected function echoPageTrailer()
  {
    fwrite($this->handle, '</body></html>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Opens the stream to were the error log must be written.
   *
   * @return void
   */
  abstract protected function openStream();

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Converts the arguments of a callable to a string.
   *
   * @param array $args The arguments.
   *
   * @return string
   */
  private function argumentsToString($args)
  {
    $isAssoc = ($args!==array_values($args));

    $count = 0;
    foreach ($args as $key => $value)
    {
      $count++;
      if ($count>=7)
      {
        if ($count>7)
        {
          unset($args[$key]);
        }
        else
        {
          $args[$key] = '...';
        }
        continue;
      }

      if (is_object($value))
      {
        $args[$key] = Html::generateElement('span', ['class' => 'class'], get_class($value));
      }
      elseif (is_bool($value))
      {
        $args[$key] = Html::generateElement('span', ['class' => 'keyword'], ($value ? 'true' : 'false'));
      }
      elseif (is_string($value))
      {
        if (mb_strlen($value)>32)
        {
          $args[$key] = Html::generateElement('span',
                                              ['class' => 'string',
                                               'title' => mb_substr($value, 0, 512)],
                                              mb_substr($value, 0, 32).'...');
        }
        else
        {
          $args[$key] = Html::generateElement('span', ['class' => 'string'], $value);
        }
      }
      elseif (is_array($value))
      {
        $args[$key] = '['.$this->argumentsToString($value).']';
      }
      elseif ($value===null)
      {
        $args[$key] = '<span class="keyword">null</span>';
      }
      elseif (is_resource($value))
      {
        $args[$key] = '<span class="keyword">resource</span>';
      }
      elseif (is_numeric($value))
      {
        $args[$key] = '<span class="number">'.$value.'</span>';
      }
      else
      {
        $args[$key] = '<span class="unknown">???</span>';
      }

      if (is_string($key))
      {
        $tmp = Html::generateElement('span', ['class' => 'string'], $key);
        $tmp .= ' => ';
        $tmp .= (strpos($key, 'password')===false) ? $args[$key] : str_repeat('*', 12);

        $args[$key] = $tmp;
      }
      elseif ($isAssoc)
      {
        $tmp = Html::generateElement('span', ['class' => 'number'], $key);
        $tmp .= ' => ';
        $tmp .= $args[$key];

        $args[$key] = $tmp;
      }
    }

    return implode(', ', $args);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the name of a callable in a trace stack item.
   *
   * @param array $item The trace stack item.
   */
  private function echoCallable($item)
  {
    if ($item['class']===null)
    {
      fwrite($this->handle, Html::generateElement('span', ['class' => 'function'], $item['function']));
    }
    else
    {
      fwrite($this->handle, Html::generateElement('span', ['class' => 'class'], $item['class']));
      fwrite($this->handle, '::');
      fwrite($this->handle, Html::generateElement('span', ['class' => 'function'], $item['function']));
    }

    fwrite($this->handle, '(');
    fwrite($this->handle, $this->argumentsToString($item['args']));
    fwrite($this->handle, ')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos a snippet of a source file around a source line.
   *
   * @param string $filename The name of the file.
   * @param int    $line     The source line number.
   */
  private function echoFileSnippet($filename, $line)
  {
    $lines = explode("\n", file_get_contents($filename));
    $first = max(1, $line - $this->numberOfSourceLines / 2);
    $last  = min(sizeof($lines), $line + $this->numberOfSourceLines / 2);

    fwrite($this->handle, '<div class="source">');

    // div with lines numbers.
    fwrite($this->handle, '<div class="lines">');
    fwrite($this->handle, Html::generateTag('ol', ['start' => $first]));
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, '<li></li>');
    }
    fwrite($this->handle, '</ol>');
    fwrite($this->handle, '</div>');

    // The code (without tags).
    fwrite($this->handle, '<pre><code class="php">');
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, Html::txt2Html($lines[$i - 1]));
      fwrite($this->handle, "\n");
    }
    fwrite($this->handle, '</code></pre>');

    fwrite($this->handle, '<div class="markup">');
    fwrite($this->handle, Html::generateTag('ol', ['start' => $first]));
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, Html::generateElement('li', ['class' => ($i==$line) ? 'error' : null], ''));
    }
    fwrite($this->handle, '</ol>');
    fwrite($this->handle, '</div>');

    fwrite($this->handle, '</div>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos an item of a trace stack.
   *
   * @param int   $number The item number.
   * @param array $item   The item of the trace stack.
   */
  private function echoTraceItem($number, $item)
  {
    fwrite($this->handle, '<p class="file">');

    fwrite($this->handle, Html::generateElement('span', ['class' => 'level'], $number));

    fwrite($this->handle, Html::generateElement('span', ['class' => 'file'], $item['file'].'('.$item['line'].'):'));

    $this->echoCallable($item);

    $this->echoFileSnippet($item['file'], $item['line']);

    fwrite($this->handle, '</p>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the trace stock of a throwable.
   *
   * @param \Throwable $throwable The throwable.
   */
  private function echoTraceStack($throwable)
  {
    fwrite($this->handle, '<div class="trace">');
    fwrite($this->handle, '<h2>Stack Trace</h2>');

    $trace = $throwable->getTrace();
    $level = count($trace);
    foreach ($trace as $item)
    {
      $this->echoTraceItem(--$level, $item);
    }

    fwrite($this->handle, '</div>');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
