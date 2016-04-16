<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetCategoryTree
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $query = 'select c.categories_id, c.parent_id, c.categories_name,
  (select count(*) from :table_website_live_shops where category_id = c.categories_id and status = 3';

        if (isset($params['country_id'])) {
            $query .= ' and country_id = :country_id';
        }

        $query .= ') as total
from :table_website_live_shops_categories c order by c.parent_id, c.sort_order, c.categories_name';

        $Qcats = $OSCOM_PDO->prepare($query);

        if (isset($params['country_id'])) {
            $Qcats->bindInt(':country_id', $params['country_id']);
        }

        $cache_name = 'sites-categories';

        if (isset($params['country_id'])) {
            $cache_name .= '-country' . $params['country_id'];
        }

        $Qcats->setCache($cache_name);
        $Qcats->execute();

        return $Qcats->fetchAll();
    }
}
