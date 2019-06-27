<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data\Block;


use Pyshnov\Core\Extension\Libraries;

class Complaint
{
    protected $id;

    public $enabled;

    protected $display;

    protected $option;

    public function __construct()
    {
        if ($this->enabled = (bool)\Pyshnov::config()->get('enable.data_complaint')) {
            $this->init();
        }
    }

    public function init()
    {
        // Если программой разрешино оставлять жалобы только авторизованным пользователя
        // проверям авторизован ли пользоваетель
        if (\Pyshnov::config()->get('data_complaint_authorized_user')) {
            $this->display = \Pyshnov::user()->isAuthenticated();
        } else {
            $this->display = true;
        }

        $this->id = (int)\Pyshnov::request()->attributes->get('id');

        // Если разрешено добавлять
        // проверяем сессию
        if ($this->display) {
            $session = \Pyshnov::request()->getSession();

            if ($comp_id = $session->get('data_complaint', false)) {
                if (in_array($this->id, $comp_id)) {
                    $this->display = false;
                }
            }
        }

        preg_match_all('/\{[^\}]+\}/', \Pyshnov::config()->get('data_complaint_option'), $matches);
        if (count($matches)) {
            foreach ($matches[0] as $item) {
                $item = str_replace(['{', '}'], '', $item);
                $res = explode('~', $item);
                $this->option[$res[0]] = $res[1];
            }
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isDisplay(): bool
    {
        return $this->display;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!$this->display || is_null($this->id)) {
            return '';
        }

        Libraries::addJs('/core/modules/data/js/complaint.js', 'data.realty.view');

        $html = '<div class="complain-ok"></div>';
        $html .= '<div id="complain" class="dropdown pull-left">';
        $html .= '<a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-lg fa-thumbs-o-down fa-fw"></i>Пожаловаться</a>';
        $html .= '<ul class="dropdown-menu" aria-labelledby="complain">';

        foreach ($this->option as $key => $value) {
            $html .= '<li><a href="#" data-id="' . $this->id . '">' . $value . '</a></li>';
        }

        $html .= '</ul>';
        $html .= '</div>';


        return $html;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->option ?? [];
}
}