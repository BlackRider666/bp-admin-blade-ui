<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\PageBuilder\Components;

use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\PageBuilder\Component\LinkInterface;

class LinkComponent implements LinkInterface
{
    private array $attributes = [
        'color' => 'primary',
    ];
    private string $label;
    private string $icon;


    public function __construct(string $label, string $icon = '', array $attributes = [])
    {
        $this->attributes = array_merge($this->attributes,$attributes);
        $this->label = $label;
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('bpadmin::components.link',[
            'attributes' => $this->attributes,
            'icon' => $this->icon,
            'label' => $this->label,
        ])->render();
    }
}
