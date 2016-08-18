<?php

namespace App\Services\Oauth;

use Liaol\SocialiteCn\SocialiteCnManager;

class SocialiteManager extends SocialiteCnManager
{

    protected function createKuaiyudianDriver()
    {
        $config = $this->app['config']['services.kuaiyudian'];
        return $this->buildProvider(
            \App\Services\Oauth\Providers\KuaiyudianProvider::class, $config
        );
    }
}
