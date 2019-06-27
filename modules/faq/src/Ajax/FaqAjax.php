<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\faq\Ajax;


use Pyshnov\Core\Ajax\AjaxResponse;
use Pyshnov\Core\DB\DB;

class FaqAjax extends AjaxResponse
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

    public function addFaq()
    {
        parse_str($this->request()->request->get('s'), $output);

        $query = DB::select('MAX(sort) AS sort', DB_PREFIX . '_faq')
            ->execute()->fetch();

        $active = isset($output['active']) && $this->get('user')->isAdmin() ? (int)$output['active'] :
            $this->config()->get('faq.premoderate') ? 0 : 1;

        $data = [
            'active' => $active,
            'author_email' => $output['author_email'] ?? '',
            'author' => $output['author'] ?? '',
            'question' => $output['question'] ?? '',
            'answer' => $output['answer'] ?? '',
            'sort' => $query['sort'] + 1
        ];

        $stmt = DB::insert($data, DB_PREFIX . '_faq')->execute();

        if($stmt) {
            $this->setMessageSuccess('Текст Вашего вопроса успешно отправлен.');
            return true;
        }

        return false;
    }

    public function updateStatus()
    {
        $id = $this->request()->request->get('id');
        $value = $this->request()->request->get('value');

        $stmt = DB::update(['active' => $value], DB_PREFIX . '_faq')->where('id', '=', $id)->execute();

        if($stmt) {
            return true;
        }

        return false;
    }

    public function remove()
    {
        $id = $this->request()->request->get('id');

        $stmt = DB::delete(DB_PREFIX . '_faq')->where('id', '=', $id)->execute();

        if($stmt) {
            return true;
        }

        return false;
    }
}