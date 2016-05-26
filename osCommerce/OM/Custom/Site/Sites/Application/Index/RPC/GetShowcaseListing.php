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

class GetShowcaseListing
{
    public static function execute()
    {
        $result = null;

        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $req_category = HTML::sanitize(strtolower(basename($_GET['category'])));

            if (Partner::categoryExists($req_category)) {
                $category = Partner::getCategory($req_category, 'code');

                if (isset($_GET['partner']) && !empty($_GET['partner'])) {
                    $req_partner = HTML::sanitize(strtolower(basename($_GET['partner'])));

                    if (Partner::exists($req_partner, $req_category)) {
                        $partner = Partner::get($req_partner);

                        $result = [
                            'partner_code' => $partner['code'],
                            'partner_title' => $partner['title'],
                            'partner_desc' => $partner['desc_short'],
                            'partner_url' => $partner['url'],
                            'sites' => Sites::getShowcaseListing($partner['code'])
                        ];
                    }
                }
            }
        }

        echo json_encode($result);
    }
}
