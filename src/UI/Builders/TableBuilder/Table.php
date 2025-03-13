<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\TableBuilder;

use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\TableBuilder\TableInterface;

class Table implements TableInterface
{
    private array $headers;
    private array $items;
    private bool $searchable;
    private array $routes;

    public function __construct(array $headers, array $items, string $name, bool $searchable, array $routes = [])
    {
        $this->headers = array_map( static function($header) use ($name) {
            $item['key'] = $header;
            $item['title'] = trans('bpadmin::'.$name.'.'.$header);
            $item['sortable'] = $header !== 'actions';
            return $item;
        },$headers);
        $this->items = $items;
        $this->searchable = $searchable;
        $this->routes = $routes;
    }

    public function render(array $options = []): string
    {
        return view('bpadmin::components.common.crud-table', [
            'headers'   =>  $this->headers,
            'items' => $this->items,
            'routes'            =>  $this->routes,
            'searchable'        =>  $this->searchable,
        ])->render();
    }
}
