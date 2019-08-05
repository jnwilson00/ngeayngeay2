<?php
/**
 * Single Product Image
 *
 * @version     3.5.1
 */

defined( 'ABSPATH' ) || exit;

global $post, $woocommerce, $product, $porto_settings, $porto_settings_optimize, $porto_product_layout;

$attachment_ids = $product->get_gallery_image_ids();

$items_count            = 1;
$product_images_classes = '';
$product_image_classes  = 'img-thumbnail';
$product_images_attrs   = '';

if ( 'extended' === $porto_product_layout ) {
	$items_count               = get_post_meta( get_the_ID(), 'product_layout_columns', true );
	$items_count               = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 3;
	$product_images_attrs     .= ' data-items="3" data-centeritem';
	$columns_responsive        = array();
	$columns_responsive['768'] = 3;
	$columns_responsive['0']   = 1;
	$product_images_attrs     .= ' data-responsive="' . esc_attr( json_encode( $columns_responsive ) ) . '"';
}
if ( 'grid' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block row';
	$items_count            = get_post_meta( get_the_ID(), 'product_layout_grid_columns', true );
	$items_count            = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 2;
	$items_count            = '2';
	if ( '1' === $items_count ) {
		$product_image_classes .= ' col-lg-12';
	} elseif ( '2' === $items_count ) {
		$product_image_classes .= ' col-md-6';
	} elseif ( '3' === $items_count ) {
		$product_image_classes .= ' col-md-6 col-lg-4';
	} elseif ( '4' === $items_count ) {
		$product_image_classes .= ' col-md-6 col-lg-3';
	}
} elseif ( 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block';
} else {
	$product_images_classes = 'product-image-slider owl-carousel show-nav-hover has-ccols';
	if ( 'extended' === $porto_product_layout ) {
		$product_images_classes .= ' ccols-1 ccols-md-3';
	} else {
		$product_images_classes .= ' ccols-1';
	}
}

?>
<div class="product-images images">
	<?php
	$html          = '<div class="' . esc_attr( $product_images_classes ) . '"' . $product_images_attrs . '>';
	$attachment_id = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();

	if ( $attachment_id ) {

		$image_title = get_the_title( $attachment_id );
		if ( ! $image_title ) {
			$image_title = '';
		}
		if ( 'full_width' === $porto_product_layout ) {
			$image_single_link = wp_get_attachment_image_src( $attachment_id, 'full' );
			$width             = $image_single_link[1];
			$height            = $image_single_link[2];
			$image_single_link = $image_single_link[0];
		} else {
			$image_single_link = wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' );
			$width             = $image_single_link[1];
			$height            = $image_single_link[2];
			$image_single_link = $image_single_link[0];
		}
		$image_link = wp_get_attachment_url( $attachment_id );

		$html .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';
		$html .= '<img src="' . esc_url( $image_single_link ) . '" href="' . esc_url( $image_link ) . '" class="woocommerce-main-image img-responsive" alt="' . esc_attr( $image_title ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" itemprop="image" content="' . esc_url( $image_link ) . '" />';
		if ( $porto_settings['product-image-popup'] && ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout ) ) {
			$html .= '<a class="zoom" href="' . esc_url( $image_link ) . '"><i class="fas fa-search"></i></a>';
		}
		$html .= '</div></div>';

	} else {

		$image_link = wc_placeholder_img_src( 'woocommerce_single' );
		$html      .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';
		$html      .= '<img src="' . esc_url( $image_link ) . '" alt="placeholder" href="' . esc_url( $image_link ) . '" class="woocommerce-main-image img-responsive" itemprop="image" content="' . esc_url( $image_link ) . '" />';
		$html      .= '</div></div>';
	}

	if ( $attachment_ids ) {
		foreach ( $attachment_ids as $attachment_id ) {

			$image_link = wp_get_attachment_url( $attachment_id );

			if ( ! $image_link ) {
				continue;
			}

			$image_title = get_the_title( $attachment_id );
			if ( ! $image_title ) {
				$image_title = '';
			}
			if ( 'full_width' === $porto_product_layout ) {
				$image_single_link = wp_get_attachment_image_src( $attachment_id, 'full' );
				$width             = $image_single_link[1];
				$height            = $image_single_link[2];
				$image_single_link = $image_link;
			} else {
				$image_single_link = wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' );
				$width             = $image_single_link[1];
				$height            = $image_single_link[2];
				$image_single_link = $image_single_link[0];
			}

			$html .= '<div class="' . esc_attr( $product_image_classes ) . '"><div class="inner">';
			$size  = 'full_width' === $porto_product_layout ? 'full' : 'woocommerce_single';
			if ( strpos( $product_images_classes, 'product-image-slider owl-carousel' ) !== false && isset( $porto_settings_optimize['lazyload'] ) && $porto_settings_optimize['lazyload'] ) {
				$thumb_image = wp_get_attachment_image_src( $attachment_id, $size );
				if ( $thumb_image && is_array( $thumb_image ) && count( $thumb_image ) >= 3 ) {
					$thumb_classes  = '';
					$thumb_attr     = '';
					$placeholder    = porto_generate_placeholder( $thumb_image[1] . 'x' . $thumb_image[2] );
					$thumb_classes .= 'owl-lazy';
					$thumb_attr     = ' data-src="' . esc_url( $thumb_image[0] ) . '" src="' . esc_url( $placeholder[0] ) . '"';
					$alt_text       = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
					$html          .= '<img ' . $thumb_attr . ' href="' . esc_url( $image_link ) . '" width="' . esc_attr( $thumb_image[1] ) . '" height="' . esc_attr( $thumb_image[2] ) . '" alt="' . esc_attr( $alt_text ) . '" class="img-responsive ' . esc_attr( $thumb_classes ) . '" itemprop="image" content="' . esc_url( $image_link ) . '" />';
				}
			} else {
				$html .= '<img src="' . esc_url( $image_single_link ) . '" href="' . esc_url( $image_link ) . '" class="img-responsive" alt="' . esc_attr( $image_title ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" itemprop="image" content="' . esc_url( $image_link ) . '" />';
			}
			if ( $porto_settings['product-image-popup'] && ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout ) ) {
				$html .= '<a class="zoom" href="' . esc_url( $image_link ) . '"><i class="fas fa-search"></i></a>';
			}
			$html .= '</div></div>';

		}
	}

	$html .= '</div>';

	if ( $porto_settings['product-image-popup'] && ( 'default' === $porto_product_layout || 'full_width' === $porto_product_layout || 'transparent' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout || 'extended' === $porto_product_layout || 'left_sidebar' === $porto_product_layout ) ) {
		$html .= '<span class="zoom" data-index="0"><i class="fas fa-search"></i></span>';
	}

	echo apply_filters( 'woocommerce_single_product_image_html', $html, $post->ID ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

	?>
</div>

<?php
if ( $porto_settings['product-thumbs'] ) {
	do_action( 'woocommerce_product_thumbnails' );
}
