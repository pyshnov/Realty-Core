<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data\Form;


use Pyshnov\Core\Image\Exception\ImageException;
use Pyshnov\Core\Image\Image;
use Pyshnov\Core\Logger\FileLogger;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Upload
{
    protected $validFormats = ['jpeg', 'jpg', 'png', 'gif'];

    /**
     * @return array
     */
    public function getValidFormats():array
    {
        return $this->validFormats;
    }

    public function loadImage($key, SessionInterface $session)
    {
        if (!empty($_FILES)) {

            $tempFile = $_FILES['file']['tmp_name'];
            $targetPath = dirname(\Pyshnov::kernel()->getCacheDir()) . '/img/';

            $arr = explode('.', $_FILES['file']['name']); // Разбиваем строку на подстроки
            $ext = strtolower(array_pop($arr)); // Получим тип файла

            // Проверим, не привышает ли размер фото размер максимально допустимого размера сервера
            if (($_FILES['file']['size'] / 1000000) > ((int)str_replace('M', '', ini_get('upload_max_filesize')))) {
                return [
                    'status' => 'error',
                    'msg' => 'Недопустимый размер файла'
                ];
            }

            // Проверим расширение файла
            if (!in_array($ext, $this->getValidFormats())) {
                return [
                    'status' => 'error',
                    'msg' => 'Недопустимый тип файла'
                ];
            }

            $name = $_FILES['file']['name'];
            $targetFile = $targetPath . $name;

            move_uploaded_file($tempFile, $targetFile);
            /* На случай, если сервер выставляет на загруженные файлы права 0600*/
            chmod($targetFile, 0755);

            if ($session->has('img_add')) {
                $img = unserialize($session->get('img_add'));
                $img[$key][] = $name;
            } else {
                $img[$key] = [$name];
            }

            $session->set('img_add', serialize($img));

            return [
                'status' => 'success',
                'msg' => $name
            ];
        }

        return [
            'status' => 'error',
            'msg' => 'Ошибка загрузки файла'
        ];
    }

    /**
     * @param       $img
     * @param bool  $crop
     * @param array $img_data
     * @param bool  $link - если вместо имени файла испольльзуется ссылка
     * @return string
     */
    public function appendUpload($img, $crop = false, $img_data = [], $link = false)
    {
        $config = \Pyshnov::config();

        $cache_dir = dirname(\Pyshnov::kernel()->getCacheDir()) . '/img/';
        $path = \Pyshnov::root() . \Pyshnov::DATA_IMG_DIR . '/';

        $max_filesize = (int)str_replace('M', '', ini_get('upload_max_filesize'));

        $i = 1;

        foreach ($img as $item) {

            if ($link) {
                $arr = explode('/', $item);
                $file_name = array_pop($arr);
                file_put_contents($cache_dir . $file_name, file_get_contents($item));
                $item = $file_name;
            }

            if (!file_exists($cache_dir . $item))
                continue;

            if (filesize($cache_dir . $item) / (1024 * 1024) > $max_filesize)
                continue;

            $arr = explode('.', $item); // Разбиваем строку на подстроки
            $ext = strtolower(array_pop($arr)); // Получим тип файла

            // Проверим расширение файла
            if (in_array($ext, $this->getValidFormats())) {

                $big_width = $config->get('data_image_big_width');
                $big_height = $config->get('data_image_big_height');
                $preview_width = $config->get('data_image_preview_width');
                $preview_height = $config->get('data_image_preview_height');

                $name = time() . '.' . uniqid((string)rand(10, 100)) . $i . '.' . $ext;

                try {
                    $image = new Image($cache_dir . $item);

                    if ($crop)
                        $image->crop($image->getWidth(), $image->getHeight() - $config->get('avito_crop'));

                    $image->resize($big_width, $big_height)
                        ->save($path . $name);

                    $image->thumbnail($preview_width, $preview_height, $config->get('data_thumbnail_position'))
                        ->save($path . 'thumbs/' . $name);

                    // Если в настройках разрешено накладывать водяной знак
                    if ($config->get('is_watermark')) {
                        $image = new Image($path . $name);
                        $image->watermark(\Pyshnov::root() .
                            '/uploads/watermark/watermark.png',
                            'bottom right',
                            \Pyshnov::config()->get('watermark_opacity'),
                            \Pyshnov::config()->get('watermark_offset_x'),
                            \Pyshnov::config()->get('watermark_offset_y'))
                            ->save($path . $name);
                    }

                    $img_data[] = [
                        'name' => $name,
                        'alt' => ''
                    ];

                    // Удаляем файл
                    @unlink($cache_dir . $item);

                    $i++;

                } catch (ImageException $e) {

                    //$error[] = $e->getMessage();
                    $log = new FileLogger(\Pyshnov::kernel()->getLogDir() . '/img_log.txt');
                    $log->error($e->getMessage());

                }

            }
        }

        return !empty($img_data) ? serialize($img_data) : '';

    }
}