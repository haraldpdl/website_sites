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
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Website\Partner;

class Showcase
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_Template = Registry::get('Template');

        $breadcrumb = [];

        if ($OSCOM_Template->valueExists('breadcrumb_path')) {
            $breadcrumb = $OSCOM_Template->getValue('breadcrumb_path');
        }

        $breadcrumb[] = [
            'title' => OSCOM::getDef('breadcrumb_showcase'),
            'link' => OSCOM::getLink(null, null, 'Showcase')
        ];

        $req = array_slice(array_keys($_GET), array_search('Showcase', array_keys($_GET)));

        if (isset($req[1]) && !empty($req[1])) {
            $req_category = HTML::sanitize(strtolower(basename($req[1])));

            if (Partner::categoryExists($req_category)) {
                $category = Partner::getCategory($req_category);

                $breadcrumb[] = [
                    'title' => $category['title'],
                    'link' => OSCOM::getLink(null, null, 'Showcase&' . $category['code'])
                ];

                $OSCOM_Template->addJavascriptBlock('OSCOM.a.Index.currentShowcaseCategory = "' . $category['code'] . '";');

                if (isset($req[2]) && !empty($req[2])) {
                    $req_partner = HTML::sanitize(strtolower(basename($req[2])));

                    if (Partner::exists($req_partner, $category['code'])) {
                        $partner = Partner::get($req_partner);

                        $breadcrumb[] = [
                            'title' => $partner['title'],
                            'link' => OSCOM::getLink(null, null, 'Showcase&' . $category['code'] . '&' . $partner['code'])
                        ];

                        $OSCOM_Template->addJavascriptBlock('OSCOM.a.Index.currentShowcasePartner = "' . $partner['code'] . '";');
                    }
                }
            }
        }

        $OSCOM_Template->setValue('breadcrumb_path', $breadcrumb, true);

        $application->setPageContent('showcase.html');
        $application->setPageTitle(OSCOM::getDef('showcase_html_title'));

        if ((OSCOM::getConfig('use_minified_resources') === 'true') && file_exists(OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/Sites/Application/Index/showcase.min.js')) {
            $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/showcase.min.js');
        } else {
            $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/showcase.js');
        }
    }
}
