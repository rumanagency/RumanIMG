<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap rumanimg-wrap">

	<?php /* ── Header ── */ ?>
	<div class="rumanimg-header">
		<img
			src="<?php echo esc_url( RUMANIMG_URL . 'assets/images/logo.svg' ); ?>"
			alt="Ruman IMG"
			class="rumanimg-header__logo"
		/>
		<span class="rumanimg-header__version">v<?php echo esc_html( RUMANIMG_VERSION ); ?></span>
	</div>

	<div class="rumanimg-settings-grid">

		<?php /* ── Settings form ── */ ?>
		<div class="rumanimg-section">
			<h2 class="rumanimg-section__title">
				<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
				<?php esc_html_e( 'Settings', 'rumanimg' ); ?>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'rumanimg_settings_group' );
				do_settings_sections( 'rumanimg-settings' );
				submit_button( __( 'Save Changes', 'rumanimg' ), 'primary rumanimg-submit' );
				?>
			</form>
		</div>

		<?php /* ── About ── */ ?>
		<div class="rumanimg-section rumanimg-about">
			<h2 class="rumanimg-section__title">
				<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
				<?php esc_html_e( 'About', 'rumanimg' ); ?>
			</h2>

			<div class="rumanimg-about__logo-wrap">
				<img
					src="<?php echo esc_url( RUMANIMG_URL . 'assets/images/logo.svg' ); ?>"
					alt="Ruman"
					class="rumanimg-about__logo"
				/>
			</div>

			<ul class="rumanimg-about__list">
				<li>
					<span class="rumanimg-about__icon">👤</span>
					<span><?php esc_html_e( 'Saleh', 'rumanimg' ); ?></span>
				</li>
				<li>
					<span class="rumanimg-about__icon">🏢</span>
					<span><?php esc_html_e( 'Ruman Agency', 'rumanimg' ); ?></span>
				</li>
				<li>
					<span class="rumanimg-about__icon">✉️</span>
					<a href="mailto:saleh@ruman.sa">saleh@ruman.sa</a>
				</li>
				<li>
					<span class="rumanimg-about__icon">🌐</span>
					<a href="https://ruman.sa" target="_blank" rel="noopener noreferrer">ruman.sa</a>
				</li>
			</ul>

			<div class="rumanimg-about__version">
				<strong><?php esc_html_e( 'Version', 'rumanimg' ); ?>:</strong>
				<?php echo esc_html( RUMANIMG_VERSION ); ?>
			</div>
		</div>

	</div>

</div>
