<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\form;

use Pyshnov\form\Element\Checkbox;
use Pyshnov\form\Element\Html;
use Pyshnov\form\Element\Input;
use Pyshnov\form\Element\Password;
use Pyshnov\form\Element\Select;
use Pyshnov\form\Element\Textarea;
use Symfony\Component\HttpFoundation\Request;

class Form
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

    protected $errors;

    protected $data;

    /**
     * @var array
     */
    protected $elements;

    public function __construct(Request $request = null, $name = null, $method = 'GET')
    {
        $this->request = $request ?? \Pyshnov::request();
        $this->attributes['name'] = $name;
        $this->attributes['method'] = strtoupper($method);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->attributes['name'];
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->attributes['name'] = $name;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        if (isset($this->attributes['class'])) {
            $this->attributes['class'] = $this->attributes['class'] . ' ' . $class;
        } else {
            $this->attributes['class'] = $class;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->attributes['method'];
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->attributes['method'] = $method;
    }

    public function isMethod($method)
    {
        return $this->attributes['method'] === strtoupper($method);
    }

    /**
     * Проверит есть ли ошибки
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * Добавление ошибки
     *
     * @param $error
     * @return $this
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Вернет массив ысех ошибок, иначе false
     * если передать $join, массив будет объединен в строку функцией implode()
     *
     * @param string $join
     * @return bool|string|array
     */
    public function getErrors($join = null)
    {
        if ($this->isValid()) {
            return false;
        }

        return $join ? implode($join, $this->errors) : $this->errors;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param ElementInterface $element
     */
    public function addElement(ElementInterface $element)
    {
        $this->elements[$element->getName()] = $element;
    }

    public function getElement($name)
    {
        if ($this->elements[$name]) {
            return $this->elements[$name];
        }

        return false;
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements ?? [];
    }

    /**
     * <div class="form-group">
     * <label for="exampleInputEmail1">Email address</label>
     * <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
     * </div>
     *
     * @param      $name
     * @param null $label
     * @param bool $group - если true, будет выполнена обертка в div с классом .form-control
     * @return Input
     */
    public function addInput($name, $label = null, $group = false)
    {
        $input = new Input();

        $input->setName($name);
        $input->setLabel($label);
        $input->setIsGroup($group);

        $this->addElement($input);

        return $input;
    }

    /**
     * @param      $name
     * @param null $label
     * @param bool $group
     * @return Password
     */
    public function addPassword($name, $label = null, $group = false)
    {
        $input = new Password();

        $input->setName($name);
        $input->setLabel($label);
        $input->setIsGroup($group);

        $this->addElement($input);

        return $input;
    }

    /**
     * @param      $name
     * @param null $label
     * @param bool $group
     * @return Textarea
     */
    public function addTextarea($name, $label = null, $group = false)
    {
        $el = new Textarea();

        $el->setName($name);
        $el->setLabel($label);
        $el->setIsGroup($group);
        $el->setSolitary(false);

        $this->addElement($el);

        return $el;
    }

    /**
     * @param      $name
     * @param null $label
     * @param bool $group
     * @param null $options
     * @return Select
     */
    public function addSelect($name, $label = null, $group = false, $options = null)
    {
        $el = new Select();

        $el->setName($name);
        $el->setLabel($label);
        $el->setIsGroup($group);
        $el->setSolitary(false);

        if (null !== $options) {
            $el->setOptions($options);
        }

        $this->addElement($el);

        return $el;
    }

    public function addCheckbox($name, $label = null, $group = false, $options = null)
    {
        $el = new Checkbox();

        $el->setName($name);
        $el->setLabel($label);
        $el->setIsGroup($group);

        if (null !== $options) {
            //$el->setOptions($options);
        }

        $this->addElement($el);

        return $el;
    }


    public function render()
    {
        $html = '<form ' . Html::renderAttributes($this->getAttributes()) . '>';

        foreach ($this->getElements() as $element) {
            $html .= $element->render();
        }

        $html .= '<button class="btn btn-primary" name="enter" type="sumbit">Отправить</button>';

        $html .= '</form>';

        return $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    public function reset($reset_defaults = false)
    {
        $this->errors = null;
        $this->data = [];
        foreach ($this->getElements() as $el) {
            $el->reset($reset_defaults);
        }

        return $this;
    }

    public function validate()
    {
        $this->reset();

        //$data = $this->postParams();

        foreach ($this->getElements() as $name => $element) {
            $val = $this->getRequest()->get($name);

            $element->validate($val);

            $this->data[$name] = $val;

            if ($err = $element->getErrors()) {
                foreach ($err as $str) {
                    $this->addError($str);
                }
            }
        }

        return $this->isValid();

    }
}