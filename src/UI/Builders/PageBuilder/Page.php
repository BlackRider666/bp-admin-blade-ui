<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\PageBuilder;

use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\PageBuilder\PageInterface;
use Illuminate\View\View;

class Page implements PageInterface
{
    private string $title;

    private array $components;

    private array $headers;
    private string $layout;

    public function __construct(string $layout, string $title, array $components, array $headers = [])
    {
        $this->layout = $layout;
        $this->title = $title;
        $this->components = $components;
        $this->headers = $headers;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        $html = implode('', $this->components);
        $headers = implode('',$this->headers);

        return view('pages.page',[
            'layout' => $this->layout,
            'title' => $this->title,
            'headers' => $headers,
            'html' => $html,
        ]);
    }

    /**
     * @param string $component
     */
    public function addComponent(string $component): void
    {
        $this->components[] = $component;
    }
}
