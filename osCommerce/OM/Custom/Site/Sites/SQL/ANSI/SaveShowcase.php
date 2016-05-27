<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class SaveShowcase
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $data = [
            'live_shop_id' => $params['site_id'],
            'partner_id' => $params['partner_id'],
            'user_id' => $params['user_id'],
            'ip_address' => $params['ip_address'],
            'date_added' => 'now()'
        ];

        return ($OSCOM_PDO->save('website_live_shops_showcase', $data) === 1);
    }
}
