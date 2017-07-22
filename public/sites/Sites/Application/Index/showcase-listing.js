/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global toastr */

'use strict';

OSCOM.a.Index.showShowcaseListing = function() {
    if (OSCOM.a.Index.isGettingListing === true) {
        return false;
    }

    OSCOM.a.Index.isGettingListing = true;

    toastr.clear();

    var loadUrlParams = {
        'RPC': ''
    };

    $.extend(loadUrlParams, OSCOM.urlBaseReq, {
        'Index': '',
        'GetShowcaseListing': '',
        'category': OSCOM.a.Index.currentShowcaseCategory,
        'partner': OSCOM.a.Index.currentShowcasePartner
    });

    var dfd = $.Deferred();

    var rpcGetListing = $.getJSON(OSCOM.generateUrl(false, loadUrlParams), function(result) {
        if ((result === null) || (typeof result !== 'object') || (Array.isArray(result.sites) === false)) {
            return dfd.reject();
        }

        $('#loadMoreSpinner').removeClass('is-active');

        var template = $('#cardSiteTemplate').html();
        Mustache.parse(template);

        var data = {
            'partner_title': result.partner_title,
            'partner_desc': result.partner_desc,
            'partner_url': result.partner_url,
            'lang_visit_partner_website': OSCOM.strReplace(OSCOM.def.js_visit_partner_website, {':partner': result.partner_title}),
            'sites': [],
            'lang_in_country': OSCOM.def.js_site_card_in_country
        };

        result.sites.forEach(function(s) {
            var parentCategoryCode = s.parent_category_name;
            if (parentCategoryCode !== null) {
                parentCategoryCode = parentCategoryCode.replace(/ /g, '-');
            }

            var categoryCode = s.category_name;
            categoryCode = categoryCode.replace(/ /g, '-');

//some old entries are still assigned top level categories
            if (parentCategoryCode === null) {
                s.parent_category_name = s.category_name;
                parentCategoryCode = categoryCode;
            }

            var parentCategoryCodeFilterParam = {};
            parentCategoryCodeFilterParam[parentCategoryCode] = '';

            var categoryCodeFilterParam = {};
            categoryCodeFilterParam[categoryCode] = '';

            data.sites.push({
                'publicId': s.public_id,
                'colorClass': 'mdl-color--' + OSCOM.getCardBackgroundColor(s.title),
                'title': s.title,
                'url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Go': s.public_id})),
                'img_src': OSCOM.siteImagePreviewBase + s.round_id + '/' + s.public_id + '.png',
                'parent_category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam)),
                'parent_category_name': s.parent_category_name,
                'category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam, categoryCodeFilterParam)),
                'category_name': s.category_name,
                'country_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'country': s.country_code})),
                'country_name': s.country_name,
                'total_likes': s.total_likes
            });
        });

        $('#liveSitesShowcaseGrid').append(Mustache.render(template, data));

        dfd.resolve();
    }).fail(function() {
        dfd.reject();
    });

// abort json call if it takes longer than 10 seconds
    setTimeout(function() {
        if (rpcGetListing.state() === 'pending') {
            rpcGetListing.abort();
        }
    }, 10000);

    $.when(dfd).then(function() {
        OSCOM.a.Index.isGettingListing = false;
    }, function() {
        OSCOM.a.Index.isGettingListing = false;

        $('#loadMoreSpinner').removeClass('is-active');

        toastr.error(OSCOM.def.js_error_general);
    });
};
