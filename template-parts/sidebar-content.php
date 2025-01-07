<?php

/**
 * Template part for displaying sidebar navigation content
 */
?>
<ul>
    <?php
    $categories = get_categories(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false
    ));

    foreach ($categories as $category) :
        $first_letter = mb_substr($category->name, 0, 1);
    ?>
        <li>
            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                <span class="category-letter"><?php echo esc_html($first_letter); ?></span>
                <?php echo esc_html($category->name); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>