<?php
namespace Craft;

class CoalescentFormsVariable
{
    public function getForms()
    {
        // form name, form id?
        return craft()->coalescentForms->getAllForms();
    }

    public function getFormConfig()
    {
        // Form field labels by field key, label value all indexed by formType
        return craft()->coalescentForms->getConfig();
    }

    public function getFormTypes()
    {
        return craft()->coalescentForms->getFormTypes();
    }
/*
    public function getFormsData()
    {
        return array(
            array('firstName' => 'Rick "Jolly"', 'lastName' => "Jolly's", 'fields' => array(array('key' => 'sport','value' => 'mountain biking'), array('key' => 'food','value' => 'mexican'))),
            array('firstName' => 'Mike', 'lastName' => 'Cassie', 'fields' => array(array('key' => 'sport','value' => 'running'), array('key' => 'food','value' => 'curry')))
        );
    }
*/
}