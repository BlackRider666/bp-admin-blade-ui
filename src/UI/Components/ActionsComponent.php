<?php

namespace BlackParadise\AdminBladeUI\UI\Components;


use BlackParadise\LaravelAdmin\Core\Interfaces\Components\ComponentInterface;

class ActionsComponent implements ComponentInterface
{
    private array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes['label'] = __('bpadmin::common.forms.submit');
        $this->attributes['back-url'] = url()->previous();
        $this->attributes['back-label'] = __('bpadmin::common.forms.back');
        $this->attributes = array_merge($this->attributes,$attributes);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $view =  view('bpadmin::components.common.actions', [
            'attributes' => $this->attributes,
        ]);

        return $view->render();
    }
}
