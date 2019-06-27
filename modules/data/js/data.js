/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var data = (function () {
    return {
        DATA: {
            isRegionLocation: false
        },
        init: function () {

            data.refreshTopic();

            data.cityElementRefresh();

            var _form = $('#dataForm');

            _form.validator().on('submit', function (e) {
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
            });

        },
        refreshTopic: function () {

            var $this = $('#topicId');
            var topic_id = $this.val();

            if(!topic_id) topic_id = 0;

            var id =[];

            $this.closest('form').find('.refresh_topic').each(function(){
                id.push($(this).attr('id'));
            });

            id.forEach(function(el) {
                var id = '#' + el;
                var value = $(id).data('show');
                value = value.split(',');
                if (value.indexOf(topic_id) !== -1) {
                    $(id).show(300);
                    if(el === 'squareRooms') {
                        if(topic_id == 2 || topic_id == 3) {
                            $('.square_rooms').html('Площадь комнаты');
                        } else {
                            $('.square_rooms').html('Площадь комнат');
                        }
                    }
                } else {
                    $(id).hide(300);
                }
            });

            // Если категория изменилась
            $this.on('change', function() {
                data.refreshTopic();
            });

        },
        cityElementRefresh: function () {
            var $this = $('#cityId'),
                arr = $this.data('refresh').split(',');

            var city_id = $this.val();

            // После формирования DOM перебераем элементы
            // по необходимоти скрываем или отображем
            arr.forEach(function(item) {
                data.updateFormElement(city_id, item);
            });
            
            // Автокоплит адреса
            data.typeahead(city_id);

            // Если город был изменен
            $this.on('change', function() {

                var $modal = $('#locationModal');

                // Если выбран нулевой элемент
                // раскрываем модальное окно для выбора города
                // иначе снова перебираем элементы
                if ($this.val() == 0) {

                    $modal.modal('show');

                    var apply = $("#applyRegion"),
                        region_loc = $('#regionsLocation'),
                        location = $("#cityLocation");

                    // Если был раскрыт список регионов
                    region_loc.on('show.bs.select', function() {

                        // Если впервые, ajax получаем список регионов
                        if (data.DATA.isRegionLocation === false) {
                            $.ajax({
                                type: "POST",
                                url: "/ajax/data/",
                                dataType: "json",
                                data: {
                                    action: 'loadRegionsHtml'
                                },
                                beforeSend: function(){
                                    region_loc.html('<i class="fa fa-spinner fa-spin"></i>').selectpicker('refresh');
                                },
                                success: function(res){
                                    if(res.status === 'success') {
                                        region_loc.html(res.data).selectpicker('refresh');
                                        data.DATA.isRegionLocation = true;
                                    }
                                }
                            });
                        }
                    }).on('change', function(){
                        // Если был выбран или изменен регион из списка
                        // подгружаем ajax список городов выбранного гериона

                        $.ajax({
                            type: "POST",
                            url: "/ajax/data/",
                            dataType: "json",
                            data: {
                                action: 'loadCityHtml',
                                id: region_loc.val()
                            },
                            success: function(res){

                                if(res.status === 'success') {
                                    // в список городов подгруэаем новый полученных список
                                    location.html(res.data);

                                    // Если полученный список имеет более одного option (города)
                                    // делаем этот список активным, а кнопку "Выбрать" не активной
                                    // на случай если ранее оно уже была активной
                                    // и вешаем обработчик на списсок городов, если он будет изменен
                                    // кнопку "Выбрать" активируем
                                    // иначе список городов делаем неактивно, на случай если ранее оно уже была активной
                                    // кнопку "Выбрать" активируем
                                    if ($("option", location).size() > 1) {
                                        location.prop("disabled", false).on('change', function(){
                                            apply.prop("disabled", false);
                                        });
                                        apply.prop("disabled", true);
                                    } else {
                                        location.prop("disabled", true);
                                        apply.prop("disabled", false);
                                    }
                                } else {
                                    // Иначе список гороов и кнопку делаем не активной
                                    apply.prop("disabled", true);
                                    location.prop("disabled", true);
                                }

                                location.selectpicker('refresh');
                            }
                        });

                    });

                    $('.js-city__link').on('click', function () {
                        var id = $(this).data('id');
                        data.reloadCitySelect(id, $(this).text());
                        arr.forEach(function(item) {
                            console.log(id)
                            data.updateFormElement(id, item);
                        });
                    });

                    apply.on('click', function () {
                        var id = location.val();
                        data.reloadCitySelect(id, $("option:selected", location).text());
                        arr.forEach(function(item) {
                            data.updateFormElement(id, item);
                        });
                    });

                } else {
                    arr.forEach(function(item) {
                        data.updateFormElement($this.val(), item);
                    });
                }

                // Если ничего не выбрано и модальное окно скрылось, делаем активным первый элемент
                $modal.on('hidden.bs.modal', function () {

                    var city_id = $this.val();

                    if (city_id == '0') {
                        $(':first', $this).prop('selected', true);
                        $this.selectpicker('refresh').validator().valid();
                    } else {
                        data.typeahead(city_id);
                    }
                });
            });

        },
        updateFormElement: function (city_id, el) {

            if(!city_id) city_id = 0;

            var name = el.charAt(0).toUpperCase() + el.slice(1);

            if (!$('div').is('#form' + name)) return;

            var value = $('#select' +  name).data('value');

            if(!value) value = 0;

            if(city_id) {
                $.ajax({
                    async: false,
                    type: "POST",
                    url: "/ajax/data/",
                    dataType: "json",
                    data: {
                        action: 'reloadSelectForm',
                        city_id: city_id,
                        table: el,
                        value: value
                    },
                    success: function(res){
                        if(res.status === 'success') {

                            $('#select' + name).html(res.data);
                            $('select').selectpicker();
                            $('#form' + name).fadeIn("slow");

                            if(el === 'metro') {
                                $('#timeMetro').fadeIn("slow");
                            }

                        } else {
                            $('#form' + name).fadeOut("slow");
                            $('#select' + name).html('');

                            if(el === 'metro') {
                                $('#timeMetro').fadeOut("slow");
                            }
                        }
                    }

                });
            } else {
                $('#form' + name).fadeOut("slow");
                $('#select' + name).html('');
            }

        },
        reloadCitySelect: function (city_id, city_name) {
            if(city_id) {

                var city_sel = $("#cityId"),
                    first_id = $(':first', city_sel).val();

                if(first_id != city_id) {

                    $('option', city_sel).each(function(){
                        // Перебераем все елементы списка и если id совпадает с id выбранного города, удаляем его
                        if($(this).val() == city_id) {
                            $('[value="' + city_id + '"]', city_sel). remove();
                        } else {
                            // делаем все элементы не активными
                            city_sel.prop("selected", false);
                        }
                    });

                    // Добавляем на первое место списка выбраный город
                    city_sel.prepend( $('<option value="' + city_id + '">' + city_name + '</option>'));

                    city_sel.data('refresh').split(',').forEach(function(item) {
                        data.updateFormElement(city_sel.val(), item);
                    });
                }

                // Делаем первый (новый) элемент активным
                $(':first', city_sel).prop('selected', true);
                city_sel.selectpicker('refresh').validator().valid();

                // Скрываем модольное окно
                $('#locationModal').modal('hide');
            }
        },
        typeahead: function (city_id) {

            var el = $('.typeahead');

            el.typeahead('destroy');

            $.getJSON('/ajax/data/?action=streetLoad&city_id=' + city_id, function (res) {
                el.typeahead({
                    source:res.data,
                    minLength: 2,
                    delay: 600
                }).change(function() {
                    var current = el.typeahead("getActive");

                    if (current) {
                        if (current.name == el.val()) {
                            $("input[name='street_id']").val(current.street_id);
                        } else {
                            $("input[name='street_id']").val(0);
                        }
                    }
                });
            });
        }

    }
})();

$(document).ready(function () {
    data.init();
});