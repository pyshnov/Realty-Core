<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Plugin\Pagination;


interface PaginationInterface
{
    /**
     * @param string $class
     * @return $this
     */
    public function setActiveClass($class);

    /**
     * @param string $class
     * @return $this
     */
    public function setElementClass($class);

    /**
     * Номер текущей страницы
     *
     * @return int|null
     */
    public function getCurrentPage();

    /**
     * Номер текущей страницы
     *
     * @param $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage);

    /**
     * Url path
     * Если не передан, будет получен из \Pyshnov::request()->getPathInfo()
     *
     * @param string $link
     * @return $this
     */
    public function setLink(string $link);

    /**
     * @return string
     */
    public function getLink();

    /**
     * GET параметры
     *
     * @param array $otherParams
     * @return $this
     */
    public function setQueryParams(array $otherParams);

    /**
     * @return array
     */
    public function getQueryParams();

    /**
     * Количество отображаемых ссылок
     * по умолчанию 10
     *
     * @param int $maxItem
     * @return $this
     */
    public function setMaxItem(int $maxItem);

    /**
     * @return int
     */
    public function getMaxItem();

    /**
     * Текст кнопки "В начало"
     *
     * @param string $startingName
     */
    public function setButtonFirstName(string $startingName);

    /**
     * @return string
     */
    public function getButtonFirstName();

    /**
     * Текст кнопки "Последняя"
     *
     * @param string $lastName
     */
    public function setButtonLastName(string $lastName);

    /**
     * @return string
     */
    public function getButtonLastName();

    /**
     * Отображать ли кнопку "В начало"
     *
     * @param bool $buttonFirst
     */
    public function setButtonFirst(bool $buttonFirst);

    /**
     * @return bool
     */
    public function isButtonFirst();

    /**
     * Отображать ли кнопку "Последняя"
     *
     * @param bool $buttonLast
     */
    public function setButtonLast(bool $buttonLast);

    /**
     * @return bool
     */
    public function isButtonLast();

    /**
     * Собирет и вернет
     *
     * @return string
     */
    public function render();

    /**
     * Рассчитать общее количество страниц
     *
     * @return int
     */
    public function getTotalPages();
}