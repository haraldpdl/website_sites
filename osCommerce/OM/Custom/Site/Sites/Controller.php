<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites;

use osCommerce\OM\Core\{
    Cache,
    DataTree,
//    Events,
    Hash,
    HTML,
    OSCOM,
    PDO,
    Registry
};

use osCommerce\OM\Core\Site\Sites\{
    MessageStack,
    Sites
};

use osCommerce\OM\Core\Site\Website\{
    Invision,
    Language,
    Session,
    Template
};

class Controller implements \osCommerce\OM\Core\SiteInterface
{
    protected static $_default_application = 'Index';

    public static function initialize()
    {
        Registry::set('MessageStack', new MessageStack());
        Registry::set('Cache', new Cache());
        Registry::set('PDO', PDO::initialize());
        Registry::set('Session', Session::load());

        $OSCOM_Session = Registry::get('Session');
        $OSCOM_Session->setLifeTime(3600);
        $OSCOM_Session->start();

        if (!isset($_SESSION['Website']['public_token'])) {
            $_SESSION['Website']['public_token'] = Hash::getRandomString(32);
        }

        if (!OSCOM::isRPC()) {
            if (!isset($_SESSION['Website']['Account'])) {
                $user = Invision::canAutoLogin();

                if (is_array($user) && isset($user['id'])) {
//                    Events::fire('auto_login-before', $user);

                    if (($user['verified'] === true) && ($user['banned'] === false)) {
                        $_SESSION['Website']['Account'] = $user;

                        $OSCOM_Session->recreate();

//                        Events::fire('auto_login-after');
                    } else {
                        Invision::killCookies();
                    }
                }
            }
        }

        Registry::set('Language', new Language());
        Registry::set('Template', new Template());

        $OSCOM_Template = Registry::get('Template');
        $OSCOM_Template->set('Therese');

        $OSCOM_Language = Registry::get('Language');

        $OSCOM_Template->addHtmlTag('dir', $OSCOM_Language->getTextDirection());
        $OSCOM_Template->addHtmlTag('lang', OSCOM::getDef('html_lang_code')); // HPDL A better solution is to define the ISO 639-1 value at the language level

        $OSCOM_Template->addHtmlElement('header', '<link rel="stylesheet" href="public/sites/Sites/templates/' . $OSCOM_Template->getCode() . '/stylesheets/main' . (OSCOM::getConfig('use_minified_resources') === 'true' ? '.min' : '') . '.css">');
        $OSCOM_Template->addHtmlElement('header', '<meta name="generator" content="osCommerce Sites v' . HTML::outputProtected(OSCOM::getVersion(OSCOM::getSite())) . '">');

        $OSCOM_Template->addExternalJavascript('public/sites/Sites/javascript/site' . (OSCOM::getConfig('use_minified_resources') === 'true' ? '.min' : '') . '.js');

        $OSCOM_Template->setValue('country', isset($_GET['country']) && !empty($_GET['country']) && Sites::countryExists($_GET['country'], true) ? $_GET['country'] : '');

        Registry::set('CategoryTree', new DataTree(Sites::getCategoryTree($OSCOM_Template->getValue('country'))));
        $OSCOM_CategoryTree = Registry::get('CategoryTree');

        $req_codes = [];
        $path = [];

        if (count($_GET) > 0) {
            foreach (array_keys($_GET) as $g) {
                $g = HTML::sanitize(basename($g));

                if (empty($path) && (($g === OSCOM::getSite()) || ($g === OSCOM::getSiteApplication()))) {
                    continue;
                }

                $req_codes[] = $g;

                if (Sites::categoryExists($req_codes, true)) {
                    $path[] = Sites::getCategoryId($req_codes);
                } else {
                    break;
                }
            }
        }

        $OSCOM_Template->setValue('category_path', $path);

        $current_category_id = empty($path) ? 0 : end($path);

        $OSCOM_Template->setValue('category_path_code', $OSCOM_CategoryTree->getFullPath($current_category_id, 'code'));

        $application = 'osCommerce\\OM\\Core\\Site\\Sites\\Application\\' . OSCOM::getSiteApplication() . '\\Controller';
        Registry::set('Application', new $application());
        $OSCOM_Template->setApplication(Registry::get('Application'));

        $OSCOM_Template->setValue('public_token', $_SESSION['Website']['public_token']);

        $siteConfigJs = [
            'urlBase' => $OSCOM_Template->getValue('base_url'),
            'urlBaseReq' => $OSCOM_Template->getValue('base_req'),
            'app' => $OSCOM_Template->getValue('current_site_application'),
            'loggedIn' => $OSCOM_Template->getValue('logged_in'),
            'isAdmin' => $OSCOM_Template->getValue('is_admin'),
            'ambassadorLevel' => $OSCOM_Template->getValue('ambassador_level'),
            'categoryPath' => $OSCOM_Template->getValue('category_path_code'),
            'country' => $OSCOM_Template->getValue('country'),
            'secureToken' => md5($OSCOM_Template->getValue('public_token')),
            'siteImagePreviewBase' => 'public/sites/Sites/' . Sites::IMAGE_PREVIEWS_PUBLIC_PATH,
            'def' => $OSCOM_Language->getAllDefinitions('js_'), // language definitions
            'a' => [ // namespace for applications
                $OSCOM_Template->getValue('current_site_application') => []
            ],
            'urlSiteWebsite' => OSCOM::getBaseUrl('Website')
        ];

        $OSCOM_Template->addHtmlElement('header', '<script>var OSCOM = ' . json_encode($siteConfigJs) . ';</script>');

        $OSCOM_Template->setValue('html_tags', $OSCOM_Template->getHtmlTags());
        $OSCOM_Template->setValue('html_character_set', $OSCOM_Language->getCharacterSet());
        $OSCOM_Template->setValue('html_page_title', $OSCOM_Template->getPageTitle());
        $OSCOM_Template->setValue('html_page_contents_file', $OSCOM_Template->getPageContentsFile());
        $OSCOM_Template->setValue('html_base_href', $OSCOM_Template->getBaseUrl());
        $OSCOM_Template->setValue('current_year', date('Y'));

        $countriesList = [];

        foreach (Sites::getCountries() as $c) {
            $countriesList[] = [
                'code' => $c['code'],
                'title' => $c['title']
            ];
        }

        $OSCOM_Template->addJavascriptBlock('OSCOM.countries = ' . json_encode($countriesList) . ';');

        $categories = Sites::getCategories($current_category_id);

        if (empty($categories)) {
            $categories = Sites::getCategories($OSCOM_CategoryTree->getParentId($current_category_id));
        }

        $categoriesList = [];

        foreach ($categories as $c) {
            $categoriesList[] = [
                'code' => $c['full_path'],
                'title' => $c['title']
            ];
        }

        $OSCOM_Template->addJavascriptBlock('OSCOM.categories = ' . json_encode($categoriesList) . ';');

        $OSCOM_MessageStack = Registry::get('MessageStack');

        if ($OSCOM_MessageStack->exists()) {
            $OSCOM_Template->addJavascriptBlock($OSCOM_MessageStack->get());
        }
    }

    public static function getDefaultApplication()
    {
        return static::$_default_application;
    }

    public static function hasAccess($application)
    {
        return true;
    }
}
