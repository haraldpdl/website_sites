/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

/*global dialogPolyfill, toastr */

'use strict';

$('#dialogModerateSite').appendTo('body'); // mdl 1.1 dialog must be direct child of body so jiggle it with appendTo()

OSCOM.a.Index.dialogModerateSite = $('#dialogModerateSite').get(0);

if (!OSCOM.a.Index.dialogModerateSite.showModal) { // use compatibility polyfill for browsers that don't support <dialog>
    dialogPolyfill.registerDialog(OSCOM.a.Index.dialogModerateSite);
}

OSCOM.a.Index.showModerateSiteDialog = function(action, publicId, page) {
    if (OSCOM.loggedIn !== true) {
        var params = $.extend({}, OSCOM.urlBaseReq);

        $.extend(params, {
            'Account': '',
            'Login': ''
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

        if (page > 0) {
            $.extend(params, {
                'page': page
            });
        }

        window.location.href = OSCOM.generateUrl(false, params);

        return false;
    }

    if (OSCOM.isAdmin !== true) {
        window.location.href = OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq));

        return false;
    }

    $('#dialogModerateSite .osc-dialog-content').hide();
    $('#dialogModerateSite .osc-dialog-prepare').show();

// reset form
    $('#dialogModerateSite').removeData('publicId');
    $('#dialogModerateSite .mdl-card__title').removeClass().addClass('mdl-card__title mdl-color-text--white');
    $('#dialogModerateSite .mdl-card__title-text').empty();
    $('#dialogModerateSite .mdl-card__media').empty();
    $('#dialogModerateSiteSpinner').removeClass('is-active');
    $('#dialogModerateSite .mdl-dialog__actions button').show();

    OSCOM.a.Index.dialogModerateSite.showModal();

    var dfd = $.Deferred();

    var formData = {
        'publicToken': OSCOM.secureToken,
        'site': publicId
    };

    var rpcModSitePrereq = $.post(OSCOM.generateUrl(false, $.extend({}, {'RPC': ''}, OSCOM.urlBaseReq, {'Index': '', 'GetModerateSitePrerequisites': ''})), formData, function(data) {
        if ((data === null) || (typeof data !== 'object')) {
            return dfd.reject();
        }

        if (data.hasOwnProperty('error')) {
            return dfd.reject(data.error);
        }

        if (data.hasOwnProperty('site')) {
            $('#dialogModerateSite').data('publicId', data.site.public_id);
            $('#dialogModerateSite').data('page', page);

            $('#dialogModerateSite .mdl-card__title').addClass('mdl-color--' + OSCOM.getCardBackgroundColor(data.site.title));
            $('#dialogModerateSite .mdl-card__title-text').html(OSCOM.escapeHtml(data.site.title));

            $('<a>', {
                href: OSCOM.generateUrl(false, $.extend({}, OSCOM.urlBaseReq, {'Go': data.site.public_id})),
                rel: 'nofollow noopener noreferrer',
                target: '_blank'
            }).appendTo('#dialogModerateSite .mdl-card__media');

            $('<img>', {
                src: OSCOM.siteImagePreviewBase + data.site.round_id + '/' + data.site.public_id + '.png',
                style: 'max-width: 100%'
            }).appendTo('#dialogModerateSite .mdl-card__media a');

            dfd.resolve();
        } else {
            dfd.reject();
        }
    }, 'json').fail(function() {
        dfd.reject();
    });

// abort json call if it takes longer than 10 seconds
    setTimeout(function() {
        if (rpcModSitePrereq.state() === 'pending') {
            rpcModSitePrereq.abort();
        }
    }, 10000);

    $.when(dfd).then(function() {
        $('#dialogModerateSite .mdl-dialog__actions button:not([data-action="close"])').hide();
        $('#dialogModerateSite .mdl-dialog__actions button[data-action="' + action + '"]').show();

        $('#dialogModerateSite .osc-dialog-prepare').hide();
        $('#dialogModerateSite .osc-dialog-content').show();
    }, function(data) {
        OSCOM.a.Index.dialogModerateSite.close();

        if (data !== null) {
            switch (data) {
            case 100:
                toastr.error(OSCOM.def.js_error_mod_site_nonexistent);
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

$('#liveSitesGrid').on('click', 'a.dialogModerateLink', function(event) {
    event.preventDefault();

    toastr.clear();

    OSCOM.a.Index.showModerateSiteDialog($(this).data('action'), $('#c' + $(this).data('id')).data('publicId'), $('#c' + $(this).data('id')).data('page'));
});

$('#dialogModerateSite .mdl-dialog__actions button[data-action="close"]').click(function(event) {
    event.preventDefault();

    toastr.clear();

    OSCOM.a.Index.dialogModerateSite.close();
});

$('#dialogModerateSite').on('cancel', function(event) { // when ESC is pressed simulate a button close click event to close the dialog
    event.preventDefault();

    $('#dialogModerateSite .mdl-dialog__actions button[data-action="close"]').click();
});

$('#dialogModerateSite .mdl-dialog__actions button:not([data-action="close"])').click(function(event) {
    event.preventDefault();

    toastr.clear();

    $('#dialogModerateSite .mdl-dialog__actions button').hide();
    $('#dialogModerateSiteSpinner').addClass('is-active');

    var dfd = $.Deferred();

    var action = $(this).data('action');

    var data = {
        'publicToken': OSCOM.secureToken,
        'action': action,
        'publicId': $('#dialogModerateSite').data('publicId')
    };

    var params = {
        'RPC': ''
    };

    $.extend(params, OSCOM.urlBaseReq, {
        'Index': '',
        'Moderate': ''
    });

    var rpcModSite = $.post(OSCOM.generateUrl(false, params), data, function(response) {
        if ((response === null) || (typeof response !== 'object')) {
            return dfd.reject();
        }

        if (response.hasOwnProperty('error')) {
            return dfd.reject(response);
        }

        if (response.hasOwnProperty('status') && (response.status === 1)) {
            dfd.resolve();
        } else {
            dfd.reject();
        }
    }, 'json').fail(function() {
        dfd.reject();
    });

// abort json call if it takes longer than 10 seconds
    setTimeout(function() {
        if (rpcModSite.state() === 'pending') {
            rpcModSite.abort();
        }
    }, 10000);

    $.when(dfd).then(function() {
        OSCOM.a.Index.dialogModerateSite.close();

        OSCOM.a.Index.showListing($('#dialogModerateSite').data('page'));

        toastr.success(OSCOM.def.js_mod_success);
    }, function(response) {
        $('#dialogModerateSiteSpinner').removeClass('is-active');
        $('#dialogModerateSite .mdl-dialog__actions button[data-action="close"]').show();
        $('#dialogModerateSite .mdl-dialog__actions button[data-action="' + action + '"]').show();

        if ((typeof response !== 'undefined') && response.hasOwnProperty('error')) {
            switch (response.error) {
            case 100:
                OSCOM.a.Index.dialogModerateSite.close();

                toastr.error(OSCOM.def.js_error_mod_site_nonexistent);

                break;

            case 200:
                OSCOM.a.Index.dialogModerateSite.close();

                toastr.error(OSCOM.def.js_error_login_required);

                break;

            case 300:
                OSCOM.a.Index.dialogModerateSite.close();

                toastr.error(OSCOM.def.js_error_add_site_security_token_integrity, null, {
                    escapeHtml: false
                });

                break;

            case 400:
                OSCOM.a.Index.dialogModerateSite.close();

                toastr.error(OSCOM.def.js_error_mod_invalid_action);

                break;

            case 500:
            default:
                toastr.error(OSCOM.def.js_error_general, null, {
                    target: '#dialogModerateSite'
                });

                break;
            }
        } else {
            toastr.error(OSCOM.def.js_error_general, null, {
                target: '#dialogModerateSite'
            });
        }
    });
});
