<div class="defp bg3">
    <div class="main-wrapper">
        <h2 class="title"><span><?php t_e( 'Our blog', 'def-theme' ); ?></span></h2>
        <div class="df bpb mb0">
            <ul class="brcr">
                <li><a href="<?php echo site_url(); ?>"><?php t_e( 'Home', 'def-theme' ); ?></a></li>
            </ul>
        </div>
    </div>
</div>

<div style="display:flex;overflow:hidden;">
    <svg preserveAspectRatio="none" viewBox="0 0 1440 36" class="u-block" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;margin:-1px 0;"><rect width="100%" height="100%" fill="#e3e2df"></rect><path d="M1440 36V8.2s-105.6-1.2-160.7-6a877 877 0 00-150.5 2.5c-42.1 3.9-140 15-223 15C754 19.6 700.3 6.8 548.8 7c-143.7 0-273.4 11.5-350 12.6-76.6 1.2-198.8 0-198.8 0V36h1440z" fill="#fff"></path></svg>
</div>

<div class="defp bg1">
    <div class="main-wrapper">
        <div class="grid">
            <?php foreach( ( $pages = pages()->userView( 'blog-posts' )->setItemsPerPage( 6 ) )->fetch() as $page ) {
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

<?php echo ( new \theme\helpers\pagination( '' ) )->markup( $pages->getPagination() ); 
nav_active( 'blog' ); ?>