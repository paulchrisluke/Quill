/* global jQuery, quillRecipe */
(function($) {
    'use strict';

    // Initialize recipe meta boxes
    function initRecipeMeta() {
        initRepeaterFields();
        initSortable();
    }

    // Initialize repeater fields
    function initRepeaterFields() {
        // Add new item
        $('.recipe-repeater').on('click', '.add-repeater-item', function(e) {
            e.preventDefault();
            var $repeater = $(this).closest('.recipe-repeater');
            var $items = $repeater.find('.recipe-repeater-items');
            var template = $(this).data('template');
            var index = $items.children().length;
            
            // Replace placeholder index with actual index
            template = template.replace(/\{\{index\}\}/g, index);
            
            // Add new item
            $items.append(template);
            
            // Initialize nested repeaters
            initNestedRepeaters($items.children().last());
        });

        // Remove item
        $('.recipe-repeater').on('click', '.remove-repeater-item', function(e) {
            e.preventDefault();
            if (confirm(quillRecipe.i18n.confirmDelete)) {
                var $item = $(this).closest('.recipe-repeater-item');
                $item.slideUp(200, function() {
                    $item.remove();
                    reindexItems();
                });
            }
        });
    }

    // Initialize sortable functionality
    function initSortable() {
        $('.recipe-repeater-items').sortable({
            handle: '.group-name',
            items: '> .recipe-repeater-item',
            cursor: 'move',
            axis: 'y',
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            opacity: 0.8,
            tolerance: 'pointer',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            update: function() {
                reindexItems();
            }
        });
    }

    // Initialize nested repeater fields
    function initNestedRepeaters($container) {
        $container.find('.recipe-repeater').each(function() {
            var $nestedRepeater = $(this);
            var $nestedItems = $nestedRepeater.find('.recipe-repeater-items');
            
            // Make nested items sortable
            $nestedItems.sortable({
                handle: '.group-name',
                items: '> .recipe-repeater-item',
                cursor: 'move',
                axis: 'y',
                placeholder: 'ui-sortable-placeholder',
                forcePlaceholderSize: true,
                opacity: 0.8,
                tolerance: 'pointer',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                update: function() {
                    reindexItems();
                }
            });
        });
    }

    // Reindex all repeater items
    function reindexItems() {
        $('.recipe-repeater').each(function() {
            var $repeater = $(this);
            var baseKey = $repeater.data('repeater');
            
            $repeater.find('.recipe-repeater-item').each(function(index) {
                var $item = $(this);
                
                // Update input names
                $item.find('input, select, textarea').each(function() {
                    var $input = $(this);
                    var name = $input.attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + index + ']');
                        $input.attr('name', name);
                    }
                });
                
                // Update IDs and labels
                $item.find('label').each(function() {
                    var $label = $(this);
                    var forAttr = $label.attr('for');
                    if (forAttr) {
                        var newId = forAttr.replace(/\-\d+$/, '-' + index);
                        $label.attr('for', newId);
                        $item.find('#' + forAttr).attr('id', newId);
                    }
                });
            });
        });
    }

    // Handle dynamic updates for ingredient amounts
    function initIngredientCalculator() {
        var originalServings = parseInt($('#recipe_servings').val()) || 1;
        var $servingsField = $('#recipe_servings');
        var $ingredientAmounts = $('.ingredient-amount');
        
        // Store original amounts
        $ingredientAmounts.each(function() {
            $(this).data('original-amount', $(this).val());
        });
        
        // Update amounts when servings change
        $servingsField.on('change', function() {
            var newServings = parseInt($(this).val()) || 1;
            var ratio = newServings / originalServings;
            
            $ingredientAmounts.each(function() {
                var $amount = $(this);
                var originalAmount = parseFloat($amount.data('original-amount')) || 0;
                var newAmount = (originalAmount * ratio).toFixed(2);
                $amount.val(newAmount);
            });
        });
    }

    // Initialize tooltips for help text
    function initTooltips() {
        $('.recipe-meta-field .description').each(function() {
            var $description = $(this);
            var $field = $description.closest('.recipe-meta-field');
            var $label = $field.find('label').first();
            
            $label.append('<span class="dashicons dashicons-editor-help tooltip-trigger"></span>');
            $description.addClass('tooltip-content').hide();
            
            $field.on('mouseenter', '.tooltip-trigger', function() {
                $description.fadeIn(200);
            }).on('mouseleave', '.tooltip-trigger', function() {
                $description.fadeOut(200);
            });
        });
    }

    // Initialize validation
    function initValidation() {
        $('#post').on('submit', function(e) {
            var isValid = true;
            
            // Check required fields
            $('.recipe-meta-field.required').each(function() {
                var $field = $(this);
                var $input = $field.find('input, select, textarea');
                
                if (!$input.val()) {
                    isValid = false;
                    $field.addClass('error');
                    $field.find('.error-message').remove();
                    $field.append('<span class="error-message">This field is required.</span>');
                } else {
                    $field.removeClass('error');
                    $field.find('.error-message').remove();
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('.recipe-meta-field.error').first().offset().top - 50
                }, 500);
            }
        });
    }

    // Document ready
    $(function() {
        initRecipeMeta();
        initIngredientCalculator();
        initTooltips();
        initValidation();
    });

})(jQuery); 