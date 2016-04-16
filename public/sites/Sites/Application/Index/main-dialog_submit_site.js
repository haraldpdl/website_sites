/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global dialogPolyfill, toastr */

'use strict';

$('#dialog').appendTo('body'); // mdl 1.1 dialog must be direct child of body so jiggle it with appendTo()

OSCOM.a.Index.dialogSite = $('#dialog').get(0);

if (!OSCOM.a.Index.dialogSite.showModal) { // use compatibility polyfill for browsers that don't support <dialog>
    dialogPolyfill.registerDialog(OSCOM.a.Index.dialogSite);
}

OSCOM.a.Index.showSiteDialog = function() {
    if (OSCOM.loggedIn !== true) {
        var params = $.extend({}, OSCOM.urlBaseReq, {
            'Account': '',
            'Login': '',
            'Redirect': 'Add'
        });

        if (OSCOM.categoryPath.length > 0) {
            $.extend(params, {
                'category': OSCOM.categoryPath.join('--')
            });
        }

        if (OSCOM.country.length > 0) {
            $.extend(params, {
                'country': OSCOM.country
            });
        }

        window.location.href = OSCOM.generateUrl(false, params);

        return false;
    }

    $('#dialog .osc-dialog-content').hide();
    $('#dialog .osc-dialog-prepare').show();

// reset form
    $('#formAddSite .osc-error-field').css('color', '');
    $('#siteName').val($('#siteName').prop('defaultValue')).parent().removeClass('is-dirty');
    $('#siteUrl').val($('#siteUrl').prop('defaultValue'));
    $('#siteCategory option:selected').prop('selected', false);
    $('#siteCategory').parent().removeClass('is-dirty');
    $('#siteCategory').val($('#siteCategory').find('option[selected]').val());
    $('#siteCountry option:selected').prop('selected', false);
    $('#siteCountry').parent().removeClass('is-dirty');
    $('#siteCountry').val($('#siteCountry').find('option[selected]').val());
    $('#disclaimerCheck').prop('checked', false).parent().removeClass('is-checked');

    OSCOM.a.Index.dialogSite.showModal();

    var dfd = $.Deferred();

    if (($('#siteCountry option').length > 1) && ($('#siteCategory option').length > 1)) {
        dfd.resolve();
    } else {
        var formData = {
            'publicToken': OSCOM.secureToken
        };

        var rpcNewSitePrereq = $.post(OSCOM.generateUrl(false, $.extend({}, {'RPC': ''}, OSCOM.urlBaseReq, {'Index': '', 'GetNewSitePrerequisites': ''})), formData, function(data) {
            if ((data === null) || (typeof data !== 'object')) {
                return dfd.reject();
            }

            if (data.hasOwnProperty('error')) {
                return dfd.reject(data.error);
            }

            if (data.hasOwnProperty('countries') && (data.countries.length > 0) && data.hasOwnProperty('categories') && (data.categories.length > 0)) {
                var countriesList = '';

                data.countries.forEach(function(value) {
                    countriesList += '<option value="' + OSCOM.escapeHtml(value.code) + '">' + OSCOM.escapeHtml(value.title) + '</option>';
                });

                $('#siteCountry').append(countriesList);

                var categoriesList = '';

                data.categories.forEach(function(value) {
                    categoriesList += '<optgroup label="' + OSCOM.escapeHtml(value.title) + '">';

                    value.children.forEach(function(v2) {
                        categoriesList += '<option value="' + OSCOM.escapeHtml(value.code) + '/' + OSCOM.escapeHtml(v2.code) + '">' + OSCOM.escapeHtml(v2.title) + '</option>';
                    });

                    categoriesList += '</optgroup>';
                });

                $('#siteCategory').append(categoriesList);

                dfd.resolve();
            } else {
                dfd.reject();
            }
        }, 'json').fail(function() {
            dfd.reject();
        });

// abort json call if it takes longer than 10 seconds
        setTimeout(function() {
            if (rpcNewSitePrereq.state() === 'pending') {
                rpcNewSitePrereq.abort();
            }
        }, 10000);
    }

    $.when(dfd).then(function() {
        $('#dialog .osc-dialog-prepare').hide();
        $('#dialog .osc-dialog-content').show();
    }, function(data) {
        OSCOM.a.Index.dialogSite.close();

        if (data !== null) {
            switch (data) {
            case 100:
                toastr.error(OSCOM.def.js_error_daily_limit);
                break;

            case 200:
                toastr.error(OSCOM.def.js_error_login_required);
                break;

            case 300:
                toastr.error(OSCOM.def.js_error_add_site_security_token_integrity, null, {
                    escapeHtml: false
                });
                break;

            default:
                toastr.error(OSCOM.def.js_error_general);
            }
        } else {
            toastr.error(OSCOM.def.js_error_general);
        }
    });
};

$('#addSiteButton').click(function(event) {
    event.preventDefault();

    toastr.clear();

    OSCOM.a.Index.showSiteDialog();
});

$('#dialogButtonClose').click(function(event) {
    event.preventDefault();

    toastr.clear();

    OSCOM.a.Index.dialogSite.close();
});

$('#dialog').on('cancel', function(event) { // when ESC is pressed simulate a button close click event to close the dialog
    event.preventDefault();

    $('#dialogButtonClose').click();
});

$('#formAddSite').submit(function(event) {
    event.preventDefault();

    toastr.clear();

    $('#dialogSaveSpinner').parent().find('button').hide();
    $('#dialogSaveSpinner').addClass('is-active');

    var error = false;

    if ($('#siteName').val().length < 1) {
        $('#siteNameField .osc-error-field').css('color', '#ff0000');

        error = true;
    } else {
        $('#siteNameField .osc-error-field').css('color', '');
    }

    if (/^(http|https)\:\/\/.+/.test($('#siteUrl').val()) === false) {
        $('#siteUrlField .osc-error-field').css('color', '#ff0000');

        error = true;
    } else {
        $('#siteUrlField .osc-error-field').css('color', '');
    }

    if ($('#siteCategory option:selected').val().length < 1) {
        $('#siteCategoryField .osc-error-field').css('color', '#ff0000');

        error = true;
    } else {
        $('#siteCategoryField .osc-error-field').css('color', '');
    }

    if ($('#siteCountry option:selected').val().length < 1) {
        $('#siteCountryField .osc-error-field').css('color', '#ff0000');

        error = true;
    } else {
        $('#siteCountryField .osc-error-field').css('color', '');
    }

    if ($('#disclaimerCheck').prop('checked') !== true) {
        $('#disclaimerCheckField .osc-error-field').css('color', '#ff0000');

        error = true;
    } else {
        $('#disclaimerCheckField .osc-error-field').css('color', '');
    }

    if (error === true) {
        toastr.error(OSCOM.def.js_error_all_fields_required, null, {
            target: '#dialog'
        });

        $('#dialogSaveSpinner').removeClass('is-active');
        $('#dialogSaveSpinner').parent().find('button').show();
    } else {
        var dfd = $.Deferred();

        var formData = {
            'publicToken': OSCOM.secureToken,
            'name': $('#siteName').val(),
            'url': $('#siteUrl').val(),
            'category': $('#siteCategory option:selected').val(),
            'country': $('#siteCountry option:selected').val(),
            'disclaimerCheck': $('#disclaimerCheck').val()
        };

        var rpcNewSiteAdd = $.post(OSCOM.generateUrl(false, $.extend({}, {'RPC': ''}, OSCOM.urlBaseReq, {'Index': '', 'Add': ''})), formData, function(data) {
            if ((data === null) || (typeof data !== 'object')) {
                return dfd.reject();
            }

            if (data.hasOwnProperty('error')) {
                return dfd.reject(data);
            }

            if (data.hasOwnProperty('status') && (data.status === 1)) {
                dfd.resolve();
            } else {
                dfd.reject();
            }
        }, 'json').fail(function() {
            dfd.reject();
        });

// abort json call if it takes longer than 10 seconds
        setTimeout(function() {
            if (rpcNewSiteAdd.state() === 'pending') {
                rpcNewSiteAdd.abort();
            }
        }, 10000);

        $.when(dfd).then(function() {
            window.location.href = OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Account': 'new-site-added'}));
        }, function(data) {
            $('#dialogSaveSpinner').removeClass('is-active');
            $('#dialogSaveSpinner').parent().find('button').show();

            if ((data !== null) && (data.error !== null)) {
                switch (data.error) {
                case 100:
                    OSCOM.a.Index.dialogSite.close();

                    toastr.error(OSCOM.def.js_error_daily_limit);

                    break;

                case 200:
                    OSCOM.a.Index.dialogSite.close();

                    toastr.error(OSCOM.def.js_error_login_required);

                    break;

                case 300:
                    OSCOM.a.Index.dialogSite.close();

                    toastr.error(OSCOM.def.js_error_add_site_security_token_integrity, null, {
                        escapeHtml: false
                    });

                    break;

                case 400:
                    if ($.inArray('name', data.fields) > -1) {
                        $('#siteNameField .osc-error-field').css('color', '#ff0000');
                    }

                    if ($.inArray('url', data.fields) > -1) {
                        $('#siteUrlField .osc-error-field').css('color', '#ff0000');
                    }

                    if ($.inArray('category', data.fields) > -1) {
                        $('#siteCategoryField .osc-error-field').css('color', '#ff0000');
                    }

                    if ($.inArray('country', data.fields) > -1) {
                        $('#siteCountryField .osc-error-field').css('color', '#ff0000');
                    }

                    if ($.inArray('disclaimerCheck', data.fields) > -1) {
                        $('#disclaimerCheckField .osc-error-field').css('color', '#ff0000');
                    }

                    toastr.error(OSCOM.def.js_error_all_fields_required, null, {
                        target: '#dialog'
                    });

                    break;

                case 500:
                    $('#siteUrlField .osc-error-field').css('color', '#ff0000');

                    toastr.error(OSCOM.def.js_error_url_not_accessible, null, {
                        target: '#dialog'
                    });

                    break;

                default:
                    toastr.error(OSCOM.def.js_error_add_site_general, null, {
                        target: '#dialog'
                    });

                    break;
                }
            } else {
                toastr.error(OSCOM.def.js_error_add_site_general, null, {
                    target: '#dialog'
                });
            }
        });
    }
});
