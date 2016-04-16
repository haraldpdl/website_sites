<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\RPC;

use osCommerce\OM\Core\OSCOM;

use osCommerce\OM\Core\Site\Sites\Sites;

class Moderate
{
    public static function execute()
    {
        $result = [];

        if (isset($_SESSION['Website']['Account']) && (($_SESSION['Website']['Account']['admin'] === true) || ($_SESSION['Website']['Account']['team'] === true))) {
            $publicToken = isset($_POST['publicToken']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicToken'])) : '';
            $publicId = isset($_POST['publicId']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicId'])) : '';
            $action = isset($_POST['action']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['action'])) : '';

            if ($publicToken !== md5($_SESSION['Website']['public_token'])) {
                $result['error'] = 300;
            } elseif (!in_array($action, ['disable', 'requeue'])) {
                $result['error'] = 400;
            } elseif (empty($publicId) || !Sites::exists($publicId)) {
                $result['error'] = 100;
            }

            if (empty($result)) {
                $status = false;

                switch ($action) {
                    case 'disable':
                        if (Sites::disable($publicId)) {
                            $status = true;
                        }

                        break;

                    case 'requeue':
                        if (Sites::requeue($publicId)) {
                            $status = true;
                        }

                        break;
                }

                if ($status === true) {
                    $result['status'] = 1;
                } else {
                    $result['error'] = 500;
                }
            }
        } else {
            $result['error'] = 200;
        }

        echo json_encode($result);
    }
}
