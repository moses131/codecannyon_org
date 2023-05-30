<div class="df h100">
    <div class="tc mauto">
        <i class="fas fa-key ico"></i>
        <h2 class="mb20"><?php t_e( 'Password' ); ?></h2>
        <div class="df mb20">
            <div class="mc">
                <form method="POST">
                    <div class="form_line mb20">
                        <input name="check_password" type="text" />
                    </div>
                    <button><?php t_e( 'Go' ); ?></button>
                </form>
            </div>
        </div>
        <a href="<?php esc_url_e( get_option( 'site_url' ) ); ?>" class="link"><?php esc_html_e( get_option( 'website_name' ) ); ?></a>
    </div>
</div>