<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\blog\Model;


use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Helpers;
use Pyshnov\Core\Image\Image;
use Pyshnov\system\Model\BaseModel;
use Pyshnov\system\Plugin\Pagination\Pagination;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Route;

class BlogModel extends BaseModel
{
    /**
     * Возвращает параматры всех статей
     * используется для админки
     *
     * @param bool $pager
     * @return array
     */
    public function getAllArticles($pager = true)
    {
        $per_page = $this->config()->get('blog.per_page');
        $start = 0;
        $pagination = '';

        if ($pager) {
            $params = $this->getParam()->all();

            $total_records = DB::select('id', DB_PREFIX . '_blog')->execute()->rowCount();

            if ($total_records > $per_page) {

                $page = (int)$params['page'];

                $pagination = new Pagination($total_records, $per_page, $page);

                $pagination->setQueryParams($params);

                $start = ($page - 1) * $per_page;
            }
        }

        $stmt = DB::select('
                b.id, 
                b.active,
                b.alias, 
                b.name, 
                b.views, 
                c.name AS category_name, 
                c.alias AS category_alias', DB_PREFIX . '_blog b
            ')->leftJoin(DB_PREFIX . '_blog_category c', 'b.category_id', '=', 'c.id')
            ->limit($per_page, $start)->execute();

        if ($rows = $stmt->fetchAll()) {
            return [
                'rows' => $rows,
                'pager' => $pagination
            ];
        }

        return [
            'rows' => false,
            'pager' => ''
        ];
    }

    /**
     * Возврвщает параметры всех категорий
     *
     * @return bool
     */
    public function getCategoryAll()
    {
        $stmt = DB::select('id, name, alias', DB_PREFIX . '_blog_category')->orderBy('name')->execute();

        if ($rows = $stmt->fetchAll()) {
            return $rows;
        }

        return false;
    }

    /**
     * Создает новую статью
     *
     * @return bool
     */
    public function articleAdd()
    {
        $data = $this->prepareData();
        $data['active'] = 1;

        if (!$this->error()->has()) {

            if ($image = $this->request()->files->get('image', false)) {
                $data['image'] = $this->uploadPhoto($image);
            }

            $stmt = DB::insert($data, DB_PREFIX . '_blog')->execute();

            if ($stmt) {
                $route = $this->route($data['alias'], 'article-' . $stmt, $this->config()->get('blog.html_postfix') ? '.html' : '/', 'articleView');
                DB::insert($route, DB_PREFIX . '_router')
                    ->execute();
                $this->clearRouterCache();

                return true;
            }
        }

        return false;
    }

    /**
     * Сохраняет параметры статьи после редактирования
     *
     * @return bool
     */
    public function articleEdit()
    {
        if (!$id = $this->request()->attributes->get('id'))
            return false;

        $data = $this->prepareData();

        if (!$this->error()->has()) {

            if ($image = $this->request()->files->get('image', false)) {
                $data['image'] = $this->uploadPhoto($image);

                if ($current_photo = $this->request()->request->get('current_photo')) {
                    $path = \Pyshnov::root() . '/uploads/blog/';

                    @unlink($path . $current_photo);
                }
            }

            $stmt = DB::update($data, DB_PREFIX . '_blog')
                ->where('id', '=', $id)->execute();

            if ($stmt) {
                $route = $this->route($data['alias'], 'article-' . $id, $this->config()->get('blog.html_prefix') ? '.html' : '/', 'articleView');
                DB::update($route, DB_PREFIX . '_router')
                    ->where('name', '=', 'blog.article' . $id)
                    ->execute();
                $this->clearRouterCache();
            }

            return true;
        }

        return false;
    }

    protected function prepareData()
    {
        $params = $this->request()->request->all();

        if (!$params['name']) {
            $this->error()->add($this->t('blog.error_required_name'));
        }

        $data = [
            'category_id' => (int)$params['category_id'],
            'name' => $params['name'],
            'alias' => $params['alias'] ?: Helpers::translit($params['name']),
            'meta_title' => $params['meta_title'],
            'meta_keywords' => $params['keywords'],
            'meta_description' => $params['description'],
            'anons' => $params['anons'],
            'body' => $params['body'],
            'date' => $params['date'] ?: date('Y-m-d H:i:s')
        ];

        return $data;
    }

    /**
     * Загрузит фото
     *
     * @param $image
     * @return string
     */
    public function uploadPhoto(UploadedFile $image)
    {
        if ($image->isValid()) {
            $ext = strtolower(pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION)); // Получим тип файла

            if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                $image = new Image($image);
                $preview_height = $this->config()->get('blog.image_preview_height');
                $preview_width = $this->config()->get('blog.image_preview_width');

                $path = \Pyshnov::root() . '/uploads/blog/';

                $name = time() . rand(10, 1000) . '.' . $ext;

                $image->resize($preview_width, $preview_height)
                    ->save($path . $name);

                return $name;
            }
        }

        return '';
    }

    /**
     * Сохраняет или обновляет параметры записи.
     *
     * @param null $category
     * @return bool
     */
    public function saveCategory($category = null)
    {
        $params = $this->request()->request->all();

        if (!$params['name'])
            $this->error()->add($this->t('blog.error_required_name'));

        if (!$this->error()->has()) {
            $data = [
                'name' => $params['name'],
                'alias' => $params['alias'] ?: Helpers::translit($params['name']),
                'meta_title' => $params['meta_title'] ?: '',
                'meta_keywords' => $params['keywords'] ?: '',
                'meta_description' => $params['description'] ?: '',
            ];

            if (null !== $category) {
                $stmt = DB::update($data, DB_PREFIX . '_blog_category')
                    ->where('id', '=', $this->request()->attributes->get('id'))
                    ->execute();
                if ($stmt) {
                    if ($category['alias'] != $data['alias']) {
                        $route = $this->route($data['alias']);
                        DB::update($route, DB_PREFIX . '_router')
                            ->where('name', '=', 'blog.category' . $category['id'])
                            ->execute();
                        $this->clearRouterCache();
                    }
                }

                return true;
            } else {
                $stmt = DB::insert($data, DB_PREFIX . '_blog_category')->execute();
                if ($stmt) {
                    DB::insert($this->route($data['alias'], 'category' . $stmt), DB_PREFIX . '_router')->execute();
                    $this->clearRouterCache();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Возвращает параметры категории из бд по ее id
     *
     * @param null $id
     * @return bool
     */
    public function getCategoryById($id = null)
    {
        $id = $id ?? $this->request()->attributes->get('id');

        if (!$id)
            return false;

        $stmt = DB::select('*', DB_PREFIX . '_blog_category')
            ->where('id', '=', $id)
            ->limit(1)->execute();

        if ($rows = $stmt->fetch())
            return $rows;

        return false;
    }

    /**
     * Возвращает парамтры статьи с определенным id
     *
     * @param null $id
     * @param bool $view_count
     * @return bool
     */
    public function getArticle($id = null, $view_count = false)
    {
        $id = $id ?? $this->request()->attributes->get('id');

        if (!$id)
            return false;

        $stmt = DB::select('
                b.*, 
                c.name AS category_name, 
                c.alias AS category_alias', DB_PREFIX . '_blog b
            ')->leftJoin(DB_PREFIX . '_blog_category c', 'b.category_id', '=', 'c.id')
            ->where('b.id', '=', $id)
            ->limit(1)->execute();

        if ($row = $stmt->fetch()) {
            if ($view_count) {
                DB::update(['views' => $row['views'] + 1], DB_PREFIX . '_blog')
                    ->where('id', '=', $id)
                    ->execute();
            }

            return $row;
        }

        return false;

    }

    private function route($alias, $name = null, $format = '/', $action = 'blog')
    {
        $route = new Route('/blog/' . $alias . $format, [
            '_controller' => '\Pyshnov\blog\Controller\BlogController::' . $action
        ]);

        $res = [
            'route' => serialize($route)
        ];

        if ($name !== null) {
            $res['name'] = 'blog.' . $name;
        }

        return $res;
    }

    public function clearRouterCache()
    {
        $cache = New FileCache();
        $cache->remove('router');
    }

    /**
     * @param null|int $category_id
     * @param bool     $pager
     * @return array
     */
    public function getArticles($category_id = null, $pager = true)
    {
        $per_page = $this->config()->get('blog.per_page');
        $start = 0;
        $pagination = '';

        if ($pager) {
            $params = $this->getParam()->all();

            $query = DB::select('id', DB_PREFIX . '_blog');

            if ($category_id !== null) {
                $query->where('category_id', '=', $category_id);
            }

            $total_records = $query->execute()->rowCount();

            if ($total_records > $per_page) {

                $page = (int)$params['page'];

                $pagination = new Pagination($total_records, $per_page, $page);

                $pagination->setQueryParams($params);

                $start = ($page - 1) * $per_page;
            }
        }

        $query = DB::select('
                b.id, b.alias, b.name, b.anons, b.image, date, b.views, 
                c.name AS category_name, 
                c.alias AS category_alias', DB_PREFIX . '_blog b
            ')->leftJoin(DB_PREFIX . '_blog_category c', 'b.category_id', '=', 'c.id');

        if ($category_id !== null) {
            $query->where('category_id', '=', $category_id);
        }

        $stmt = $query->limit($per_page, $start)->orderBy('date', 'DESC')->execute();

        if ($rows = $stmt->fetchAll()) {
            $res = [];
            foreach ($rows as $row) {
                $row['url'] = '/blog/' . $row['alias'] . ($this->config()->get('blog.html_prefix') ? '.html' : '/');
                $res[] = $row;
            }

            return [
                'rows' => $res,
                'pager' => $pagination
            ];
        }

        return [
            'rows' => false,
            'pager' => ''
        ];
    }
}