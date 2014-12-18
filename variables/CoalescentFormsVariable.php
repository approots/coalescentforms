<?php
namespace Craft;

class CoalescentFormsVariable
{
    public function getForms()
    {
        return craft()->coalescentForms->getAllForms();
    }

    public function getFormFieldLabels()
    {
        // Form field labels by field key, label value all indexed by formType
        return craft()->coalescentForms->getFormFieldLabels();
    }
}