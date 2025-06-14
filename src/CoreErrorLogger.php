<?php
declare(strict_types=1);

namespace Plaisio\ErrorLogger;

use Plaisio\Debug\VarDumper;
use Plaisio\Helper\Html;

/**
 * An abstract error logger that writes the error log in HTML format to a stream and any errors and exception during
 * the error logging itself are suppressed.
 */
abstract class CoreErrorLogger implements ErrorLogger
{
  //--------------------------------------------------------------------------------------------------------------------
  protected static array $errorNames = [E_COMPILE_ERROR     => 'PHP Compile Error',
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
   * The variables to be dumped in the log.
   *
   * @var array|null
   */
  private ?array $dump = null;

  /**
   * The number of source lines shown.
   *
   * @var int
   */
  private int $numberOfSourceLines = 24;

  /**
   * If true scalar references to values must be traced.
   *
   * @var bool
   */
  private bool $scalarReferences;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Main function for dumping.
   *
   * @param mixed $dump             The variables for dumping.
   * @param bool  $scalarReferences If true scalar references to values must be traced.
   *
   * @api
   * @since 1.0.0
   */
  public function dumpVars(mixed $dump, bool $scalarReferences = false): void
  {
    $this->dump             = $dump;
    $this->scalarReferences = $scalarReferences;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Logs an error.
   *
   * @param \Throwable $throwable The error to be logged.
   *
   * @return void
   *
   * @api
   * @since 1.0.0
   */
  public function logError(\Throwable $throwable): void
  {
    try
    {
      $this->openStream();
      $this->echoPageLeader();
      $this->echoErrorLog($throwable);
      $this->echoVarDump();
      $this->echoPageTrailer();
      $this->closeStream();
    }
    catch (\Throwable)
    {
      // Nothing to do.
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Closes the stream were the error log is written to.
   *
   * @return void
   */
  abstract protected function closeStream(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the log of an error.
   *
   * @param \Throwable|null $throwable  The error.
   * @param bool            $isPrevious If true the exception is a previous exception.
   */
  protected function echoErrorLog(?\Throwable $throwable, bool $isPrevious = false): void
  {
    // Return immediately if there is not throwable.
    if ($throwable===null)
    {
      return;
    }

    if (!$isPrevious)
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'h1',
                                              'text' => get_class($throwable)]));
    }
    else
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'h2',
                                              'text' => 'Previous Exception: '.get_class($throwable)]));
    }

    if (isset(self::$errorNames[$throwable->getCode()]))
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'p',
                                              'attr' => ['class' => 'code'],
                                              'text' => self::$errorNames[$throwable->getCode()]]));
    }

    $message = str_replace("\n", '<br/>', Html::txt2Html($throwable->getMessage()));
    fwrite($this->handle, Html::htmlNested(['tag'  => 'p',
                                            'attr' => ['class' => 'message'],
                                            'html' => $message]));

    fwrite($this->handle, Html::htmlNested(['tag'  => 'p',
                                            'attr' => ['class' => 'file'],
                                            'text' => $throwable->getFile().'('.$throwable->getLine().')']));

    $this->echoFileSnippet($throwable->getFile(), $throwable->getLine());

    $this->echoTraceStack($throwable);

    $this->echoErrorLog($throwable->getPrevious(), true);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document leader, i.e. the start html tag, the head element, and start body tag.
   */
  protected function echoPageLeader(): void
  {
    fwrite($this->handle, '<!DOCTYPE html>');
    fwrite($this->handle, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">');
    fwrite($this->handle, '<head>');
    fwrite($this->handle, Html::htmlNested(['tag' => 'meta', 'attr' => ['charset' => Html::$encoding]]));
    fwrite($this->handle, '<title>Exception</title>');

    fwrite($this->handle, '<style>');
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets/css/reset.css'));
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets/css/error.css'));
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets/css/dracula.css'));
    fwrite($this->handle, '</style>');

    fwrite($this->handle, '<script>');
    fwrite($this->handle, file_get_contents(__DIR__.'/../assets/js/highlight.pack.js'));
    fwrite($this->handle, '</script>');
    fwrite($this->handle, '<script>hljs.initHighlightingOnLoad();</script>');
    fwrite($this->handle, '</head><body>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document trailer.
   */
  protected function echoPageTrailer(): void
  {
    fwrite($this->handle, '</body></html>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Opens the stream to where the error log must be written.
   *
   * @return void
   */
  abstract protected function openStream(): void;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Converts the arguments of a callable to a string.
   *
   * @param array $args The arguments.
   *
   * @return string
   */
  private function argumentsToString(array $args): string
  {
    $isAssoc = ($args!==array_values($args));

    $count = 0;
    $out   = [];
    foreach ($args as $key => $value)
    {
      $count++;
      if ($count>=7)
      {
        $out[$key] = '...';
        break;
      }

      if (is_object($value))
      {
        $out[$key] = Html::htmlNested(['tag'  => 'span',
                                       'attr' => ['class' => 'class'],
                                       'text' => get_class($value)]);
      }
      elseif (is_bool($value))
      {
        $out[$key] = Html::htmlNested(['tag'  => 'span',
                                       'attr' => ['class' => 'keyword'],
                                       'text' => ($value ? 'true' : 'false')]);
      }
      elseif (is_string($value))
      {
        if (mb_strlen($value)>32)
        {
          $out[$key] = Html::htmlNested(['tag'  => 'span',
                                         'attr' => ['class' => 'string',
                                                    'title' => mb_substr($value, 0, 512)],
                                         'text' => mb_substr($value, 0, 32).'...']);
        }
        else
        {
          $out[$key] = Html::htmlNested(['tag'  => 'span',
                                         'attr' => ['class' => 'string'],
                                         'text' => $value]);
        }
      }
      elseif (is_array($value))
      {
        $out[$key] = '['.$this->argumentsToString($value).']';
      }
      elseif ($value===null)
      {
        $out[$key] = '<span class="keyword">null</span>';
      }
      elseif (is_resource($value))
      {
        $out[$key] = Html::htmlNested(['tag'  => 'span',
                                       'attr' => ['class' => 'keyword'],
                                       'text' => get_resource_type($value)]);
      }
      elseif (is_numeric($value))
      {
        $out[$key] = Html::htmlNested(['tag'  => 'span',
                                       'attr' => ['class' => 'number'],
                                       'text' => $value]);
      }
      else
      {
        $out[$key] = '<span class="unknown">???</span>';
      }

      if (is_string($key))
      {
        $tmp = Html::htmlNested(['tag'  => 'span',
                                 'attr' => ['class' => 'string'],
                                 'text' => $key]);
        $tmp .= ' => ';
        $tmp .= (strpos($key, 'password')===false) ? $out[$key] : str_repeat('*', 12);

        $out[$key] = $tmp;
      }
      elseif ($isAssoc)
      {
        $tmp = Html::htmlNested(['tag'  => 'span',
                                 'attr' => ['class' => 'number'],
                                 'text' => $key]);
        $tmp .= ' => ';
        $tmp .= $out[$key];

        $out[$key] = $tmp;
      }
    }

    return implode(', ', $out);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the name of a callable in a trace stack item.
   *
   * @param array $item The trace stack item.
   */
  private function echoCallable(array $item): void
  {
    if (isset($item['class']))
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'span',
                                              'attr' => ['class' => 'class'],
                                              'text' => $item['class']]));
      fwrite($this->handle, '::');
      fwrite($this->handle, Html::htmlNested(['tag'  => 'span',
                                              'attr' => ['class' => 'function'],
                                              'text' => $item['function']]));
    }
    else
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'span',
                                              'attr' => ['class' => 'function'],
                                              'text' => $item['function']]));
    }

    fwrite($this->handle, '(');
    fwrite($this->handle, $this->argumentsToString($item['args'] ?? []));
    fwrite($this->handle, ')');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos a snippet of a source file around a source line.
   *
   * @param string $filename The name of the file.
   * @param int    $line     The source line number.
   */
  private function echoFileSnippet(string $filename, int $line): void
  {
    $lines = explode("\n", file_get_contents($filename));
    $first = max(1, $line - $this->numberOfSourceLines / 2);
    $last  = min(sizeof($lines), $line + $this->numberOfSourceLines / 2);

    fwrite($this->handle, '<div class="source">');

    // div with lines numbers.
    fwrite($this->handle, '<div class="lines">');
    fwrite($this->handle, str_replace('/>', '>', Html::htmlNested(['tag'  => 'ol',
                                                                   'attr' => ['start' => $first]])));
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, '<li></li>');
    }
    fwrite($this->handle, '</ol>');
    fwrite($this->handle, '</div>');

    // The code as plain text (without markup and tags).
    fwrite($this->handle, '<pre><code class="php">');
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, Html::txt2Html($lines[$i - 1]));
      fwrite($this->handle, "\n");
    }
    fwrite($this->handle, '</code></pre>');

    // div for markup.
    fwrite($this->handle, '<div class="markup">');
    fwrite($this->handle, str_replace('/>', '>', Html::htmlNested(['tag'  => 'ol',
                                                                   'attr' => ['start' => $first]])));
    for ($i = $first; $i<=$last; $i++)
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'li',
                                              'attr' => ['class' => ($i==$line) ? 'error' : null],
                                              'html' => null]));
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
  private function echoTraceItem(int $number, array $item): void
  {
    fwrite($this->handle, '<p class="file">');

    fwrite($this->handle, Html::htmlNested(['tag'  => 'span',
                                            'attr' => ['class' => 'level'],
                                            'text' => $number]));

    if (isset($item['file']))
    {
      fwrite($this->handle, Html::htmlNested(['tag'  => 'span',
                                              'attr' => ['class' => 'file'],
                                              'text' => $item['file'].'('.$item['line'].'):']));
    }

    $this->echoCallable($item);

    if (isset($item['file']))
    {
      $this->echoFileSnippet($item['file'], $item['line']);
    }

    fwrite($this->handle, '</p>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the trace stock of a throwable.
   *
   * @param \Throwable $throwable The throwable.
   */
  private function echoTraceStack(\Throwable $throwable): void
  {
    $trace = $throwable->getTrace();

    // Return immediately if the trace is empty.
    if (empty($trace))
    {
      return;
    }

    fwrite($this->handle, '<div class="trace">');
    fwrite($this->handle, '<h2>Stack Trace</h2>');

    $level = count($trace);
    foreach ($trace as $item)
    {
      $this->echoTraceItem(--$level, $item);
    }

    fwrite($this->handle, '</div>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos variables.
   */
  private function echoVarDump(): void
  {
    // Return immediately if there are no variables to dump.
    if ($this->dump===null)
    {
      return;
    }

    fwrite($this->handle, Html::htmlNested(['tag'  => 'h2',
                                            'text' => 'VarDump']));

    $varDumper = new VarDumper(new HtmlVarWriter($this->handle));
    $varDumper->dump('', $this->dump, $this->scalarReferences);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
