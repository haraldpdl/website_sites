<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetAmbassadorShowcaseListing
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = <<<EOD
select
  s.id,
  s.public_id,
  s.title,
  s.url,
  c.countries_name as country_name,
  c.countries_iso_code_2 as country_code,
  cat.categories_name as category_name,
  parent_cat.categories_name as parent_category_name,
  count(l.id) as total_likes
from
  :table_website_live_shops_ambassador_showcase las
    inner join
      :table_website_live_shops s
        on
          (las.live_shop_id = s.id)
    left join
      :table_website_live_shops_likes l
        on
          (s.id = l.live_shop_id)
    inner join
      :table_countries c
        on
          (s.country_id = c.countries_id)
    inner join
      :table_website_live_shops_categories cat
        on
          (s.category_id = cat.categories_id)
    left join
      :table_website_live_shops_categories parent_cat
        on
          (cat.parent_id = parent_cat.categories_id)
where
  las.date_removed is null and
  las.user_id = s.user_id and
  s.status = :status
group by
  s.id
order by
  rand()
EOD;

        $Qsites = $OSCOM_PDO->prepare($sql);
        $Qsites->bindInt(':status', $params['status']);
        $Qsites->execute();

        return $Qsites->fetchAll();
    }
}
