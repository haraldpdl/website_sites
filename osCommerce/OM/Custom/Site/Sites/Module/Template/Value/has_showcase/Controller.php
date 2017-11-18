<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Value\has_showcase;

use osCommerce\OM\Core\Site\Sites\Sites;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): bool
    {
        return !empty(Sites::getShowcasePartners()) || Sites::hasAmbassadorShowcase();
    }
}
