<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index\RPC;

use osCommerce\OM\Core\{
    HTML,
    OSCOM
};

use osCommerce\OM\Core\Site\Sites\Sites;

class Add
{
    public static function execute()
    {
        $result = [];

        if (isset($_SESSION['Website']['Account'])) {
            $publicToken = isset($_POST['publicToken']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['publicToken'])) : '';
            $name = isset($_POST['name']) ? HTML::sanitize(trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['name']))) : '';
            $url = isset($_POST['url']) ? str_replace(array("\r\n", "\n", "\r"), '', $_POST['url']) : '';
            $category = isset($_POST['category']) ? str_replace(array("\r\n", "\n", "\r"), '', $_POST['category']) : '';
            $country = isset($_POST['country']) ? str_replace(array("\r\n", "\n", "\r"), '', $_POST['country']) : '';
            $disclaimerCheck = isset($_POST['disclaimerCheck']) ? str_replace(array("\r\n", "\n", "\r"), '', $_POST['disclaimerCheck']) : '';

            if ($publicToken !== md5($_SESSION['Website']['public_token'])) {
                $result['error'] = 300;
            } elseif (Sites::canUserAddNewSite() !== true) {
                $result['error'] = 100;
            } elseif (count(Sites::getUserListing()) >= 24) {
                $result['error'] = 700;
            } else {
                if (empty($name)) {
                    $result['fields'][] = 'name';
                }

                if (empty($url) || (preg_match('/^(http|https)\:\/\/.+/', $url) !== 1)) {
                    $result['fields'][] = 'url';
                }

                if (empty($category) || (strpos($category, '/') === false) || (Sites::categoryExists(explode('/', $category)) === false)) {
                    $result['fields'][] = 'category';
                }

                if (empty($country) || (Sites::countryExists($country) === false)) {
                    $result['fields'][] = 'country';
                }

                if (empty($disclaimerCheck) || ($disclaimerCheck != '1')) {
                    $result['fields'][] = 'disclaimerCheck';
                }

                if (isset($result['fields'])) {
                    $result['error'] = 400;
                }
            }

            if (empty($result)) {
                $url_filtered = $url;

// international domains (eg, containing german umlauts) are converted to punycode
                if (mb_detect_encoding($url_filtered, 'ASCII', true) !== 'ASCII') {
                    $url_filtered = idn_to_ascii($url_filtered);
                }

                if (filter_var($url_filtered, FILTER_VALIDATE_URL) === false) {
                    $result['error'] = 500;
                }
            }

            if (empty($result)) {
                $data = [
                    'public_id' => Sites::generatePublicId(),
                    'user_id' => $_SESSION['Website']['Account']['id'],
                    'title' => $name,
                    'url' => $url,
                    'category_id' => Sites::getCategoryId(explode('/', $category)),
                    'country_id' => Sites::getCountry($country, 'id'),
                    'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
                ];

                if (Sites::save($data)) {
                    $result['status'] = 1;
                } else {
                    $result['error'] = 600;
                }
            }
        } else {
            $result['error'] = 200;
        }

        echo json_encode($result);
    }
}
