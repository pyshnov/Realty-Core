<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\config\Model;


use Pyshnov\config\Form\ConfigForm;
use Pyshnov\Core\DB\DB;
use Pyshnov\system\Model\BaseModel;

class ConfigModel extends BaseModel
{
    public function getConfig($space = 'core')
    {

        $rows = DB::select('*', DB_PREFIX . '_config')
            ->where('space', '=', $space)
            ->orderBy('sort')
            ->execute()
            ->fetchAll();

        $section = [];

        $form = new ConfigForm();

        foreach ($rows as $row) {

            $language = \Pyshnov::language();
            $row['description'] = $language->get($row['title'] . '_desc');
            $row['title'] = $language->get($row['title']);
            $row['element'] = $form->compileForm($row['type'], $row['setting'], $row['value']);

            $section[$row['section']][] = $row;
        }

        // Общие ставим на первое место
        $res['system'] = $section['system'];
        unset($section['system']);
        $res += $section;

        return $res;
    }
}