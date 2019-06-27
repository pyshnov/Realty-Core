<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\blacklist\Controller;


use Pyshnov\blacklist\Model\BlacklistModel;
use Pyshnov\Core\Controller\BaseController;

class BlacklistController extends BaseController
{
    public function adminMain($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Антиагент' => ''
        ]);

        $model = new BlacklistModel();
        $model->setContainer($this->container);

        $data['do'] = $this->getParam()->get('do') ?? 'main';

        if ($data['do'] == 'double') {
            $data['page'] = $model->getDouble();
        } elseif ($data['do'] == 'agency') {
            $data['page'] = $model->getAgency();
        } else {
            $data['page'] = $model->getBlacklist();
        }

        return $this->render($data);
    }
}