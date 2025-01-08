<?php

/**
 * Recipe Block Handler
 */
class Quill_Recipe
{
    /**
     * Initialize the recipe block
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_recipe_block'));
    }

    /**
     * Register the recipe block
     */
    public function register_recipe_block()
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type('quill/recipe', array(
            'api_version' => 2,
            'editor_script' => 'quill-recipe-block',
            'render_callback' => array($this, 'render_recipe_block'),
            'attributes' => array(
                'ingredients' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'instructions' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'equipment' => array(
                    'type' => 'array',
                    'default' => array()
                ),
                'prepTime' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'cookTime' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'totalTime' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'servings' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'cuisine' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'categories' => array(
                    'type' => 'array',
                    'default' => array()
                ),
                'keywords' => array(
                    'type' => 'array',
                    'default' => array()
                ),
                'notes' => array(
                    'type' => 'array',
                    'default' => array()
                ),
                'generatedSchema' => array(
                    'type' => 'object',
                    'default' => null
                )
            )
        ));
    }

    /**
     * Render the recipe block
     */
    public function render_recipe_block($attributes)
    {
        if (empty($attributes['ingredients']) && empty($attributes['instructions'])) {
            return '';
        }

        // Add schema to head
        add_action('wp_head', function () use ($attributes) {
            if (!empty($attributes['generatedSchema'])) {
                echo '<script type="application/ld+json">';
                echo wp_json_encode($attributes['generatedSchema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                echo '</script>';
            }
        });

        ob_start();
?>
        <div class="wp-block-quill-recipe recipe-block" itemscope itemtype="https://schema.org/Recipe">
            <?php if (!empty($attributes['prepTime']) || !empty($attributes['cookTime'])) : ?>
                <div class="recipe-times">
                    <?php if (!empty($attributes['prepTime'])) : ?>
                        <div class="prep-time">
                            <strong><?php _e('Prep Time:', 'quill'); ?></strong>
                            <span itemprop="prepTime"><?php echo esc_html($attributes['prepTime']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($attributes['cookTime'])) : ?>
                        <div class="cook-time">
                            <strong><?php _e('Cook Time:', 'quill'); ?></strong>
                            <span itemprop="cookTime"><?php echo esc_html($attributes['cookTime']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($attributes['totalTime'])) : ?>
                        <div class="total-time">
                            <strong><?php _e('Total Time:', 'quill'); ?></strong>
                            <span itemprop="totalTime"><?php echo esc_html($attributes['totalTime']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($attributes['ingredients'])) : ?>
                <div class="recipe-ingredients">
                    <h3><?php _e('Ingredients', 'quill'); ?></h3>
                    <ul>
                        <?php foreach (explode("\n", $attributes['ingredients']) as $ingredient) : ?>
                            <li itemprop="recipeIngredient"><?php echo esc_html(trim($ingredient)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($attributes['instructions'])) : ?>
                <div class="recipe-instructions">
                    <h3><?php _e('Instructions', 'quill'); ?></h3>
                    <ol>
                        <?php foreach (explode("\n", $attributes['instructions']) as $instruction) : ?>
                            <li itemscope itemprop="recipeInstructions" itemtype="https://schema.org/HowToStep">
                                <meta itemprop="text" content="<?php echo esc_attr(trim($instruction)); ?>">
                                <?php echo esc_html(trim($instruction)); ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>

            <?php if (!empty($attributes['equipment'])) : ?>
                <div class="recipe-equipment">
                    <h3><?php _e('Equipment Needed', 'quill'); ?></h3>
                    <ul>
                        <?php foreach ($attributes['equipment'] as $item) : ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($attributes['notes'])) : ?>
                <div class="recipe-notes">
                    <h3><?php _e('Recipe Notes', 'quill'); ?></h3>
                    <ul>
                        <?php foreach ($attributes['notes'] as $note) : ?>
                            <li><?php echo esc_html(trim($note)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
<?php
        return ob_get_clean();
    }
}
