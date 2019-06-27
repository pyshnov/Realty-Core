<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\form\Element;


class Html
{

    protected $attributes;

    protected $tag;

    protected $content;

    protected $type;

    protected $solitary = true;

    protected $group;

    protected $groupClass = 'form-group';

    protected $labelAttributes;

    public static function renderAttributes(array $attributes = [])
    {
        if (!$attributes) {
            return false;
        }

        $str = '';
        foreach ($attributes as $key => $val) {
            if (is_string($val)) {
                $val = htmlspecialchars(stripslashes($val), ENT_NOQUOTES);
            }

            if ($key == 'style') {
                $val = static::ProcessStyle($val);
            }

            if (is_int($key)) {
                $str .= ' ' . $val;
            } else {
                $str .= ' ' . $key . '="' . $val . '"';
            }
        }

        return $str;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    protected static function ProcessStyle($style)
    {
        if (is_array($style)) {
            $style_arr = array();
            foreach ($style as $k => $v) {
                if (is_numeric($k)) {
                    $style_arr[] = trim($v, ';') . ';';
                } else {
                    $style_arr[] = $k . ': ' . trim($v, ';') . ';';
                }
            }
            $style = join(' ', $style_arr);
        }

        return $style;
    }

    public function tag()
    {
        $html = '<' . $this->getTag() . static::renderAttributes($this->getAttributes());

        if (!$this->isSolitary()) {
            $html .= '>' . $this->getContent() . '</' . $this->getTag() . '>';
        } else {
            $html .= ' />';
        }

        return $html;
    }

    /**
     * @param $el
     * @param $class
     * @return string
     */
    public function renderGroup($el, $class)
    {
        $html = '
        <div class="' . $class . '">';
        $html .= $el;
        $html .= '
        </div>';

        return $html;
    }

    protected function renderLabel($label)
    {
        if ('' == $label) {
            return $label;
        }

        return '
            <label' . static::renderAttributes($this->getLabelAttributes()) . '>' . $label . '</label>
            ';
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->addAttribute('value', $value);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->addAttribute('name', $name);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        if (null !== $cl = $this->getClass()) {
            $this->addAttribute('class', $cl . ' ' . $class);
        } else {
            $this->addAttribute('class', $class);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->addAttribute('id', $id);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }


    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttribute(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->addAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addAttribute($name, $value = null)
    {
        if (null === $value) {
            $this->attributes[] = $name;
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    /**
     * @param        $name
     * @param string $default
     * @return string|null
     */
    public function getAttribute($name, string $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }


    /**
     * @return bool
     */
    public function isSolitary(): bool
    {
        return $this->solitary;
    }

    /**
     * @param bool $solitary
     *
     * @return $this
     */
    public function setSolitary(bool $solitary)
    {
        $this->solitary = $solitary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGroup(): bool
    {
        return (bool)$this->group;
    }

    /**
     * @param $group
     * @return $this
     */
    public function setIsGroup($group = true)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupClass(): string
    {
        return $this->groupClass;
    }

    /**
     * @param string $groupClass
     * @return $this
     */
    public function setGroupClass(string $groupClass)
    {
        $this->groupClass = $groupClass;

        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setLabelClass($class)
    {
        if (isset($this->labelAttributes['class'])) {
            $this->labelAttributes['class'] = $this->labelAttributes['class'] . ' ' . $class;
        } else {
            $this->labelAttributes['class'] = $class;
        }

        return $this;
    }

    /**
     * @param $for
     * @return $this
     */
    public function setLabelFor($for)
    {
        $this->labelAttributes['for'] = $for;

        return $this;
    }

    /**
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes ?? [];
    }
}