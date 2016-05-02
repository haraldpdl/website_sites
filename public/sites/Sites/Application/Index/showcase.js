/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global toastr */

'use strict';

OSCOM.a.Index.isGettingListing = false;
OSCOM.a.Index.loadMoreUrlParams = {
    'RPC': ''
};

$.extend(OSCOM.a.Index.loadMoreUrlParams, OSCOM.urlBaseReq, {
    'Index': '',
    'GetShowcaseListing': ''
});

if (typeof OSCOM.a.Index.currentShowcaseCategory !== 'undefined') {
    $.extend(OSCOM.a.Index.loadMoreUrlParams, {'category': OSCOM.a.Index.currentShowcaseCategory});
}

if (typeof OSCOM.a.Index.currentShowcasePartner !== 'undefined') {
    $.extend(OSCOM.a.Index.loadMoreUrlParams, {'partner': OSCOM.a.Index.currentShowcasePartner});
}

OSCOM.a.Index.showShowcaseListing = function() {
    if (OSCOM.a.Index.isGettingListing === true) {
        return false;
    }

    OSCOM.a.Index.isGettingListing = true;

    toastr.clear();

    var dfd = $.Deferred();

    var rpcGetListing = $.getJSON(OSCOM.generateUrl(false, OSCOM.a.Index.loadMoreUrlParams), function(result) {
        if ((result === null) || (typeof result !== 'object') || ($.isArray(result) === false)) {
            return dfd.reject();
        }

        $('#loadMoreSpinner').removeClass('is-active');

        var template = $('#cardTemplate').html();
        Mustache.parse(template);

        var listingData = {
            'partners': [],
            'lang_in_country': OSCOM.def.js_site_card_in_country
        };

        result.forEach(function(p) {
            var currentPartnerParam = {};
            currentPartnerParam[p.code] = '';

            var currentPartnerCategoryParam = {};
            currentPartnerCategoryParam[p.category_code] = '';

            var partner = {
                'title': p.title,
                'url': OSCOM.generateUrl(false, $.extend({}, {'Services': ''}, currentPartnerCategoryParam, currentPartnerParam), OSCOM.urlSiteWebsite),
                'category_title': p.category_title,
                'category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Showcase': ''}, currentPartnerCategoryParam)),
                'sites': []
            };

            p.sites.forEach(function(s) {
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

                partner.sites.push({
                    'publicId': s.public_id,
                    'colorClass': 'mdl-color--' + OSCOM.getCardBackgroundColor(s.title),
                    'title': OSCOM.escapeHtml(s.title),
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

            listingData.partners.push(partner);
        });

        $('#liveSitesShowcaseGrid').append(Mustache.render(template, listingData));

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

$(function() {
    OSCOM.a.Index.showShowcaseListing();
});
