<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\ErrorLogger;

use SetBased\Abc\Debug\VarWriter;
use SetBased\Abc\Helper\Html;

/**
 * Writes a var dump in HTML to a stream.
 */
class HtmlVarWriter implements VarWriter
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The output handle.
   *
   * @var resource
   */
  protected $handle;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param resource $handle The handle to write the var dump to.
   */
  public function __construct($handle)
  {
    $this->handle = $handle;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Does nothing.
   */
  public function start()
  {
    fwrite($this->handle, '<table class="var-dump">');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Does nothing.
   */
  public function stop()
  {
    fwrite($this->handle, '</table>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeArrayClose($id, $name)
  {
    if ($name!==null)
    {
      fwrite($this->handle, '</table>');
      fwrite($this->handle, '</td>');
      fwrite($this->handle, '</tr>');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeArrayOpen($id, $name)
  {
    if ($name!==null)
    {
      fwrite($this->handle, '<tr>');
      $this->writeName($name, $id);
      fwrite($this->handle, '<td>');
      fwrite($this->handle, Html::generateElement('div', ['class' => 'array'], 'array').'<br/>');
      fwrite($this->handle, '<table>');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeArrayReference($ref, $name)
  {
    $html = Html::generateElement('span', ['class' => 'array'], 'array');
    $html .= ', ';
    $html .= Html::generateElement('a', ['href' => '#'.$ref], 'see '.$ref);

    fwrite($this->handle, '<tr>');
    $this->writeName($name);
    fwrite($this->handle, Html::generateElement('td', [], $html, true));
    fwrite($this->handle, '</tr>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeBool($id, $ref, &$value, $name)
  {
    $this->writeScalar($id, $ref, $name, ($value) ? 'true' : 'false', 'keyword');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeFloat($id, $ref, &$value, $name)
  {
    $this->writeScalar($id, $ref, $name, $value, 'number');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeInt($id, $ref, &$value, $name)
  {
    $this->writeScalar($id, $ref, $name, $value, 'number');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeNull($id, $ref, $name)
  {
    $this->writeScalar($id, $ref, $name, 'null', 'keyword');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeObjectClose($id, $name, $class)
  {
    if ($name!==null)
    {
      fwrite($this->handle, '</table>');
      fwrite($this->handle, '</td>');
      fwrite($this->handle, '</tr>');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeObjectOpen($id, $name, $class)
  {
    if ($name!==null)
    {
      fwrite($this->handle, '<tr>');
      $this->writeName($name, $id);
      fwrite($this->handle, '<td>');
      fwrite($this->handle, Html::generateElement('div', ['class' => 'class'], $class).'<br/>');
      fwrite($this->handle, '<table>');
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeObjectReference($ref, $name, $class)
  {
    $html = Html::generateElement('span', ['class' => 'class'], $class);
    $html .= ', ';
    $html .= Html::generateElement('a', ['href' => '#'.$ref], 'see '.$ref);

    fwrite($this->handle, '<tr>');
    $this->writeName($name);
    fwrite($this->handle, Html::generateElement('td', [], $html, true));
    fwrite($this->handle, '</tr>');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeResource($id, $ref, $name, $type)
  {
    $this->writeScalar($id, $ref, $name, $type, 'keyword');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function writeString($id, $ref, &$value, $name)
  {
    $text  = mb_strimwidth($value, 0, 80, '...');
    $title = ($text!=$value) ? mb_strimwidth($value, 0, 512, '...') : null;

    $this->writeScalar($id, $ref, $name, $text, 'string', $title);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes the name of a variable.
   *
   * @param string   $name The name of the variable.
   * @param int|null $id   The ID of the value.
   */
  private function writeName($name, $id = null)
  {
    $title = null;
    $class = null;

    if (is_int($name))
    {
      $text  = $name;
      $class = 'number';
    }
    else
    {
      $class = 'string';
      $text  = mb_strimwidth($name, 0, 20, '...');
      if ($text!=$name)
      {
        $title = mb_strimwidth($name, 0, 512, '...');
      }
    }

    fwrite($this->handle, Html::generateElement('th', ['class' => 'id'], $id));

    fwrite($this->handle, Html::generateElement('th',
                                                ['class' => $class,
                                                 'id'    => $id,
                                                 'title' => $title],
                                                $text));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Dumps a scalar value.
   *
   * @param int|null    $id    The ID of the value.
   * @param int|null    $ref   The ID of the value if the variable is a reference to a value that has been dumped
   *                           already.
   * @param string      $name  The name of the variable.
   * @param string      $text  The text for displaying the value.
   * @param string      $class The class of the value.
   * @param string|null $title The title for the value.
   */
  private function writeScalar($id, $ref, $name, $text, $class, $title = null)
  {
    $html = Html::generateElement('span', ['class' => $class, 'title' => $title], $text);
    if ($ref!==null)
    {
      $html .= ', ';
      $html .= Html::generateElement('a', ['href' => '#'.$ref], 'see '.$ref);
    }

    fwrite($this->handle, '<tr>');
    $this->writeName($name, $id);
    fwrite($this->handle, Html::generateElement('td', [], $html, true));
    fwrite($this->handle, '</tr>');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
