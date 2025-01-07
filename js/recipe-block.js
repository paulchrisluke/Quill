const { registerBlockType } = wp.blocks;
const { Button, TextControl, TextareaControl, PanelBody } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { __ } = wp.i18n;
const { select } = wp.data;
const { createElement } = wp.element;

// Helper function to convert hours and minutes to ISO 8601 duration
function toISO8601Duration(hours, minutes) {
    let duration = 'PT';
    if (hours > 0) duration += hours + 'H';
    if (minutes > 0) duration += minutes + 'M';
    return duration;
}

// Helper function to parse ISO 8601 duration to hours and minutes
function parseISO8601Duration(duration) {
    const hours = duration.match(/(\d+)H/);
    const minutes = duration.match(/(\d+)M/);
    return {
        hours: hours ? parseInt(hours[1]) : 0,
        minutes: minutes ? parseInt(minutes[1]) : 0
    };
}

// Helper function to convert ISO duration to human readable format in text
function convertISODurationsToHumanReadable(text) {
    return text.replace(/PT(\d+)H(\d+)M|PT(\d+)H|PT(\d+)M/g, (match, hours, minutes, hoursOnly, minutesOnly) => {
        if (hours && minutes) {
            return `${hours} hour${hours === '1' ? '' : 's'} ${minutes} minute${minutes === '1' ? '' : 's'}`;
        } else if (hoursOnly) {
            return `${hoursOnly} hour${hoursOnly === '1' ? '' : 's'}`;
        } else if (minutesOnly) {
            return `${minutesOnly} minute${minutesOnly === '1' ? '' : 's'}`;
        }
        return match;
    });
}

registerBlockType('quill/recipe', {
    title: 'Recipe Generator',
    icon: 'food',
    category: 'common',
    attributes: {
        ingredients: {
            type: 'string',
            default: ''
        },
        instructions: {
            type: 'string',
            default: ''
        },
        prepTimeHours: {
            type: 'string',
            default: ''
        },
        prepTimeMinutes: {
            type: 'string',
            default: ''
        },
        cookTimeHours: {
            type: 'string',
            default: ''
        },
        cookTimeMinutes: {
            type: 'string',
            default: ''
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        
        function generateRecipe() {
            const content = select('core/editor').getEditedPostContent();
            console.log('Post content to analyze:', content);
            
            setAttributes({
                ingredients: 'Generating recipe...',
                instructions: 'Please wait...',
                prepTimeHours: '',
                prepTimeMinutes: '',
                cookTimeHours: '',
                cookTimeMinutes: ''
            });
            
            jQuery.ajax({
                url: quillData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quill_generate_recipe',
                    nonce: quillData.nonce,
                    content: content
                },
                timeout: 65000,
                success: function(response) {
                    if (response.success && response.data) {
                        const prepTime = parseISO8601Duration(response.data.prep_time);
                        const cookTime = parseISO8601Duration(response.data.cook_time);
                        const humanReadableInstructions = convertISODurationsToHumanReadable(response.data.instructions || '');
                        
                        setAttributes({
                            ingredients: response.data.ingredients || '',
                            instructions: humanReadableInstructions,
                            prepTimeHours: prepTime.hours.toString(),
                            prepTimeMinutes: prepTime.minutes.toString(),
                            cookTimeHours: cookTime.hours.toString(),
                            cookTimeMinutes: cookTime.minutes.toString()
                        });
                    } else {
                        setAttributes({
                            ingredients: 'Error: ' + (response.data || 'Failed to generate recipe'),
                            instructions: '',
                            prepTimeHours: '',
                            prepTimeMinutes: '',
                            cookTimeHours: '',
                            cookTimeMinutes: ''
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let errorMessage = textStatus === 'timeout' ? 
                        'Request timed out. Please try again.' : 
                        'Failed to generate recipe';
                    
                    setAttributes({
                        ingredients: 'Error: ' + errorMessage,
                        instructions: '',
                        prepTimeHours: '',
                        prepTimeMinutes: '',
                        cookTimeHours: '',
                        cookTimeMinutes: ''
                    });
                }
            });
        }

        return [
            createElement(Button, {
                isPrimary: true,
                onClick: generateRecipe,
                style: { marginBottom: '1em' }
            }, 'Generate Recipe from Content'),
            createElement('div', {}, [
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Prep Time'),
                    createElement(TextControl, {
                        type: 'number',
                        label: 'Hours',
                        value: attributes.prepTimeHours,
                        onChange: (value) => setAttributes({ prepTimeHours: value })
                    }),
                    createElement(TextControl, {
                        type: 'number',
                        label: 'Minutes',
                        value: attributes.prepTimeMinutes,
                        onChange: (value) => setAttributes({ prepTimeMinutes: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Cook Time'),
                    createElement(TextControl, {
                        type: 'number',
                        label: 'Hours',
                        value: attributes.cookTimeHours,
                        onChange: (value) => setAttributes({ cookTimeHours: value })
                    }),
                    createElement(TextControl, {
                        type: 'number',
                        label: 'Minutes',
                        value: attributes.cookTimeMinutes,
                        onChange: (value) => setAttributes({ cookTimeMinutes: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Ingredients'),
                    createElement(TextareaControl, {
                        value: attributes.ingredients,
                        onChange: (value) => setAttributes({ ingredients: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Instructions'),
                    createElement(TextareaControl, {
                        value: attributes.instructions,
                        onChange: (value) => setAttributes({ instructions: value })
                    })
                ])
            ])
        ];
    },

    save: function(props) {
        const { attributes } = props;
        
        const prepTimeDisplay = [];
        if (attributes.prepTimeHours && parseInt(attributes.prepTimeHours) > 0) {
            prepTimeDisplay.push(attributes.prepTimeHours + ' hour' + (attributes.prepTimeHours === '1' ? '' : 's'));
        }
        if (attributes.prepTimeMinutes && parseInt(attributes.prepTimeMinutes) > 0) {
            prepTimeDisplay.push(attributes.prepTimeMinutes + ' minute' + (attributes.prepTimeMinutes === '1' ? '' : 's'));
        }
        
        const cookTimeDisplay = [];
        if (attributes.cookTimeHours && parseInt(attributes.cookTimeHours) > 0) {
            cookTimeDisplay.push(attributes.cookTimeHours + ' hour' + (attributes.cookTimeHours === '1' ? '' : 's'));
        }
        if (attributes.cookTimeMinutes && parseInt(attributes.cookTimeMinutes) > 0) {
            cookTimeDisplay.push(attributes.cookTimeMinutes + ' minute' + (attributes.cookTimeMinutes === '1' ? '' : 's'));
        }
        
        return createElement('div', {}, [
            prepTimeDisplay.length > 0 && createElement('p', {}, ['Prep Time: ', prepTimeDisplay.join(' ')]),
            cookTimeDisplay.length > 0 && createElement('p', {}, ['Cook Time: ', cookTimeDisplay.join(' ')]),
            attributes.ingredients && createElement('div', {}, [
                createElement('h3', {}, 'Ingredients'),
                createElement('ul', {},
                    attributes.ingredients.split('\n').map(
                        (ingredient) => createElement('li', {}, ingredient)
                    )
                )
            ]),
            attributes.instructions && createElement('div', {}, [
                createElement('h3', {}, 'Instructions'),
                createElement('ol', {},
                    attributes.instructions.split('\n').map(
                        (instruction) => createElement('li', {}, instruction)
                    )
                )
            ])
        ]);
    }
}); 