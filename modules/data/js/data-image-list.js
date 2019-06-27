/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

$('.data-uploaded').on('click', '.btn', function (e) {
    e.preventDefault();
    var id = $('.data-uploaded').data('id'),
        function_name = $(this).data('action') + 'Image';

    DataImageList[function_name]($(this), id);

});

var DataImageList = {
    list: null,
    item: null,
    position: null,
    prepare: function (el) {
        var list = el.closest('.data-uploaded__list'),
            item = el.closest('.data-uploaded__item'),
            item_all = list.find('.data-uploaded__item'),
            position = item_all.index(item);

        this.list = list;
        this.item = item;
        this.position = position;

    },
    upImage: function (el, id) {

        this.prepare(el);

        if (this.position > 0) {
            var prev = this.item.prev('.data-uploaded__item');

            $.ajax({
                type: "POST",
                url: "/ajax/data/dropzone/",
                dataType: "json",
                data: {
                    action: "imageWork",
                    do: 'reorder',
                    reorder: 'up',
                    id: id,
                    position: DataImageList.position
                },
                success: function (res) {
                    if (res.status === 'success') {
                        DataImageList.item.fadeOut('slow', function () {
                            DataImageList.item.insertBefore(prev).fadeIn('slow');
                        });
                    }
                }
            });
        }
    },
    downImage: function (el, id) {

        this.prepare(el);

        if ((this.position + 1) < this.list.find('.data-uploaded__item').length) {

            var next = this.item.next('.data-uploaded__item');

            $.ajax({
                type: "POST",
                url: "/ajax/data/dropzone/",
                dataType: "json",
                data: {
                    action: "imageWork",
                    do: 'reorder',
                    reorder: 'down',
                    id: id,
                    position: DataImageList.position
                },
                success: function (res) {
                    if (res.status === 'success') {
                        DataImageList.item.fadeOut('slow', function () {
                            DataImageList.item.insertAfter(next).fadeIn('slow');
                        });
                    }
                }
            });
        }
    },
    mainImage: function (el, id) {

        this.prepare(el);

        if (this.position > 0) {
            $.ajax({
                type: "POST",
                url: "/ajax/data/dropzone/",
                dataType: "json",
                data: {
                    action: "imageWork",
                    do: 'main',
                    id: id,
                    position: DataImageList.position
                },
                success: function (res) {
                    if (res.status === 'success') {
                        DataImageList.item.fadeOut('slow', function () {
                            DataImageList.item.prependTo(DataImageList.list).fadeIn('slow');
                        });
                    }
                }
            });
        }
    },
    deleteImage: function (el, id) {

        this.prepare(el);

        bootbox.confirm({
            title: ' ',
            message: 'Хотите удалить фото?',
            size: 'small',
            buttons: {
                confirm: {
                    className: 'btn-default'
                },
                cancel: {
                    label: 'Отмена'
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/data/dropzone/",
                        dataType: "json",
                        data: {
                            action: "imageWork",
                            do: 'delete',
                            id: id,
                            position: DataImageList.position
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                DataImageList.item.fadeOut('slow', function () {
                                    DataImageList.item.remove();
                                });
                            }
                        }
                    });
                }
            }
        });

    },
    clearImage: function (el, id) {
        bootbox.confirm({
            title: ' ',
            message: 'Хотите удалить все фото?',
            size: 'small',
            buttons: {
                confirm: {
                    className: 'btn-default'
                },
                cancel: {
                    label: 'Отмена'
                }
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/data/dropzone/",
                        dataType: "json",
                        data: {
                            action: "imageWork",
                            do: 'delete_all',
                            id: id
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                $('.data-uploaded').remove();
                            }
                        }
                    });
                }
            }
        });
    }
};
