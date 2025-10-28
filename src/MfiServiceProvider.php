<?php

namespace Auxfin\Mfi;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Schema\Source\SchemaStitcher;


class MfiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        try {
            //Register generate command
            $this->commands([
                \Auxfin\Mfi\Console\MfiInstall::class,
            ]);

            //Register Config file
            $this->mergeConfigFrom(__DIR__ . '/../config/mfi.php', 'mfi');

            //Publish Config
            $this->publishes([
                __DIR__ . '/../config/mfi.php' => config_path('mfi.php'),
            ], 'mfi-config');

//            $dispatcher = app(\Illuminate\Contracts\Events\Dispatcher::class);
//            $dispatcher->listen(
//                \Nuwave\Lighthouse\Events\BuildSchemaString::class,
//                function (): string {
//                    $stitcher = new SchemaStitcher(__DIR__ . '/resources/graphql/schema.graphql');
//                    return $stitcher->getSchemaString();
//                }
//            );

            //Register Routes
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
        catch(\Exception $e){
            throw $e;
        }

    }

    public function boot(): void
    {
        //you boot methods here
    }
}
