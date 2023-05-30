<div class="df h100">
    <div class="tc mauto">
        <i class="fas fa-lock ico"></i>
        <h2 class="mb20"><?php t_e( 'Please login' ); ?></h2>
        <div class="df mb20">
            <div class="mc">
            <?php echo '
            <a href="' . esc_url( admin_url() ) . '" class="btn">' . t( 'Login' ) . '</a>
            <span class="mtext">' . t( 'or' ) . '</span> 
            <a href="' . esc_url( admin_url( 'register' ) ) . '" class="btn">' . t( 'Register' ) . '</a>';
            ?>
            </div>
        </div>
        <a href="<?php esc_url_e( get_option( 'site_url' ) ); ?>" class="link"><?php esc_html_e( get_option( 'website_name' ) ); ?></a>
    </div>
</div>