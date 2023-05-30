<div class="defp bg3">
    <div class="main-wrapper">
        <h2 class="title"><span><?php t_e( 'Pricing', 'def-theme' ); ?></span></h2>
        <div class="df bpb mb0">
            <ul class="brcr">
                <li><a href="<?php echo site_url(); ?>"><?php t_e( 'Home', 'def-theme' ); ?></a></li>
            </ul>
        </div>
    </div>
</div>

<div style="display:flex;overflow:hidden;">
    <svg preserveAspectRatio="none" viewBox="0 0 1440 36" class="u-block" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;margin:-1px 0;"><rect width="100%" height="100%" fill="#e3e2df"></rect><path d="M1440 36V8.2s-105.6-1.2-160.7-6a877 877 0 00-150.5 2.5c-42.1 3.9-140 15-223 15C754 19.6 700.3 6.8 548.8 7c-143.7 0-273.4 11.5-350 12.6-76.6 1.2-198.8 0-198.8 0V36h1440z" fill="#fff"></path></svg>
</div>

<?php echo \theme\helpers\parts::pricing_personal(); ?>

<div class="defp bg1">
    <div class="main-wrapper">
        <h2 class="title"><?php t_e( 'Plans for you', 'def-theme' ); ?></h2>
        <div class="pricing-container">
            <div class="pricing-plans">
            <?php $pricing = new \theme\helpers\pricing_table;
            foreach( $pricing->getIndividualPlans() as $plan ) {
                $offer  = $plan['offer'] ?? NULL;
                echo '<div class="plan' . ( $offer ? ' hoffer' : '' ) . '">';
                echo '<h2 class="sline"><span>' . ( $plan['isFree'] ? t( 'Free', 'def-theme' ) : esc_html( $plan['name'] ) ) . '</span>' . ( $offer ? '<strong class="discount">' . sprintf( t( '%s%% OFF', 'def-theme' ), $offer['discount'] ) . '</strong>' : '' ) . '</h2>';
                echo '<div class="price"><div><span class="price">' . ( $offer ? $offer['priceF'] : $plan['priceF'] ) . '</span><span>' . t( '/ mo.', 'def-theme' ) . '</span></div></div>';
                echo '<ul class="info"><li>';
                $hfeat = [];
                if( $plan['isFree'] )
                    $hfeat[] = '<strong>' . t( 'No credit card required', 'def-theme' ) . '</strong></li></ul>';
                else {
                    if( $offer ) {
                        $hfeat[]    = sprintf( t( 'Price without discount: %s' ), $plan['priceF'] );
                        $hfeat[]    = sprintf( t( 'Discount available for %s+ month', 'def-theme', $offer['months'], 'Discount available for %s+ months' ), $offer['months'] );
                        $hfeat[]    = sprintf( t( 'Expires: <strong>%s</strong>', 'def-theme' ), custom_time( $offer['expires'], 2 ) );
                    } else
                        $hfeat[] = 'Price per month';
                }
                echo implode( '</li><li>', $hfeat );
                echo '</li>
                </ul>
                <ul class="features">
                    <li>' . sprintf( t( '<strong>%s</strong> surveys', 'def-theme' ), ( $plan['surveys'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['surveys'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> responses per survey', 'def-theme' ), ( $plan['responses'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['responses'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> questions', 'def-theme' ), ( $plan['questions'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['questions'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> collector', 'def-theme', $plan['collectors'], '<strong>%s</strong> collectors' ), ( $plan['collectors'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['collectors'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> space', 'def-theme' ), ( $plan['space'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['space'] ) ) . '</li>
                    <li' . ( $plan['remove_brand'] ? ' class="rb"><i class="fas fa-check"></i> ' : ' class="lt"><i class="fas fa-times"></i> ' ) . '<span>' . t( 'White label', 'def-theme' ) . '</span>' . '</li>
                </ul>';
                echo '<div class="button"><a href="' . admin_url( 'register' ) . '" class="btn">' . t( 'Get started', 'def-theme' ) . '</a></div>';
                echo '</div>';
            } ?>
            </div>
        </div>
    </div>
</div>

<?php echo \theme\helpers\parts::pricing_team(); ?>

<div class="defp bg1">
    <div class="main-wrapper">
        <h2 class="title"><?php t_e( 'Plans for teams', 'def-theme' ); ?></h2>
        <div class="pricing-container">
            <div class="pricing-plans">
            <?php if( $pricing->getTeamPlans() )
            foreach( $pricing->getTeamPlans() as $plan ) {
                $offer  = $plan['offer'] ?? NULL;
                echo '<div class="plan' . ( $offer ? ' hoffer' : '' ) . '">';
                echo '<h2 class="sline"><span>' . ( $plan['isFree'] ? t( 'Free', 'def-theme' ) : esc_html( $plan['name'] ) ) . '</span>' . ( $offer ? '<strong class="discount">' . sprintf( t( '%s%% OFF', 'def-theme' ), $offer['discount'] ) . '</strong>' : '' ) . '</h2>';
                echo '<div class="price"><div><span class="price">' . ( $offer ? $offer['priceF'] : $plan['priceF'] ) . '</span><span>' . t( '/ mo.', 'def-theme' ) . '</span></div></div>';
                echo '<ul class="info"><li>';
                $hfeat = [];
                if( $plan['isFree'] )
                    $hfeat[] = '<strong>' . t( 'No credit card required', 'def-theme' ) . '</strong></li></ul>';
                else {
                    if( $offer ) {
                        $hfeat[]    = sprintf( t( 'Price without discount: %s' ), $plan['priceF'] );
                        $hfeat[]    = sprintf( t( 'Discount available for %s+ month', 'def-theme', $offer['months'], 'Discount available for %s+ months' ), $offer['months'] );
                        $hfeat[]    = sprintf( t( 'Expires: <strong>%s</strong>', 'def-theme' ), custom_time( $offer['expires'], 2 ) );
                    } else
                        $hfeat[] = 'Price per month';
                }
                echo implode( '</li><li>', $hfeat );
                echo '</li>
                </ul>
                <ul class="features">
                    <li>' . sprintf( t( '<strong>%s</strong> team members', 'def-theme' ), ( $plan['team_members'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['team_members'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> surveys', 'def-theme' ), ( $plan['surveys'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['surveys'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> responses per survey', 'def-theme' ), ( $plan['responses'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['responses'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> questions', 'def-theme' ), ( $plan['questions'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['questions'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> collector', 'def-theme', $plan['collectors'], '<strong>%s</strong> collectors' ), ( $plan['collectors'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['collectors'] ) ) . '</li>
                    <li>' . sprintf( t( '<strong>%s</strong> space', 'def-theme' ), ( $plan['space'] < 0 ? t( 'unlimited', 'def-theme' ) : $plan['space'] ) ) . '</li>
                    <li' . ( $plan['remove_brand'] ? ' class="rb"><i class="fas fa-check"></i> ' : ' class="lt"><i class="fas fa-times"></i> ' ) . '<span>' . t( 'White label', 'def-theme' ) . '</span>' . '</li>
                </ul>';
                echo '<div class="button"><a href="' . admin_url( 'register' ) . '" class="btn">' . t( 'Get started', 'def-theme' ) . '</a></div>';
                echo '</div>';
            } ?>
            </div>
        </div>
    </div>
</div>

<?php echo \theme\helpers\parts::pricing_cta(); 
nav_active( 'pricing' ); ?>