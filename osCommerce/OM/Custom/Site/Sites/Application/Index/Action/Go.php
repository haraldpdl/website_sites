<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM
};

use osCommerce\OM\Core\Site\Sites\Sites;

class Go
{
    public static function execute(ApplicationAbstract $application)
    {
        if (!empty($_GET['Go']) && Sites::exists($_GET['Go'], true)) {
            Sites::logClick($_GET['Go']);

            OSCOM::redirect(Sites::get($_GET['Go'], 'url'));
        }
    }
}
