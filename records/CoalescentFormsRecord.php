<?php
namespace Craft;

class CoalescentFormsRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'coalescent_forms';
    }
    /**
     * Define columns for our database table. Id is added by Yii
     *
     * @return array
     */
    public function defineAttributes()
    {
        return array(
            'formType'   => array(AttributeType::String, 'required' => true),
            'firstName'   => array(AttributeType::String),
            'lastName'   => array(AttributeType::String),
            'email'   => array(AttributeType::Email),
            'fields'  => array(AttributeType::Mixed),
        );
    }
    /**
     * Create a new instance of the current class. This allows us to
     * properly unit test our service layer.
     *
     * @return BaseRecord
     */
    public function create()
    {
        $class = get_class($this);
        $record = new $class();
        return $record;
    }
}