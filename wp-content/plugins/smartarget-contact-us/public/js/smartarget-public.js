(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    window.onload =  function ()
    {
		if (smartarget_params.smartarget_user_id)
		{
			init_old(smartarget_params.smartarget_user_id);
		}
		else if (smartarget_params.smartarget_integration_hash)
		{
			init_new(smartarget_params.smartarget_integration_hash);
		}
    };

    function init_old (idUser)
    {
        if (!idUser)
        {
            return;
        }

		insertJs_old(idUser);
    }

    function insertJs_old (idUser)
    {
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = `https://smartarget.online/loader.js?ver=9321871&u=${idUser}&source=wordpress_smartarget`;
        document.head.appendChild(script);
    }

	function init_new (hash)
	{
		if (!hash)
		{
			return;
		}

		insertJs_new(hash);
	}

	function insertJs_new (hash)
	{
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = `https://smartarget.online/loader.js?ver=${Math.random()}&u=${hash}&source=wordpress_all`;
		document.head.appendChild(script);
	}

})( jQuery );
