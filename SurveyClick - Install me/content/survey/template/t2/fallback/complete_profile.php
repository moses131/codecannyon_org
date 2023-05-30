<div class="df h100">
    <div class="tc mauto">
        <i class="fas fa-user-edit ico"></i>
        <h2 class="mb20"><?php t_e( 'Please complete your profile' ); ?></h2>
        <div class="df mb20">
            <div class="mc">
                <a href="<?php esc_url_e( admin_url() ); ?>" class="btn"><?php t_e( 'Manage your account' ); ?></a>
            </div>
        </div>
        <a href="<?php esc_url_e( get_option( 'site_url' ) ); ?>" class="link"><?php esc_html_e( get_option( 'website_name' ) ); ?></a>
    </div>
</div>