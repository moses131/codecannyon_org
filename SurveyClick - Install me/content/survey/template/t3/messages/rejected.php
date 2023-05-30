<div class="df h100">
    <div class="tc fpage articles">
        <article class="mta">
            <div class="content">
            <?php if( $page = getPage( 'disqualified' ) ) {
                if( $page['isDefault'] ) {
                    echo '<i class="fas fa-exclamation-triangle ico"></i>';
                    echo '<h2>' . $page['content'] . '</h2>';
                } else {
                    echo getContent( $page['content'] );
                } 
             } ?>
            </div>
        </article>
        <div class="pb20">
            <a href="<?php esc_url_e( site_url() ); ?>" class="btn"><?php t_e( 'Home' ); ?></a>
        </div>
    </div>
</div>