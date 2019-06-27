<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\user\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\data\Form\DataForm;
use Pyshnov\data\Model\DataModel;
use Pyshnov\user\Model\UserModel;
use Pyshnov\user\Restore;
use Pyshnov\user\UserAction;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    public function signIn()
    {
        if ($this->get('user')->isAuthenticated()) {
            header('Location: /account/profile/');
            exit();
        }

        $this->template()->setMetaTitle('Авторизация');

        $this->template()->setThemeType('global');

        $data['blocked'] = false;

        if ($this->session()->has('blockTimeStart')) {
            $data['blocked'] = true;

            return $this->render($data, 'user_signin', new Response('', 403));
        }

        if ($this->request()->request->get('do') == 'enter') {
            $login = $this->request()->request->get('login');
            $password = $this->request()->request->get('password');
            $rememberme = $this->request()->request->has('rememberme');

            if ($login && $password) {

                if ($this->get('user.auth')->authorize($login, $password, $rememberme)) {
                    header('Location: ' . $this->request()->request->get('returnUrl'));
                    exit();
                } else {
                    if ($this->session()->has('blockTimeStart')) {
                        $data['blocked'] = true;
                    } else {
                        $this->error()->set('Неверный логин или пароль');
                    }
                }
            }
        }

        return $this->render($data, 'user_signin');
    }

    public function logout()
    {
        $this->get('user.auth')->logout();

        header('Location: /');
        exit();
    }

    public function register()
    {
        if ($this->get('user')->isAuthenticated()) {
            header('Location: /account/profile/');
            exit();
        }

        $this->template()->setTitle('Регистрация');
        $this->template()->setMetaTitle('Регистрация нового пользователя на сайте ' . $this->config()->get('site_name'));
        $this->template()->setMetaDescription('Бесплатная регистрация позволит Вам управлять подписками, объявлениями, быстрее всех узнавать о новых предложениях.');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Новое объявление'
        ]);

        $data['ip'] = $this->request()->getClientIp();

        if ('register' == $do = $this->request()->request->get('do')) {
            $model = new UserAction();
            if ($model->newUser()) {
                if ($this->config()->get('authorize_new_user')) {
                    $this->get('user.auth')->authorize($this->postParam()->get('email'), $this->postParam()->get('password'));
                    header('Location: /account/profile/');
                    exit();
                } else {
                    $this->addFlash('<p><b>Регистрация успешно завершена.</b></p>
<p>Благодарим Вас за регистрацию на нашем сайте! Теперь Вы можете авторизоваться на сайте, используя Ваш логин и пароль. </p>', true);
                }
            } else {
                $this->error()->set($model->getError());
            }

        }

        return $this->render();
    }

    public function restore()
    {
        if ($this->get('user')->isAuthenticated()) {
            header('Location: /');
            exit();
        }

        $this->template()->setTitle('Восстановление пароля');
        $this->template()->setMetaTitle('Восстановление пароля');

        $restore = new Restore();
        $restore->setContainer($this->container);

        $data = $restore->run();

        return $this->render($data);
    }

    public function accountProfile()
    {
        if ($this->get('user')->isAnonymous()) {
            header('Location: /signin/');
            exit();
        }

        $this->template()->setTitle('Профиль и настройки');
        $this->template()->setMetaTitle('Личный кабинет - Профиль пользователя');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Профиль пользователя'
        ]);

        return $this->render();
    }

    public function accountData()
    {
        if ($this->get('user')->isAnonymous()) {
            header('Location: /signin/');
            exit();
        }

        $this->template()->setTitle('Мои объявления');
        $this->template()->setMetaTitle('Личный кабинет - Мои объявления');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Мои объявления'
        ]);

        $model = new DataModel();
        $model->setContainer($this->getContainer());

        $data['page'] = $model->getUserObjects($this->get('user')->getId());

        return $this->render($data);
    }

    public function accountDataEdit()
    {
        if ($this->get('user')->isAnonymous()) {
            header('Location: /register/');
            exit();
        }

        $this->template()->setMetaTitle('Личный кабинет - Редактирование объявления');
        $this->template()->setTitle('Редактирование объявления');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Мои объявления' => '/account/data/',
            'Редактирование объявления'
        ]);

        $model = new DataModel();
        $model->setContainer($this->container);

        $data['object'] = $model->getObjectById();

        // Если id владельца объявления не совпадает с id авторизованного
        // пользователя, выбрасываем на главную
        if($data['object']['user_id'] != $this->get('user')->getId()) {
            header("Location: /");
            exit();
        }

        if('edit' == $this->request()->get('do')) {
            if($model->editObject($data['object'], true)) {
                header('Location: /account/data/');
                exit();
            }

        }

        $data_form = new DataForm();
        $data['data_form'] = $data_form;
        $data['district'] = $data_form->getReference('district', $data['object']['city_id'], $data['object']['district_id']);
        $data['metro'] = $data_form->getReference('metro', $data['object']['city_id'], $data['object']['metro_id']);

        return $this->render($data);
    }

    public function adminGroups()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('user.groups'));

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->t('user.groups')
        ]);

        $model = new UserModel();
        $model->setContainer($this->container);

        $data['page'] = $model->getGroupsAll();

        return $this->render($data);
    }

    public function adminUsers()
    {
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $this->t('user.groups'));

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $this->t('user.users')
        ]);

        $model = new UserModel();
        $model->setContainer($this->container);

        return $this->render();
    }
}