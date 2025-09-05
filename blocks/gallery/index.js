(function(blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType('sha-simple-gallery/gallery', {
        title: __('Simple Gallery', 'sha-sgal'),
        description: __('Visual Simple Gallery insert', 'sha-sgal'),
        icon: 'format-gallery',
        category: 'sha-plugins',
        attributes: {
            selectedGallery: { type: 'string', default: '' },
            photos: { type: 'array', default: [] },
            isPreview: { type: 'boolean', default: false }
        },
        example: {
            attributes: { isPreview: true },
        },

        edit: function(props) {
            var blockProps = useBlockProps();
            var selectedId = props.attributes.selectedGallery;

            // Prepare select options: default empty + all registered galleries
            var options = [{ id: '', name: __('— Choose gallery —', 'sha-sgal') }]
                .concat(SHA_GALLERY.terms || []);

            // Triggered when gallery is selected
            function onGalleryChange(e) {
                var galleryId = e.target.value;
                props.setAttributes({ selectedGallery: galleryId, photos: [] });

                if (!galleryId) return;

                // Show temporary "Loading…" item
                props.setAttributes({
                    photos: [{ id: 'loading', thumb: '', title: __('Loading…', 'sha-sgal') }]
                });

                // AJAX request to load gallery photos
                jQuery.post(
                    ajaxurl,
                    {
                        action: 'sha_simple_gallery_load_photos',
                        gallery_id: galleryId,
                        nonce: SHA_GALLERY.nonce
                    },
                    function(response) {
                        if (response.success) {
                            props.setAttributes({ photos: response.data });
                        } else {
                            // Show error if something goes wrong
                            props.setAttributes({
                                photos: [{ id: 'error', thumb: '', title: __('Error loading', 'sha-sgal') }]
                            });
                        }
                    }
                );
            }

            // Render photos inside <ul>
            var photoElements = (props.attributes.photos || []).map(function(photo) {
                if (photo.id === 'loading' || photo.id === 'error') {
                    return el('li', {
                        key: photo.id,
                        style: {
                            listStyle: 'none',
                            textAlign: 'center',
                            width: '100%'
                        }
                    }, el('span', {}, photo.title));
                }

                return el('li', { key: photo.id },
                    el('img', { src: photo.thumb, title: photo.title })
                );
            });

            // Block preview (appears in the block inserter library)
            if (props.attributes.isPreview) {
                var imageSvgIcon = el('svg', { width: 100, height: 100, viewBox: '0 0 256 256' },
                    el('path', {
                        d: 'M21.1,246h213.9c6.1,0,11.1-5,11.1-11.1V21c0-6.1-5-11.1-11.1-11.1H21.1C15,10,10,14.9,10,21v213.9C10,241.1,15,246,21.1,246z M17.4,21c0-2,1.7-3.7,3.7-3.7h213.9c2,0,3.7,1.7,3.7,3.7v213.9c0,2-1.7,3.7-3.7,3.7H21.1c-2,0-3.7-1.7-3.7-3.7V21z M161.2,102.2c8.1,0,14.8-6.6,14.8-14.8s-6.6-14.8-14.8-14.8c-8.1,0-14.8,6.6-14.8,14.8S153.1,102.2,161.2,102.2z M161.2,80c4.1,0,7.4,3.3,7.4,7.4s-3.3,7.4-7.4,7.4s-7.4-3.3-7.4-7.4S157.1,80,161.2,80z M43.2,194.4h169.6c2,0,3.7-1.7,3.7-3.7V43.2c0-2-1.7-3.7-3.7-3.7H43.2c-2,0-3.7,1.7-3.7,3.7v147.5C39.5,192.7,41.2,194.4,43.2,194.4z M46.9,187v-41.6c0.2-0.1,0.3-0.2,0.5-0.3l37.7-37.7c1.9-1.9,5.3-1.9,7.2,0l55.5,55.5c0.7,0.7,1.7,1.1,2.6,1.1c0.9,0,1.8-0.3,2.6-1l28.5-27.1c1-1,2.2-1.5,3.6-1.5c1.3,0,2.6,0.5,3.5,1.4l20.7,22.5l0,0V187H46.9L46.9,187z M209.2,46.9v100.5l-15.3-16.7c-2.3-2.4-5.5-3.7-8.8-3.7l0,0c-3.3,0-6.5,1.3-8.7,3.6l-25.9,24.5l-52.9-52.9c-4.7-4.7-12.9-4.7-17.6,0l-33,33V46.9H209.2L209.2,46.9z',
                        fill: '#000000'
                    })
                );

                return el('div', {
                    style: {
                        display: 'grid',
                        gridTemplateColumns: 'repeat(4, 1fr)',
                        gap: '10px',
                        justifyItems: 'center',
                        width: '100%'
                    }
                }, imageSvgIcon, imageSvgIcon, imageSvgIcon, imageSvgIcon);
            }

            // Block edit UI
            return el('div', blockProps,
                // Show select in editor only if gallery is not chosen yet
                !selectedId && el('div', { style: { textAlign: 'center', marginBottom: '10px', paddingBottom: '10px' } },
                    el('p', {}, __( 'Simple Gallery', 'sha-sgal' )),
                    el('select', {
                        value: selectedId,
                        onChange: onGalleryChange,
                        style: { width: '100%' }
                    },
                        options.map(function(opt) {
                            return el('option', { value: opt.id, key: opt.id }, opt.name);
                        })
                    )
                ),

                // Right sidebar settings (InspectorControls)
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Gallery to show', 'sha-sgal'), initialOpen: true },
                        el('select', {
                            value: selectedId,
                            onChange: onGalleryChange,
                            style: { width: '100%' }
                        },
                            options.map(function(opt) {
                                return el('option', { value: opt.id, key: opt.id }, opt.name);
                            })
                        )
                    )
                ),

                // Preview selected gallery inside the editor
                selectedId && el('div', { class: 'sha-sgal-wrapper' },
                    el('ul', { class: 'items-' + SHA_GALLERY.per_row[selectedId] }, photoElements)
                )
            );
        },

        // Save is null, because frontend render is handled by PHP render_callback
        save: function() {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
