const { registerBlockType } = wp.blocks;
const { Button, TextControl, TextareaControl, PanelBody } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { __ } = wp.i18n;
const { select } = wp.data;
const { createElement } = wp.element;

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
        prep_time: {
            type: 'string',
            default: ''
        },
        cook_time: {
            type: 'string',
            default: ''
        },
        total_time: {
            type: 'string',
            default: ''
        },
        yield: {
            type: 'string',
            default: ''
        },
        calories: {
            type: 'string',
            default: ''
        },
        cuisine: {
            type: 'string',
            default: ''
        },
        category: {
            type: 'string',
            default: ''
        },
        keywords: {
            type: 'array',
            default: []
        },
        notes: {
            type: 'array',
            default: []
        },
        equipment: {
            type: 'array',
            default: []
        },
        difficulty: {
            type: 'string',
            default: ''
        },
        nutrition: {
            type: 'object',
            default: {
                servingSize: '',
                servings: '',
                calories: '',
                fatContent: '',
                saturatedFatContent: '',
                cholesterolContent: '',
                sodiumContent: '',
                carbohydrateContent: '',
                fiberContent: '',
                sugarContent: '',
                proteinContent: '',
                vitaminC: '',
                calciumContent: '',
                ironContent: '',
                vitaminD: '',
                potassiumContent: ''
            }
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        
        function generateRecipe() {
            const content = select('core/editor').getEditedPostContent();
            const postId = select('core/editor').getCurrentPostId();
            
            // Set loading state for all fields
            setAttributes({
                ingredients: '👩‍🍳 Gathering ingredients...',
                instructions: '📝 Crafting the perfect recipe steps...',
                prep_time: '⏲️ Calculating prep time...',
                cook_time: '🔥 Estimating cooking duration...'
            });
            
            jQuery.ajax({
                url: quillData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quill_generate_recipe',
                    nonce: quillData.nonce,
                    content: content,
                    post_id: postId
                },
                timeout: 65000,
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('Recipe data received:', response.data);
                        setAttributes(response.data);
                    } else {
                        setAttributes({
                            ingredients: '❌ ' + (response.data || '🤔 Hmm... our chef needs another try at this recipe!'),
                            instructions: '🔄 Click "Generate Recipe" to try again',
                            prep_time: '',
                            cook_time: ''
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let errorMessage = textStatus === 'timeout' ? 
                        '⏰ Oops! The recipe took too long to cook up. Please try again.' : 
                        '🤔 Something went wrong in the kitchen. Let\'s try again!';
                    
                    setAttributes({
                        ingredients: '❌ ' + errorMessage,
                        instructions: '🔄 Click "Generate Recipe" to try again',
                        prep_time: '',
                        cook_time: ''
                    });
                }
            });
        }

        function organizeRecipe() {
            // Only organize if we have basic recipe data
            if (!attributes.ingredients || !attributes.instructions) {
                alert('Please generate a recipe first!');
                return;
            }

            // Calculate total time from prep and cook time
            let total = 0;
            if (attributes.prep_time) {
                const prepMinutes = parseInt(attributes.prep_time.match(/\d+/)[0]);
                total += prepMinutes;
            }
            if (attributes.cook_time) {
                const cookMinutes = parseInt(attributes.cook_time.match(/\d+/)[0]);
                total += cookMinutes;
            }

            // Set loading states for organization fields
            setAttributes({
                total_time: `PT${total}M`,
                yield: '🍽️ Organizing servings...',
                cuisine: '🌍 Determining cuisine...',
                category: '📑 Categorizing recipe...',
                keywords: ['✨ Finding keywords...'],
                notes: ['📌 Analyzing recipe...'],
                equipment: ['🔪 Listing equipment...'],
                difficulty: '📈 Assessing difficulty...'
            });

            jQuery.ajax({
                url: quillData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quill_organize_recipe',
                    nonce: quillData.nonce,
                    ingredients: attributes.ingredients,
                    instructions: attributes.instructions,
                    prep_time: attributes.prep_time,
                    cook_time: attributes.cook_time,
                    total_time: `PT${total}M`
                },
                timeout: 65000,
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('Recipe organization data received:', response.data);
                        setAttributes(response.data);
                    } else {
                        setAttributes({
                            yield: '',
                            cuisine: '',
                            category: '',
                            keywords: [],
                            notes: [],
                            equipment: [],
                            difficulty: ''
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    setAttributes({
                        yield: '',
                        cuisine: '',
                        category: '',
                        keywords: [],
                        notes: [],
                        equipment: [],
                        difficulty: ''
                    });
                }
            });
        }

        function calculateNutrition() {
            // Only calculate if we have organized recipe data
            if (!attributes.ingredients || !attributes.yield) {
                alert('Please organize the recipe first!');
                return;
            }

            // Set loading states for nutrition fields
            setAttributes({
                nutrition: {
                    servingSize: '🥄 Measuring portions...',
                    servings: attributes.yield,
                    calories: '🔢 Calculating calories...',
                    fatContent: '📊 Measuring fats...',
                    saturatedFatContent: '🥓 Calculating saturated fats...',
                    cholesterolContent: '🍳 Measuring cholesterol...',
                    sodiumContent: '🧂 Checking sodium...',
                    carbohydrateContent: '🍚 Measuring carbs...',
                    fiberContent: '🥬 Calculating fiber...',
                    sugarContent: '🍯 Measuring sugars...',
                    proteinContent: '🥩 Counting proteins...',
                    vitaminC: '🍊 Measuring vitamin C...',
                    calciumContent: '🥛 Calculating calcium...',
                    ironContent: '🥬 Measuring iron...',
                    vitaminD: '☀️ Calculating vitamin D...',
                    potassiumContent: '🍌 Measuring potassium...'
                }
            });

            jQuery.ajax({
                url: quillData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quill_calculate_nutrition',
                    nonce: quillData.nonce,
                    ingredients: attributes.ingredients,
                    servings: attributes.yield
                },
                timeout: 65000,
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('Nutrition data received:', response.data);
                        // Clean nutrition data by stripping units before setting
                        const cleanedNutrition = {};
                        for (const [key, value] of Object.entries(response.data)) {
                            if (typeof value === 'string') {
                                // Extract numeric value from string (e.g., "0g" -> "0")
                                const numericValue = value.replace(/[^0-9.]/g, '');
                                cleanedNutrition[key] = numericValue;
                            } else {
                                cleanedNutrition[key] = value;
                            }
                        }
                        setAttributes({ nutrition: cleanedNutrition });
                    } else {
                        setAttributes({
                            nutrition: {
                                servingSize: '',
                                servings: attributes.yield,
                                calories: '',
                                fatContent: '',
                                saturatedFatContent: '',
                                cholesterolContent: '',
                                sodiumContent: '',
                                carbohydrateContent: '',
                                fiberContent: '',
                                sugarContent: '',
                                proteinContent: '',
                                vitaminC: '',
                                calciumContent: '',
                                ironContent: '',
                                vitaminD: '',
                                potassiumContent: ''
                            }
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    setAttributes({
                        nutrition: {
                            servingSize: '',
                            servings: attributes.yield,
                            calories: '',
                            fatContent: '',
                            saturatedFatContent: '',
                            cholesterolContent: '',
                            sodiumContent: '',
                            carbohydrateContent: '',
                            fiberContent: '',
                            sugarContent: '',
                            proteinContent: '',
                            vitaminC: '',
                            calciumContent: '',
                            ironContent: '',
                            vitaminD: '',
                            potassiumContent: ''
                        }
                    });
                }
            });
        }

        return [
            createElement('div', { style: { display: 'flex', gap: '1em', marginBottom: '1em' } }, [
                createElement(Button, {
                    isPrimary: true,
                    onClick: generateRecipe
                }, '1. Generate Recipe'),
                createElement(Button, {
                    isSecondary: true,
                    onClick: organizeRecipe
                }, '2. Recipe Organizer'),
                createElement(Button, {
                    isSecondary: true,
                    onClick: calculateNutrition
                }, '3. Calculate Nutrition')
            ]),
            createElement('div', {}, [
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Recipe Details'),
                    createElement(TextControl, {
                        label: 'Cuisine Type',
                        value: attributes.cuisine,
                        onChange: (value) => setAttributes({ cuisine: value })
                    }),
                    createElement(TextControl, {
                        label: 'Category',
                        value: attributes.category,
                        onChange: (value) => setAttributes({ category: value })
                    }),
                    createElement(TextControl, {
                        label: 'Difficulty',
                        value: attributes.difficulty,
                        onChange: (value) => setAttributes({ difficulty: value })
                    }),
                    createElement(TextControl, {
                        label: 'Servings',
                        value: attributes.yield,
                        onChange: (value) => setAttributes({ yield: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Prep Time'),
                    createElement(TextControl, {
                        value: attributes.prep_time,
                        onChange: (value) => setAttributes({ prep_time: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Cook Time'),
                    createElement(TextControl, {
                        value: attributes.cook_time,
                        onChange: (value) => setAttributes({ cook_time: value })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Total Time'),
                    createElement(TextControl, {
                        value: attributes.total_time,
                        onChange: (value) => setAttributes({ total_time: value })
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
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Equipment Needed'),
                    createElement(TextareaControl, {
                        value: attributes.equipment.join('\n'),
                        onChange: (value) => setAttributes({ equipment: value.split('\n') })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Recipe Notes'),
                    createElement(TextareaControl, {
                        value: attributes.notes.join('\n'),
                        onChange: (value) => setAttributes({ notes: value.split('\n') })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Keywords'),
                    createElement(TextareaControl, {
                        value: attributes.keywords.join('\n'),
                        onChange: (value) => setAttributes({ keywords: value.split('\n') })
                    })
                ]),
                createElement('div', { style: { marginBottom: '1em' } }, [
                    createElement('h3', {}, 'Nutrition Information (per serving)'),
                    createElement(TextControl, {
                        label: 'Serving Size',
                        value: attributes.nutrition?.servingSize || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, servingSize: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Number of Servings',
                        type: 'number',
                        value: attributes.nutrition?.servings || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, servings: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Calories',
                        type: 'number',
                        value: attributes.nutrition?.calories || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, calories: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Total Fat (g)',
                        type: 'number',
                        value: attributes.nutrition?.fatContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, fatContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Saturated Fat (g)',
                        type: 'number',
                        value: attributes.nutrition?.saturatedFatContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, saturatedFatContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Cholesterol (mg)',
                        type: 'number',
                        value: attributes.nutrition?.cholesterolContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, cholesterolContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Sodium (mg)',
                        type: 'number',
                        value: attributes.nutrition?.sodiumContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, sodiumContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Total Carbohydrates (g)',
                        type: 'number',
                        value: attributes.nutrition?.carbohydrateContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, carbohydrateContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Dietary Fiber (g)',
                        type: 'number',
                        value: attributes.nutrition?.fiberContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, fiberContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Sugars (g)',
                        type: 'number',
                        value: attributes.nutrition?.sugarContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, sugarContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Protein (g)',
                        type: 'number',
                        value: attributes.nutrition?.proteinContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, proteinContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Vitamin C (%)',
                        type: 'number',
                        value: attributes.nutrition?.vitaminC || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, vitaminC: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Calcium (%)',
                        type: 'number',
                        value: attributes.nutrition?.calciumContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, calciumContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Iron (%)',
                        type: 'number',
                        value: attributes.nutrition?.ironContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, ironContent: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Vitamin D (%)',
                        type: 'number',
                        value: attributes.nutrition?.vitaminD || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, vitaminD: value }
                        })
                    }),
                    createElement(TextControl, {
                        label: 'Potassium (mg)',
                        type: 'number',
                        value: attributes.nutrition?.potassiumContent || '',
                        onChange: (value) => setAttributes({ 
                            nutrition: { ...attributes.nutrition, potassiumContent: value }
                        })
                    })
                ])
            ])
        ];
    },

    save: function(props) {
        const { attributes } = props;
        
        // Helper function to format nutrition values
        const formatNutritionValue = (value, unit) => {
            if (!value || value === '') return '-';
            return `${value}${unit}`;
        };

        // Ensure nutrition object exists
        const nutrition = attributes.nutrition || {};
        
        return createElement('div', { className: 'recipe-block' }, [
            attributes.prep_time && createElement('p', { className: 'prep-time' }, ['Prep Time: ', attributes.prep_time]),
            attributes.cook_time && createElement('p', { className: 'cook-time' }, ['Cook Time: ', attributes.cook_time]),
            attributes.total_time && createElement('p', { className: 'total-time' }, ['Total Time: ', attributes.total_time]),
            attributes.yield && createElement('p', { className: 'recipe-yield' }, ['Servings: ', attributes.yield]),
            attributes.cuisine && createElement('p', { className: 'recipe-cuisine' }, ['Cuisine: ', attributes.cuisine]),
            attributes.category && createElement('p', { className: 'recipe-category' }, ['Category: ', attributes.category]),
            attributes.difficulty && createElement('p', { className: 'recipe-difficulty' }, ['Difficulty: ', attributes.difficulty]),
            attributes.ingredients && createElement('div', { className: 'recipe-ingredients' }, [
                createElement('h3', {}, 'Ingredients'),
                createElement('ul', {},
                    attributes.ingredients.split('\n').map(
                        (ingredient) => createElement('li', {}, ingredient)
                    )
                )
            ]),
            attributes.instructions && createElement('div', { className: 'recipe-instructions' }, [
                createElement('h3', {}, 'Instructions'),
                createElement('ol', {},
                    attributes.instructions.split('\n').map(
                        (instruction) => createElement('li', {}, instruction)
                    )
                )
            ]),
            attributes.equipment.length > 0 && createElement('div', { className: 'recipe-equipment' }, [
                createElement('h3', {}, 'Equipment Needed'),
                createElement('ul', {},
                    attributes.equipment.map(
                        (item) => createElement('li', {}, item)
                    )
                )
            ]),
            attributes.notes.length > 0 && createElement('div', { className: 'recipe-notes' }, [
                createElement('h3', {}, 'Recipe Notes'),
                createElement('ul', {},
                    attributes.notes.map(
                        (note) => createElement('li', {}, note)
                    )
                )
            ]),
            attributes.keywords.length > 0 && createElement('div', { className: 'recipe-keywords' }, [
                createElement('h3', {}, 'Keywords'),
                createElement('p', {}, attributes.keywords.join(', '))
            ]),
            attributes.nutrition && createElement('div', { 
                className: 'recipe-nutrition',
                itemScope: true,
                itemType: 'https://schema.org/NutritionInformation'
            }, [
                createElement('h3', {}, 'Nutrition Information'),
                createElement('table', { className: 'nutrition-table' }, [
                    createElement('tbody', {}, [
                        createElement('tr', {}, [
                            createElement('th', {}, 'Serving Size'),
                            createElement('td', { itemProp: 'servingSize' }, nutrition.servingSize || '-')
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Number of Servings'),
                            createElement('td', {}, formatNutritionValue(nutrition.servings, ''))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Calories'),
                            createElement('td', { itemProp: 'calories' }, formatNutritionValue(nutrition.calories, ''))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Total Fat'),
                            createElement('td', { itemProp: 'fatContent' }, formatNutritionValue(nutrition.fatContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Saturated Fat'),
                            createElement('td', { itemProp: 'saturatedFatContent' }, formatNutritionValue(nutrition.saturatedFatContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Cholesterol'),
                            createElement('td', { itemProp: 'cholesterolContent' }, formatNutritionValue(nutrition.cholesterolContent, 'mg'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Sodium'),
                            createElement('td', { itemProp: 'sodiumContent' }, formatNutritionValue(nutrition.sodiumContent, 'mg'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Total Carbohydrates'),
                            createElement('td', { itemProp: 'carbohydrateContent' }, formatNutritionValue(nutrition.carbohydrateContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Dietary Fiber'),
                            createElement('td', { itemProp: 'fiberContent' }, formatNutritionValue(nutrition.fiberContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Sugars'),
                            createElement('td', { itemProp: 'sugarContent' }, formatNutritionValue(nutrition.sugarContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Protein'),
                            createElement('td', { itemProp: 'proteinContent' }, formatNutritionValue(nutrition.proteinContent, 'g'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Vitamin C'),
                            createElement('td', {}, formatNutritionValue(nutrition.vitaminC, '%'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Calcium'),
                            createElement('td', {}, formatNutritionValue(nutrition.calciumContent, '%'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Iron'),
                            createElement('td', {}, formatNutritionValue(nutrition.ironContent, '%'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Vitamin D'),
                            createElement('td', {}, formatNutritionValue(nutrition.vitaminD, '%'))
                        ]),
                        createElement('tr', {}, [
                            createElement('th', {}, 'Potassium'),
                            createElement('td', {}, formatNutritionValue(nutrition.potassiumContent, 'mg'))
                        ])
                    ])
                ])
            ])
        ]);
    }
}); 