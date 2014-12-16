<?php
namespace Craft;

class CoalescentFormsModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'id'    => array(AttributeType::Number, 'label' => 'Id'),
            'formType'   => array(AttributeType::String, 'label' => 'Form Type'),
            'dateCreated'   => array(AttributeType::String, 'label' => 'Date'),
            'dateUpdated'   => array(AttributeType::String, 'label' => 'Date'),
            'firstName'   => array(AttributeType::String, 'label' => 'First Name'),
            'lastName'   => array(AttributeType::String, 'label' => 'Last Name'),
            'email'   => array(AttributeType::Email, 'label' => 'Email'),
            'fields'  => array(AttributeType::Mixed, 'label' => 'Fields'),
        );
    }
}