<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\config\Form;


use Pyshnov\Core\DB\DB;
use Pyshnov\Core\Helpers\Directory;
use Pyshnov\form\Form;

class ConfigForm extends Form
{
    public function compileForm($type, $name, $value)
    {
        $el = '';
        switch ($type) {
            case 'text':
                $el = $this->addInput($name)->setClass('form-control')->setValue($value)->render();
                break;
            case 'textarea':
                $el = $this->addTextarea($name)->setClass('form-control')->setContent($value)->render();
                break;
            case 'checkbox':
                $el = $this->compileCheckboxElement($name, $value);
                break;
            case 'language':
                $option = [];
                foreach (\Pyshnov::language()->getLocale() as $item) {
                    $option[$item] = $item;
                }
                $el = $this->addSelect($name)
                    ->setOptions($option)
                    ->setValue($value)
                    ->render();
                break;
            case 'template':
                $option = [];
                foreach (Directory::getDirsList('templates') as $item) {
                    $option[$item] = $item;
                }
                $el = $this->addSelect($name)
                    ->setOptions($option)
                    ->setValue($value)
                    ->render();
                break;
            case 'editor':
                $el = $this->compileEditorElement($name, $value);
                break;
            case 'select':
                $el = $this->compileSelectElement($name, $value);
                break;
        }

        return $el;
    }

    public function compileEditorElement($name, $value)
    {
        $html = '<select name="' . $name . '" id="' . $name . '">';
        $html .= '<option value="none">Не использовать</option>';
        foreach (Directory::getDirsList('editor') as $item) {
            $html .= '<option value="' . $item . '"' . (($value == $item) ? ' selected' : '') . '>' . $item . '</option>';
        }
        $html .= '</select>';

        return $html;

    }

    /**
     * @param $name
     * @param $value
     * @return string
     */
    public function compileSelectElement($name, $value)
    {
        $rows = DB::select('value', DB_PREFIX . '_operation_type')
            ->where('name', '=', $name)
            ->limit(1)
            ->execute()
            ->fetchObject();

        if($rows) {
            $option = [];
            preg_match_all('/\{[^\}]+\}/', $rows->value, $matches);
            if(count($matches)) {
                foreach($matches[0] as $item){
                    $item = str_replace(['{', '}'], '', $item);
                    $res = explode('~', $item);
                    $option[$res[0]] = $res[1];
                }
            }
            $html = $this->addSelect($name)->setOptions($option)->setValue($value)->render();
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * Checkbox
     * @param $name
     * @param $value
     * @return string
     */
    public function compileCheckboxElement($name, $value)
    {
        $html = '<div class="switch">';
        $html .= '<div class="onoffswitch">';
        $html .= $this->addCheckbox($name)
            ->setClass('onoffswitch-checkbox')
            ->setId($name)
            ->setValue(1)
            ->setIsChecked($value)
            ->setLabelFor($name)
            ->setLabelClass('onoffswitch-label')
            ->setLabelSpan('<span class="onoffswitch-inner"></span><span class="onoffswitch-switch"></span>')
            ->render();
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}