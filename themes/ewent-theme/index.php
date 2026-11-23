<?php get_header(); ?>
<main class="wrap pagewrap"><article class="lg page">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<h1><?php the_title(); ?></h1><?php the_content(); ?>
<?php endwhile; else : ?>
<h1>Nicht gefunden</h1><p>Diese Seite gibt es nicht. <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Zurück zu den Events</a></p>
<?php endif; ?>
</article></main>
<?php get_footer();
