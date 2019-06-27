<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Template;


abstract class Layout
{
    protected $title;
    protected $metaTitle;
    protected $metaKeywords;
    protected $metaDescription;
    protected $themeType = 'inherited';

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title = '')
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle ?? '';
    }

    /**
     * @param string $meta_title
     */
    public function setMetaTitle(string $meta_title = '')
    {
        $this->metaTitle = $meta_title;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords ?? '';
    }

    /**
     * @param string $meta_keywords
     */
    public function setMetaKeywords(string $meta_keywords = '')
    {
        $this->metaKeywords = $meta_keywords;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription ?? '';
    }

    /**
     * @param string $meta_description
     */
    public function setMetaDescription(string $meta_description = '')
    {
        $this->metaDescription = $meta_description;
    }

    /**
     * @return string
     */
    public function getThemeType(): string
    {
        return $this->themeType;
    }

    /**
     * @param string $theme_type
     */
    public function setThemeType(string $theme_type)
    {
        $this->themeType = $theme_type;
    }
}