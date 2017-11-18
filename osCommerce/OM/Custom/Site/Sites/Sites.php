<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Sites;

use osCommerce\OM\Core\{
    AuditLog,
    Hash,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Website\{
    Partner,
    Users
};

use osCommerce\OM\Core\Site\Apps\Cache;

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

        $cache_name = 'sites-listing-NS';

        if (isset($params['categories']) && is_array($params['categories']) && !empty($params['categories'])) {
            $cache_name .= '-cat' . $params['categories'][0];
        }

        if (!empty($params['country'])) {
            $cache_name .= '-country' . $params['country'];
        }

        $cache_name .= '-page' . $params['pageset'];

        $OSCOM_Cache = new Cache($cache_name);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetListing', $params, 'Site');

            $OSCOM_Cache->set($result);
        }

        if (!is_array($result)) {
            $result = [];
        }

        foreach ($result as $k => $v) {
            $result[$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result[$k]['id']);
        }

        return $result;
    }

    public static function getShowcasePartners(string $category = null): array
    {
        $OSCOM_Language = Registry::get('Language');

        $params = [
            'language_id' => $OSCOM_Language->getDefaultId()
        ];

        if (isset($category)) {
            $params['category'] = $category;
        }

        $cache_name = 'sites-showcase-NS';

        if (isset($params['category'])) {
            $cache_name .= '-cat' . $params['category'];
        }

        $cache_name .= '-lang' . $params['language_id'];

        $OSCOM_Cache = new Cache($cache_name);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetShowcasePartners', $params, 'Site');

            $OSCOM_Cache->set($result, 720);
        }

        if (!is_array($result)) {
            $result = [];
        }

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

        $OSCOM_Cache = new Cache('sites-showcase-NS-partner' . $params['partner']);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetShowcaseListing', $params, 'Site');

            $OSCOM_Cache->set($result, 720);
        }

        if (!is_array($result)) {
            $result = [];
        }

        foreach ($result as $k => $v) {
            $result[$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result[$k]['id']);
        }

        return $result;
    }

    public static function hasAmbassadorShowcase(): bool
    {
        $params = [
            'status' => static::STATUS_LIVE
        ];

        $OSCOM_Cache = new Cache('sites-ambassador-showcase-check');

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\CheckAmbassadorShowcase', $params, 'Site');

            $OSCOM_Cache->set($result, 360);
        }

        return $result === 1;
    }

    public static function getAmbassadorShowcase(): array
    {
        $params = [
            'status' => static::STATUS_LIVE
        ];

        $OSCOM_Cache = new Cache('sites-ambassador-showcase');

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetAmbassadorShowcase', $params, 'Site');

            $OSCOM_Cache->set($result, 360);
        }

        if (!is_array($result)) {
            $result = [];
        } else {
            $result[0]['title'] = OSCOM::getDef('ambassador_title');
            $result[0]['code'] = 'ambassadors';

            $result[0]['site_round_id'] = round((int)$result[0]['site_id'], -3) / 1000;

            unset($result[0]['site_id']);
        }

        return $result;
    }

    public static function getAmbassadorShowcaseListing(bool $get_expiry_time = false): array
    {
        $params = [
            'status' => static::STATUS_LIVE
        ];

        $OSCOM_Cache = new Cache('sites-ambassador-showcase-NS-listing');

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = [
                'entries' => OSCOM::callDB('Sites\GetAmbassadorShowcaseListing', $params, 'Site'),
                'ttl' => (new \DateTime())->modify('+6 hours')->format('c')
            ];

            $OSCOM_Cache->set($result, 360);
        }

        if ($get_expiry_time === true) {
            return [
                'ttl' => $result['ttl']
            ];
        }

        if (!is_array($result['entries'])) {
            $result['entries'] = [];
        }

        foreach ($result['entries'] as $k => $v) {
            $result['entries'][$k]['round_id'] = round((int)$v['id'], -3) / 1000;

            unset($result['entries'][$k]['id']);
        }

        return $result['entries'];
    }

    public static function getAmbassadorShowcaseListingCacheTtl()
    {
        return static::getAmbassadorShowcaseListing(true)['ttl'];
    }

    public static function getUserListing(int $id = null, bool $only_public = true)
    {
        if (!isset($id)) {
            $id = $_SESSION['Website']['Account']['id'];
        }

        $params = [
            'user_id' => $id
        ];

        if ($only_public === true) {
            $params['with_status'] = [
                static::STATUS_NEW,
                static::STATUS_QUEUE_IMAGE,
                static::STATUS_LIVE
            ];
        }

        $cache_name = 'sites-user-NS-u' . $params['user_id'];

        if (isset($params['with_status'])) {
            $status_values = $params['with_status'];
            natsort($status_values);

            $cache_name .= '-s' . implode('_s', $status_values);
        }

        $OSCOM_Cache = new Cache($cache_name);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetUserListing', $params, 'Site');

            $OSCOM_Cache->set($result, 1440);
        }

        if (!is_array($result)) {
            $result = [];
        }

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

        if ($params['category_id'] === -1) {
            $cache_name = 'sites-countries-all';
        } else {
            $cache_name = 'sites-countries-active';
        }

        $OSCOM_Cache = new Cache($cache_name);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetCountries', $params, 'Site');

            $OSCOM_Cache->set($result);
        }

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
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

        $cache_name = 'sites-categories-NS';

        if (isset($params['country_id'])) {
            $cache_name .= '-country' . $params['country_id'];
        } else {
            $cache_name .= '-all';
        }

        $OSCOM_Cache = new Cache($cache_name);

        if (($data = $OSCOM_Cache->get()) === false) {
            $data = OSCOM::callDB('Sites\GetCategoryTree', $params, 'Site');

            $OSCOM_Cache->set($data);
        }

        if (!is_array($data)) {
            $data = [];
        }

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

    public static function canUserAddNewSite(int $user_id = null): bool
    {
        if (!isset($user_id)) {
            $user_id = $_SESSION['Website']['Account']['id'];
        }

        $user = ($user_id === $_SESSION['Website']['Account']['id']) ? $_SESSION['Website']['Account'] : Users::get($user_id);

        if (($user['admin'] === true) || ($user['team'] === true)) {
            return true;
        }

        if (Partner::hasCampaign($user['id'])) {
            return true;
        }

        $params = [
            'user_id' => $user['id']
        ];

        $OSCOM_Cache = new Cache('sites-user-NS-u' . $params['user_id'] . '-prereqcheck');

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\CanUserAddNewSite', $params, 'Site');

            $OSCOM_Cache->set($result, 15);
        }

        return $result;
    }

    public static function save(array $data): bool
    {
        if (OSCOM::callDB('Sites\Save', $data, 'Site')) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-user-NS');

            return true;
        }

        return false;
    }

    public static function saveShowcase(string $public_id, string $partner, int $user_id = null): bool
    {
        if (!isset($user_id)) {
            $user_id = $_SESSION['Website']['Account']['id'];
        }

        $data = [
            'site_id' => static::get($public_id, 'id'),
            'partner_id' => Partner::get($partner, 'id'),
            'user_id' => $user_id,
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress())),
        ];

        if (OSCOM::callDB('Sites\SaveShowcase', $data, 'Site')) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-showcase-NS');

            return true;
        }

        return false;
    }

    public static function deleteShowcase(string $public_id, string $partner): bool
    {
        $data = [
            'site_id' => static::get($public_id, 'id'),
            'partner_id' => Partner::get($partner, 'id')
        ];

        if (OSCOM::callDB('Sites\DeleteShowcase', $data, 'Site')) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-showcase-NS');

            return true;
        }

        return false;
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
        $OSCOM_Cache = new Cache('sites-NS-s' . $public_id);

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetSite', [ 'public_id' => $public_id ], 'Site');

            $OSCOM_Cache->set($result, 1440);
        }

        if (!is_array($result)) {
            $result = [];
        }

        $result['round_id'] = round((int)$result['id'], -3) / 1000;

        if (isset($key)) {
            $result = $result[$key];
        }

        return $result;
    }

    public static function getUserAmbassadorShowcaseTotal(int $user_id = null): int
    {
        if (!isset($user_id)) {
            $user_id = $_SESSION['Website']['Account']['id'];
        }

        $params = [
            'user_id' => $user_id,
            'status' => static::STATUS_LIVE
        ];

        $OSCOM_Cache = new Cache('sites-user-NS-u' . $params['user_id'] . '-ambshowcasetotal');

        if (($result = $OSCOM_Cache->get()) === false) {
            $result = OSCOM::callDB('Sites\GetUserAmbassadorShowcaseTotal', $params, 'Site');

            $OSCOM_Cache->set($result);
        }

        return $result;
    }

    public static function addAmbassadorShowcase(string $public_id, int $user_id = null): bool
    {
        if (!isset($user_id)) {
            $user_id = $_SESSION['Website']['Account']['id'];
        }

        $params = [
            'site_id' => static::get($public_id, 'id'),
            'user_id' => $user_id,
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (OSCOM::callDB('Sites\SaveAmbassadorShowcase', $params, 'Site')) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-user-NS');

            return true;
        }

        return false;
    }

    public static function deleteAmbassadorShowcase(string $public_id, int $user_id = null): bool
    {
        if (!isset($user_id)) {
            $user_id = $_SESSION['Website']['Account']['id'];
        }

        $params = [
            'site_id' => static::get($public_id, 'id'),
            'user_id' => $user_id,
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (OSCOM::callDB('Sites\DeleteAmbassadorShowcase', $params, 'Site')) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-user-NS');

            return true;
        }

        return false;
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
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('sites-NS');
            $OSCOM_Cache->delete('sites-user-NS');
            $OSCOM_Cache->delete('sites-listing-NS');
            $OSCOM_Cache->delete('sites-countries-all');
            $OSCOM_Cache->delete('sites-countries-active');
            $OSCOM_Cache->delete('sites-categories-NS');
            $OSCOM_Cache->delete('sites-showcase-NS');

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
