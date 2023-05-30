<div class="container">
    <?php $form = new \markup\front_end\form_fields( [
        'agree'     => [ 'type' => 'checkbox', 'classes' => 's2', 'title' => getTextMsg( 'agree', t( 'Agree & take the survey' ) ), 'value' => true, 'required' => 'required' ],
        'button'    => [ 'type' => 'button', 'classes' => 's2', 'label' => getTextMsg( 'start', t( 'Start survey' ) ), 'when' => [ '=', 'data[agree]', true ] ]
    ] );

    if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['check_password'] ) ) {
        if( ( $password = getSetting( 'password' ) ) && $_POST['check_password'] == $password ) {
            $form->addFields( [
                'password' => [ 'type' => 'hidden', 'value' => $password ]
            ] );
        }
    }

    if( requireDecryption() ) {
        $form->addFields( [
            'encKey'    => [ 'type' => 'hidden', 'value' => $_GET['key'] ]
        ] );
    }

    if( !empty( $_GET['trackId'] ) ) {
        $form->addFields( [
            'trackId'   => [ 'type' => 'hidden', 'value' => $_GET['trackId'] ]
        ] );
    }

    $fields                     = $form->build();
    $attributes['data-ajax']    = ajax()->get_call_url( 'survey', [ 'action2' => 'start', 'collector' => esc_html( collector()->getSlug() ) ] );

    $markup = '
    <div class="mid">
    <form id="add_survey" class="form mid add_survey_form" ' . \util\attributes::add_attributes( filters()->do_filter( 'edit_survey_form_attrs', $attributes ) ) . $form->formAttributes() . '>';
    $markup .= '
    <h2 class="big">' . esc_html( survey()->getName() ) . '</h2>
    <div class="terms">';
    
    if( ( $terms = getMeta( 'tou', false ) ) ) {
        $markup .= ( new \site\shortcodes( $terms ) )->toMarkup();
    }

    $markup .= '</div>';
    $markup .= $fields;
    $markup .= '</form>';

    if( !limits()->removeBrand() )
    $markup .= '<div class="copy">' . sprintf( 'Powered by <a href="%s" target="_blank">%s</a>', site_url(), esc_html( get_option( 'website_name' ) ) ) . '</div>';

    $markup .= '</div>';

    echo $markup; ?>
</div>