/**
 * Header interactions: mobile menu toggle and the Resources dropdown.
 * Everything else is real page navigation (WordPress permalinks), so there
 * is no client-side routing here — only the UI toggle state the design
 * spec calls out as staying client-side JS.
 */
( function () {
	'use strict';

	/**
	 * RFC 4122-ish fallback for browsers without crypto.randomUUID (older
	 * Safari). The token's real security property comes from the server-side
	 * Zoho Creator lookup in inc/zoho.php, not from this generator alone.
	 */
	function kaligirlGenerateToken() {
		if ( window.crypto && typeof window.crypto.randomUUID === 'function' ) {
			return window.crypto.randomUUID();
		}
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace( /[xy]/g, function ( c ) {
			var r = ( Math.random() * 16 ) | 0;
			var v = c === 'x' ? r : ( r & 0x3 ) | 0x8;
			return v.toString( 16 );
		} );
	}

	/**
	 * Get Started fork page: Personal/Business toggle, Route One/Two form
	 * reveal, and one-time gate-token generation per route — appended to
	 * each Zoho Forms iframe's src, and carried into a fallback "Continue"
	 * link for /booking or /payment in case Zoho's own redirect-on-submission
	 * setting can't forward the token dynamically (see page-get-started.php
	 * header comment — this needs confirming in Zoho Forms' own settings).
	 */
	function kaligirlInitGetStarted() {
		var forkView = document.querySelector( '[data-kg-view="fork"]' );
		if ( ! forkView ) {
			return; // Not on the Get Started page.
		}

		var routeOneView = document.querySelector( '[data-kg-view="route-one"]' );
		var routeTwoView = document.querySelector( '[data-kg-view="route-two"]' );
		var allViews = [ forkView, routeOneView, routeTwoView ];

		function showView( view ) {
			allViews.forEach( function ( el ) {
				if ( el ) {
					el.hidden = el !== view;
				}
			} );
		}

		// Personal/Business toggle only controls whether the Personal-only
		// "Find the Right Plan" button is visible — Route Two's button is
		// always visible in both states.
		var modeRadios = document.querySelectorAll( 'input[name="kg-advising-mode"]' );
		var personalOnlyEls = document.querySelectorAll( '[data-kg-personal-only]' );

		function applyMode() {
			var checked = document.querySelector( 'input[name="kg-advising-mode"]:checked' );
			var isPersonal = ! checked || checked.value === 'personal';
			personalOnlyEls.forEach( function ( el ) {
				el.hidden = ! isPersonal;
			} );
		}

		modeRadios.forEach( function ( radio ) {
			radio.addEventListener( 'change', applyMode );
		} );
		applyMode();

		/**
		 * Generates (or reuses, if this route's view was already opened once
		 * this session) a gate token for one route, sets it on that route's
		 * iframe src, and points its fallback "Continue" link at the right
		 * destination with that same token.
		 */
		function loadRoute( routeKey, view ) {
			if ( ! view || view.dataset.kgLoaded ) {
				return;
			}
			view.dataset.kgLoaded = 'true';

			var storageKey = 'kg_gate_token_' + routeKey;
			var token = window.sessionStorage.getItem( storageKey );
			if ( ! token ) {
				token = kaligirlGenerateToken();
				window.sessionStorage.setItem( storageKey, token );
			}

			var iframe = view.querySelector( 'iframe[data-kg-form-src]' );
			if ( iframe ) {
				var baseSrc = iframe.getAttribute( 'data-kg-form-src' );
				iframe.src = baseSrc + '?gated_token=' + encodeURIComponent( token );

				// Zoho's own "Redirect URL on Submission" navigates *within
				// the iframe's own frame*, not the top-level page — so after
				// a real submission, our /booking or /payment page would
				// otherwise render nested inside this small form iframe
				// instead of taking over the whole tab. Same-origin policy
				// blocks us from reading the iframe's location while it's
				// still showing Zoho's domain (the try/catch below just
				// swallows that, silently, every load until it changes) —
				// but once Zoho's redirect lands the iframe on our own
				// domain, reading it succeeds, and that's our signal to
				// force a full top-level navigation to the same URL.
				iframe.addEventListener( 'load', function () {
					try {
						var landedUrl = iframe.contentWindow.location.href;
						window.top.location.href = landedUrl;
					} catch ( e ) {
						// Still cross-origin (on Zoho's domain) — expected; ignore.
					}
				} );
			}

			var continueLink = view.querySelector( '[data-kg-continue-link]' );
			if ( continueLink ) {
				var destination = continueLink.getAttribute( 'data-kg-destination' );
				continueLink.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					window.location.href = destination + '?token=' + encodeURIComponent( token );
				} );
			}
		}

		document.querySelectorAll( '[data-kg-route-button]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var route = button.getAttribute( 'data-kg-route-button' );
				var view = route === 'one' ? routeOneView : routeTwoView;
				loadRoute( route, view );
				showView( view );
			} );
		} );

		document.querySelectorAll( '[data-kg-back-to-fork]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				showView( forkView );
			} );
		} );
	}

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

		kaligirlInitGetStarted();
	} );
} )();
