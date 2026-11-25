<?php
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$dates = function_exists( 'ewent_get_dates' ) ? ewent_get_dates( $id ) : array();
	$m     = function ( $k ) use ( $id ) { return (string) get_post_meta( $id, $k, true ); };
	$place = $m( '_ew_place' ); $town = $m( '_ew_town' ); $cat = $m( '_ew_cat' ); $price = $m( '_ew_price' ) ?: 'Gratis';
	$org   = $m( '_ew_org' ); $link = $m( '_ew_link' ); $tier = $m( '_ew_tier' ) ?: 'free'; $manual = '1' === $m( '_ew_manual' );
	$dow   = array( 'Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag' );
	$mon   = array( 'Jän','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez' );
	$fmt   = function ( $d ) use ( $dow, $mon ) { $t = strtotime( $d . ' 12:00' ); return $dow[ (int) gmdate( 'w', $t ) ] . ', ' . (int) gmdate( 'j', $t ) . '. ' . $mon[ (int) gmdate( 'n', $t ) - 1 ]; };
	$first = $dates ? $dates[0] : null; $last = $dates ? $dates[ count( $dates ) - 1 ][0] : '';
	$multi = count( $dates ) > 1;
	$today = wp_date( 'Y-m-d' ); $next = null;
	foreach ( $dates as $d ) { if ( $d[0] >= $today ) { $next = $d; break; } }
	$next = $next ?: $first;
	wp_localize_script( 'ewent-single', 'EWENT_SINGLE', array( 'id' => $id, 'title' => get_the_title(), 'place' => $place, 'town' => $town, 'dates' => $dates ) );
	?>
<main class="wrap pagewrap evpage">
  <a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>">‹ Alle Events</a>
  <?php if ( has_post_thumbnail() ) : ?><div class="pic"><?php the_post_thumbnail( 'large' ); ?></div><?php endif; ?>
  <span class="label"><?php echo esc_html( $cat ); ?><?php echo in_array( $tier, array( 'top', 'premium' ), true ) ? ' · Anzeige' : ''; ?></span>
  <h1><?php the_title(); ?></h1>
  <div class="desc"><?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?></div>
  <?php $fly = (int) get_post_meta( $id, '_ew_flyer', true ); if ( $fly && wp_attachment_is_image( $fly ) ) : ?><div class="flyer"><h2>Flyer / Plakat</h2><a href="<?php echo esc_url( wp_get_attachment_url( $fly ) ); ?>" target="_blank" rel="noopener"><?php echo wp_get_attachment_image( $fly, 'large' ); ?></a></div><?php endif; ?>
  <?php if ( $first ) : ?>
  <div class="facts">
    <div><span><?php echo $multi ? 'Zeitraum' : 'Datum'; ?></span><span><?php echo esc_html( $multi ? $fmt( $first[0] ) . ' – ' . $fmt( $last ) : $fmt( $first[0] ) ); ?></span></div>
    <div><span><?php echo $multi ? 'Nächster Termin' : 'Beginn'; ?></span><span><?php echo esc_html( ( $multi ? $fmt( $next[0] ) . ', ' : '' ) . $next[1] . ( get_post_meta( $id, '_ew_time_end', true ) ? '–' . get_post_meta( $id, '_ew_time_end', true ) : '' ) . ' Uhr' ); ?></span></div>
    <div><span>Ort</span><span><?php echo esc_html( trim( $place . ', ' . $town, ', ' ) ); ?></span></div>
    <div><span>Preis</span><span><?php echo esc_html( $price ); ?></span></div>
    <?php if ( $org ) : ?><div><span>Veranstalter</span><span><?php echo esc_html( $org ); ?></span></div><?php endif; ?>
  </div>
  <?php if ( $multi ) : ?>
  <details style="margin:-8px 0 20px"><summary style="cursor:pointer;font-weight:600">Alle <?php echo count( $dates ); ?> Termine</summary><div style="margin-top:10px;font-size:14px;color:var(--muted);line-height:1.8"><?php foreach ( $dates as $d ) { echo esc_html( $fmt( $d[0] ) . ' · ' . $d[1] . ' Uhr' ) . '<br>'; } ?></div></details>
  <?php endif; ?>
  <?php endif; ?>
  <?php if ( $link ) : ?><a class="btn accent block" target="_blank" rel="noopener nofollow" href="<?php echo esc_url( $link ); ?>">Tickets / Anmeldung</a><?php endif; ?>
  <div class="dact">
    <button class="btn ghost" id="d_share" type="button">Teilen</button>
    <button class="btn ghost" id="d_cal" type="button">Zum Kalender</button>
    <a class="btn ghost" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode( $place . ' ' . $town ); ?>">Karte</a>
  </div>
  <?php if ( $manual ) : ?>
  <div class="fnote" style="margin-top:26px;text-align:left"><b>Manuell angelegt.</b> Dieses Event haben wir manuell angelegt (alle Events im November und Dezember). Bei Änderungen schick uns bitte kurz eine Mail an <a href="mailto:info@ewent.at?subject=<?php echo rawurlencode( 'Änderung: ' . get_the_title() ); ?>">info@ewent.at</a>.</div>
  <?php endif; ?>
</main>
<?php endwhile; get_footer();
