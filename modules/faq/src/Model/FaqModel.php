<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\faq\Model;


use Pyshnov\Core\DB\DB;
use Pyshnov\system\Model\BaseModel;

class FaqModel extends BaseModel
{

    public function getFaq()
    {
        $query = DB::select('id, question, answer', DB_PREFIX . '_faq')
            ->where('active', '=', 1)
            ->orderBy('id', 'DESC')
            ->execute()->fetchAll();

        return [
            'rows' => $query
        ];
    }

    public function getFaqAll()
    {
        $query = DB::select('id, question, answer, active', DB_PREFIX . '_faq')
            ->orderBy('sort')
            ->execute()->fetchAll();

        return [
            'rows' => $query
        ];
    }

    /**
     * @param null $id
     * @return bool
     */
    public function getFaqById($id = null)
    {
        if(null === $id)
            $id = $this->request()->attributes->get('id');

        if(!$id)
            return false;

        $query = DB::select('*', DB_PREFIX . '_faq')
            ->where('id', '=', $id)
            ->limit(1)
            ->execute()->fetch();

        if($query) {
            return $query;
        }

        return false;
    }

    public function update($id = null)
    {
        if(null === $id)
            $id = $this->request()->attributes->get('id');

        if(!$id)
            return false;

        $active = $this->request()->request->has('active') && $this->get('user')->isAdmin() ? $this->request()->request->get('active') : 0 ;

        $data = [
            'active' => $active,
            'author' => $this->request()->request->get('author') ?? '',
            'author_email' => $this->request()->request->get('author_email') ?? '',
            'question' => $this->request()->request->get('question') ?? '',
            'answer' => $this->request()->request->get('answer') ?? ''
        ];

        $stmt = DB::update($data, DB_PREFIX . '_faq')->where('id', '=', $id)->execute();

        if($stmt)
            return true;

        return false;
    }

}