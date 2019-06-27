/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var system_admin = (function () {
    return {
        init: function () {
            $('body').on('click', '.install-module', function () {
                var mod_name = $(this).data('name');
                $.getJSON('/admin/ajax/system/?action=installModule&name=' + mod_name, function (data) {
                    if (data.status === 'success') {
                        location.reload();
                    }
                });
            });

            $('.system-modules').on('change', '.module-enable', function () {
                var val = $(this).prop("checked") == true ? 1 : 0,
                    mod_name = $(this).attr('name');

                $.post('/admin/ajax/system/', {
                    action: 'moduleEnable',
                    name: mod_name,
                    val: val
                });
            }).on('click', '.module-remove', function () {
                var mod_name = $(this).data('name');

                $.getJSON('/admin/ajax/system/?action=removeModule&name=' + mod_name, function (data) {
                    if (data.status === 'success') {
                        location.reload();
                    }
                });
            });
        }
    }
})();

system_admin.init();
