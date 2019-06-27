<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\faq\Controller;

use Pyshnov\Core\Controller\BaseController;
use Pyshnov\faq\Model\FaqModel;

class FaqController extends BaseController {

    public function adminFaq()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | Вопрос-Ответ');

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Вопрос-Ответ'
        ]);

        $model = new FaqModel();
        $model->setContainer($this->getContainer());
        $data['page'] = $model->getFaqAll();

        $data['nav_active'] = 'content';

        return $this->render($data);
    }

    public function adminFaqEdit()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | Редактировать вопрос');

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Вопрос-Ответ' => '/admin/faq/',
            'Редактировать вопрос'
        ]);

        $model = new FaqModel();
        $model->setContainer($this->getContainer());

        if($this->request()->request->get('do') == 'edit' && $model->update()) {
            header("Location: /admin/faq/");
            exit();
        }

        $data['faq'] = $model->getFaqById();

        $data['nav_active'] = 'content';

        return $this->render($data);
    }

    public function faq()
    {
        $title = $this->config()->get('faq.title');
        $meta_title = $this->config()->get('faq.meta_title');

        $this->template()->setTitle($title);
        $this->template()->setMetaTitle($meta_title ? : $title);
        $this->template()->setMetaDescription($this->config()->get('faq.description'));
        $this->template()->setMetaKeywords($this->config()->get('faq.keywords'));

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $title
        ]);

        $model = new FaqModel();
        $model->setContainer($this->getContainer());

        $data['page'] = $model->getFaq();

        return $this->render($data);
    }

}