<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\system\Plugin\Pagination;


class Pagination
{
    private $currentPage;

    private $totalRecords;
    private $recordsPerPage;

    private $link;
    private $otherParams;

    private $maxItem = 10;

    private $elementClass = 'pagination';
    private $activeClass = 'active';

    private $buttonFirstName = 'В начало';
    private $buttonLastName = 'Последняя';

    private $buttonFirst = true;
    private $buttonLast = true;

    /**
     * Pagination constructor.
     *
     * @param int $totalRecords - Всего обектов
     * @param int $recordsPerPage - Количество на страницу
     * @param int $currentPage - Номер страницы
     */
    public function __construct($totalRecords = 0, $recordsPerPage = 10, $currentPage = null)
    {
        $this->totalRecords = (int)$totalRecords;
        $this->recordsPerPage = (int)$recordsPerPage;

        $this->currentPage = $currentPage;

        $this->otherParams = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setActiveClass($class)
    {
        $this->activeClass = $class;

        return $this;
    }

    public function getActiveClass()
    {
        return $this->activeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setElementClass($class)
    {
        $this->elementClass = $class;

        return $this;
    }

    public function getElementClass()
    {
        return $this->elementClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink(): string
    {
        return $this->link ?? \Pyshnov::request()->getPathInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function setLink(string $link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->otherParams;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParams(array $otherParams)
    {
        $this->otherParams = $otherParams;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxItem(): int
    {
        return $this->maxItem;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxItem(int $maxItem)
    {
        $this->maxItem = $maxItem;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonFirstName(): string
    {
        return $this->buttonFirstName;
    }

    /**
     * {@inheritdoc}
     */
    public function setButtonFirstName(string $startingName)
    {
        $this->buttonFirstName = $startingName;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonLastName(): string
    {
        return $this->buttonLastName;
    }

    /**
     * {@inheritdoc}
     */
    public function setButtonLastName(string $lastName)
    {
        $this->buttonLastName = $lastName;
    }

    /**
     * {@inheritdoc}
     */
    public function isButtonFirst(): bool
    {
        return $this->buttonFirst;
    }

    /**
     * {@inheritdoc}
     */
    public function setButtonFirst(bool $buttonFirst)
    {
        $this->buttonFirst = $buttonFirst;
    }

    /**
     * {@inheritdoc}
     */
    public function isButtonLast(): bool
    {
        return $this->buttonLast;
    }

    /**
     * {@inheritdoc}
     */
    public function setButtonLast(bool $buttonLast)
    {
        $this->buttonLast = $buttonLast;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function render()
    {
        if ($this->totalRecords <= $this->recordsPerPage) {
            return '';
        }

        $other_params = $this->getQueryParams();

        // Если не передан номер текущей страницы,
        // проверяем переданные параметры на наличие номера текущей страницы
        // иначе используем как первую
        if (null === $this->getCurrentPage()) {
            $this->currentPage = $other_params['page'] ?? 1;
        }

        foreach ($other_params as $key => $value) {
            if (!$value) {
                unset($other_params[$key]);
            }
        }

        if (isset($other_params['page'])) {
            unset($other_params['page']);
        }

        $query = false;

        $pairs = [];

        if (count($other_params) > 0) {
            foreach ($other_params as $key => $value) {
                $pairs[] = $key . '=' . $value;
            }

            $query = implode('&', $pairs);
        }

        $query_page = $query ? '&' . $query : '';
        $query = $query ? '?' . $query : '';

        $interval = (int)ceil($this->getMaxItem() / 2);

        $html = '<ul class="' . $this->getElementClass() . '">';

        $link = $this->getLink();

        if ($this->isButtonFirst() && $this->currentPage > ($interval + 1)) {
            $html .= '<li><a href="' . $link . $query . '" title="' . $this->getButtonFirstName() . '">' . $this->getButtonFirstName() . '</a></li>';
        }

        if (($previous = $this->previousPage()) >= 1) {

            if ($previous > 1)
                $previous = $link . '?page=' . $previous . $query_page;
            else
                $previous = $link . $query;

            $html .= '<li><a href="' . $previous . '" title="предыдущая">&larr;</a></li>';
        }

        $lineStart = $this->currentPage - $interval;
        $lineEnd = $this->currentPage + $interval;

        if ($lineStart <= 1) {
            $lineStart = 1;
        }

        if ($lineEnd >= $this->getTotalPages()) {
            $lineEnd = $this->getTotalPages();
        }

        for ($i = $lineStart; $i <= $lineEnd; $i++) {
            if ($this->currentPage == $i) {
                $html .= '<li class="' . $this->getActiveClass() . '"><span> ' . $i . ' </span></li>';
            } else {
                if ($i == 1) {
                    $html .= '<li><a rel="nofollow" href="' . $link . $query . '">' . $i . '</a></li>';
                } else {
                    $html .= '<li><a rel="nofollow" href="' . $link . '?page=' . $i . $query_page . '">' . $i . '</a></li>';
                }
            }
        }

        if ($this->nextPage() <= $this->getTotalPages()) {
            $next = $link . '?page=' . $this->nextPage() . $query_page;

            $html .= '<li><a href="' . $next . '" title="следующая">&rarr;</a></li>';
        }

        if ($this->isButtonLast() && $this->currentPage < ($this->getTotalPages() - $interval)) {
            $html .= '<li><a href="' . $link . '?page=' . $this->getTotalPages() . $query_page . '" title="' . $this->getButtonLastName() . '">' . $this->getButtonLastName() . '</a></li>';
        }

        $html .= '</ul>';

        return $html;

    }

    /**
     * {@inheritdoc}
     */
    public function getTotalPages()
    {
        return ceil($this->totalRecords / $this->recordsPerPage);
    }

    /**
     * Расчет предыдущей странице
     *
     * @return int
     */
    public function previousPage()
    {
        return $this->currentPage - 1;
    }

    /**
     * Расчет следующей страницы
     *
     * @return int
     */
    public function nextPage()
    {
        return $this->currentPage + 1;
    }
}