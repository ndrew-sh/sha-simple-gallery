<?php
/*
 * Plugin Name:       Simple CPT Gallery
 * Description:       Simple gallery, based on Custom Post Types
 * Version:           0.1.0
 * Author:            Andrew Sh
 * Text Domain:       sha-sgal
 * Domain Path:       /languages
 */

if ( !defined( 'ABSPATH' ) )  {
  exit;
}

class SHA_Simple_Gallery {

	private static $_instance;

	protected $_plugin_version = '0.1.0';

	protected $_plugin_slug = 'sgal';

	protected $_prefix = 'sgal_';

	protected $_taxonomy = 'sha-sgal';

	protected $_shortcode = 'cptgallery';

	protected $_settings = array();

    // Instance of this class
    public static function get_instance() {

        if ( !isset( self::$_instance ) ) {
            self::$_instance = new SHA_Simple_Gallery;
            self::$_instance->init();
        }

        return self::$_instance;
    }

	// Base initing function
	public function init() {

		$this->init_admin_hooks();
		$this->init_public_hooks();
	}

	// Initing all admin actions and filters
	private function init_admin_hooks() {

        $plugin_slug = $this->_plugin_slug;
        $taxonomy = $this->_taxonomy;

        add_action( 'init', array( $this, 'define_variables' ) );
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_image_size' ) );
        add_action( 'init', array( $this, 'register_gutenberg_block' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_head', array( $this, 'hide_parent_taxonomy' ) );
		add_action( 'enter_title_here', array( $this, 'change_placeholder' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		add_action( 'wp_dropdown_cats', array( $this, 'override_taxonomy_dropdown' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'gallery_filter_admin_grid' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'override_gallery_grid_columns' ), 10, 2 );
		add_action( 'manage_' . $taxonomy . '_custom_column', array( $this, 'add_shortcode_value_to_admin_grid' ), 10, 3 );
		add_action( $taxonomy . '_add_form_fields', array( $this, 'add_extra_fields_to_add_form' ) );
		add_action( $taxonomy . '_edit_form_fields', array( $this, 'add_shortcode_field_to_edit_form' ) );
		add_action( 'edited_' . $taxonomy, array( $this, 'gallery_save_controller' ) );
		add_action( 'create_' . $taxonomy, array( $this, 'gallery_save_controller' ) );
		add_action( 'manage_' . $plugin_slug . '_posts_columns', array( $this, 'add_colums_to_admin_grid' ), 10, 2 );
        add_action( 'manage_' . $plugin_slug . '_posts_custom_column', array( $this, 'columns_content_in_admin_grid' ), 10, 4 );

        add_action( 'wp_ajax_sha_simple_gallery_load_photos', array( $this, 'load_gallery_photos' ) );

		add_filter( 'parse_query', array( $this, 'add_gallery_filter' ) );
        add_filter( 'block_categories_all', array( $this, 'add_custom_block_category' ), 10, 2 );

		add_shortcode( $this->_shortcode, array( $this, 'add_shortcode' ) );
	}

	// Initing all public actions and filters
	private function init_public_hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
	}

	// ReDefine variables
	public function define_variables() {

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

		$this->_settings = $default_settings;
		$this->_settings = apply_filters( 'sha_sgal_settings', $this->_settings );
	}

	// Register custom post type and taxonomy
	public function register_cpt() {

        $plugin_slug = $this->_plugin_slug;
        $taxonomy = $this->_taxonomy;

        $labels = array(
            'name'          => __( 'CPT gallery', 'sha-sgal' ),
            'add_new'       => __( 'Add New Picture', 'sha-sgal' ),
            'add_new_item'  => __( 'Add New Picture', 'sha-sgal' ),
            'all_items'     => __( 'Gallery Pictures', 'sha-sgal' ),
            'edit_item'     => __( 'Edit Picture', 'sha-sgal' ),
            'new_item'      => __( 'New Picture', 'sha-sgal' ),
            'view_item'     => __( 'View Picture', 'sha-sgal' ),
            'search_items'  => __( 'Search Pictures', 'sha-sgal' ),
            'not_found'     => __( 'No Pictures found', 'sha-sgal' ),
        );

        // For detailed translation
        $labels = apply_filters( 'sha_sgal_cpt_labels', $labels );

        register_post_type( $plugin_slug,
			array(
                'labels'				=> $labels,
				'public'				=> true,
				'publicly_queryable'	=> false, 
				'menu_position'			=> 29,
				'supports'				=> array(
					'title',
					'excerpt',
					'thumbnail'					
				),
				'taxonomies'			=> array(
					$taxonomy,
				),
				'menu_icon'				=> 'dashicons-format-gallery',
				'has_archive'			=> false
			)
		);

		if ( ! taxonomy_exists( $taxonomy ) ) {
            $tax_labels = array(
                'name'              => __( 'Galleries', 'sha-sgal' ),
                'singular_name'     => __( 'Gallery', 'sha-sgal' ),
                'search_items'      => __( 'Search Galleries', 'sha-sgal' ),
                'all_items'         => __( 'Galleries', 'sha-sgal' ),
                'edit_item'         => __( 'Edit Gallery', 'sha-sgal' ),
                'view_item'         => __( 'View Gallery', 'sha-sgal' ),
                'add_new_item'      => __( 'Add New Gallery', 'sha-sgal' ),
                'new_item_name'     => __( 'New Gallery Name', 'sha-sgal' ),
                'not_found'         => __( 'No galleries', 'sha-sgal' ),
            );

            // For detailed translation
            $tax_labels = apply_filters( 'sha_sgal_cpt_tax_labels', $tax_labels );

			register_taxonomy(
				$taxonomy,
				$plugin_slug,
				array(
					'hierarchical'			=> true,
					'labels'				=> $tax_labels,
					'publicly_queryable'	=> false,
					'show_in_quick_edit'	=> true,
					'show_admin_column'		=> true,
				)
			);
		}
	}

	// Load textdomain
	public function load_textdomain() {

		load_plugin_textdomain( 'sha-sgal', false, basename( dirname( __FILE__, 1 ) ) . '/languages' );
	}

    // Register image size
    public function register_image_size() {
        add_image_size( 'simple-gallery', 300, 225, 1 );
        add_image_size( 'simple-gallery-admin-thumbnail', 120, 90, 1 );
    }

	// Change placeholder text
	public function change_placeholder( $title ) {

		$screen = get_current_screen();

		if  ( $this->_plugin_slug == $screen->post_type ) {
			$title = __( 'Type picture name', 'sha-sgal' );
		}

		return $title;
	}

	// Excerot text override current screen handler
	public function current_screen( $screen ) {

		if ( is_object( $screen ) && ( $screen->post_type == $this->_plugin_slug ) ) {
			add_filter( 'gettext', array( $this, 'override_excerpt_text' ), 10, 2 );
		}
	}

	// Override default excerpt labels
	public function override_excerpt_text( $translation, $original ) {

		if ( 'Excerpt' == $original ) {
			return __( 'Picture description', 'sha-sgal' );
		} else {
			$pos = strpos( $original, 'Excerpts are optional hand-crafted summaries of your' );

			if ( $pos !== false ) {
				return  __( 'Description for picture', 'sha-sgal' );
			}
		}

		return $translation;
	}

	// Override parent item dropdown to hidden field
	public function override_taxonomy_dropdown( $output, $r ) { 

		if ( $r['taxonomy'] == $this->_taxonomy ) {
			return $this->get_module_template(
				'admin/templates/elements/taxonomy_hidden.phtml'
			);
        }

        return $output; 
	}

	// Add styles to admin head to hide slug and parent item dropdown
	public function hide_parent_taxonomy() {

        $screen = get_current_screen();
        if ( $screen->taxonomy == $this->_taxonomy ) {
            echo $this->get_module_template(
                'admin/templates/elements/admin_head_styles.phtml'
            );
        }
	}

	// Change order and add extra fields in gallery grid
	public function override_gallery_grid_columns( $columns ) {

		if ( isset( $_REQUEST['taxonomy'] ) && ( $_REQUEST['taxonomy'] == $this->_taxonomy ) ) {
			return array(
				'cb'        => '<input type="checkbox" />',
				'name'      => __( 'Gallery Name', 'sha-sgal' ),
				'shortcode'	=> __( 'Gallery Shortcode', 'sha-sgal' ),
				'posts'     => __( 'Images', 'sha-sgal' )
			);
		}

		return $columns;
	}

    // Add Picture column to admin grid
    public function add_colums_to_admin_grid( $defaults ) {

        $pos = array_search( 'title', array_keys( $defaults ) ) + 1;
        $defaults = array_slice( $defaults, 0, $pos, true)
        + array( 'picture' => __('Picture', 'sha-sgal' ) )
        + array_slice( $defaults, $pos, null, true);

        return $defaults;
    }

    // Add Picture column in admin grid 
    public function columns_content_in_admin_grid( $column_name, $post_ID ) {

        // Show user id value
        if ( $column_name == 'picture' ) {
            $image_html = get_the_post_thumbnail( $post_ID, 'simple-gallery-admin-thumbnail' );
            echo $image_html ?: '&mdash;';
        }
    }

	// Output gallery shortcode in admin grid
	public function add_shortcode_value_to_admin_grid( $value, $name, $id ) {

		return 'shortcode' === $name ? '[' . $this->_shortcode . ' id=' . $id . ']' : $value;
	}

	// Add extra fields to gallery list page
	public function add_extra_fields_to_add_form() {

        $available_image_sizes = $this->get_available_image_sizes();

		echo $this->get_module_template(
			'admin/templates/elements/gallery_extra_fields.phtml',
			array(
                'variants'      => $this->_settings['variants'],
				'image_sizes'   => $available_image_sizes
			)
		);
	}

	// Add extra fields to gallery edit page
	public function add_shortcode_field_to_edit_form( $term ) {

        $available_image_sizes = $this->get_available_image_sizes();

		echo $this->get_module_template(
			'admin/templates/elements/gallery_edit_extra_fields.phtml',
			array(
                'variants'      => $this->_settings['variants'],
				't_id'          => $term->term_id,
				'term_meta'     => get_option( 'cpt_gal_' . $term->term_id ),
                'image_sizes'   => $available_image_sizes
			)
		);
	}

	// Save gallery extra field on save
	public function gallery_save_controller( $term_id ) {

		if ( isset( $_POST['term_meta'] ) ) {
			$term_meta = get_option( 'cpt_gal_' . $term_id );
			$cat_keys = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][ $key ] ) ) {
					$term_meta[$key] = sanitize_text_field( $_POST['term_meta'][ $key ] );
				}
			}

			update_option( 'cpt_gal_' . $term_id, $term_meta );
		}
	}

	// Register and output shortcode
	public function add_shortcode( $atts ) {    

		if ( isset( $atts['id'] ) ) {
            $gal_id = (int) $atts['id'];
            $pictures = $this->get_pictures_of_gallery( $gal_id );
            $gallery_settings = get_option( 'cpt_gal_' . $gal_id );
            $gallery_data = get_term( $gal_id );

            $theme_url = is_child_theme() ? get_stylesheet_directory() : get_template_directory();
            $theme_url = trailingslashit( $theme_url );

            if ( file_exists( $theme_url . basename( dirname( __FILE__, 1 ) ) . '/shortcode.phtml' ) ) {
                $template = $theme_url . basename( dirname( __FILE__, 1 ) ) . '/shortcode.phtml';
                $global = 1;
            } else {
                $template = 'frontend/templates/elements/shortcode.phtml';
                $global = 0;
			}

			return $this->get_module_template(
                $template,
                array(
                    'gallery_id'        => $gal_id,
                    'gallery_settings'  => $gallery_settings,
                    'gallery_data'      => $gallery_data,
                    'gallery_pics'      => $pictures
                ),
                $global
            );
		}
	}

	// Enqueue fancybox css/js
    public function enqueue_styles_and_scripts() {

        $timestamp = ( WP_DEBUG ) ? time() : $this->_plugin_version;

        if ( ! wp_style_is('fancybox', 'enqueued') ) {
            // Loading Fancybox files from CDN
            wp_enqueue_style(
                'fancybox',
                $this->_settings['fancybox_css'],
                array(),
                $timestamp,
                'all'
            );
        }

        if ( ! wp_script_is('fancybox', 'enqueued') ) {
            wp_enqueue_script(
                'fancybox',
                $this->_settings['fancybox_js'],
                array( 'jquery' ),
                $timestamp,
                false
            );
        }

        // Loading custom scripts and styles from plugin folder
        if ( $this->_settings['load_css'] ) {
            wp_enqueue_style(
                $this->_plugin_slug,
                plugin_dir_url( __FILE__ ) . 'frontend/css/styles.css',
                array(),
                $timestamp,
                'all'
            );
        }

        if ( $this->_settings['load_js'] ) {
            wp_enqueue_script(
                $this->_plugin_slug,
                plugin_dir_url( __FILE__ ) . 'frontend/js/scripts.js',
                array( 'jquery' ),
                $timestamp,
                false
            );
        }
	}

	// Add gallery filter to pictures admin grid
    public function gallery_filter_admin_grid() {

        $type = 'post';

        if ( isset( $_GET['post_type'] ) ) {
            $type = $_GET['post_type'];
        }

        if ( $this->_plugin_slug == $type ) {
            $terms = get_terms( $this->_taxonomy );
            echo $this->get_module_template(
                'admin/templates/elements/gallery_filter_dropdown.phtml',
                array(
                    'terms' => $terms
                )
            );
        }
	}

	// Add filter by gallery to pictures admin grid
    public function add_gallery_filter( $query ) {

        global $pagenow;

        if ( ! isset( $_GET['gal_id'] ) ) {
            return;
        }

        if ( $_GET['gal_id'] == -1 ) {
            return;
        }

        $type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';

        $qv = &$query->query_vars;

        if ( is_admin() && ( $this->_plugin_slug == $type ) && ( $pagenow == 'edit.php' ) && !empty( $_GET['gal_id'] ) ) {
            $term = get_term_by('id', $_GET['gal_id'], $this->_taxonomy );
            $qv[ $this->_taxonomy ] = $term->slug;
        }
    }

    // Add custom category to blocks list
    public function add_custom_block_category( $categories ) {

        // Check if category exist
        $exists = false;
        foreach ( $categories as $category ) {
            if ( $category['slug'] === 'sha-plugins' ) {
                $exists = true;
                break;
            }
        }

        if ( ! $exists ) {
            $custom_category = array(
                array(
                    'slug'  => 'sha-plugins',
                    'title' => __('SHA Plugins', 'sha-sgal'),
                    'icon'  => null,
                ),
            );

            array_unshift( $categories, $custom_category[0] );
        }

        return $categories;
    }


    // Register Gutenberg block
    public function register_gutenberg_block() {

        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type(
            'sha-simple-gallery/gallery',
            array(
                'attributes'        => array(
                    'selectedGallery'   => array(
                        'type'              => 'string',
                        'default'           => ''
                    )
                ),
                'editorStyle'       => 'sha-sgal-block-editor',
                'render_callback'   => array( $this, 'render_guttenberg_block' ),
            )
        );
    }

    // Guttenberg block rendered
    public function render_guttenberg_block( $attributes ) {

        if ( empty( $attributes['selectedGallery'] ) ) {
            return '';
        }

        return do_shortcode('[' . $this->_shortcode . ' id="' . (int)$attributes['selectedGallery'] . '"]');        
    }

    // Equeueing block assets and passing data to js
    public function enqueue_block_editor_assets() {

        $timestamp = ( WP_DEBUG ) ? time() : $this->_plugin_version;

        wp_enqueue_script(
            'sha-sgal-block',
            plugins_url( 'blocks/gallery/index.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
            $timestamp,
            true
        );

        // Getting all galleries
        $galleries = array();

        $terms = get_terms(
            array(
                'taxonomy'      => $this->_taxonomy,
                'hide_empty'    => false
            )
        );

        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $term_meta = get_option( 'cpt_gal_' . $term->term_id );

                $galleries['terms'][] = array(
                    'id'    => $term->term_id,
                    'name'  => $term->name
                );

                $galleries['per_row'][ $term->term_id ] = $term_meta ? $term_meta['cpt_cols'] : $this->_settings['default'];
            }
        }

        $galleries['nonce'] = wp_create_nonce('sha_sgal_nonce');

        $galleries = apply_filters( 'sha_gallery_block_data', $galleries );

        // Passing data to JS
        wp_localize_script(
            'sha-sgal-block',
            'SHA_GALLERY',
            $galleries
        );

        // Add custom editor CSS 
        wp_register_style(
            'sha-sgal-block-editor',
            plugins_url( 'blocks/gallery/editor.css', __FILE__ ),
            array('wp-edit-blocks'),
            $timestamp
        );        
        wp_enqueue_style('sha-sgal-block-editor');
    }

    // AJAX handler for galery photos
    public function load_gallery_photos() {

        // Check nonce
        check_ajax_referer( 'sha_sgal_nonce', 'nonce' );

        // Check user rights
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $gallery_id = (int) ( $_POST['gallery_id'] ?? 0 );
        
        if ( ! $gallery_id ) {
            wp_send_json_error('Empty gallery ID');
        }

        $photos = $this->get_pictures_of_gallery( $gallery_id );

        wp_send_json_success( $photos );
    }

    // Get available image sizes
    private function get_available_image_sizes() {

        $available_image_sizes = array();

        // For compatibility with WP < 5.3 versions
        if ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
            $available_image_sizes = wp_get_registered_image_subsizes();
        } else {
            $existing_image_sizes = get_intermediate_image_sizes();
            foreach ( $existing_image_sizes as $size ) {
                $available_image_sizes[ $size ] = array(
                    'width'     => get_option( $size . '_size_w' ),
                    'height'    => get_option( $size . '_size_h' )
                );
            }
        }

        return $available_image_sizes;
    }

    // Get pictures by gallery_id
    private function get_pictures_of_gallery( $gallery_id ) {

        $gallery_settings = get_option( 'cpt_gal_' . $gallery_id );
        $thumb_size = isset( $gallery_settings['cpt_thumb_size'] ) ? $gallery_settings['cpt_thumb_size'] : $this->_settings['default_thumb_size'];

        $gallery_post_ids = get_posts(
            array(
                'post_type'   => $this->_plugin_slug,
                'tax_query'   => array(
                    array(
                        'taxonomy' => $this->_taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $gallery_id,
                    ),
                ),
                'numberposts' => -1,
                'fields'      => 'ids',
            )
        );

        $pictures = array();

        foreach ( $gallery_post_ids as $post_id ) {

            $thumb_id = get_post_thumbnail_id( $post_id );
            $thumb_data = wp_get_attachment_image_src( $thumb_id, $thumb_size );
            $large_data = wp_get_attachment_image_src( $thumb_id, $this->_settings['default_full_size'] );

            if ( $thumb_data && $large_data ) {
                $pictures[] = array(
                    'id'      => $post_id,
                    'thumb'   => $thumb_data[0],
                    'large'   => $large_data[0],
                    'title'   => get_the_title( $post_id ),
                    'caption' => get_the_excerpt( $post_id ),
                );
            }
        }

        return $pictures;
    }

	// Get template and output it's html
	private function get_module_template( $template, $args = array(), $global_template = 0 ) {

        if ( $global_template == 1 ) {
            $template_file = $template;
        } else {
            $template_file = sprintf(
                '%s%s',
                plugin_dir_path( __FILE__ ),
                $template
            );
        }

        if ( ! file_exists( $template_file ) ) {
            return '';
        }

        $args['module_slug'] = $this->_plugin_slug;
        extract( $args );

        ob_start();
        require( $template_file );
        return ob_get_clean();
	}
}

// Init module instance
function init_wc_sgal_module() {

    return SHA_Simple_Gallery::get_instance();
}

add_action( 'plugins_loaded', 'init_wc_sgal_module', 100 );
