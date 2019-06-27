
(function (window) {
    'use strict';

    window.pn = window.Pyshnov = window.Pyshnov || {};

    /**
     * Helper function for getting the root URL.
     * @return {[type]} [description]
     */
    Pyshnov.getRootUrl = function() {
        return window.location.origin ?
            window.location.origin + '/' :
            window.location.protocol + '/' + window.location.host + '/';
    };

    // Не риализована
    Pyshnov.ajax = {
        post: function (url, data, callBack) {
            $.ajax({
                type: "POST",
                url: Pyshnov.getRootUrl . url,
                data: data,
                dataType: "json",
                success: callBack,
                beforeSend: function () {
                    // флаг, говорит о том что начался процесс загрузки с сервера
                    Pyshnov.WAIT_PROCESS = true;
                },
                complete: function () {
                    // завершился процесс
                    Pyshnov.WAIT_PROCESS = false;
                },
                error: function (request, status, error) {
                    
                }
            });
        }
    };

    /**
     * plural(5, "Комментарий ", "Комментария ", "Комментариев ")
     *
     * @param e
     * @param t
     * @param n
     * @param i
     * @returns {*}
     */
    Pyshnov.plural = function (e, t, n, i) {
        var r = [t, n, i],
            o = [2, 0, 1, 1, 1, 2];
        return r[(e %= 100) > 4 && 20 > e ? 2 : o[Math.min(e % 10, 5)]]
    };
    Pyshnov.openPhone = function (s, b, i) {
        $.ajax({
            type: "POST",
            url: "/ajax/data/",
            data: {
                action: "getRealtyPhone",
                id: i
            },
            dataType: "json",
            beforeSend: function () {
                $(b).html('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (res) {
                if (res.status === 'success') {
                    $(b).fadeOut(200).text('');
                    $(s).html(res.data);
                }
            }
        });
    };

    Pyshnov.actionObject = function (e, action) {
        var result;
        var id = e.data('id');
        if (id !== undefined) {
            $.ajax({
                async: false,
                type: "POST",
                url: "/ajax/data/",
                dataType: "json",
                data: {
                    action: 'actionObject',
                    id: id,
                    do: action
                },
                beforeSend: function(){
                    e.prop("disabled", true);
                },
                success: function(res){
                    result = res;
                    e.prop("disabled", false);
                }
            });
        }

        return result;
    };

    Pyshnov.formatNumber = function (selector, options) {
        options = $.extend({
            mDec: 0,
            aDec: '.',
            aSep: ' '
        }, options);

        function format(nStr) {
            if (!nStr) return;
            var number = (nStr + '').replace(/[^0-9+\-Ee.]/g, ''),
                n = !isFinite(+number) ? 0 : +number,
                prec = options.mDec,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, options.aSep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(options.aDec);
        }

        return $(selector).each(function () {
            var t = $(this);
            t.val(format(t.val()));

            t.on('keyup', function (e) {
                t.val(format(e.target.value));
            });

            t.on('focusout', function (e) {
                t.val(format(e.target.value));
            });
        });
    };

    Pyshnov.addFavorites = function() {
        var t = $(this),
            id = t.data('id'),
            count = $('.favorites_count');

        console.log(t)

    };

    /*Pyshnov.addFavorites = function (e) {

        console.log(5454)

        var t = $(el),
            id = t.data('id'),
            count = $('.favorites_count');

        if (id) {
            $.ajax({
                type: "POST",
                url: "/ajax/data/",
                dataType: "json",
                data: {
                    action: "addFavorites",
                    id: id
                },
                success: function (res) {
                    if (res.status === 'success') {
                        count.text(Number(count.text()) + 1);
                        t.removeClass('add_favorites')
                            .addClass('remove_favorites')
                            .attr('title', res.data.title)
                            .html(res.data.html);
                    }
                }
            });
        }
    }*/

})(window);

var engine = (function () {
    return {
        data: {},
        init: function () {

            //$('.test').on('click', pn.addFavorites);

            $('#checkAll').on('click', function () {
                engine.checkAll();
            });

            // Добавляем в избранное
            $('body').on('click', '.add_favorites', function (e) {
                e.preventDefault();
                engine.addFavorites($(this));
            }).on('click', '.remove_favorites', function (e) {
                e.preventDefault();
                engine.removeFavorites($(this));
            });
        },
        checkAll: function () {
            if ($("#checkAll").prop('checked')) {
                $(".check").prop('checked', true);
            } else {
                $(".check").prop('checked', false);
            }
        },
        addFavorites: function (t) {
            var id = t.data('id'),
                count = $('.favorites_count');
            if (id) {
                $.ajax({
                    type: "POST",
                    url: "/ajax/data/",
                    dataType: "json",
                    data: {
                        action: "addFavorites",
                        id: id
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            count.text(Number(count.text()) + 1);
                            t.removeClass('add_favorites')
                                .addClass('remove_favorites')
                                .attr('title', res.data.title)
                                .html(res.data.html);
                        }
                    }
                });
            }
        },
        removeFavorites: function (t) {
            var id = t.data('id'),
                count = $('.favorites_count');
            if (id) {
                $.ajax({
                    type: "POST",
                    url: "/ajax/data/",
                    dataType: "json",
                    data: {
                        action: "removeFavorites",
                        id: id
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            count.text(Number(count.text()) - 1);
                            t.removeClass('remove_favorites')
                                .addClass('add_favorites')
                                .attr('title', res.data.title)
                                .html(res.data.html);
                        }
                    }
                });
            }
        }

    }
})();

$(document).ready(function () {
    engine.init();
});