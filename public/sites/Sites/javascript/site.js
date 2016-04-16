/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global toastr */

'use strict';

$.extend(OSCOM, {
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
    escapeHtml: function(string, quoteStyle, charset, doubleEncode) { // http://phpjs.org/functions/htmlspecialchars/ (github Oct 18 2015)
        var optTemp = 0,
            i = 0,
            noquotes = false;

        if (typeof quoteStyle === 'undefined' || quoteStyle === null) {
            quoteStyle = 2;
        }

        string = string || '';
        string = string.toString();

        if (doubleEncode !== false) {
            // Put this first to avoid double-encoding
            string = string.replace(/&/g, '&amp;');
        }

        string = string.replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

        var OPTS = {
            'ENT_NOQUOTES': 0,
            'ENT_HTML_QUOTE_SINGLE': 1,
            'ENT_HTML_QUOTE_DOUBLE': 2,
            'ENT_COMPAT': 2,
            'ENT_QUOTES': 3,
            'ENT_IGNORE': 4
        };

        if (quoteStyle === 0) {
            noquotes = true;
        }

        if (typeof quoteStyle !== 'number') {
            // Allow for a single string or an array of string flags
            quoteStyle = [].concat(quoteStyle);

            for (i = 0; i < quoteStyle.length; i += 1) {
                // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
                if (OPTS[quoteStyle[i]] === 0) {
                    noquotes = true;
                } else if (OPTS[quoteStyle[i]]) {
                    optTemp = optTemp | OPTS[quoteStyle[i]];
                }
            }

            quoteStyle = optTemp;
        }

        if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
            string = string.replace(/'/g, '&#039;');
        }

        if (!noquotes) {
            string = string.replace(/"/g, '&quot;');
        }

        return string;
    }
});

$(function() {
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
