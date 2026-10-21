<?php

namespace App\Modules;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ReflectionClass;

/**
 * Basis provider modul (modular monolith, vertical slice).
 * Setiap modul MEMILIKI file fiturnya sendiri di dalam folder modul,
 * dan provider inilah yang merakitnya.
 *
 * - routes/{portal}.php - dimuat otomatis; isinya wajib dibungkus PortalRoutes
 * - resources/views - view('alias::...')
 * - resources/views/livewire - <livewire:alias::nama-komponen />
 * - Http/Controllers, Services, Data, Actions - kelas PHP biasa (PSR-4)
 *
 * Shared kernel (TIDAK dipindah ke modul): model Eloquent, layout/komponen UI,
 * middleware, dan dashboard portal.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract protected function moduleAlias(): string;

    public function boot(): void
    {
        $path = $this->modulePath();

        $this->loadViewsFrom($path.'/resources/views', $this->moduleAlias());
        
        if (class_exists(Livewire::class)) {
            Livewire::addNamespace(
                namespace: $this->moduleAlias(),
                viewPath: $path.'/resources/views/livewire',
            );
        }

        foreach (glob($path.'/routes/*.php') ?: [] as $routeFile) {
            $this->loadRoutesFrom($routeFile);
        }
    }

    protected function modulePath(): string
    {
        return dirname((new ReflectionClass($this))->getFileName());
    }
}