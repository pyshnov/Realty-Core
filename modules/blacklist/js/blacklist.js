/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var blacklist = (function () {
    return {

        init: function () {

            $('#ajaxAddPhoneBlacklist').submit(function (e) {
                e.preventDefault();

                var phone = $(this).find('[name="phone"]').val();

                if (phone) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: '/admin/ajax/blacklist/',
                        data: {
                            action: 'addPhone',
                            phone: phone,
                            info: $('.blacklist-info').val()
                        },
                        success: function (data) {
                            if (data.status === 'success') {
                                location.reload(true);
                            }
                        }
                    });
                }
            });

            $('#searchPhone').on('submit', function (e) {
                e.preventDefault();
                var phone = $('.search-phone').val();

                if (phone != '') {
                    window.location.href = '/admin/blacklist/?phone=' + phone;
                }
            });

            $('.remove-blacklist').on('click', function (e) {
                e.preventDefault();
                blacklist.removePhone($(this))
            });

            $('.remove-data').on('click', function (e) {
                e.preventDefault();
                blacklist.removeData($(this))
            });

        },
        removePhone: function (el) {
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
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '/admin/ajax/blacklist/',
                            data: {
                                action: 'removePhone',
                                id: el.data('id')
                            },
                            success: function (data) {
                                if (data.status === 'success') {
                                    el.closest('tr').fadeOut();
                                }
                            }
                        });
                    }
                }
            });
        },
        removeData: function (el) {
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
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '/admin/ajax/blacklist/',
                            data: {
                                action: 'removeData',
                                phone: el.data('phone')
                            },
                            success: function (data) {
                                if (data.status === 'success') {
                                    el.closest('tr').fadeOut();
                                }
                            }
                        });
                    }
                }
            });
        }
    }
})();

$(document).ready(function () {
    blacklist.init();
});