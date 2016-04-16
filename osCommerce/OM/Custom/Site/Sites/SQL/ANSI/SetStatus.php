<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class SetStatus
{
    public static function execute(array $data): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qsite = $OSCOM_PDO->prepare('update :table_website_live_shops set status = :status where id = :id');
        $Qsite->bindInt(':status', $data['status']);
        $Qsite->bindInt(':id', $data['id']);
        $Qsite->execute();

        return ($Qsite->rowCount() === 1);
    }
}
