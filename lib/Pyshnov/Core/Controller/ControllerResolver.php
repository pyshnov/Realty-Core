<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Controller;

use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as SymfonyControllerResolver;

class ControllerResolver extends SymfonyControllerResolver
{
    public function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return false;
        }

        if (is_array($controller)) {
            return $controller;
        }

        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }

            throw new \InvalidArgumentException(sprintf('Контроллер "%s" для URI "%s" не может быть выполнен.', get_class($controller), $request->getPathInfo()));
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        $callable = $this->createController($controller);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('Контроллер для URI "%s" не может быть выполнен. %s', $request->getPathInfo(), $this->getControllerError($callable)));
        }

        return $callable;
    }

    /**
     * @param string $controller
     * @return array
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Не удалось определить контроллер "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Класс "%s" не найден.', $class));
        }

        $class = $this->instantiateController($class);

        if ($class instanceof ContainerAwareInterface) {
            $class->setContainer(\Pyshnov::getContainer());
        }

        // Если есть метод execute, выполняем его
        if(method_exists($class, 'execute')) {
            $class->execute();
        }

        return [$class, $method];
    }

    private function getControllerError($callable)
    {
        if (is_string($callable)) {
            if (false !== strpos($callable, '::')) {
                $callable = explode('::', $callable);
            }

            if (class_exists($callable) && !method_exists($callable, '__invoke')) {
                return sprintf('Класс "%s" не имеет метода "__invoke".', $callable);
            }

            if (!function_exists($callable)) {
                return sprintf('Функция "%s" не существует.', $callable);
            }
        }

        if (!is_array($callable)) {
            return sprintf('Недопустимый тип для заданного контроллера, ожидается строка или массива, получено "%s".', gettype($callable));
        }

        if (2 !== count($callable)) {
            return sprintf('Недопустимый формат для контроллера, ожидается массив (контроллер, метод) или контроллер::метод.');
        }

        list($controller, $method) = $callable;

        $className = is_object($controller) ? get_class($controller) : $controller;

        if (method_exists($controller, $method)) {
            return sprintf('Метод "%s" класса "%s" не найден', $method, $className);
        }

        $collection = get_class_methods($controller);

        $alternatives = array();

        foreach ($collection as $item) {
            $lev = levenshtein($method, $item);

            if ($lev <= strlen($method) / 3 || false !== strpos($item, $method)) {
                $alternatives[] = $item;
            }
        }

        asort($alternatives);

        $message = sprintf('Требуется метод "%s" для класса "%s"', $method, $className);

        if (count($alternatives) > 0) {
            $message .= sprintf(', быть может ты имел ввиду "%s"?', implode('", "', $alternatives));
        } else {
            $message .= sprintf('. Доступные методы: "%s".', implode('", "', $collection));
        }

        return $message;
    }
}