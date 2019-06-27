<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/ReCaptcha.php';
require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/RequestMethod.php';
require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/RequestParameters.php';
require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/Response.php';
require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/RequestMethod/Curl.php';
require_once dirname(__FILE__) . '/recaptcha/src/ReCaptcha/RequestMethod/CurlPost.php';

function captcha_template_pre_process(&$variables)
{
    if(Pyshnov::user()->isAnonymous()) {
        $variables['recaptcha_sitekey'] = Pyshnov::config()->get('recaptcha.sitekey');
        $variables['captcha'] = theme_render('captcha', $variables);
    } else {
        $variables['captcha'] = false;
    }
}

function captcha_validation($captcha = 'recaptcha')
{
    if($captcha === 'recaptcha') {
        $recaptcha_secret_key = Pyshnov::config()->get('recaptcha.secret_key');

        $recaptcha = new ReCaptcha\ReCaptcha($recaptcha_secret_key, new ReCaptcha\RequestMethod\CurlPost());

        if (!($recaptcha instanceof ReCaptcha\ReCaptcha)) {
            return false;
        }

        if(Pyshnov::request()->request->has('g-recaptcha-response')) {
            $resp = $recaptcha->verify(
                Pyshnov::request()->request->get('g-recaptcha-response'),
                Pyshnov::request()->getClientIp()
            );

            if($resp->isSuccess()) {
                return true;
            }
        }
    }

    return false;
}