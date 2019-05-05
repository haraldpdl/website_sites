<?php
/**
 * osCommerce Sites Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetPending
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qsites = $OSCOM_PDO->get('website_live_shops', '*', [
            'status' => $params['status']
        ], 'date_added', $params['limit'] ?? null);

        return $Qsites->fetchAll();
    }
}
