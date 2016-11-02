Event.observe(window, "load", function() {
    var strakerMenu = $$("#nav LI.level0:has(A[href*=admin/straker])")[0];
    var n = SKIN_URL.indexOf("adminhtml");
    var imageElement = '<img style="height: 23px;margin-right: 3px;line-height: 27px;vertical-align: middle;margin-top: -2px;" id="straker_block" src="'+SKIN_URL.substring(0, n)+'adminhtml/default/straker/images/straker-translations-logo.png"." alt="" border="0" />';
    $(strakerMenu.select(' > a > span'))[0].setStyle({display: 'inline-block'}).insert({before: imageElement});

    var strakerAccountLink = $$('a[href*="admin/straker_new/account"]')[0];
    strakerAccountLink.observe('click', function(event) {
        window.open(strakerAccountLink.href, '_blank');
        Event.stop(event);
    });

    var strakerTermsLink = $$('a[href*="admin/straker_new/terms"]')[0];
    strakerTermsLink.observe('click', function(event) {
        window.open(strakerTermsLink.href, '_blank');
        Event.stop(event);
    });
});
