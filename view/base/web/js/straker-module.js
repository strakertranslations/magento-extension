define(['jquery'], function( $ ){

    // var straker = {};
    //
    // var $j = $.noConflict();
    //
    // straker.hideEnvironmentField = function(){
    //     var evnField = $j('#row_straker_general_version');
    //
    //     if( evnField != null ){
    //         evnField.css('display','none');
    //     }
    // }

    var straker = (function(){

        var $j = $.noConflict();

        var hideEnvEield = function(){

            var evnField = $j('#row_straker_general_version');

            if( evnField != null ){
                evnField.css('display','none');
            }
        };

        var hello = function(){

            console.log('rakesh.test')
        };

        return {
            hideEnvField : hideEnvEield,
            hello : hello
        }
        
    })();

    return straker;
});