<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\RPC;

use osCommerce\OM\Core\{
    HTML,
    OSCOM
};

use osCommerce\OM\Core\Site\RPC\Controller as RPC;

use osCommerce\OM\Core\Site\Sites\Sites;

class GetListing
{
    public static function execute()
    {
        $path = [];
        $ignore = [
            'RPC',
            OSCOM::getSite(),
            OSCOM::getSiteApplication(),
            'GetListing'
        ];

        $req_codes = [];

        foreach (array_keys($_GET) as $g) {
            $g = HTML::sanitize(basename($g));

            if (empty($path) && in_array($g, $ignore)) {
                continue;
            }

            $req_codes[] = $g;

            if (Sites::categoryExists($req_codes, true)) {
                $path[] = Sites::getCategoryId($req_codes);
            } else {
                break;
            }
        }

        $category_id = empty($path) ? 0 : end($path);

        $pageset = null;

        if (isset($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] > 0)) {
            $pageset = $_GET['page'];
        }

        $country = null;

        if (isset($_GET['country']) && Sites::countryExists($_GET['country'], true)) {
            $country = $_GET['country'];
        }

        $result = Sites::getListing($category_id, $country, $pageset);

        echo json_encode($result);
    }
}
