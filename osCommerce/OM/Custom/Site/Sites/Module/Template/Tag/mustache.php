<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Tag;

use osCommerce\OM\Core\Registry;

class mustache extends \osCommerce\OM\Core\Template\TagAbstract
{
    protected static $_parse_result = false;

    public static function execute($file): string
    {
        $args = func_get_args();

        $result = '';

        if (!empty($file) && file_exists($file)) {
            if (isset($args[1]) && !empty($args[1])) {
                $id = trim($args[1]);

                $result .= '<script id="' . $id . '" type="x-tmpl-mustache">' . "\n";
            }

            $result .= file_get_contents($file);

            if (isset($args[1]) && !empty($args[1])) {
                $result .= "\n" . '</script>';
            }
        } else {
            trigger_error('Template Tag {mustache}: File does not exist: ' . $file);
        }

        return $result;
    }
}
