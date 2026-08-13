<?php
/**
 * Template Name: Get Started
 *
 * Fork page per the Get Started migration handoff (supersedes the earlier
 * Moxo iframe entirely — do not reintroduce it). A Personal/Business
 * sliding toggle controls which of two buttons are visible.
 *
 * - "Schedule an Introductory Consultation" (Route Two) — always visible,
 *   both toggle states — is booking-first: clicking it shows a Zoho
 *   Bookings widget (Personal or Business, per the toggle) directly on
 *   this page, not a form. Once that booking completes, Zoho's own
 *   redirect lands back on this exact URL with customer_* and
 *   service_uuid params, which flips $kg_from_booking to true and swaps the booking
 *   widget for the matching Zoho Form (Personal "GetStarted" or Business
 *   "LetsreviewyourfinancestogetherBusiness") to collect the rest of the
 *   questions — gated by a one-time token generated client-side
 *   (js/main.js) and appended to that form's iframe src, which then
 *   submits to /booking?token=...&flow=...
 * - "Find the Right Plan" (Route One) — visible only in "Personal
 *   Advising" mode — still forms-first, unrelated to the above — submits
 *   to /payment?token=... on completion.
 *
 * The actual security boundary is NOT this page or the token generator —
 * it's the server-side check in /booking and /payment (page-booking.php,
 * page-payment.php via inc/zoho.php), which requires a matching token
 * record in Zoho Creator with status "used". That status only gets set by
 * a real Zoho Forms submission (on Zoho's side), so guessing or
 * hand-crafting a token client-side doesn't get anyone in — there's
 * nothing for an attacker to reach without a genuine submission having
 * already happened. This page's job is only to generate the token and get
 * it into the iframe URL and the fallback "Continue" link.
 *
 * Zoho's own "Redirect URL on Submission" navigates *inside the form's own
 * iframe* — left alone, that would render the full /booking or /payment
 * page (header, footer, and all) nested inside this small form iframe
 * instead of taking over the tab. js/main.js watches for the iframe
 * landing on our own domain (same-origin access starts working at that
 * exact moment, having thrown until then) and forces a real top-level
 * navigation to that same URL — see the iframe `load` listener in
 * loadRoute().
 *
 * Route Two can also be entered "booking-first": if a Zoho Bookings
 * confirmation redirects back here with its own customer_* merge-field
 * params (customer_name, customer_first_name, customer_last_name,
 * customer_contact_no, customer_email — Zoho Bookings' native redirect
 * vocabulary, not something this codebase invented), the fork is skipped
 * and Route Two's form is shown immediately with those values appended to
 * the form iframe's src for prefill. See loadRoute() in main.js.
 *
 * Different Zoho Bookings *services* redirect here for different reasons
 * (Personal Financial Consulting vs. others still being wired up), so the
 * heading/copy shown is looked up by that service's own service_uuid (also
 * in the redirect) via $kg_booking_routes below — add an entry per service
 * as its booking link is provided; unmapped service_uuids fall back to
 * generic copy.
 *
 * THINGS THAT NEED MANUAL CONFIRMATION (see README + PR notes):
 * 1. Whether each Zoho Form's own "Redirect URL on Submission" setting can
 *    carry `gated_token` forward dynamically to /payment or /booking. If
 *    yes, prefer configuring that in Zoho Forms directly — this page's
 *    "Continue" links below are the fallback for if it can't.
 * 2. Whether the Route Two form's Name/Email/Phone fields actually have
 *    "Prefill using URL parameter" enabled with parameter names matching
 *    customer_name / customer_email / customer_contact_no — that mapping
 *    lives in the Zoho Forms field editor, not in the embed script, and
 *    can't be confirmed from code. Test by opening the form's iframe URL
 *    directly with e.g. `&customer_email=test@test.com` appended.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Zoho Bookings' own post-booking redirect merge fields, when this page is
// landed on after a completed booking rather than reached via the fork.
$kg_booking_prefill = array();
foreach ( array( 'customer_name', 'customer_first_name', 'customer_last_name', 'customer_contact_no', 'customer_email' ) as $kg_field ) {
	if ( empty( $_GET[ $kg_field ] ) ) {
		continue;
	}
	$kg_value = sanitize_text_field( wp_unslash( $_GET[ $kg_field ] ) );
	if ( 'customer_email' === $kg_field && ! is_email( $kg_value ) ) {
		continue;
	}
	$kg_booking_prefill[ $kg_field ] = $kg_value;
}
$kg_from_booking = ! empty( $kg_booking_prefill['customer_email'] );

// Which booking-first landing to show depends on which Zoho Bookings
// *service* was booked — Zoho includes that service's own service_uuid in
// the redirect automatically, so use it as the routing key rather than
// treating every booking-first arrival as the same generic route. Add an
// entry here for each additional booking-first service as its link is
// provided; anything not yet mapped falls back to a best-effort guess
// below. Each entry's 'mode' also decides which of the two Route Two
// forms (Personal/Business) actually shows — not just the heading copy —
// since the Personal/Business toggle's checked radio otherwise always
// resets to its HTML default ("personal") on this fresh page load, no
// matter which service was really booked.
$kg_booking_service_uuid = isset( $_GET['service_uuid'] ) ? sanitize_text_field( wp_unslash( $_GET['service_uuid'] ) ) : '';
$kg_booking_service_name = isset( $_GET['service_name'] ) ? sanitize_text_field( wp_unslash( $_GET['service_name'] ) ) : '';
$kg_booking_routes       = array(
	'4946279000000136007' => array(
		'title' => 'Personal Financial Consulting',
		'lede'  => "You're booked — just a few more questions to help us prepare.",
		'mode'  => 'personal',
	),
	'4946279000000136026' => array(
		'title' => 'Business Advisory',
		'lede'  => "You're booked — just a few more questions to help us prepare.",
		'mode'  => 'business',
	),
);

if ( isset( $kg_booking_routes[ $kg_booking_service_uuid ] ) ) {
	$kg_booking_route = $kg_booking_routes[ $kg_booking_service_uuid ];
} else {
	// Unmapped service_uuid (e.g. Business, until its real service_uuid is
	// provided and added above) — guess personal vs. business from the
	// service's own name rather than always defaulting to personal, so at
	// least the right FORM shows even before this is properly mapped.
	$kg_is_business_guess = ( false !== stripos( $kg_booking_service_name, 'business' ) );
	$kg_booking_route      = array(
		'title' => $kg_is_business_guess ? 'Business Advisory' : 'Schedule an Introductory Consultation',
		'lede'  => "You're booked — just a few more questions to help us prepare.",
		'mode'  => $kg_is_business_guess ? 'business' : 'personal',
	);
}

$kg_steps = array(
	array(
		'n'     => '01',
		'title' => 'Reach out',
		'body'  => "Tell us a little about your situation and what you're hoping to sort out.",
	),
	array(
		'n'     => '02',
		'title' => 'Introductory meeting',
		'body'  => "A no-obligation conversation to understand your goals and confirm we're a good fit.",
	),
	array(
		'n'     => '03',
		'title' => 'Secure onboarding',
		'body'  => 'Agreements and documents are exchanged and tracked through your private client portal.',
	),
);
?>
<main>
	<section class="kg-section--no-border hero-wash">
		<div class="kg-container hero hero--narrow">
			<div>
				<p class="kg-eyebrow" style="margin-bottom:1rem;">Get started</p>
				<h1>Book your introduction right here.</h1>
				<p class="lede">Tell us which kind of advising you're looking for, and we'll point you the right way.</p>
			</div>
		</div>
	</section>

	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">

			<div data-kg-view="fork"<?php echo $kg_from_booking ? ' hidden' : ''; ?>>
				<div class="advising-toggle" role="radiogroup" aria-label="Type of advising">
					<input type="radio" id="kg-mode-personal" name="kg-advising-mode" value="personal"<?php echo ( ! $kg_from_booking || 'business' !== $kg_booking_route['mode'] ) ? ' checked' : ''; ?>>
					<label for="kg-mode-personal">Personal Advising</label>
					<input type="radio" id="kg-mode-business" name="kg-advising-mode" value="business"<?php echo ( $kg_from_booking && 'business' === $kg_booking_route['mode'] ) ? ' checked' : ''; ?>>
					<label for="kg-mode-business">Business Advising</label>
					<span class="advising-toggle__thumb" aria-hidden="true"></span>
				</div>

				<div class="route-buttons">
					<button type="button" class="btn-pill" data-kg-route-button="two">Schedule an Introductory Consultation</button>
					<button type="button" class="btn-outline" data-kg-route-button="one" data-kg-personal-only>Find the Right Plan</button>
				</div>
			</div>

			<div data-kg-view="route-one" hidden>
				<button type="button" class="back-link" data-kg-back-to-fork>&larr; Back</button>
				<h2 class="route-view__title">Find the Right Plan</h2>
				<p class="route-view__lede">Fill this out and we'll follow up with a plan that fits.</p>
				<div class="embed-frame">
					<iframe id="ziframe_296885" aria-label="Let's review your finances together" frameborder="0" style="height:500px;width:99%;border:none;" data-kg-form-src="https://forms.zohopublic.com/ryankaligirlfina1/form/Letsreviewyourfinancestogether/formperma/LKaR5Cbaj_hr11k288Ew9580SoVc_aBzz1IY0RzZiDs"></iframe>
				</div>
				<!--
					TODO: paste the standard Zoho referrer-tracking <script> block
					that ships with this form's embed code here, unmodified.
				-->
				<p class="route-view__continue">Already submitted the form above? <a href="#" data-kg-continue-link data-kg-destination="/payment">Continue &rarr;</a></p>
			</div>

			<div data-kg-view="route-two"<?php echo $kg_from_booking ? '' : ' hidden'; ?> data-kg-booking-prefill="<?php echo esc_attr( wp_json_encode( (object) $kg_booking_prefill ) ); ?>">
				<button type="button" class="back-link" data-kg-back-to-fork>&larr; Back</button>
				<h2 class="route-view__title"><?php echo esc_html( $kg_from_booking ? $kg_booking_route['title'] : 'Schedule an Introductory Consultation' ); ?></h2>
				<p class="route-view__lede"><?php echo esc_html( $kg_from_booking ? $kg_booking_route['lede'] : "A no-obligation conversation to see if we're a good fit." ); ?></p>
				<?php if ( ! $kg_from_booking ) : ?>
				<!--
					Not booked yet — show a Zoho Bookings widget first (gated
					by the same Personal/Business toggle above), not the form.
					js/main.js's kaligirlEnsureBookingWidget() lazily calls
					Bookings.inlineEmbed() into whichever container becomes
					relevant once this view is actually shown — deferred
					rather than eager like the Forms scripts below, since an
					inline calendar widget initialized inside a display:none
					container can misjudge its own size. Each Bookings
					service's own "redirect after booking" setting (Zoho-side,
					not this file) points back at this same /get-started URL,
					which is what flips $kg_from_booking to true above and
					swaps this whole block for the matching form below.
				-->
				<div data-kg-personal-only>
					<div class="embed-frame">
						<div id="inline-container-personal"></div>
					</div>
				</div>
				<div data-kg-business-only>
					<div class="embed-frame">
						<div id="inline-container-business"></div>
					</div>
				</div>
				<script src="https://bookings.nimbuspop.com/assets/embed.js"></script>
				<?php else : ?>
				<!--
					Route Two shows a different Zoho Form depending on the
					Personal/Business toggle above — js/main.js's applyMode()
					(the same function that already hides "Find the Right Plan"
					in Business mode) toggles which of these two is visible.
					Both scripts run at page load regardless (they inject into
					hidden divs same as everything else here), and loadRoute()
					augments every div[id^="zf_div_"] iframe it finds, not just
					one, so both are ready the instant either becomes visible.
				-->
				<div data-kg-personal-only>
				<div class="embed-frame">
					<!--
						Zoho's own embed script for this form (verbatim, unmodified
						— do not hand-edit; if Zoho reissues this form's embed code,
						replace this whole block). It builds the iframe's src (with
						its own UTM/referrer tracking params) and injects the iframe
						into the div below itself. js/main.js's loadRoute() finds
						that iframe afterward and appends gated_token, plus the
						customer_* prefill params above when arriving from a
						completed booking, onto whatever src Zoho already built —
						see the "div[id^=zf_div_] iframe" branch there.
					-->
					<div id="zf_div_IA70KZBAwznXrIPD3dBzx_Y26wUDWEnX78U0u80ivMQ"></div>
					<script type="text/javascript">
					(function() {
						try{
							var f = document.createElement("iframe");

								var ifrmSrc = 'https://forms.zohopublic.com/ryankaligirlfina1/form/GetStarted/formperma/IA70KZBAwznXrIPD3dBzx_Y26wUDWEnX78U0u80ivMQ?zf_rszfm=1';


					        try{
								if ( typeof ZFAdvLead != "undefined" && typeof zfutm_zfAdvLead != "undefined" ) {
									for( var prmIdx = 0 ; prmIdx < ZFAdvLead.utmPNameArr.length ; prmIdx ++ ) {
									    var utmPm = ZFAdvLead.utmPNameArr[ prmIdx ];
									    utmPm = ( ZFAdvLead.isSameDomian && ( ZFAdvLead.utmcustPNameArr.indexOf(utmPm) == -1 ) ) ? "zf_" + utmPm : utmPm;
									    var utmVal = zfutm_zfAdvLead.zfautm_gC_enc( ZFAdvLead.utmPNameArr[ prmIdx ] );
									    if ( typeof utmVal !== "undefined" ) {
									      if ( utmVal != "" ) {
									        if(ifrmSrc.indexOf('?') > 0){
									             ifrmSrc = ifrmSrc+'&'+utmPm+'='+utmVal;
									        }else{
									            ifrmSrc = ifrmSrc+'?'+utmPm+'='+utmVal;
									        }
									      }
									    }
									}
								}
								if ( typeof ZFLead !== "undefined" && typeof zfutm_zfLead !== "undefined" ) {
									for( var prmIdx = 0 ; prmIdx < ZFLead.utmPNameArr.length ; prmIdx ++ ) {
							        	var utmPm = ZFLead.utmPNameArr[ prmIdx ];
							        	var utmVal = zfutm_zfLead.zfutm_gC_enc( ZFLead.utmPNameArr[ prmIdx ] );
								        if ( typeof utmVal !== "undefined" ) {
								          if ( utmVal != "" ){
								            if(ifrmSrc.indexOf('?') > 0){
								              ifrmSrc = ifrmSrc+'&'+utmPm+'='+utmVal;//No I18N
								            }else{
								              ifrmSrc = ifrmSrc+'?'+utmPm+'='+utmVal;//No I18N
								            }
								          }
								        }
							      	}
								}
								if (!((new RegExp("[?&]referrername=")).test(ifrmSrc))) {
					            var rfr = window.location.href;

					            try {
					                rfr = window.self !== window.top ?
					                    window.top.location.href :
					                    (/^https?:\/\/[\w.-]+\.[a-zA-Z]{2,}/i.test(rfr) ? rfr : "");
					            } catch (e) {}

					            if (rfr && rfr !== "") {
					                if (rfr.length > 1800) {
					                    var queryIndex = rfr.indexOf('?');
					                    if (queryIndex > -1) {
					                        rfr = rfr.substring(0, queryIndex);
					                    }
					                    if (rfr.length > 1800) {
					                        rfr = rfr.substring(0, 1800);
					                    }
					                }
					                ifrmSrc += ((ifrmSrc.indexOf('?') > 0) ? '&' : '?') + 'referrername=' + encodeURIComponent(rfr);
					            }
					        }
							}catch(e){}


							f.src = ifrmSrc;
							f.style.border="none";
							f.style.height="150px";
							f.style.width="99%";
							f.style.transition="all 0.5s ease";
							f.setAttribute("aria-label", 'Let\x27s review your finances together');

							var d = document.getElementById("zf_div_IA70KZBAwznXrIPD3dBzx_Y26wUDWEnX78U0u80ivMQ");
							d.appendChild(f);
							window.addEventListener('message', function (){
								var evntData = event.data;
								if( evntData && evntData.constructor == String ){
									var zf_ifrm_data = evntData.split("|");
									if ( zf_ifrm_data.length == 2 || zf_ifrm_data.length == 3 ) {
										var zf_perma = zf_ifrm_data[0];
										var zf_ifrm_ht_nw = ( parseInt(zf_ifrm_data[1], 10) + 15 ) + "px";
										var iframe = document.getElementById("zf_div_IA70KZBAwznXrIPD3dBzx_Y26wUDWEnX78U0u80ivMQ").getElementsByTagName("iframe")[0];
										if ( (iframe.src).indexOf('formperma') > 0 && (iframe.src).indexOf(zf_perma) > 0 ) {
											var prevIframeHeight = iframe.style.height;
											var zf_tout = false;
											if( zf_ifrm_data.length == 3 ) {
											    iframe.scrollIntoView();
											    zf_tout = true;
											}

											if ( prevIframeHeight != zf_ifrm_ht_nw ) {
												if( zf_tout ) {
												    setTimeout(function(){
												        iframe.style.height = zf_ifrm_ht_nw;
												    },500);
												} else {
												    iframe.style.height = zf_ifrm_ht_nw;
												}
											}
										}
									}
								}
							}, false);
					    }catch(e){}


					})();
					</script>
				</div>
				</div>

				<div data-kg-business-only>
				<div class="embed-frame">
					<!-- Zoho's own embed script for the Business variant of this form (verbatim, unmodified). -->
					<div id="zf_div_uVgk75TZ_faL_CS1CqIoxwOqXFtdAbK_gCSC5hqdDjY"></div>
					<script type="text/javascript">
					(function() {
						try{
							var f = document.createElement("iframe");

								var ifrmSrc = 'https://forms.zohopublic.com/ryankaligirlfina1/form/LetsreviewyourfinancestogetherBusiness/formperma/uVgk75TZ_faL_CS1CqIoxwOqXFtdAbK_gCSC5hqdDjY?zf_rszfm=1';


					        try{
								if ( typeof ZFAdvLead != "undefined" && typeof zfutm_zfAdvLead != "undefined" ) {
									for( var prmIdx = 0 ; prmIdx < ZFAdvLead.utmPNameArr.length ; prmIdx ++ ) {
									    var utmPm = ZFAdvLead.utmPNameArr[ prmIdx ];
									    utmPm = ( ZFAdvLead.isSameDomian && ( ZFAdvLead.utmcustPNameArr.indexOf(utmPm) == -1 ) ) ? "zf_" + utmPm : utmPm;
									    var utmVal = zfutm_zfAdvLead.zfautm_gC_enc( ZFAdvLead.utmPNameArr[ prmIdx ] );
									    if ( typeof utmVal !== "undefined" ) {
									      if ( utmVal != "" ) {
									        if(ifrmSrc.indexOf('?') > 0){
									             ifrmSrc = ifrmSrc+'&'+utmPm+'='+utmVal;
									        }else{
									            ifrmSrc = ifrmSrc+'?'+utmPm+'='+utmVal;
									        }
									      }
									    }
									}
								}
								if ( typeof ZFLead !== "undefined" && typeof zfutm_zfLead !== "undefined" ) {
									for( var prmIdx = 0 ; prmIdx < ZFLead.utmPNameArr.length ; prmIdx ++ ) {
							        	var utmPm = ZFLead.utmPNameArr[ prmIdx ];
							        	var utmVal = zfutm_zfLead.zfutm_gC_enc( ZFLead.utmPNameArr[ prmIdx ] );
								        if ( typeof utmVal !== "undefined" ) {
								          if ( utmVal != "" ){
								            if(ifrmSrc.indexOf('?') > 0){
								              ifrmSrc = ifrmSrc+'&'+utmPm+'='+utmVal;//No I18N
								            }else{
								              ifrmSrc = ifrmSrc+'?'+utmPm+'='+utmVal;//No I18N
								            }
								          }
								        }
							      	}
								}
								if (!((new RegExp("[?&]referrername=")).test(ifrmSrc))) {
					            var rfr = window.location.href;

					            try {
					                rfr = window.self !== window.top ?
					                    window.top.location.href :
					                    (/^https?:\/\/[\w.-]+\.[a-zA-Z]{2,}/i.test(rfr) ? rfr : "");
					            } catch (e) {}

					            if (rfr && rfr !== "") {
					                if (rfr.length > 1800) {
					                    var queryIndex = rfr.indexOf('?');
					                    if (queryIndex > -1) {
					                        rfr = rfr.substring(0, queryIndex);
					                    }
					                    if (rfr.length > 1800) {
					                        rfr = rfr.substring(0, 1800);
					                    }
					                }
					                ifrmSrc += ((ifrmSrc.indexOf('?') > 0) ? '&' : '?') + 'referrername=' + encodeURIComponent(rfr);
					            }
					        }
							}catch(e){}


							f.src = ifrmSrc;
							f.style.border="none";
							f.style.height="150px";
							f.style.width="99%";
							f.style.transition="all 0.5s ease";
							f.setAttribute("aria-label", 'Let\x27s review your finances together \- Business');

							var d = document.getElementById("zf_div_uVgk75TZ_faL_CS1CqIoxwOqXFtdAbK_gCSC5hqdDjY");
							d.appendChild(f);
							window.addEventListener('message', function (){
								var evntData = event.data;
								if( evntData && evntData.constructor == String ){
									var zf_ifrm_data = evntData.split("|");
									if ( zf_ifrm_data.length == 2 || zf_ifrm_data.length == 3 ) {
										var zf_perma = zf_ifrm_data[0];
										var zf_ifrm_ht_nw = ( parseInt(zf_ifrm_data[1], 10) + 15 ) + "px";
										var iframe = document.getElementById("zf_div_uVgk75TZ_faL_CS1CqIoxwOqXFtdAbK_gCSC5hqdDjY").getElementsByTagName("iframe")[0];
										if ( (iframe.src).indexOf('formperma') > 0 && (iframe.src).indexOf(zf_perma) > 0 ) {
											var prevIframeHeight = iframe.style.height;
											var zf_tout = false;
											if( zf_ifrm_data.length == 3 ) {
											    iframe.scrollIntoView();
											    zf_tout = true;
											}

											if ( prevIframeHeight != zf_ifrm_ht_nw ) {
												if( zf_tout ) {
												    setTimeout(function(){
												        iframe.style.height = zf_ifrm_ht_nw;
												    },500);
												} else {
												    iframe.style.height = zf_ifrm_ht_nw;
												}
											}
										}
									}
								}
							}, false);
					    }catch(e){}


					})();
					</script>
				</div>
				</div>
				<p class="route-view__continue">Already submitted the form above? <a href="#" data-kg-continue-link data-kg-destination="/thank-you">Continue &rarr;</a></p>
				<?php endif; ?>
			</div>

		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container" style="padding:clamp(2.5rem,6vh,3.5rem) 0;">
			<?php kaligirl_principle_list( $kg_steps ); ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
