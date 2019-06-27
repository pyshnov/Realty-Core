<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Ajax;

use Pyshnov\Core\DependencyInjection\PyshnovContainerAwareTrait;
use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;


class AjaxResponse implements ContainerAwareInterface
{
    /**
     * @var string сообщение об успешнон результате выполнения операции.
     */
    protected $messageSuccess = 'Success';

    /**
     * @var string сообщение о неудачном результате выполнения операции.
     */
    protected $messageError = 'Error';

    /**
     * @var mixed массив с данными возвращаемый в ответ на AJAX запрос.
     */
    protected $data = [];

    use ContainerAwareTrait;
    use PyshnovContainerAwareTrait;

    /**
     * @param $name
     * @return string
     */
    public function t($name)
    {
        return $this->get('language')->get($name);
    }

    public function getPostParam($key, $default = null)
    {
        return $this->request()->request->get($key, $default);
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
     * @param string $messageSuccess
     */
    public function setMessageSuccess(string $messageSuccess)
    {
        $this->messageSuccess = $messageSuccess;
    }

    /**
     * @param string $messageError
     */
    public function setMessageError(string $messageError)
    {
        $this->messageError = $messageError;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param mixed $data
     * @param mixed $response
     * @return JsonResponse|mixed
     */
    public function render($data = true, $response = null)
    {
        if (null === $response) {
            if (is_bool($data)) {
                $data = [
                    'data' => $this->data,
                    'message' => $data ? $this->messageSuccess : $this->messageError,
                    'status' => $data ? 'success' : 'error',
                ];
            }
            $response = new JsonResponse($data);
        }

        return $response;
    }

}