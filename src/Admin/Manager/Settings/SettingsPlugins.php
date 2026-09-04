<?php
/**
 * SettingsPlugins class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;

final class SettingsPlugins {
	/**
	 * Check if a settings page exists for the given slug.
	 *
	 * @param string $slug The slug of the settings page.
	 * @return bool True if the settings page exists, false otherwise.
	 */
	public function has_settings_page( string $slug ): bool {
		return isset( $this->settings_pages()[ $slug ] );
	}

	/**
	 * Check whether the current user can view a provider settings page.
	 *
	 * @param string $slug The settings page slug.
	 * @return bool True when the page is public to the current settings user.
	 */
	public function can_view_settings_page( string $slug ): bool {
		$page = $this->settings_pages()[ $slug ] ?? null;
		if ( ! is_array( $page ) ) {
			return false;
		}

		$capability = SanitizationHelper::key(
			$page['capability'] ?? 'licencepress_settings_plugins_int_view'
		);
		return '' !== $capability && current_user_can( $capability );
	}
	/**
	 * Render the settings page for the given slug.
	 *
	 * @param string $slug The slug of the settings page.
	 * @param array  $values The current values of the settings.
	 */
	public function render_settings_page( string $slug, array $values ): void {
		$page = $this->settings_pages()[ $slug ] ?? null;
		if ( ! is_array( $page ) ) {
			return;
		}

		echo '<tr><th scope="row">' . esc_html( $page['title'] ?? $page['label'] ) . '</th><td>';
		foreach ( $page['fields'] as $field ) {
			$key = SanitizationHelper::key( $field['key'] ?? '' );
			if ( '' === $key ) {
				continue;
			}
			$default = array_key_exists( 'default', $field ) ? $field['default'] : false;
			$name    = 'licencepress_' . SanitizationHelper::key( $page['slug'] ) . '[' . $key . ']';
			$value   = $values[ $key ] ?? $default;
			$type    = SanitizationHelper::key( $field['type'] ?? 'checkbox', 'checkbox' );
			echo '<div class="mb-3">' . FormFieldHelper::label(
				'licencepress-' . $key,
				(string) ( $field['label'] ?? $key ),
				array(
					'description'  => (string) ( $field['description'] ?? '' ),
					'tooltip'      => (string) ( $field['tooltip'] ?? '' ),
					'tooltip_type' => SanitizationHelper::key(
						$field['tooltip_type'] ?? 'question',
						'question'
					),
					'tooltip_icon' => (string) ( $field['tooltip_icon'] ?? '' ),
				)
			);
			if ( 'select' === $type ) {
				echo FormFieldHelper::select(
					$name,
					(array) ( $field['options'] ?? array() ),
					$value,
					array( 'id' => 'licencepress-' . $key )
				);
			} elseif ( 'text' === $type ) {
				echo FormFieldHelper::input(
					$name,
					is_scalar( $value ) ? (string) $value : '',
					array(
						'id'   => 'licencepress-' . $key,
						'type' => 'text',
					)
				);
			} elseif ( 'custom' === $type ) {
				$render = $field['render'] ?? null;
				if ( is_callable( $render ) ) {
					call_user_func( $render, $value, $name, 'licencepress-' . $key );
				}
			} else {
				echo FormFieldHelper::checkbox(
					$name,
					'1',
					'',
					array(
						'id'      => 'licencepress-' . $key,
						'checked' => ! empty( $value ),
					)
				);
			}
			echo '</div>';
		}
		echo '</td></tr>';
	}
	/**
	 * Render the settings page for the given tab.
	 *
	 * @param string $tab The tab to render.
	 */
	public function render( string $tab ): void {
		if ( 'third-party' === $tab ) {
			$this->render_third_party_plugins();
			return;
		}

		$page = $this->settings_pages()[ $tab ] ?? null;
		if (
			is_array( $page )
			&& ! empty( $page['render_page'] )
			&& is_callable( $page['render_page'] )
		) {
			call_user_func( $page['render_page'] );
			return;
		}

		$this->render_licencepress_plugins();
	}
	/**
	 * Get the registered settings pages from enabled plugins.
	 *
	 * @return array An associative array of registered settings pages.
	 */
	private function settings_pages(): array {
		$pages = array();
		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if (
				! $plugin instanceof PluginInterface
				|| ! $plugin instanceof SettingsPageProviderInterface
				|| ! Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() )
			) {
				continue;
			}

			$page = $plugin->get_settings_page();
			if (
				empty( $page['slug'] )
				|| empty( $page['label'] )
				|| ( empty( $page['fields'] ) && empty( $page['render_page'] ) )
			) {
				continue;
			}
			$pages[ SanitizationHelper::key( $page['slug'] ) ] = $page;

			foreach ( $page['tabs'] ?? array() as $tab ) {
				if (
					! is_array( $tab )
					|| empty( $tab['slug'] )
					|| empty( $tab['label'] )
					|| empty( $tab['render_page'] )
					|| ! is_callable( $tab['render_page'] )
				) {
					continue;
				}

				$pages[ SanitizationHelper::key( $tab['slug'] ) ] = array_merge(
					$page,
					$tab,
					array( 'fields' => $tab['fields'] ?? array() )
				);
			}
		}
		return $pages;
	}
	/**
	 * Render the LicencePress plugins section.
	 *
	 * @since 1.0.0
	 */
	private function render_licencepress_plugins(): void {
		?>
		<div class="row g-4">
			<?php foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) : ?>
				<?php if ( $plugin instanceof PluginInterface && $this->can_view_plugin( $plugin ) ) : ?>
					<?php $this->render_licencepress_plugin_card( $plugin ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
	/**
	 * Render the third-party plugins section.
	 *
	 * @since 1.0.0
	 */
	private function render_third_party_plugins(): void {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		?>
		<div class="row g-4">
			<?php foreach ( get_plugins() as $file => $plugin ) : ?>
				<?php if ( function_exists( 'plugin_basename' ) && plugin_basename( LICENCEPRESS_FILE ) === $file ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php $this->render_third_party_plugin_card( $file, $plugin ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_licencepress_plugin_card( $plugin ): void {
		$enabled       = Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() );
		$settings_page = $plugin instanceof SettingsPageProviderInterface
			? $plugin->get_settings_page()
			: array();
		$modal_id      = SanitizationHelper::key( $plugin->get_slug() );
		$can_edit      = $this->can_edit_plugin( $plugin );
		?>
		<div class="col-12 col-md-6 col-xl-4 d-flex">
			<article class="card licencepress-plugin-card shadow-sm h-100 w-100">
				<div class="card-header d-flex align-items-center gap-2">
					<?php /* translators: %s is the plugin name. */ ?>
					<?php
					echo FormFieldHelper::switch(
						'licencepress-plugin-status',
						'1',
						'',
						array(
							'id'               => 'licencepress-plugin-status-' . SanitizationHelper::key( $plugin->get_slug() ),
							'checked'          => $enabled,
							'disabled'         => ! $can_edit,
							'data-licencepress-plugin-toggle' => 'true',
							'data-plugin-slug' => $plugin->get_slug(),
							'aria-label'       => sprintf(
								/* translators: %s is the plugin name. */
								__( 'Enable %s', 'licencepress' ),
								$plugin->get_name()
							),
						)
					);
					?>
					<span class="fw-semibold"><?php echo esc_html( $plugin->get_name() ); ?></span>
				</div>
				<div class="card-body d-flex flex-column">
					<?php $this->render_plugin_icon( $plugin ); ?>
					<p class="card-text text-secondary mt-3"><?php echo esc_html( $plugin->get_description() ); ?></p>
					<p class="card-text mb-2"><span class="text-secondary"><?php esc_html_e( 'Author:', 'licencepress' ); ?></span> <?php echo esc_html( $plugin->get_author() ); ?></p>
					<p class="card-text mb-2"><span class="text-secondary"><?php esc_html_e( 'Version:', 'licencepress' ); ?></span> <?php echo esc_html( $plugin->get_version() ); ?></p>
					<p class="card-text mb-3"><span class="text-secondary"><?php esc_html_e( 'Docs:', 'licencepress' ); ?></span> <a href="<?php echo esc_url( $plugin->get_uri() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View documentation', 'licencepress' ); ?></a></p>
					<?php if ( ! empty( $settings_page['fields'] ) ) : ?>
						<?php
						echo FormFieldHelper::button(
							__( 'Settings', 'licencepress' ),
							array(
								'type'           => 'button',
								'class'          => 'btn-primary mt-auto',
								'data-bs-toggle' => 'modal',
								'data-bs-target' => '#' . $modal_id,
							)
						);
						?>
					<?php endif; ?>
				</div>
			</article>
		</div>
		<?php

		if ( ! empty( $settings_page['fields'] ) ) {
			$this->render_plugin_settings_modal( $plugin, $settings_page, $modal_id, $can_edit );
		}
	}

	/**
	 * Render a plugin icon from its declared icon variant.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 */
	private function render_plugin_icon( PluginInterface $plugin ): void {
		$icon = $plugin->get_icon();

		if ( is_array( $icon ) && ! empty( $icon[0] ) ) {
			$split_icon_classes = preg_split( '/\s+/', trim( $icon[0] ) );
			$icon_classes       = array_filter(
				array_map( 'sanitize_html_class', is_array( $split_icon_classes ) ? $split_icon_classes : array() )
			);
			$icon_class         = implode( ' ', $icon_classes );
			if ( '' === $icon_class ) {
				$icon_class = 'dashicons dashicons-admin-plugins';
			}
			$color = isset( $icon[1] ) ? sanitize_hex_color( $icon[1] ) : '';
			$style = $color ? 'color: ' . esc_attr( $color ) . ';' : '';

			printf(
				'<i class="licencepress-plugin-icon %1$s" style="%2$s" aria-hidden="true"></i>',
				esc_attr( $icon_class ),
				esc_attr( $style )
			);
			return;
		}

		if ( is_string( $icon ) && '' !== $icon ) {
			printf( '<img src="%1$s" class="licencepress-plugin-icon" alt="" aria-hidden="true" />', esc_url( $icon ) );
			return;
		}
		?>

		<span class="licencepress-plugin-icon dashicons dashicons-admin-plugins" aria-hidden="true"></span>';
		<?php
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_plugin_settings_modal(
		PluginInterface $plugin,
		array $settings_page,
		string $modal_id,
		bool $can_edit
	): void {
		$settings_group = SanitizationHelper::key( $settings_page['settings_group'] ?? $settings_page['slug'] );
		$values         = Settings::get_group( $settings_group, array() ) ?? array();
		?>
		<div class="modal fade licencepress-plugin-settings-modal" id="<?php echo esc_attr( $modal_id ); ?>" tabindex="-1" aria-labelledby="<?php echo esc_attr( $modal_id . '-label' ); ?>" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title fs-5" id="<?php echo esc_attr( $modal_id . '-label' ); ?>"><?php echo esc_html( $plugin->get_name() ); ?></h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'licencepress' ); ?>"></button>
					</div>
					<div class="modal-body">
						<section class="licencepress-plugin-modal-info mb-4" aria-labelledby="<?php echo esc_attr( $modal_id . '-info' ); ?>">
							<h3 class="h6" id="<?php echo esc_attr( $modal_id . '-info' ); ?>"><?php esc_html_e( 'Plugin information', 'licencepress' ); ?></h3>
							<p class="text-secondary mb-3"><?php echo esc_html( $plugin->get_description() ); ?></p>
							<dl class="row mb-0 small">
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'Author', 'licencepress' ); ?></dt>
								<dd class="col-sm-9"><?php echo esc_html( $plugin->get_author() ); ?></dd>
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'Version', 'licencepress' ); ?></dt>
								<dd class="col-sm-9"><?php echo esc_html( $plugin->get_version() ); ?></dd>
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'License', 'licencepress' ); ?></dt>
								<dd class="col-sm-9 mb-0"><?php echo esc_html( $plugin->get_license() ); ?></dd>
							</dl>
						</section>
						<form class="licencepress-plugin-settings-form" data-plugin-settings-form data-plugin-slug="<?php echo esc_attr( $plugin->get_slug() ); ?>" data-internal-licencepress-fields>
							<h3 class="h6 mb-3"><?php echo esc_html( $settings_page['title'] ?? $settings_page['label'] ); ?></h3>
							<fieldset <?php disabled( ! $can_edit ); ?>>
								<?php $this->render_plugin_settings_fields( $settings_page, $values, $modal_id ); ?>
							</fieldset>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e( 'Cancel', 'licencepress' ); ?></button>
						<?php if ( $can_edit ) : ?>
							<button type="button" class="btn btn-primary" data-plugin-settings-save><?php esc_html_e( 'Save', 'licencepress' ); ?></button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Render a card for a plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 */
	private function can_view_plugin( PluginInterface $plugin ): bool {
		$capability = $this->is_internal_plugin( $plugin )
			? 'licencepress_settings_plugins_int_view'
			: 'licencepress_settings_plugins_ext_view';
		return current_user_can( $capability );
	}
	/**
	 * Check if the current user can edit the settings of a plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 * @return bool True if the user can edit, false otherwise.
	 */
	private function can_edit_plugin( PluginInterface $plugin ): bool {
		$capability = $this->is_internal_plugin( $plugin )
			? 'licencepress_settings_plugins_int_edit'
			: 'licencepress_settings_plugins_ext_edit';
		return current_user_can( $capability );
	}
	/**
	 * Check if a plugin is an internal LicencePress plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 * @return bool True if the plugin is internal, false otherwise.
	 */
	private function is_internal_plugin( PluginInterface $plugin ): bool {
		return 0 === strpos( get_class( $plugin ), 'LicencePress\\Includes\\Plugins\\' );
	}
	/**
	 * Render the settings fields for a plugin's settings page.
	 *
	 * @param array  $settings_page The settings page configuration.
	 * @param array  $values The current values of the settings.
	 * @param string $prefix The prefix for the field IDs.
	 */
	private function render_plugin_settings_fields( array $settings_page, array $values, string $prefix ): void {
		$layout = SanitizationHelper::key( $settings_page['layout'] ?? 'box', 'box' );
		$layout = in_array(
			$layout,
			array( 'table', 'box' ),
			true
		) ? $layout : 'box';

		if ( 'table' === $layout ) {
			?>
			<div class="licencepress-plugin-settings-fields licencepress-plugin-settings-fields-table"><table class="table align-middle"><tbody>
			<?php
		} else {
			?>
			<div class="licencepress-plugin-settings-fields licencepress-plugin-settings-fields-box">
			<?php
		}

		foreach ( $settings_page['fields'] as $field ) {
			$key = SanitizationHelper::key( $field['key'] ?? '' );
			if ( '' === $key ) {
				continue;
			}

			$default            = array_key_exists( 'default', $field ) ? $field['default'] : false;
			$value              = $values[ $key ] ?? $default;
			$type               = SanitizationHelper::key( $field['type'] ?? 'checkbox', 'checkbox' );
			$id                 = SanitizationHelper::key( $prefix . '-' . $key );
			$name               = 'settings[' . $key . ']';
			$wrapper_attributes = array();
			if ( ! empty( $field['wrapper_class'] ) ) {
				$wrapper_attributes['class'] = (string) $field['wrapper_class'];
			}
			if ( ! empty( $field['wrapper_attributes'] ) && is_array( $field['wrapper_attributes'] ) ) {
				$wrapper_attributes = array_merge( $wrapper_attributes, $field['wrapper_attributes'] );
			}
			if ( ! empty( $field['visible_when'] ) && is_array( $field['visible_when'] ) ) {
				$wrapper_attributes['data-licencepress-visible-when'] = wp_json_encode( $field['visible_when'] );
			}
			$wrapper_attributes = FormFieldHelper::attributes_to_string( $wrapper_attributes );
			$label              = FormFieldHelper::label(
				$id,
				(string) ( $field['label'] ?? $key ),
				array(
					'tooltip'      => (string) ( $field['tooltip'] ?? '' ),
					'tooltip_type' => SanitizationHelper::key(
						$field['tooltip_type'] ?? 'question',
						'question'
					),
					'tooltip_icon' => (string) ( $field['tooltip_icon'] ?? '' ),
				)
			);
			if ( 'table' === $layout ) {
				?>
				<tr<?php echo $wrapper_attributes ? ' ' . $wrapper_attributes : ''; ?>>
					<th scope="row" class="w-50"><?php echo wp_kses_post( $label ); ?></th>
					<td>
			<?php } else { ?>
				<article class="licencepress-plugin-settings-field card h-100"<?php echo $wrapper_attributes ? ' ' . $wrapper_attributes : ''; ?>>
					<div class="card-body">
						<div class="licencepress-plugin-settings-field-header d-flex align-items-start justify-content-between gap-3">
							<?php echo wp_kses_post( $label ); ?>
				<?php
				if ( 'checkbox' === $type ) {
					echo FormFieldHelper::switch(
						$name,
						'1',
						'',
						array(
							'id'            => $id,
							'checked'       => ! empty( $value ),
							'wrapper_class' => 'ms-auto flex-shrink-0',
						)
					);
				}
				?>
						</div>
						<?php if ( ! empty( $field['description'] ) ) { ?>
							<p class="licencepress-plugin-settings-field-description text-secondary mb-3">
								<?php echo esc_html( (string) $field['description'] ); ?>
							</p>
						<?php } ?>
					</div>
				</article>
				<?php
			}
			if ( 'custom' === $type && ! empty( $field['render'] ) && is_callable( $field['render'] ) ) {
				call_user_func( $field['render'], $value, $name, $id );
			} elseif ( 'table' === $layout && 'select' === $type ) {
				echo FormFieldHelper::select(
					$name,
					(array) ( $field['options'] ?? array() ),
					$value,
					array(
						'id'         => $id,
						'attributes' => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'table' === $layout && 'multiselect' === $type ) {
				echo FormFieldHelper::bootstrap_multiselect(
					$name,
					array(
						'id'                  => $id,
						'data'                => (array) ( $field['options'] ?? array() ),
						'selected'            => (array) $value,
						'dropup_auto'         => $field['dropup_auto'] ?? true,
						'show_tick'           => $field['show_tick'] ?? null,
						'selection_indicator' => $field['selection_indicator'] ?? null,
						'attributes'          => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'table' === $layout && 'text' === $type ) {
				echo FormFieldHelper::input(
					$name,
					is_scalar( $value ) ? (string) $value : '',
					array(
						'id'   => $id,
						'type' => 'text',
					)
				);
			} elseif ( 'table' === $layout ) {
				echo FormFieldHelper::checkbox(
					$name,
					'1',
					'',
					array(
						'id'      => $id,
						'checked' => ! empty( $value ),
					)
				);
			} elseif ( 'select' === $type ) {
				echo FormFieldHelper::select(
					$name,
					(array) ( $field['options'] ?? array() ),
					$value,
					array(
						'id'         => $id,
						'attributes' => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'multiselect' === $type ) {
				echo FormFieldHelper::bootstrap_multiselect(
					$name,
					array(
						'id'                  => $id,
						'data'                => (array) ( $field['options'] ?? array() ),
						'selected'            => (array) $value,
						'dropup_auto'         => $field['dropup_auto'] ?? true,
						'show_tick'           => $field['show_tick'] ?? null,
						'selection_indicator' => $field['selection_indicator'] ?? null,
						'attributes'          => $field['attributes'] ?? array(),
					)
				);
			} elseif ( in_array( $type, array( 'text', 'email', 'url', 'number' ), true ) ) {
				echo FormFieldHelper::input(
					$name,
					is_scalar( $value ) ? (string) $value : '',
					array(
						'id'   => $id,
						'type' => $type,
					)
				);
			} elseif ( 'textarea' === $type ) {
				$textarea_value = is_scalar( $value )
					? (string) $value
					: wp_json_encode( $value, JSON_PRETTY_PRINT );
				echo FormFieldHelper::textarea(
					$name,
					(string) $textarea_value,
					array(
						'id'   => $id,
						'rows' => 6,
					)
				);
			}
			echo 'table' === $layout ? '</td></tr>' : '</div></article>';
		}

		if ( 'table' === $layout ) {
			?>
			</tbody>
		</table>
	</div>
		<?php } else { ?>
			</div>
			<?php
		}
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_third_party_plugin_card( string $file, array $plugin ): void {
		$active = function_exists( 'is_plugin_active' ) && is_plugin_active( $file );
		?>
		<div class="col-12 col-md-6 col-xl-6 d-flex">
			<article class="card licencepress-plugin-card shadow-sm h-100 w-100">
				<div class="card-header d-flex align-items-center gap-2">
					<?php /* translators: %s is the plugin name. */ ?>
					<?php
					echo FormFieldHelper::switch(
						'licencepress-third-party-status',
						'1',
						'',
						array(
							'id'         => 'licencepress-third-party-status-' . SanitizationHelper::key( $file ),
							'checked'    => $active,
							'disabled'   => true,
							'aria-label' => sprintf(
								/* translators: %s is the plugin name. */
								__( 'Enable %s', 'licencepress' ),
								$plugin['Name'] ?? $file
							),
						)
					);
					?>
					<span class="fw-semibold">
						<?php echo esc_html( $plugin['Name'] ?? $file ); ?>
					</span>
				</div>
				<div class="card-body d-flex flex-column">
					<span class="licencepress-plugin-icon dashicons dashicons-admin-plugins" aria-hidden="true"></span>
					<p class="card-text text-secondary mt-3">
						<?php echo esc_html( $plugin['Description'] ?? __( 'No description provided.', 'licencepress' ) ); ?>
					</p>
					<p class="card-text mb-2">
						<span class="text-secondary">
							<?php esc_html_e( 'Author:', 'licencepress' ); ?>
						</span>
						<?php echo esc_html( $plugin['AuthorName'] ?? wp_strip_all_tags( $plugin['Author'] ?? __( 'Unknown', 'licencepress' ) ) ); ?>
					</p>
					<p class="card-text mb-2">
						<span class="text-secondary">
							<?php esc_html_e( 'Version:', 'licencepress' ); ?>
						</span>
						<?php echo esc_html( $plugin['Version'] ?? __( 'Unknown', 'licencepress' ) ); ?>
					</p>
					<p class="card-text mb-3">
						<span class="text-secondary">
							<?php esc_html_e( 'Docs:', 'licencepress' ); ?>
						</span> 
						<?php if ( ! empty( $plugin['PluginURI'] ) ) : ?>
							<a href="<?php echo esc_url( $plugin['PluginURI'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View documentation', 'licencepress' ); ?>
							</a>
						<?php else : ?>
							<?php esc_html_e( 'Not available', 'licencepress' ); ?>
						<?php endif; ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="btn btn-primary mt-auto">
						<?php esc_html_e( 'Settings', 'licencepress' ); ?>
					</a>
				</div>
			</article>
		</div>
		<?php
	}
}
