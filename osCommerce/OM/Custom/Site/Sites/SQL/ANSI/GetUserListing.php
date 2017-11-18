<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetUserListing
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = 'select s.id, s.public_id, s.title, s.url, s.status, if(las.id is null, 0, 1) as ambassador_showcase_flag, c.countries_name as country_name, c.countries_iso_code_2 as country_code, cat.categories_name as category_name, parent_cat.categories_name as parent_category_name, count(l.id) as total_likes from :table_website_live_shops s left join :table_website_live_shops_likes l on (s.id = l.live_shop_id) left join :table_website_live_shops_ambassador_showcase las on (s.id = las.live_shop_id and s.user_id = las.user_id and las.date_removed is null), :table_countries c, :table_website_live_shops_categories cat left join :table_website_live_shops_categories parent_cat on (cat.parent_id = parent_cat.categories_id) where s.user_id = :user_id';

        if (isset($params['with_status'])) {
            $sql .= ' and s.status in (:status_' . implode(', :status_', array_keys($params['with_status'])) . ')';
        }

        $sql .= ' and s.country_id = c.countries_id and s.category_id = cat.categories_id group by s.id order by s.date_added desc, s.title';

        $Qsites = $OSCOM_PDO->prepare($sql);
        $Qsites->bindInt(':user_id', $params['user_id']);

        if (isset($params['with_status'])) {
            foreach ($params['with_status'] as $k => $v) {
                $Qsites->bindInt(':status_' . $k, $v);
            }
        }

        $Qsites->execute();

        return $Qsites->fetchAll();
    }
}
