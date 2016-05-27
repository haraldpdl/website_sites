<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Account;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Sites\Sites;

class Controller extends \osCommerce\OM\Core\Site\Sites\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Template = Registry::get('Template');

        $OSCOM_Template->addHtmlHeaderTag('<meta name="robots" content="noindex, nofollow">');

        if (isset($_SESSION['Website']['Account'])) {
            if ($_GET['Account'] == 'new-site-added') {
                $OSCOM_MessageStack->add(OSCOM::getSiteApplication(), OSCOM::getDef('ms_new_submission_success'), 'success');
            }

            $OSCOM_Template->setValue('user_name', $_SESSION['Website']['Account']['name']);

            $breadcrumb = [
                [
                    'title' => OSCOM::getDef('breadcrumb_account'),
                    'link' => OSCOM::getLink(null, 'Account')
                ]
            ];

            $OSCOM_Template->setValue('breadcrumb_path', $breadcrumb);

            $this->_page_contents = 'main.html';
            $this->_page_title = OSCOM::getDef('account_html_title');

            if ((OSCOM::getConfig('use_minified_resources') === 'true') && file_exists(OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/Sites/Application/Account/main.min.js')) {
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Account/main.min.js');
            } else {
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Account/main.js');
            }
        } else {
            if (empty($this->getRequestedActions())) {
                $this->runAction('Login');
            }
        }
    }

    public function getRedirectUrlParams(): array
    {
        $params = [];

        if (isset($_GET['category']) && !empty($_GET['category']) && Sites::categoryExists(explode('--', $_GET['category']), true)) {
            foreach (explode('--', $_GET['category']) as $c) {
                $params[] = $c;
            }
        }

        if (isset($_GET['country']) && !empty($_GET['country']) && Sites::countryExists($_GET['country'], true)) {
            $params[] = 'country=' . $_GET['country'];
        }

        if (isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) && ($page > 0)) {
            $params[] = 'page=' . (int)$_GET['page'];
        }

        return $params;
    }
}
