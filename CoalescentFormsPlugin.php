<?php
namespace Craft;

class CoalescentFormsPlugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('Forms');
    }

    function getVersion()
    {
        return '0.1';
    }

    function getDeveloper()
    {
        return 'Coalescent Design';
    }

    function getDeveloperUrl()
    {
        return 'http://coalescentdesign.com';
    }

    public function hasCpSection()
    {
        return true;
    }

    protected function defineSettings()
    {
        return array(
            'toEmail' => array(AttributeType::String, 'required' => false),
            'subject' => array(AttributeType::String, 'required' => false),
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('coalescentforms/_settings', array(
            'settings' => $this->getSettings()
        ));
    }
}
