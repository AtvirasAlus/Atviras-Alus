<?php get_header(); ?>

		<div id="primary">
			<div id="content" role="main">
				
				<header class="page-header">
					<h1 class="page-title"><?php single_term_title( ); ?></h1>
				</header>
				
				<div class="ims-gallery">
				<?php while ( have_posts( ) ) : the_post(); ?>
					<?php echo $ImStore->taxonomy_content() ?>
				<?php endwhile; ?>
				</div>
				
				<nav id="<?php echo $nav_id; ?>" role="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older Galleries', 'ims' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer Galleries <span class="meta-nav">&rarr;</span>', 'ims' ) ); ?></div>
				</nav><!-- #nav-above -->

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
				

