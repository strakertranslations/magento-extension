require(['jquery', 'jquery/ui'], function($){
    $('#item_general_locale_code').on('change',function(){
        $('input[name="name"]').val($(this).find('option:selected').text());
    });
});

