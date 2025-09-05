## Simple image gallery based on Custom Post Types/Taxonomy and Fancybox

### Description
This plugin allow to create simple gallery and output it with shortcode.

### Usage
1. Download archive and unpack/or clone into plugins directory.
2. Activate.
3. Under admin area, go to CPT Gallery.
4. Add Gallery. Each gallery supports number of images per row, thumbnail size (customizable) and gallery name/description display.
5. Add Images. Plugin use post Featured image as gallery image. To change images order in gallery, set post date (images ordered by date).
4. Use shortcode `[cptgallery id=GALLERY_ID]` (or copy it from Galleries page) on any page/post.

### Customize
1. Use `sha_sgal_settings` filter to override default settings (images per row, default thumbnail size and default full image size).
````
add_filter( 'sha_sgal_settings', 'sha_sgal_override_settings', 10 );

function sha_sgal_override_settings( $settings ) {

  unset( $settings['variants'][2] );
  unset( $settings['variants'][3] );
  unset( $settings['variants'][6] );

  return $settings;
}

````
2. If you want to add your own thumbnail size, use WP `add_image_size()` function.
3. To override gallery template, copy `public/templates/elements/shortcode.phtml` to `sha-simple-gallery/shortcode.phtml` in your theme folder.

### Initing and styling gallery
By default, gallery initing in `public/js/scripts.js`, which enqueued with `wp_enqueue_scripts` action. If you want to init gallery in your own script or init it with an extra params or style gallery with your style, you can disable loading scripts and styles in plugin settings. Use `sha_sgal_settings` filter and set `load_js` and `load_css` variables to `false`.
