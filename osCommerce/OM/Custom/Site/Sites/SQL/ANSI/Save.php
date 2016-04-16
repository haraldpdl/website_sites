<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class Save
{
    public static function execute(array $data): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qsite = $OSCOM_PDO->prepare('insert into :table_website_live_shops (public_id, title, url, category_id, country_id, date_added, status, user_id, ip_address) values (:public_id, :title, :url, :category_id, :country_id, now(), :status, :user_id, :ip_address)');
        $Qsite->bindValue(':public_id', $data['public_id']);
        $Qsite->bindValue(':title', $data['title']);
        $Qsite->bindValue(':url', $data['url']);
        $Qsite->bindInt(':category_id', $data['category_id']);
        $Qsite->bindInt(':country_id', $data['country_id']);
        $Qsite->bindInt(':status', 1);
        $Qsite->bindInt(':user_id', $data['user_id']);
        $Qsite->bindInt(':ip_address', $data['ip_address']);
        $Qsite->execute();

        return ($Qsite->rowCount() === 1);
    }
}
