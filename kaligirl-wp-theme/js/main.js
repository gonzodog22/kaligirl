/**
 * Header interactions: mobile menu toggle and the Resources dropdown.
 * Everything else is real page navigation (WordPress permalinks), so there
 * is no client-side routing here — only the UI toggle state the design
 * spec calls out as staying client-side JS.
 */
( function () {
	'use strict';

	// Get Started intake form — replace with your real intake webhook
	// (Zapier/Make/n8n/etc. catch hook) before launch. The form POSTs the
	// submitted JSON here directly from the browser; nothing server-side
	// in WordPress sees this data.
	var KALIGIRL_INTAKE_WEBHOOK_URL = 'https://example.com/PLACEHOLDER-intake-webhook';

	// Fields belonging to each conditional branch, keyed by the value of
	// the "What brings you here?" select. Only the active branch's fields
	// are included in the submitted payload.
	var KALIGIRL_INTAKE_BRANCH_FIELDS = {
		personal: [ 'personalGoal', 'workedWithAdvisor', 'preferredContact' ],
		business: [ 'businessName', 'businessWhat', 'accountingSystem', 'businessIssue' ]
	};

	/**
	 * Reads an element's native constraint-validation state and returns a
	 * friendly inline message, or '' if the field is valid. Relies on the
	 * browser's built-in validity checks (required, type="email") rather
	 * than reimplementing them, so the form stays novalidate + fully
	 * custom-rendered without duplicating HTML5's own rules.
	 */
	function kaligirlFieldError( el ) {
		if ( el.disabled || el.closest( '[hidden]' ) ) {
			return ''; // Fields in a hidden branch are never validated.
		}
		var validity = el.validity;
		if ( validity.valid ) {
			return '';
		}
		if ( validity.valueMissing ) {
			return el.type === 'checkbox' ? 'Please check this box to continue.' : 'This field is required.';
		}
		if ( validity.typeMismatch && el.type === 'email' ) {
			return 'Please enter a valid email address.';
		}
		return 'Please check this field.';
	}

	function kaligirlShowFieldError( el, message ) {
		var errorEl = document.getElementById( el.id + '-error' );
		if ( message ) {
			el.setAttribute( 'aria-invalid', 'true' );
			if ( errorEl ) {
				errorEl.textContent = message;
				errorEl.hidden = false;
			}
		} else {
			el.removeAttribute( 'aria-invalid' );
			if ( errorEl ) {
				errorEl.textContent = '';
				errorEl.hidden = true;
			}
		}
	}

	function kaligirlInitIntakeForm() {
		var form = document.getElementById( 'kg-intake-form' );
		if ( ! form ) {
			return;
		}

		var segmentSelect = document.getElementById( 'kg-segment' );
		var branches = form.querySelectorAll( '[data-kg-branch]' );
		var submitButton = document.getElementById( 'kg-intake-submit' );
		var statusEl = document.getElementById( 'kg-intake-status' );
		var honeypotField = form.querySelector( 'input[name="kg_hp_get_started"]' );

		function showBranch( segment ) {
			branches.forEach( function ( branch ) {
				branch.hidden = branch.getAttribute( 'data-kg-branch' ) !== segment;
			} );
		}

		segmentSelect.addEventListener( 'change', function () {
			showBranch( segmentSelect.value );
			kaligirlShowFieldError( segmentSelect, kaligirlFieldError( segmentSelect ) );
		} );

		// Clear an error as soon as the field becomes valid again, rather
		// than waiting for the next submit attempt.
		form.querySelectorAll( 'input[required], select[required], textarea[required]' ).forEach( function ( el ) {
			var eventName = ( el.tagName === 'SELECT' || el.type === 'checkbox' ) ? 'change' : 'input';
			el.addEventListener( eventName, function () {
				if ( el.getAttribute( 'aria-invalid' ) === 'true' ) {
					kaligirlShowFieldError( el, kaligirlFieldError( el ) );
				}
			} );
		} );

		function setStatus( kind, message ) {
			statusEl.hidden = false;
			statusEl.className = 'form-status form-status--' + kind;
			statusEl.textContent = message;
		}

		function clearStatus() {
			statusEl.hidden = true;
			statusEl.textContent = '';
			statusEl.className = 'form-status';
		}

		function validateForm() {
			var fieldsToCheck = form.querySelectorAll( 'input[required], select[required], textarea[required]' );
			var firstInvalid = null;

			fieldsToCheck.forEach( function ( el ) {
				var message = kaligirlFieldError( el );
				kaligirlShowFieldError( el, message );
				if ( message && ! firstInvalid ) {
					firstInvalid = el;
				}
			} );

			if ( firstInvalid ) {
				firstInvalid.focus();
				return false;
			}
			return true;
		}

		function buildPayload() {
			var segment = segmentSelect.value;
			var payload = {
				segment: segment,
				firstName: form.elements.firstName.value.trim(),
				lastName: form.elements.lastName.value.trim(),
				email: form.elements.email.value.trim(),
				phone: form.elements.phone.value.trim(),
				referral: form.elements.referral.value.trim(),
				consent: form.elements.consent.checked
			};

			var branchFieldNames = KALIGIRL_INTAKE_BRANCH_FIELDS[ segment ] || [];
			branchFieldNames.forEach( function ( name ) {
				var el = form.elements[ name ];
				if ( el ) {
					payload[ name ] = el.value.trim();
				}
			} );

			return payload;
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			clearStatus();

			// Honeypot: bots that fill this hidden field get no feedback
			// either way — just drop the submission silently.
			if ( honeypotField && honeypotField.value ) {
				return;
			}

			if ( ! validateForm() ) {
				setStatus( 'error', 'Please fix the highlighted fields and try again.' );
				return;
			}

			var payload = buildPayload();

			submitButton.disabled = true;
			submitButton.textContent = 'Sending…';

			fetch( KALIGIRL_INTAKE_WEBHOOK_URL, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( payload )
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Request failed with status ' + response.status );
					}
					form.reset();
					showBranch( '' );
					setStatus( 'success', "Thanks — we've got your info and will be in touch shortly." );
				} )
				.catch( function () {
					setStatus( 'error', "Something went wrong sending that — please try again, or email us directly if it keeps happening." );
				} )
				.finally( function () {
					submitButton.disabled = false;
					submitButton.textContent = 'Send introduction request';
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

		kaligirlInitIntakeForm();
	} );
} )();
