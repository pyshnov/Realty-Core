<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\data\Form;


use Pyshnov\Core\DB\DB;
use Pyshnov\form\Form;
use Pyshnov\system\Model\ReferenceModel;

class DataForm extends Form
{
    public function getDropzone()
    {

        $key = \Pyshnov::session()->get('key');

        $html = '<script src="/core/assets/dropzone/dropzone.min.js"></script>';
        $html .= '
                            <link rel="stylesheet" type="text/css" href="/core/assets/dropzone/dropzone.min.css">';
        $html .= '
                            <script type="text/javascript">';
        $html .= '
                                $(document).ready(function() {';
        $html .= '
                                    var addDropzone = new Dropzone("#addDropzone", {';
        $html .= '
                                        maxFilesize: 5,';
        $html .= '
                                        url: "/ajax/data/dropzone/?action=loadImages&key=' . $key . '",';
        $html .= '
                                        addRemoveLinks: true,';
        $html .= '
                                        dictRemoveFile: "Удалить",';
        $html .= '
                                        maxFiles: 20';
        $html .= '
                                     });';
        $html .= '
                                    addDropzone.on("complete", function() {';
        $html .= '
                                        if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){';
        $html .= '
                                            var form=$(this.element).parents("form");';
        $html .= '
                                            form.find("[type=submit]").prop("disabled", false);';
        $html .= '
                                        }';
        $html .= '
                                    }).on("success", function(file, responce){';
        $html .= '
                                        if (responce.status == "error"){';
        $html .= '
                                            $(file.previewElement).remove();';
        $html .= '
                                        }else{';
        $html .= '
                                            var rem = $(file.previewElement).find(".dz-remove");';
        $html .= '
                                            rem.attr("data-dz-remove", responce.message);';
        $html .= '
                                            rem.on("click", function(){';
        $html .= '
                                                var url="/ajax/data/dropzone/?action=deleteImage&key=' . $key . '&name=" + $(this).attr("data-dz-remove");';
        $html .= '
                                                $.getJSON(url);';
        $html .= '
                                            });';
        $html .= '
                                        }';
        $html .= '
                                    }).on("addedfile", function(file){';
        $html .= '
                                        var form=$(this.element).parents("form");';
        $html .= '
                                        form.find("[type=submit]").prop("disabled", true);';
        $html .= '
                                    });';
        $html .= '
                                });';
        $html .= '
                            </script>';
        $html .= '
                            <div id="addDropzone" class="dropzone needsclick">';
        $html .= '
                                <div class="dz-message needsclick">';
        $html .= '
                                    Перетащите сюда файлы<br />';
        $html .= '
                                        <span class="note needsclick">(или выберите на компьютере)</span><br />';
        $html .= '
                                        <i class="fa fa-cloud-upload"></i>';
        $html .= '
                                </div>';
        $html .= '
                            </div>';
        return $html;
    }

    /**
     * @param $images
     * @param $id
     * @return string
     */
    public function uploadsImgForm($images, $id)
    {

        if(!empty($images)) {
            $html = '<div class="data-uploaded" data-id="' . $id . '">';
            $html .= '<a class="btn btn-link" href="#" data-action="clear">Удалить все фото</a>';
            $html .= '<div class="clearfix"></div>';
            $html .= '<ul class="data-uploaded__list">';
            foreach ($images as $item) {
                $html .= '<div class="data-uploaded__item">';
                $html .= '<li>';
                $html .= '<div class="data-uploaded__item__img">';
                $html .= '<img src="'. \Pyshnov::DATA_IMG_DIR . '/thumbs/' . $item['name'] . '" />';
                $html .= '</div>';
                $html .= '<a href="#" class="btn btn-small" title="Выше" data-action="up"><i class="fa fa-chevron-left"></i></a>';
                $html .= '<a href="#" class="btn btn-small" title="Сделать главной" data-action="main"><i class="fa fa-star-o"></i></a>';
                $html .= '<a href="#" class="btn btn-small" title="Ниже" data-action="down"><i class="fa fa-chevron-right"></i></a>';
                $html .= '<a href="#" class="btn btn-small" title="Удалить" data-action="delete"><i class="fa fa-trash-o"></i></a>';
                $html .= '</li>';
                $html .= '</div>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            $html .= '<script src="/core/modules/data/js/data-image-list.js"></script>';

            return $html;
        }
        return '';
    }

    public function getReference($type, $city_id, $select_id)
    {
        $stmt = DB::select('*', DB_PREFIX . '_' . $type)
            ->where('city_id', '=', $city_id)
            ->where('active', '=', 1);
        $arr = $stmt->orderBy('name')->execute()->fetchAll();

        if (!empty($arr)) {
            $option = [];
            foreach ($arr as $item) {
                $option[$item[$type . '_id']] = $item['name'];
            }

            return $this->addSelect($type, null, false, $option)
                ->addAttribute('title', \Pyshnov::t('system.' . $type . '_zero_select'))
                ->setValue($select_id);
        }

        return false;
    }
}