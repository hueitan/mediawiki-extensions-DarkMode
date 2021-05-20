$( function () {
	const api = new mw.Api();
	let isLoading = false;
	var darkMode = document.documentElement.classList.contains( 'client-dark-mode' );

	function updatetext() {
		$( '.darkmode-link' ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	}

	function updatingText() {
		$( '.darkmode-link' ).text( 'Updating...' )
		isLoading = true;
	}

	$( updatetext() );
	$( '.darkmode-link, .darkmode-link-mobilemenu' ).on( 'click', function ( e ) {
		e.preventDefault();

		if ( isLoading ) return;

		darkMode = !darkMode;

		updatingText();

		api.saveOption( 'darkmode', darkMode ? 1 : 0 ).then( () => {
			$( document.documentElement ).toggleClass( 'client-dark-mode', darkMode );
			updatetext();
			isLoading = false;
		});
	} );
} );
