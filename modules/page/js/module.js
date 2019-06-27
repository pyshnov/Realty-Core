/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var page = (function () {
    return {
        init: function () {

            $('.page-remove').on('click', function (e) {
                e.preventDefault();
                page.remove($(this))
            });

        },
        remove: function (el) {
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
                    if(result) {
                        var id = el.data('id');
                        if(id) {
                            $.getJSON('/ajax/page/remove/?id=' + id, function(data){
                                if(data.status === 'success') {
                                    el.closest('tr').fadeOut();
                                }
                            });
                        }
                    }
                }
            });
        }
    }
})();

page.init();