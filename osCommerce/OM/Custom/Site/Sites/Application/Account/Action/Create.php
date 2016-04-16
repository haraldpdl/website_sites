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
    OSCOM
};

class Create
{
    public static function execute(ApplicationAbstract $application)
    {
        $params = $application->getRedirectUrlParams();

        if (isset($_SESSION['Website']['Account'])) {
            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
        }

        $_SESSION['login_redirect'] = [
            'url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)),
            'cancel_url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)),
            'cancel_text' => OSCOM::getDef('redirect_cancel_return_to_site')
        ];

        OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'Create', 'SSL'));
    }
}
