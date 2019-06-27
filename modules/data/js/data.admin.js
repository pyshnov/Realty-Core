/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var data_admin = (function () {
    return {
        DATA: {
            user_select_option: false
        },
        init: function () {
            $('#userSelect').on('show.bs.select', function () {
                data_admin.userSelect($(this));
            });

            // Если выбран нулевой элемент, делаем неактивной кнопку "Выполнить"
            $("#selectActionObject").on('change', function() {
                $("#checkedActionObject").removeClass('disabled');
            });

            // Выполнения действий над выбраными объектами
            $("#checkedActionObject").on('click', function () {
                data_admin.checkedAction();
            });

            $(".data-action").on('click', 'a', function (e) {
                e.preventDefault();
                data_admin.dataAction($(this).data('action'), $(this).closest('.data-action').data('id'));
            });

        },
        userSelect: function (el) {
            if (!data_admin.DATA.user_select_option) {
                var id = el.val();
                if (!id)
                    id = 0;
                $.ajax({
                    type: "POST",
                    url: "/admin/ajax/data/",
                    dataType: "json",
                    data: {
                        action: "loadUserSelect",
                        id: id
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            el.html('')
                                .html(data.data)
                                .selectpicker('refresh');
                            data_admin.DATA.user_select_option = true;
                        }
                    }
                });
            }
        },
        checkedAction: function () {
            var id = [];

            $('#tableData').find('input.check:checked').each(function(){
                id.push($(this).val());
            });

            if (id.length) {
                var action = $("#selectActionObject").val();

                if(action) {
                    $.ajax({
                        type: "POST",
                        url: "/admin/ajax/data/",
                        dataType: "json",
                        data: {
                            action: "actionObject",
                            id: id.join(),
                            do: action
                        },
                        success: function (data) {
                            if (data.status === 'success') {
                                location.reload(true);
                            }
                        }
                    });
                }
            } else {
                alert('Ни одного  объявления не выбрано.');
            }
        },
        dataAction: function (action, id) {
            if (id && action) {
                $.ajax({
                    type: "POST",
                    url: "/admin/ajax/data/",
                    dataType: "json",
                    data: {
                        action: "actionObject",
                        id: id,
                        do: action
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            location.reload(true);
                        }
                    }
                });
            }
        }
    }
})();

$(document).ready(function () {
    data_admin.init();
});
