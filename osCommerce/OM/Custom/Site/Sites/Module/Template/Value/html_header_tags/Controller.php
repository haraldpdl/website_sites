<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Value\html_header_tags;

use osCommerce\OM\Core\Registry;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): string
    {
        $OSCOM_Template = Registry::get('Template');

        $result = '';

        if ($OSCOM_Template->hasHtmlElements('header')) {
            $result = $OSCOM_Template->getHtmlElements('header');
        }

        return $result;
    }
}
