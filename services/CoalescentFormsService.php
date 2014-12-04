<?php
namespace Craft;

class CoalescentFormsService extends BaseApplicationComponent
{
    protected $coalescentFormsRecord;

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

    public function getFormTypes()
    {
        $formType = null;
        $formTypes = array();

        $records = $this->coalescentFormsRecord->findAll(array(
            'select'=>'t.formType',
            'group'=>'t.formType',
            'distinct'=>true,
            'order'=>'t.formType'
        ));

        foreach($records as $record) {
            $formTypes[] = $record["formType"];
        }

        return $formTypes;
    }

    public function getAllForms()
    {
        $records = $this->coalescentFormsRecord->findAll(array('order'=>'t.dateUpdated DESC, t.formType'));
        return CoalescentFormsModel::populateModels($records);
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
        }
        if ($settings->toEmail) {
            $emailRecipients = array_merge(ArrayHelper::stringToArray($settings->toEmail), $emailRecipients);
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
        $mainConfig = require dirname(dirname(__FILE__)) . '/config/main.php';
        $configFields = $mainConfig[$formFields['formType']]['fields'];
        $message = '';
        $formsModel = $this->newFormsModel($formFields);
        $labels = $formsModel->attributeLabels();
        $label = '';

        foreach ($formFields as $key => $value) {
            if ($key === 'fields') {
                foreach ($value as $extraKey => $extraValue) {
                    if ($configFields[$extraKey]) {
                        $extraKey = $configFields[$extraKey];
                    }
                    $message .= $extraKey . ': ' . $extraValue . "\n\n";
                }
            } else {
                $label = ($labels[$key]) ? $labels[$key] : $key;
                $message .= $label . ': ' . $value . "\n\n";
            }
        }

        return $message;
    }
}