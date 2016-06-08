/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global toastr */

'use strict';

$.extend(OSCOM, {
    cardBackgroundColors: [
        'red',
        'red-900',
        'pink',
        'pink-900',
        'purple',
        'purple-900',
        'deep-purple',
        'indigo',
        'indigo-900',
        'blue',
        'light-blue',
        'cyan',
        'teal',
        'green',
        'green-900',
        'light-green',
        'lime',
        'yellow',
        'amber',
        'orange',
        'orange-900',
        'deep-orange',
        'brown',
        'brown-900',
        'blue-grey',
        'blue-grey-900'
    ],
    getCardBackgroundColor: function(siteTitle) {
        var pos = ((siteTitle.charAt(0).toLowerCase()).charCodeAt(0) - 97) + 1;
        var g = (pos > 0 && pos < 27) ? pos : 5;

        return this.cardBackgroundColors[g - 1];
    },
    generateUrl: function(exclude, parameters, base) {
        if (base === undefined) {
            base = this.urlBase;
        }

        if (parameters === undefined) {
            parameters = $.extend({}, this.urlBaseReq); // clone object, not reference it
        }

        if (exclude !== false) {
            $(exclude).each(function(i, ex) {
                if (parameters[ex] !== undefined) {
                    delete parameters[ex];
                }
            });
        }

        return base + '?' + $.param(parameters).replace(/\=\&/g, '&').replace(/=$/, '');
    },
    escapeHtml: function(string, strict) {
        var map = {
            '&': '&amp;',
            '"': '&quot;',
            '<': '&lt;',
            '>': '&gt;'
        };

        if (strict === true) {
            map['\''] = '&#039;';
        }

        return this.strReplace(string, map);
    },
    strReplace: function(string, map) {
        if (typeof map !== 'object') {
            return string;
        }

        return string.replace(new RegExp(Object.keys(map).join('|'), 'g'), function(m) {
            return map[m];
        });
    }
});

$(function() {
/* MDL 1.1.3 does not allow tabs to be dynamically added
    var jsonBreadcrumb = $.parseJSON($('#jsonldBreadcrumb').html());

    for (var i = 0, n = jsonBreadcrumb.itemListElement.length; i < n; i += 1) {
        $('#breadcrumbNav').append('<a href="' + jsonBreadcrumb.itemListElement[i].item['@id'] + '" class="mdl-layout__tab">' + jsonBreadcrumb.itemListElement[i].item.name + '</a>');
    }

    componentHandler.upgradeElements($('#breadcrumbNav').get(0));
*/

    if (OSCOM.loggedIn === true) {
        $('#userMenu').append('<li class="mdl-menu__item"><a href="' + OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Account': ''})) + '">' + OSCOM.def.js_navbutton_account_my_account + '</a></li>');
        $('#userMenu').append('<li class="mdl-menu__item"><a href="' + OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Account': '', 'OkILoveYouByeBye': '', 'category': OSCOM.categoryPath.join('--'), 'country': OSCOM.country})) + '">' + OSCOM.def.js_navbutton_account_log_out + '</a></li>');
    } else {
        $('#userMenu').append('<li class="mdl-menu__item"><a href="' + OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Account': '', 'Login': '', 'category': OSCOM.categoryPath.join('--'), 'country': OSCOM.country})) + '">' + OSCOM.def.js_navbutton_account_log_in + '</a></li>');
        $('#userMenu').append('<li class="mdl-menu__item"><a href="' + OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Account': '', 'Create': '', 'category': OSCOM.categoryPath.join('--'), 'country': OSCOM.country})) + '">' + OSCOM.def.js_navbutton_account_create_account + '</a></li>');
    }

    var clist = '';

    OSCOM.countries.forEach(function(c) {
        clist += '<option value="' + OSCOM.escapeHtml(c.code) + '">' + OSCOM.escapeHtml(c.title) + '</option>';
    });

    $(clist).appendTo('#countriesList');

    if (OSCOM.country.length > 0) {
        $('#countriesList').val(OSCOM.country);
    }

    clist = '';

    OSCOM.categories.forEach(function(c) {
        var g = {};

        $.each(c.code.split('&'), function(x, y) {
            g[y] = '';
        });

        if (OSCOM.country.length > 0) {
            g.country = OSCOM.country;
        }

        clist += '<a class="mdl-navigation__link mdl-navigation__link" href="' + OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, g)) + '">' + OSCOM.escapeHtml(c.title) + '</a>';
    });

    $(clist).appendTo('#categoriesList');

    $('#countriesList').change(function() {
        var countryFilterUrl = {};

        if (this.value !== 'all') {
            countryFilterUrl.country = this.value;
        }

        window.location.href = OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, countryFilterUrl));
    });
});

toastr.options = {
    'escapeHtml': true,
    'closeButton': true,
    'positionClass': 'toast-top-full-width',
    'timeOut': 0
};
