<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetAmbassadorShowcase
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = '
            select
                sql_calc_found_rows
                s.id as site_id, s.public_id as site_public_id
            from
                :table_website_live_shops_ambassador_showcase las,
                :table_website_live_shops s
            where
                las.date_removed is null and
                las.live_shop_id = s.id and
                las.user_id = s.user_id and
                s.status = :status
            order by rand()
            limit 1;
            select found_rows();';

        $Qambassadors = $OSCOM_PDO->prepare($sql);
        $Qambassadors->bindInt(':status', $params['status']);
        $Qambassadors->execute();

        $result = $Qambassadors->fetchAll();

        if (isset($result[0])) {
            $Qambassadors->nextRowset();

            $result[0]['total_sites'] = $Qambassadors->fetchColumn();
        }

        return $result;
    }
}
