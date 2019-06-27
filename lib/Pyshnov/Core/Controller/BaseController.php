<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Controller;


use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;
use Pyshnov\Core\DependencyInjection\PyshnovContainerAwareTrait;
use Pyshnov\Core\Template\Template;
use Pyshnov\system\Plugin\Breadcrumb\BreadcrumbInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use PyshnovContainerAwareTrait;

    /**
     * @param array|null $links
     * @return BreadcrumbInterface
     */
    public function breadcrumb(array $links = null)
    {
        if (null !== $links) {
            return $this->get('breadcrumb')->setLinks($links);
        }

        return $this->get('breadcrumb');
    }

    /**
     * @return Template
     */
    public function template()
    {
        return $this->get('template');
    }

    /**
     * @param $name
     * @return string
     */
    public function t($name)
    {
        return $this->get('language')->get($name);
    }

    /**
     * Проверка сделан ли запрос через Ajax
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->request()->isXmlHttpRequest();
    }

    /**
     * @param     $url
     * @param int $status
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Редиректом по HTTP_REFERER или при его отсутствии, на главную
     *
     * @return RedirectResponse
     */
    public function back()
    {
        return $this->redirect($this->request()->server->get('HTTP_REFERER', '/'));
    }

    /**
     * Выброс исключения: страница не найдена
     *
     * @param null $msg
     * @throws HttpException
     */
    public function notFound($msg = null)
    {
        throw new HttpException(404, $msg);
    }

    /**
     * Выброс исключения: доступ запрещен
     *
     * @param null $msg
     * @throws HttpException
     */
    public function forbidden($msg = null)
    {
        throw new HttpException(403, $msg);
    }

    /**
     * @param string|array $message
     * @param mixed        $type
     */
    public function setFlash($message, $type = null)
    {
        $type = $type === null ? 'info' : (is_bool($type) ? ($type ? 'success' : 'error') : $type);
        $this->session()->getFlashBag()->set($type, $message);
    }

    /**
     * @param string $message
     * @param mixed  $type
     */
    public function addFlash($message, $type = null)
    {
        $type = $type === null ? 'info' : (is_bool($type) ? ($type ? 'success' : 'error') : $type);
        $this->session()->getFlashBag()->add($type, $message);
    }

    /**
     * @return FlashBagInterface
     */
    public function getFlash()
    {
        return $this->session()->getFlashBag();
    }

    /**
     * @param array         $data
     * @param null          $view
     * @param Response|null $response
     * @return Response
     */
    public function render($data = [], $view = null, Response $response = null)
    {
        if (null === $response)
            $response = new Response();

        $this->template()->setData($data);

        return $response->setContent($this->template()->render($view));
    }

}