<?php get_header(); ?>

<section class="wrap herowrap">
  <div class="hero2">
    <h1>Was ist los in der Region Krems?</h1>
    <p>Wachau, Kremstal, Kamptal, Traisental</p>
  </div>
</section>

<div class="filters">
  <div class="wrap">
    <div class="bar">
      <div class="chips" id="quick" role="group" aria-label="Zeitraum"></div>
      <button class="ibtn dark" id="fopen" aria-label="Filter öffnen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg><span class="n" id="fcount"></span></button>
    </div>
    <div class="pills" id="pills" hidden></div>
  </div>
</div>

<main class="wrap">
  <div id="weekBox"></div><div id="featuredBox"></div>
  <div id="list"><noscript><?php
	if ( function_exists( 'ewent_occurrences' ) ) {
		echo '<ul>';
		foreach ( ewent_occurrences( array( 'limit' => 60 ) ) as $o ) {
			echo '<li><a href="' . esc_url( $o['url'] ) . '">' . esc_html( $o['date'] . ' · ' . $o['title'] . ' · ' . $o['town'] ) . '</a></li>';
		}
		echo '</ul>';
	}
  ?></noscript></div>

  <section class="nlbox" id="newsletter"><span class="label">Newsletter</span><h2>Jede Woche: Was sich tut</h2><p>Jeden Donnerstag eine kurze Mail mit den besten Events der Woche in der Region Krems. Kostenlos, jederzeit abbestellbar.</p><form class="nl" id="nlform" novalidate><input type="email" id="nlmail" placeholder="Deine E-Mail" aria-label="E-Mail für den Newsletter" autocomplete="email"><button class="btn accent" type="submit">Anmelden</button></form><small id="nlmsg">Mit der Anmeldung stimmst du dem Empfang zu. Mehr dazu in der <a href="<?php echo esc_url( ewent_page_url( 'datenschutz' ) ); ?>" style="color:inherit">Datenschutzerklärung</a>.</small></section>
  <section class="packs" id="pakete">
    <span class="label">Für Veranstalter</span>
    <h2>Mehr Aufmerksamkeit für dein Event</h2>
    <p class="lead">Der Eintrag ist gratis. Soll dein Event mehr Leute erreichen, wähle ein Paket pro Event.</p>
    <div class="pgrid" id="packGrid"></div>
  </section>
</main>

<?php get_footer();
