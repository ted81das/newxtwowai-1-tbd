"use strict"; 
const { registerBlockType: customRegisterBlockType19 } = wp.blocks;
const mygcel15 = wp.element.createElement;

customRegisterBlockType19( 'aiomatic-automatic-ai-content-writer/aiomatic-ideogram-image-generator', {
    title: 'AIomatic Ideogram Image Generator Form',
    icon: 'text',
    category: 'embed',
    attributes: {
        image_size : {
            default: '1024x1024',
            type:   'string',
        },
        user_token_cap_per_day : {
            default: '',
            type:   'string',
        },
        prompt_templates : {
            default: '',
            type:   'string',
        },
        prompt_editable : {
            default: '',
            type:   'string',
        }
    },
    keywords: ['list', 'posts', 'aiomatic'],
    edit: (function( props ) {
        var image_size = props.attributes.image_size;
        var user_token_cap_per_day = props.attributes.user_token_cap_per_day;
        var prompt_templates = props.attributes.prompt_templates;
        var prompt_editable = props.attributes.prompt_editable;
        function updateMessage( event ) {
            props.setAttributes( { image_size: event.target.value} );
		}
        function updateMessage6( event ) {
            props.setAttributes( { user_token_cap_per_day: event.target.value} );
		}
        function updateMessage7( event ) {
            props.setAttributes( { prompt_templates: event.target.value} );
		}
        function updateMessage8( event ) {
            props.setAttributes( { prompt_editable: event.target.value} );
		}
		return mygcel15(
			'div', 
			{ className: 'coderevolution_gutenberg_div' },
            mygcel15(
				'h4',
				{ className: 'coderevolution_gutenberg_title' },
                'AIomatic Ideogram Image Generator Form ',
                mygcel15(
                    'div', 
                    {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                    ,
                    mygcel15(
                        'div', 
                        {className:'bws_hidden_help_text'},
                        'This block is used to generate AI images.'
                    )
                )
			),
            mygcel15(
				'br'
			),
            mygcel15(
				'br'
			),
            mygcel15(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Image Size: '
			),
            mygcel15(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel15(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the image size for AI generated images.'
                )
            ),
            mygcel15(
				'select',
				{ value: image_size, onChange: updateMessage, className: 'coderevolution_gutenberg_select' }, 
                mygcel15(
                    'option',
                    { value: 'default'},
                    'default'
                ), 
                mygcel15(
                    'option',
                    { value: '1024x1024'},
                    '1024x1024'
                ), 
                mygcel15(
                    'option',
                    { value: '512x512'},
                    '512x512'
                ), 
                mygcel15(
                    'option',
                    { value: '1024x1792'},
                    '1024x1792'
                ), 
                mygcel15(
                    'option',
                    { value: '1792x1024'},
                    '1792x1024'
                )
            ),
            mygcel15(
				'br'
			),
            mygcel15(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Daily Token Count for Logged In Users: '
			),
            mygcel15(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel15(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the daily token count for logged in users. Users who are not logged in will not be allowed to submit the form. To disable this feature, leave this field blank.'
                )
            ),
			mygcel15(
				'input',
				{ type:'number',min:0,placeholder:'Daily token count for users', value: user_token_cap_per_day, onChange: updateMessage6, className: 'coderevolution_gutenberg_input' }
			),
            mygcel15(
				'br'
			),
            mygcel15(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Prompt Templates (Semicolon Separated): '
			),
            mygcel15(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel15(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Add a semicolon (;) separated list of prompt templates from which the users will be able to select and submit one.'
                )
            ),
			mygcel15(
				'input',
				{ type:'text',placeholder:'Template1;Template2;Template3', value: prompt_templates, onChange: updateMessage7, className: 'coderevolution_gutenberg_input' }
			),
            mygcel15(
				'br'
			),
            mygcel15(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Prompt Editable: '
			),
            mygcel15(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel15(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select wheather the prompt will be editable by users. This is useful when combined with prompt templates from above, when you don\'t want the users to edit the entered template.'
                )
            ),
            mygcel15(
				'select',
				{ value: prompt_editable, onChange: updateMessage8, className: 'coderevolution_gutenberg_select' },
                mygcel15(
                    'option',
                    { value: 'yes'},
                    'yes'
                ), 
                mygcel15(
                    'option',
                    { value: 'no'},
                    'no'
                )
            ),
		);
    }),
    save: (function( props ) {
       return null;
    }),
} );