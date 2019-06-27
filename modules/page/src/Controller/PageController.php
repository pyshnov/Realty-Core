<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\page\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\page\Model\PageModel;

class PageController extends BaseController
{
    public function adminPage()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('page.pages'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('page.pages') => ''
        ]);

        $model = new PageModel();
        $model->setContainer($this->container);

        return $this->render($model->getPages());
    }

    public function adminPageAdd()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('page.new_page'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('page.pages') => '/admin/page/',
            $this->t('page.new_page')
        ]);

        if($this->request()->request->get('do') == 'add') {
            $model = new PageModel();
            $model->setContainer($this->container);
            if($model->newPage()) {
                header('Location: /admin/page/');
                exit();
            }
        }

        $request = $this->request()->request->all();

        $data['request'] = [
            'title' => $request['title'] ?? '',
            'body' => $request['body'] ?? '',
            'alias' => $request['alias'] ?? '',
            'meta_title' => $request['meta_title'] ?? '',
            'keywords' => $request['keywords'] ?? '',
            'description' => $request['description'] ?? '',
            'theme' => $request['theme'] ?? '',
        ];

        return $this->render($data);
    }

    public function adminPageEdit()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('page.edit_page'));

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('page.pages') => '/admin/page/',
            $this->t('page.edit_page')
        ]);

        $model = new PageModel();
        $model->setContainer($this->container);
        $data['page'] = $model->getPageById();

        if($this->request()->request->get('do') == 'edit') {
            if($model->pageEdit($data['page'])) {
                header('Location: /admin/page/');
                exit();
            }
        }

        $request = $this->request()->request->all();

        $data['request'] = [
            'title' => $request['title'] ?? false,
            'body' => $request['body'] ?? false,
            'alias' => $request['alias'] ?? false,
            'meta_title' => $request['meta_title'] ?? false,
            'keywords' => $request['keywords'] ?? false,
            'description' => $request['description'] ?? false,
            'theme' => $request['theme'] ?? false,
        ];

        return $this->render($data);
    }

    public function pageView()
    {
        $model = new PageModel();
        $model->setContainer($this->container);

        $data['page'] = $model->getPage();

        $this->template()->setTitle($data['page']['title']);
        $this->template()->setMetaTitle($data['page']['meta_title']);
        $this->template()->setMetaDescription($data['page']['meta_description']);
        $this->template()->setMetaKeywords($data['page']['meta_keywords']);

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $data['page']['title'] => ''
        ]);

        if($data['page']['theme']) {
            $theme = $data['page']['theme'];
        } else {
            $theme = 'page_view';
        }

        return $this->render($data, $theme);
    }
}