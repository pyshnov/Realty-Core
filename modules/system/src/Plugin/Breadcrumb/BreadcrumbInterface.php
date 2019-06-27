<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\system\Plugin\Breadcrumb;


interface BreadcrumbInterface
{
    /**
     * @param string $label
     * @param string $link
     * @return $this
     */
    public function addLink($label, $link = '');

    /**
     * Добавит ссылки
     *  [
     *      'Главная' => '/',
     *      'Аренда' => '/arenda/',
     *      'Комнаты' => ''
     *  ]
     *
     * @param array $links
     * @return $this
     */
    public function setLinks(array $links);

    /**
     * Запишет разделитель
     *
     * @param string $separator
     * @return $this
     */
    public function setSeparator($separator);

    /**
     * @return array
     */
    public function getLinks();

    /**
     * Отобразить последнюю ссылку активно
     * по умолчанию нет
     *
     * @param bool $active
     * @return $this
     */
    public function activeLastLink($active = true);

    public function reset();

    /**
     * @return string
     */
    public function render();
}