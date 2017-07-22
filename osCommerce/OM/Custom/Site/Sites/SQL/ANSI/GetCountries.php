<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetCountries
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        if ($params['category_id'] === -1) {
            $Qcountries = $OSCOM_PDO->query('select countries_id as id, countries_name as title, countries_iso_code_2 as code from :table_countries order by countries_name');
            $Qcountries->execute();
        } else {
            $Qcountries = $OSCOM_PDO->prepare('select distinct s.country_id as id, c.countries_name as title, c.countries_iso_code_2 as code from :table_website_live_shops s, :table_countries c where s.status = :status and s.country_id = c.countries_id order by c.countries_name');
            $Qcountries->bindInt(':status', 3);
            $Qcountries->execute();
        }

        return $Qcountries->fetchAll();
    }
}
