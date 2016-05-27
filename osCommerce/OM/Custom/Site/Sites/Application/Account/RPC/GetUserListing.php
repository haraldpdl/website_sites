<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account\RPC;

use osCommerce\OM\Core\Site\Sites\Sites;

class GetUserListing
{
    public static function execute()
    {
        $result = Sites::getUserListing();

        echo json_encode($result);
    }
}
