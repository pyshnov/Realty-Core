<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\Core\Cookies\Cookies;
use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Geocode\Geocode;
use Pyshnov\data\Form\DataForm;
use Pyshnov\data\Model\DataModel;
use Pyshnov\data\Model\GeodataModel;
use Symfony\Component\HttpFoundation\Response;

class DataController extends BaseController
{

    public function execute()
    {
    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminData($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $data['breadcrumb'] = $this->breadcrumb()->setLinks([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['page'] = $model->prepareBackend($model->getDataAll());
        $data['count_active'] = $model->countActive();
        $data['count_un_active'] = $model->countUnActive();
        $data['count_moderation'] = $model->countModeration();
        $data['params'] = $this->request()->query->all();

        return $this->render($data);
    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminEdit($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => '/admin/data/',
            'Редактировать' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        if ($object = $model->getObjectById()) {
            $data['object'] = $object;
        } else {
            return $this->render([], 'not_object');
        }

        if ('edit' == $this->request()->request->get('do')) {

            if ($this->request()->request->get('post_vk', false)) {
                vkPost($data);
            }

            if ($model->editObject($data['object'])) {
                header("Location: /admin/data/");
                exit();
            }
        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;

        return $this->render($data);
    }

    public function adminAdd($_title)
    {

        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('data.managing_ads') => '/admin/data/',
            'Новое объявление' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        if ('add' == $this->request()->request->get('do')) {
            if ($model->newObject()) {
                header("Location: /admin/data/");
                exit();
            }
        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;

        $data['request'] = $this->postParam()->all();

        return $this->render($data);
    }

    /**
     * @param $_title
     * @return Response
     */
    public function adminComplaint($_title)
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . $_title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            'Жалобы' => ''
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['complaint'] = $model->getComplaint();

        return $this->render($data);
    }

    public function location()
    {
        $this->template()->setMetaTitle($this->config()->get('meta_title_base'));
        $this->template()->setMetaDescription($this->config()->get('meta_description_base'));
        $this->template()->setMetaKeywords($this->config()->get('meta_keywords_base'));
        $this->template()->setTitle($this->config()->get('title_base'));

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->config()->get('title_base')
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['data'] = $model->getObjects();
        $data['price_range'] = $model->getPriceRange();

        return $this->render($data, 'data_location');
    }

    protected function redirectTopic()
    {
        $category = $this->get('category');

        $attr = $this->request()->attributes->get('topic');
        $topic_id = array_search($attr, $category->getAliases());

        if ($topic = $category->getCategory($topic_id)) {
            return $this->redirect('/' . $this->get('city')->getAlias() . $topic['url'], 301);
        } else {
            $this->template()->setMetaTitle($this->t('system.page_not_found') . ' | ' . $this->config()->get('site_name'));

            return $this->render([], '404', new Response('', 404));
        }
    }

    public function realtyView()
    {
        $model = new DataModel();
        $model->setContainer($this->getContainer());

        if (!$data['data'] = $model->getObjectById(true)) {
            return $this->redirectTopic();
        }

        $location = $this->get('location')->getCity();

        if (!$data['data']->active) {
            if (!$this->config()->get('realty_allow_notactive_direct')
                && $data['data']->user_id != $this->get('user')->getId()) {
                return $this->redirectTopic();
            } else {
                if ($data['data']->status_data != 3
                    && $data['data']->status_data != 4
                    && $data['data']->user_id != $this->get('user')->getId()) {
                    return $this->redirectTopic();
                }
            }
        }

        // Если был прямой переход и город объявления не совпадает с городом сайта
        // перезаписывыем его
        if ($location->getId() != $data['data']->city_id) {
            $stmt = DB::select('city_id, name, aliases, region_id', DB_PREFIX . '_city')
                ->where('city_id', '=', $data['data']->city_id)
                ->limit(1)
                ->execute();

            $res = $stmt->fetchObject();

            $location->setId($res->city_id)
                ->setName($res->name)
                ->setAlias($res->aliases)
                ->setRegionId($res->region_id);

            $cookie = new Cookies('cid', $res->city_id, '+1 year', '/', null, false, false);
            $cookie->send();
        }

        $this->template()->setTitle($data['data']->title());
        $this->template()->setMetaTitle($data['data']->meta()->title);
        $this->template()->setMetaDescription($data['data']->meta()->description);
        $this->template()->setMetaKeywords($data['data']->meta()->keywords);

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->config()->get('title_base') => getDataBaseLink(),
            $data['data']->topic->public_title => $data['data']->topic->url
        ])->activeLastLink();

        $data['price_range'] = $model->getPriceRange();
        $data['similar'] = $model->getSimilar($data['data']);

        return $this->render($data);
    }

    public function topic()
    {
        $model = new DataModel();
        $model->setContainer($this->container);
        $data['price_range'] = $model->getPriceRange();

        $category = $this->get('category');
        $url = $this->request()->getPathInfo();

        if ($location = $this->request()->attributes->get('_location', false)) {
            $url = str_replace('/' . $location, '', $url);
        }

        $topic_id = array_search($url, $category->getLinks());

        if (!$topic_id) {
            $this->template()->setMetaTitle($this->t('system.page_not_found') . ' | ' . $this->config()->get('site_name'));
            return $this->render($data, '404', new Response('', 404));
        }

        $topic = $category->getCategory($topic_id);

        if ($category->hasChildren($topic_id)) {
            $topic_id = $category->allChildrenCategories($topic_id);
        }

        $this->template()->setMetaTitle($topic['meta_title']);
        $this->template()->setMetaDescription($topic['meta_description']);
        $this->template()->setMetaKeywords($topic['meta_keywords']);
        $this->template()->setTitle($topic['public_title']);

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->config()->get('title_base') => getDataBaseLink()
        ]);

        if ($this->request()->attributes->get('_topic', false)) {
            $this->breadcrumb()->addLink($topic['public_title']);
        }

        $data['data'] = $model->getObjects($topic_id);

        return $this->render($data, 'data_realty_list');
    }

    public function favorites()
    {

        $this->template()->setMetaTitle('Избранные объекты');
        $this->template()->setMetaDescription('Список избранных объектовы');
        $this->template()->setTitle('Избранное');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->config()->get('title_base') => getDataBaseLink(),
            'Избранное'
        ]);


        $model = new DataModel();
        $model->setContainer($this->container);

        $data['data'] = $model->getObjects(null, true);
        $data['price_range'] = $model->getPriceRange();

        return $this->render($data);
    }

    public function add()
    {
        if ($this->get('user')->isAnonymous()
            && $this->config()->get('new_object_anonymous_user') == false
        ) {
            header('Location: /register/');
            exit();
        }

        $this->template()->setMetaTitle('Подать бесплатное объявление о сдаче в аренду недвижимости');
        $this->template()->setMetaDescription('Разместить рекламное объявление о сдаче в аренду недвижимости бесплатно в  базу данных сайта нет агента быстро, удобно и без сложных регистраций.');
        $this->template()->setMetaKeywords('');
        $this->template()->setTitle('Новое объявление');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Новое объявление'
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        if ('add' == $do = $this->request()->request->get('do')) {
            if ($id = $model->newObject(true)) {
                $this->addFlash(sprintf($this->t('data.success_new_object'), $id), true);
                header("Location: /add/");
                exit();
            }
        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;

        $data['success'] = $this->getFlash()->has('success') ? $this->getFlash()->get('success')[0] : false;

        return $this->render($data);
    }

    public function mapSearch()
    {
        $this->template()->setTitle('Поиск на карте');
        $this->template()->setMetaTitle('Поиск на карте');
        $this->template()->setMetaDescription('Поиск объектов на карте.');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Поиск на карте'
        ]);

        $model = new GeodataModel();
        $model->setContainer($this->container);
        $data['geodata'] = $model->map();

        $geocode = new Geocode();

        $geo = $geocode->setQuery($this->get('city')->getName())->setLimit(1)->load();
        if ($geo) {
            $data['coordinate'] = $geocode->getLatitude() . ',' . $geocode->getLongitude();
        } else {
            $data['coordinate'] = false;
        }

        return $this->render($data);
    }

}