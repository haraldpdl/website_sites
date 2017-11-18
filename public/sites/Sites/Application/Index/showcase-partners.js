/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global toastr */

'use strict';

OSCOM.a.Index.showShowcasePartners = function() {
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
        'GetShowcasePartners': ''
    });

    if (typeof OSCOM.a.Index.currentShowcaseCategory !== 'undefined') {
        $.extend(loadUrlParams, {'category': OSCOM.a.Index.currentShowcaseCategory});
    }

    var dfd = $.Deferred();

    var rpcGetListing = $.getJSON(OSCOM.generateUrl(false, loadUrlParams), function(result) {
        if ((result === null) || (typeof result !== 'object') || (Array.isArray(result) === false)) {
            return dfd.reject();
        }

        $('#loadMoreSpinner').removeClass('is-active');

        var template = $('#cardPartnerTemplate').html();
        Mustache.parse(template);

        var data = {
            'partners': []
        };

        result.forEach(function(p) {
            var currentPartnerCategoryParam = {};

            if (typeof p.category_code !== 'undefined') {
                currentPartnerCategoryParam[p.category_code] = '';
            }

            var currentPartnerParam = {};
            currentPartnerParam[p.code] = '';

            var partner = {
                'code': p.code,
                'title': p.title,
                'url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Showcase': ''}, currentPartnerCategoryParam, currentPartnerParam)),
                'colorClass': 'mdl-color--' + OSCOM.getCardBackgroundColor(p.title),
                'img_src': OSCOM.siteImagePreviewBase + p.site_round_id + '/' + p.site_public_id + '.png',
                'total_sites': p.total_sites,
                'total_sites_label': (p.total_sites > 1) ? OSCOM.def.js_sites_plural : OSCOM.def.js_sites_single
            };

            if (typeof p.category_code !== 'undefined') {
                partner.category_title = p.category_title;
                partner.category_url = OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Showcase': ''}, currentPartnerCategoryParam));
            }

            data.partners.push(partner);
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
