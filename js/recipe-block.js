const { registerBlockType } = wp.blocks;
const { Button, PanelBody, Notice } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { createElement, useState } = wp.element;
const { createBlock } = wp.blocks;
const { dispatch } = wp.data;
const apiFetch = wp.apiFetch;

registerBlockType('quill/recipe', {
    apiVersion: 2,
    title: __('Recipe Generator', 'quill'),
    icon: 'food',
    category: 'text',
    supports: {
        html: false,
        multiple: false
    },

    attributes: {
        generatedSchema: {
            type: 'object',
            default: null
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const [isGenerating, setIsGenerating] = useState(false);
        const [error, setError] = useState(null);
        
        const postContent = useSelect(select => 
            select('core/editor').getEditedPostContent()
        );

        const postId = useSelect(select =>
            select('core/editor').getCurrentPostId()
        );

        const postTitle = useSelect(select =>
            select('core/editor').getEditedPostAttribute('title')
        );

        const postPermalink = useSelect(select =>
            select('core/editor').getPermalink()
        );

        // Get post author info
        const author = useSelect(select => {
            const authorId = select('core/editor').getEditedPostAttribute('author');
            const authorInfo = select('core').getEntityRecord('root', 'user', authorId);
            return authorInfo;
        });

        // Get site info
        const site = useSelect(select => {
            return {
                name: select('core').getSite()?.title,
                description: select('core').getSite()?.description,
                url: select('core').getSite()?.url,
                logo: select('core').getEntityRecord('root', 'site')?.site_logo_url,
                social: select('core').getEntityRecord('root', 'site')?.social_links
            };
        });

        // Get featured image and media from post
        const media = useSelect(select => {
            const featuredImageId = select('core/editor').getEditedPostAttribute('featured_media');
            const featuredImage = featuredImageId ? select('core').getMedia(featuredImageId) : null;
            return featuredImage;
        });

        // Get embedded videos from post content
        const getVideoInfo = (content) => {
            const videoEmbed = content.match(/<figure class="wp-block-embed[^>]*>\s*<div[^>]*>\s*<iframe[^>]*src="([^"]*)"[^>]*>/);
            if (videoEmbed) {
                const embedUrl = videoEmbed[1];
                // Extract video ID for YouTube or Vimeo
                const videoId = embedUrl.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/)?.[1];
                if (videoId) {
                    return {
                        "@type": "VideoObject",
                        "name": postTitle,
                        "description": postTitle,
                        "uploadDate": new Date().toISOString().replace(/\.\d+Z$/, '+00:00'),
                        "thumbnailUrl": `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`,
                        "embedUrl": `https://www.youtube.com/embed/${videoId}?feature=oembed`,
                        "contentUrl": `https://www.youtube.com/watch?v=${videoId}`
                    };
                }
            }
            return null;
        };

        const { replaceBlocks } = dispatch('core/block-editor');
        const { getBlock } = useSelect((select) => select('core/block-editor'));

        async function generateRecipe() {
            if (isGenerating) return;
            
            setIsGenerating(true);
            setError(null);

            try {
                if (!postContent) {
                    throw new Error('Please add some content to your post first.');
                }

                const response = await apiFetch({
                    path: '/quill/v1/generate-recipe',
                    method: 'POST',
                    data: {
                        content: postContent,
                        post_id: postId,
                        post_title: postTitle,
                        post_url: postPermalink,
                        author: author ? {
                            name: author.name,
                            url: author.url,
                            description: author.description
                        } : null,
                        media: media ? {
                            url: media.source_url,
                            sizes: media.media_details?.sizes
                        } : null,
                        generate_nutrition: true // Request nutrition data
                    }
                });

                if (response?.success && response?.data) {
                    const { data } = response;
                    
                    console.log('Recipe API Response:', data);
                    
                    // Format the recipe data into a text block
                    const recipeText = formatRecipeText(data);
                    
                    // Create blocks array starting with the text content
                    const blocks = [
                        createBlock('core/paragraph', {
                            content: recipeText
                        })
                    ];

                    // Create schema data
                    const schema = createRecipeSchema(data, postPermalink);
                    
                    // Add schema block
                    blocks.push(
                        createBlock('core/html', {
                            content: `<!-- Recipe Schema -->
<script type="application/ld+json">
${JSON.stringify(schema, null, 2)}
</script>`
                        })
                    );

                    // Get the current block's clientId
                    const currentBlock = getBlock(props.clientId);
                    
                    // Replace the recipe generator block with the new blocks
                    replaceBlocks(currentBlock.clientId, blocks);
                } else {
                    console.error('Invalid API response:', response);
                    throw new Error('Invalid response from server');
                }
            } catch (error) {
                console.error('Recipe generation failed:', error);
                setError(error.message || 'Failed to generate recipe. Please try again.');
            } finally {
                setIsGenerating(false);
            }
        }

        function formatRecipeText(data) {
            const sections = [];

            // Add ingredients section
            if (data.ingredients) {
                sections.push(
                    '🥘 Ingredients:\n' +
                    data.ingredients
                );
            }

            // Add instructions section
            if (data.instructions) {
                const instructionsText = Array.isArray(data.instructions)
                    ? data.instructions.map((step, index) => `${index + 1}. ${step}`).join('\n')
                    : data.instructions;

                sections.push('\n\n📝 Instructions:\n' + instructionsText);
            }

            // Add equipment section if available
            if (data.equipment) {
                const equipmentList = Array.isArray(data.equipment)
                    ? data.equipment.map(item => `• ${item}`).join('\n')
                    : data.equipment;

                sections.push('\n\n🔪 Equipment Needed:\n' + equipmentList);
            }

            // Add prep/cook time if available
            const timeInfo = [];
            if (data.prep_time) timeInfo.push(`Prep Time: ${data.prep_time}`);
            if (data.cook_time) timeInfo.push(`Cook Time: ${data.cook_time}`);
            if (data.total_time) timeInfo.push(`Total Time: ${data.total_time}`);
            if (timeInfo.length) {
                sections.push('\n\n⏲️ Timing:\n' + timeInfo.join('\n'));
            }

            // Add servings if available
            if (data.yield) {
                sections.push(`\n\n👥 Servings: ${data.yield}`);
            }

            // Add cuisine if available
            if (data.cuisine) {
                sections.push(`\n\n🌍 Cuisine: ${data.cuisine}`);
            }

            // Add notes if available
            if (data.notes) {
                const notesList = Array.isArray(data.notes)
                    ? data.notes.map(note => `• ${note}`).join('\n')
                    : data.notes;

                sections.push('\n\n📌 Notes:\n' + notesList);
            }

            return sections.join('\n');
        }

        function createRecipeSchema(data, postUrl) {
            // Convert ingredients to array if it's a string
            const ingredients = data.ingredients
                ? data.ingredients.split('\n').filter(Boolean)
                : [];

            // Convert instructions to HowToStep objects
            const instructions = Array.isArray(data.instructions)
                ? data.instructions
                : data.instructions.split('\n').filter(Boolean);
            
            const instructionSteps = instructions.map((step, index) => ({
                "@type": "HowToStep",
                "text": step.replace(/^\d+\.\s*/, ''), // Remove leading numbers
                "name": step.replace(/^\d+\.\s*/, ''),
                "url": `${postUrl}#step-${index + 1}`
            }));

            // Format date in WordPress format
            const now = new Date();
            const datePublished = now.toISOString().replace(/\.\d+Z$/, '+00:00');

            // Get video info if available
            const videoInfo = getVideoInfo(postContent);

            // Get image URLs from media
            const imageUrls = [];
            const imageSizes = ['full', 'large', 'medium', 'thumbnail'];
            if (media?.media_details?.sizes) {
                imageSizes.forEach(size => {
                    if (media.media_details.sizes[size]) {
                        imageUrls.push(media.media_details.sizes[size].source_url);
                    }
                });
            }

            // Get article data
            const article = {
                "@type": "Article",
                "@id": `${postUrl}#article`,
                "headline": data.name,
                "datePublished": datePublished,
                "dateModified": datePublished,
                "wordCount": postContent.split(/\s+/).length,
                "commentCount": 0,
                "thumbnailUrl": imageUrls[0],
                "keywords": data.article_keywords || data.keywords,
                "articleSection": data.categories || [],
                "inLanguage": "en-US"
            };

            // Get organization data
            const organization = {
                "@type": "Organization",
                "@id": `${window.location.origin}/#organization`,
                "name": site?.name || '',
                "url": site?.url || window.location.origin,
                "description": site?.description,
                "sameAs": site?.social || [],
                "logo": site?.logo ? {
                    "@type": "ImageObject",
                    "@id": `${window.location.origin}/#/schema/logo/image/`,
                    "inLanguage": "en-US",
                    "url": site.logo,
                    "contentUrl": site.logo,
                    "width": 1000, // These should come from WordPress
                    "height": 300,
                    "caption": site.name
                } : undefined
            };

            // Get breadcrumb data
            const breadcrumb = {
                "@type": "BreadcrumbList",
                "@id": `${postUrl}#breadcrumb`,
                "itemListElement": [
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": site?.name || "Home",
                        "item": {
                            "@type": "Thing",
                            "@id": site?.url || window.location.origin
                        }
                    },
                    {
                        "@type": "ListItem",
                        "position": 2,
                        "name": "Recipes",
                        "item": {
                            "@type": "Thing",
                            "@id": `${site?.url || window.location.origin}/recipes/`
                        }
                    },
                    {
                        "@type": "ListItem",
                        "position": 3,
                        "name": data.name
                    }
                ]
            };

            // Create the schema object
            return {
                "@context": "https://schema.org/",
                "@type": "Recipe",
                "@id": `${postUrl}#recipe`,
                "name": data.name,
                "description": data.description?.replace(/\[&hellip;\]/, '...'),
                "datePublished": datePublished,
                "image": imageUrls,
                "recipeYield": [
                    data.yield,
                    data.yield.match(/\d+/) ? data.yield.match(/\d+/)[0] + " servings" : data.yield
                ].filter((v, i, a) => a.indexOf(v) === i),
                "cookTime": data.cook_time,
                "prepTime": data.prep_time,
                "totalTime": data.total_time,
                "recipeIngredient": ingredients,
                "recipeCategory": data.categories.filter(cat => 
                    !cat.includes('Low') && !cat.includes('Vegetarian')
                ),
                "recipeCuisine": data.cuisine,
                "keywords": data.keywords?.join(', '),
                "suitableForDiet": [
                    ...(data.categories.includes('Low Lactose') ? ["http://schema.org/LowLactoseDiet"] : []),
                    ...(data.categories.includes('Low Salt') ? ["http://schema.org/LowSaltDiet"] : []),
                    ...(data.categories.includes('Vegetarian') ? ["http://schema.org/VegetarianDiet"] : []),
                    ...(data.suitable_diets || [])
                ],
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": postUrl
                },
                "author": author ? {
                    "@type": "Person",
                    "@id": `${window.location.origin}/#/schema/person/${author.id}`,
                    "name": author.name,
                    "url": author.url || undefined,
                    "description": author.description || undefined
                } : {
                    "@type": "Person",
                    "@id": `${window.location.origin}/#/schema/person/author`,
                    "name": "Recipe Author"
                },
                "recipeInstructions": instructionSteps.map((step, index) => ({
                    "@type": "HowToStep",
                    "text": step.text,
                    "name": step.name,
                    "url": `${postUrl}#wprm-recipe-${postId}-step-0-${index}`,
                    "image": data.step_images?.[index]
                })),
                "nutrition": data.nutrition ? {
                    "@type": "NutritionInformation",
                    "calories": data.nutrition.calories,
                    "carbohydrateContent": data.nutrition.carbohydrates,
                    "proteinContent": data.nutrition.protein,
                    "fatContent": data.nutrition.fat,
                    "saturatedFatContent": data.nutrition.saturated_fat,
                    "cholesterolContent": data.nutrition.cholesterol,
                    "sodiumContent": data.nutrition.sodium,
                    "fiberContent": data.nutrition.fiber,
                    "sugarContent": data.nutrition.sugar,
                    "unsaturatedFatContent": data.nutrition.unsaturated_fat,
                    "servingSize": "1 serving"
                } : undefined,
                "video": videoInfo || (data.video ? {
                    "@type": "VideoObject",
                    "name": data.video.title,
                    "description": data.video.description,
                    "uploadDate": data.video.upload_date,
                    "duration": data.video.duration,
                    "thumbnailUrl": data.video.thumbnail_url,
                    "contentUrl": data.video.content_url,
                    "embedUrl": data.video.embed_url
                } : undefined),
                "isPartOf": {
                    ...article,
                    "isPartOf": {
                        "@type": "WebPage",
                        "@id": postUrl,
                        "url": postUrl,
                        "name": `${data.name} - ${site?.name}`,
                        "thumbnailUrl": imageUrls[0],
                        "datePublished": datePublished,
                        "dateModified": datePublished,
                        "description": data.description,
                        "inLanguage": "en-US",
                        "isPartOf": {
                            "@type": "WebSite",
                            "@id": `${window.location.origin}/#website`,
                            "url": window.location.origin,
                            "name": site?.name,
                            "description": site?.description,
                            "inLanguage": "en-US"
                        }
                    }
                },
                "publisher": organization,
                "breadcrumb": breadcrumb,
                "potentialAction": [
                    {
                        "@type": "CommentAction",
                        "name": "Comment",
                        "target": {
                            "@type": "EntryPoint",
                            "urlTemplate": `${postUrl}#respond`
                        }
                    },
                    {
                        "@type": "SearchAction",
                        "target": {
                            "@type": "EntryPoint",
                            "urlTemplate": `${window.location.origin}/?s={search_term_string}`
                        },
                        "query-input": {
                            "@type": "PropertyValueSpecification",
                            "valueRequired": "http://schema.org/True",
                            "valueName": "search_term_string"
                        }
                    }
                ]
            };
        }

        // Sidebar Controls
        const inspectorControls = createElement(InspectorControls, {},
            createElement(PanelBody, { 
                title: __('Recipe Generator', 'quill'),
                initialOpen: true 
            },
                error && createElement(Notice, {
                    status: 'error',
                    isDismissible: true,
                    onRemove: () => setError(null)
                }, error),
                
                createElement(Button, {
                    isPrimary: true,
                    onClick: generateRecipe,
                    disabled: isGenerating,
                    isBusy: isGenerating,
                    className: 'generate-recipe-button',
                    style: { width: '100%', marginBottom: '16px' }
                }, isGenerating ? __('Generating Recipe...', 'quill') : __('Generate Recipe from Content', 'quill')),
                
                createElement('p', { className: 'components-base-control__help' },
                    __('Click to analyze your post content and generate a structured recipe.', 'quill')
                )
            )
        );

        // Main placeholder content
        const blockContent = createElement('div', { 
            className: 'wp-block-quill-recipe-placeholder'
        },
            createElement('div', { className: 'components-placeholder' },
                createElement('div', { className: 'components-placeholder__label' },
                    __('Recipe Generator', 'quill')
                ),
                createElement('div', { className: 'components-placeholder__instructions' },
                    __('Click the "Generate Recipe" button in the sidebar to create a recipe from your post content.', 'quill')
                )
            )
        );

        return [inspectorControls, blockContent];
    },

    save: function() {
        return null; // Content is saved as paragraph and HTML blocks
    }
}); 