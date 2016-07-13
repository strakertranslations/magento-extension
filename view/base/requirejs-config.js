var config = {
    paths:{
        "straker" : 'Straker_EasyTranslationPlatform/js/other-libraries'
    }
}

requirejs(['straker' ], function( straker ) {
    straker.hideEnvField();
    straker.hello();
});