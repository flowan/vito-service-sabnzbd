<?php

namespace App\Vito\Plugins\Flowan\VitoServiceSabnzbd;

use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterServiceType;
use App\Plugins\RegisterViews;
use App\Vito\Plugins\Flowan\VitoServiceSabnzbd\Services\Sabnzbd;
use Illuminate\Support\Facades\Artisan;

class Plugin extends AbstractPlugin
{
    protected string $name = 'SABnzbd';

    protected string $description = 'The automated usenet download tool';

    public function boot(): void
    {
        RegisterViews::make('vito-service-sabnzbd')
            ->path(__DIR__.'/views')
            ->register();

        RegisterServiceType::make('sabnzbd')
            ->type(Sabnzbd::type())
            ->label($this->name)
            ->handler(Sabnzbd::class)
            ->versions([
                'latest',
            ])
            ->register();
    }

    public function enable(): void
    {
        // Temporary fix until this is fixed in vito, see https://github.com/vitodeploy/vito/issues/842
        dispatch(fn () => Artisan::call('horizon:terminate'));
    }
}
