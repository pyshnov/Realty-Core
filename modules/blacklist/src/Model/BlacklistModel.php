<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\blacklist\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\system\Model\BaseModel;
use Pyshnov\system\Plugin\Pagination\Pagination;

class BlacklistModel extends BaseModel
{
    public function getBlacklist()
    {

        $query = DB::select('id, phone, info', DB_PREFIX . '_blacklist');

        if ($this->getParam()->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $this->getParam()->get('phone'));
            $query->whereLike('phone', '%' . $phone . '%');
        }

        $count = $query->execute()->rowCount();

        $pagination = '';

        $limit = 20;

        if ($count > $limit) {

            $params = $this->request()->query->all();

            $page = $params['page'] ?? 1;

            if ($page > ceil($count / $limit)) {
                $page = 1;
            }
            $start = ($page - 1) * $limit;

            $query->limit($limit, $start);

            // Получем постраничную навигацию
            $pagination = new Pagination($count, $limit, $page);
            $pagination->setLink($this->request()->getPathInfo())
                ->setQueryParams($params)
                ->setMaxItem(6);
        }

        $rows = $query->orderBy('id', 'DESC')->execute()->fetchAll();

        return [
            'rows' => $rows,
            'pager' => $pagination
        ];
    }

    public function getDouble()
    {
        $res = [];

        $pagination = '';

        $stmt = DB::select('id, phone', DB_PREFIX . '_data')
            ->count('phone', 'count')
            ->groupBy('phone')
            ->havingCount('phone', '>', 1)
            ->execute();

        if (!empty($rows = $stmt->fetchAll())) {

            $count = count($rows);
            $limit = 20;

            if ($count > $limit) {

                $params = $this->request()->query->all();

                $page = $params['page'] ?? 1;

                if ($page > ceil($count / $limit)) {
                    $page = 1;
                }
                $start = ($page - 1) * $limit;

                $rows = DB::select('id, phone', DB_PREFIX . '_data')
                    ->count('phone', 'count')
                    ->groupBy('phone')
                    ->havingCount('phone', '>', 1)
                    ->limit($limit, $start)
                    ->execute();

                $pagination = new Pagination($count, $limit, $page);
                $pagination->setLink($this->request()->getPathInfo())
                    ->setQueryParams($params)
                    ->setMaxItem(6);
            }

            $values = [];

            foreach ($rows as $item) {
                $values[] = $item['phone'];
            }

            $rows = DB::select('id, phone', DB_PREFIX . '_data')
                ->whereIn('phone', $values)
                ->orderBy('id', 'DESC')
                ->execute()
                ->fetchAll();

            $arr = [];

            foreach ($rows as $item) {
                $arr[$item['phone']][] = '<a href="/admin/data/edit-' . $item['id'] . '/">' . $item['id'] . '</a>';
            }

            foreach ($arr as $key => $value) {
                $res[$key] = implode(' | ', $value);
            }
        }

        return [
            'rows' => $res,
            'pager' => $pagination
        ];
    }

    public function getAgency()
    {

        $res = [];

        $pagination = '';

        $rows = DB::select('a.id, b.phone',  DB_PREFIX . '_data a')
            ->leftJoin(DB_PREFIX . '_blacklist b', 'a.phone', '=', 'b.phone')
            ->having('phone', '!=', 'null')
            ->orderBy('id', 'DESC')
            ->execute()->fetchAll();

        if (!empty($rows)) {
            $arr = [];

            foreach ($rows as $item) {
                $arr[$item['phone']][] = $item['id'];
            }

            foreach ($arr as $phone => $value) {
                $test = [];
                foreach ($value as $id) {
                    $test[$phone][] = '<a href="/admin/data/edit-' . $id. '/">' . $id . '</a>';
                }

                $res[$phone] = implode(' | ', $test[$phone]);
            }
        }

        return [
            'rows' => $res,
            'pager' => $pagination
        ];
    }

}