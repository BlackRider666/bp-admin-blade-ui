<?php

namespace BlackParadise\AdminBladeUI\UI\Components;

use BlackParadise\LaravelAdmin\Core\Interfaces\Components\ComponentInterface;

class TableComponent implements ComponentInterface
{
    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }
    
    /**
     * @return string
     */
    public function render(): string
    {
        return view('bpadmin::components.common.table', [
            'items' => $this->items,
        ]);
    }
}
