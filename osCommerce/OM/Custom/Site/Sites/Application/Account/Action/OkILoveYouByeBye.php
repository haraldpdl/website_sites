<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM,
    Registry
};

class OkILoveYouByeBye
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');

        $params = $application->getRedirectUrlParams();

        if (!isset($_SESSION['Website']['Account'])) {
            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
        }

        $_SESSION['logout_redirect'] = [
            'url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params))
        ];

        $OSCOM_MessageStack->add(OSCOM::getDefaultSiteApplication(), OSCOM::getDef('ms_logout_success'), 'success');

        OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'OkILoveYouByeBye', 'SSL'));
    }
}
