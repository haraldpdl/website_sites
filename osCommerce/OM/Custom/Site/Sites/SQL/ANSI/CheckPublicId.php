<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class CheckPublicId
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = 'select id from :table_website_live_shops where public_id = :public_id';

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $sql .= ' and status = :status';
        }

        $sql .= ' limit 1';

        $Qcheck = $OSCOM_PDO->prepare($sql);
        $Qcheck->bindValue(':public_id', $params['public_id']);

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $Qcheck->bindInt(':status', 3);
        }

        $Qcheck->execute();

        return $Qcheck->fetch() !== false;
    }
}
