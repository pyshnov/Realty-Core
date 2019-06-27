<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\reviews\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\system\Model\BaseModel;

class ReviewsModel extends BaseModel
{

    /**
     * Вернет все одобренные отзывы
     * используется для вывода в польльзовательской части
     *
     * @return array|bool
     */
    public function getReviews()
    {
        $rows = DB::select('*', DB_PREFIX . '_reviews')
            ->where('approved', '=', 1)
            ->orderBy('date', 'DESC')->execute()->fetchAll();

        // TODO Нужно реализовать постраничную навигацию

        if ($rows)
            return $rows;

        return false;
    }

    /**
     * Вернет все отзывы из БД
     *
     * @return array|bool
     */
    public function getReviewsAll()
    {
        $rows = DB::select('*', DB_PREFIX . '_reviews')
            ->orderBy('date', 'DESC')->execute()->fetchAll();

        // TODO Нужно реализовать постраничную навигацию

        if ($rows) {
            return [
                'rows' => $rows
            ];
        }

        return false;

    }

    /**
     * Вернет отзыв из БД по его id
     *
     * @param null|int $id
     * @return bool
     */
    public function getReviewById($id = null)
    {
        if (null === $id)
            $id = $this->request()->attributes->get('id');

        if (!$id)
            return false;

        $query = DB::select('*', DB_PREFIX . '_reviews')
            ->where('id', '=', $id)
            ->limit(1)
            ->execute()->fetch();

        if ($query) {
            return $query;
        }

        return false;
    }

    /**
     * Обновит отзыв в БД
     *
     * @param null|int $id
     * @return bool
     */
    public function update($id = null)
    {
        if (null === $id)
            $id = $this->request()->attributes->get('id');

        if (!$id)
            return false;

        $request = $this->request()->request;

        $approved = $request->has('approved') && $this->get('user')->isAdmin() ? $request->get('approved') : 0;

        $data = [
            'approved' => $approved,
            'author' => $request->get('author') ?? '',
            'author_email' => $request->get('author_email') ?? '',
            'review' => $request->get('review') ?? '',
            'date' => $request->get('date') ?? ''
        ];

        $stmt = DB::update($data, DB_PREFIX . '_reviews')->where('id', '=', $id)->execute();

        if ($stmt)
            return true;

        return false;
    }
}