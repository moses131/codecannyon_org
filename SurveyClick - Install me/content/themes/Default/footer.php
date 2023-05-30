<footer class="defp">
    <div class="main-wrapper">
        <div class="lang-sw">
            <strong><i class="fas fa-globe"></i> <?php t_e( 'Choose your language', 'def-theme' ); ?></strong>
        </div>

        <ul class="menu menu-ls s2">
            <?php 
            $cl = getLanguage();
            array_map( function( $v ) use( $cl ) {
                if( $cl['locale_e'] == $v['locale_e'] )
                echo '<li><strong>' . $v['name'] . '</strong></li>';
                else
                echo '<li><a href="#" data-lang="' . $v['locale_e'] . '">' . $v['name'] . '</a></li>';
            }, getLanguages() ); ?>
        </ul>

        <hr>

        <?php echo menus()->getMenu( 'theme_footer_menu', 'menu' ); 
        $footer_opts = theme_option_lang( 'footer' ); ?>
        <div class="afooter">
            <div class="copyrights"><?php echo ( !empty( $footer_opts['footer_copyright'] ) ? esc_html( $footer_opts['footer_copyright'] ) : t( '(c) 2021 | All rights reserved' ) ); ?></div>
            <?php if( !empty( $footer_opts['social_profiles'] ) ) {
                echo '<ul>';
                foreach( $footer_opts['social_profiles'] as $snetwork ) {
                    if( !empty( $snetwork['name'] ) && !empty( $snetwork['link'] ) )
                    echo '<li><a href="' . esc_url( $snetwork['link'] ) . '" target="_blank">' . esc_html( $snetwork['name'] ) . '</a></li>';
                }
                echo '</ul>';
            } ?>
        </div>
    </div>
</footer>

<?php getFooter(); ?>

</body>
</html>