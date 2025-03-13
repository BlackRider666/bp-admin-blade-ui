<?php

namespace BlackParadise\AdminBladeUI\UI\Components;

use BlackParadise\LaravelAdmin\Core\Interfaces\Components\ComponentInterface;

class LinkComponent implements ComponentInterface
{
    private array $attributes = [
        'color' => 'primary',
    ];


    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge($this->attributes,$attributes);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('bpadmin::components.common.link',[
            'attributes' => $this->attributes,
        ])->render();
    }
}
