/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global componentHandler, toastr */

'use strict';

OSCOM.a.Account.isGettingListing = false;
OSCOM.a.Account.siteCounter = 0;
OSCOM.a.Account.loadMoreUrlParams = {
    'RPC': ''
};

$.extend(OSCOM.a.Account.loadMoreUrlParams, OSCOM.urlBaseReq, {
    'Account': '',
    'GetUserListing': ''
});

OSCOM.a.Account.showUserListing = function() {
    if (OSCOM.a.Account.isGettingListing === true) {
        return false;
    }

    OSCOM.a.Account.isGettingListing = true;

    toastr.clear();

    var dfd = $.Deferred();

    var rpcGetListing = $.getJSON(OSCOM.generateUrl(false, OSCOM.a.Account.loadMoreUrlParams), function(result) {
        if ((result === null) || (typeof result !== 'object') || (Array.isArray(result) === false)) {
            return dfd.reject();
        }

        $('#loadMoreSpinner').removeClass('is-active');

        $('#liveSitesUserGrid > div').remove();

        var template = $('#cardTemplate').html();
        Mustache.parse(template);

        var listingData = {
            'sites': [],
            'lang_in_country': OSCOM.def.js_site_card_in_country,
            'lang_showcase_add': OSCOM.def.js_site_card_showcase_add,
            'lang_showcase_remove': OSCOM.def.js_site_card_showcase_remove,
            'lang_mod_remove': OSCOM.def.js_site_card_mod_remove,
            'lang_no_site_submissions': OSCOM.def.js_no_site_submissions
        };

        result.forEach(function(s) {
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

            var site = {
                'id': OSCOM.a.Account.siteCounter,
                'status_live': (s.status === '3') ? true : false,
                'publicId': s.public_id,
                'colorClass': 'mdl-color--' + OSCOM.getCardBackgroundColor(s.title),
                'title': s.title,
                'url': (s.status === '3') ? OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Go': s.public_id})) : false,
                'img_src': (s.status === '3') ? OSCOM.siteImagePreviewBase + s.round_id + '/' + s.public_id + '.png' : OSCOM.siteImagePreviewBase + 'pending.png',
                'parent_category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam)),
                'parent_category_name': s.parent_category_name,
                'category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam, categoryCodeFilterParam)),
                'category_name': s.category_name,
                'country_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'country': s.country_code})),
                'country_name': s.country_name,
                'total_likes': s.total_likes,
                'ambassador_showcase_flag': (s.ambassador_showcase_flag === '1') ? true : false
            };

            OSCOM.a.Account.siteCounter += 1;

            listingData.sites.push(site);
        });

        $('#liveSitesUserGrid').append(Mustache.render(template, listingData));

        $('#liveSitesUserGrid > div[data-ambassador-showcase-flag="true"]').addClass('osc-showcase-card-border');

        componentHandler.upgradeElements($('#liveSitesUserGrid').get(0));

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
        OSCOM.a.Account.isGettingListing = false;
    }, function() {
        OSCOM.a.Account.isGettingListing = false;

        $('#loadMoreSpinner').removeClass('is-active');

        toastr.error(OSCOM.def.js_error_general);
    });
};

$(function() {
    OSCOM.a.Account.showUserListing();
});
