<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Plugin\Breadcrumb;


class Breadcrumb implements BreadcrumbInterface
{
    protected $separator = '';

    protected $breadcrumbs = [];

    protected $activeLastLink = false;

    /**
     * {@inheritdoc}
     */
    public function addLink($label, $link = '')
    {
        $label = trim($label);
        $link = trim($link);

        if ($label) {
            $this->breadcrumbs[] = [
                'label' => $label,
                'link' => $link
            ];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLinks(array $links)
    {
        foreach ($links as $label => $link) {
            if (is_int($label)) {
                $label = $link;
                $link = '';
            }
            $this->addLink($label, $link);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks(): array
    {
        return $this->breadcrumbs;
    }

    /**
     * {@inheritdoc}
     */
    public function activeLastLink($active = true)
    {
        $this->activeLastLink = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $breadcrumbs = $this->breadcrumbs;
        if (empty($breadcrumbs))
            return '';

        $html = '<ul class="breadcrumb">';

        $last = count($breadcrumbs) - 1;

        $last_active = $this->activeLastLink;

        foreach ($breadcrumbs as $key => $value) {
            if ($key != $last) {
                $html .= '<li><a href="' . $value['link'] . '">' . $value['label'] . '</a></li>' . $this->separator;
            } else {
                if ($last_active) {
                    $html .= '<li><a href="' . $value['link'] . '">' . $value['label'] . '</a></li>';
                } else {
                    $html .= '<li  class="active"><strong>' . $value['label'] . '</strong></li>';
                }
            }
        }

        $html .= '</ul>';

        return $html;
    }

    public function reset()
    {
        $this->breadcrumbs = [];

        return $this;
    }
}