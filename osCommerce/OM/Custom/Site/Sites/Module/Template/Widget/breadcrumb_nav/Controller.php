<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Module\Template\Widget\breadcrumb_nav;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Sites\Sites;

class Controller extends \osCommerce\OM\Core\Template\WidgetAbstract
{
    public static function execute($param = null): string
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');
        $OSCOM_Template = Registry::get('Template');

        $result = '';
        $breadcrumb = [];

        if ($OSCOM_Template->valueExists('breadcrumb_path')) {
            $breadcrumb = $OSCOM_Template->getValue('breadcrumb_path');
        }

        $country = $OSCOM_Template->getValue('country');

        if (!empty($country)) {
            $breadcrumb[] = [
                'title' => Sites::getCountry($country, 'title'),
                'link' => OSCOM::getLink(null, null, 'country=' . $country)
            ];
        }

        $path = $OSCOM_Template->getValue('category_path');

        $current_category_id = empty($path) ? 0 : end($path);

        if ($current_category_id > 0) {
            foreach ($path as $p) {
                $breadcrumb[] = [
                    'title' => $OSCOM_CategoryTree->getData($p, 'title'),
                    'link' => OSCOM::getLink(null, null, implode('&', $OSCOM_CategoryTree->getFullPath($p, 'code')) . (!empty($country) ? '&country=' . $country : ''))
                ];
            }
        }

        if (!empty($breadcrumb)) {
            $OSCOM_Template->setValue('breadcrumb_path', $breadcrumb, true);

            $breadcrumb_path_json = [
                '@context' => 'http://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ];

            $bc_counter = 0;

            foreach ($breadcrumb as $bc) {
                $bc_counter += 1;

                $breadcrumb_path_json['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $bc_counter,
                    'item' => [
                        '@id' => $bc['link'],
                        'name' => $bc['title']
                    ]
                ];
            }

            $OSCOM_Template->addHtmlElement('footer', '<script type="application/ld+json" id="jsonldBreadcrumb">' . json_encode($breadcrumb_path_json) . '</script>');

            $result = file_get_contents(OSCOM::BASE_DIRECTORY . 'Custom/Site/Sites/Module/Template/Widget/breadcrumb_nav/pages/main.html');

            $js = <<<'EOT'
if ($('#jsonldBreadcrumb').length) {
    var jsonBreadcrumb = $.parseJSON($('#jsonldBreadcrumb').html());

    for (var i = 0, n = jsonBreadcrumb.itemListElement.length; i < n; i += 1) {
        $('#breadcrumbNav').append('<a href="' + jsonBreadcrumb.itemListElement[i].item['@id'] + '" class="mdl-layout__tab">' + jsonBreadcrumb.itemListElement[i].item.name + '</a>');
    }

    $('#breadcrumbNav a').filter(':last').addClass('is-active');
}
EOT;

            $OSCOM_Template->addJavascriptBlock($js);
        }

        return $result;
    }
}
