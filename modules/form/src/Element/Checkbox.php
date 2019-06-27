<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\form\Element;


use Pyshnov\form\Element;

class Checkbox extends Element
{
    protected $tag = 'input';

    protected $type = 'checkbox';

    protected $isChecked;

    protected $labelSpan;

    /**
     * @param $checked
     */
    public function setIsChecked($checked = true)
    {
        $this->isChecked = $checked;

        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return (bool)$this->isChecked;
    }

    public function setLabelSpan($str)
    {
        $this->labelSpan = $str;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelSpan()
    {
        return $this->labelSpan ?? '';
    }

    public function render()
    {

        $this->addAttribute('type', $this->getType());

        if ($this->isChecked()) {
            $this->addAttribute('checked', 'checked');
        }

        $html = '<' . $this->getTag() . static::renderAttributes($this->getAttributes()) . ' />';

        $html .= '<label' . Html::renderAttributes($this->getLabelAttributes()) . '>';

        $html .= $this->getLabelSpan() . $this->getLabel() . '</label>';

        return $this->isGroup() ? $this->renderGroup($html, $this->getGroupClass()) : $html;
    }
}