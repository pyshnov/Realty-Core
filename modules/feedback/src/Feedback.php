<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\feedback;


use Pyshnov\Core\Controller\BaseController;

class Feedback extends BaseController
{

    protected $params;

    public function init()
    {
        $this->template()->setMetaTitle('Обратная связь');
        $this->template()->setMetaDescription('Задайте свой вопрос по средствам формы обратной связи.');
        $this->template()->setTitle('Обратная связь');

        $this->breadcrumb([
            $this->t('system.main') => '/',
            'Обратная связь'
        ]);

        $data['request'] = [
            'fio' => false,
            'email' => false,
            'phone' => false,
            'text' => false
        ];

        if ($this->request()->request->get('do') == 'submit') {
            $request = $this->request()->request;
            $data['request'] = [
                'fio' => $request->get('fio', false),
                'email' => $request->get('email', false),
                'phone' => $request->get('phone', false),
                'text' => $request->get('text', false)
            ];

            $this->prepareParams();

            if (!$this->error()->has()) {
                $this->sendMail();
                $this->addFlash('Ваше сообщение успешно отправлено.', true);
                header('Location: /feedback/');
                exit();
            }
        }

        $data['success'] = $this->getFlash()->has('success') ? $this->getFlash()->get('success')[0] : false;

        return $this->render($data);
    }

    protected function prepareParams()
    {
        $params = $this->request()->request->all();

        $this->params['fio'] = $params['fio'] ?? '';
        $email = $params['email'] ?? '';

        if (!$email) {
            $this->error()->add('Не заполнено поле "Email"');
        } else {
            if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/",
                $email)
            ) {
                $this->error()->add('Не верный формат Email');
            }
        }

        $this->params['email'] = $email;
        $this->params['phone'] = $params['phone'] ?? '';
        $text = $params['text'] ?? '';

        if (!$text) {
            $this->error()->add('Не заполнено поле "Сообщение"');
        }

        $this->params['text'] = $text;

        if ($this->get('user')->isAnonymous()) {
            if (!captcha_validation()) {
                $this->error()->add('Вы не прошли проверку "Я не робот"');
            }
        }

    }

    protected function sendMail()
    {
        // TODO нужно вынести шаблон в базу
        $template = '
            <div style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px; margin:0; table-layout:fixed; text-align:center;">
                <h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">Сообщение с формы обратной связи!</h1>
                <p style="padding: 0;margin: 10px 0;font-size: 12px;text-align: left">
                    Имя: <strong>' . $this->params['fio'] . '</strong><br>
                    Email: <strong>' . $this->params['email'] . '</strong><br>
                    Телефон для связи: <strong>' . $this->params['phone'] . '</strong><br>
                <div style="margin: 0;padding: 10px 0;text-align: left">' . $this->params['text'] . '</div>
                </p>
            </div>';

        $mail = $this->get('mail');
        $mail->addAddress($this->config()->get('email_notice_reply'));
        $mail->setBody('Сообщение с сайта ' . $this->config()->get('site_name'), $template, true);

        $mail->send();
    }

}