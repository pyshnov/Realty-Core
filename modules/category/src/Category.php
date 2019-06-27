<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\category;


use Pyshnov\Core\DB\DB;
use Pyshnov\form\Element\Select;
use Pyshnov\form\Form;

class Category
{

    protected $category;
    protected $aliases;
    protected $children;
    protected $links;

    public function __construct()
    {
        $load_active = \Pyshnov::config()->get('active_topic');

        $stmt = DB::select('*', DB_PREFIX . '_topic')->orderBy('`order`');
        if ($load_active)
            $stmt->where('active', '=', 1);
        $rows = $stmt->execute()->fetchAll();

        $category = [];

        foreach ($rows as $row) {
            $category[$row['id']] = $row;
        }

        if ($load_active) {
            // Выкидываем из массива элементы принадлежащие к неактивным категориям
            foreach ($category as $item) {
                if (!isset($category[$item['parent_id']]) && $item['parent_id'] != 0) {
                    unset($category[$item['id']]);
                }
            }
        }

        foreach ($category as $item) {
            $test[$item['parent_id']][] = $item;
            $this->children[$item['parent_id']][] = $item['id'];
        }

        $this->prepareUrl($test);

        foreach ($category as $k => $v) {
            $this->aliases[$k] = $v['url'];
            $link = '/' . trim($this->links[$v['id']], '/') . '/';
            $this->links[$k] = $link;
            $category[$k]['url'] = $link;
        }

        //var_dump($this->links);

        $this->category = $category;

    }

    public function prepareUrl($cats, $parent_id = 0)
    {
        if (is_array($cats) && isset($cats[$parent_id])) {
            foreach ($cats[$parent_id] as $cat) {
                $p = isset($this->links[$cat['parent_id']]) ? $this->links[$cat['parent_id']] . '/' : '';
                $this->links[$cat['id']] = $p . $cat['url'];
                 $this->prepareUrl($cats, $cat['id']);
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Вернет массив категорий
     * Результат будет зависить от настроек программы
     * если активирован параметр 'active_topic'
     * будет возвращены только активные категории
     *
     * @param null|int $id
     *
     * @return array|bool
     */
    public function getCategory($id = null)
    {
        if (!is_null($id)) {
            return $this->category[$id] ?? false;
        }

        return $this->category;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasChildren($id)
    {
        return isset($this->children[$id]);
    }

    /**
     * Вернет массив всех категорий
     *
     * @return array
     */
    public function getDbCategoryAll()
    {
        $stmt = DB::select(['id', 'name', 'parent_id'], DB_PREFIX . '_topic')->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param int          $selected
     * @param array        $parent_id
     * @param array|string $attr
     * @param null         $zero_name
     *
     * @return Select
     */
    public function getTopicSelect($selected = 0, $parent_id = 0, $attr = [], $zero_name = null)
    {
        if (null === $zero_name) {
            $zero_name = \Pyshnov::t('system.topic_zero_select');
        }

        if (is_string($attr)) {
            $attr = (array)$attr;
        }

        $options = [];

        if (!is_array($parent_id)) {
            $parent_id = (array)$parent_id;
        }

        $tree = $this->getChildren();

        foreach ($parent_id as $item) {
            $options = $options + $this->level($tree, $item);
        }

        $form = new Form();

        $el = $form->addSelect('topic_id')
            ->setId('topicId')
            ->setAttribute($attr)
            ->setOptions($options)
            ->addAttribute('title', $zero_name)
            ->setValue($selected);

        return $el;
    }

    /**
     * @param     $tree
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public function level($tree, $parent_id = 0, $level = 0)
    {
        $catalog = $this->category;

        $option = [];
        if (isset($tree[$parent_id])) {
            $lev = '';
            for ($i = 0; $i < $level; $i++)
                $lev .= '&nbsp;&nbsp; ';
            foreach ($tree[$parent_id] as $id) {
                $test = $catalog[$id];
                $option[$test['id']] = $lev . $test['name'];
                foreach ($this->level($tree, $test['id'], $level + 1) as $key => $value)
                    $option[$key] = $value;
            }
        } else
            return [];

        return $option;
    }

    /**
     * Вернет всех детей принадлежащих указанной категории
     *
     * @param $topic_id
     *
     * @return array
     */
    public function allChildrenCategories($topic_id)
    {
        $children = $this->getChildren();

        $child = [];

        if (isset($children[$topic_id])) {

            foreach ($children[$topic_id] as $id) {

                $child[] = $id;

                foreach ($this->allChildrenCategories($id) as $value)
                    $child[] = $value;;
            }
        } else {
            return [];
        }

        return $child;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases ?? [];
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links ?? [];
    }

}