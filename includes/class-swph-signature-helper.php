<?php
/**
 * Class that implements helper functions.
 *
 * @package SWPH_Order_Signature_Free
 */

/**
 * Class that implements helper functions.
 */
class SWPH_Signature_Helper {

	/**
	 * Function that checks if a plugin is active.
	 *
	 * @param string $plugin Plugin folder and file name path.
	 * @return boolean
	 */
	public static function is_plugin_active( $plugin ) {
		return in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
	}

	/**
	 * Display signature based on order.
	 *
	 * @param WC_Order $order Woocommerce order object.
	 * @param string   $signature_width Width for image container.
	 * @return void
	 */
	public static function display_order_signature( $order, $signature_width = 'auto' ) {
		$order_id = $order;
		if ( ! empty( $order_id->ID ) ) {
			$order_id = $order_id->ID;
		}

		$signature = self::get_signature_from_order( $order_id );

		if ( empty( $signature ) ) {
			return;
		}
		$signature_color     = get_option( 'wc_settings_tab_signature_sign_color', '#010000' );
		$signature_thickness = get_option( 'wc_settings_tab_signature_sign_stroke', 5 );

		$signature_image = self::process_image_data( $signature, $signature_color, $signature_thickness );

		$signature_wavier_title = get_post_meta( $order_id, '_customer-signature-custom-field-title', true );
		$signature_wavier_text  = get_post_meta( $order_id, '_customer-signature-custom-field-text', true );

		$title_tag = get_option( 'wc_settings_tab_signature_customer_signature_text_size', 'h2' );

		include SWPH_PLUGIN_DIR . 'templates/signature-template.php';
	}

	/**
	 * Return signature data from order with backwards compatibility.
	 *
	 * @param int $order_id ID of order.
	 * @return string
	 */
	public static function get_signature_from_order( $order_id ) {
		$data = get_post_meta( $order_id, '_customer-signature', true );
		if ( empty( $data ) ) {
			$data = get_post_meta( $order_id, 'customer-signature', true );
		}

		if ( empty( $data ) ) {
			$data = get_post_meta( $order_id, 'Customer Signature', true );
		}
		return $data;
	}


	/**
	 * Process signature base30 data and return base64 image.
	 *
	 * @param string $image_data Base30 format of the image.
	 * @param string $signature_color Hex color of the signature.
	 * @param int    $signature_thickness Thickness of the signature.
	 * @return string
	 */
	public static function process_image_data( $image_data, $signature_color, $signature_thickness ) {
		require_once SWPH_PLUGIN_DIR . 'jsignature/jSignature_Tools_Base30.php';

		$data = str_replace( 'image/jsignature;base30,', '', $image_data );

		$signature = new jSignature_Tools_Base30();

		// Decode base30 format.
		$a = $signature->Base64ToNative( $data );

		// Calculate dimensions.
		$width  = 0;
		$height = 0;

		foreach ( $a as $line ) {
			if ( max( $line ['x'] ) > $width ) {
				$width = max( $line ['x'] );
			}
			if ( max( $line ['y'] ) > $height ) {
				$height = max( $line ['y'] );
			}
		}

		$im = imagecreatetruecolor( $width + 10, $height + 10 );

		// Save transparency for PNG.
		imagesavealpha( $im, true );

		// Fill background with transparency.
		$trans_colour = imagecolorallocatealpha( $im, 0, 0, 0, 127 );

		imagefill( $im, 0, 0, $trans_colour );

		// Set pen thickness.
		// get_option( 'wc_settings_tab_signature_sign_stroke', '5' ).
		imagesetthickness( $im, $signature_thickness );

		// Set pen color to black.
		if ( '#000000' === $signature_color ) {
			$signature_color = '#010000'; // Should see actual problem, but...
		}

		list($r, $g, $b)     = sscanf( $signature_color, '#%02x%02x%02x' );
		$swph_sign_rgb_color = imagecolorallocate( $im, $r, $g, $b );

		$count = count( $a );
		// Loop through array pairs from each signature word.
		for ( $i = 0; $i < $count; $i++ ) {
			$inner_count = count( $a[ $i ]['x'] );
			// Loop through each pair in a word.
			for ( $j = 0; $j < $inner_count; $j++ ) {

				// Make sure we are not on the last coordinate in the array.
				if ( ! isset( $a[ $i ]['x'][ $j ] ) ) {
					break;
				}
				if ( ! isset( $a[ $i ]['x'][ $j + 1 ] ) ) {

					// Draw the dot for the coordinate.
					imagesetpixel( $im, $a[ $i ]['x'][ $j ], $a[ $i ]['y'][ $j ], $swph_sign_rgb_color );

				} else {
					// Draw the line for the coordinate pair.
					imageline( $im, $a[ $i ]['x'][ $j ], $a[ $i ]['y'][ $j ], $a[ $i ]['x'][ $j + 1 ], $a[ $i ]['y'][ $j + 1 ], $swph_sign_rgb_color );
				}
			}
		}

		$imagedata = self::trim_image( $im );

		imagedestroy( $im );

		// phpcs:ignore
		return base64_encode( $imagedata );
	}

	/**
	 * Trim image to signature.
	 *
	 * @param mixed $img Image path or image bytes array.
	 * @return array
	 */
	private static function trim_image( $img = null ) {

		if ( ! $img ) {
			exit();
		}

		if ( is_string( $img ) ) {
			$img = imagecreatefrompng( $img );
		}

		$width  = imagesx( $img );
		$height = imagesy( $img );

		$top    = 0;
		$bottom = 0;
		$left   = 0;
		$right  = 0;

		$bgcolor = 0xFFFFFF;
		$bgcolor = imagecolorat( $img, $top, $left );

		for ( ; $top < $height; ++$top ) {
			for ( $x = 0; $x < $width; ++$x ) {
				if ( imagecolorat( $img, $x, $top ) !== $bgcolor ) {
					break 2;
				}
			}
		}

		for ( ; $bottom < $height; ++$bottom ) {
			for ( $x = 0; $x < $width; ++$x ) {
				if ( imagecolorat( $img, $x, $height - $bottom - 1 ) !== $bgcolor ) {
					break 2;
				}
			}
		}

		for ( ; $left < $width; ++$left ) {
			for ( $y = 0; $y < $height; ++$y ) {
				if ( imagecolorat( $img, $left, $y ) !== $bgcolor ) {
					break 2;
				}
			}
		}

		for ( ; $right < $width; ++$right ) {
			for ( $y = 0; $y < $height; ++$y ) {
				if ( imagecolorat( $img, $width - $right - 1, $y ) !== $bgcolor ) {
					break 2;
				}
			}
		}

		$newimg = imagecreate(
			$width - ( $left + $right ),
			$height - ( $top + $bottom )
		);

		imagecopy( $newimg, $img, 0, 0, $left, $top, imagesx( $newimg ), imagesy( $newimg ) );

		ob_start();
		imagepng( $newimg );

		$imagedata = ob_get_contents();

		ob_end_clean();

		return $imagedata;
	}
}
