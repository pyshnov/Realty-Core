<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\config\Controller;


use Pyshnov\config\Model\ConfigModel;
use Pyshnov\Core\Controller\BaseController;

class ConfigController extends BaseController
{
    public function main($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('config.settings') => ''
        ]);

        $model = new ConfigModel();

        $data['config'] = $model->getConfig();

        $data['tab'] = $this->request()->query->get('tab') ?? 'system';

        return $this->render($data);
    }
}