<?php
global $porto_settings;

$featured_images = porto_get_featured_images();
$image_size      = isset( $image_size ) ? $image_size : 'full';
?>

<?php
if ( count( $featured_images ) ) :
	$attachment_id = $featured_images[0]['attachment_id'];
	$attachment    = porto_get_attachment( $attachment_id, $image_size );
	?>

	<div class="thumb-info thumb-info-no-borders thumb-info-bottom-info thumb-info-lighten thumb-info-bottom-info-dark thumb-info-centered-icons">
		<div class="thumb-info-wrapper">
			<div class="post-image">
				<img width="<?php echo esc_attr( $attachment['width'] ); ?>" height="<?php echo esc_attr( $attachment['height'] ); ?>" src="<?php echo esc_url( $attachment['src'] ); ?>" alt="<?php echo esc_attr( $attachment['alt'] ); ?>" />
			</div>
			<span class="thumb-info-title">
				<span class="thumb-info-inner"><?php echo the_title(); ?></span>
				<span class="thumb-info-type"><?php echo get_the_category_list( ', ' ); ?></span>
			</span>
			<span class="thumb-info-action">
				<a href="<?php the_permalink(); ?>">
					<span class="thumb-info-action-icon thumb-info-action-icon-light"><i class="fas fa-link text-dark"></i></span>
				</a>
			</span>
		</div>
	</div>
<?php endif; ?>
