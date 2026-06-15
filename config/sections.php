<?php

return [

    'hero' => [

        'label' => 'Hero Section',
        'fields' => [
            [
                'key'   => 'title',
                'type'  => 'text',
                'label' => 'Main Title',
                'default' => 'Flip Smarter with your <br> all in one platform <span style="color: #e6005c;">Fliply.</span>',
                'required' => true,
            ],

            [
                'key'   => 'description',
                'type'  => 'textarea',
                'label' => 'Description',
                'default' => 'Work smarter, not harder. Let Fliply do the heavy lifting.',
                'required' => true,
            ],
            [
                'key'   => 'button_text',
                'type'  => 'text',
                'label' => 'Button Text',
                'default' => 'Get started free',
                'required' => true,
            ],
            [
                'key'   => 'button_link',
                'type'  => 'text',
                'label' => 'Button Link',
                'default' => '#',
                'required' => true,
            ],
            [
                'key'   => 'video_button_text',
                'type'  => 'text',
                'label' => 'Video Button Text',
                'default' => 'See how it works',
                'required' => true,
            ],
            [
                'key'   => 'video_button_link',
                'type'  => 'text',
                'label' => 'Video Button Link',
                'default' => '#',
                'required' => true,
            ],
            [
                'key'   => 'stat_1_number',
                'type'  => 'text',
                'label' => 'Stat 1 Number',
                'default' => '8',
                'required' => true,
            ],
            [
                'key'   => 'stat_1_text',
                'type'  => 'text',
                'label' => 'Stat 1 Text',
                'default' => 'Properties Flipped',
            ],
            [
                'key'   => 'stat_2_number',
                'type'  => 'text',
                'label' => 'Stat 2 Number',
                'default' => '$0',
                'required' => true,
            ],
            [
                'key'   => 'stat_2_text',
                'type'  => 'text',
                'label' => 'Stat 2 Text',
                'default' => 'Added to Profit',
                'required' => true,
            ],
            [
                'key'   => 'image',
                'type'  => 'image',
                'label' => 'Hero Image',
                'default' => 'https://via.placeholder.com/600x400/002147/ffffff?text=Hero+Image',
                'required' => true,
            ],
            [
                'key'   => 'submenu_items',
                'type'  => 'repeater',
                'label' => 'Sub Menu Items',
                'fields' => [
                    [
                        'key'   => 'item_text',
                        'type'  => 'text',
                        'label' => 'Item Text',
                        'required' => true,
                    ],
                ]
            ],
        ]
    ],
    
    'pricing' => [
        'label' => 'Pricing Section',
        'fields' => [
             [
                'key'   => 'subtitle',
                'type'  => 'text',
                'label' => 'Section Subtitle',
                'default' => 'Simple pricing',
            ],
            [
                'key'   => 'title',
                'type'  => 'text',
                'label' => 'Section Title',
                'default' => 'Start free, grow when ready.',
            ],
            [
                'key'   => 'description',
                'type'  => 'text',
                'label' => 'Section Description',
                'default' => '14 day trial. Credit card required. No charges during trial. Cancel anytime. All prices AUD + GST.',
            ],
           
        ]
    ],

    'blogs' => [
        'label' => 'Blogs Section',
        'fields' => [
             [
                'key'   => 'subtitle',
                'type'  => 'text',
                'label' => 'Section Subtitle',
                'default' => 'Blogs',
            ],
            [
                'key'   => 'title',
                'type'  => 'text',
                'label' => 'Section Title',
                'default' => 'Fliply Blogs.',
            ],
            [
                'key'   => 'description',
                'type'  => 'text',
                'label' => 'Section Description',
                'default' => 'Stay updated with the latest trends, tutorials, and development guides.',
            ],
            [
                'key'   => 'post_per_page',
                'type'  => 'text',
                'label' => 'Posts Per Page',
                'default' => '5',
            ],
            [
                'key'   => 'post_order',
                'type'  => 'text',
                'label' => 'Posts Order',
                'default' => 'desc',
            ],
        ]
    ],

    'demo_all_fields' => [

    'label' => 'Demo All Fields Section',

    'fields' => [

        /*
        |--------------------------------------------------------------------------
        | Text
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'text_field',
            'type' => 'text',
            'label' => 'Text Field',
            'default' => 'This is text field',
            'required' => true,
            'placeholder' => 'Enter text',
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Textarea
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'textarea_field',
            'type' => 'textarea',
            'label' => 'Textarea Field',
            'default' => 'This is textarea content',
            'required' => true,
            'class' => 'col-md-12',
        ],

        /*
        |--------------------------------------------------------------------------
        | Number
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'number_field',
            'type' => 'number',
            'label' => 'Number Field',
            'default' => '10',
            'required' => true,
            'class' => 'col-md-4',
        ],

        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'email_field',
            'type' => 'email',
            'label' => 'Email Field',
            'default' => 'demo@example.com',
            'required' => true,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Link / URL
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'link_field',
            'type' => 'link',
            'label' => 'Link Field',
            'default' => 'https://google.com',
            'required' => false,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Select
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'select_field',
            'type' => 'select',
            'label' => 'Select Field',

            'options' => [

                [
                    'label' => 'Option One',
                    'value' => 'option_1',
                ],

                [
                    'label' => 'Option Two',
                    'value' => 'option_2',
                ],

                [
                    'label' => 'Option Three',
                    'value' => 'option_3',
                ],

            ],

            'default' => 'option_2',
            'required' => true,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Radio
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'radio_field',
            'type' => 'radio',
            'label' => 'Radio Field',

            'options' => [

                [
                    'label' => 'Left',
                    'value' => 'left',
                ],

                [
                    'label' => 'Center',
                    'value' => 'center',
                ],

                [
                    'label' => 'Right',
                    'value' => 'right',
                ],

            ],

            'default' => 'center',
            'required' => true,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Checkbox
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'checkbox_field',
            'type' => 'checkbox',
            'label' => 'Checkbox Field',

            'options' => [

                [
                    'label' => 'Search',
                    'value' => 'search',
                ],

                [
                    'label' => 'Analytics',
                    'value' => 'analytics',
                ],

                [
                    'label' => 'CRM',
                    'value' => 'crm',
                ],

            ],

            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'image_field',
            'type' => 'image',
            'label' => 'Image Upload',
            'required' => false,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | File
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'file_field',
            'type' => 'file',
            'label' => 'File Upload',
            'required' => false,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Video
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'video_field',
            'type' => 'video',
            'label' => 'Video Upload',
            'required' => false,
            'class' => 'col-md-6',
        ],

        /*
        |--------------------------------------------------------------------------
        | Repeater
        |--------------------------------------------------------------------------
        */

        [
            'key' => 'repeater_field',
            'type' => 'repeater',
            'label' => 'Repeater Example',

            'fields' => [

                [
                    'key' => 'title',
                    'type' => 'text',
                    'label' => 'Title',
                    'required' => true,
                ],

                [
                    'key' => 'description',
                    'type' => 'textarea',
                    'label' => 'Description',
                ],

                [
                    'key' => 'image',
                    'type' => 'image',
                    'label' => 'Image',
                ],

                [
                    'key' => 'file',
                    'type' => 'file',
                    'label' => 'File',
                ],

                [
                    'key' => 'video',
                    'type' => 'video',
                    'label' => 'Video',
                ],

                [
                    'key' => 'button_link',
                    'type' => 'link',
                    'label' => 'Button Link',
                ],

                [
                    'key' => 'theme',
                    'type' => 'select',
                    'label' => 'Theme',

                    'options' => [

                        [
                            'label' => 'Light',
                            'value' => 'light',
                        ],

                        [
                            'label' => 'Dark',
                            'value' => 'dark',
                        ],

                    ],

                ],

            ],

            'class' => 'col-md-12',
        ],

    ],

    ],

];
