<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\RPC;

use osCommerce\OM\Core\HTML;

use osCommerce\OM\Core\Site\Sites\Sites;

use osCommerce\OM\Core\Site\Website\Partner;

class GetShowcasePartners
{
    public static function execute()
    {
        $category = null;

        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $req_category = HTML::sanitize(strtolower(basename($_GET['category'])));

            if (Partner::categoryExists($req_category)) {
                $category = Partner::getCategory($req_category, 'code');
            }
        }

        $result = Sites::getShowcasePartners($category);

        if (Sites::hasAmbassadorShowcase()) {
            $ambassadors = Sites::getAmbassadorShowcase();

            if (!empty($ambassadors)) {
                array_push($result, $ambassadors[0]);
            }
        }

        echo json_encode($result);
    }
}
