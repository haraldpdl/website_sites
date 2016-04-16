<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class LogClick
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $fields = [
            'live_shop_id' => ':live_shop_id',
            'date_added' => 'now()',
            'ip_address' => ':ip_address'
        ];

        if (isset($params['user_id'])) {
            $fields['user_id'] = ':user_id';
        }

        $sql = 'insert into :table_website_live_shops_clicks (' . implode(', ', array_keys($fields)) . ') values (' . implode(', ', $fields) . ')';

        $Qlog = $OSCOM_PDO->prepare($sql);
        $Qlog->bindInt(':live_shop_id', $params['id']);
        $Qlog->bindInt(':ip_address', $params['ip_address']);

        if (isset($params['user_id'])) {
            $Qlog->bindInt(':user_id', $params['user_id']);
        }

        $Qlog->execute();

        return ($Qlog->rowCount() === 1);
    }
}
