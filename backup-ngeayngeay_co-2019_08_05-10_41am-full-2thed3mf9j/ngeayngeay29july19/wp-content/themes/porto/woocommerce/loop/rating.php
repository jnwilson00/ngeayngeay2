<?php

/**
 * Loop Rating
 *
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
	return;
}

?>

<?php if ( $rating_html = porto_get_rating_html( $product ) ) : ?>

<div class="rating-wrap">
	<div class="rating-content"><?php echo porto_filter_output( $rating_html ); ?></div>
</div>

<?php endif; ?>
