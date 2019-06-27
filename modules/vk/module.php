<?php
/**
 * @copyright  2018 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function vk_theme_pre_process_data_admin_edit(&$variables)
{

   // var_dump($variables['object']['post_vk']);

   /* if ($variables['object']['post_vk']) {

    }*/

    //vkPost($variables);

// Получение сервера vk для загрузки изображения.
   /* $res = json_decode(file_get_contents(
        'https://api.vk.com/method/photos.getWallUploadServer?group_id='
        . $group_id . '&access_token=' . $access_token
    ));

    //https://oauth.vk.com/authorize?client_id=6332537&scope=notify,friends,photos,offline,wall&redirect_uri=blank.html&display=popup&response_type=token

    if (!empty($res->response->upload_url)) {
        // Отправка изображения на сервер.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $res->response->upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => '@' . $imga));

        //  если у вас php 5.6 + удалите // с начала строки у строки ниже
        //curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => new CurlFile($imga)));

        $res = json_decode(curl_exec($ch));
        curl_close($ch);

        var_dump($res);

        if (!empty($res->server)) {
            // Сохранение фото в группе.
            $res = json_decode(file_get_contents(
                'https://api.vk.com/method/photos.saveWallPhoto?group_id=' . $group_id
                . '&server=' . $res->server . '&photo='
                . stripslashes($res->photo) . '&hash='
                . $res->hash . '&access_token=' . $access_token
            ));

            var_dump($res);

            if (!empty($res->response[0]->id)) {
                // Отправляем сообщение.
                $params = array(
                    'access_token' => $access_token,
                    'owner_id'     => '-' . $group_id,
                    'from_group'   => '1',
                    'message'      => $message,
                    'attachments'  => $res->response[0]->id
                );

                file_get_contents(
                    'https://api.vk.com/method/wall.post?' . http_build_query($params)
                );
            }
        }
    }
    unlink($imga);*/
}

function vkPost($variables) {
    $imga = [];

    if (!empty($variables['object']['image'])) {
        //var_dump($variables['object']['image']);

        for ($i = 0, $size = count($variables['object']['image']); $i < 4 && $i < $size; ++$i) {
            $imga[] = Pyshnov::service('kernel')->getRootDir() . \Pyshnov::DATA_IMG_DIR  . '/' .$variables['object']['image'][$i]['name'];
        }
    }

    $object = $variables['object'];

    $category = Pyshnov::service('category')->getCategory($object['topic_id']);

    $url = \Pyshnov::request()->getSchemeAndHttpHost() . $category['url']
        . \Pyshnov::config()->get('prefix_realty_name')
        . $object['id']
        . (\Pyshnov::config()->get('object_html_prefix') ? '.html' : '/');

    //var_dump($variables['object']);

    $html = 'Без посредников ' . mb_strtolower($category['name_list']) . ' г.' . $object['city_name']
        . ' — ' . $object['price'] . ' / ' . ($object['lease_period'] == 1 ? 'месяц' : 'день')
        . ' 
Адрес: ' . $object['street'] . ' ' . $object['number'] . '
Подробнее с фото: ' . $url . "\n
Больше вариантов: net-agenta.net/saint-petersburg/arenda/
Бесплатно подать объявление: net-agenta.net/add/";

    //var_dump($html);

    ///Настройки скрипта

    $group_id     = '87047307'; //вместо 1 пишем id своего сообщества 87047307

    $config['secret_key'] = 'KEZ5AfCZGaFSNCWI7Pmi';
    $config['client_id'] = 6332537; // номер приложения
    $config['user_id'] = 208529228; // id текущего пользователя (не обязательно)
    $config['access_token'] = 'aac105b4e130f11164edae8e735432f155f8cbcde16a8a7bafb1713e7eb54a8d9437a125fe1a2f3ebb78a';
    //$config['scope'] = 'photos'; // права доступа к методам (для генерации токена)

    $v = new \Pyshnov\vk\Vk($config);

    // загрузка фото на сервер
    $attachments = $v->upload_photo($group_id, $imga, false);

    $response = $v->api('wall.post', array(
        'owner_id' => '-'. $group_id,
        'from_group' => 1,
        'message' => $html,
        'attachments' => implode(',', $attachments),
        // 'publish_date' => ''
    ));

    //var_dump($response);
}

