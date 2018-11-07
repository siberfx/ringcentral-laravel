<?php

namespace Coxy121\RingCentralLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;

class RingCentralServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Config file path.
        $dist = __DIR__.'/../config/ringcentral.php';

        // Merge config.
        $this->mergeConfigFrom($dist, 'ringcentral');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind RingCentral Client in Service Container.
        $this->app->singleton('ringcentral', function () {
            return $this->createRingCentralClient();
        });
    }

    /**
     * Create a new RingCentral Client.
     *
     * @return RingCentral
     *
     */
    protected function createRingCentralClient()
    {
        // Check for RingCentral config file.
        if (! $this->hasRingCentralConfigSection()) {
            $this->raiseRunTimeException('Missing RingCentral configuration.');
        }

        if ($this->ringCentralConfigHasNo('client_id')) {
            $this->raiseRunTimeException('Missing client_id.');
        }

        if ($this->ringCentralConfigHasNo('client_secret')) {
            $this->raiseRunTimeException('Missing client_secret.');
        }

        if ($this->ringCentralConfigHasNo('server_url')) {
            $this->raiseRunTimeException('Missing server_url.');
        }

        if ($this->ringCentralConfigHasNo('username')) {
            $this->raiseRunTimeException('Missing username.');
        }

        if ($this->ringCentralConfigHasNo('operator_extension')) {
            $this->raiseRunTimeException('Missing extension.');
        }

        if ($this->ringCentralConfigHasNo('operator_password')) {
            $this->raiseRunTimeException('Missing password.');
        }

        $ringCentral =  (new RingCentral())
                ->setClientId(config('ringcentral.client_id'))
                ->setClientSecret(config('ringcentral.client_secret'))
                ->setServerUrl(config('ringcentral.server_url'))
                ->setUsername(config('ringcentral.username'))
                ->setOperatorExtension(config('ringcentral.operator_extension'))
                ->setOperatorPassword(config('ringcentral.operator_password'));

        if($this->ringCentralConfigHas('admin_extension')){
            $ringCentral->setAdminExtension(config('ringcentral.admin_extension'));
        }

        if($this->ringCentralConfigHas('admin_password')){
            $ringCentral->setAdminPassword(config('ringcentral.admin_password'));
        }

        return $ringCentral;
    }

    /**
     * Checks if has global RingCentral configuration section.
     *
     * @return bool
     */
    protected function hasRingCentralConfigSection()
    {
        return $this->app->make(Config::class)
                         ->has('ringcentral');
    }

    /**
     * Checks if RingCentral config does not
     * have a value for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function ringCentralConfigHasNo($key)
    {
        return ! $this->ringCentralConfigHas($key);
    }

    /**
     * Checks if RingCentral config has value for the
     * given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function ringCentralConfigHas($key)
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);

        // Check for RingCentral config file.
        if (! $config->has('ringcentral')) {
            return false;
        }

        return
            $config->has('ringcentral.'.$key) &&
            ! is_null($config->get('ringcentral.'.$key)) &&
            ! empty($config->get('ringcentral.'.$key));
    }


    /**
     * Raises Runtime exception.
     *
     * @param string $message
     *
     * @throws \RuntimeException
     */
    protected function raiseRunTimeException($message)
    {
        throw new \RuntimeException($message);
    }
}
