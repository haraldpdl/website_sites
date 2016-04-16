<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Value\logged_in;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): bool
    {
        return isset($_SESSION['Website']['Account']);
    }
}
