<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * void AddReplyTo (string $address, [string $name = »]) — Адрес для ответа на ваше письмо. По умолчанию, адрес для ответа совпадает с адресом, с которого вы отослали письмо (FROM)
 */


namespace Pyshnov\Core\Mail;

use Pyshnov\Core\Logger\FileLogger;

require_once dirname(__FILE__) . '/phpmailer/class.phpmailer.php';
require_once dirname(__FILE__) . '/phpmailer/class.smtp.php';

class Mail extends \PHPMailer
{

    public function __construct($exceptions = null)
    {
        $this->CharSet = \Pyshnov::kernel()->getCharset();
        //$this->Encoding = "base64";

        if (\Pyshnov::config()->get('use_smtp')) {
            $this->Host = \Pyshnov::config()->get('smtp_server');
            $this->SMTPSecure = 'ssl';
            $this->Port = \Pyshnov::config()->get('smtp_port');

            if ($smtp_user = \Pyshnov::config()->get('smtp_user')) {
                $this->SMTPAuth  = true;
                $this->Username  = $smtp_user;
                $this->Password  =  \Pyshnov::config()->get('smtp_password');
            }

            $this->Mailer = 'smtp';
        }

        $this->setLanguage('ru', dirname(__FILE__) . '/phpmailer/language/');

        $this->From = \Pyshnov::config()->get('noreply_email');
        $this->Sender = \Pyshnov::config()->get('noreply_email');
        $this->FromName = \Pyshnov::config()->get('site_name');

        $this->XMailer = "Pyshnov Realty CMS";

        parent::__construct($exceptions);
    }

    /**
     * @param      $subject - Заголовок
     * @param      $message
     * @param bool $is_html
     */
    public function setBody($subject, $message, $is_html = false)
    {
        $this->Subject = $subject;

        if ($is_html) {
            $this->msgHTML($message);
        } else {
            $this->Body = $message;
        }

    }

    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }

            if ($this->postSend()) {
                $this->clearAllRecipients();
                $this->clearAttachments();

                return true;
            }

            return false;

        } catch (\phpmailerException $exc) {
            $this->mailHeader = '';
            $log = new FileLogger(\Pyshnov::kernel()->getLogDir() . '/mail_log.txt');
            $log->error($exc->getMessage());

            return false;
        }
    }

}