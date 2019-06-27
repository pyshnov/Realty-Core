<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\form;


interface ElementInterface
{
    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return null|string
     */
    public function getValue();

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return null|string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return null|string
     */
    public function getClass();

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class);

    /**
     * @return null|string
     */
    public function getId();

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttribute(array $attributes);

    /**
     * @param        $name
     * @param string $default
     * @return string|null
     */
    public function getAttribute($name, string $default = null);

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addAttribute($name, $value);

    /**
     * @return string
     */
    public function getTag();

    /**
     * @return string
     */
    public function getGroupClass();

    /**
     * @param string $groupClass
     * @return $this
     */
    public function setGroupClass(string $groupClass);

    /**
     * @return null|string
     */
    public function getContent();

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @param $class
     * @return $this
     */
    public function setLabelClass($class);

    /**
     * @param $for
     * @return $this
     */
    public function setLabelFor($for);

    /**
     * @return array
     */
    public function getLabelAttributes();

}