$("form").bind('submit', function (e) {
    e.preventDefault();
    var form_data = $(this).serialize();
    $.ajax({
        type: "POST",
        url: "data.php",
        data: form_data,
        success: function(data){
            $('pre').html(data);
        },
        error: function (){
            $('pre').html("Ошибка!");

        }
    });
});