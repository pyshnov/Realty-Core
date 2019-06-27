<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\reviews\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class AjaxAction extends AjaxResponse
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

    public function addReview()
    {
        parse_str($this->request()->request->get('s'), $output);

        $approved = isset($output['approved']) && $this->get('user')->isAdmin() ? (int)$output['approved'] :
            $this->config()->get('faq.premoderate') ? 0 : 1;

        $data = [
            'approved' => $approved,
            'author_email' => $output['author_email'] ?? '',
            'author' => $output['author'] ?? '',
            'review' => $output['review'] ?? '',
            'date' => date('Y-m-d H:i:s')
        ];

        $stmt = DB::insert($data, DB_PREFIX . '_reviews')->execute();

        if($stmt) {
            $this->setMessageSuccess('Спасибо за Ваш отзыв! В ближайшее время он появится у нас на сайте.');
            return true;
        }

        return false;
    }

    public function updateStatus()
    {
        $id = $this->request()->request->get('id');
        $value = $this->request()->request->get('value');

        $stmt = DB::update(['approved' => $value], DB_PREFIX . '_reviews')->where('id', '=', $id)->execute();

        if($stmt) {
            return true;
        }
        return false;
    }

    public function remove()
    {
        $id = $this->request()->request->get('id');

        $stmt = DB::delete(DB_PREFIX . '_reviews')->where('id', '=', $id)->execute();

        if($stmt) {
            return true;
        }
        return false;
    }
}