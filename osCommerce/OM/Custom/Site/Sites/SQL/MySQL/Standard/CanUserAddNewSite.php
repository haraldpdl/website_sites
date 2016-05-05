<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\MySQL\Standard;

use osCommerce\OM\Core\Registry;

class CanUserAddNewSite
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        if (!isset($params['user_id']) || !is_numeric($params['user_id']) || ($params['user_id'] < 1)) {
            return false;
        }

        $Qcheck = $OSCOM_PDO->prepare('select id from :table_website_live_shops where user_id = :user_id and date_added >= date_sub(now(), interval 1 day) limit 1');
        $Qcheck->bindInt(':user_id', $params['user_id']);
        $Qcheck->setCache('sites-user-' . $params['user_id'] . '-prereqcheck', 15);
        $Qcheck->execute();

        return $Qcheck->fetch() === false;
    }
}
