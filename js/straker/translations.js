Event.observe(window, "load", function() {
    var strakerMenu = $$("#nav LI.level0:has(A[href*=straker/adminhtml])")[0];
    var n = SKIN_URL.indexOf("adminhtml");
    var imageElement = '<img style="height: 23px;margin-right: 3px;line-height: 27px;vertical-align: middle;margin-top: -2px;" id="amasty_block" src="'+SKIN_URL.substring(0, n)+'adminhtml/default/straker/images/straker-translations-logo.png"." alt="" border="0" />';
    $(strakerMenu.select(' > a > span'))[0].setStyle({display: 'inline-block'}).insert({before: imageElement});
    console.log('straker ready');
});