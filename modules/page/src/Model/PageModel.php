<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\page\Model;


use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\system\Model\BaseModel;
use Pyshnov\system\Plugin\Pagination\Pagination;
use Symfony\Component\Routing\Route;

class PageModel extends BaseModel
{

    /**
     * Вернет весь список страниц
     *
     * @return array
     */
    public function getPages()
    {
        $pagination = '';

        $stmt = DB::select('id, title, alias', DB_PREFIX . '_page')->execute();

        $count = $stmt->RowCount();
        $per_page = 20;

        if ($count > $per_page) {
            $params = $this->getParam()->all();
            $page = (int)$params['page'];

            $pagination = new Pagination($count, $per_page, $page);

            $pagination->setQueryParams($params);

            $start = ($page - 1) * $per_page;

            $stmt = DB::select('id, title, alias', DB_PREFIX . '_page')->limit($per_page, $start)->execute();
        }

        $rows = $stmt->fetchAll();

        return [
            'pages' => $rows,
            'pager' => $pagination
        ];
    }

    /**
     * Новая страница
     *
     * @return bool
     */
    public function newPage()
    {
        $request = $this->request()->request->all();

        $data = $this->prepareData($request);

        $data['date'] = date('Y-m-d H:i:s');

        if (!$this->error()->has()) {
            if ($columns = $this->route($data['alias'])) {
                DB::insert($data, DB_PREFIX . '_page')->execute();
                DB::insert($columns, DB_PREFIX . '_router')->execute();

                $this->clearRouterCache();

                return true;
            }
        }

        return false;
    }

    public function pageEdit($page)
    {
        if (!$id = $this->request()->attributes->get('id'))
            return false;

        $request = $this->request()->request->all();

        $data = $this->prepareData($request);

        if (!$this->error()->has()) {
            if ($data['alias'] != $page['alias']) {
                if ($columns = $this->route($data['alias'])) {
                    DB::update($columns, DB_PREFIX . '_router')
                        ->where('name', '=', 'page.' . str_replace("-", "_", $page['alias']))
                        ->execute();

                    $this->clearRouterCache();
                }
            }

            DB::update($data, DB_PREFIX . '_page')->where('id', '=', $id)->execute();

            return true;
        }

        return false;
    }

    protected function prepareData($request)
    {

        if (!$request['title']) {
            $this->error()->add($this->t('page.error_title'));
        }

        $data['title'] = $request['title'];
        $data['meta_title'] = $request['meta_title'] ?: $request['title'];
        $data['meta_keywords'] = $request['keywords'];
        $data['meta_description'] = $request['description'];
        $data['body'] = $request['body'];
        $data['theme'] = $request['theme'];

        if ($request['alias']) {
            if (!preg_match('/^[a-zA-Z0-9\/_-]+$/i', $request['alias'])) {
                $this->error()->add($this->t('page.error_alias'));
            } else {
                $data['alias'] = trim($request['alias'], '/');
            }
        } else {
            $data['alias'] = Helpers::translit($request['title']);
        }

        return $data;
    }

    protected function route($alias)
    {
        $count = DB::select('alias', DB_PREFIX . '_page')
            ->where('alias', '=', $alias)
            ->execute()
            ->RowCount();

        if ($count) {
            $this->error()->add(sprintf($this->t('page.error_duplicate_aliases'), $alias));
            return false;
        }

        $default = [
            '_controller' => '\Pyshnov\page\Controller\PageController::pageView'
        ];
        $route = new Route('/' . trim($alias, '/') . '/', $default);

        return [
            'name' => 'page.' . str_replace("-", "_", $alias),
            'route' => serialize($route)
        ];
    }

    public function clearRouterCache()
    {
        $cache = New FileCache();
        $cache->remove('router');
    }

    public function getPage()
    {
        $stmt = DB::select('*', DB_PREFIX . '_page')
            ->where('alias', '=', trim($this->request()->getRequestUri(), '/'))
            ->execute();

        if ($res = $stmt->fetch()) {
            return $res;
        }

        return false;
    }

    public function getPageById($id = null)
    {
        $id = $id ?? $this->request()->attributes->get('id');

        if (!$id)
            return false;

        $stmt = DB::select('*', DB_PREFIX . '_page')
            ->where('id', '=', $id)
            ->execute();

        if ($res = $stmt->fetch()) {
            return $res;
        }

        return false;
    }


}