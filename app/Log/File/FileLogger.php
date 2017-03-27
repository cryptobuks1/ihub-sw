<?php

namespace App\Log\File;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;

class FileLogger
{
    public function bootLogger(Application $app, Monolog $monolog)
    {
        $log = $this->registerLogger($app);

        $this->configureHandlers($app, $log);

        return $log;
    }

    protected function registerLogger(Application $app)
    {
        return new Writer(
            new Monolog($this->channel($app)), $app['events']
        );
    }

    /**
     * Get the name of the log "channel".
     *
     * @return string
     */
    protected function channel(Application $app)
    {
        return $app->bound('env') ? $app->environment() : 'production';
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureHandlers(Application $app, Writer $log)
    {
        $method = 'configure'.ucfirst($app['config']['log.logger.connections.file.log']).'Handler';

        $this->{$method}($app, $log);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSingleHandler(Application $app, Writer $log)
    {
        $log->useFiles(
            $app->storagePath().'/logs/laravel.log',
            $app->make('config')->get('log.logger.connections.file.log_level', 'debug')
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Application $app, Writer $log)
    {
        $config = $app->make('config');

        $maxFiles = $config->get('log.logger.connections.file.log_max_files');

        $log->useDailyFiles(
            $app->storagePath().'/logs/laravel.log', is_null($maxFiles) ? 5 : $maxFiles,
            $config->get('app.log_level', 'debug')
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSyslogHandler(Application $app, Writer $log)
    {
        $log->useSyslog(
            'laravel',
            $app->make('config')->get('log.logger.connections.file.log_level', 'debug')
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureErrorlogHandler(Application $app, Writer $log)
    {
        $log->useErrorLog($app->make('config')->get('log.logger.connections.file.log_level', 'debug'));
    }
}