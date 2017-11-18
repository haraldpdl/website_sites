/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

'use strict';

OSCOM.a.Index.isGettingListing = false;

$(function() {
    if ((typeof OSCOM.a.Index.currentShowcasePartner !== 'undefined') || ((typeof OSCOM.a.Index.currentShowcaseCategory !== 'undefined') && (OSCOM.a.Index.currentShowcaseCategory === 'ambassadors'))) {
        OSCOM.a.Index.showShowcaseListing();
    } else {
        OSCOM.a.Index.showShowcasePartners();
    }
});
