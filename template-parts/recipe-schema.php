<?php

/**
 * Template part for displaying recipe schema
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

$post = get_post();
if (! $post) {
    return;
}

$meta = quill_get_recipe_meta($post);
$ingredients = quill_get_recipe_ingredients($post);
$instructions = quill_get_recipe_instructions($post);
$nutrition = quill_get_recipe_nutrition($post);

$schema = array(
    '@context' => 'https://schema.org/',
    '@type' => 'Recipe',
    'name' => get_the_title(),
    'description' => get_the_excerpt(),
    'datePublished' => get_the_date('c'),
    'author' => array(
        '@type' => 'Person',
        'name' => get_the_author(),
    ),
);

// Add image if available
if (has_post_thumbnail()) {
    $schema['image'] = array(
        '@type' => 'ImageObject',
        'url' => get_the_post_thumbnail_url(null, 'full'),
        'width' => 1200,
        'height' => 675,
    );
}

// Add recipe meta
if (! empty($meta['prep_time'])) {
    $schema['prepTime'] = 'PT' . strtoupper(str_replace(' ', '', $meta['prep_time']));
}

if (! empty($meta['cook_time'])) {
    $schema['cookTime'] = 'PT' . strtoupper(str_replace(' ', '', $meta['cook_time']));
}

if (! empty($meta['total_time'])) {
    $schema['totalTime'] = 'PT' . strtoupper(str_replace(' ', '', $meta['total_time']));
}

if (! empty($meta['servings'])) {
    $schema['recipeYield'] = $meta['servings'] . ' servings';
}

if (! empty($meta['difficulty'])) {
    $schema['recipeDifficulty'] = ucfirst($meta['difficulty']);
}

if (! empty($meta['cuisine'])) {
    $schema['recipeCuisine'] = $meta['cuisine'];
}

if (! empty($meta['course'])) {
    $schema['recipeCategory'] = $meta['course'];
}

if (! empty($meta['dietary'])) {
    $schema['suitableForDiet'] = array_map(function ($diet) {
        return ucfirst(str_replace('_', '', $diet)) . 'Diet';
    }, $meta['dietary']);
}

// Add ingredients
if (! empty($ingredients)) {
    $schema['recipeIngredient'] = array_map(function ($ingredient) {
        return sprintf(
            '%s %s %s',
            $ingredient['amount'],
            $ingredient['unit'],
            $ingredient['name']
        );
    }, $ingredients);
}

// Add instructions
if (! empty($instructions)) {
    $schema['recipeInstructions'] = array_map(function ($index, $instruction) {
        return array(
            '@type' => 'HowToStep',
            'position' => $index + 1,
            'text' => $instruction,
        );
    }, array_keys($instructions), $instructions);
}

// Add nutrition
if (! empty($nutrition)) {
    $schema['nutrition'] = array(
        '@type' => 'NutritionInformation',
    );

    if (! empty($nutrition['calories'])) {
        $schema['nutrition']['calories'] = $nutrition['calories'] . ' calories';
    }

    if (! empty($nutrition['fat'])) {
        $schema['nutrition']['fatContent'] = $nutrition['fat'] . 'g';
    }

    if (! empty($nutrition['saturated_fat'])) {
        $schema['nutrition']['saturatedFatContent'] = $nutrition['saturated_fat'] . 'g';
    }

    if (! empty($nutrition['cholesterol'])) {
        $schema['nutrition']['cholesterolContent'] = $nutrition['cholesterol'] . 'mg';
    }

    if (! empty($nutrition['sodium'])) {
        $schema['nutrition']['sodiumContent'] = $nutrition['sodium'] . 'mg';
    }

    if (! empty($nutrition['carbohydrates'])) {
        $schema['nutrition']['carbohydrateContent'] = $nutrition['carbohydrates'] . 'g';
    }

    if (! empty($nutrition['fiber'])) {
        $schema['nutrition']['fiberContent'] = $nutrition['fiber'] . 'g';
    }

    if (! empty($nutrition['sugar'])) {
        $schema['nutrition']['sugarContent'] = $nutrition['sugar'] . 'g';
    }

    if (! empty($nutrition['protein'])) {
        $schema['nutrition']['proteinContent'] = $nutrition['protein'] . 'g';
    }
}

// Add keywords
$keywords = array();

// Add cuisine type
if (! empty($meta['cuisine'])) {
    $keywords = array_merge($keywords, $meta['cuisine']);
}

// Add course type
if (! empty($meta['course'])) {
    $keywords = array_merge($keywords, $meta['course']);
}

// Add dietary restrictions
if (! empty($meta['dietary'])) {
    $keywords = array_merge($keywords, array_map('ucfirst', $meta['dietary']));
}

// Add post tags
$tags = get_the_tags();
if ($tags) {
    $keywords = array_merge($keywords, wp_list_pluck($tags, 'name'));
}

if (! empty($keywords)) {
    $schema['keywords'] = implode(', ', array_unique($keywords));
}

// Add aggregate rating if available
$comments = get_comments(array(
    'post_id' => get_the_ID(),
    'status' => 'approve',
));

if (! empty($comments)) {
    $ratings = array_filter(array_map(function ($comment) {
        return get_comment_meta($comment->comment_ID, 'rating', true);
    }, $comments));

    if (! empty($ratings)) {
        $schema['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => number_format(array_sum($ratings) / count($ratings), 1),
            'ratingCount' => count($ratings),
            'bestRating' => '5',
            'worstRating' => '1',
        );
    }
}

// Add video if available
$video_url = get_post_meta(get_the_ID(), '_recipe_video_url', true);
if ($video_url) {
    $schema['video'] = array(
        '@type' => 'VideoObject',
        'name' => get_the_title(),
        'description' => get_the_excerpt(),
        'thumbnailUrl' => get_the_post_thumbnail_url(null, 'full'),
        'contentUrl' => $video_url,
        'uploadDate' => get_the_date('c'),
        'duration' => get_post_meta(get_the_ID(), '_recipe_video_duration', true),
    );
}

// Output schema
?>
<script type="application/ld+json">
    <?php echo wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>