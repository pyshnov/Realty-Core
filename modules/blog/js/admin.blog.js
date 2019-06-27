/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var blogModule = (function () {
    return {
        init: function () {

            $('.update-status-article').on('dblclick', 'a', function () {
                blogModule.updateStatus($(this))
            });

            $('.blog-remove').on('click', function (e) {
                e.preventDefault();
                blogModule.remove($(this))
            });

            $("#selectAction").on('change', function() {
                blogModule.actionEntity();
            });

        },
        updateStatus: function (el) {
            var id = el.data('id');
            var value = el.data('value');

            if(id && value !== undefined) {
                $.ajax({
                    type: "POST",
                    url: "/admin/ajax/blog/",
                    dataType: "json",
                    data: {
                        action: 'updateStatus',
                        id: id,
                        value: value
                    },
                    success: function(res){
                        if(res.status === 'success') {
                            el.toggleClass('active')
                                .data('value', res.data.value)
                                .attr('title', res.data.title);
                        }
                    }
                });
            }
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
                        var id = el.data('id'),
                            table = el.data('table');
                        if(id && table) {
                            $.getJSON('/admin/ajax/blog/?action=remove&table=' + table + '&id=' + id, function(data){
                                if(data.status === 'success') {
                                    el.closest('tr').fadeOut();
                                }
                            });
                        }
                    }
                }
            });
        },
        actionEntity: function () {
            var action = $("#selectAction").val();

            if(action) {
                $("#btnAction").prop('disabled', false).on('click', function () {
                    var arr =[];
                    $( '.check:checked' ).each(function(){
                        arr.push($(this).val());
                    });
                    if(arr.length) {
                        $.ajax({
                            type: "POST",
                            url: "/admin/ajax/blog/",
                            dataType: "json",
                            data: {
                                action: 'actionEntity',
                                id: arr,
                                do: action
                            },
                            success: function(data){
                                if(data.status === 'success') {
                                    location.reload(true);
                                }
                            }
                        });
                    }
                });
            }
        }
    }
})();

blogModule.init();