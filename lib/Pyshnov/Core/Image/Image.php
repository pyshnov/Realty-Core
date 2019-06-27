<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Image;


use Pyshnov\Core\Image\Exception\ImageException;

class Image
{
    protected $image;

    protected $width;
    protected $height;
    protected $type;
    protected $modificators;
    const RESIZE = 1;
    const CROP = 2;
    const WATERMARK = 3;
    const CROP_RESIZE = 4;
    const THUMBNAIL = 5;
    const ROTATE = 6;
    const QUALITY_JPG = 75;
    const QUALITY_PNG = 3;

    /**
     * Image constructor.
     *
     * @param null $img - файл изображения или GD ресурс
     */
    public function __construct($img = null)
    {
        if ($img)
            $this->load($img);
    }

    /**
     * Загрузка изображения из файла или GD-ресурса
     *
     * @param $img
     */
    public function load($img)
    {
        $this->reset();
        $this->image = $this->makeGD($img);
        $this->updateDimensions();
    }

    /**
     * Сбросим все настройки
     */
    protected function reset()
    {
        $this->type = null;
        $this->width = null;
        $this->height = null;
    }

    /**
     * Создание ресурса GD из файла
     *
     * @param $img
     * @return resource
     */
    protected function makeGD($img)
    {
        if (is_resource($img))
            return $img;

        if (!is_file($img))
            ImageException::ThrowError(ImageException::FILE_NOT_EXIST, $img);

        $this->type = $this->getTypeByFile($img);
        switch ($this->type) {
            case IMG_JPEG:
                return imagecreatefromjpeg($img);
                break;
            case IMG_GIF:
                return imagecreatefromgif($img);
                break;
            case IMG_PNG:
                return imagecreatefrompng($img);
                break;
            default:
                $file = file_get_contents($img);

                return imagecreatefromstring($file);
        }
    }

    /**
     * Получение типа файла IMG_* по расширению
     *
     * @param $file
     * @return bool|int
     */
    protected function getTypeByFile($file)
    {
        switch (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            case 'jpg':
            case 'jpeg':
                $type = IMG_JPEG;
                break;
            case 'png':
                $type = IMG_PNG;
                break;
            case 'gif':
                $type = IMG_GIF;
                break;
            default:
                $type = false;
        }

        return $type;
    }

    /**
     * Обновить размеры текущего изображения
     *
     * @return $this
     */
    protected function updateDimensions()
    {
        $this->width = $this->image ? imagesx($this->image) : null;
        $this->height = $this->image ? imagesy($this->image) : null;

        return $this;
    }

    /**
     * Пропорциональное изменение размера - добавляется в цепочку модификаторов
     *
     * @param $width
     * @param $height
     * @return $this
     */
    public function resize($width, $height)
    {
        $this->modificators[] = [
            'method' => static::RESIZE,
            'width' => $width,
            'height' => $height
        ];

        return $this;
    }

    /**
     * Обрезка изображения - добавляется в цепочку модификаторов.
     * Можно чередовать с RESIZE и WATERMARK
     *
     * @param      $width
     * @param      $height
     * @param null $x - отступ по горизонтали: left|right|center|±int - в пикселях|"int%" - в процентах
     * @param null $y - отступ по вертикали: top|bottom|center|±int - в пикселях|"int%" - в процентах
     * @return $this
     */
    public function crop($width, $height, $x = null, $y = null)
    {
        $this->modificators[] = [
            'method' => static::CROP,
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y
        ];

        return $this;
    }

    /**
     * Обрезка изображения с предварительным сжатием по максимальной стороне - добавляется в цепочку модификаторов.
     *
     * @param      $width
     * @param      $height
     * @param null $x - отступ по горизонтали: left|right|center|±int - в пикселях|"int%" - в процентах
     * @param null $y - отступ по вертикали: top|bottom|center|±int - в пикселях|"int%" - в процентах
     * @return $this
     */
    public function cropResize($width, $height, $x = null, $y = null)
    {
        $this->modificators[] = [
            'method' => static::CROP_RESIZE,
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y
        ];

        return $this;
    }

    /**
     * Превью изображения
     *
     * @param        $width
     * @param        $height
     * @param string $focal
     * @return $this
     */
    public function thumbnail($width, $height, $focal = 'center')
    {
        $this->modificators[] = [
            'method' => static::THUMBNAIL,
            'width' => $width,
            'height' => $height,
            'focal' => $focal
        ];

        return $this;
    }

    /**
     * Водяной знак - добавляется в цепочку модификаторов.
     * Можно чередовать с RESIZE и CROP
     *
     * @param        $file
     * @param string $position
     * @param int    $opacity
     * @param int    $x
     * @param int    $y
     * @return $this
     */
    public function watermark($file, $position = 'center', $opacity = 1, $x = 0, $y = 0)
    {
        $this->modificators[] = [
            'method' => static::WATERMARK,
            'file' => $file,
            'position' => $position,
            'opacity' => $opacity,
            'x' => $x,
            'y' => $y
        ];

        return $this;
    }

    /**
     * Вращаем изображение
     *
     * @param        $angle
     * @param string $bg_color
     * @return $this
     */
    public function rotate($angle, $bg_color = '#000000')
    {
        $this->modificators[] = [
            'method' => static::ROTATE,
            'angle' => $angle,
            'bg_color' => $bg_color
        ];

        return $this;
    }

    /**
     * Вывести изображение в STDOUT
     *
     * @param null $quality - качество изображения, для JPG: от 0 до 100, для PNG: от 9 до 0
     * @return bool
     */
    public function output($quality = null)
    {
        $this->apply();

        return $this->makeImage(null, $quality);
    }

    /**
     * Получить GD-ресурс
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Ширина изображения
     *
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Высота изображения
     *
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Тип загруженного изображения
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Выполнение изменения размера
     *
     * @param $width
     * @param $height
     * @return Image
     */
    protected function doResize($width, $height)
    {
        list ($x, $y) = static::CalculateResize($width, $height, $this->getWidth(), $this->getHeight());
        // Прозрачность
        $new_image = imagecreatetruecolor($x, $y);
        imagealphablending($new_image, true);
        imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 255, 255, 255, 127));
        imagesavealpha($new_image, true);
        // Меняем размер
        if (!imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $x, $y, $this->getWidth(), $this->getHeight())) {
            ImageException::ThrowError(ImageException::RESIZE);
        }

        $this->image = $new_image;

        return $this->updateDimensions();
    }

    /**
     * Выполнение обрезки изображения
     *
     * @param $width
     * @param $height
     * @param $x
     * @param $y
     * @return Image
     */
    protected function doCrop($width, $height, $x, $y)
    {
        if ($width > $this->getWidth()) {
            $width = $this->getWidth();
            $x = 0;
        }
        if ($height > $this->getHeight()) {
            $height = $this->getHeight();
            $y = 0;
        }

        // Прозрачность
        $new_image = imagecreatetruecolor($width, $height);
        imagealphablending($new_image, true);
        imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 255, 255, 255, 127));
        imagesavealpha($new_image, true);

        if (!imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height)) {
            ImageException::ThrowError(ImageException::CROP);
        }

        $this->image = $new_image;

        return $this->updateDimensions();
    }

    /**
     * Выполнение ужатия и обрезки изображения
     *
     * @param $width
     * @param $height
     * @param $x
     * @param $y
     * @return Image
     */
    protected function doCropResize($width, $height, $x = null, $y = null)
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        if (($h / ($w / $width)) < $height) {
            $this->doResize(null, $height);
        } else {
            $this->doResize($width, null);
        }

        return $this->doCrop($width, $height, $x, $y);
    }

    /**
     * Выполнение ужатия и обрезки изображения
     *
     * @param $width
     * @param $height
     * @param $focal
     * @return Image
     */
    protected function doThumbnail($width, $height, $focal)
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        if (($h / ($w / $width)) < $height) {
            $this->doResize(null, $height);
        } else {
            $this->doResize($width, null);
        }

        switch (strtolower($focal)) {
            case 'top':
                $left = floor(($this->width / 2) - ($width / 2));
                $top = 0;
                break;
            case 'bottom':
                $left = floor(($this->width / 2) - ($width / 2));
                $top = $this->height - $height;
                break;
            case 'left':
                $left = 0;
                $top = floor(($this->height / 2) - ($height / 2));
                break;
            case 'right':
                $left = $this->width - $width;
                $top = floor(($this->height / 2) - ($height / 2));
                break;
            case 'top left':
                $left = 0;
                $top = 0;
                break;
            case 'top right':
                $left = $this->width - $width;
                $top = 0;
                break;
            case 'bottom left':
                $left = 0;
                $top = $this->height - $height;
                break;
            case 'bottom right':
                $left = $this->width - $width;
                $top = $this->height - $height;
                break;
            case 'center':
            default:
                $left = floor(($this->width / 2) - ($width / 2));
                $top = floor(($this->height / 2) - ($height / 2));
        }

        return $this->doCrop($width, $height, $left, $top);

    }

    /**
     * Наложение водяного знака
     *
     * @param $file
     * @param $position
     * @param $opacity
     * @param $x
     * @param $y
     * @return $this
     */
    protected function doWatermark($file, $position = 'center', $opacity = 1, $x, $y)
    {
        $watermark = new Image($file);

        $opacity = $opacity * 100;

        // Определяем позицию
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x;
                $y = 0 + $y;
                break;
            case 'top right':
                $x = $this->getWidth() - $watermark->getWidth() + $x;
                $y = 0 + $y;
                break;
            case 'top':
                $x = ($this->getWidth() / 2) - ($watermark->getWidth() / 2) + $x;
                $y = 0 + $y;
                break;
            case 'bottom left':
                $x = 0 + $x;
                $y = $this->getHeight() - $watermark->getHeight() + $y;
                break;
            case 'bottom right':
                $x = $this->getWidth() - $watermark->getWidth() + $x;
                $y = $this->getHeight() - $watermark->getHeight() + $y;
                break;
            case 'bottom':
                $x = ($this->getWidth() / 2) - ($watermark->getWidth() / 2) + $x;
                $y = $this->getHeight() - $watermark->getHeight() + $y;
                break;
            case 'left':
                $x = 0 + $x;
                $y = ($this->height / 2) - ($watermark->getHeight() / 2) + $y;
                break;
            case 'right':
                $x = $this->getWidth()->width - $watermark->getWidth() + $x;
                $y = ($this->height / 2) - ($watermark->getHeight() / 2) + $y;
                break;
            case 'center':
            default:
                $x = ($this->getWidth() / 2) - ($watermark->getWidth() / 2) + $x;
                $y = ($this->getHeight() / 2) - ($watermark->getHeight() / 2) + $y;
                break;
        }

        $this->imagecopymerge_alpha($this->getImage(), $watermark->getImage(), $x, $y, 0, 0, $watermark->getWidth(), $watermark->getHeight(), $opacity);

        return $this;
    }

    public function doRotate($angle, $bg_color)
    {
        $rgba = $this->normalize_color($bg_color);
        $bg_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $new_image = imagerotate($this->image, -($this->keep_within($angle, -360, 360)), $bg_color);
        imagesavealpha($new_image, true);
        imagealphablending($new_image, true);

        $this->image = $new_image;

        return $this->updateDimensions();
    }

    /**
     * @param $color
     * @return array|bool
     */
    protected function normalize_color($color)
    {
        if (is_string($color)) {
            $color = trim($color, '#');
            if (strlen($color) == 6) {
                list($r, $g, $b) = array(
                    $color[0] . $color[1],
                    $color[2] . $color[3],
                    $color[4] . $color[5]
                );
            } elseif (strlen($color) == 3) {
                list($r, $g, $b) = array(
                    $color[0] . $color[0],
                    $color[1] . $color[1],
                    $color[2] . $color[2]
                );
            } else {
                return false;
            }

            return array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b),
                'a' => 0
            );
        } elseif (is_array($color) && (count($color) == 3 || count($color) == 4)) {
            if (isset($color['r'], $color['g'], $color['b'])) {
                return array(
                    'r' => $this->keep_within($color['r'], 0, 255),
                    'g' => $this->keep_within($color['g'], 0, 255),
                    'b' => $this->keep_within($color['b'], 0, 255),
                    'a' => $this->keep_within(isset($color['a']) ? $color['a'] : 0, 0, 127)
                );
            } elseif (isset($color[0], $color[1], $color[2])) {
                return array(
                    'r' => $this->keep_within($color[0], 0, 255),
                    'g' => $this->keep_within($color[1], 0, 255),
                    'b' => $this->keep_within($color[2], 0, 255),
                    'a' => $this->keep_within(isset($color[3]) ? $color[3] : 0, 0, 127)
                );
            }
        }

        return false;
    }

    /**
     * @param $value
     * @param $min
     * @param $max
     * @return mixed
     */
    protected function keep_within($value, $min, $max)
    {
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }

        return $value;
    }


    /**
     * @param $dst_im
     * @param $src_im
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $src_w
     * @param $src_h
     * @param $pct
     */
    protected function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        // Get image width and height and percentage
        $pct /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        // Turn alpha blending off
        imagealphablending($src_im, false);
        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }
        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                // Get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                // Calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // Get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                // Set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }
        // Copy it
        imagesavealpha($dst_im, true);
        imagealphablending($dst_im, true);
        imagesavealpha($src_im, true);
        imagealphablending($src_im, true);
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }

    /**
     * Расчет пропорционального изменения размеров изображения.
     *
     * @param $width
     * @param $height
     * @param $orig_width
     * @param $orig_height
     * @return array
     */
    public static function CalculateResize($width, $height, $orig_width, $orig_height)
    {
        $ratio = $orig_width / $orig_height;
        if (!$width) {
            $width = $orig_width;
        }
        if (!$height) {
            $height = $orig_height;
        }
        //Если изначальные размеры меньше необходимых - ничего не меняем
        if ($width >= $orig_width && $height >= $orig_height) {
            return [
                $orig_width,
                $orig_height
            ];
        }
        $new_width = $width;
        $new_height = $height;
        if ($width < $orig_width) {
            $new_width = $width;
            $new_height = floor($width / $ratio);
        }
        if ($new_height > $height) {
            $new_width = floor($new_width * ($new_height / $height));
            $new_height = $height;
        }
        if ($new_height < $orig_height) {
            $new_width = floor($new_height * $ratio);
        }

        return [
            $new_width,
            $new_height
        ];
    }

    /**
     * Применяем модификаторы
     *
     * @return $this
     */
    public function apply()
    {
        if (!empty($this->modificators)) {

            foreach ($this->modificators as $k => $mod) {
                switch ($mod['method']) {
                    case static::CROP:
                        $this->doCrop($mod['width'], $mod['height'], $mod['x'], $mod['y']);
                        break;
                    case static::ROTATE:
                        $this->doRotate($mod['angle'], $mod['bg_color']);
                        break;
                    case static::RESIZE:
                        $this->doResize($mod['width'], $mod['height']);
                        break;
                    case static::CROP_RESIZE:
                        $this->doCropResize($mod['width'], $mod['height'], $mod['x'], $mod['y']);
                        break;
                    case static::WATERMARK:
                        $this->doWatermark($mod['file'], $mod['position'], $mod['opacity'], $mod['x'], $mod['y']);
                        break;
                    case static::THUMBNAIL:
                        $this->doThumbnail($mod['width'], $mod['height'], $mod['focal']);
                        break;
                }
                unset($this->modificators[$k]);

            }
        }

        return $this;
    }

    /**
     * Формирование результирующего изображения
     *
     * @param null $file
     * @param null $quality
     * @return bool
     */
    protected function makeImage($file = null, $quality = null)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        switch ($this->getTypeByFile($file)) {
            case IMG_JPEG:
                return imagejpeg($this->image, $file, $quality ?: static::QUALITY_JPG);
                break;
            case IMG_PNG:
                return imagepng($this->image, $file, $quality ?: static::QUALITY_PNG);
                break;
            case IMG_GIF:
                return imagegif($this->image, $file);
                break;
            default:
                ImageException::ThrowError(ImageException::BAD_TYPE);

                return false;
        }
    }

    /**
     * Сохранить в файл
     *
     * @param      $file
     * @param null $quality - качество изображения, для JPG: от 0 до 100, для PNG: от 9 до 0
     * @return bool
     */
    public function save($file, $quality = null)
    {
        $this->apply();

        return $this->makeImage($file, $quality);
    }
}