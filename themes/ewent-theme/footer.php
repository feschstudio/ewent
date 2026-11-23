<?php
$logo = get_template_directory() . '/assets/fesch-logo.png';
$home = ewent_is_home();
$h    = $home ? '' : esc_url( home_url( '/' ) );
?>
<footer>
  <div class="wrap foot">
    <div class="brand"><div class="wm" aria-label="ewent.">ewent<i>.</i></div><div class="by"><span>by</span><?php if ( file_exists( $logo ) ) : ?><span class="logo-tint" role="img" aria-label="fesch."><img class="logo-img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/fesch-logo.png' ); ?>" alt=""></span><?php else : ?><a href="https://fesch.co.at" style="font-family:var(--serif);font-size:24px;color:inherit;text-decoration:none">fesch.</a><?php endif; ?></div></div>
    <p class="tagl">ewent. by fesch.<br>Euer Veranstaltungskalender in der Region.<br>Fragen an <a href="mailto:info@ewent.at">info@ewent.at</a></p>
    <nav class="fmenu" aria-label="Menü"><?php if ( $home ) : ?><button type="button" data-open-submit>Event eintragen<i>›</i></button><?php else : ?><a href="<?php echo $h; ?>#submit">Event eintragen<i>›</i></a><?php endif; ?><a href="<?php echo $h; ?>#newsletter">Newsletter<i>›</i></a><a href="<?php echo $h; ?>#pakete">Pakete und Preise<i>›</i></a>
    <details class="hs"><summary>Als App speichern</summary><div class="hsb"><div class="hsi"><svg class="appico" viewBox="0 0 120 120" aria-hidden="true"><rect width="120" height="120" rx="27" fill="#2F5D46"/><circle cx="96" cy="-6" r="46" fill="#244A38"/><text x="54" y="86" text-anchor="middle" font-family="Bricolage Grotesque,DM Sans,sans-serif" font-weight="500" font-size="92" letter-spacing="-4" fill="#F6F3E8">e</text><circle cx="90" cy="80" r="8" fill="#D9A92E"/></svg><span>Icon auf dem Homescreen, ohne App Store</span></div><h4>iPhone (Safari)</h4><ol><li>Seite in Safari öffnen</li><li>Unten auf <b>Teilen</b> tippen (Quadrat mit Pfeil)</li><li><b>Zum Home-Bildschirm</b> wählen</li><li>Oben rechts <b>Hinzufügen</b></li></ol><h4>Android (Chrome)</h4><ol><li>Seite in Chrome öffnen</li><li>Oben rechts auf <b>⋮</b> tippen</li><li><b>App installieren</b> oder <b>Zum Startbildschirm hinzufügen</b></li><li>Mit <b>Installieren</b> bestätigen</li></ol></div></details><a href="https://instagram.com/ewent.at" target="_blank" rel="noopener">Instagram<i>›</i></a></nav>
    <nav class="legal" aria-label="Rechtliches"><a href="<?php echo esc_url( ewent_page_url( 'impressum' ) ); ?>">Impressum</a><a href="<?php echo esc_url( ewent_page_url( 'datenschutz' ) ); ?>">Datenschutz</a><a href="<?php echo esc_url( ewent_page_url( 'agb' ) ); ?>">AGB</a></nav>
  </div>
</footer>

<?php if ( $home ) : ?>
<div class="toast" id="toast" role="status" hidden></div>
<div class="dock"><div class="dockbar"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="q" type="text" inputmode="search" enterkeyhint="search" placeholder="Event, Ort oder Veranstalter" autocomplete="off" aria-label="Suche"><button class="clr" id="qclr" type="button" hidden aria-label="Suche löschen">×</button><button class="plus" data-open-submit aria-label="Event eintragen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></button></div></div>

<div class="scrim" id="detailScrim" role="dialog" aria-modal="true" aria-label="Event-Details"><div class="sheet" id="detailSheet"></div></div>
<div class="scrim" id="filterScrim" role="dialog" aria-modal="true" aria-label="Filter"><div class="sheet" id="filterSheet"></div></div>
<div class="scrim" id="regionScrim" role="dialog" aria-modal="true" aria-label="Region"><div class="sheet" id="regionSheet"></div></div>
<div class="scrim" id="submitScrim" role="dialog" aria-modal="true" aria-label="Event eintragen"><div class="sheet"><div class="pad" id="submitPad"></div></div></div>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
