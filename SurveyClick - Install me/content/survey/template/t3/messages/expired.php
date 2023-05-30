<div class="df h100">
    <div class="tc fpage articles">
        <article class="mta">
            <div class="content">
                <div class="ico">
                    <i class="fas fa-hourglass-end"></i>
                </div>
                <h2 class="title"><?php echo getTextMsg( 'rtime', t( 'Response time has expired' ) ); ?></h2>
                <?php if( survey()->meta()->get( 'restart', false ) ) { ?>
                <div class="df">
                    <div class="mauto">
                        <a href="#" data-ajax="<?php echo ajax()->get_call_url( 'survey', [ 'action2' => 'restart', 'collector' => esc_html( collector()->getSlug() ) ] ); ?>" class="btn"><?php echo getTextMsg( 'restart', t( 'Restart survey' ) ); ?></a>
                
                    </div>
                </div>
                <?php } ?>
            </div>
        </article>
        <div class="pb20">
            <a href="<?php esc_url_e( site_url() ); ?>" class="btn"><?php t_e( 'Home' ); ?></a>
        </div>
    </div>
</div>