/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var reference = (function () {
    return {
        data: {
            value: '',
            table: $('table').attr('id')
        },
        init: function () {

            //при нажатии на ячейку таблицы с классом edit
            $("table").on('dblclick', 'td.edit', function () {
                reference.loadReferenceData($(this))
            });

            //определяем нажатие кнопки на клавиатуре
            $('td.edit').keydown(function (e) {
                //проверяем какая была нажата клавиша и если была нажата клавиша Enter (код 13)
                if (e.which === 13) {
                    reference.updateReference($(this));
                }
            });

            $('#referenceAdd').validator().on('submit', function () {
                if ($(this).validator({}, 'check') > 0) {
                    setTimeout(function(){
                        toastr.error('Проверьте правильность введенных данных.', 'Ошибка!', {
                            timeOut: 5000,
                            closeButton: true,
                            progressBar: true
                        });
                    }, 1000);
                    return false;
                }
            });

        },
        loadReferenceData: function (el) {
            var data = reference.data;

            $('.ajax').html(data.value).removeClass('ajax');
            data.value = el.text();
            el.addClass('ajax');
            el.css('width', el.width());

            if (el.attr('data-load') !== undefined) {
                $.ajax({
                    type: "POST",
                    url: "/admin/ajax/system/",
                    dataType: "json",
                    data: {
                        action: 'loadReferenceData',
                        table: el.data('load'),
                        value: data.value
                    },
                    beforeSend: function () {
                        $('.ajax').html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            el.html(res.data);
                            $('.editSelect').selectpicker('toggle').on('changed.bs.select', function () {

                                var id = el.closest('tr').data('id'),
                                    value = $(this).val(),
                                    text = $('option:selected', this).text();

                                $.ajax({
                                    type: "POST",
                                    url: "/admin/ajax/system/",
                                    dataType: "json",
                                    data: {
                                        action: 'updateReference',
                                        table: reference.data.table,
                                        id: id,
                                        field: el.data('load') + '_id',
                                        value: value
                                    },
                                    beforeSend: function () {
                                       el.html('<i class="fa fa-spinner fa-spin"></i>');
                                    },
                                    success: function (data) {
                                        if (data.status === 'success') {
                                            el.html(text).removeClass('ajax');
                                        } else {
                                            el.text(reference.data.value).removeClass('ajax');
                                            alert('Упс!!! Что пошло нетак.');
                                        }
                                    }
                                });
                            }).on('hidden.bs.select', function () {
                                el.text(data.value).removeClass('ajax');
                            });
                        } else {
                            el.text(data.value).removeClass('ajax');
                            alert('Упс!!! Выбирать не из чего.');
                        }
                    }
                });
            } else {
                el.html('<input id="editbox" class="form-control" type="text" value="' + data.value + '" />');
                $('#editbox').focus().on('blur', function () {
                    el.text(reference.data.value).css('width', '').removeClass('ajax');
                });
            }
        },
        updateReference: function (el) {
            var value = $('input', el).val();

            if (reference.data.value != value) {
                $.ajax({
                    type: "POST",
                    url: "/admin/ajax/system/",
                    dataType: "json",
                    data: {
                        action: 'updateReference',
                        table: reference.data.table,
                        id: el.closest('tr').data('id'),
                        field: el.data('field'),
                        value: value
                    },
                    beforeSend: function () {
                        el.html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            el.text(value).removeClass('ajax');
                        } else {
                            el.text(reference.data.value).removeClass('ajax');
                            alert('Упс!!! Что пошло нетак.');
                        }
                    }
                });
            } else {
                el.text(value).removeClass('ajax');
            }
        }
    }
})();

/**
 * Активация, деактивация справочника
 */
$(".update_status_reference").on('click', function (e) {
    // Отмена выполнение события "клик"
    e.preventDefault();
}).on('dblclick', function () {
    var $this = $(this),
        table = $('table').attr('id'),
        id = $this.closest('tr').data('id'),
        value = $this.data('value'),
        new_value = 1,
        new_title = 'Активировать';

    if (value) {
        new_value = 0;
        new_title = 'Деактивировать';
    }

    $.ajax({
        type: "POST",
        url: "/admin/ajax/system/",
        dataType: "json",
        data: {
            action: 'updateReferenceStatus',
            table: table,
            id: id,
            value: value
        },
        success: function (data) {
            if (data.status === 'success') {
                $this.toggleClass('active');
                $this.data('value', new_value).attr('title', new_title).tooltip('destroy');
            } else {
                alert('Упс!!! Что пошло нетак.');
            }
        }
    });
    return false;
});

/**
 * Удаление из справочника
 */
$(".delete_reference").on('click', function (e) {
    e.preventDefault();
    var $this = $(this),
        table = $('table').attr('id'),
        id = $this.closest('tr').data('id');

    bootbox.confirm({
        title: ' ',
        message: 'Хотите удалить?',
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
                    url: "/admin/ajax/system/",
                    dataType: "json",
                    data: {
                        action: 'deleteReference',
                        table: table,
                        id: id
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            $this.closest('tr').fadeOut('slow');
                        }
                    }
                });
            }
        }
    });
});

$(document).ready(function () {
    reference.init();
});