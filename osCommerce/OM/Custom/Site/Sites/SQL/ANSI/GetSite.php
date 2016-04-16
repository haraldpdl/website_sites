<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetSite
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qsite = $OSCOM_PDO->prepare('select * from :table_website_live_shops where public_id = :public_id limit 1');
        $Qsite->bindValue(':public_id', $params['public_id']);
        $Qsite->setCache('sites-' . $params['public_id'], 1440);
        $Qsite->execute();

        return $Qsite->fetch();
    }
}
