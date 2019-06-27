/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

$(document).ready(function () {

    var _form = $("#faqFormAdd");

    _form.validator().on('submit', function(e) {
        e.preventDefault();
        if (_form.validator({}, 'check') > 0) {
            setTimeout(function(){
                toastr.error('Проверьте правильность введенных данных.', 'Ошибка!', {
                    timeOut: 5000,
                    closeButton: true,
                    progressBar: true
                });
            }, 1000);
            return false;
        } else {
            if($("div").is(".g-recaptcha")) {
                if (!grecaptcha.getResponse().length) {
                    grecaptcha.reset();
                    toastr.error('Введённый код не совпадает.', 'Ошибка!', {
                        timeOut: 5000,
                        closeButton: true,
                        progressBar: true
                    });
                    return false;
                }
            }
        }
        _this = $(this);
        $('.error').hide();
        if ($('[name=question]').val().length > 4) {
            var s = $(this).serialize();

            if($("div").is(".g-recaptcha")) {
                var captcha = grecaptcha.getResponse();

                if (!captcha.length) {
                    // Сбрасываем виджет reCaptcha
                    grecaptcha.reset();

                    error.html('<p class="small" style="color: red;">Вы не прошли проверку "Я не робот"</p>').show();

                    return false;
                }
            }

            $.post(
                '/ajax/faq/',
                {
                    action: 'addFaq',
                    s: s
                },
                function(data) {
                    if(data.status === 'success') {
                        _this.slideToggle(400);
                        setTimeout(function() {
                            $('.form-success').text(data.message).fadeIn("slow");
                        }, 600);
                    }
                },
                'json'
            );
        } else {
            return false;
        }
    });
});