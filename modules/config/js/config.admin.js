/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var config = (function () {
    return {
        data: {},
        init: function () {

            $('.form_config').on('change', 'input[type="checkbox"]', function () {
                config.data[$(this).attr('name')] = $(this).prop('checked');
                console.log(config.data)
            }).on('change', 'input[type="text"]', function () {
                config.data[$(this).attr('name')] = $(this).val();
            }).on('change', 'select', function () {
                config.data[$(this).attr('name')] = $(this).val();
            }).submit(function (e) {
                e.preventDefault();
                if(!$.isEmptyObject(config.data)) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: '/admin/ajax/config/',
                        data: {
                            action: 'updateConfig',
                            data: config.data
                        },
                        success: function(data) {
                            if(data.status === 'success') {
                                config.data = {};
                                setTimeout(function(){
                                    toastr.success('', 'Данные сохранены успешно', {
                                        timeOut: 5000,
                                        closeButton: true,
                                        progressBar: true
                                    });
                                }, 600);
                            }
                        }
                    });
                }
            });

            $('#ajaxSettingAdd').validator({
                scrollTop: false
            }).submit(function (e) {
                e.preventDefault();
                if ($('#ajaxSettingAdd').validator({scrollTop: false }, 'check') > 0) {
                    e.preventDefault();
                    setTimeout(function(){
                        toastr.error('Проверьте правильность введенных данных.', 'Ошибка!', {
                            timeOut: 5000,
                            closeButton: true,
                            progressBar: true
                        });
                    }, 1000);
                } else {

                    var msg = $(this).serialize();

                    $.ajax({
                        type: "POST",
                        url: "/admin/ajax/config/",
                        data: {
                            action: "ajaxSettingAdd",
                            data: msg
                        },
                        dataType: "json",
                        success: function (data) {
                            if(data.status === 'success') {
                                location.reload(true);
                            }
                        }
                    });
                }
            });

        }
    }
})();

$(document).ready(function () {
    config.init();
});