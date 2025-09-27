## Simple image gallery based on Custom Post Types/Taxonomy and Fancybox
This plugin allow to create simple gallery and output it with shortcode or Gutenber block.

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Customization](#customization)
- [Filters](#filters)

## Installation

Clone this repo or upload and unzip archive to `wp-content/plugins` directory. Enable plugin.

## Usage
1. Under admin area, go to CPT Gallery.
2. Add Gallery. Each gallery supports number of images per row, thumbnail size (customizable) and gallery name/description display.
3. Add Images. Plugin use post Featured image as gallery image. To change images order in gallery, set post date (images ordered by date).
4. Use shortcode `[cptgallery id=GALLERY_ID]` (or copy it from Galleries page) on any page/post. For Gutenberg editor, add gallery block from **SHA Plugins** group.

## Customization
1. If you want to add your own thumbnail size, use WP `add_image_size()` function.
2. To override gallery template, copy `public/templates/elements/shortcode.phtml` from plugin folder to `sha-simple-gallery/shortcode.phtml` in your theme folder.

## Filters
**sha_sgal_settings**
- Override default gallery settings.
```
$default_settings = array(
    'default'               => 4,
    'variants'              => array(
        '2' => __( '2 images', 'sha-sgal' ),
        '3' => __( '3 images', 'sha-sgal' ),
        '4' => __( '4 images', 'sha-sgal' ),
        '5' => __( '5 images', 'sha-sgal' ),
        '6' => __( '6 images', 'sha-sgal' )
    ),
    'default_thumb_size'    => 'medium',
    'default_full_size'		=> 'large',
    'load_js'				=> true,
    'load_css'				=> true,
    'fancybox_css'          => 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css',
    'fancybox_js'           => 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js'
);
```
- `default` - default amount of pictures per row
- `variants` - possible amounts of pictures per row in `amount => label` format
- `default_thumb_size` and `default_full_size` - size of the thumbnail and full picture (add your with `add_image_size()`)
- `load_js` and `loac_css` - load plugin styles and scripts (you can disable loading if you want to use them in your own files)
- `fancybox_css` and `fancybox_js` - CDN styles and script for Fancybox Lib

**sha_sgal_cpt_labels**
- Override labels in `register_post_type()`.

**sha_sgal_cpt_tax_labels**
- Override labels in `register_taxonomy()`.

**sha_gallery_block_data**
- Override data, sent to Gutenberg block.
