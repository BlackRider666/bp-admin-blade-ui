<?php

namespace BlackParadise\AdminBladeUI\UI\Builders\FormBuilder;



use BlackParadise\AdminBladeUI\UI\Builders\FormBuilder\Inputs\{BelongsToInput,
    BooleanInput,
    EditorInput,
    EmailInput,
    FileInput,
    FloatInput,
    HiddenInput,
    IntegerInput,
    PasswordInput,
    StringInput,
    SubmitInput,
    TextInput,
    TranslatableEditorInput,
    TranslatableInput};
use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\FormBuilder\FormInterface;
use BlackParadise\LaravelAdmin\Core\Interfaces\Builders\FormBuilder\Inputs\InputInterface;
use BlackParadise\LaravelAdmin\Core\Models\BPModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ViewErrorBag;

class Form implements FormInterface
{
    private array $availableTypes = [
        'boolean'   =>  BooleanInput::class,
        'email'     =>  EmailInput::class,
        'float'     =>  FloatInput::class,
        'integer'   =>  IntegerInput::class,
        'string'    =>  StringInput::class,
        'text'      =>  TextInput::class,
        'hashed'  =>  PasswordInput::class,
        'submit'    =>  SubmitInput::class,
        'BelongsTo' =>  BelongsToInput::class,
        'BelongsToMany' =>  BelongsToInput::class,
        //date
        //phone
        'file'          =>  FileInput::class,
        'translatable'  =>  TranslatableInput::class,
        'translatableEditor'  =>  TranslatableEditorInput::class,
        'editor'        =>  EditorInput::class,
    ];
    private array $attributes = [
        'justify'   =>  'center',
        'align'     =>  'center',
        'enctype'   =>  'multipart/form-data'
    ];
    public array $fields = [];
    private string $entityName;
    private ?Model $model;

    public function __construct(array $attributes, Model $model = null, BPModel $BPModel = null)
    {
        $this->attributes = array_merge($this->attributes,$attributes);
        $this->entityName = $BPModel? $BPModel->name:'search';
        if ($BPModel) {
            if (!$model->exists) {
                $fields = $BPModel->getFields();
            } else {
                $fields =  $BPModel->getFieldsWithoutHidden();
            }
            $errors = session()->get('errors', app(ViewErrorBag::class))->messages();
            foreach ($fields as $key => $value)
            {
                if (substr($key, -6) === 'method' && $value) {
                    $modelMethod = substr($key, 0,-7);
                    $valueModel = $model->$modelMethod()->allRelatedIds()->toArray();
                } else {
                    if (!in_array($value['type'],['translatable','translatableEditor'])) {
                        $valueModel = $model->$key;
                    } else {
                        $valueModel = $model->getTranslations($key);
                    }
                }
                if (in_array($value['type'],['BelongsTo', 'BelongsToMany'])) {
                    $modelMethod = $value['method'];
                    $modelRel = $model->$modelMethod()->getRelated();
                    $items = method_exists($modelRel,'forSelect') ?
                        $modelRel->forSelect()
                        :
                        $modelRel->pluck('name', 'id');
                } else {
                    $items = null;
                }

                $attrUpdated = [
                    'name' => $key,
                    'value' => $valueModel,
                    'model_id' => $model->getKey(),
                    'items' => $items,
                    'key_model' => $BPModel::$key,
                ];

                if (!$valueModel) {
                    unset($attrUpdated['value']);
                }

                $attrField = array_merge($value,$attrUpdated);
                $attrErrors = array_key_exists($key,$errors)?$errors[$key]:[];
                if (array_key_exists($value['type'], $this->availableTypes)) {
                    if($value['type'] === 'file') {
                        $attrField['path'] = $BPModel->filePath;
                    }

                    $this->addField(new ($this->availableTypes[$value['type']])(
                        $attrField,
                        $this->entityName,
                        $attrErrors,
                    ));
                } else {
                    $this->addField(new $this->availableTypes['string']($attrField,$this->entityName, $attrErrors));
                }
            }
        }
        $this->model = $model;
    }

    /**
     * @param InputInterface $field
     */
    public function addField(InputInterface $field): void
    {
        $this->fields[] = $field;
    }

    public function addFieldToStart(InputInterface $field): void
    {
        array_unshift($this->fields,$field);
    }
    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $attributes
     */
    public function addAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes,$attributes);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $attributes = $this->attributes;
        if (!in_array($attributes['method'],['POST','GET'])) {
            $this->addFieldToStart((new HiddenInput(['name' => '_method', 'value' => $attributes['method']])));
            $attributes['method'] = 'POST';
        }

        return view('bpadmin::components.form',[
            'attributes' => $attributes,
            'fields'     => $this->fields,
            'submitBtn'  => (new SubmitInput(['label' => $this->attributes['submit_label']]))->render(),
        ])->render();
    }


    /**
     * @return string
     */
    public function renderCreateForm(): string
    {
        $createAttribute = [
            'method' => 'POST',
            'action' => route('bpadmin.'.$this->entityName.'.store'),
            'submit_label' => trans('bpadmin::common.forms.create'),
        ];
        $this->attributes = array_merge($this->attributes,$createAttribute);

        return $this->render();
    }

    /**
     * @return string
     */
    public function renderEditForm(): string
    {
        $createAttribute = [
            'method' => 'PUT',
            'action' => route('bpadmin.'.$this->entityName.'.update', $this->model->getKey()),
            'submit_label' => trans('bpadmin::common.forms.update'),
        ];
        $this->attributes = array_merge($this->attributes,$createAttribute);

        return $this->render();
    }

    /**
     * @param $model
     * @return array
     */
    public function getRules($model = null): array
    {
        $fields = collect($this->fields);
        return $fields->map(static function($item) use ($model){
            if (!in_array($item->getType(),['translatable','translatableEditor'])) {
                $rules = $item->getRules();
                if (in_array('file',$rules) && in_array('required', $rules) && $model) {
                    $rules = array_map(static function ($rule) {
                        if ($rule !== 'required') {
                            return $rule;
                        }
                    },$rules);
                    $rules = array_filter($rules);
                }
                return [$item->getName() => $rules];
            }
            return [
                $item->getName() => 'array',
                $item->getName().'.*' => $item->getRules(),
            ];
        })->collapse()->toArray();
    }
}
