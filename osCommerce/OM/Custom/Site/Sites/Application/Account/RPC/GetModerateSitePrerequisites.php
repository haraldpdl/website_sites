<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account\RPC;

use osCommerce\OM\Core\Registry;

use osCommerce\OM\Core\Site\Sites\Sites;

class GetModerateSitePrerequisites
{
    public static function execute()
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        $result = [];

        if (isset($_SESSION['Website']['Account'])) {
            $publicToken = isset($_POST['publicToken']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicToken'])) : '';
            $site = isset($_POST['site']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['site'])) : '';

            if ($publicToken !== md5($_SESSION['Website']['public_token'])) {
                $result['error'] = 300;
            } elseif (empty($site) || !Sites::exists($site) || (Sites::get($site, 'user_id') != $_SESSION['Website']['Account']['id'])) {
                $result['error'] = 100;
            }

            if (empty($result)) {
                $result['site'] = Sites::get($site);
                $result['category'] = $OSCOM_CategoryTree->getFullPath($result['site']['category_id'], 'title');
                $result['country'] = Sites::getCountry(Sites::getCountryCode($result['site']['country_id']), 'title');
            }
        } else {
            $result['error'] = 200;
        }

        echo json_encode($result);
    }
}
