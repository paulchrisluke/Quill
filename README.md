# Quill Recipe Generator

A WordPress theme that automatically generates recipe data and schema markup using AI.

## Installation

1. Upload the theme to your WordPress themes directory
2. Activate the theme in WordPress admin
3. Add your OpenAI API key to `wp-config.php`:

```php
// Add this line to wp-config.php
define('OPENAI_API_KEY', 'your-api-key-here');
```

## Usage

1. Create a new post or edit an existing one
2. Add the Recipe Generator block
3. Write your recipe content in the post editor
4. Click "Generate Recipe" to automatically create:
   - Ingredient list
   - Step-by-step instructions
   - Cooking times
   - Recipe metadata
   - Schema.org markup

## Features

- Automatic recipe generation from post content
- Schema.org recipe markup for SEO
- Recipe metadata organization
- Nutrition facts calculation
- Equipment list generation
- Recipe notes and tips
- Category suggestions

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- OpenAI API key (GPT-4 access required)

## Support

For support or feature requests, please open an issue on GitHub. 