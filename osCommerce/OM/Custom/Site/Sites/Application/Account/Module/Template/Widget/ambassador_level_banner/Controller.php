<?php
/**
 * osCommerce Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account\Module\Template\Widget\ambassador_level_banner;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Template\WidgetAbstract
{
    public static function execute($param = null)
    {
        $OSCOM_Template = Registry::get('Template');

        $amb_level = $OSCOM_Template->getValue('ambassador_level');

        if ($amb_level > 0) {
            $filename = 'main.html';
        } else {
            $filename = 'new.html';
        }

        $file = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/Module/Template/Widget/ambassador_level_banner/pages/' . $filename;

        if (!file_exists($file)) {
            $file = OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/Module/Template/Widget/ambassador_level_banner/pages/' . $filename;
        }

        return file_get_contents($file);
    }
}
