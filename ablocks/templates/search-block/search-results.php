
<li class="ablocks-block--search-result__list">			
	<?php if ( $thumbnail ) : ?>
	<a href="<?php echo esc_url( $link ); ?>"> <img class="ablocks-block--search-result__list-thumbnail" src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $title ); ?>"></a>
	<?php else : ?>
	<a href="<?php echo esc_url( $link ); ?>"><img class="ablocks-block--search-result__list-thumbnail" src="<?php echo esc_url( ABLOCKS_ASSETS_URL . 'images/search.png' ); ?>"></a>
	<?php endif; ?>
	<a class="ablocks-block--search-result__list-title" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
</li>
