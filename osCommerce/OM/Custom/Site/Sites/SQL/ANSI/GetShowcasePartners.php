<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetShowcasePartners
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql = '
            select
                s.id as site_id, s.public_id as site_public_id,
                p.title, p.code,
                pc.title as category_title, pc.code as category_code,
                (
                    select
                        count(*)
                    from
                        :table_website_live_shops_showcase ssq_sc
                        inner join :table_website_live_shops ssq_s on (ssq_sc.live_shop_id = ssq_s.id)
                    where
                        ssq_sc.partner_id = p.id and
                        ssq_s.status = 3
                ) as total_sites
            from
                :table_website_live_shops_showcase sc
                inner join :table_website_live_shops s on (sc.live_shop_id = s.id)
                inner join :table_website_partner p on (sc.partner_id = p.id)
                inner join :table_website_partner_category pc on (p.category_id = pc.id)
                inner join :table_website_partner_transaction pt on (p.id = pt.partner_id)
                inner join :table_website_partner_package pp on (pt.package_id = pp.id)
            where ';

        if (isset($params['category'])) {
            $sql .= 'pc.code = :partner_category_code and ';
        }

        $sql .= '
                pt.date_start <= now() and
                pt.date_end >= now() and
                pp.status = 1 and
                s.id = (
                    select
                        sq_s.id
                    from
                        :table_website_live_shops_showcase sq_sc
                        inner join :table_website_live_shops sq_s on (sq_sc.live_shop_id = sq_s.id)
                    where
                        sq_sc.partner_id = p.id and
                        sq_s.status = 3
                    order by
                        sq_s.date_added desc,
                        sq_s.title
                    limit 1
                )
            group by
                p.id
            order by
                sum(pt.cost) desc,
                p.title';

        $cache_key = 'sites-listing-showcase';

        if (isset($params['category'])) {
            $cache_key .= '-' . $params['category'];
        }

        $Qpartners = $OSCOM_PDO->prepare($sql);

        if (isset($params['category'])) {
            $Qpartners->bindValue(':partner_category_code', $params['category']);
        }

        $Qpartners->setCache($cache_key, 720);
        $Qpartners->execute();

        return $Qpartners->fetchAll();
    }
}
