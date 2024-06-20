<?php

namespace OrangeHive\Simplyment\Hook;

use OrangeHive\Simplyment\Cache\CustomCache;

class CacheClearHook
{

    public function clearCache($params = [], &$reference = NULL) {
        // Add your code for clearing cache here.

        CustomCache::flush();
    }

}