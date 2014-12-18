<?php
namespace Craft;

class CoalescentFormsService extends BaseApplicationComponent
{
    protected $coalescentFormsRecord;
    private $config;

    public function __construct($coalescentFormsRecord = null)
    {
        $this->coalescentFormsRecord = $coalescentFormsRecord;
        if (is_null($this->coalescentFormsRecord)) {
            $this->coalescentFormsRecord = CoalescentFormsRecord::model();
        }
    }

    public function newFormsModel($attributes = array())
    {
        $model = new CoalescentFormsModel();
        $model->setAttributes($attributes);
        return $model;
    }

    public function getAllForms()
    {
        $records = $this->coalescentFormsRecord->findAll(array('order'=>'t.dateUpdated DESC'));
        return CoalescentFormsModel::populateModels($records);
    }

    public function getConfig()
    {
        if (! $this->config) {
            $this->config = require dirname(dirname(__FILE__)) . '/config/main.php';
        }
        return $this->config;
    }

    public function getFormFieldLabels($formType = null)
    {
        $fieldLabels = array();
        $mainConfig = $this->getConfig();

        if ($formType) {
            // just get labels for this form type
            if (($mainConfig[$formType]) && ($mainConfig[$formType]['fields'])) {
                $fieldLabels = $this->_getFieldLabels($mainConfig[$formType]['fields']);
            }
        } else {
            // get labels indexed by formType
            foreach ($mainConfig as $formType => $values) {
                if (($mainConfig[$formType]) && ($mainConfig[$formType]['fields'])) {
                    $fieldLabels[$formType] = $this->_getFieldLabels($mainConfig[$formType]['fields']);
                }
            }
        }

        return $fieldLabels;
    }

    private function _getFieldLabels(array $fields)
    {
        $fieldLabels = array();

        foreach ($fields as $fieldsKey => $fieldsValue) {
            if (! empty($fieldsValue)) {
                if (is_array($fieldsValue)) {
                    // Try to find and use the label field in the array
                    if ($fieldsValue['label']) {
                        $fieldLabels[$fieldsKey] = $fieldsValue['label'];
                    }
                } else if ($fieldsValue) {
                    // The field value is a string. Use it as the label.
                    $fieldLabels[$fieldsKey] = $fieldsValue;
                }
            }

            // Couldn't find a label for this field, so use the field key.
            if (! isset($fieldLabels[$fieldsKey])) {
                $fieldLabels[$fieldsKey] = $fieldsKey;
            }
        }

        return $fieldLabels;
    }

    public function getFormById($id)
    {
        if ($record = $this->coalescentFormsRecord->findByPk($id)) {
            return CoalescentFormsModel::populateModel($record);
        }
        return null;
    }

    public function getFormsByType($formType)
    {
        $records = $this->coalescentFormsRecord->findAll(array(
            'condition'=>'formType=:formType',
            'params'=>array(':formType'=>$formType),
            'order'=>'t.dateUpdated DESC'
        ));

        if ($records) {
            return CoalescentFormsModel::populateModels($records);
        }

        return null;
    }

    public function getFormsByTypeAndName($formType, $formName)
    {
        $records = $this->coalescentFormsRecord->findAll(array(
            'condition'=>'formType=:formType AND formName=:formName',
            'params'=>array(':formType'=>$formType, ':formName'=>$formName),
            'order'=>'t.dateUpdated DESC'
        ));

        if ($records) {
            return CoalescentFormsModel::populateModels($records);
        }

        return null;
    }

    public function saveForm(CoalescentFormsModel &$model)
    {
        if ($id = $model->getAttribute('id')) {
            if (null === ($record = $this->coalescentFormsRecord->findByPk($id))) {
                throw new Exception(Craft::t('Can\'t find form with ID "{id}"', array('id' => $id)));
            }
        } else {
            $record = $this->coalescentFormsRecord->create();
        }

        // Note: can't use $record->setAttributes() due to possible bug where some fields aren't copied.
        foreach ($model->getAttributes() as $key => $value) {
            $record->setAttribute($key, $value);
        }

        if ($record->save()) {
            // update id on model (for new records)
            $model->setAttribute('id', $record->getAttribute('id'));
            return true;
        } else {
            $model->addErrors($record->getErrors());
            return false;
        }
    }

    public function deleteFormById($id)
    {
        return $this->coalescentFormsRecord->deleteByPk($id);
    }

    public function sendEmail($formFields, $formEmailRecipients, $formEmailSubject)
    {
        $settings = craft()->plugins->getPlugin('coalescentforms')->getSettings();
        $emailRecipients = array();
        $emailSubject = "Form Submission";

        if ($formEmailRecipients) {
            $emailRecipients = ArrayHelper::stringToArray($formEmailRecipients);
        } else if ($settings->toEmail) {
            $emailRecipients = ArrayHelper::stringToArray($settings->toEmail);
        }
        // No duplicate email addresses
        $emailRecipients = array_unique($emailRecipients);

        if ($formEmailSubject) {
            $emailSubject = $formEmailSubject;
        } else if ($settings->subject) {
            $emailSubject = $settings->subject;
        }

        $message = $this->_formattedEmailMessage($formFields);

        foreach ($emailRecipients as $toEmail)
        {
            $email = new EmailModel();
            $emailSettings = craft()->email->getSettings();

            $email->fromEmail = $emailSettings['emailAddress'];
            $email->replyTo   = $emailSettings['emailAddress'];
            $email->sender    = $emailSettings['emailAddress'];
            $email->fromName  = $emailSettings['senderName'];
            $email->toEmail   = $toEmail;
            $email->subject   = $emailSubject;
            $email->body      = $message;

            craft()->email->sendEmail($email);
        }
    }

    private function _formattedEmailMessage($formFields)
    {
        $extraFieldLabels = $this->getFormFieldLabels($formFields['formType']);
        $message = '';
        $formsModel = $this->newFormsModel($formFields);
        $labels = $formsModel->attributeLabels();
        $label = '';

        foreach ($formFields as $key => $value) {
            if ($key === 'fields') {
                foreach ($value as $extraKey => $extraValue) {
                    $extraKey = ($extraFieldLabels[$extraKey]) ? $extraFieldLabels[$extraKey] : $extraKey;
                    $message .= $extraKey . ': ' . $extraValue . "\n\n";
                }
            } elseif ($key !== 'formType') {
                $label = ($labels[$key]) ? $labels[$key] : $key;
                $message .= $label . ': ' . $value . "\n\n";
            }
        }

        return $message;
    }
}