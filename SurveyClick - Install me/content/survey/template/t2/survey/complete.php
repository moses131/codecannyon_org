<form class="container articles" method="POST" enctype="multipart/form-data" data-send-survey="<?php echo ajax()->get_call_url( 'send-survey' ); ?>">
    <?php if( ( $logoId = getMeta( 'logo', false ) ) && ( $logoURL = mediaLinks( $logoId )->getItemURL() ) ) { ?>
    <article class="article logo">
        <img src="<?php esc_url_e( $logoURL ); ?>" />
    </article>
    <?php }
    $title  = response()->getSetting( 'title' );
    $desc   = response()->getSetting( 'desc' ); 
    if( $title || $desc ) { ?>
    <article>
        <?php if( !empty( $title ) ) { ?>
            <h2 class="title"><?php echo getInlineContent( $title ); ?></h2>
        <?php }
        if( !empty( $desc ) ) { ?>
        <div><?php echo getInlineContent( $desc ) ; ?></div>
        <?php } ?>
    </article>
    <?php } ?>

    <?php foreach( getQuestions()->fetch( -1 ) as $question ) {
        getQuestion( $question );
        echo questionMarkup();
    } ?>

    <article class="bt">
        <div class="btns df">
            <input type="hidden" name="results" value="<?php esc_html_e( response()->getId() ); ?>" />
            <?php if( response()->hasPrevStep() ) { ?>
            <div class="prev asc">
                <a href="#" class="link" data-ajax="prev-page-survey" data-data='<?php echo cms_json_encode( [ 'results' => response()->getId() ] ); ?>'><?php echo getTextMsg( 'prev', t( 'Previous' ) ); ?></a>
            </div>
            <?php } ?>
            <input type="hidden" name="results" value="<?php esc_html_e( response()->getId() ); ?>" />
            <div class="mla">
                <button>
                    <span><?php echo getTextMsg( 'next', t( 'Next' ) ); ?></span>
                </button>
            </div>
        </div>
        <?php  if( !limits()->removeBrand() ) { ?>
        <div class="copy"><?php echo sprintf( 'Powered by <a href="%s" target="_blank">%s</a>', site_url(), esc_html( get_option( 'website_name' ) ) ); ?></div>
        <?php } ?>
    </article>
</form>