<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthSuccessAnimationServiceProvider;
use App\Providers\PasskeyServiceProvider;
use SocialiteProviders\Manager\ServiceProvider as SocialiteManagerServiceProvider;

return [
    AppServiceProvider::class,
    AuthSuccessAnimationServiceProvider::class,
    PasskeyServiceProvider::class,
    SocialiteManagerServiceProvider::class,
];