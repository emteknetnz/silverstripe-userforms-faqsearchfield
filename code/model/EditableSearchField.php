<?php
/**
 * EditableSearchField used to execute a search based on user input,
 * can be configured to use custom javascript to display results
 *
 * @package userforms
 */
class EditableSearchField extends EditableFormField
{
    /**
     * @var string
     */
    private static $singular_name = 'Search Field';

    /**
     * @var string
     */
    private static $plural_name = 'Search Fields';

    /**
     * @var array
     */
    private static $db = [
        'Placeholder' => 'Varchar(255)'
    ];

    /**
     * @return Fieldlist
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldToTab(
                'Root.Main',
                TextField::create(
                    'Placeholder',
                    _t('EditableSearchField.PLACEHOLDER', 'Placeholder')
                )
            );
        });

        return parent::getCMSFields();
    }

    /**
     * @return TextareaField|TextField
     */
    public function getFormField()
    {
        // add in custom javasctript to handle serachfield interaction
        Requirements::javascript('userforms-faqsearchfield/javascript/dist/UserFormSearchField.js');

        $field = TextField::create($this->Name, $this->EscapedTitle, $this->Default)
            ->setFieldHolderTemplate('UserFormsField_holder')
            ->setTemplate('UserFormsSearchField');

        $this->doUpdateFormField($field);

        return $field;
    }

    /**
     * Updates a formfield with the additional metadata specified by this field
     *
     * @param FormField $field
     */
    protected function updateFormField($field)
    {
        parent::updateFormField($field);

        if ($this->Placeholder) {
            $field->setAttribute('placeholder', $this->Placeholder);
        }
    }
}
