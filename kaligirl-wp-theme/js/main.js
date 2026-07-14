/**
 * Header interactions: mobile menu toggle and the Resources dropdown.
 * Everything else is real page navigation (WordPress permalinks), so there
 * is no client-side routing here — only the UI toggle state the design
 * spec calls out as staying client-side JS.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var mobileToggle = document.querySelector( '[data-kg-mobile-toggle]' );
		var mobileMenu = document.querySelector( '[data-kg-mobile-menu]' );

		if ( mobileToggle && mobileMenu ) {
			mobileToggle.addEventListener( 'click', function () {
				var isOpen = mobileMenu.classList.toggle( 'is-open' );
				mobileToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );
		}

		var resourcesToggle = document.querySelector( '[data-kg-resources-toggle]' );
		var resourcesMenu = document.querySelector( '[data-kg-resources-menu]' );

		if ( resourcesToggle && resourcesMenu ) {
			resourcesToggle.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				var isOpen = resourcesMenu.classList.toggle( 'is-open' );
				resourcesToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );

			// Close on outside click, and on selecting a link (per spec).
			document.addEventListener( 'click', function ( e ) {
				if ( ! resourcesMenu.contains( e.target ) && e.target !== resourcesToggle ) {
					resourcesMenu.classList.remove( 'is-open' );
					resourcesToggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );
			resourcesMenu.addEventListener( 'click', function ( e ) {
				if ( e.target.tagName === 'A' ) {
					resourcesMenu.classList.remove( 'is-open' );
					resourcesToggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		}

		// Footer "Form ADV / Disclosures" is a stub pending a real destination
		// (see footer.php / README "Compliance placeholders").
		var advStub = document.querySelector( '[data-kg-adv-stub]' );
		if ( advStub ) {
			advStub.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				window.alert( 'This link is a placeholder pending compliance-approved content.' );
			} );
		}
	} );
} )();
