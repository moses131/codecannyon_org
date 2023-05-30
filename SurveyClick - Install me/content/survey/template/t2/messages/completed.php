<div class="df h100">
    <div class="tc fpage">
        <div class="mta">
            <div class="content">
            <?php if( $page = getPage( 'thank_you' ) ) {
                if( $page['isDefault'] ) {
                    echo '<div class="ico"><i class="fas fa-check"></i></div>';
                    echo '<h2>' . $page['content'] . '</h2>';
                } else {
                    echo getContent( $page['content'] );
                }
             } ?>
            </div>
        </div>
        <div class="pb20">
            <a href="<?php esc_url_e( site_url() ); ?>" class="btn"><?php t_e( 'Home' ); ?></a>
        </div>
    </div>
</div>