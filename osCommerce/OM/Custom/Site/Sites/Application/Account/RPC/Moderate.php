<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account\RPC;

use osCommerce\OM\Core\OSCOM;

use osCommerce\OM\Core\Site\Sites\Sites;

class Moderate
{
    public static function execute()
    {
        $result = [];

        if (isset($_SESSION['Website']['Account'])) {
            $publicToken = isset($_POST['publicToken']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicToken'])) : '';
            $publicId = isset($_POST['publicId']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicId'])) : '';
            $action = isset($_POST['action']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['action'])) : '';

            if ($publicToken !== md5($_SESSION['Website']['public_token'])) {
                $result['error'] = 300;
            } elseif (!in_array($action, ['remove'])) {
                $result['error'] = 400;
            } elseif (empty($publicId) || !Sites::exists($publicId) || (Sites::get($publicId, 'user_id') != $_SESSION['Website']['Account']['id'])) {
                $result['error'] = 100;
            }

            if (empty($result)) {
                $status = false;

                switch ($action) {
                    case 'remove':
                        if (Sites::get($publicId, 'status') != Sites::STATUS_DISABLED) {
                            if (Sites::disable($publicId)) {
                                $status = true;
                            }
                        } else {
                            $result['error'] = 100;
                        }

                        break;
                }

                if ($status === true) {
                    $result['status'] = 1;
                } elseif (empty($result)) {
                    $result['error'] = 500;
                }
            }
        } else {
            $result['error'] = 200;
        }

        echo json_encode($result);
    }
}
