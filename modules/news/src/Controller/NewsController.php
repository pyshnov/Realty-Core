<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\news\Controller;


use Pyshnov\Core\Controller\BaseController;

class NewsController extends BaseController
{
    public function newsList()
    {
        $this->template()->setMetaTitle('Новости и акции');
        $this->template()->setMetaDescription('Список новостей и акций');
        $this->template()->setTitle('Новости и акции');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Новости и акции'
        ]);

        return $this->render();
    }
}