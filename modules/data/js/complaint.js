/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

var complaint = (function () {
    return {
        init: function () {

            $('#complain .dropdown-menu').on('click', 'a', function(e){
                e.preventDefault();

                var id = Number($(this).data('id')),
                    text = $(this).text(),
                    res_ok = $(".complain-ok");

                if (id) {
                    $.ajax({
                        type: "POST",
                        url: "/ajax/data/",
                        dataType: "json",
                        data: {
                            action: "addComplaint",
                            id: id,
                            text: text
                        },
                        beforeSend: function(){
                            res_ok.html('<i class="fa fa-spinner fa-spin"></i>');
                        },
                        success: function(data){
                            if(data.status === 'success'){
                                $("#complain").hide('slow');
                                res_ok.html(data.message).show('slow');
                            }
                        }
                    });
                }
            });

            $('.delete_complaint').on('click', function(e){
                e.preventDefault();
                var _this = $(this),
                    id = Number(_this.data('id'));

                if (id) {
                    $.ajax({
                        type: "POST",
                        url: "/admin/ajax/data/",
                        dataType: "json",
                        data: {
                            action: "deleteComplaint",
                            id: id
                        },
                        success: function(data){
                            if(data.status === 'success'){
                                _this.closest('tr').fadeOut();
                            }
                        }
                    });
                }
            });
        }
    }
})();

$(document).ready(function () {
    complaint.init();
});