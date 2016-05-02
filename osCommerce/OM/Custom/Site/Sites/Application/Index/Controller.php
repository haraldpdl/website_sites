<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Application\Index;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Site\Sites\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_Template = Registry::get('Template');

        $this->_page_contents = 'main.html';
        $this->_page_title = OSCOM::getDef('html_page_title');

        if (isset($_GET['Add'])) {
            $js = <<<'EOT'
$(window).on('load', function() {
    $('#addSiteButton').click();
});
EOT;

            $OSCOM_Template->addJavascriptBlock($js);
        }

        if (!in_array('Showcase', $this->getRequestedActions())) {
            if ((OSCOM::getConfig('use_minified_resources') === 'true') && file_exists(OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/Sites/Application/Index/main.min.js')) {
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/main.min.js');
            } else {
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/main.js');
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/main-dialog_submit_site.js');
                $OSCOM_Template->addExternalJavascript('public/sites/Sites/Application/Index/main-dialog_moderate_site.js');
            }
        }
    }
}
