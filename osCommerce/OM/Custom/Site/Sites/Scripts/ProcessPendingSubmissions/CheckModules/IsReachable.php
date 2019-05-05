<?php
/**
 * osCommerce Sites Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions\CheckModules;

use GuzzleHttp\Client as GuzzleClient;

class IsReachable extends \osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions\CheckModulesAbstract
{
    public static $priority = 10;

    public $module_id = 1;
    public $public_fail_error = 'Site not reachable';

    public function execute(): ?bool
    {
        try {
            $client = new GuzzleClient();
            $response = $client->get($this->url, ['http_errors' => false]);

            $this->result = $response->getStatusCode();

            if ($this->result === 200) {
                return true;
            }

            $this->public_fail_error = 'HTTP ' . $this->result . ': ' . $response->getReasonPhrase();
        } catch (\Exception $e) {
        }

        return false;
    }
}
