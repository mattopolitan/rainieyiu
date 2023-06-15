( function( api ) {

	// Extends our custom "mega-store-woocommerce" section.
	api.sectionConstructor['mega-store-woocommerce'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );