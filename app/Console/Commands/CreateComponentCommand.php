<?php

namespace Webpress\Command\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateComponentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:webpress-component {name} {--type=webpress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Webpress component';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating a new Webpress component...');
        $type = $this->option('type');
        $name = $this->argument('name');
        $viewName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
        if ($type === 'webpress') {
            $componentClassNamespace = config('webpress-component.component.class_namespace.webpress');
            $componentPath = config('webpress-component.component.class_path.webpress') . '\\' . $name . '.php';
            $componentViewPath = config('webpress-component.component.view_path.webpress') . '\\' . $viewName . '.blade.php';
            $componentView = "webpress.component::components." . $viewName;
            $livewireClassNamespace = config('webpress-component.livewire.class_namespace.webpress');
            $livewirePath = config('webpress-component.livewire.class_path.webpress') . '\\' . $name . '.php';
            $livewireViewPath = config('webpress-component.livewire.view_path.webpress') . '\\' . $viewName . '.blade.php';
            $livewireView = "webpress.livewire::" . $viewName;
        } else {
            $componentClassNamespace = config('webpress-component.component.class_namespace.app');
            $componentPath = config('webpress-component.component.class_path.app') . '\\' . $name . '.php';
            $componentViewPath = config('webpress-component.component.view_path.app') . '\\' . $viewName . '.blade.php';
            $componentView = "components." . $viewName;
            $livewireClassNamespace = config('webpress-component.livewire.class_namespace.app');
            $livewirePath = config('webpress-component.livewire.class_path.app') . '\\' . $name . '.php';
            $livewireViewPath = config('webpress-component.livewire.view_path.app') . '\\' . $viewName . '.blade.php';
            $livewireView = "livewire." . $viewName;
        }
        if (File::exists($componentPath)) {
            $this->error("Class component already exists: $componentPath");
        } else {
            $componentDefaultContent = File::get(config('webpress-component.component.class_default'));
            $componentDefaultContent = str_replace('DefaultComponent', $name, $componentDefaultContent);
            $componentDefaultContent = str_replace('Webpress\Component\Components', $componentClassNamespace, $componentDefaultContent);
            $componentDefaultContent = str_replace('4a008341-4356-4b29-bbf7-6892b3b9e469', Str::uuid(), $componentDefaultContent);
            $componentDefaultContent = str_replace('webpress.component::default', $componentView, $componentDefaultContent);

            File::put($componentPath, $componentDefaultContent);
            $this->info('CLASS COMPONENT: ' . $componentPath);
        }

        if (File::exists($componentViewPath)) {
            $this->error("View component already exists: $componentViewPath");  
        } else {
            $componentDefaultViewContent = "@livewire('$name')";
            File::put($componentViewPath, $componentDefaultViewContent);
            $this->info('VIEW COMPONENT: ' . $componentViewPath);
        }

        if (File::exists($livewirePath)) {
            $this->error("Livewire component already exists: $livewirePath");
        } else {
            $livewireDefaultContent = File::get(config('webpress-component.livewire.class_default'));
            $livewireDefaultContent = str_replace('DefaultComponent', $name, $livewireDefaultContent);
            $livewireDefaultContent = str_replace('Webpress\Livewire\Livewire', $livewireClassNamespace, $livewireDefaultContent);
            $livewireDefaultContent = str_replace('webpress.livewire::default', $livewireView, $livewireDefaultContent);
            File::put($livewirePath, $livewireDefaultContent);
            $this->info('LIVEWIRE COMPONENT: ' . $livewirePath);
        }

        if (File::exists($livewireViewPath)) {
            $this->error("Livewire view already exists: $livewireViewPath");
        } else {
            $livewireDefaultViewContent = "<section>$name</section>";
            File::put($livewireViewPath, $livewireDefaultViewContent);
            $this->info('LIVEWIRE VIEW: ' . $livewireViewPath);
        }
        $this->info('Webpress component created successfully!');
        return Command::SUCCESS;
    }
}
