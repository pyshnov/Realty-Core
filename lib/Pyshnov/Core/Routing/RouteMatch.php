<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Routing;

use Symfony\Component\Routing\Route;

class RouteMatch
{

    protected $name;
    protected $route;
    protected $userAccess;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->getRoute()->hasOption($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->getRoute()->getOption($name);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->getRoute()->getOptions();
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRequirement($name)
    {
        return $this->getRoute()->hasRequirement($name);
    }

    /**
     * @param $name
     * @return null|string
     */
    public function getRequirement($name)
    {
        return $this->getRoute()->getRequirement($name);
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->getRoute()->getRequirements();
    }

    /**
     * @return bool
     */
    public function isUserAccess(): bool
    {
        return $this->userAccess;
    }

    /**
     * @param bool $user_access
     */
    public function setUserAccess(bool $user_access)
    {
        $this->userAccess = $user_access;
    }

}