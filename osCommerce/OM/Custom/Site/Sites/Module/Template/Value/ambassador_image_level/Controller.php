<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Value\ambassador_image_level;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): int
    {
        $level = 0;

        if (isset($_SESSION['Website']['Account'])) {
            $level = ($_SESSION['Website']['Account']['amb_level'] > 3) ? 3 : $_SESSION['Website']['Account']['amb_level'];
        }

        return $level;
    }
}
