<?php
/**
 * osCommerce Sites Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions;

use osCommerce\OM\Core\{
    DirectoryListing,
    FileSystem,
    Mail,
    OSCOM,
    Registry,
    RunScript
};

use osCommerce\OM\Core\Site\Sites\Sites;

use osCommerce\OM\Core\Site\Website\Users;

use osCommerce\OM\Core\Site\Apps\Cache;

class ProcessPendingSubmissions implements \osCommerce\OM\Core\RunScriptInterface
{
    protected const INTERNAL_GENERAL_ERROR_NO_CHECK_MODULES = 'INTERNAL_GENERAL_ERROR_NO_CHECK_MODULES';
    protected const INTERNAL_GENERAL_ERROR_RETRY = 'INTERNAL_GENERAL_ERROR_RETRY';

    protected static $check_modules;
    protected static $check_modules_results = [];

    public static function execute()
    {
        OSCOM::initialize('Sites');

        $OSCOM_PDO = Registry::get('PDO');
        $OSCOM_Template = Registry::get('Template');

        foreach (Sites::getPending(Sites::STATUS_NEW, 15) as $p) {
            $result = static::checkModules($p['url']);

            if ($result === static::INTERNAL_GENERAL_ERROR_NO_CHECK_MODULES) {
                RunScript::error('Error: No check modules found.');
                exit;
            }

            if ($result === static::INTERNAL_GENERAL_ERROR_RETRY) {
                RunScript::error('Error: Service disruption, general retry.');
                exit;
            }

            $status_flag = is_null($result) ? Sites::STATUS_QUEUE_IMAGE : Sites::STATUS_DISABLED;

            $save_success = false;

            try {
                $OSCOM_PDO->beginTransaction();

                $Qupdate = $OSCOM_PDO->save('website_live_shops', [
                    'status' => $status_flag
                ], [
                    'id' => $p['id']
                ]);

                foreach (static::$check_modules_results as $r) {
                    $Qlog = $OSCOM_PDO->save('website_live_shops_log', [
                        'module_id' => $r['id'],
                        'live_shop_id' => $p['id'],
                        'result' => $r['value'],
                        'user_id' => 0,
                        'ip_address' => 0,
                        'date_added' => 'now()'
                    ]);
                }

                $OSCOM_PDO->commit();

                $save_success = true;
            } catch (\Exception $e) {
                $OSCOM_PDO->rollBack();
            }

            if ($save_success === true) {
                if (!empty($result) && ($status_flag === Sites::STATUS_DISABLED)) {
                    $user = Users::get($p['user_id']);

                    $OSCOM_Template->setValue('user_name', $user['name'], true);
                    $OSCOM_Template->setValue('live_site_title', $p['title'], true);
                    $OSCOM_Template->setValue('live_site_url', $p['url'], true);
                    $OSCOM_Template->setValue('live_site_error_message', $result, true);

                    $email_txt = $OSCOM_Template->getContent(__DIR__ . '/pages/email_error.txt');
                    $email_html = $OSCOM_Template->getContent(__DIR__ . '/pages/email_error.html');

                    if (!empty($email_txt) || !empty($email_html)) {
                        $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce Live Site Submission');
                        $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                        if (!empty($email_txt)) {
                            $OSCOM_Mail->setBodyPlain($email_txt);
                        }

                        if (!empty($email_html)) {
                            $OSCOM_Mail->setBodyHTML($email_html);
                        }

                        $OSCOM_Mail->send();
                    }
                }
            }
        }

        if (static::runModules() === true) {
            $images_file = OSCOM::BASE_DIRECTORY . 'Work/Temp/sites-screenshot-images.zip';
            $work_dir = OSCOM::BASE_DIRECTORY . 'Work/Temp/sites-screenshot-images/';
            $public_dir = OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/Sites/' . Sites::IMAGE_PREVIEWS_PUBLIC_PATH;

            if (is_file($images_file)) {
                if (is_dir($work_dir)) {
                    FileSystem::rmdir($work_dir);
                }

                mkdir($work_dir);

                $zip = new \ZipArchive();
                $zip->open($images_file);
                $zip->extractTo($work_dir);
                $zip->close();

                if (is_file($work_dir . 'ids.txt')) {
                    $ids = json_decode(file_get_contents($work_dir . 'ids.txt'), true);

                    if (is_array($ids) && !empty($ids)) {
                        foreach ($ids as $id) {
                            if (is_numeric($id)) {
                                $Qs = $OSCOM_PDO->get('website_live_shops', [
                                    'public_id',
                                    'title',
                                    'url',
                                    'status',
                                    'user_id'
                                ], [
                                    'id' => $id
                                ]);

                                if ($Qs->valueInt('status') === Sites::STATUS_QUEUE_IMAGE) {
                                    if (is_file($work_dir . $id . '.png') && (filesize($work_dir . $id . '.png') > 0) && is_array(getimagesize($work_dir . $id . '.png'))) {
                                        $target_dir = round($id, -3) / 1000;

                                        if (!is_dir($public_dir . $target_dir)) {
                                            mkdir($public_dir . $target_dir);
                                        }

                                        if (is_dir($public_dir . $target_dir)) {
                                            if (copy($work_dir . $id . '.png', $public_dir . $target_dir . '/' . basename($Qs->value('public_id')) . '.png')) {
                                                $OSCOM_PDO->save('website_live_shops', [
                                                    'status' => Sites::STATUS_LIVE
                                                ], [
                                                    'id' => $id
                                                ]);

                                                $OSCOM_PDO->save('website_live_shops_log', [
                                                    'module_id' => 4,
                                                    'live_shop_id' => $id,
                                                    'result' => 1,
                                                    'user_id' => 0,
                                                    'ip_address' => 0,
                                                    'date_added' => 'now()'
                                                ]);

                                                $user = Users::get($Qs->valueInt('user_id'));

                                                $OSCOM_Template->setValue('user_name', $user['name'], true);
                                                $OSCOM_Template->setValue('live_site_title', $Qs->value('title'), true);
                                                $OSCOM_Template->setValue('live_site_url', $Qs->value('url'), true);

                                                $email_txt = $OSCOM_Template->getContent(__DIR__ . '/pages/email_new.txt');
                                                $email_html = $OSCOM_Template->getContent(__DIR__ . '/pages/email_new.html');

                                                if (!empty($email_txt) || !empty($email_html)) {
                                                    $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce Live Site Submission');
                                                    $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                                                    if (!empty($email_txt)) {
                                                        $OSCOM_Mail->setBodyPlain($email_txt);
                                                    }

                                                    if (!empty($email_html)) {
                                                        $OSCOM_Mail->setBodyHTML($email_html);
                                                    }

                                                    $OSCOM_Mail->send();
                                                }
                                            }
                                        }
                                    } else {
                                        RunScript::error('Failed to process live site ID: ' . $id);

                                        $OSCOM_PDO->save('website_live_shops', [
                                            'status' => Sites::STATUS_DISABLED
                                        ], [
                                            'id' => $id
                                        ]);

                                        $OSCOM_PDO->save('website_live_shops_log', [
                                            'module_id' => 4,
                                            'live_shop_id' => $id,
                                            'result' => 0,
                                            'user_id' => 0,
                                            'ip_address' => 0,
                                            'date_added' => 'now()'
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                if (is_dir($work_dir)) {
                    FileSystem::rmdir($work_dir);
                }

                unlink($images_file);

                $OSCOM_Cache = new Cache();

                $OSCOM_Cache->delete('sites-NS');
                $OSCOM_Cache->delete('sites-user-NS');
                $OSCOM_Cache->delete('sites-listing-NS');
                $OSCOM_Cache->delete('sites-countries-all');
                $OSCOM_Cache->delete('sites-countries-active');
                $OSCOM_Cache->delete('sites-categories-NS');
                $OSCOM_Cache->delete('sites-showcase-NS');
            }
        }
    }

    protected static function checkModules(string $url): ?string
    {
        if (!isset(static::$check_modules)) {
            static::$check_modules = [];

            $DL = new DirectoryListing(__DIR__ . '/CheckModules');
            $DL->setIncludeDirectories(false);
            $DL->setCheckExtension('php');

            foreach ($DL->getFiles() as $f) {
                $class = 'osCommerce\\OM\\Core\\Site\\Sites\\Scripts\\ProcessPendingSubmissions\\CheckModules\\' . basename($f['name'], '.php');

                if (class_exists($class) && is_subclass_of($class, 'osCommerce\\OM\\Core\\Site\\Sites\\Scripts\\ProcessPendingSubmissions\\CheckModulesAbstract')) {
                    $priority = isset($class::$priority) ? $class::$priority : (!empty(static::$check_modules) ? max(array_keys(static::$check_modules))+1 : 0);

                    do {
                        if (array_key_exists($priority, static::$check_modules)) {
                            $priority++;
                            continue;
                        }

                        static::$check_modules[$priority] = $class;

                        break;
                    } while (true);
                }
            }

            ksort(static::$check_modules);
        }

        static::$check_modules_results = [];

        if (is_array(static::$check_modules) && !empty(static::$check_modules)) {
            foreach (static::$check_modules as $class) {
                $obj = new $class($url);
                $result = $obj->execute();

                if (isset($obj->module_id) && isset($obj->result)) {
                    static::$check_modules_results[] = [
                        'id' => $obj->module_id,
                        'value' => $obj->result
                    ];
                }

                if ($result === false) {
                    return $obj->public_fail_error;
                } elseif (is_null($result)) {
                    return static::INTERNAL_GENERAL_ERROR_RETRY;
                }
            }
        } else {
            return static::INTERNAL_GENERAL_ERROR_NO_CHECK_MODULES;
        }

        return null;
    }

    protected static function runModules(): bool
    {
        $run_modules = [];

        $DL = new DirectoryListing(__DIR__ . '/RunModules');
        $DL->setIncludeDirectories(false);
        $DL->setCheckExtension('php');

        foreach ($DL->getFiles() as $f) {
            $class = 'osCommerce\\OM\\Core\\Site\\Sites\\Scripts\\ProcessPendingSubmissions\\RunModules\\' . basename($f['name'], '.php');

            if (class_exists($class) && is_subclass_of($class, 'osCommerce\\OM\\Core\\Site\\Sites\\Scripts\\ProcessPendingSubmissions\\RunModulesInterface')) {
                $priority = isset($class::$priority) ? $class::$priority : (!empty($run_modules) ? max(array_keys($run_modules))+1 : 0);

                do {
                    if (array_key_exists($priority, $run_modules)) {
                        $priority++;
                        continue;
                    }

                    $run_modules[$priority] = $class;

                    break;
                } while (true);
            }
        }

        ksort($run_modules);

        if (is_array($run_modules) && !empty($run_modules)) {
            foreach ($run_modules as $class) {
                $result = forward_static_call([$class, 'execute']);

                if ($result === false) {
                    return false;
                }
            }

            return true;
        } else {
            RunScript::error('No Run Modules loaded.');
        }

        return false;
    }
}
