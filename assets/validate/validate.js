+function ($) {
    'use strict';
    $.fn.validator = function(options, action){

        var $self = this;

        if(!$self.length) {
            return $self;
        }

        var validator = $.fn.validator;

        validator.id_list = [];

        // extend defaults, existing settings (to save state)
        //   and passed options.
        validator.settings = $.extend({
            dataErrorMsg: 'error-msg',
            defaultMsg: 'Поле не может быть пустым.',
            formGroupErrorClass: 'has-error',
            validateSelecters: 'input[type="text"],input[type="email"],input[type="password"],textarea,select',
            validOnBlur: true, // При потери фокуса
            validOnChange: true, // Если элемент изменен
            scrollTop: true
        }, options);

        validator._init = function() {

            // отменяем проверку данных на валидность
            $self.attr('novalidate', 'novalidate');

            // define .valid() with existing settings
            $.extend($.fn, {
                valid: function() {
                    var settings = validator.settings;
                    var _this = this;
                    var id = this.prop('name');
                    var required = this.prop('required');
                    var formGroup = this.closest('.form-group');

                    var min = isNaN(parseInt(this.prop('min'))) ? 1 : parseInt(this.prop('min'));
                    var msg = typeof(this.data(settings.dataErrorMsg)) != 'undefined' ? this.data(settings.dataErrorMsg) : settings.defaultMsg;

                    var type = this.prop('type');
                    var makeErrors = function(message) {
                        message = typeof(message) == 'undefined' ? msg : message;

                        var help;

                        if (formGroup.find('.help-block-errors').length) formGroup.find('.help-block-errors').remove();
                        help = '<div class="help-block-errors">' + message + '</div>';

                        if(formGroup.find('[class*="col-"]').length) {
                            _this.closest('[class*="col-"]').append(help);
                        } else {
                            formGroup.append(help);
                        }


                        formGroup.addClass('has-error');

                        validator.id_list.push(id);

                    };
                    var removeErrors = function(obj) {
                        obj = typeof(obj) == 'undefined' ? formGroup : obj;
                        obj.removeClass('has-error');

                        obj.find('.help-block-errors').remove();

                        if(validator.id_list[id]) {
                            delete validator.id_list[id];
                        }
                    };

                    // Если элемент скрыт, завершаем проверку
                    if (this.is(':hidden')) return true;
                    switch (type) {
                        case "email":
                            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                            var length = this.val() === null ? 0 : this.val().length;

                            if (required && length < min) {
                                makeErrors(msg);
                                return false;
                            } else if (re.test(this.val())) {
                                removeErrors();
                                return true;
                            } else {
                                makeErrors('Не верный формат Email');
                                return false;
                            }
                            break;
                        case "text":
                            var length = this.val() === null ? 0 : this.val().length;
                            if (required && length < min) {
                                makeErrors();
                                return false;
                            }
                            break;
                        case "password":
                            var length = this.val() === null ? 0 : this.val().length;
                            if (required && length < min) {
                                makeErrors();
                                return false;
                            }
                            break;
                        case "textarea":
                            var length = this.val() === null ? 0 : this.val().length;
                            var textarea_min = isNaN(parseInt(this.prop('min'))) ? 5 : parseInt(this.prop('min'));
                            if (required && length < textarea_min) {
                                makeErrors();
                                return false;
                            }
                            break;
                        default:
                            var value = this.val() === null ? 0 : this.val();
                            if (required && value == 0) {
                                makeErrors();
                                return false;
                            }
                    }

                    removeErrors();

                    return true;
                }
            });

        };

        if (!validator.isinit) {
           validator._init(options);
        }

        //  define function to validate entire form
        //  creates a collection of jQuery objects to run .valid() on
        var validate = function() {

            var errors = 0;
            var validobjs = $();

            // add everything else
            validobjs = validobjs.add($self.find(validator.settings.validateSelecters));

            // validate each obj, count errors
            $.each(validobjs, function() {
                var required = $(this).prop('required') ? true : false;
                if (required) {
                    if ($(this).valid() !== true) {
                        errors++
                    }
                }
            });

            if (errors > 0 && validator.settings.scrollTop) {

                $("html,body").animate({
                    'scrollTop': $('[name=' + validator.id_list[0] + ']').offset().top - 30
                }, 1000);

                // console.log(validator.id_list[0]);
            }

            return errors;

        };

        if (action == 'check') {
            return validate();
        }

        // Если потерян фокус
        if (validator.settings.validOnBlur) {
            $self.on('blur', validator.settings.validateSelecters, function() {
                $(this).valid();
            });
        }

        // Если было изненение в select
        if (validator.settings.validOnChange) {
            $self.on('change', 'select', function() {
                $(this).valid();
            });
        }

        return $self;
    };
}(jQuery);