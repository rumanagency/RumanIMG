<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = $attributes['parsedItems'] ?? [];

if ( empty( $items ) ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'rumanimg-gallery' ] ); ?>>
	<?php foreach ( $items as $item ) :
		$page_url  = esc_url( $item['pageUrl'] );
		$img_url   = esc_url( $item['imgUrl'] );
		$site_name = esc_attr( $item['siteName'] );
	?>
		<a href="<?php echo $page_url; ?>"
		   class="rumanimg-gallery__item"
		   data-src="<?php echo $img_url; ?>"
		   data-site="<?php echo $site_name; ?>">
			<img
				src="<?php echo $img_url; ?>"
				title="source: <?php echo $site_name; ?>"
				loading="lazy"
				alt=""
			/>
		</a>
	<?php endforeach; ?>
</div>
