<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class DeleteShowcase
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $data = [
            'live_shop_id' => $params['site_id'],
            'partner_id' => $params['partner_id']
        ];

        return ($OSCOM_PDO->delete('website_live_shops_showcase', $data) === 1);
    }
}
