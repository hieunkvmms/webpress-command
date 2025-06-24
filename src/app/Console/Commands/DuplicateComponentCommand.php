<?php

namespace Hieunk\Command\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use ReflectionClass;
use Webpress\Component\Facades\WebpressComponent;

class DuplicateComponentCommand extends Command
{
    protected $signature = 'duplicate:webpress-component {component} {--name=} {--from=core} {--to=app}';
    protected $description = 'Duplicate a component {component} from {from} to {to}';
    protected $component;
    protected $from;
    protected $to;
    protected $name;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->component = $this->argument('component');
        $this->from = $this->option('from');
        $this->to = $this->option('to');
        $this->name = $this->option('name');

        $this->info("Duplicating component {$this->component} from {$this->from} to {$this->to}");

        $this->duplicateComponent();
    }

    protected function duplicateComponent()
    {
        switch ($this->to) {
            case 'core':
                $toComponentClassPath = config('webpress-component.component.class_path.webpress', '');
                $toComponentViewPath = config('webpress-component.component.view_path.webpress', '');
                $toLivewireClassPath = config('webpress-component.livewire.class_path.webpress', '');
                $toLivewireViewPath = config('webpress-component.livewire.view_path.webpress', '');
                break;
            case 'app':
                $toComponentClassPath = config('webpress-component.component.class_path.app', '');
                $toComponentViewPath = config('webpress-component.component.view_path.app', '');
                $toLivewireClassPath = config('webpress-component.livewire.class_path.app', '');
                $toLivewireViewPath = config('webpress-component.livewire.view_path.app', '');
                break;
            default:
                $this->error("Invalid to {$this->to}");
                break;
        }

        foreach (WebpressComponent::all() as $item) {
            if ($item->id() == $this->component) {
                $coreComponentClassPath = (new ReflectionClass($item))->getFileName();
                $coreComponentViewPath = View::getFinder()->find($item->view());
                $componentContentView = file_get_contents($coreComponentViewPath);
                if (preg_match("/@livewire\(\\\s*['\"]([^'\"]+)\\\['\"]\s*,/m", $componentContentView, $matches)) {
                    $livewireViewName = $matches[1];
                    $livewireClassName = str_replace(' ', '', ucwords(str_replace('-', ' ', $matches[1])));
                } else {
                    $this->error("Component {$this->component} is not have livewire");
                    return;
                }
                $coreLivewireClassPath = config('webpress-component.livewire.class_path.webpress', '') . '/' . $livewireClassName . '.php';
                $coreLivewireViewPath = config('webpress-component.livewire.view_path.webpress', '') . '/' . $livewireViewName . '.blade.php';
                // Copy class component
                $componentContent = file_get_contents($coreComponentClassPath);
                $componentContent = str_replace(
                    config('webpress-component.component.class_namespace.webpress'),
                    config('webpress-component.component.class_namespace.' . $this->to),
                    $componentContent
                );
                $componentName = class_basename($item) . config('webpress-component.component.subfix_name.' . $this->to, '');
                $componentContent = str_replace(
                    class_basename($item),
                    $componentName,
                    $componentContent
                );
                $componentViewFileName = str_replace(
                    config('webpress-component.component.view_prefix.webpress', ''),
                    '',
                    $item->view() . '-' . strtolower(config('webpress-component.component.subfix_name.' . $this->to))
                );
                $componentViewName = config('webpress-component.component.view_prefix.' . $this->to, '') . $componentViewFileName;
                $componentContent = str_replace(
                    $item->view(),
                    $componentViewName,
                    $componentContent
                );
                $componentContent = str_replace(
                    $item->id(),
                    Str::uuid(),
                    $componentContent
                );
                $componentFilePath = $toComponentClassPath . '/' . $componentName . '.php';
                File::put($componentFilePath, $componentContent);

                // Copy view component
                $componentViewContent = file_get_contents($coreComponentViewPath);
                $componentViewContent = str_replace(
                    $livewireViewName,
                    $livewireViewName . '-' . strtolower(config('webpress-component.livewire.subfix_name.' . $this->to, '')),
                    $componentViewContent
                );
                $componentViewFilePath = $toComponentViewPath . '/' . $componentViewFileName . '.blade.php';
                File::put($componentViewFilePath, $componentViewContent);

                // Copy livewire component
                $livewireContent = file_get_contents($coreLivewireClassPath);
                $livewireContent = str_replace(
                    config('webpress-component.livewire.class_namespace.webpress', ''),
                    config('webpress-component.livewire.class_namespace.' . $this->to, ''),
                    $livewireContent
                );
                $livewireContent = str_replace(
                    $livewireViewName,
                    $livewireViewName . '-' . strtolower(config('webpress-component.livewire.subfix_name.' . $this->to, '')),
                    $livewireContent
                );
                $livewireContent = str_replace(
                    $livewireClassName,
                    $livewireClassName . config('webpress-component.livewire.subfix_name.' . $this->to, ''),
                    $livewireContent
                );
                $livewireContent = str_replace(
                    config('webpress-component.livewire.view_prefix.webpress', ''),
                    config('webpress-component.livewire.view_prefix.' . $this->to, ''),
                    $livewireContent
                );
                $livewireFilePath = $toLivewireClassPath . '/' . $livewireClassName . config('webpress-component.livewire.subfix_name.' . $this->to, '') . '.php';
                File::put($livewireFilePath, $livewireContent);

                // Copy view livewire
                $livewireViewContent = file_get_contents($coreLivewireViewPath);
                $livewireViewFilePath = $toLivewireViewPath . '/' . $livewireViewName . '-' . strtolower(config('webpress-component.livewire.subfix_name.' . $this->to, '')) . '.blade.php';
                File::put($livewireViewFilePath, $livewireViewContent);
                $this->info("Duplicated component {$this->component} to {$this->to}");
            }
        }
    }
}
