<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\blog\Controller;


use Pyshnov\blog\Model\BlogModel;
use Pyshnov\Core\Controller\BaseController;

class BlogController extends BaseController
{
    public function adminBlog()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('blog.name'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('blog.name') => ''
        ]);

        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        if($this->request()->query->has('categories')) {
            $data['categories'] = $model->getCategoryAll();
        } else {
            $data['page'] = $model->getAllArticles();
        }

        return $this->render($data);
    }

    public function adminArticleAdd()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('blog.new_article'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('blog.name') => '/admin/blog/',
            $this->t('blog.new_article') => ''
        ]);

        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        if($this->request()->request->get('do') == 'add') {
            if($model->articleAdd()) {
                header('Location: /admin/blog/');
                exit();
            }
        }
        $data['category'] = $model->getCategoryAll();
        $data['request'] = $this->request()->request->all();

        return $this->render($data);
    }

    public function adminArticleEdit()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('blog.edit_article'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('blog.name') => '/admin/blog/',
            $this->t('blog.edit_article') => ''
        ]);

        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        if($this->request()->request->get('do') == 'edit') {
            if($model->articleEdit()) {
                header('Location: /admin/blog/');
                exit();
            }
        }
        $data['category'] = $model->getCategoryAll();
        $data['article'] = $model->getArticle();
        $data['request'] = $model->request()->request->all();

        return $this->render($data);
    }

    public function adminCategoryAdd()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('blog.category_add'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('blog.name') => '/admin/blog/',
            $this->t('blog.category_add') => ''
        ]);

        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        if($this->request()->request->get('do') == 'add') {
            if($model->saveCategory()) {
                header('Location: /admin/blog/?categories');
                exit();
            }
        }

        $data['request'] = $model->request()->request->all();

        return $this->render($data);
    }

    public function adminCategoryEdit()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('blog.category_edit'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('blog.name') => '/admin/blog/',
            $this->t('blog.category_edit') => ''
        ]);

        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        $data['category'] = $model->getCategoryById();

        if($this->request()->request->get('do') == 'edit') {
            if($model->saveCategory($data['category'])) {
                header('Location: /admin/blog/?categories=true');
                exit();
            }
        }

        $data['request'] = $model->request()->request->all();

        return $this->render($data);
    }

    public function blog()
    {
        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        if ($this->request()->attributes->get('_route') !== 'blog') {
            // TODO Временное решение, определение id статьи из имени роута
            $id = (int)str_replace('blog.category', '', $this->request()->attributes->get('_route'));
            $data['page'] = $model->getArticles($id);

            $category = $model->getCategoryById($id);

            $this->template()->setTitle($category['name']);
            $this->template()->setMetaTitle($category['meta_title']);
            $this->template()->setMetaDescription($category['meta_description']);
            $this->template()->setMetaKeywords($category['meta_keywords']);

            $this->breadcrumb([
                $this->t('system.main') => '/',
                $this->t('blog.name') => '/blog/',
                $category['name']
            ]);

        } else {
            $this->template()->setTitle($this->config()->get('blog.title'));
            $this->template()->setMetaTitle($this->config()->get('blog.meta_title'));
            $this->template()->setMetaDescription($this->config()->get('blog.meta_description'));
            $this->template()->setMetaKeywords($this->config()->get('blog.meta_keywords'));
            $this->breadcrumb([
                $this->t('system.main') => '/',
                $this->t('blog.name')
            ]);
            $data['page'] = $model->getArticles();
        }



        return $this->render($data, 'blog');
    }

    public function articleView()
    {
        $model = new BlogModel();
        $model->setContainer($this->getContainer());

        // TODO Временное решение, определение id статьи из имени роута
        $id = (int)str_replace('blog.article-', '', $this->request()->attributes->get('_route'));

        $data['page'] = $model->getArticle($id, true);

        $this->template()->setTitle($data['page']['name']);
        $this->template()->setMetaTitle($data['page']['meta_title']);
        $this->template()->setMetaDescription($data['page']['meta_description']);
        $this->template()->setMetaKeywords($data['page']['meta_keywords']);

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->t('blog.name') => '/blog/',
            $data['page']['category_name'] => '/blog/' . $data['page']['category_alias'] . '/'
        ])->activeLastLink();

        return $this->render($data, 'blog_article_view');
    }
}