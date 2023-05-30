<?php getHeader(); ?>

<header<?php echo ( empty( $_GET['path'] ) ? ' class="big"' : '' ); ?>>
    <div class="mbh bg3">
        <div class="main-wrapper">
            <div class="bh">
                <?php 
                $header_opts = theme_option_lang( 'header' );
                if( !empty( $header_opts['phone_no'] ) || !empty( $header_opts['email'] ) ) {
                    echo '<ul class="l">';
                    if( !empty( $header_opts['phone_no'] ) )
                    echo '<li><i class="fas fa-mobile-alt"></i> ' . t( 'Call Us:', 'def-theme' ) . ' <a href="tel:' . esc_html( $header_opts['phone_no'] ) . '" class="sline"><span>' . esc_html( $header_opts['phone_no'] ) . '</span></a></li>';
                    if( !empty( $header_opts['email'] ) )
                    echo '<li><i class="far fa-envelope"></i> ' . t( 'Email Us:', 'def-theme' ) . ' <a href="mailto:' . esc_html( $header_opts['email'] ) . '" class="sline"><span>' . esc_html( $header_opts['email'] ) . '</span></a></li>';
                    echo '</ul>';
                } ?>
                <div class="r">
                    <ul>
                        <li><a href="<?php echo site_url( 'help' ); ?>"><?php t_e( 'Help', 'def-theme' ); ?></a></li>
                        <?php if( !me() ) { ?>
                        <li><a href="<?php echo admin_url( 'login' ); ?>"><?php t_e( 'Sign in', 'def-theme' ); ?></a></li>
                        <?php } ?>
                    </ul>
                    <select class="lang">
                        <?php 
                        $cl = getLanguage();
                        array_map( function( $v ) use( $cl ) {
                            if( $cl['locale_e'] == $v['locale_e'] )
                            echo '<option selected disabled>' . $v['name'] . '</option>';
                            else
                            echo '<option value="' . $v['locale_e'] . '">' . $v['name'] . '</option>';
                        }, getLanguages() ); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <nav>
        <div class="main-wrapper">
            <div class="menu-container">
                <?php echo \theme\helpers\parts::main_logo();
                echo \theme\helpers\parts::second_logo(); ?>
                <div class="menu-links">
                    <?php echo menus()->getMenu( 'theme_main_menu', 'menu', 'dd' ); ?>
                    <div>
                        <?php if( me() ) { ?>
                            <a href="<?php echo admin_url( '/' ); ?>" class="btn"><i class="fas fa-user"></i> <?php esc_html_e( me()->getDisplayName() ); ?></a>
                        <?php } else { ?>
                            <a href="<?php echo admin_url( 'register' ); ?>" class="btn"><i class="fas fa-user"></i> <?php t_e( 'Sign up', 'def-theme' ); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <a href="#" class="mmenu">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <?php if( empty( $_GET['path'] ) ) { 
    $options    = theme_option_lang( 'index' );
    $hero_img   = !empty( $options['hero_img'] ) ? current( $options['hero_img'] ) : NULL; ?>
    <div class="hero<?php echo ( $hero_img ? '' : ' nimg' ); ?>">
        <div class="main-wrapper">
            <div class="txt">
                <h2 class="sline"><span>
                    <?php echo ( !empty( $options['index_msg'] ) ? esc_html( $options['index_msg'] ) : t( 'Build beautiful surveys.', 'def-theme' ) ); ?>
                </span></h2>
                <?php if( isset(  $options['subtitle'] ) ) { ?>
                <div class="txt2"><?php esc_html_e( $options['subtitle'] ); ?></div>
                <?php }
                if( !empty( $options['cta_name'] ) ) { ?>
                <div class="df mt60">
                    <div class="df btnc">
                        <a href="<?php echo ( !empty( $options['cta_link'] ) ? esc_url( $options['cta_link'] ) : '#' ); ?>" class="btn big"><?php esc_html_e( $options['cta_name'] ); ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php if( $hero_img ) { ?>
            <div class="img">
                <img src="<?php esc_url_e( $hero_img ); ?>" alt="">
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</header>

<?php if( !empty( $options['hl_link']['title'] ) ) { ?>
    <div class="bg4">
        <div class="main-wrapper">
            <div class="cta2 deftp">
                <a href="<?php echo esc_url( $options['hl_link']['link_url'] ); ?>"><?php esc_html_e( $options['hl_link']['title'] ); ?></a>
            </div>
        </div>
    </div>
<?php } ?>