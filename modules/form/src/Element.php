<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\form;


use Pyshnov\form\Element\Html;
use Pyshnov\form\Helpers\Filter;

abstract class Element extends Html implements ElementInterface
{

    protected $label;

    protected $errors;

    protected $is_required;

    protected $multipleChoice;

    protected $value;

    protected $filters;

    protected $tpl_err_required = 'Обязательное поле "%s" не заполнено';

    protected $tpl_err_wrong = 'Поле "%s" заполнено некорректно';

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label = null)
    {
        $this->label = $label ?? '';

        return $this;
    }

    /**
     * Разрешить множественный выбор значений
     *
     * @param bool $allow
     * @return $this
     */
    public function setMultipleChoice($allow = true)
    {
        $this->multipleChoice = $allow;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipleChoice()
    {
        return (bool)$this->multipleChoice;
    }

    public function render()
    {
        if (null !== $this->getType()) {
            $this->addAttribute('type', $this->getType());
        }

        $html = $this->renderLabel($this->getLabel());

        $html .= $this->tag();

        return $this->isGroup() ? $this->renderGroup($html, $this->getGroupClass()) : $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /****************************************
     * Валидация
     ***************************************/

    /**
     * Вернет сообщение ошибки для обязательного элемента
     *
     * @return string
     */
    public function getTplErrRequired(): string
    {
        return $this->tpl_err_required;
    }

    /**
     * @param string $tpl_err_required
     */
    public function setTplErrRequired(string $tpl_err_required)
    {
        $this->tpl_err_required = $tpl_err_required;
    }

    /**
     * Вернет сообщение об ошибке если элемент заполнен не корретно
     *
     * @return string
     */
    public function getTplErrWrong(): string
    {
        return $this->tpl_err_wrong;
    }

    /**
     * @param string $tpl_err_wrong
     */
    public function setTplErrWrong(string $tpl_err_wrong)
    {
        $this->tpl_err_wrong = $tpl_err_wrong;
    }


    /**
     * Добавить ошибку
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
     * Является ли значение валидным
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return (bool)$this->is_required;
    }

    /**
     * Является ли поле обязательным
     *
     * @param bool $required
     * @return $this
     */
    public function setIsRequired($required = true)
    {
        $this->is_required = $required;

        return $this;
    }

    /**
     * @param $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters ?? [];
    }

    public function reset()
    {
        $this->value = null;
        $this->errors = null;
    }

    /**
     * Если элемент является обязательным, проверит не пустое ли оно
     *
     * @return bool
     */
    protected function validateRequired()
    {
        if ($this->isRequired() && empty($this->value)) {
            $this->addError(sprintf($this->getTplErrRequired(), $this->getLabel() ?? $this->getName()));

            return false;
        }

        return true;
    }

    /**
     * Проверка по списку фильтров
     *
     * @return bool
     */
    protected function validateFilters()
    {
        if (!empty($this->value) && !empty($this->filters)) {
            foreach ($this->filters as $filter) {
                if (is_array($this->value) && $this->isMultipleChoice()) {
                    $this->value = Filter::Arr($this->value, $filter, null);
                    $bad = empty($this->value);
                } else {
                    $bad = is_null(Filter::Value($this->value, $filter, null));
                }
                if ($bad) {
                    $this->addError(sprintf($this->getTplErrWrong(), $this->getLabel() ?? $this->getName()));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        $this->reset();

        $this->value = $value;

        $this->validateRequired();
        $this->validateFilters();

        return $this->isValid();
    }
}