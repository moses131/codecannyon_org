<?php 

$settings   = getMeta( 'af:thank_you', '' );
$options    = $settings !== '' && !empty( $settings ) && ( $options = json_decode( $settings, true ) ) ? $options : [];

if( isset( $options['r'] ) ) {
    header( 'Location: ' . esc_url( $options['r'] ) );
    die;
} ?>
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
            <?php if( isset( $options['dr'] ) ) { ?>
                <script>
                    const el = document.createElement( 'div' );
                    el.innerHTML = "<?php t_e( "You'll be redirected in <strong class='sec'>10</strong> second(s)" ); ?>";
                    document.write( el.outerHTML );
                    const sec = document.querySelector( 'strong.sec' );
                    var secc = 10;
                    const intval = setInterval( () => {
                        secc--;
                        sec.innerHTML = secc;
                        if( secc == 1 ) {
                            clearInterval( intval );
                            window.location = "<?php echo esc_url( $options['dr'] ); ?>";
                        }
                    }, 1000 );
                </script>
            <?php } ?>
            <a href="<?php esc_url_e( site_url() ); ?>" class="btn"><?php t_e( 'Home' ); ?></a>
        </div>
    </div>
</div>