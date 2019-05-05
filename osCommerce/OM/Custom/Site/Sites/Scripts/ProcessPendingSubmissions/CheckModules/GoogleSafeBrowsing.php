<?php
/**
 * osCommerce Sites Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions\CheckModules;

use osCommerce\OM\Core\{
    HttpRequest,
    OSCOM
};

class GoogleSafeBrowsing extends \osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions\CheckModulesAbstract
{
    public static $priority = 100;

    public $module_id = 2;
    public $public_fail_error = 'Dangerous Site detected (Virus/Malware/Phishing)';

    public function execute(): ?bool
    {
        $data = [
            'client' => [
                'clientId' => 'oscommerce-live-sites',
                'clientVersion' => '1.0.0'
            ],
            'threatInfo' => [
                'threatTypes' => [
                    'MALWARE',
                    'SOCIAL_ENGINEERING'
                ],
                'platformTypes' => [
                    'ANY_PLATFORM'
                ],
                'threatEntryTypes' => [
                    'URL'
                ],
                'threatEntries' => [
                    [
                        'url' => $this->url
                    ]
                ]
            ]
        ];

        $result = HttpRequest::getResponse([
            'url' => 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . OSCOM::getConfig('google_safe_browsing_lookup_api_key'),
            'format' => 'json',
            'parameters' => $data
        ]);

        if (is_array($result)) {
            $this->result = 'ok';

            if (isset($result['matches'])) {
                foreach ($result['matches'] as $match) {
                    if ($match['threat']['url'] === $this->url) {
                        $this->result = $match['threatType'];

                        return false;
                    }
                }
            }

            return true;
        }

        return null;
    }
}
