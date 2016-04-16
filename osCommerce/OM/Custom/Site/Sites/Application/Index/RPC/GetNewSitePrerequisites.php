<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\RPC;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Sites\Sites;

class GetNewSitePrerequisites
{
    public static function execute()
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        $result = [];

        if (isset($_SESSION['Website']['Account'])) {
            $publicToken = isset($_POST['publicToken']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicToken'])) : '';

            if ($publicToken !== md5($_SESSION['Website']['public_token'])) {
                $result['error'] = 300;
            }

            if (empty($result)) {
                $params = [
                    'user_id' => $_SESSION['Website']['Account']['id']
                ];

                if (OSCOM::callDB('Sites\CanUserAddNewSite', $params, 'Site') !== true) {
                    $result['error'] = 100;
                } else {
                    foreach ($OSCOM_CategoryTree->getChildren(0) as $p) {
                        $parent = [
                            'code' => $p['code'],
                            'title' => $p['title'],
                            'children' => []
                        ];

                        foreach ($OSCOM_CategoryTree->getChildren($p['id']) as $c) {
                            $parent['children'][] = [
                                'code' => $c['code'],
                                'title' => $c['title']
                            ];
                        }

                        $result['categories'][] = $parent;
                    }

                    foreach (Sites::getCountries(-1) as $s) {
                        $result['countries'][] = [
                            'code' => $s['code'],
                            'title' => $s['title']
                        ];
                    }
                }
            }
        } else {
            $result['error'] = 200;
        }

        echo json_encode($result);
    }
}
