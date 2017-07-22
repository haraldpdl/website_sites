<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetListing
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $query = 'select s.id, s.public_id, s.title, s.url, c.countries_name as country_name, c.countries_iso_code_2 as country_code, cat.categories_name as category_name, parent_cat.categories_name as parent_category_name, count(l.id) as total_likes from :table_website_live_shops s left join :table_website_live_shops_likes l on (s.id = l.live_shop_id), :table_countries c, :table_website_live_shops_categories cat left join :table_website_live_shops_categories parent_cat on (cat.parent_id = parent_cat.categories_id) where s.status = 3 and s.country_id = c.countries_id and ';

        if (!empty($params['country'])) {
            $query .= 'c.countries_iso_code_2 = :countries_iso_code_2 and ';
        }

        if (isset($params['categories']) && is_array($params['categories']) && !empty($params['categories'])) {
            $query .= 's.category_id in (' . implode(',', $params['categories']) . ') and ';
        }

        $query .= 's.category_id = cat.categories_id group by s.id order by s.date_added desc, s.title limit :batch_pageset, :batch_max_results';

        $Qsites = $OSCOM_PDO->prepare($query);

        if (!empty($params['country'])) {
            $Qsites->bindValue(':countries_iso_code_2', $params['country']);
        }

        $Qsites->bindInt(':batch_pageset', $OSCOM_PDO->getBatchFrom($params['pageset'], 24));
        $Qsites->bindInt(':batch_max_results', 24);
        $Qsites->execute();

        return $Qsites->fetchAll();
    }
}
