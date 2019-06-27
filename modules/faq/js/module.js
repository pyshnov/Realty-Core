/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var faqModule = (function () {
    return {
        init: function () {
            $('.update-status-faq').on('click', 'a', function (e) {
                e.preventDefault();
                var t = $(this),
                    id = t.data('id'),
                    value = t.data('value');

                if(id !== undefined && value !== undefined) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/faq/",
                        dataType: "json",
                        data: {
                            action: 'updateStatus',
                            id: id,
                            value: value
                        },
                        success: function(data){
                            if(data.status === 'success') {
                                t.toggleClass('active');
                                var v = 1;
                                var title = 'Активировать';
                                if(value) {
                                    v = 0;
                                    title = 'Деактивировать';
                                }
                                t.data('value', v).attr('title', title);
                            }
                        }
                    });
                }
            });

            $('.remove-faq').on('click', function (e) {
                e.preventDefault();

                var t = $(this),
                    id = t.data('id');

                if(id !== undefined) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/faq/",
                        dataType: "json",
                        data: {
                            action: 'remove',
                            id: id
                        },
                        success: function(data){
                            if(data.status === 'success') {
                                t.closest('tr').slideUp(600);
                            }
                        }
                    });
                }
            });

            $('#ajaxFaqAdd').validator().on('submit', function () {
                var t = $(this);
                if (t.validator({scrollTop: false}, 'check') > 0) {
                    return false;
                }

                if ($('[name=question]').val().length > 4) {
                    var s = t.serialize();
                    $.post(
                        '/ajax/faq/',
                        {
                            action: 'addFaq',
                            s: s
                        },
                        function(data) {
                            if(data.status = 'success') {
                                location.href = '/admin/faq/'
                            }
                        },
                        'json'
                    );
                }
            });
        }
    }
})();


faqModule.init();

