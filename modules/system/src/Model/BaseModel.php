<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\system\Model;


use Pyshnov\Core\DependencyInjection\PyshnovContainerAwareTrait;
use Pyshnov\Core\Template\Template;
use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;

class BaseModel implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use PyshnovContainerAwareTrait;

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

}