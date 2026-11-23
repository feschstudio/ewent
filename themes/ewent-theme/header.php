<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="banner"></div>
<header>
  <div class="wrap head">
    <a class="wm" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="ewent." style="text-decoration:none;color:inherit">ewent<i>.</i></a>
    <div class="hr"><?php if ( ewent_is_home() ) : ?><button class="region" id="regionBtn" aria-label="Region wählen">Region Krems</button><?php else : ?><a class="region" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="text-decoration:none">Alle Events</a><?php endif; ?><a class="ibtn" href="<?php echo esc_url( home_url( '/konto/' ) ); ?>" aria-label="Veranstalter-Login" style="text-decoration:none;color:inherit"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg></a></div>
  </div>
</header>
