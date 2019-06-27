/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

$(document).ready(function () {

    $("#ajaxReviewAdd").validator().on('submit', function(e) {
        e.preventDefault();
        var t = $(this);
        if (t.validator({}, 'check') > 0) {
            return false;
        } else {
            if($("div").is(".g-recaptcha")) {
                if (!grecaptcha.getResponse().length) {
                    grecaptcha.reset();
                    alert('Введённый код не совпадает');
                    return false;
                }
            }
        }

        $.post(
            '/ajax/reviews/',
            {
                action: 'addReview',
                s: t.serialize()
            },
            function(data) {
                if(data.status === 'success') {
                    t.hide(300).html('<p class="m-small">' + data.message + '</p>').show("slow");
                }
            },
            'json'
        );
    });
});