( function( blocks, i18n, element, components, editor ) {
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var SelectControl = components.SelectControl;

    blocks.registerBlockType( 'location-map/block', {
        title: 'Location Map',
        icon: 'location',
        category: 'widgets',
        attributes: {
            locationID: {
                type: 'number'
            }
        },
        edit: function( props ) {
            var locationID = props.attributes.locationID;

            // Use localized locations data (passed from PHP).
            var locations = window.lmLocations || [];
            var options = [
                { label: 'Select a Location', value: 0 }
            ];
            locations.forEach( function( location ) {
                options.push( {
                    label: location.title,
                    value: location.id
                } );
            } );

            return [
                el( InspectorControls, {},
                    el( 'div', { className: 'lm-inspector-control' },
                        el( SelectControl, {
                            label: 'Location',
                            value: locationID,
                            options: options,
                            onChange: function( newVal ) {
                                props.setAttributes( { locationID: parseInt( newVal ) } );
                            }
                        } )
                    )
                ),
                el( 'div', { className: 'lm-block-preview' },
                    locationID ? el( 'p', {}, 'Location ID: ' + locationID ) : el( 'p', {}, 'No location selected.' )
                )
            ];
        },
        save: function() {
            // Rendered on the server via PHP.
            return null;
        }
    } );
} )(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window.wp.editor
);
