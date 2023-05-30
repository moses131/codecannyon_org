<?php echo \theme\helpers\parts::index(); ?>

<div class="defp bg1">
    <div class="main-wrapper">
        <h2 class="title"><?php t_e( 'Frequently asked questions', 'def-theme' ); ?></h2>
        <ul class="tabs">
        <?php
        $page = pages();
        foreach( homePageFAQs()->fetch( -1 ) as $page_info ) {
            $page->setObject( $page_info );
            echo '
            <li>
                <a href="#">
                    <h2>
                        <span>' . esc_html( $page->getTitle() ) . '</span>
                        <i class="fas fa-chevron-down aic"></i>
                    </h2>
                </a>
                <div>' . $page->getContent() . '</div>
            </li>';
        } ?>
        </ul>
    </div>
</div>

<?php echo \theme\helpers\parts::index_boxes();
echo \theme\helpers\parts::index_cta(); ?>