<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\Core;

use Composer\Autoload\ClassLoader;
use Pyshnov\Core\DependencyInjection\Container;
use Pyshnov\Core\DependencyInjection\ParameterBag\ParameterBag;
use Pyshnov\Core\DependencyInjection\YamlFileLoader;
use Pyshnov\Core\Exception\HttpExceptionInterface;
use Pyshnov\Core\Extension\ExtensionDiscovery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\TerminableInterface;

class PyshnovKernel implements PyshnovKernelInterface, TerminableInterface
{
    /**
     * @var
     */
    protected $container;
    protected $classLoader;
    protected $environment;
    protected $debug;
    protected $startTime;

    protected $rootDir;

    protected $charset = 'utf-8';

    protected $booted = false;

    protected $ymlServices;

    protected $moduleList;

    protected $routeFiles;

    public function __construct(ClassLoader $class_loader, $environment, $debug = false, $root = null)
    {
        $this->classLoader = $class_loader;
        $this->environment = $environment;
        $this->debug = $debug;

        if (null === $root)
            $root = $this->guessRoot();

        $this->rootDir = $root;

        if ($this->debug)
            $this->startTime = microtime(true);
    }

    public function __clone()
    {
        if ($this->isDebug()) {
            $this->startTime = microtime(true);
        }

        $this->booted = false;
        $this->container = null;
    }

    private function loadIncludes()
    {
        require_once $this->getRootDir() . '/core/templates/engine/twig/twig.php';
        require_once $this->getRootDir() . '/core/includes/common.php';
        require_once $this->getRootDir() . '/core/includes/template.php';
    }

    /**
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->bootEnvironment();

        $path_info = $request->getPathInfo();
        $query_string = $request->getQueryString();

        if (strpos($path_info, '.') === false && substr($path_info, -1) != '/') {
            $query = $query_string !== null ? '?' . $query_string : '';
            header('HTTP/1.1 301 Moved Permanently');
            header("Location:" . $path_info . '/' . $query);
            exit();
        }

        if (!$request->query->has('page')) {
            $request->query->add(['page' => 1]);
        }

        try {
            $this->loadIncludes();
            // Инициализируем контейнер.
            $this->initializeContainer($request);
            $response = $this->getHttpKernel()->handle($request, $type, $catch);
        } catch (\Exception $e) {
            if (false === $catch)
                throw $e;

            $response = $this->handleException($e);
        }

        return $response->prepare($request);
    }

    protected function initializeContainer(Request $request)
    {
        $this->container = $this->compileContainer();

        $this->container->get('request_stack')->push($request);

        $this->container->get('session')->init($request);

        $this->container->get('config')->init($request);

        $this->container->get('user.auth')->init();

        $this->container->get('router')->match($request);

        $this->container->get('location')->init($request);

        \Pyshnov::setContainer($this->getContainer());

    }

    public function compileContainer()
    {
        $this->findExtensionFiles();

        $container = new Container(new ParameterBag($this->getKernelParameters()));
        $container->set('kernel', $this);

        $loader = new YamlFileLoader($container);

        foreach ($this->ymlServices as $filename) {
            $loader->load($this->getRootDir() . '/' . $filename);
        }

        return $container;
    }

    public function findExtensionFiles()
    {
        $this->ymlServices['core'] = 'core/services.yml';

        foreach ($this->getModuleList() as $name => $module) {
            $pathname = $module->getPathname();
            if (file_exists($this->getRootDir() . '/' . $pathname . '/services.yml')) {
                $this->ymlServices[] = $pathname . '/services.yml';
            }
            // Сразу же проверим наличие файлов с муршрутами
            if (file_exists($this->getRootDir() . '/' . $pathname . '/routing.yml')) {
                $this->routeFiles[] = $pathname . '/routing.yml';
            }

            $module->load();

            $this->getClassLoader()->addPsr4('Pyshnov\\' . $name . '\\', $this->getRootDir() . '/' . $pathname . '/src');

        }

    }

    /**
     * Вернет списко всех модулей программы
     *
     * @return array
     */
    public function getModuleList()
    {
        if (null === $this->moduleList) {
            $listing = new ExtensionDiscovery($this->getRootDir());
            $this->moduleList = $listing->scan('module');
        }

        return $this->moduleList;
    }

    /**
     * Преобразует исключение в ответ.
     *
     * @param \Exception $e Исключение
     * @return Response
     * @throws \Exception Если переданное исключение не может быть превращено в ответ.
     */
    protected function handleException(\Exception $e)
    {
        if ($e instanceof HttpExceptionInterface) {

            $message = <<<HTML
<html>
<head>
    <title>404 Not Found</title>
    <style>
        body {
            padding-top: 50px;
            background-color: #f3f3f4;
            font-family: "open sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #676a6c;
            text-align: center;
        }
        h1, h2, h3 {
            margin: 10px 0;
            line-height: 1.1;
        }

        h1 {
            font-size: 140px;
            font-weight: 100;
        }
        
        h3 {
            font-size: 16px;
        }

        .font-bold {
            font-weight: 600;
        }

    </style>
</head>
<body>
    <h1>404</h1>
    <h3 class="font-bold">Страница не найдена</h3>
    <div class="error-desc">
    {{ content }}
    </div>
</body>
</html>
HTML;

            $loader = new \Twig_Loader_Array([
                '404' => $message
            ]);
            $twig = new \Twig_Environment($loader);



            $response = new Response($twig->render('404', array('content' => $e->getMessage())), $e->getStatusCode());
            $response->headers->add($e->getHeaders());


            //var_dump($response);

            return $response;
        } else {
            throw $e;
        }
    }

    public function terminate(Request $request, Response $response)
    {
        if (false === $this->booted) {
            return;
        }

        if ($this->getHttpKernel() instanceof TerminableInterface) {
            $this->getHttpKernel()->terminate($request, $response);
        }
    }

    /**
     * Вернет HTTP kernel из контейнера.
     *
     * @return HttpKernel
     */
    protected function getHttpKernel()
    {
        return $this->getContainer()->get('http_kernel');
    }

    /**
     * @return array
     */
    protected function getKernelParameters()
    {
        return [
            'kernel.root_dir' => $this->getRootDir(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogDir(),
            'kernel.charset' => $this->getCharset(),
            'kernel.modules' => $this->getModuleList(),
            'kernel.route_files' => $this->getRouteFiles()
        ];
    }

    public function bootEnvironment()
    {
        error_reporting(E_ALL);

        if ($this->isDebug())
            ini_set('display_errors', 1);
        else
            ini_set('display_errors', 0);

        if (version_compare(phpversion(), '7', '<')) {
            exit('Требуется PHP 7 и выше');
        }

        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        ini_set('session.cache_limiter', '');
        ini_set('session.cache_limiter', '1');
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ClassLoader
     */
    public function getClassLoader(): ClassLoader
    {
        return $this->classLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Вычислит абсолютный путь до программы
     *
     * @return string
     */
    protected function guessRoot()
    {
        return dirname(dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__))));
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getStartTime()
    {
        return $this->isDebug() ? $this->startTime : -INF;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $this->getRootDir() . '/cache/' . $this->getEnvironment();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->getRootDir() . '/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return array
     */
    public function getRouteFiles()
    {
        return $this->routeFiles;
    }

}