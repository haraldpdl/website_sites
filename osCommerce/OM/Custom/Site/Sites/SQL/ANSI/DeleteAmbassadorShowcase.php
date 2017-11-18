<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class DeleteAmbassadorShowcase
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        return $OSCOM_PDO->save('website_live_shops_ambassador_showcase', [
            'date_removed' => 'now()',
            'removed_ip_address' => $params['ip_address']
        ], [
            'live_shop_id' => $params['site_id'],
            'user_id' => $params['user_id']
        ]) === 1;
    }
}
