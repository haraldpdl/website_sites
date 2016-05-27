<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites;

use osCommerce\OM\Core\{
    AuditLog,
    Cache,
    Hash,
    OSCOM,
    Registry
};

class Sites
{
    const STATUS_DISABLED = 0;
    const STATUS_NEW = 1;
    const STATUS_QUEUE_IMAGE = 2;
    const STATUS_LIVE = 3;

    const IMAGE_PREVIEWS_PUBLIC_PATH = 'images/previews/';

    public static function getListing(int $parent_category = null, string $country = null, int $pageset = null)
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        if (!isset($pageset)) {
            $pageset = 1;
        }

        $params = [
            'country' => $country,
            'pageset' => $pageset
        ];

        if ($parent_category > 0) {
            $categories = [
                $parent_category
            ];

            foreach ($OSCOM_CategoryTree->getChildren($parent_category, true) as $category) {
                $categories[] = $category['id'];
            }

            $params['categories'] = $categories;
        }

        $result = OSCOM::callDB('Sites\GetListing', $params, 'Site');

        foreach ($result as $k => $v) {
            $result[$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result[$k]['id']);
        }

        return $result;
    }

    public static function getShowcasePartners(string $category = null): array
    {
        $params = [];

        if (isset($category)) {
            $params['category'] = $category;
        }

        $result = OSCOM::callDB('Sites\GetShowcasePartners', $params, 'Site');

        foreach ($result as $k => $v) {
            $result[$k]['site_round_id'] = round((int)$v['site_id'], -3) / 1000;

            unset($result[$k]['site_id']);
        }

        return $result;
    }

    public static function getShowcaseListing(string $partner): array
    {
        $params = [
            'partner' => $partner
        ];

        $result = OSCOM::callDB('Sites\GetShowcaseListing', $params, 'Site');

        foreach ($result as $k => $v) {
            $result[$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result[$k]['id']);
        }

        return $result;
    }

    public static function getUserListing(int $id = null, bool $only_public = true)
    {
        if (!isset($id)) {
            $id = $_SESSION['Website']['Account']['id'];
        }

        $data = [
            'user_id' => $id
        ];

        if ($only_public === true) {
            $data['with_status'] = [
                static::STATUS_NEW,
                static::STATUS_QUEUE_IMAGE,
                static::STATUS_LIVE
            ];
        }

        $result = OSCOM::callDB('Sites\GetUserListing', $data, 'Site');

        foreach ($result as $k => $v) {
            $result[$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result[$k]['id']);
        }

        return $result;
    }

    public static function getCategories(int $category_id = null, string $country = null): array
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        if (!isset($category_id)) {
            $category_id = 0;
        }

        $result = [];

        foreach ($OSCOM_CategoryTree->getChildren($category_id) as $c) {
            if ($c['total'] > 0) {
                $c['full_path'] = implode('&', $OSCOM_CategoryTree->getFullPath($c['id'], 'code'));

                $result[] = $c;
            }
        }

        return $result;
    }

    public static function getCountries(int $category_id = null)
    {
        if (!isset($category_id)) {
            $category_id = 0;
        }

        $params = [
            'category_id' => $category_id
        ];

        return OSCOM::callDB('Sites\GetCountries', $params, 'Site');
    }

    public static function countryExists(string $code, bool $strict = false): bool
    {
        $category_id = ($strict === true) ? null : -1;

        foreach (static::getCountries($category_id) as $c) {
            if ($c['code'] == $code) {
                return true;
            }
        }

        return false;
    }

    public static function getCountry(string $code, string $key = null)
    {
        foreach (static::getCountries(-1) as $c) {
            if ($c['code'] == $code) {
                if (isset($key)) {
                    $c = $c[$key];
                }

                return $c;
            }
        }

        return false;
    }

    public static function getCountryCode(int $id): string
    {
        foreach (static::getCountries(-1) as $c) {
            if ($c['id'] == $id) {
                return $c['code'];
            }
        }

        return '';
    }

    public static function categoryExists(array $categories, bool $strict = false): bool
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        $parent_id = 0;
        $count = 0;

        $data = $OSCOM_CategoryTree->getArray();

        foreach ($categories as $c) {
            if (isset($data[$parent_id])) {
                foreach ($data[$parent_id] as $cid => $cinfo) {
                    if (($cinfo['code'] == $c) && (($strict === false) || ($cinfo['total'] > 0))) {
                        $parent_id = $cid;

                        $count += 1;

                        break;
                    }
                }
            }
        }

        return ($count > 0) && ($count === count($categories));
    }

    public static function getCategoryId(array $categories): int
    {
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        $parent_id = 0;

        $data = $OSCOM_CategoryTree->getArray();

        foreach ($categories as $c) {
            foreach ($data[$parent_id] as $cid => $cinfo) {
                if ($cinfo['code'] == $c) {
                    $parent_id = $cid;

                    break;
                }
            }
        }

        return $parent_id;
    }

    public static function getCategoryTree(string $country = null): array
    {
        $result = [];

        $params = [
            'country_id' => !empty($country) ? static::getCountry($country, 'id') : null
        ];

        $data = OSCOM::callDB('Sites\GetCategoryTree', $params, 'Site');

        foreach ($data as $row) {
            $result[(int)$row['parent_id']][(int)$row['categories_id']] = [
                'title' => $row['categories_name'],
                'code' => str_replace(' ', '-', $row['categories_name']),
                'total' => (int)$row['total']
            ];

// calculate totals all the way up to root level
            if (((int)$row['parent_id'] > 0) && ((int)$row['total'] > 0)) {
                $id = (int)$row['parent_id'];

                while ($id > 0) {
                    foreach ($result as $parent_id => $category) {
                        foreach (array_keys($category) as $category_id) {
                            if ($id === $category_id) {
                                $result[$parent_id][$category_id]['total'] += (int)$row['total'];

                                $id = $parent_id;

                                break 2;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function save(array $data): bool
    {
        return OSCOM::callDB('Sites\Save', $data, 'Site');
    }

    public static function generatePublicId(): string
    {
        while (true) {
            $id = Hash::getRandomString(8, 'chars');

            if (OSCOM::callDB('Sites\CheckPublicId', [ 'public_id' => $id ], 'Site') === false) {
                break;
            }
        }

        return $id;
    }

    public static function exists(string $public_id, bool $strict = false): bool
    {
        if (preg_match('/^[a-zA-Z]{8}$/', $public_id) !== 1) {
            return false;
        }

        $data = [
            'public_id' => $public_id,
            'strict' => $strict
        ];

        return OSCOM::callDB('Sites\CheckPublicId', $data, 'Site');
    }

    public static function get(string $public_id, string $key = null)
    {
        $result = OSCOM::callDB('Sites\GetSite', [ 'public_id' => $public_id ], 'Site');

        $result['round_id'] = round((int)$result['id'], -3) / 1000;

        if (isset($key)) {
            $result = $result[$key];
        }

        return $result;
    }

    public static function logClick(string $public_id)
    {
        $data = [
            'id' => static::get($public_id, 'id'),
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (isset($_SESSION['Website']['Account'])) {
            $data['user_id'] = $_SESSION['Website']['Account']['id'];
        }

        return OSCOM::callDB('Sites\LogClick', $data, 'Site');
    }

    public static function disable(string $public_id): bool
    {
        $site = static::get($public_id);

        if (static::setStatus($public_id, static::STATUS_DISABLED)) {
            $file = OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/Sites/' . static::IMAGE_PREVIEWS_PUBLIC_PATH . $site['round_id'] . '/' . $public_id . '.png';

            if (file_exists($file) && is_writable($file)) {
                unlink($file);
            }

            return true;
        }

        return false;
    }

    public static function requeue(string $public_id): bool
    {
        return static::setStatus($public_id, static::STATUS_QUEUE_IMAGE);
    }

    protected static function setStatus(string $public_id, int $status): bool
    {
        if (!in_array($status, static::getStatuses())) {
            return false;
        }

        $site = static::get($public_id);

        $data = [
            'id' => $site['id'],
            'status' => $status
        ];

        if (OSCOM::callDB('Sites\SetStatus', $data, 'Site')) {
            Cache::clear('sites-' . $public_id);
            Cache::clear('sites-user-' . $site['user_id']);
            Cache::clear('sites-listing');
            Cache::clear('sites-countries');
            Cache::clear('sites-categories');
            Cache::clear('sites-showcase');

            $diff = array_diff_assoc($data, $site);

            if (!empty($diff)) {
                $log = [
                    'action' => 'Sites',
                    'id' => $site['id'],
                    'user_id' => $_SESSION['Website']['Account']['id'],
                    'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress())),
                    'action_type' => 'update',
                    'rows' => []
                ];

                foreach ($diff as $key => $new_value) {
                    $log['rows'][] = [
                        'key' => $key,
                        'old' => isset($site[$key]) ? $site[$key] : null,
                        'new' => $new_value
                    ];
                }

                AuditLog::save($log);
            }

            return true;
        }

        return false;
    }

    public static function getStatuses(): array
    {
        $result = [];

        $ref = new \ReflectionClass(__CLASS__);

        foreach ($ref->getConstants() as $k => $v) {
            if (substr($k, 0, 7) == 'STATUS_') {
                $result[] = $v;
            }
        }

        return $result;
    }
}
