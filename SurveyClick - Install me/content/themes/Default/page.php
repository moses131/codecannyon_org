<?php if( !defined( 'DIR' ) || !isset( item()->object ) ) return;

filters()->add_filter( 'title_tag', function() {
    return esc_html( item()->object->getTitle() );
} );

filters()->add_filter( 'image_meta', function() {
    return esc_html( current( item()->object->getThumbnails() ) );
} );

?>

<div class="defp bg3">
    <div class="main-wrapper">
        <h2 class="title"><?php esc_html_e( item()->object->getTitle() ); ?></h2>
        <div class="df">
            <ul class="brcr">
                <?php switch( item()->object->getType() ) {
                    case 'blog-posts': ?>
                    <li><a href="<?php echo site_url(); ?>"><?php t_e( 'Home', 'def-theme' ); ?></a></li>
                    <li><a href="<?php echo site_url( 'blog' ); ?>"><?php t_e( 'Blog', 'def-theme' ); ?></a></li>
                    <?php break;
                    default: ?>
                    <li><a href="<?php echo site_url(); ?>"><?php t_e( 'Home', 'def-theme' ); ?></a></li>
                    <?php break;
                } ?>
            </ul>
        </div>
    </div>
</div>

<div style="display:flex;overflow:hidden;">
    <svg preserveAspectRatio="none" viewBox="0 0 1440 36" class="u-block" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;margin:-1px 0;"><rect width="100%" height="100%" fill="#e3e2df"></rect><path d="M1440 36V8.2s-105.6-1.2-160.7-6a877 877 0 00-150.5 2.5c-42.1 3.9-140 15-223 15C754 19.6 700.3 6.8 548.8 7c-143.7 0-273.4 11.5-350 12.6-76.6 1.2-198.8 0-198.8 0V36h1440z" fill="#fff"></path></svg>
</div>

<div class="defp bg1">
    <div class="main-wrapper page-content">
        <?php echo item()->object->getContent(); ?>
    </div>
</div>

<div class="defp bg0">
    <div class="main-wrapper">
    <h2 class="title"><?php t_e( 'Do you like it? Share!', 'def-theme' ); ?></h2>
        <div class="sic">
            <ul class="shareic">
                <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php esc_url_e( urlencode( item()->object->getPermalink() ) ); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="https://twitter.com/intent/tweet?url=<?php esc_url_e( urlencode( item()->object->getPermalink() ) ); ?>&text=<?php esc_html_e( urlencode( item()->object->getTitle() ) ); ?>" target="_blank"><i class="fab fa-twitter"></i></a></li>
            </ul>
        </div>
    </div>
</div>

<div class="defp bg1">
    <div class="main-wrapper">
    <h2 class="title"><?php t_e( 'Related articles', 'def-theme' ); ?></h2>
        <div class="grid">
            <?php foreach( ( $pages = item()->object->getRelatedPages() )->orderBy( 'rand' )->fetch( 2 ) as $page ) {
                $pages      ->setObject( $page );
                $thumb      = $pages->getThumbnails();
                $cthumb     = !empty( $thumb ) ? current( $thumb ) : false;
                $meta       = $pages->getMeta( 'duration' );
                $duration   = $meta['duration'] ?? false;

                echo '
                <div class="item bg0">
                    <div class="img"' . ( $cthumb ? ' style="background-image:url(' . esc_url( current( $thumb ) ) . ');"' : '' ) . '></div>
                    <div class="ibody">
                        <div class="author">
                            ' . ( $duration ? '<span>' . sprintf( t( '~ %s min.', 'def-theme' ), (int) $duration ). '</span>' : '' ) . '
                            <span>' . custom_time( $pages->getDate(), 2 ) . '</span>
                        </div>
                        <h2>' . esc_html( $pages->getTitle() ) . '</h2>
                        <div class="link">
                            <a href="' . $pages->getPermalink() . '" class="sline"><span>' . t( 'Read more', 'def-theme' ) . '</span></a>
                        </div>
                    </div>
                </div>
                ';
            } ?>
        </div>
    </div>
</div>