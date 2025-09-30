<?php

namespace App\Vito\Plugins\Flowan\VitoServiceSabnzbd\Services;

use App\Actions\FirewallRule\ManageRule;
use App\Services\AbstractService;
use Closure;
use Illuminate\Validation\Rule;

class Sabnzbd extends AbstractService
{
    public static function id(): string
    {
        return 'sabnzbd';
    }

    public static function type(): string
    {
        return 'automation';
    }

    public function unit(): string
    {
        return 'sabnzbdplus';
    }

    public function data(): array
    {
        return [
            'port' => $this->service->type_data['port'] ?? 8080,
            'access_level' => $this->service->type_data['access_level'] ?? 5,
            'username' => $this->service->type_data['username'] ?? 'vito',
            'password' => $this->service->type_data['password'] ?? 'vito',
        ];
    }

    public function creationData(array $input): array
    {
        return [
            'port' => 8080,
            'access_level' => 5,
            'username' => 'vito',
            'password' => 'vito',
        ];
    }

    public function creationRules(array $input): array
    {
        return [
            'type' => [
                function (string $attribute, mixed $value, Closure $fail): void {
                    $existingService = $this->service->server->services()
                        ->where('type', self::type())
                        ->where('name', self::id())
                        ->exists();
                    if ($existingService) {
                        $fail('SABnzbd is already installed on this server.');
                    }
                },
            ],
            'version' => ['required', Rule::in(['latest'])],
        ];
    }

    public function install(): void
    {
        $this->service->server->ssh()->exec(
            view('vito-service-sabnzbd::install-sabnzbd', [
                'port' => $this->data()['port'],
                'accessLevel' => $this->data()['access_level'],
                'username' => $this->data()['username'],
                'password' => $this->data()['password'],
            ]),
            'install-sabnzbd'
        );

        app(ManageRule::class)->create($this->service->server, [
            'name' => 'SABnzbd',
            'type' => 'allow',
            'protocol' => 'tcp',
            'port' => $this->data()['port'],
            'source_any' => true,
        ]);

        $status = $this->service->server->systemd()->status($this->unit());
        $this->service->validateInstall($status);

        $this->service->type_data = $this->data();
        $this->service->save();

        event('service.installed', $this->service);
        $this->service->server->os()->cleanup();
    }

    public function uninstall(): void
    {
        $this->service->server->ssh()->exec(
            view('vito-service-sabnzbd::uninstall-sabnzbd'),
            'uninstall-sabnzbd'
        );

        if ($rule = $this->service->server->firewallRules()
            ->where('name', 'SABnzbd')
            ->where('port', $this->data()['port'])
            ->first()
        ) {
            app(ManageRule::class)->delete($rule);
        }

        event('service.uninstalled', $this->service);
        $this->service->server->os()->cleanup();
    }

    public function enable(): void
    {
        $this->service->server->systemd()->enable($this->unit());
    }

    public function disable(): void
    {
        $this->service->server->systemd()->disable($this->unit());
    }

    public function restart(): void
    {
        $this->service->server->systemd()->restart($this->unit());
    }

    public function stop(): void
    {
        $this->service->server->systemd()->stop($this->unit());
    }

    public function start(): void
    {
        $this->service->server->systemd()->start($this->unit());
    }

    public function status(): string
    {
        try {
            $result = $this->service->server->ssh()->exec("sudo systemctl is-active {$this->unit()}");

            return trim($result) === 'active' ? 'running' : 'stopped';
        } catch (\Exception $e) {
            return 'stopped';
        }
    }

    public function version(): string
    {
        try {
            $result = $this->service->server->ssh()->exec('sabnzbdplus --version | grep -oE \'[0-9]+\.[0-9]+\.[0-9]+\'');

            return trim($result);
        } catch (\Exception $e) {
            return 'latest';
        }
    }
}
