require(['jquery', 'jquery/ui'], function($){
    $('#item_general_locale_code').on('change',function(){
        $('input[name="name"]').val($(this).find('option:selected').text());
    });

    $('#job_tabs_attribute_section').on('click',function(e){

        console.log($('input[name="products"]').val());

        var products = $('input[name="products"]').val();

        $.ajax({
            url: "/index.php/admin/EasyTranslationPlatform/jobs/productattributes",
            data: {form_key: window.FORM_KEY,products:products},
            type: 'POST'

        }).done(function( data ) {

            $('#job_tabs_attribute_section_content').html(data);

        });

    })

});

