<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\Core\Helpers\Directory;
use Pyshnov\Core\Image\Image;
use Pyshnov\system\Model\SystemModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SystemController extends BaseController
{
    /**
     * Главная админки
     *
     * @return Response
     */
    public function adminMain()
    {

        $this->template()->setMetaTitle($this->t('system.control_panel'));

        return $this->render([], 'admin_main');
    }

    public function adminModule($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('system.extensions') => ''
        ]);

        $model = new SystemModel();

        $model->setContainer($this->getContainer());

        $data['modules'] = $model->getModules();

        return $this->render($data);
    }

    public function adminHelp()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | Помощь');

        return $this->render();
    }

    /**
     * Главная сайта
     *
     * @return Response
     */
    public function main()
    {
        $this->template()->setMetaTitle($this->config()->get('meta_title_main'));
        $this->template()->setMetaDescription($this->config()->get('meta_description_main'));
        $this->template()->setMetaKeywords($this->config()->get('meta_keywords_main'));

        return $this->render([]);
    }

    public function userAjax()
    {
        $theme_path = $this->get('kernel')->getRootDir() . '/' . \Pyshnov::config()->get('theme_pathname');

        if (file_exists($theme_path . '/UserAjax.php')) {
            include $theme_path . '/UserAjax.php';

            if ($action = $this->request()->get('action', false)) {

                $object = new \UserAjax();

                if (method_exists($object, $action)) {
                    $object->setContainer($this->container);
                    $result = $object->$action();
                    if ($result instanceof JsonResponse) {
                        return $result;
                    }
                }
            }
        }

        return new JsonResponse([], '404');
    }

    /**
     * @return Response
     */
    public function error404()
    {
        $this->template()->setMetaTitle($this->t('system.page_not_found') . ' | ' . $this->config()->get('site_name'));
        return $this->render([], '404', new Response('', 404));
    }

    /**
     * Страница "доступ запрещен"
     *
     * @return Response
     */
    public function accessDenied()
    {
        if ($this->isAjax()) {
            return new JsonResponse('', 403);
        }

        $this->template()->setMetaTitle('403 — ' . $this->config()->get('site_name'));
        $this->template()->setThemeType('global');
        return $this->render([], 'access_denied', new Response('', 403));
    }

    public function imgResize()
    {
        $src = $this->get('kernel')->getRootDir() . $this->request()->query->get('src');

        $width = $this->request()->query->get('w');
        $height = $this->request()->query->get('h');

        $image = new Image($src);

        $image->thumbnail($width, $height, 'center');
        $image->apply();

        header("Content-type: image/jpeg");

        imagejpeg($image->getImage(), null, 100);

        exit;
    }

}