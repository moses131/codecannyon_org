<?php 
// Prevent loading the page directly
if( !defined( 'DIR' ) ) return;

// Create an object for the selected category, if needed
$currentCategory = NULL;
if( !empty( item()->params[0] ) ) {
    $categories = categories();
    if( $categories->setSlug( item()->params[0] ) ) {
        $currentCategory = $categories;
        nav_active( 'help-' . $categories->getSlug() );
    }
} ?>

<div class="defp bg3">
    <div class="main-wrapper">
        <h2 class="title"><span><?php echo ( $currentCategory ? esc_html( $currentCategory->getName() ) : t( 'Help', 'def-theme' ) ); ?></span></h2>
        <div class="df bpb mb0">
            <ul class="brcr">
                <li><a href="<?php echo site_url(); ?>"><?php esc_html_e( 'Home', 'def-theme' ); ?></a></li>
                <?php if( $currentCategory ) { ?>
                <li><a href="<?php echo site_url( 'help' ); ?>"><?php esc_html_e( 'Help', 'def-theme' ); ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<div style="display:flex;overflow:hidden;">
    <svg preserveAspectRatio="none" viewBox="0 0 1440 36" class="u-block" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;margin:-1px 0;"><rect width="100%" height="100%" fill="#e3e2df"></rect><path d="M1440 36V8.2s-105.6-1.2-160.7-6a877 877 0 00-150.5 2.5c-42.1 3.9-140 15-223 15C754 19.6 700.3 6.8 548.8 7c-143.7 0-273.4 11.5-350 12.6-76.6 1.2-198.8 0-198.8 0V36h1440z" fill="#fff"></path></svg>
</div>

<div class="bg1 defp">
    <div class="main-wrapper">
        <div class="cols2">
            <div class="">
                <ul class="help-nav">
                    <?php $categories = categories()->userView( 'help-posts' );
                    foreach( $categories->fetch( -1 ) as $category ) {
                        $categories->setObject( $category );
                        echo '<li' . ( $currentCategory && ( $currentCategory->getId() == $categories->getId() || $currentCategory->getParentId() == $categories->getId() ) ? ' class="selected"' : '' ) . '><a href="' . site_url( 'help/' . $categories->getSlug() ) . '">' . esc_html( $categories->getName() ) . '</a>';
                        $childs = $categories->getChilds();
                        if( !empty( $childs ) ) {
                            echo '<ul>';
                            foreach( $childs as $child ) {
                                $categories->setObject( $child );
                                echo '<li' . ( $currentCategory && $currentCategory->getId() == $categories->getId() ? ' class="selected"' : '' ) . '><a href="' . site_url( 'help/' . $categories->getSlug() ) . '">' . esc_html( $categories->getName() ) . '</a></li>';
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    } ?>
                    <li></li>
                </ul>
            </div>

            <ul>
                <?php if( $currentCategory )
                    $pages = $currentCategory->getPages();
                else {
                    $pages = pages();
                    $pages ->userView( 'help-posts' );
                }

                if( $pages->count() ) {
                    foreach( $pages->fetch() as $page ) {
                        $pages  ->setObject( $page );
                        $meta   = $pages->getMeta( 'excerpt' );
                        $cats   = $pages->getCategoriesPages()
                                ->select( [ 'id', 'slug', 'title' ] );
                        $catsL  = $cats->fetch( -1 );
                        echo '
                        <li>
                            <h2>' . esc_html( $pages->getTitle() ) . '</h2>';
                            if( isset( $meta['excerpt'] ) )
                            echo '<div>' . esc_html( $meta['excerpt'] ) . '</div>';
                            echo '
                            <a href="' . $pages->getPermalink() . '" class="btn">' . t( 'Read more', 'def-theme' ) . '</a>
                        </li>';
                    }
                } ?>
            </ul>
        </div>
    </div>
</div>

<?php echo ( new \theme\helpers\pagination( '' ) )->markup( $pages->getPagination() ); 
nav_active( 'help' ); ?>