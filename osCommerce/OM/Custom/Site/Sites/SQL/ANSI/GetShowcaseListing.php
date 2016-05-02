<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetShowcaseListing
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = 'select s.id, s.public_id, s.title, s.url, c.countries_name as country_name, c.countries_iso_code_2 as country_code, cat.categories_name as category_name, parent_cat.categories_name as parent_category_name, count(l.id) as total_likes, p.title as partner_title, p.code as partner_code, pc.title as partner_category_title, pc.code as partner_category_code from :table_website_live_shops_showcase sc, :table_website_live_shops s left join :table_website_live_shops_likes l on (s.id = l.live_shop_id), :table_countries c, :table_website_live_shops_categories cat left join :table_website_live_shops_categories parent_cat on (cat.parent_id = parent_cat.categories_id), :table_website_partner p, :table_website_partner_category pc, :table_website_partner_transaction pt, :table_website_partner_package pp where sc.partner_id = p.id and p.category_id = pc.id and p.id = pt.partner_id and pt.date_start <= now() and pt.date_end >= now() and pt.package_id = pp.id and pp.status = 1 and sc.live_shop_id = s.id and s.status = 3 and s.country_id = c.countries_id and s.category_id = cat.categories_id';

        if (isset($params['partner'])) {
            $sql .= ' and p.code = :partner_code';
        }

        if (isset($params['category'])) {
            $sql .= ' and pc.code = :partner_category_code';
        }

        $sql .= ' group by s.id, p.id order by sum(pt.cost) desc, s.date_added desc, s.title';

        $Qsites = $OSCOM_PDO->prepare($sql);

        if (isset($params['partner'])) {
            $Qsites->bindValue(':partner_code', $params['partner']);
        }

        if (isset($params['category'])) {
            $Qsites->bindValue(':partner_category_code', $params['category']);
        }

        if (isset($params['partner'])) {
            $Qsites->setCache('sites-listing-showcase-' . $params['partner'], 720);
        } else if (isset($params['category'])) {
            $Qsites->setCache('sites-listing-showcase-' . $params['category'], 720);
        } else {
            $Qsites->setCache('sites-listing-showcase', 720);
        }

        $Qsites->execute();

        return $Qsites->fetchAll();
    }
}
