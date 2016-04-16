<?php
/**
 * osCommerce Sites
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Sites;

use osCommerce\OM\Core\OSCOM;

class MessageStack extends \osCommerce\OM\Core\MessageStack
{
    public function get(string $group = null): string
    {
        if (empty($group)) {
            $group = OSCOM::getSiteApplication();
        }

        $result = '';

        if ($this->exists($group)) {
            $result .= '$(window).on(\'load\', function() {' . "\n";

            foreach ($this->_data[$group] as $message) {
                $result .= '  toastr.' . $message['type'] . '("' . $message['text'] . '");' . "\n";
            }

            $result .= '});' . "\n";

            unset($this->_data[$group]);
        }

        return $result;
    }
}
