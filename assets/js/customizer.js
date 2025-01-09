/* global wp */

/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
    'use strict';

    // Primary color
    wp.customize( 'quill_primary_color', function( value ) {
        value.bind( function( newval ) {
            document.documentElement.style.setProperty( '--color-primary', newval );
        } );
    } );

    // Copyright text
    wp.customize( 'quill_copyright_text', function( value ) {
        value.bind( function( newval ) {
            $( '.copyright' ).html( newval );
        } );
    } );

    // Recipe layout
    wp.customize( 'quill_recipe_layout', function( value ) {
        value.bind( function( newval ) {
            const recipeGrid = $( '.recipe-grid' );
            recipeGrid.removeClass( 'layout-grid layout-list layout-masonry' );
            recipeGrid.addClass( 'layout-' + newval );

            if ( 'masonry' === newval && $.fn.masonry ) {
                recipeGrid.masonry( 'reloadItems' ).masonry( 'layout' );
            }
        } );
    } );

    // Sidebar position
    wp.customize( 'quill_sidebar_position', function( value ) {
        value.bind( function( newval ) {
            const body = $( 'body' );
            body.removeClass( 'sidebar-left sidebar-right no-sidebar' );
            body.addClass( 'sidebar-' + newval );
        } );
    } );
} )( jQuery ); 