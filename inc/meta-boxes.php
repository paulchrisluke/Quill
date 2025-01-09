<?php

/**
 * Register meta boxes for recipes
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add recipe meta boxes
 */
function quill_add_recipe_meta_boxes()
{
    add_meta_box(
        'recipe_details',
        __('Recipe Details', 'quill'),
        'quill_recipe_details_meta_box',
        'recipe',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'quill_add_recipe_meta_boxes');

/**
 * Recipe details meta box callback
 */
function quill_recipe_details_meta_box($post)
{
    // Add nonce for security
    wp_nonce_field('quill_recipe_details', 'recipe_details_nonce');

    // Get existing values
    $cooking_time = get_post_meta($post->ID, 'cooking_time', true);
    $servings = get_post_meta($post->ID, 'servings', true);
    $difficulty = get_post_meta($post->ID, 'difficulty', true);
    $ingredients = get_post_meta($post->ID, 'ingredients', true);
    $instructions = get_post_meta($post->ID, 'instructions', true);

    // Default arrays if empty
    $ingredients = !empty($ingredients) ? $ingredients : array('');
    $instructions = !empty($instructions) ? $instructions : array('');
?>

    <div class="recipe-meta-box">
        <p>
            <label for="cooking_time"><?php _e('Cooking Time (minutes)', 'quill'); ?></label>
            <input type="number" id="cooking_time" name="cooking_time" value="<?php echo esc_attr($cooking_time); ?>" />
        </p>

        <p>
            <label for="servings"><?php _e('Number of Servings', 'quill'); ?></label>
            <input type="number" id="servings" name="servings" value="<?php echo esc_attr($servings); ?>" />
        </p>

        <p>
            <label for="difficulty"><?php _e('Difficulty Level', 'quill'); ?></label>
            <select id="difficulty" name="difficulty">
                <option value="easy" <?php selected($difficulty, 'easy'); ?>><?php _e('Easy', 'quill'); ?></option>
                <option value="medium" <?php selected($difficulty, 'medium'); ?>><?php _e('Medium', 'quill'); ?></option>
                <option value="hard" <?php selected($difficulty, 'hard'); ?>><?php _e('Hard', 'quill'); ?></option>
            </select>
        </p>

        <div class="recipe-ingredients">
            <h4><?php _e('Ingredients', 'quill'); ?></h4>
            <div class="ingredients-list">
                <?php foreach ($ingredients as $index => $ingredient) : ?>
                    <p>
                        <input type="text" class="widefat" name="ingredients[]" value="<?php echo esc_attr($ingredient); ?>" />
                        <button type="button" class="button remove-row"><?php _e('Remove', 'quill'); ?></button>
                    </p>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-ingredient"><?php _e('Add Ingredient', 'quill'); ?></button>
        </div>

        <div class="recipe-instructions">
            <h4><?php _e('Instructions', 'quill'); ?></h4>
            <div class="instructions-list">
                <?php foreach ($instructions as $index => $instruction) : ?>
                    <p>
                        <textarea class="widefat" name="instructions[]" rows="3"><?php echo esc_textarea($instruction); ?></textarea>
                        <button type="button" class="button remove-row"><?php _e('Remove', 'quill'); ?></button>
                    </p>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-instruction"><?php _e('Add Instruction', 'quill'); ?></button>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Add ingredient
            $('.add-ingredient').on('click', function() {
                var row = '<p><input type="text" class="widefat" name="ingredients[]" value="" />' +
                    '<button type="button" class="button remove-row"><?php _e('Remove', 'quill'); ?></button></p>';
                $('.ingredients-list').append(row);
            });

            // Add instruction
            $('.add-instruction').on('click', function() {
                var row = '<p><textarea class="widefat" name="instructions[]" rows="3"></textarea>' +
                    '<button type="button" class="button remove-row"><?php _e('Remove', 'quill'); ?></button></p>';
                $('.instructions-list').append(row);
            });

            // Remove row
            $('.recipe-meta-box').on('click', '.remove-row', function() {
                $(this).parent('p').remove();
            });
        });
    </script>

    <style>
        .recipe-meta-box label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        .recipe-meta-box input[type="number"] {
            width: 100px;
        }

        .recipe-ingredients,
        .recipe-instructions {
            margin: 20px 0;
        }

        .ingredients-list p,
        .instructions-list p {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .remove-row {
            flex-shrink: 0;
        }
    </style>
<?php
}

/**
 * Save recipe meta box data
 */
function quill_save_recipe_meta_box($post_id)
{
    // Check if nonce is set
    if (!isset($_POST['recipe_details_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['recipe_details_nonce'], 'quill_recipe_details')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save cooking time
    if (isset($_POST['cooking_time'])) {
        update_post_meta($post_id, 'cooking_time', sanitize_text_field($_POST['cooking_time']));
    }

    // Save servings
    if (isset($_POST['servings'])) {
        update_post_meta($post_id, 'servings', sanitize_text_field($_POST['servings']));
    }

    // Save difficulty
    if (isset($_POST['difficulty'])) {
        update_post_meta($post_id, 'difficulty', sanitize_text_field($_POST['difficulty']));
    }

    // Save ingredients
    if (isset($_POST['ingredients'])) {
        $ingredients = array_filter(array_map('sanitize_text_field', $_POST['ingredients']));
        update_post_meta($post_id, 'ingredients', $ingredients);
    }

    // Save instructions
    if (isset($_POST['instructions'])) {
        $instructions = array_filter(array_map('wp_kses_post', $_POST['instructions']));
        update_post_meta($post_id, 'instructions', $instructions);
    }
}
add_action('save_post_recipe', 'quill_save_recipe_meta_box');
