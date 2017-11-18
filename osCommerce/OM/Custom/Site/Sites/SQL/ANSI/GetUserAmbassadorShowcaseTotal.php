<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetUserAmbassadorShowcaseTotal
{
    public static function execute(array $params): int
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qtotal = $OSCOM_PDO->get([
            'website_live_shops_ambassador_showcase las',
            'website_live_shops s'
        ], [
            'count(*) as total'
        ], [
            'las.user_id' => [
                'val' => $params['user_id'],
                'rel' => 's.user_id'
            ],
            'las.date_removed' => 'null',
            'las.live_shop_id' => [
                'rel' => 's.id'
            ],
            's.status' => $params['status']
        ]);

        return $Qtotal->valueInt('total');
    }
}
