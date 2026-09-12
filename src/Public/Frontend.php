<?php

namespace LicencePress\Public;

use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Functions\Helpers\ContentHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Frontend {
	public function filter_content( string $content ): string {
		if ( ! is_singular( PostType::LICENCE_TYPE_VARIANT ) || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		$parts = array(
			'<article class="licencepress-page">',
			'<div class="licencepress-content">' . $content . '</div>',
			'</article>',
		);

		return (string) apply_filters( 'licencepress_frontend_content', implode( '', $parts ), $content );
	}

	public function body_classes( array $classes ): array {
		if ( is_singular( PostType::LICENCE_TYPE_VARIANT ) ) {
			$classes[] = 'licencepress-page-template';
		}

		return array_values( array_unique( array_map( 'sanitize_html_class', $classes ) ) );
	}
}
