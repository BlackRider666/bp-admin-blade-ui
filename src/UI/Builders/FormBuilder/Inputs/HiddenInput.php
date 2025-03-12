<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\FormBuilder\Inputs;

use BlackParadise\LaravelAdmin\Core\Builders\FormBuilder\Inputs\GetTypeTrait;
use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\FormBuilder\Inputs\InputInterface;
use Throwable;

class HiddenInput implements InputInterface
{
    use GetTypeTrait;

    private array $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = array_merge($this->attributes,$attributes);
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function render(): string
    {
        $view =  view('bpadmin::components.inputs.hidden', [
            'attributes' => $this->attributes,
        ]);
        return $view->render();
    }

    public function getRules(): array
    {
        return [];
    }
}
