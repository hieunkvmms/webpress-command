<?php

namespace Hieunk\Command\Console\Commands;

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
            $componentDefaultContent = $this->getComponentClassDefaultContent($name, $componentClassNamespace, $componentView);
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
            $livewireDefaultContent = $this->getLivewireClassDefaultContent($name, $livewireClassNamespace, $livewireView);
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

    public function getComponentClassDefaultContent($name, $componentClassNamespace, $componentView, $hasColumn = false, $hasLimit = false)
    {
        $uuid = Str::uuid();
        $content =  <<<'PHP'
        <?php
        
        namespace {$componentClassNamespace};
        
        use Illuminate\View\Compilers\BladeCompiler;
        use Webpress\Component\Contracts\ExportableWebpressComponent;
        use Webpress\Component\Contracts\WebpressComponent;
        use Webpress\Component\Enums\ComponentSettingKey;
        use Webpress\Component\Enums\CoreComponentControlType;
        use Webpress\Component\Enums\CoreGroupComponent;
        use Webpress\Component\Traits\CanExportComponentTrait;
        
        class {$name} implements WebpressComponent, ExportableWebpressComponent
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
                return '{$name}';
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
                    [
                        'key' => ComponentSettingKey::HEADING_TAG->name(),
                        'label' => 'core.component.setting.heading_tag.label',
                        'placeholder' => 'core.component.setting.heading_tag.placeholder',
                        'default' => 'h3',
                        'control' => CoreComponentControlType::SELECT->name(),
                        'options' => [
                            ...app('webpress.component')->getHeadingTagAsControlOption(),
                        ],
                    ],
                    {$hasColumn}
                    {$hasLimit}
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
        $content = str_replace('{$componentClassNamespace}', $componentClassNamespace, $content);
        $content = str_replace('{$name}', $name, $content);
        $content = str_replace('{$uuid}', $uuid, $content);
        $content = str_replace('{$componentView}', $componentView, $content);
        $content = str_replace('{$hasColumn}', $hasColumn ? $this->getComponentSettingColumn() : '', $content);
        $content = str_replace('{$hasLimit}', $hasLimit ? $this->getComponentSettingLimit() : '', $content);
        return $content;
    }

    public function getLivewireClassDefaultContent($name, $livewireClassNamespace, $livewireView, $hasColumn = false, $hasLimit = false)
    {
        $content = <<<'PHP'
        <?php
            namespace {$livewireClassNamespace};

            use Livewire\Component;

            class {$name} extends Component
            {
                public $className;
                public $style = 'style-1';
                public $headingTag = 'h2';
                {$hasColumn}
                {$hasLimit}
                public $componentId;
                
                public function mount()
                {
                    $this->componentId = '{$livewireView}' . $this->__id;
                }

                public function render()
                {
                    return view('{$livewireView}');
                }
            }
        PHP;
        $content = str_replace('{$livewireClassNamespace}', $livewireClassNamespace, $content);
        $content = str_replace('{$name}', $name, $content);
        $content = str_replace('{$livewireView}', $livewireView, $content);
        $content = str_replace('{$hasColumn}', $hasColumn ? $this->getLivewireAttributeColumn() : '', $content);
        $content = str_replace('{$hasLimit}', $hasLimit ? $this->getLivewireAttributeLimit() : '', $content);
        return $content;
    }

    public function getComponentSettingColumn()
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

    public function getComponentSettingLimit()
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

    public function getLivewireAttributeColumn()
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
    
    public function getLivewireAttributeLimit()
    {
        return <<<'PHP'
        public $limit = 8;
        PHP;
    }
}
