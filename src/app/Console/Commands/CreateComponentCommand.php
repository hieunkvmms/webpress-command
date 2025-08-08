<?php

namespace Hieunk\Command\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateComponentCommand extends Command
{
    protected $signature = 'make:webpress-component {name} {--type=app} {--column} {--limit}';
    protected $description = 'Create a new Webpress component with configurable paths and namespaces';

    protected $config;
    protected $type;
    protected $baseName;
    protected $componentName;
    protected $livewireName;
    protected $componentViewName;
    protected $livewireViewName;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Creating a new Webpress component...');

        $this->initializeProperties();

        if (!$this->validateConfiguration()) {
            return Command::FAILURE;
        }

        $this->createComponentFiles();

        $this->info('Webpress component created successfully!');
        return Command::SUCCESS;
    }

    protected function initializeProperties()
    {
        $this->config = config('webpress-component');
        $this->type = $this->option('type');
        $this->baseName = $this->argument('name');

        // Tạo tên class với suffix
        $this->componentName = $this->baseName . $this->getConfigValue('component.subfix_name');
        $this->livewireName = $this->baseName . $this->getConfigValue('livewire.subfix_name');

        // Tạo tên view với suffix
        $this->componentViewName = $this->generateViewName($this->componentName);
        $this->livewireViewName = $this->generateViewName($this->livewireName);
    }

    protected function validateConfiguration()
    {
        if (!isset($this->config['component']['class_namespace'][$this->type])) {
            $this->error("Configuration for type '{$this->type}' not found. Available types: " .
                implode(', ', array_keys($this->config['component']['class_namespace'])));
            return false;
        }
        return true;
    }

    protected function createComponentFiles()
    {
        $this->createComponentClass();
        $this->createComponentView();
        $this->createLivewireClass();
        $this->createLivewireView();
    }

    protected function createComponentClass()
    {
        $path = $this->getFullPath('component.class_path') . DIRECTORY_SEPARATOR . $this->componentName . '.php';

        if (File::exists($path)) {
            $this->error("Component class already exists: $path");
            return;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->getComponentClassContent();
        File::put($path, $content);
        $this->info('COMPONENT CLASS: ' . $path);
    }

    protected function createComponentView()
    {
        $path = $this->getFullPath('component.view_path') . DIRECTORY_SEPARATOR . $this->componentViewName . '.blade.php';

        if (File::exists($path)) {
            $this->error("Component view already exists: $path");
            return;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->getComponentViewContent();
        File::put($path, $content);
        $this->info('COMPONENT VIEW: ' . $path);
    }

    protected function createLivewireClass()
    {
        $path = $this->getFullPath('livewire.class_path') . DIRECTORY_SEPARATOR . $this->livewireName . '.php';

        if (File::exists($path)) {
            $this->error("Livewire class already exists: $path");
            return;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->getLivewireClassContent();
        File::put($path, $content);
        $this->info('LIVEWIRE CLASS: ' . $path);
    }

    protected function createLivewireView()
    {
        $path = $this->getFullPath('livewire.view_path') . DIRECTORY_SEPARATOR . $this->livewireViewName . '.blade.php';

        if (File::exists($path)) {
            $this->error("Livewire view already exists: $path");
            return;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->getLivewireViewContent();
        File::put($path, $content);
        $this->info('LIVEWIRE VIEW: ' . $path);
    }

    protected function getConfigValue($key)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (isset($value[$k][$this->type])) {
                $value = $value[$k][$this->type];
            } elseif (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $this->getDefaultConfigValue($key);
            }
        }

        return $value;
    }

    protected function getDefaultConfigValue($key)
    {
        $keys = explode('.', $key);
        $value = $this->config['default_settings'] ?? [];

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return '';
            }
        }

        return $value;
    }

    protected function getFullPath($configKey)
    {
        $path = $this->getConfigValue($configKey);

        // Chuẩn hóa đường dẫn - thay thế / và \ thành DIRECTORY_SEPARATOR
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // Nếu path không phải absolute path, thì convert từ base_path
        if (!$this->isAbsolutePath($path)) {
            $path = base_path($path);
        }

        return $path;
    }

    protected function isAbsolutePath($path)
    {
        // Windows: C:\ hoặc C:/
        // Unix/Linux: /
        return (DIRECTORY_SEPARATOR === '\\' && preg_match('/^[a-zA-Z]:/', $path)) ||
            (DIRECTORY_SEPARATOR === '/' && strpos($path, '/') === 0);
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function generateViewName($name)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    }

    protected function getComponentClassContent()
    {
        $namespace = $this->getConfigValue('component.class_namespace');
        $componentView = $this->getConfigValue('component.view_prefix') . $this->componentViewName;
        $uuid = Str::uuid();
        $hasColumn = $this->option('column');
        $hasLimit = $this->option('limit');

        $content = <<<'PHP'
<?php

namespace {$namespace};

use Illuminate\View\Compilers\BladeCompiler;
use Webpress\Component\Contracts\ExportableWebpressComponent;
use Webpress\Component\Contracts\WebpressComponent;
use Webpress\Component\Enums\ComponentSettingKey;
use Webpress\Component\Enums\CoreComponentControlType;
use Webpress\Component\Enums\CoreGroupComponent;
use Webpress\Component\Traits\CanExportComponentTrait;

class {$className} implements WebpressComponent, ExportableWebpressComponent
{
    use CanExportComponentTrait;
    
    public function id(): string
    {
        return '{$uuid}';
    }

    public function thumbnail(): string
    {
        return 'block';
    }

    public function description(): string
    {
        return 'block';
    }

    public function group(): string
    {
        return CoreGroupComponent::BLOCK->name();
    }

    public function name(): string
    {
        return '{$className}';
    }

    public function setting(): array
    {
        return [
            [
                'key' => ComponentSettingKey::CLASS_NAME->name(),
                'label' => 'core.component.setting.class_name.label',
                'placeholder' => 'core.component.setting.class_name.placeholder',
                'default' => '',
                'control' => CoreComponentControlType::TEXT->name(),
            ],
            {$columnSettings}
            {$limitSettings}
        ];
    }

    public function schema(): array
    {
        return [
            [
                'key' => 'style',
                'label' => 'Kiểu',
                'placeholder' => 'Chọn kiểu',
                'control' => CoreComponentControlType::SELECT->name(),
                'options' => [
                    [
                        'label' => 'Kiểu 1',
                        'value' => 'style-1'
                    ],
                ],
            ],
        ];
    }

    public function view(): string
    {
        return '{$componentView}';
    }

    public function render($data, $setting): string
    {
        return BladeCompiler::render($this->view(), ['data' => $data, 'setting' => $setting]);
    }
}
PHP;

        return str_replace([
            '{$namespace}',
            '{$className}',
            '{$uuid}',
            '{$componentView}',
            '{$columnSettings}',
            '{$limitSettings}'
        ], [
            $namespace,
            $this->componentName,
            $uuid,
            $componentView,
            $hasColumn ? $this->getComponentSettingColumn() : '',
            $hasLimit ? $this->getComponentSettingLimit() : ''
        ], $content);
    }

    protected function getComponentViewContent()
    {
        $content = <<<'PHP'
@php
$className = app('webpress.component.setting')->getClassName($setting, '');
$style = app('webpress.component')->getValueComponentByKey($data, 'style', 'style-1');
echo '@livewire(\'{$livewireViewName}\', [
    "className" => "' . $className . '",
    "style" => "' . $style . '",
])'; 
@endphp
PHP;

        return str_replace('{$livewireViewName}', $this->livewireViewName, $content);
    }

    protected function getLivewireClassContent()
    {
        $namespace = $this->getConfigValue('livewire.class_namespace');
        $livewireView = $this->getConfigValue('livewire.view_prefix') . $this->livewireViewName;
        $hasColumn = $this->option('column');
        $hasLimit = $this->option('limit');

        $content = <<<'PHP'
<?php

namespace {$namespace};

use Livewire\Component;

class {$className} extends Component
{
    public $className;
    public $style = 'style-1';
    public $headingTag = 'h2';
    {$columnAttributes}
    {$limitAttributes}
    public $componentId;
    
    public function mount()
    {
        $this->componentId = '{$baseViewName}-' . $this->__id;
    }

    public function render()
    {
        return view('{$livewireView}');
    }
}
PHP;

        return str_replace([
            '{$namespace}',
            '{$className}',
            '{$baseViewName}',
            '{$livewireView}',
            '{$columnAttributes}',
            '{$limitAttributes}'
        ], [
            $namespace,
            $this->livewireName,
            $this->generateViewName($this->baseName), // Sử dụng base name cho componentId
            $livewireView,
            $hasColumn ? $this->getLivewireAttributeColumn() : '',
            $hasLimit ? $this->getLivewireAttributeLimit() : ''
        ], $content);
    }

    protected function getLivewireViewContent()
    {
        $baseViewName = $this->generateViewName($this->baseName);

        $content = <<<'PHP'
<div class="{$viewName}" id="{{ $componentId }}">
    <style>
    
    </style>
    <div class="{$viewName}__wrapper">
    
    </div>
    <script>

    </script>
</div>
PHP;

        return str_replace('{$viewName}', $baseViewName, $content);
    }

    protected function getComponentSettingColumn()
    {
        return <<<'PHP'
            [
                'key' => ComponentSettingKey::XS_COLUMN->name(),
                'label' => 'core.component.setting.xs_column.label',
                'placeholder' => 'core.component.setting.xs_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
            [
                'key' => ComponentSettingKey::SM_COLUMN->name(),
                'label' => 'core.component.setting.sm_column.label',
                'placeholder' => 'core.component.setting.sm_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
            [
                'key' => ComponentSettingKey::MD_COLUMN->name(),
                'label' => 'core.component.setting.md_column.label',
                'placeholder' => 'core.component.setting.md_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
            [
                'key' => ComponentSettingKey::LG_COLUMN->name(),
                'label' => 'core.component.setting.lg_column.label',
                'placeholder' => 'core.component.setting.lg_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
            [
                'key' => ComponentSettingKey::XL_COLUMN->name(),
                'label' => 'core.component.setting.xl_column.label',
                'placeholder' => 'core.component.setting.xl_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
            [
                'key' => ComponentSettingKey::XXL_COLUMN->name(),
                'label' => 'core.component.setting.xxl_column.label',
                'placeholder' => 'core.component.setting.xxl_column.placeholder',
                'default' => 1,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
PHP;
    }

    protected function getComponentSettingLimit()
    {
        return <<<'PHP'
            [
                'key' => ComponentSettingKey::LIMIT->name(),
                'label' => 'core.component.setting.limit.label',
                'placeholder' => 'core.component.setting.limit.placeholder',
                'default' => 8,
                'control' => CoreComponentControlType::NUMBER->name(),
            ],
PHP;
    }

    protected function getLivewireAttributeColumn()
    {
        return <<<'PHP'
    public $xsColumn = 1;
    public $smColumn = 1;
    public $mdColumn = 1;
    public $lgColumn = 1;
    public $xlColumn = 1;
    public $xxlColumn = 1;
PHP;
    }

    protected function getLivewireAttributeLimit()
    {
        return <<<'PHP'
    public $limit = 8;
PHP;
    }
}
