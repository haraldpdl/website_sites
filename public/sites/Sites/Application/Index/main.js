/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global componentHandler, toastr */

'use strict';

OSCOM.a.Index.isGettingListing = false;
OSCOM.a.Index.listingCurrentPage = 1;
OSCOM.a.Index.siteCounter = 0;
OSCOM.a.Index.loadMoreUrlParams = {
    'RPC': ''
};

$.extend(OSCOM.a.Index.loadMoreUrlParams, OSCOM.urlBaseReq, {
    'Index': '',
    'GetListing': ''
});

OSCOM.categoryPath.forEach(function(value) {
    OSCOM.a.Index.loadMoreUrlParams[value] = '';
});

if (OSCOM.country.length > 0) {
    OSCOM.a.Index.loadMoreUrlParams.country = OSCOM.country;
}

OSCOM.a.Index.showListing = function(page) {
    if (OSCOM.a.Index.isGettingListing === true) {
        return false;
    }

    OSCOM.a.Index.isGettingListing = true;

    toastr.clear();

    var requestedPage = (page !== undefined) ? page : OSCOM.a.Index.listingCurrentPage;

    var dfd = $.Deferred();

    var countryFilterParam = {};

    if (OSCOM.country.length > 0) {
        countryFilterParam.country = OSCOM.country;
    }

    if (requestedPage > 1) {
        OSCOM.a.Index.loadMoreUrlParams.page = requestedPage;
    }

    var rpcGetListing = $.getJSON(OSCOM.generateUrl(false, OSCOM.a.Index.loadMoreUrlParams), function(data) {
        if ((data === null) || (typeof data !== 'object') || ($.isArray(data) === false)) {
            return dfd.reject();
        }

        $('#loadMoreSpinner').removeClass('is-active');

        if (page !== undefined) {
            $('#liveSitesGrid > div').filter(function() {
                return $(this).data('page') >= page;
            }).remove();
        }

        var template = $('#cardTemplate').html();
        Mustache.parse(template);

        data.forEach(function(value) {
            var parentCategoryCode = value.parent_category_name;
            if (parentCategoryCode !== null) {
                parentCategoryCode = parentCategoryCode.replace(/ /g, '-');
            }

            var categoryCode = value.category_name;
            categoryCode = categoryCode.replace(/ /g, '-');

//some old entries are still assigned top level categories
            if (parentCategoryCode === null) {
                value.parent_category_name = value.category_name;
                parentCategoryCode = categoryCode;
            }

            var parentCategoryCodeFilterParam = {};
            parentCategoryCodeFilterParam[parentCategoryCode] = '';

            var categoryCodeFilterParam = {};
            categoryCodeFilterParam[categoryCode] = '';

            var siteData = {
                'id': OSCOM.a.Index.siteCounter,
                'publicId': value.public_id,
                'pageSet': requestedPage,
                'colorClass': 'mdl-color--' + OSCOM.getCardBackgroundColor(value.title),
                'title': OSCOM.escapeHtml(value.title),
                'url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Go': value.public_id})),
                'img_src': OSCOM.siteImagePreviewBase + value.round_id + '/' + value.public_id + '.png',
                'parent_category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam, countryFilterParam)),
                'parent_category_name': value.parent_category_name,
                'category_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, parentCategoryCodeFilterParam, categoryCodeFilterParam, countryFilterParam)),
                'category_name': value.category_name,
                'country_url': OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'country': value.country_code})),
                'country_name': value.country_name,
                'total_likes': value.total_likes,
                'lang_in_country': OSCOM.def.js_site_card_in_country,
                'lang_admin_disable': OSCOM.def.js_site_card_admin_disable,
                'lang_admin_requeue': OSCOM.def.js_site_card_admin_requeue
            };

            $('#liveSitesGrid').append(Mustache.render(template, siteData));

            if (OSCOM.loggedIn && OSCOM.isAdmin) {
                $('#c' + OSCOM.a.Index.siteCounter + ' .osc-card-admin').show();
            }

            componentHandler.upgradeElements($('#c' + OSCOM.a.Index.siteCounter).get(0));

            OSCOM.a.Index.siteCounter += 1;
        });

        if (data.length === 24) {
            $('#loadMoreButton').show();
        }

        if (page !== undefined) {
            OSCOM.a.Index.listingCurrentPage = page + 1;
        } else {
            OSCOM.a.Index.listingCurrentPage += 1;
        }

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

        var pagesets = $('#liveSitesGrid > div[data-page]').map(function() {
            return parseInt($(this).data('page'), 10);
        }).get();

        if (pagesets.length < 1) {
            $('#loadMoreButton').show();
        } else {
            var highest = Math.max.apply(Math, pagesets);

            if ($('#liveSitesGrid > div[data-page="' + highest + '"]').length === 24) {
                $('#loadMoreButton').show();
            }
        }
    });
};

$('#loadMoreButton').click(function() {
    $('#loadMoreButton').hide();

    $('#loadMoreSpinner').addClass('is-active');

    OSCOM.a.Index.showListing();
});

$(function() {
    OSCOM.a.Index.showListing();
});
