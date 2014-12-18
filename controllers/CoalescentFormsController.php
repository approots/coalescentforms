<?php
namespace Craft;

/**
 * Contact Form controller
 */
class CoalescentFormsController extends BaseController
{
    /**
     * @var Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = array('actionSaveForm');

    public function actionDeleteForm()
    {
        $this->requirePostRequest();
        $id = craft()->request->getPost('id');
        craft()->coalescentForms->deleteFormById($id);
        $this->returnJson(array('success' => true));
    }

    public function actionDownloadCsv()
    {
        $this->requirePostRequest();

        $csv = array();
        $addedTitles = false;
        $titleLine = 'Date,First Name,Last Name,Email';
        $fieldLine = '';
        $fileName = '';
        $label = null;

        $formName = craft()->request->getPost('formName');
        $formType = craft()->request->getPost('formType');
        $labels = craft()->coalescentForms->getFormFieldLabels($formType);
        $forms = craft()->coalescentForms->getFormsByTypeAndName($formType, $formName);

        if ($forms) {

            foreach ($forms as $form) {
                $fieldLine = $this->_encodeCSVField($form['dateUpdated']);
                $fieldLine .= ',' . $this->_encodeCSVField($form['firstName']);
                $fieldLine .= ',' . $this->_encodeCSVField($form['lastName']);
                $fieldLine .= ',' . $this->_encodeCSVField($form['email']);

                // Extra fields specific to this form.
                foreach ($form['fields'] as $key => $value) {
                    if (! $addedTitles) {
                        // TODO write form field labels instead of keys
                        $label = ($labels[$key]) ? $labels[$key] : $key;
                        $titleLine .= ',' . $label;
                    }
                    $fieldLine .= ',' . $this->_encodeCSVField($value);
                }

                if (! $addedTitles) {
                    $csv[] = $titleLine;
                    $addedTitles = true;
                }

                $csv[] = $fieldLine;
            }

            $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $formName);
            $fileName .= '.csv';

            craft()->request->sendFile($fileName, implode("\n", $csv), array('forceDownload' => true));
        }
    }

    private function _encodeCSVField($string) {
        if(strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
            $string = '"' . str_replace('"', '""', $string) . '"';
        }
        return $string;
    }

    /**
     * Sends an email based on the posted params.
     *
     * @throws Exception
     */
    public function actionSaveForm()
    {
        $this->requirePostRequest();
        $formField = null;
        $formFields = array();
        $customFormFields = array();
        $formModel = null;
        $mainConfig = craft()->coalescentForms->getConfig(); //require dirname(dirname(__FILE__)) . '/config/main.php';
        $formType = craft()->request->getPost('formType');

        // Get the custom fields from the config
        if (isset($mainConfig[$formType])) {
            // Get the standard form fields
            $formFields['formType'] = $formType;
            $formFields['formName'] = craft()->request->getPost('formName');
            if (empty($formFields['formName'])) {
                $formFields['formName'] = $formFields['formType'];
            }
            $formFields['firstName'] = craft()->request->getPost('firstName');
            $formFields['lastName'] = craft()->request->getPost('lastName');
            $formFields['email'] = craft()->request->getPost('email');

            $configFields = $mainConfig[$formType]['fields'];
            foreach($configFields as $key => $value) {
                $formField = craft()->request->getPost($key);

                // Support for default values if no form value sent. Useful for unchecked checkboxes.
                if (empty($formField) && is_array($value) && isset($value['default'])) {
                    $formField = $value['default'];
                }

                // add this to an array
                $customFormFields[$key] = $formField;
            }

            $formFields['fields'] = $customFormFields;
            // create the form model
            $formModel = craft()->coalescentForms->newFormsModel($formFields);
            // save the form

            if (craft()->coalescentForms->saveForm($formModel)) {

                // Send email(s)
                craft()->coalescentForms->sendEmail(
                    $formFields,
                    craft()->request->getPost('emailRecipients'),
                    craft()->request->getPost('emailSubject')
                );

                // Success message can be printed (or checked for) in the template:
                craft()->userSession->setFlash('notice', Craft::t('The form was submitted successfully.'));

                $this->redirectToPostedUrl();
            } else {
                // failure // Post back with flash error which can be printed (or checked for) in the template:
                craft()->userSession->setFlash('error', Craft::t('There was an error saving the form.'));
            }
        } else {
            // Post back with flash error which can be printed (or checked for) in the template:
            craft()->userSession->setFlash('error', Craft::t('Form type not supported.'));
        }
    }
}
