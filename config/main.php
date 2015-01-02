<?php

return array(
    'Form 1' => array(
        'fields' => array(
            'title' => 'Title for email',
            'comments' => 'Comments for email',
        )
    ),
    'Form 2' => array(
        'fields' => array(
            'company' => 'Company',
            'comments' => 'Comments',
        )
    ),
    'Footer Optin Form' => array(
        'fields' => array(
        )
    ),
    'Sidebar Form' => array(
        'fields' => array(
            'phone' => 'Phone',
            'optin' => array('label' => 'Opt-in Checkbox', 'default' => 'No'),
        )
    ),
    'Contact Form' => array(
        'fields' => array(
            'company' => 'Company',
            'phone' => 'Phone',
            'enquiryAbout' => 'My enquiry is about',
            'comments' => 'Comments',
            'optinContact' => array('label' => 'Opt-in Checkbox', 'default' => 'No'),
        )
    ),
);