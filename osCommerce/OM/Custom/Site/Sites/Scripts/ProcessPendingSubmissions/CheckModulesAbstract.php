<?php
/**
 * osCommerce Sites Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Sites\Scripts\ProcessPendingSubmissions;

abstract class CheckModulesAbstract
{
    public static $priority;

    public $module_id;
    public $url;
    public $result;
    public $public_fail_error = 'Failed';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    abstract public function execute(): ?bool;
}
