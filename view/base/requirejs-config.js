var config = {
    paths:{
        "straker" : 'Straker_EasyTranslationPlatform/js/straker-module'
    }
}

requirejs(['straker' ], function( straker ) {
    straker.hideEnvField();
    straker.hello();
});