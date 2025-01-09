<?php

/**
 * Recipe meta box class
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Recipe meta box class
 */
class Quill_Recipe_Meta
{
    /**
     * Initialize the class
     */
    public function init()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_recipe', array($this, 'save_meta_boxes'));
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'recipe_details',
            __('Recipe Details', 'quill'),
            array($this, 'render_recipe_details'),
            'recipe',
            'normal',
            'high'
        );

        add_meta_box(
            'recipe_ingredients',
            __('Recipe Ingredients', 'quill'),
            array($this, 'render_recipe_ingredients'),
            'recipe',
            'normal',
            'high'
        );

        add_meta_box(
            'recipe_instructions',
            __('Recipe Instructions', 'quill'),
            array($this, 'render_recipe_instructions'),
            'recipe',
            'normal',
            'high'
        );

        add_meta_box(
            'recipe_nutrition',
            __('Nutrition Information', 'quill'),
            array($this, 'render_recipe_nutrition'),
            'recipe',
            'normal',
            'high'
        );

        add_meta_box(
            'recipe_notes',
            __('Recipe Notes', 'quill'),
            array($this, 'render_recipe_notes'),
            'recipe',
            'normal',
            'high'
        );
    }

    /**
     * Render recipe details meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_recipe_details($post)
    {
        wp_nonce_field('quill_recipe_details', 'quill_recipe_details_nonce');

        $prep_time = get_post_meta($post->ID, '_recipe_prep_time', true);
        $cook_time = get_post_meta($post->ID, '_recipe_cook_time', true);
        $total_time = get_post_meta($post->ID, '_recipe_total_time', true);
        $servings = get_post_meta($post->ID, '_recipe_servings', true);
        $difficulty = get_post_meta($post->ID, '_recipe_difficulty', true);
?>
        <div class="recipe-meta-box">
            <div class="recipe-meta-field">
                <label for="recipe_prep_time"><?php esc_html_e('Preparation Time', 'quill'); ?></label>
                <input type="text" id="recipe_prep_time" name="recipe_prep_time" value="<?php echo esc_attr($prep_time); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('e.g. 30 minutes', 'quill'); ?></p>
            </div>

            <div class="recipe-meta-field">
                <label for="recipe_cook_time"><?php esc_html_e('Cooking Time', 'quill'); ?></label>
                <input type="text" id="recipe_cook_time" name="recipe_cook_time" value="<?php echo esc_attr($cook_time); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('e.g. 1 hour', 'quill'); ?></p>
            </div>

            <div class="recipe-meta-field">
                <label for="recipe_total_time"><?php esc_html_e('Total Time', 'quill'); ?></label>
                <input type="text" id="recipe_total_time" name="recipe_total_time" value="<?php echo esc_attr($total_time); ?>" class="regular-text">
                <p class="description"><?php esc_html_e('e.g. 1 hour 30 minutes', 'quill'); ?></p>
            </div>

            <div class="recipe-meta-field">
                <label for="recipe_servings"><?php esc_html_e('Servings', 'quill'); ?></label>
                <input type="number" id="recipe_servings" name="recipe_servings" value="<?php echo esc_attr($servings); ?>" class="small-text" min="1">
            </div>

            <div class="recipe-meta-field">
                <label for="recipe_difficulty"><?php esc_html_e('Difficulty', 'quill'); ?></label>
                <select id="recipe_difficulty" name="recipe_difficulty">
                    <option value=""><?php esc_html_e('Select difficulty', 'quill'); ?></option>
                    <option value="easy" <?php selected($difficulty, 'easy'); ?>><?php esc_html_e('Easy', 'quill'); ?></option>
                    <option value="medium" <?php selected($difficulty, 'medium'); ?>><?php esc_html_e('Medium', 'quill'); ?></option>
                    <option value="hard" <?php selected($difficulty, 'hard'); ?>><?php esc_html_e('Hard', 'quill'); ?></option>
                </select>
            </div>
        </div>
    <?php
    }

    /**
     * Render recipe ingredients meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_recipe_ingredients($post)
    {
        wp_nonce_field('quill_recipe_ingredients', 'quill_recipe_ingredients_nonce');

        $ingredients = get_post_meta($post->ID, '_recipe_ingredients', true);
        if (! is_array($ingredients)) {
            $ingredients = array();
        }
    ?>
        <div class="recipe-meta-box">
            <div class="recipe-repeater">
                <div class="recipe-repeater-items">
                    <?php
                    foreach ($ingredients as $index => $ingredient) {
                        $this->render_ingredient_row($index, $ingredient);
                    }
                    ?>
                </div>

                <button type="button" class="button add-repeater-item" data-template="<?php echo esc_attr($this->get_ingredient_template()); ?>">
                    <?php esc_html_e('Add Ingredient', 'quill'); ?>
                </button>
            </div>
        </div>
    <?php
    }

    /**
     * Render recipe instructions meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_recipe_instructions($post)
    {
        wp_nonce_field('quill_recipe_instructions', 'quill_recipe_instructions_nonce');

        $instructions = get_post_meta($post->ID, '_recipe_instructions', true);
        if (! is_array($instructions)) {
            $instructions = array();
        }
    ?>
        <div class="recipe-meta-box">
            <div class="recipe-repeater">
                <div class="recipe-repeater-items">
                    <?php
                    foreach ($instructions as $index => $instruction) {
                        $this->render_instruction_row($index, $instruction);
                    }
                    ?>
                </div>

                <button type="button" class="button add-repeater-item" data-template="<?php echo esc_attr($this->get_instruction_template()); ?>">
                    <?php esc_html_e('Add Instruction', 'quill'); ?>
                </button>
            </div>
        </div>
    <?php
    }

    /**
     * Render recipe nutrition meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_recipe_nutrition($post)
    {
        wp_nonce_field('quill_recipe_nutrition', 'quill_recipe_nutrition_nonce');

        $calories = get_post_meta($post->ID, '_recipe_calories', true);
        $fat = get_post_meta($post->ID, '_recipe_fat', true);
        $saturated_fat = get_post_meta($post->ID, '_recipe_saturated_fat', true);
        $cholesterol = get_post_meta($post->ID, '_recipe_cholesterol', true);
        $sodium = get_post_meta($post->ID, '_recipe_sodium', true);
        $carbohydrates = get_post_meta($post->ID, '_recipe_carbohydrates', true);
        $fiber = get_post_meta($post->ID, '_recipe_fiber', true);
        $sugar = get_post_meta($post->ID, '_recipe_sugar', true);
        $protein = get_post_meta($post->ID, '_recipe_protein', true);
    ?>
        <div class="recipe-meta-box">
            <div class="nutrition-grid">
                <div class="recipe-meta-field">
                    <label for="recipe_calories"><?php esc_html_e('Calories', 'quill'); ?></label>
                    <input type="number" id="recipe_calories" name="recipe_calories" value="<?php echo esc_attr($calories); ?>" class="small-text" min="0">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_fat"><?php esc_html_e('Fat (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_fat" name="recipe_fat" value="<?php echo esc_attr($fat); ?>" class="small-text" min="0" step="0.1">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_saturated_fat"><?php esc_html_e('Saturated Fat (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_saturated_fat" name="recipe_saturated_fat" value="<?php echo esc_attr($saturated_fat); ?>" class="small-text" min="0" step="0.1">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_cholesterol"><?php esc_html_e('Cholesterol (mg)', 'quill'); ?></label>
                    <input type="number" id="recipe_cholesterol" name="recipe_cholesterol" value="<?php echo esc_attr($cholesterol); ?>" class="small-text" min="0">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_sodium"><?php esc_html_e('Sodium (mg)', 'quill'); ?></label>
                    <input type="number" id="recipe_sodium" name="recipe_sodium" value="<?php echo esc_attr($sodium); ?>" class="small-text" min="0">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_carbohydrates"><?php esc_html_e('Carbohydrates (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_carbohydrates" name="recipe_carbohydrates" value="<?php echo esc_attr($carbohydrates); ?>" class="small-text" min="0" step="0.1">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_fiber"><?php esc_html_e('Fiber (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_fiber" name="recipe_fiber" value="<?php echo esc_attr($fiber); ?>" class="small-text" min="0" step="0.1">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_sugar"><?php esc_html_e('Sugar (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_sugar" name="recipe_sugar" value="<?php echo esc_attr($sugar); ?>" class="small-text" min="0" step="0.1">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_protein"><?php esc_html_e('Protein (g)', 'quill'); ?></label>
                    <input type="number" id="recipe_protein" name="recipe_protein" value="<?php echo esc_attr($protein); ?>" class="small-text" min="0" step="0.1">
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render recipe notes meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_recipe_notes($post)
    {
        wp_nonce_field('quill_recipe_notes', 'quill_recipe_notes_nonce');

        $notes = get_post_meta($post->ID, '_recipe_notes', true);
    ?>
        <div class="recipe-meta-box">
            <div class="recipe-meta-field">
                <textarea id="recipe_notes" name="recipe_notes" class="large-text" rows="5"><?php echo esc_textarea($notes); ?></textarea>
                <p class="description"><?php esc_html_e('Add any additional notes, tips, or variations for the recipe.', 'quill'); ?></p>
            </div>
        </div>
    <?php
    }

    /**
     * Render ingredient row
     *
     * @param int   $index     Row index.
     * @param array $ingredient Ingredient data.
     */
    private function render_ingredient_row($index, $ingredient)
    {
    ?>
        <div class="recipe-repeater-item">
            <div class="group-name">
                <?php esc_html_e('Ingredient', 'quill'); ?>
                <a href="#" class="remove-repeater-item dashicons dashicons-no-alt"></a>
            </div>

            <div class="ingredient-row">
                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_<?php echo esc_attr($index); ?>_amount"><?php esc_html_e('Amount', 'quill'); ?></label>
                    <input type="number" id="recipe_ingredients_<?php echo esc_attr($index); ?>_amount" name="recipe_ingredients[<?php echo esc_attr($index); ?>][amount]" value="<?php echo esc_attr($ingredient['amount']); ?>" class="ingredient-amount" min="0" step="0.01">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_<?php echo esc_attr($index); ?>_unit"><?php esc_html_e('Unit', 'quill'); ?></label>
                    <select id="recipe_ingredients_<?php echo esc_attr($index); ?>_unit" name="recipe_ingredients[<?php echo esc_attr($index); ?>][unit]" class="ingredient-unit">
                        <option value=""><?php esc_html_e('Select unit', 'quill'); ?></option>
                        <?php foreach ($this->get_ingredient_units() as $unit => $label) : ?>
                            <option value="<?php echo esc_attr($unit); ?>" <?php selected($ingredient['unit'], $unit); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_<?php echo esc_attr($index); ?>_name"><?php esc_html_e('Name', 'quill'); ?></label>
                    <input type="text" id="recipe_ingredients_<?php echo esc_attr($index); ?>_name" name="recipe_ingredients[<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($ingredient['name']); ?>" class="ingredient-name">
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render instruction row
     *
     * @param int    $index       Row index.
     * @param string $instruction Instruction text.
     */
    private function render_instruction_row($index, $instruction)
    {
    ?>
        <div class="recipe-repeater-item">
            <div class="group-name">
                <?php
                /* translators: %d: instruction number */
                printf(esc_html__('Step %d', 'quill'), $index + 1);
                ?>
                <a href="#" class="remove-repeater-item dashicons dashicons-no-alt"></a>
            </div>

            <div class="recipe-meta-field">
                <textarea id="recipe_instructions_<?php echo esc_attr($index); ?>" name="recipe_instructions[]" class="large-text" rows="3"><?php echo esc_textarea($instruction); ?></textarea>
            </div>
        </div>
    <?php
    }

    /**
     * Get ingredient row template
     *
     * @return string Ingredient row template.
     */
    private function get_ingredient_template()
    {
    ?>
        <div class="recipe-repeater-item">
            <div class="group-name">
                <?php esc_html_e('Ingredient', 'quill'); ?>
                <a href="#" class="remove-repeater-item dashicons dashicons-no-alt"></a>
            </div>

            <div class="ingredient-row">
                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_{{index}}_amount"><?php esc_html_e('Amount', 'quill'); ?></label>
                    <input type="number" id="recipe_ingredients_{{index}}_amount" name="recipe_ingredients[{{index}}][amount]" class="ingredient-amount" min="0" step="0.01">
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_{{index}}_unit"><?php esc_html_e('Unit', 'quill'); ?></label>
                    <select id="recipe_ingredients_{{index}}_unit" name="recipe_ingredients[{{index}}][unit]" class="ingredient-unit">
                        <option value=""><?php esc_html_e('Select unit', 'quill'); ?></option>
                        <?php foreach ($this->get_ingredient_units() as $unit => $label) : ?>
                            <option value="<?php echo esc_attr($unit); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="recipe-meta-field">
                    <label for="recipe_ingredients_{{index}}_name"><?php esc_html_e('Name', 'quill'); ?></label>
                    <input type="text" id="recipe_ingredients_{{index}}_name" name="recipe_ingredients[{{index}}][name]" class="ingredient-name">
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Get instruction row template
     *
     * @return string Instruction row template.
     */
    private function get_instruction_template()
    {
    ?>
        <div class="recipe-repeater-item">
            <div class="group-name">
                <?php esc_html_e('Step', 'quill'); ?>
                <a href="#" class="remove-repeater-item dashicons dashicons-no-alt"></a>
            </div>

            <div class="recipe-meta-field">
                <textarea name="recipe_instructions[]" class="large-text" rows="3"></textarea>
            </div>
        </div>
<?php
    }

    /**
     * Get ingredient units
     *
     * @return array Ingredient units.
     */
    private function get_ingredient_units()
    {
        return array(
            'tsp'  => __('teaspoon', 'quill'),
            'tbsp' => __('tablespoon', 'quill'),
            'cup'  => __('cup', 'quill'),
            'oz'   => __('ounce', 'quill'),
            'lb'   => __('pound', 'quill'),
            'g'    => __('gram', 'quill'),
            'kg'   => __('kilogram', 'quill'),
            'ml'   => __('milliliter', 'quill'),
            'l'    => __('liter', 'quill'),
            'pc'   => __('piece', 'quill'),
            'pn'   => __('pinch', 'quill'),
            'ds'   => __('dash', 'quill'),
        );
    }

    /**
     * Save meta boxes
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_boxes($post_id)
    {
        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST['quill_recipe_details_nonce'], 'quill_recipe_details')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save recipe details
        $fields = array(
            'prep_time',
            'cook_time',
            'total_time',
            'servings',
            'difficulty',
        );

        foreach ($fields as $field) {
            if (isset($_POST['recipe_' . $field])) {
                update_post_meta($post_id, '_recipe_' . $field, sanitize_text_field($_POST['recipe_' . $field]));
            }
        }

        // Save ingredients
        if (isset($_POST['recipe_ingredients'])) {
            $ingredients = array();
            foreach ($_POST['recipe_ingredients'] as $ingredient) {
                if (empty($ingredient['amount']) && empty($ingredient['unit']) && empty($ingredient['name'])) {
                    continue;
                }
                $ingredients[] = array(
                    'amount' => sanitize_text_field($ingredient['amount']),
                    'unit'   => sanitize_text_field($ingredient['unit']),
                    'name'   => sanitize_text_field($ingredient['name']),
                );
            }
            update_post_meta($post_id, '_recipe_ingredients', $ingredients);
        }

        // Save instructions
        if (isset($_POST['recipe_instructions'])) {
            $instructions = array_filter(array_map('wp_kses_post', $_POST['recipe_instructions']));
            update_post_meta($post_id, '_recipe_instructions', $instructions);
        }

        // Save nutrition info
        $nutrition_fields = array(
            'calories',
            'fat',
            'saturated_fat',
            'cholesterol',
            'sodium',
            'carbohydrates',
            'fiber',
            'sugar',
            'protein',
        );

        foreach ($nutrition_fields as $field) {
            if (isset($_POST['recipe_' . $field])) {
                update_post_meta($post_id, '_recipe_' . $field, sanitize_text_field($_POST['recipe_' . $field]));
            }
        }

        // Save notes
        if (isset($_POST['recipe_notes'])) {
            update_post_meta($post_id, '_recipe_notes', wp_kses_post($_POST['recipe_notes']));
        }
    }
}
