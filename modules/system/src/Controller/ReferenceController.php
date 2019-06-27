<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\system\Model\ReferenceModel;

class ReferenceController extends BaseController
{
    public function adminMain($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('system.handbook') => ''
        ]);

        $model = new ReferenceModel();
        $model->setContainer($this->container);

        $data = [];

        if ($type = $this->request()->get('type')) {
            $res = $model->getReference($type);
            $data['page'] = $res;
        }

        return $this->render($data);
    }

    public function adminAdd($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $model = new ReferenceModel();
        $model->setContainer($this->container);

        $type = $this->request()->get('type');

        $data['request'] = $this->postParam()->all();

        if($type) {
            $data['page'] = $model->referenceAdd($type);
        }

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Список ' . mb_strtolower($data['page']['info']['name'][1]) => '/admin/reference/' . $type . '/',
            'Новый элемент' => ''
        ]);



        return $this->render($data);
    }
}