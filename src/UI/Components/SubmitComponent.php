<?php

namespace BlackParadise\AdminBladeUI\UI\Components;

use BlackParadise\LaravelAdmin\Core\Interfaces\Components\ComponentInterface;

class SubmitComponent implements ComponentInterface
{
    private array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes['label'] = trans('bpadmin::common.forms.submit');
        unset($attributes['name']);
        $this->attributes = array_merge($this->attributes,$attributes);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $view =  view('bpadmin::components.common.submit', [
            'attributes' => $this->attributes,
        ]);

        return $view->render();
    }
}
