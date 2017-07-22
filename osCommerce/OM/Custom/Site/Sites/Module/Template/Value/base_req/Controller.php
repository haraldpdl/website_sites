<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Value\base_req;

use osCommerce\OM\Core\OSCOM;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): array
    {
        $url = [];

        if (OSCOM::getSite() != OSCOM::getDefaultSite()) {
            $url[OSCOM::getSite()] = $_GET[OSCOM::getSite()] ?? null;
        }

        return $url;
    }
}
