<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\data\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;
use Pyshnov\data\Form\Upload;

class Dopzone extends AjaxResponse
{
    public function runAction()
    {
        $res = false;

        if ($action = $this->request()->get('action', false)) {

            if (method_exists($this, $action)) {
                $res = $this->$action();
            }
        }

        return $this->render($res);
    }

    public function loadImages()
    {
        $upload = new Upload();
        $res = $upload->loadImage($this->request()->query->get('key'), $this->session());

        if ($res['status'] == 'error') {
            $this->setMessageError($res['msg']);

            return false;
        }

        $this->setMessageSuccess($res['msg']);

        return true;
    }

    public function deleteImage()
    {

        $key = $this->request()->query->get('key');
        $name = $this->request()->query->get('name');
        $file = dirname(\Pyshnov::kernel()->getCacheDir()) . '/img/' . $name;
        @unlink($file);

        $arr = unserialize($this->session()->get('img_add'));
        $img_key = array_flip($arr[$key])[$name];

        unset($arr[$key][$img_key]);

        $this->session()->set('img_add', serialize([$key => $arr[$key]]));

        return true;
    }

    public function imageWork()
    {

        if ($this->get('user')->isAnonymous())
            return false;

        $res = false;

        $user_id = $this->get('user')->getId();

        $admin_mode = $this->get('user')->isAdmin() || $this->get('user')->isModerator();

        switch ($this->postParam()->get('do')) {

            case 'reorder':
                $position = $this->postParam()->get('position');
                $id = $this->postParam()->get('id');
                $reorder = $this->postParam()->get('reorder');

                $new_position = 0;

                if ($reorder == 'up') {
                    $new_position = $position - 1;
                } elseif ($reorder == 'down') {
                    $new_position = $position + 1;
                }

                $query = DB::select('image', DB_PREFIX . '_data');

                if ($admin_mode) {
                    $query->where('id', '=', $id);

                } else {
                    $query->multiWhere(['id' => $id, 'user_id' => $user_id]);
                }

                $stmt = $query->limit(1)->execute();

                if ($row = $stmt->fetch()) {
                    $uploads = unserialize($row['image']);

                    if (!isset($uploads[$position]) || !isset($uploads[$new_position])) {
                        break;
                    }

                    $temp = $uploads[$position];
                    $uploads[$position] = $uploads[$new_position];
                    $uploads[$new_position] = $temp;

                    $stmt = DB::update(['image' => serialize($uploads)], DB_PREFIX . '_data')
                        ->where('id', '=', $id)
                        ->execute();

                    if ($stmt)
                        $res = true;

                }

                break;

            case 'main':

                $position = $this->postParam()->get('position');
                $id = $this->postParam()->get('id');


                $query = DB::select('image', DB_PREFIX . '_data');

                if ($admin_mode) {
                    $query->where('id', '=', $id);

                } else {
                    $query->multiWhere(['id' => $id, 'user_id' => $user_id]);
                }

                $stmt = $query->limit(1)->execute();

                if ($row = $stmt->fetch()) {
                    $uploads = unserialize($row['image']);

                    if (!isset($uploads[$position]))
                        break;

                    $temp = $uploads[$position];
                    unset($uploads[$position]);
                    array_unshift($uploads, $temp);
                    $uploads = array_values($uploads);

                    $stmt = DB::update(['image' => serialize($uploads)], DB_PREFIX . '_data')
                        ->where('id', '=', $id)
                        ->execute();

                    if ($stmt)
                        $res = true;
                }

                break;

            case 'delete' :

                $position = $this->postParam()->get('position');
                $id = $this->postParam()->get('id');

                $query = DB::select('image', DB_PREFIX . '_data');

                if ($admin_mode) {
                    $query->where('id', '=', $id);
                } else {
                    $query->multiWhere(['id' => $id, 'user_id' => $user_id]);
                }

                $stmt = $query->limit(1)->execute();

                if ($row = $stmt->fetch()) {
                    $uploads = unserialize($row['image']);

                    if (!isset($uploads[$position]))
                        break;

                    $path = \Pyshnov::root() . \Pyshnov::DATA_IMG_DIR . '/';
                    $name = $uploads[$position]['name'];
                    @unlink($path . $name);
                    @unlink($path . 'thumbs/' . $name);
                    unset($uploads[$position]);
                    $uploads = array_values($uploads);
                    if (!empty($uploads))
                        $uploads = serialize($uploads);
                    $stmt = DB::update(['image' => $uploads], DB_PREFIX . '_data')
                        ->where('id', '=', $id)
                        ->execute();

                    if ($stmt)
                        $res = true;
                }

                break;

            case 'delete_all':

                $id = $this->postParam()->get('id');

                $query = DB::select('image', DB_PREFIX . '_data');

                if ($admin_mode) {
                    $query->where('id', '=', $id);
                } else {
                    $query->multiWhere(['id' => $id, 'user_id' => $user_id]);
                }

                $stmt = $query->limit(1)->execute();

                if ($row = $stmt->fetch()) {
                    $uploads = unserialize($row['image']);

                    $path = \Pyshnov::root() . \Pyshnov::DATA_IMG_DIR . '/';

                    foreach ($uploads as $item) {
                        @unlink($path . $item['name']);
                        @unlink($path . 'thumbs/' . $item['name']);
                    }

                    DB::update(['image' => ''], DB_PREFIX . '_data')
                        ->where('id', '=', $id)
                        ->execute();

                    $res = true;
                }

                break;
        }

        return $res;
    }


}