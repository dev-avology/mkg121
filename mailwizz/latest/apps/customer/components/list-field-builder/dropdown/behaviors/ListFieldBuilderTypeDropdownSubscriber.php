<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldBuilderTypeDropdownSubscriber
 *
 * The save action is running inside an active transaction.
 * For fatal errors, an exception must be thrown, otherwise the errors array must be populated.
 * If an exception is thrown, or the errors array is populated, the transaction is rolled back.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

/**
 * @property ListFieldBuilderTypeDropdown $owner
 */
class ListFieldBuilderTypeDropdownSubscriber extends ListFieldBuilderTypeSubscriber
{
    /**
     * @param CEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function _saveFields(CEvent $event)
    {
        $fieldType = $this->owner->getFieldType();
        $typeName  = $fieldType->identifier;

        /** @var ListFieldValue[] $valueModels */
        $valueModels = $this->getValueModels();
        $fields      = [];

        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = [];
        }

        /** run validation so that fields will get the errors if any. */
        foreach ($valueModels as $model) {
            if (!$model->validate()) {
                $this->owner->errors[] = [
                    'show'      => false,
                    'message'   => $model->shortErrors->getAllAsString(),
                ];
            }
            $fields[] = $this->buildFieldArray($model);
        }

        /** make the fields available */
        $event->params['fields'][$typeName] = $fields;

        /** do the actual saving of fields if there are no errors. */
        if (empty($this->owner->errors)) {
            foreach ($valueModels as $model) {
                $model->save(false);
            }
        }
    }

    /**
     * @param CEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function _displayFields(CEvent $event)
    {
        $fieldType   = $this->owner->getFieldType();
        $typeName    = $fieldType->identifier;

        /** fields created in the save action. */
        if (isset($event->params['fields'][$typeName]) && is_array($event->params['fields'][$typeName])) {
            return;
        }

        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = [];
        }

        /** @var ListFieldValue[] $valueModels */
        $valueModels = $this->getValueModels();
        $fields      = [];

        foreach ($valueModels as $model) {
            $fields[] = $this->buildFieldArray($model);
        }

        $event->params['fields'][$typeName] = $fields;
    }

    /**
     * @param CModelEvent $event
     *
     * @return void
     */
    public function _setCorrectLabel(CModelEvent $event)
    {
        $event->params['labels']['value'] = $event->sender->field->label;
    }

    /**
     * @param CModelEvent $event
     *
     * @return void
     */
    public function _setCorrectValidationRules(CModelEvent $event)
    {
        /** @var CList $rules */
        $rules = $event->params['rules'];

        /** clear any other rule we have so far */
        $rules->clear();

        /** start adding new rules. */
        if ($event->sender->field->required === 'yes') {
            $rules->add(['value', 'required']);
        }

        $rules->add(['value', 'length', 'max' => 255]);
    }

    /**
     * @param CModelEvent $event
     *
     * @return void
     */
    public function _setCorrectHelpText(CModelEvent $event)
    {
        $event->params['texts']['value'] = $event->sender->field->help_text;
    }

    /**
     * @return array
     * @throws MaxMind\Db\Reader\InvalidDatabaseException
     */
    protected function getValueModels(): array
    {
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $subscriber = $this->owner->getSubscriber();

        /** @var ListField[] $models */
        $models = ListField::model()->findAllByAttributes([
            'type_id' => (int)$fieldType->type_id,
            'list_id' => (int)$list->list_id,
        ]);

        /** @var ListFieldValue[] $valueModels */
        $valueModels = [];

        foreach ($models as $model) {

            /** @var ListFieldValue|null $valueModel */
            $valueModel = ListFieldValue::model()->findByAttributes([
                'field_id'      => (int)$model->field_id,
                'subscriber_id' => (int)$subscriber->subscriber_id,
            ]);

            if (empty($valueModel)) {
                $valueModel = new ListFieldValue();
            }

            /** setup rules and labels here. */
            $valueModel->onAttributeLabels = [$this, '_setCorrectLabel'];
            $valueModel->onRules = [$this, '_setCorrectValidationRules'];
            $valueModel->onAttributeHelpTexts = [$this, '_setCorrectHelpText'];
            $valueModel->fieldDecorator->onHtmlOptionsSetup = [$this->owner, '_addInputErrorClass'];
            $valueModel->fieldDecorator->onHtmlOptionsSetup = [$this->owner, '_addFieldNameClass'];

            /** set the correct default value. */
            $defaultValue = strlen(trim((string)$valueModel->value)) == 0 ? ListField::parseDefaultValueTags((string)$model->default_value, $subscriber) : $valueModel->value;

            /** assign props */
            $valueModel->field         = $model;
            $valueModel->field_id      = (int)$model->field_id;
            $valueModel->subscriber_id = (int)$subscriber->subscriber_id;
            $valueModel->value         = (string)request()->getPost($model->tag, $defaultValue);

            $valueModels[] = $valueModel;
        }

        return $valueModels;
    }

    /**
     * @param ListFieldValue $model
     *
     * @return array
     */
    protected function buildFieldArray(ListFieldValue $model): array
    {
        /** @var ListField $field */
        $field = $model->field;

        $fieldHtml = '';
        $viewFile  = realpath(dirname(__FILE__) . '/../views/field-display.php');
        $options   = [];

        if ($field->required != 'yes') {
            $options[''] = t('app', 'Please choose');
        }

        if (!empty($field->options)) {

            /** @var ListFieldOption $_options */
            $_options = $field->options;

            foreach ($_options as $option) {
                $options[$option->value] = $option->name;
            }
        }

        if (!$field->getVisibilityIsNone() || apps()->isAppName('customer')) {
            $visible = $field->getVisibilityIsVisible() || apps()->isAppName('customer');
            $fieldHtml = $this->owner->renderInternal($viewFile, compact('model', 'field', 'options', 'visible'), true);
        }

        return (array)hooks()->applyFilters('list_field_builder_type_dropdown_subscriber_build_field_array', [
            'sort_order' => (int)$field->sort_order,
            'field_html' => $fieldHtml,
        ], $model, $field, $options);
    }
}
