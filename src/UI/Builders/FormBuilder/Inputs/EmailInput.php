<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\FormBuilder\Inputs;

class EmailInput extends StringInput
{
    public function __construct(array $attributes, string $entity, array $errors, array $rules = [])
    {
        $attributes['type'] = 'email';
        $unique = 'unique:' . $entity;
        if (array_key_exists('value',$attributes)) {
            $unique .=','.$attributes['name'].','.$attributes['model_id'] .','. $attributes['key_model'];
        }
        $rules = !empty($rules)? $rules : [
            'front' => ['max:255'],
            'back'  => ['string', 'email', 'max:255', $unique],
        ];
        parent::__construct($attributes,$entity,$errors, $rules);
    }
}
