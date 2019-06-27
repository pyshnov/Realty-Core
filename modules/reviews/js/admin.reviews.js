/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var reviewsModule = (function () {
    return {
        init: function () {

            $('#ajaxReviewAdd').submit(function (e) {
                e.preventDefault();
                t = $(this);
                if ($('[name=review]').val().length > 4) {
                    var s = t.serialize();
                    $.post(
                        '/ajax/reviews/',
                        {
                            action: 'addReview',
                            s: s
                        },
                        function (data) {
                            if (data.status = 'success') {
                                location.href = '/admin/reviews/'
                            }
                        },
                        'json'
                    );
                } else {
                    return false;
                }
            });

            $('.update-status-review').on('click', 'a', function () {
                var t = $(this),
                    id = t.data('id'),
                    value = t.data('value');

                if (id !== undefined && value !== undefined) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/reviews/",
                        dataType: "json",
                        data: {
                            action: 'updateStatus',
                            id: id,
                            value: value
                        },
                        success: function (data) {

                            if (data.status === 'success') {
                                t.toggleClass('active');
                                var v = 1;
                                var title = 'Активировать';
                                if (value) {
                                    v = 0;
                                    title = 'Деактивировать';
                                }
                                t.data('value', v).attr('title', title);
                            }
                        }

                    });
                }
            });

            $('.review-remove').on('click', function (e) {
                e.preventDefault();
                var t = $(this),
                    id = t.data('id');

                if (id !== undefined) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/reviews/",
                        dataType: "json",
                        data: {
                            action: 'remove',
                            id: id
                        },
                        success: function (data) {
                            if (data.status === 'success') {
                                t.closest('tr').slideUp(600);
                            }
                        }
                    });
                }
            });

        }
    }
})();


reviewsModule.init();

