<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core\Routing;


use Pyshnov\Core\Cache\FileCache\FileCache;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\DependencyInjection\ContainerAwareInterface;
use Pyshnov\Core\DependencyInjection\ContainerAwareTrait;
use Pyshnov\Core\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Router implements ContainerAwareInterface
{
    protected $rootDir;
    protected $files;
    protected $cacheDir;

    /**
     * @var RouteCollection|null
     */
    protected $collection;

    use ContainerAwareTrait;

    public function __construct($root, $files, $cache_dir)
    {
        $this->rootDir = $root;
        $this->files = $files;
        $this->cacheDir = $cache_dir;

    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    private function compileCollection()
    {
        if (null === $this->collection) {
            $this->collection = new RouteCollection();
        }

        $this->files[] = $this->rootDir . '/' . \Pyshnov::CONFIG_DIR . '/routing.yml';
        $cache = new FileCache($this->cacheDir);

        if (!$collection = $cache->get('router')) {

            $routes = DB::select('*', DB_PREFIX . '_router')->execute()->fetchAll();

            foreach ($routes as $route) {
                $this->collection->add($route['name'], unserialize($route['route']));
            }

            $loader = new YamlFileLoader($this->rootDir);

            foreach ($this->files as $file) {
                $collection = $loader->load($file);

                $this->collection->addCollection($collection);
            }

            $cache->save('router', $this->collection);

        } else {
            $this->collection = $collection;
        }
    }

    /**
     * @param Request $request
     */
    public function match(Request $request)
    {
        $access = true;

        $this->compileCollection();

        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->collection, $context);

        try {
            $match = $matcher->match($request->getPathInfo());

            $alias = $this->container->get('user')->getAlias();

            $route_match = $this->getCollection()->get($match['_route']);

            if ($route_match->hasRequirement('_access')) {
                $permissions = str_replace(' ', '', $route_match->getRequirement('_access'));
                if(strpos($permissions, ',') !== false) {
                    $permissions = explode(',', $permissions);
                    if (!in_array($alias, $permissions)) {
                        $access = false;
                    }
                } else {
                    if($alias != trim($permissions)) {
                        $access = false;
                    }
                }

                if(!$access) {
                    if($this->container->get('user')->isAnonymous() && !$request->isXmlHttpRequest()) {
                        $match['_controller'] = '\Pyshnov\user\Controller\UserController::signIn';
                        $match['_route'] = 'user.signin';
                    } else {
                        $match['_controller'] = '\Pyshnov\system\Controller\SystemController::accessDenied';
                        $match['_route'] = 'system.access_denied';
                    }
                }
            }
        } catch (\Exception $e) {
            $match = $matcher->match('/system/404/');
        }

        $request->attributes->add($match);

        $this->container->get('route_match')
            ->setName($match['_route'])
            ->setRoute($this->getCollection()->get($match['_route']))
            ->setUserAccess($access);

    }
}