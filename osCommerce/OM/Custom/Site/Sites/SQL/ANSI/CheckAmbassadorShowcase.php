<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class CheckAmbassadorShowcase
{
    public static function execute(array $params): int
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qtotal = $OSCOM_PDO->get([
            'website_live_shops_ambassador_showcase las',
            'website_live_shops s'
        ], [
            'las.id'
        ], [
            'las.date_removed' => 'null',
            'las.live_shop_id' => [
                'rel' => 's.id'
            ],
            'las.user_id' => [
                'rel' => 's.user_id'
            ],
            's.status' => $params['status']
        ], null, 1);

        return $Qtotal->hasValue('id') ? 1 : 0;
    }
}
