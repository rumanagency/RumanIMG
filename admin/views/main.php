<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$stats = $this->get_stats();

$hosting_sites = [
	[ 'label' => 'Imgur',          'domain' => 'imgur.com',      'url' => 'https://imgur.com/upload'  ],
	[ 'label' => 'imgBB',          'domain' => 'imgbb.com',      'url' => 'https://imgbb.com/'        ],
	[ 'label' => 'FreeImage.host', 'domain' => 'freeimage.host', 'url' => 'https://freeimage.host/'  ],
	[ 'label' => 'PostImg',        'domain' => 'postimg.cc',     'url' => 'https://postimg.cc/'       ],
	[ 'label' => 'ImgBox',         'domain' => 'imgbox.com',     'url' => 'https://imgbox.com/'       ],
];

$posts_url       = admin_url( 'edit.php' );
$block_posts_url = admin_url( 'edit.php?s=wp%3Arumanimg%2Fimage-parser' );
?>
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

	<?php /* ── Stats ── */ ?>
	<div class="rumanimg-stats">

		<div class="rumanimg-stat-card">
			<div class="rumanimg-stat-card__inner">
				<span class="rumanimg-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['total_posts'] ) ); ?></span>
				<span class="rumanimg-stat-card__label"><?php esc_html_e( 'Published Posts', 'rumanimg' ); ?></span>
			</div>
			<a href="<?php echo esc_url( $posts_url ); ?>" class="rumanimg-stat-card__link">
				<?php esc_html_e( 'View all', 'rumanimg' ); ?> →
			</a>
		</div>

		<div class="rumanimg-stat-card rumanimg-stat-card--accent">
			<div class="rumanimg-stat-card__inner">
				<span class="rumanimg-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['block_posts'] ) ); ?></span>
				<span class="rumanimg-stat-card__label"><?php esc_html_e( 'Using Image Parser Block', 'rumanimg' ); ?></span>
			</div>
			<a href="<?php echo esc_url( $block_posts_url ); ?>" class="rumanimg-stat-card__link">
				<?php esc_html_e( 'View posts', 'rumanimg' ); ?> →
			</a>
		</div>

	</div>

	<?php /* ── Hosting links ── */ ?>
	<div class="rumanimg-section">
		<h2 class="rumanimg-section__title">
			<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm0 2h12v2.586l-4.293 4.293a1 1 0 01-1.414 0L6 7.586 4 9.586V5zm0 7.414l2-2 4.293 4.293a3 3 0 004.414 0L16 13.414V15H4v-2.586z"/></svg>
			<?php esc_html_e( 'Image Hosting Sites', 'rumanimg' ); ?>
		</h2>
		<p class="rumanimg-section__desc"><?php esc_html_e( 'Upload your photos to any of these services, then paste the embed code into the Image Parser block.', 'rumanimg' ); ?></p>
		<div class="rumanimg-hosting-links">
			<?php foreach ( $hosting_sites as $site ) : ?>
				<a
					href="<?php echo esc_url( $site['url'] ); ?>"
					class="rumanimg-hosting-link"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span class="rumanimg-hosting-link__label"><?php echo esc_html( $site['label'] ); ?></span>
					<span class="rumanimg-hosting-link__domain"><?php echo esc_html( $site['domain'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

</div>
