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
	 * Zoho's own post-submission/post-booking redirects navigate *within
	 * whatever iframe is showing Zoho's content*, not the top-level page —
	 * so landing on one of our own pages (e.g. /booking or /thank-you)
	 * would otherwise render nested inside that small embed instead of
	 * taking over the whole tab. Same-origin policy blocks reading the
	 * iframe's location while it's still showing Zoho's domain (the
	 * try/catch below just swallows that, silently, on every load until it
	 * changes) — but once Zoho's redirect lands the iframe on our own
	 * domain, reading it succeeds, and that's our signal to force a real
	 * top-level navigation to the same URL. Shared by the Get Started form
	 * iframes and the Zoho Bookings widget on /booking.
	 */
	function kaligirlBreakoutIframeOnSameOrigin( iframe ) {
		iframe.addEventListener( 'load', function () {
			try {
				var landedUrl = iframe.contentWindow.location.href;
				window.top.location.href = landedUrl;
			} catch ( e ) {
				// Still cross-origin (on Zoho's domain) — expected; ignore.
			}
		} );
	}

	/**
	 * Zoho Bookings' inlineEmbed() injects its own iframe(s) into the
	 * target container asynchronously (and possibly more than once across
	 * a multi-step booking flow), so we can't just grab one iframe once —
	 * watch the container and apply the breakout above to whatever shows
	 * up, for as long as the container exists.
	 */
	function kaligirlWatchEmbedContainerForIframes( container ) {
		if ( ! container ) {
			return;
		}

		container.querySelectorAll( 'iframe' ).forEach( kaligirlBreakoutIframeOnSameOrigin );

		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					if ( node.tagName === 'IFRAME' ) {
						kaligirlBreakoutIframeOnSameOrigin( node );
					}
					if ( node.querySelectorAll ) {
						node.querySelectorAll( 'iframe' ).forEach( kaligirlBreakoutIframeOnSameOrigin );
					}
				} );
			} );
		} );
		observer.observe( container, { childList: true, subtree: true } );
	}

	/**
	 * Fallback for when the iframe-breakout trick above finds nothing to
	 * attach to — plausible if Bookings.inlineEmbed() renders its calendar
	 * as same-origin DOM content rather than a true cross-origin iframe
	 * ("inline embed" as opposed to a sandboxed iframe embed), in which
	 * case there's no iframe boundary at all for that trick to detect.
	 *
	 * The standard mechanism for an embedded widget to notify its host
	 * page of an event like "booking completed" is window.postMessage() —
	 * this listens for any message from a Zoho domain and:
	 *   1. Always logs it to the console, so a real test run reveals the
	 *      exact shape Zoho actually sends (open dev tools, complete a
	 *      booking, check the Console tab, report back what's there).
	 *   2. Best-effort redirects to /thank-you if the message looks like a
	 *      success/completion signal — a broad keyword match, since the
	 *      real field/event name isn't confirmed (Zoho's docs blocked every
	 *      fetch attempt). Tighten this once the logged shape is known.
	 */
	function kaligirlListenForBookingComplete() {
		window.addEventListener( 'message', function ( event ) {
			if ( ! /zohobookings\.com|nimbuspop\.com/.test( event.origin ) ) {
				return;
			}

			// eslint-disable-next-line no-console
			console.log( 'Zoho Bookings postMessage:', event.origin, event.data );

			var raw = event.data;
			var text = typeof raw === 'string' ? raw : ( function () {
				try {
					return JSON.stringify( raw );
				} catch ( e ) {
					return '';
				}
			} )();

			if ( /book(ed|ing).*(success|complet|confirm)|success.*book|appointment.*(confirm|schedul)/i.test( text ) ) {
				window.top.location.href = '/thank-you';
			}
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
				kaligirlBreakoutIframeOnSameOrigin( iframe );
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

	/**
	 * Services/Resources mega menus: hover to open, with a short close-delay
	 * on mouse-leave (matches the design spec's 120ms debounce) so moving
	 * the cursor from the nav link down into the panel doesn't flicker-close
	 * it. Each trigger/panel pair is matched by a shared data-kg-mega-* key.
	 */
	function kaligirlInitMegaMenus() {
		var triggers = document.querySelectorAll( '[data-kg-mega-trigger]' );
		if ( ! triggers.length ) {
			return;
		}

		var closeTimers = {};

		function openMenu( key ) {
			window.clearTimeout( closeTimers[ key ] );
			document.querySelectorAll( '[data-kg-mega-panel]' ).forEach( function ( panel ) {
				if ( panel.getAttribute( 'data-kg-mega-panel' ) !== key ) {
					panel.classList.remove( 'is-open' );
				}
			} );
			var panel = document.querySelector( '[data-kg-mega-panel="' + key + '"]' );
			if ( panel ) {
				panel.classList.add( 'is-open' );
			}
		}

		function scheduleClose( key ) {
			closeTimers[ key ] = window.setTimeout( function () {
				var panel = document.querySelector( '[data-kg-mega-panel="' + key + '"]' );
				if ( panel ) {
					panel.classList.remove( 'is-open' );
				}
			}, 120 );
		}

		triggers.forEach( function ( trigger ) {
			var key = trigger.getAttribute( 'data-kg-mega-trigger' );
			var panel = document.querySelector( '[data-kg-mega-panel="' + key + '"]' );

			trigger.addEventListener( 'mouseenter', function () {
				openMenu( key );
			} );
			trigger.addEventListener( 'mouseleave', function () {
				scheduleClose( key );
			} );

			if ( panel ) {
				panel.addEventListener( 'mouseenter', function () {
					openMenu( key );
				} );
				panel.addEventListener( 'mouseleave', function () {
					scheduleClose( key );
				} );
			}
		} );
	}

	/**
	 * Personal/Business consulting pages: a grid of topic cards that
	 * expands in place into a single detail card on click — no page
	 * navigation, just show/hide state, matching the design's
	 * grid <-> selectedCard interaction. Generic over both pages: each
	 * detail panel's data-kg-consulting-detail index matches the card that
	 * opens it.
	 */
	function kaligirlInitConsultingCards() {
		var gridWrap = document.querySelector( '[data-kg-consulting="grid"]' );
		if ( ! gridWrap ) {
			return; // Not on a consulting page.
		}

		var cards = document.querySelectorAll( '[data-kg-consulting-card]' );
		var details = document.querySelectorAll( '[data-kg-consulting-detail]' );

		function showDetail( index ) {
			gridWrap.hidden = true;
			details.forEach( function ( detail ) {
				detail.hidden = detail.getAttribute( 'data-kg-consulting-detail' ) !== index;
			} );
		}

		function showGrid() {
			gridWrap.hidden = false;
			details.forEach( function ( detail ) {
				detail.hidden = true;
			} );
		}

		cards.forEach( function ( card ) {
			var index = card.getAttribute( 'data-kg-consulting-card' );
			card.addEventListener( 'click', function () {
				showDetail( index );
			} );
			card.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					showDetail( index );
				}
			} );
		} );

		document.querySelectorAll( '[data-kg-consulting-back]' ).forEach( function ( button ) {
			button.addEventListener( 'click', showGrid );
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
		kaligirlInitMegaMenus();
		kaligirlInitConsultingCards();

		// /booking's Zoho Bookings widget (page-booking.php) — same
		// iframe-containment problem as the Get Started forms: a completed
		// booking's redirect to /thank-you would otherwise render nested
		// inside this small embed instead of taking over the tab. Runs
		// both: the iframe-breakout trick in case Bookings does use a
		// nested iframe, and the postMessage listener in case it doesn't
		// (see kaligirlListenForBookingComplete() for why both exist).
		if ( document.getElementById( 'inline-container' ) ) {
			kaligirlWatchEmbedContainerForIframes( document.getElementById( 'inline-container' ) );
			kaligirlListenForBookingComplete();
		}
	} );
} )();
