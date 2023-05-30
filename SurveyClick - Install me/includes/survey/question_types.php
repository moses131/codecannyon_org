<?php

namespace survey;

class question_types {

    private $type;
    private $types;
    private $setting;
    private $response;
    private $inputs     = [];
    private $strValues  = [];
    private $points     = 0;
    private $vpoints    = 0;

    function __construct() {
        $this->types = filters()->do_filter( 'question-types-list', [
            'multi'     => [
                'name'          => t( 'Multiple Choice' ),
                'checkData'     => [ $this, 'cD_multi' ],
                'modifyForm'    => [ $this, 'mF_multi' ],
                'afterUpdate'   => [ $this, 'aU_multi' ],
                'markup'        => [ $this, 'markup_multi' ],
                'validation'    => [ $this, 'validation_multi' ],
                'summary'       => true
            ],

            'checkboxes'=> [
                'name'          => t( 'Checkboxes' ),
                'checkData'     => [ $this, 'cD_checkboxes' ],
                'modifyForm'    => [ $this, 'mF_checkboxes' ],
                'afterUpdate'   => [ $this, 'aU_checkboxes' ],
                'markup'        => [ $this, 'markup_checkboxes' ],
                'validation'    => [ $this, 'validation_checkboxes' ],
                'summary'       => true
            ],

            'text'              => [
                'name'          => t( 'Text Field' ),
                'checkData'     => [ $this, 'cD_textfield' ],
                'modifyForm'    => [ $this, 'mF_textfield' ],
                'afterUpdate'   => [ $this, 'aU_textfield' ],
                'markup'        => [ $this, 'markup_textfield' ],
                'validation'    => [ $this, 'validation_textfield' ],
                'summary'       => false
            ],

            'textarea'          => [
                'name'          => t( 'Textarea' ),
                'checkData'     => [ $this, 'cD_textarea' ],
                'modifyForm'    => [ $this, 'mF_textarea' ],
                'afterUpdate'   => [ $this, 'aU_textarea' ],
                'markup'        => [ $this, 'markup_textarea' ],
                'validation'    => [ $this, 'validation_textarea' ],
                'summary'       => false
            ],

            'dropdown'          => [ 
                'name'          => t( 'Dropdown' ),
                'checkData'     => [ $this, 'cD_dropdown' ],
                'modifyForm'    => [ $this, 'mF_dropdown' ],
                'afterUpdate'   => [ $this, 'aU_dropdown' ],
                'markup'        => [ $this, 'markup_dropdown' ],
                'validation'    => [ $this, 'validation_dropdown' ],
                'summary'       => true
            ],

            'date'              => [
                'name'          => t( 'Date & Time' ),
                'checkData'     => [ $this, 'cD_date' ],
                'modifyForm'    => [ $this, 'mF_date' ],
                'afterUpdate'   => [ $this, 'aU_date' ],
                'markup'        => [ $this, 'markup_date' ],
                'validation'    => [ $this, 'validation_date' ],
                'summary'       => false
            ],

            'imagec'            => [
                'name'          => t( 'Image Choice' ),
                'checkData'     => [ $this, 'cD_imagec' ],
                'modifyForm'    => [ $this, 'mF_imagec' ],
                'afterUpdate'   => [ $this, 'aU_imagec' ],
                'markup'        => [ $this, 'markup_imagec' ],
                'validation'    => [ $this, 'validation_imagec' ],
                'summary'       => true
            ],

            'contact'           => [
                'name'          => t( 'Contact Information' ),
                'checkData'     => [ $this, 'cD_contact' ],
                'modifyForm'    => [ $this, 'mF_contact' ],
                'markup'        => [ $this, 'markup_contact' ],
                'validation'    => [ $this, 'validation_contact' ],
                'summary'       => false
            ],

            'slider'            => [ 
                'name'          => t( 'Slider' ),
                'checkData'     => [ $this, 'cD_slider' ],
                'modifyForm'    => [ $this, 'mF_slider' ],
                'afterUpdate'   => [ $this, 'aU_slider' ],
                'markup'        => [ $this, 'markup_slider' ],
                'validation'    => [ $this, 'validation_slider' ],
                'summary'       => true
            ],

            'matrix_mc'         => [
                'name'          => t( 'Matrix/Multiple Choice' ),
                'checkData'     => [ $this, 'cD_matrix_mc' ],
                'modifyForm'    => [ $this, 'mF_matrix_mc' ],
                'afterUpdate'   => [ $this, 'aU_matrix_mc' ],
                'markup'        => [ $this, 'markup_matrix_mc' ],
                'validation'    => [ $this, 'validation_matrix_mc' ],
                'summary'       => true
            ],

            'matrix_rs'         => [
                'name'          => t( 'Matrix/Rating Scale' ),
                'checkData'     => [ $this, 'cD_matrix_rs' ],
                'modifyForm'    => [ $this, 'mF_matrix_rs' ],
                'afterUpdate'   => [ $this, 'aU_matrix_rs' ],
                'markup'        => [ $this, 'markup_matrix_rs' ],
                'validation'    => [ $this, 'validation_matrix_rs' ],
                'summary'       => true
            ],

            'matrix_dd'         => [
                'name'          => t( 'Matrix/Dropdowns' ),
                'checkData'     => [ $this, 'cD_matrix_dd' ],
                'modifyForm'    => [ $this, 'mF_matrix_dd' ],
                'afterUpdate'   => [ $this, 'aU_matrix_dd' ],
                'markup'        => [ $this, 'markup_matrix_dd' ],
                'validation'    => [ $this, 'validation_matrix_dd' ],
                'summary'       => true
            ],

            'srating'           => [
                'name'          => t( 'Star Rating' ),
                'checkData'     => [ $this, 'cD_srating' ],
                'modifyForm'    => [ $this, 'mF_srating' ],
                'markup'        => [ $this, 'markup_srating' ],
                'validation'    => [ $this, 'validation_srating' ],
                'summary'       => true
            ],

            'ranking'           => [
                'name'          => t( 'Ranking' ),
                'checkData'     => [ $this, 'cD_ranking' ],
                'modifyForm'    => [ $this, 'mF_ranking' ],
                'afterUpdate'   => [ $this, 'aU_ranking' ],
                'markup'        => [ $this, 'markup_ranking' ],
                'validation'    => [ $this, 'validation_ranking' ],
                'summary'       => true
            ],

            'file'              => [
                'name'          => t( 'File Upload' ),
                'checkData'     => [ $this, 'cD_file' ],
                'modifyForm'    => [ $this, 'mF_file' ],
                'markup'        => [ $this, 'markup_file' ],
                'validation'    => [ $this, 'validation_file' ],
                'summary'       => false
            ],

            'email'             => [
                'name'          => t( 'Email' ),
                'checkData'     => [ $this, 'cD_email' ],
                'modifyForm'    => [ $this, 'mF_email' ],
                'markup'        => [ $this, 'markup_email' ],
                'validation'    => [ $this, 'validation_email' ],
                'summary'       => false
            ],

            'checkbox'          => [
                'name'          => t( 'Confirmation Checkbox' ),
                'checkData'     => [ $this, 'cD_checkbox' ],
                'modifyForm'    => [ $this, 'mF_checkbox' ],
                'markup'        => [ $this, 'markup_checkbox' ],
                'validation'    => [ $this, 'validation_checkbox' ],
                'summary'       => true
            ],

            'net_prom'          => [
                'name'          => t( 'Net Promoter® Score' ),
                'checkData'     => [ $this, 'cD_net_prom' ],
                'modifyForm'    => [ $this, 'mF_net_prom' ],
                'markup'        => [ $this, 'markup_net_prom' ],
                'validation'    => [ $this, 'validation_net_prom' ],
                'summary'       => true
            ],

            'section_title'     => [
                'name'          => t( 'Section Title' ),
                'markup'        => [ $this, 'markup_section_title' ],
                'summary'       => false
            ],

            'ex_textfield'      => [
                'name'          => t( 'Advanced. External: Text Field' ),
                'checkData'     => [ $this, 'cD_ex_textfield' ],
                'modifyForm'    => [ $this, 'mF_ex_textfield' ],
                'afterUpdate'   => [ $this, 'aU_ex_textfield' ],
                'markup'        => [ $this, 'markup_ex_textfield' ],
                'validation'    => [ $this, 'validation_ex_textfield' ],
                'summary'       => false
            ],
        ] );
    }

    public function setType( string $type ) {
        if( isset( $this->types[$type] ) ) {
            $this->type = $this->types[$type];
            return true;
        }
        return false;
    }

    public function setResponse( response $res ) {
        $this->response = $res;
        return $this;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getInputs() {
        return $this->inputs;
    }

    public function getStrValues() {
        return $this->strValues;
    }

    public function getPoints() {
        return $this->points;
    }

    public function getValidPoints() {
        return $this->vpoints;
    }

    public function getType() {
        if( !$this->type )
        return false;

        return $this->type;
    }

    public function getName() {
        if( !$this->type )
        return '';

        return $this->type['name'];
    }

    public function getTypesSummary() {
        return array_keys( array_filter( $this->types, function( $v ) {
            return isset( $v['summary'] ) && $v['summary'];
        } ) );
    }

    public function getTheList() {
        return array_map( function( $v ) {
            return $v['name'];
        }, filters()->do_filter( 'question-types-list-name', $this->types ) );
    }

    public function getMarkup( string $id = NULL ) {
        if( isset( $this->types[$id]['markup'] ) && is_callable( $this->types[$id]['markup'] ) ) {
            return call_user_func( $this->types[$id]['markup'] );
        }

        return '';
    }

    public function validate( object $question, array $value ) {
        if( isset( $this->type['validation'] ) && is_callable( $this->type['validation'] ) ) {
            return call_user_func( $this->type['validation'], $question, $value );
        }

        return true;
    }

    public function markup_multi() {
        $settings   = setting( 'multi_setting' );
        $value  = value( NULL );
        $markup = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <ul class="options">';

            $options = question()->getOptions();

            if( isset( $settings['shuffle'] ) )
                $options->orderBy( 'rand' );
            else
                $options->orderBy( 'position' );

            foreach( $options->fetch( -1 ) as $option ) {
                $markup .= '
                <li class="form_line">
                    <input type="radio" name="data[' . question()->getId() . '][value]" value="' . $option->id . '" id="data[' . question()->getId() . '][' . $option->id . ']"' . ( isset( $value['int_group'] ) && array_search( $option->id, $value['int_group'] ) !== false ? ' checked' : '' ) . '>
                    <label for="data[' . question()->getId() . '][' . $option->id . ']">' . esc_html( $option->title ) . '</label>
                </li>';
            }
            $markup .= '
            </ul>
        </article>';

        return $markup;
    }

    public function markup_checkboxes() {
        $settings   = setting( 'checkboxes_setting' );
        $value      = value();
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <ul class="options">';
            $options = question()->getOptions();
            if( isset( $settings['shuffle'] ) ) {
                $options->orderBy( 'rand' );
            }
            foreach( $options->fetch( -1 ) as $option ) {
                $markup .= '
                <li class="form_line">
                    <input type="checkbox" name="data[' . question()->getId() . '][' . $option->id . ']" id="data[' . question()->getId() . '][' . $option->id . ']"' . ( isset( $value['int_group'] ) && array_search( $option->id, $value['int_group'] ) !== false ? ' checked' : '' ) . '>
                    <label for="data[' . question()->getId() . '][' . $option->id . ']">' . esc_html( $option->title ) . '</label>
                </li>';
            }
            $markup .= '
            </ul>
        </article>';

        return $markup;
    }

    public function markup_textfield() {
        $value  = value();
        return  '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
                <input type="text" name="data[' . question()->getId() . '][text]" value="' . ( !empty( $value['text'] ) ? esc_html( $value['text'] ) : '' ) . '" />
            </div>
        </article>';
    }

    public function markup_textarea() {
        $value  = value();
        return  '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
                <textarea name="data[' . question()->getId() . '][text]">' . ( !empty( $value['text'] ) ? esc_html( $value['text'] ) : '' ) . '</textarea>
            </div>
        </article>';
    }

    public function markup_dropdown() {
        $settings   = setting( 'dropdown_setting' );
        $value      = value();
        $value      = ( isset( $value['int_group'] ) ? current( $value['int_group'] ) : NULL );
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
            <select name="data[' . question()->getId() . '][value]">';
            $markup .= '<option value="">' . t( 'Select' ) . '</option>';
            $options = question()->getOptions();
            if( isset( $settings['shuffle'] ) ) {
                $options->orderBy( 'rand' );
            }
            foreach( $options->fetch( -1 ) as $option ) {
                $markup .= '<option value="' . $option->id . '"' . ( $value && $value == $option->id ? ' selected' : '' ) . '>' . esc_html( $option->title ) . '</option>';
            }
            $markup .= '
            </select>
            </div>
        </article>';

        return $markup;
    }

    public function markup_date() {
        $setting    = setting( 'date_setting' );
        $value      = value();
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="col">
                <div class="form_line">
                    <input type="date" name="data[' . question()->getId() . '][date]"' . ( isset( $value['date'] ) ? ' value="' . date( 'Y-m-d', $value['date'] ) . '"' : '' ) . '>
                </div>';
                if( $setting && !empty( $setting['save_h'] ) ) {
                    $markup .= '
                    <div class="form_line">
                        <input type="time" name="data[' . question()->getId() . '][time]"' . ( isset( $value['date'] ) ? ' value="' . date( 'h:i', $value['date'] ) . '"' : '' ) . '>
                    </div>';
                }
            $markup .= '
            </div>
        </article>';

        return $markup;
    }

    function markup_imagec() {
        $settings   = setting( 'images_setting' );
        $value      = value();
        $values     = $value['int_group'] ?? NULL;
        $isRadio    = isset( $settings['to'] ) && (int) $settings['to'] == 1;
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="img-list">';
            $options = question()->getOptions();
            if( isset( $settings['shuffle'] ) ) {
                $options->orderBy( 'rand' );
            }
            foreach( $options->fetch( -1 ) as $option ) {
                $options->setObject( $option );
                $markup .= '
                <div class="form_line">';
                    if( $isRadio ) {
                        $markup .= '<input name="data[' . question()->getId() . '][value]" id="data[' . question()->getId() . '][' . $options->getId() . ']" value="' . $options->getId() . '" type="radio"' . ( $values && array_search( $options->getId(), $values ) !== false ? ' checked' : '' ) . '>';
                    } else {
                        $markup .= '<input name="data[' . question()->getId() . '][' . $options->getId() . ']" id="data[' . question()->getId() . '][' . $options->getId() . ']" type="checkbox"' . ( $values && array_search( $options->getId(), $values ) !== false ? ' checked' : '' ) . '>';
                    }
                    $markup .= '
                    <label for="data[' . question()->getId() . '][' . $options->getId() . ']">';
                    foreach( ( $media = $options->getMedia() )->fetch( 1 ) as $mediaFile ) {
                        $media->setObject( $mediaFile );
                        $markup .= '<div style="background-image:url(' . $media->getURL() . ');"></div>';
                    }
                    $markup .= '
                    </label>
                    <div class="info elp">' . esc_html( $options->getTitle() ) . '</div>
                </div>';
            }
            $markup .= '
            </div>
        </article>';

        return $markup;
    }

    public function markup_contact() {
        $setting    = setting( 'contact_setting' );
        $opts       = $setting['info'] ?? [];
        $opts       = array_intersect_key( [ 'name' => t( 'First and last name' ), 'company' => t( 'Company' ), 'address' => t( 'Address' ), 'address2' => t( 'Address 2' ), 'city' => t( 'City/Town' ), 'state' => t( 'State/Province' ), 'zip' => t( 'ZIP/Postal Code' ), 'country' => t( 'Country' ), 'email' => t( 'Email' ), 'phone' => t( 'Phone' ) ], $opts );
        $markup     = '
        <article class="cols" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' );
            foreach( $opts as $key => $title ) {
                $markup .= '
                <div class="col">
                    <div class="form_line">
                        <label>' . $title . '</label>
                        <input name="data[' . question()->getId() . '][' . $key . ']" type="text" placeholder="' . t( 'Please fill in here' ) . '">
                    </div>
                </div>';
            }
            $markup .= '
        </article>';

        return $markup;
    }

    public function markup_slider() {
        $setting    = setting( 'slider_setting' );
        $value      = value();
        $value      = ( isset( $value['int_group'] ) ? current( $value['int_group'] ) : NULL );
        $iattr      = 'min="' . ( isset( $setting['from'] ) ? max( (int) $setting['from'], 0 ) : 0 ) . '" max="' . ( isset( $setting['to'] ) ? min( (int) $setting['to'], 100 ) : 0 ) . '"' . ( $value ? ' value="' . (int) $value . '"' : '' );
        $markup     = '
        <article class="range" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="col">
                <div class="form_line">
                    <input type="range" class="slider" ' . $iattr . '>
                </div>
                <div class="form_line wa">
                    <input type="number" name="data[' . question()->getId() . '][value]" ' . $iattr . '>
                </div>
            </div>
        </article>';

        return $markup;
    }

    public function markup_matrix_mc() {
        $setting    = setting( 'matrix_mc_setting' );
        $value      = value();
        $values     = $value['int_cascade'] ?? NULL;
        $labels     = question()->getLabels();
        $columns    = question()->getLabels( 2 );
        $col_opts   = [];

        if( !empty( $setting['shuffle_c'] ) ) {
            $columns->orderBy( 'rand' );
        }
        
        $columns_markup = '';
        $columnsf       = $columns->fetch( -1 );

        foreach( $columnsf as $column ) {
            $columns_markup .= '
            <div class="form_line tc">
                <label>' . esc_html( $column->title ) . '</label>
            </div>';
            $col_opts[$column->id] = $column->id;
        }

        $markup = '
        <article class="matrix' . ( count( $columnsf ) >= 5 ? ' tmi' : '' ) . '" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="col">
                <div class="w150"></div>';                
                if( !empty( $setting['shuffle_l'] ) ) {
                    $labels->orderBy( 'rand' );
                }
                $markup .= $columns_markup;
                $markup .= '
            </div>';
            foreach( $labels->fetch( -1 ) as $label ) {
                $markup .= '
                <div class="col">
                    <div class="w150">' . esc_html( $label->title ) . '</div>';
                    $vals = $values[$label->id] ?? NULL;
                    foreach( $col_opts as $column_id ) {
                        $markup .= '
                        <div class="form_line">
                            <input name="data[' . question()->getId() . '][' . $label->id . ']" type="radio" value="' . $column_id . '" id="data[' . question()->getId() . '][' . $label->id . '][' . $column_id . ']"' . ( $vals && $vals == $column_id ? ' checked' : '' ) . '>
                            <label for="data[' . question()->getId() . '][' . $label->id . '][' . $column_id . ']"></label>
                        </div>';
                    }
                $markup .= '
                </div>';
            }
            $markup .= '
        </article>';

        return $markup;
    }

    public function markup_matrix_rs() {
        $setting    = setting( 'matrix_rs_setting' );
        $value      = value();
        $values     = $value['int_cascade'] ?? NULL;
        $markup     = '
        <article class="matrix" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' );
            
            $labels = question()->getLabels();
            
            if( !empty( $setting['shuffle_l'] ) ) {
                $labels->orderBy( 'rand' );
            }

            foreach( $labels->fetch( -1 ) as $option ) {
                $markup .= '
                <div class="col">
                    <div>' . esc_html( $option->title ) . '</div>
                    <div class="fl_inl stars sm">';
                        for( $i = 5; $i >= 1; $i-- ) {
                            $markup .= '
                            <input type="radio" name="data[' . question()->getId() . '][' . $option->id . ']" id="data[' . question()->getId() . '][' . $option->id . '][' . $i . ']" value="' . $i . '"' . ( isset( $values[$option->id] ) && $values[$option->id] == $i ? ' checked' : '' ) . '>
                            <label for="data[' . question()->getId() . '][' . $option->id . '][' . $i . ']">
                                <span><i>' . $i . '</i></span>
                                <i class="fas fa-star"></i>
                            </label>';
                        }
                    $markup .= '
                    </div>
                </div>';
            }
            $markup .= '
        </article>';

        return $markup;
    }

    public function markup_matrix_dd() {
        $setting    = setting( 'matrix_dd_setting' );
        $value      = value();
        $values     = $value['int_cascade'] ?? NULL;
        $labels     = question()->getLabels();
        $columns    = question()->getLabels( 2 );
        $col_opts   = [];
        
        if( !empty( $setting['shuffle_l'] ) ) {
            $labels->orderBy( 'rand' );
        }

        if( !empty( $setting['shuffle_c'] ) ) {
            $columns->orderBy( 'rand' );
        }

        $columns_markup = '';
        $columnsf       = $columns->fetch( -1 );

        foreach( $columnsf as $column ) {
            $columns_markup .= '
            <div class="form_line tc">
                <label>' . esc_html( $column->title ) . '</label>
            </div>';

            $columns    ->setObject( $column );
            $options    = $columns->getOptions();
            if( !empty( $setting['shuffle_o'] ) ) {
                $options->orderBy( 'rand' );
            }
            $col_opts[$column->id] = $options->fetch( -1 );
        }

        $markup = '
        <article class="matrix' . ( count( $columnsf ) >= 5 ? ' tmi' : '' ) . '" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="col">
                <div class="w150"></div>';
                $markup .= $columns_markup;
            $markup .= '
            </div>';

            foreach( $labels->fetch( -1 ) as $label ) {
                $markup .= '
                <div class="col">
                    <div class="w150">' . esc_html( $label->title ) . '</div>';
                    foreach( $col_opts as $column_id => $options ) {
                        $markup .= '
                        <div class="form_line">
                            <select name="data[' . question()->getId() . '][' . $label->id . '][' . $column_id . ']">
                                <option value="">' . t( 'Select' ) . '</option>';
                                $vals = $values[$label->id][$column_id] ?? NULL;
                                array_map( function( $v ) use ( &$markup, $vals ) {
                                    $markup .= '<option value="' . $v->id . '"' . ( $vals && $vals == $v->id ? ' selected' : '' ) . '>' . esc_html( $v->title ) . '</option>';
                                }, $options );
                            $markup .= '
                            </select>
                        </div>';
                    }
                $markup .= '
                </div>';
            }
            $markup .= '
        </article>';

        return $markup;
    }

    public function markup_srating() {
        $setting    = setting( 'srating_setting' );
        $stars      = $setting['stars'] ?? 10;
        $stars      = (int) $stars > 10 || (int) $stars < 1 ? 10 : (int) $stars;
        $value      = value();
        $value      = ( isset( $value['int_group'] ) ? current( $value['int_group'] ) : NULL );
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="fl_inl stars">';
            for( $i = $stars; $i >= 1; $i-- ) {
                $markup .= '
                <input type="radio" name="data[' . question()->getId() . '][value]" value="' . $i . '" id="data[' . question()->getId() . '][' . $i . ']"' . ( $value && $value == $i ? ' checked' : '' ) . '>
                <label for="data[' . question()->getId() . '][' . $i . ']">';
                $markup .= '
                    <span><i>' . $i . '</i></span>
                    <i class="fas fa-star"></i>
                </label>';
            }
            $markup .= '
            </div>
        </article>';

        return $markup;
    }

    public function markup_ranking() {
        $settings   = setting( 'dropdown_setting' );
        $value      = value();
        $value      = ( isset( $value['int_cascade'] ) ? $value['int_cascade'] : NULL );
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
            <ul class="fl-rank">';
            $options    = question()->getOptions();

            if( isset( $settings['shuffle'] ) )
            $options->orderBy( 'rand' );

            $o_list     = $options->fetch( -1 );
            $limit      = count( $o_list );

            foreach( $o_list as $option ) {
                $markup .= '
                <li>
                <span>' . esc_html( $option->title ) . '</span>
                <select name="data[' . question()->getId() . '][' . $option->id . ']">
                <option></option>';
                for( $i = 1; $i <= $limit; $i++ )
                $markup .= '<option value="' . $i . '"' . ( $value && isset( $value[$option->id] ) && $value[$option->id] == $i ? ' selected' : '' ) . '>' . $i . '</option>';
                $markup .= '
                </select>';
                $markup .= '
                </li>';
            }
            $markup .= '
            </ul>
            </div>
        </article>';

        return $markup;
    }

    public function markup_file() {
        $setting    = setting( 'file_setting' );
        $ext        = array_intersect_key( [ 'pdf' => 'PDF', 'doc' => 'DOC/DOCX', 'png' => 'PNG', 'jpg' => 'JPG/JPEG', 'gif' => 'GIF' ], ( $setting['extension'] ?? [] ) );
        $ulink      = $setting['ulink'] ?? false;
        $value      = value();

        $ext_list   = array_map( function( $v ) {
            return '.' . $v;
        }, array_keys( $ext ) );

        if( isset( $ext['doc'] ) )
        $ext_list[] = '.docx';

        $markup = '
        <article class="file" id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            <div class="info">
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div>' . questionInfo() . '</div>' : '' ) . '
            <div>' . sprintf( t( 'Files accepted: %s' ), implode( ', ', $ext ) ) . '</div>
            </div>';

            $attachment = question()->getAttachments( response()->getId() );
            foreach( $attachment->fetch( -1 ) as $file ) {
                $attachment ->setObject( $file );
                $fileInfo   = $attachment->getDetailsJson();
                $markup     .= '
                <div class="ufile">
                    <i class="far fa-file-pdf"></i>
                    <span>' . esc_html( $fileInfo['name'] ) . '</span>
                </div>';
            }

            $markup .= '<div class="list">';
            if( $ulink ) {
                $checked = isset( $value['text'] );
                $markup .= '
                <div class="form_line checkbox sm' . ( $checked ? ' checked' : '' ) . '">
                    <input type="checkbox" name="data[' . question()->getId() . '][usee]" id="online"' . ( $checked ? ' checked': '' ) . '>
                    <label for="online">' . t( 'Use an external link') . '</label>
                </div>
                <div class="form_line">
                    <input type="text" name="data[' . question()->getId() . '][link]" placeholder="https:// ..." value="' . ( $checked ? esc_html( $value['text'] ) : '' ) . '">
                </div>';
            }
            $markup .= '
                <div class="form_line">
                    <input type="file" name="data[' . question()->getId() . '][file]" accept="' . implode( ',', $ext_list ) . '">
                </div>
            </div>
        </article>';

        return $markup;
    }

    public function markup_email() {
        $value  = value();
        return  '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
                <input type="email" name="data[' . question()->getId() . '][value]" value="' . ( !empty( $value['text'] ) ? esc_html( $value['text'] ) : '' ) . '" />
            </div>
        </article>';
    }

    public function markup_checkbox() {
        $setting    = setting( 'checkbox_setting' );
        $value      = value();
        $value      = ( isset( $value['int_group'] ) ? current( $value['int_group'] ) : NULL );
        $markup     = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' );
            if( !empty( $setting['terms'] ) ) {
                $markup .= '<div class="tterms">' . esc_html( $setting['terms'] ) . '</div>';
            }
            $markup .= '
            <ul class="options">
                <li class="form_line">
                    <input type="checkbox" name="data[' . question()->getId() . '][value]" id="data[' . question()->getId() . ']"' . ( $value && $value == 1 ? ' checked' : '' ) . '><label for="data[' . question()->getId() . ']">' . ( !empty( $setting['label'] ) ? esc_html( $setting['label'] ) : t( 'I agree' ) ) . '</label>
                </li>
            </ul>
        </article>';

        return $markup;
    }

    public function markup_net_prom() {
        $value  = value();
        $value  = ( isset( $value['int_group'] ) ? current( $value['int_group'] ) : NULL );

        $markup = '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="fl_inl">';
            for( $i = 1; $i <= 10; $i++ ) {
                $markup .= '
                <div class="form_line">
                    <span>';
                    if( $i < 7 ) {
                        $markup .= '<i class="far fa-sad-tear"></i>';
                    } else if( $i < 9 ) {
                        $markup .= '<i class="far fa-meh"></i>';
                    } else {
                        $markup .= '<i class="far fa-grin-hearts"></i>';
                    }
                    $markup .= '
                    </span>
                    <input type="radio" name="data[' . question()->getId() . '][value]" value="' . $i . '" id="data[' . question()->getId() . '][' . $i . ']"' . ( $value && $value == $i ? ' checked' : '' ) . '>
                    <label for="data[' . question()->getId() . '][' . $i . ']">' . $i . '</label>
                </div>';
            }
            $markup .= '
            </div>
        </article>';

        return $markup;
    }

    public function markup_section_title() {
        $markup = '
        <article>
            <h2 class="title">' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) != '' ? '<div>' . $info . '</div>' : '' ) . '
        </article>';
        
        return $markup;
    }

    public function markup_ex_textfield() {
        $value  = value();

        return  '
        <article id="' . question()->getId() . '">
            <h2>' . questionTitle() . '</h2>
            ' . ( ( $info = question()->getInfo() ) !== '' ? '<div class="info">' . questionInfo() . '</div>' : '' ) . '
            <div class="form_line">
                <input type="text" name="data[' . question()->getId() . '][text]" value="' . ( !empty( $value['text'] ) ? esc_html( $value['text'] ) : '' ) . '" />
            </div>
        </article>';
    }

    public function validation_multi( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $value['value'] ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value['value'] ) ) {
            $options    = $question->getOptions()->fetch( -1 );
            $option     = $options[$value['value']] ?? NULL;

            if( $question->isRequired() && !$option )
                throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
            else if( !$option )
                return true;

            $setting    = $question->getSetting();
            $points     = $option->points;
            $min        = $setting['multi_setting']['min'] ?? 0;

            if( (int) $min > $points )
            throw new \Exception( ( $setting['multi_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_group' => [ $option->id ], 'points' => $option->points ];
            $this->strValues[$question->getId()] = esc_html( $option->title );
            $this->points += $option->points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_checkboxes( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $value ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value ) ) {
            $options    = $question->getOptions()->fetch( -1 );
            $values     = array_intersect_key( $options, $value );
            $count      = count( $values );

            if( $question->isRequired() && $count == 0 )
                throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
            else if( $count == 0 )
                return true;

            $setting    = $question->getSetting();
            $min        = $setting['checkboxes_setting']['from'] ?? 0;
            $max        = $setting['checkboxes_setting']['to'] ?? 100;

            if( $count < (int) $min || $count > (int) $max )
            throw new \Exception( t( 'This input is not valid' ) );  

            $points     = 0;
            $type       = $setting['checkboxes_setting']['type'] ?? 'max';
            $min        = $setting['checkboxes_setting']['min'] ?? 0;

            array_map( function( $v ) use ( &$points, $type ) {
                if( $type == 'max' ) {
                    if( $v->points > $points )
                    $points = $v->points;
                } else {
                    $points += $v->points;
                }
            }, $values );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['checkboxes_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_group' => array_keys( $values ), 'points' => $points ];
            $this->strValues[$question->getId()] = implode( ', ', array_map( function( $v ) {
                return esc_html( $v->title );
            }, $values ) );
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_textfield( object $question, array $value ) {
        $text       = $value['text'] ?? '';
        $text       = trim( $text );
        $survey     = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $text ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $text ) ) {
            $setting    = $question->getSetting();
            $type       = $setting['text_setting']['type'] ?? 'string';

            if( $type == 'number' && !is_numeric( $text ) )
            throw new \Exception( t( 'Please insert a number' ) );  

            $min        = $setting['text_setting']['from'] ?? 0;
            $max        = $setting['text_setting']['to'] ?? 255;

            if( $type == 'number' && ( (int) $text < (int) $min || (int) $text > (int) $max ) )
                throw new \Exception( t( 'This is not a valid number' ) );  
            else if( ( $count = strlen( $text ) ) && $count < (int) $min || $count > (int) $max )
                throw new \Exception( t( 'This input is not valid' ) );  

            $points     = 0;
            $type       = $setting['text_setting']['type'] ?? 'max';
            $min        = $setting['text_setting']['min'] ?? 0;

            array_map( function( $v ) use ( &$points, $type, $text ) {
                $value = json_decode( $v->value, true );
                if( !isset( $value['word'] ) || !isset( $value['find'] ) ) {
                    //
                } else if( $value['find'] == 'exact' ) {
                    if( strcasecmp( $value['word'], $text ) !== 0 )
                    return ;
                } else {
                    if( !preg_match( '/' . $value['word'] . '/i', $text ) )
                    return ;
                }

                if( $type == 'max' ) {
                    if( $v->points > $points )
                    $points = $v->points;
                } else {
                    $points += $v->points;
                }
            }, $question->getAnswerConditions()->fetch( -1 ) );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['text_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'text' => esc_html( $text ), 'points' => $points ];
            $this->strValues[$question->getId()] = esc_html( $text );
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_textarea( object $question, array $value ) {
        $text       = $value['text'] ?? '';
        $text       = trim( $text );
        $survey     = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $text ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $text ) ) {
            $setting    = $question->getSetting();
            $from       = $setting['textarea_setting']['from'] ?? 0;
            $to         = $setting['textarea_setting']['to'] ?? 5000;
            $count      = strlen( $text );
    
            if( $count < (int) $from || $count > (int) $to )
            throw new \Exception( t( 'This input is not valid' ) );  

            $points     = 0;
            $type       = $setting['textarea_setting']['type'] ?? 'max';
            $min        = $setting['textarea_setting']['min'] ?? 0;

            array_map( function( $v ) use ( &$points, $type, $text ) {
                $value = json_decode( $v->value, true );
                if( !isset( $value['word'] ) || !isset( $value['find'] ) ) {
                    //
                } else if( $value['find'] == 'exact' ) {
                    if( strcasecmp( $value['word'], $text ) !== 0 )
                    return ;
                } else {
                    if( !preg_match( '/' . $value['word'] . '/i', $text ) )
                    return ;
                }

                if( $type == 'max' ) {
                    if( $v->points > $points )
                    $points = $v->points;
                } else
                    $points += $v->points;
            }, $question->getAnswerConditions()->fetch( -1 ) );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['textarea_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'text' => esc_html( $text ), 'points' => $points ];
            $this->strValues[$question->getId()] = esc_html( $text );
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_dropdown( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $value['value'] ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value['value'] ) ) {
            $options    = $question->getOptions()->fetch( -1 );
            $option     = $options[$value['value']] ?? NULL;

            if( $question->isRequired() && !$option )
                throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
            else if( !$option )
                return true;

            $setting    = $question->getSetting();
            $points     = $option->points;
            $min        = $setting['dropdown_setting']['min'] ?? 0;

            if( (int) $min > $points )
            throw new \Exception( ( $setting['dropdown_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_group' => [ $option->id ], 'points' => $points ];
            $this->strValues[$question->getId()] = esc_html( $option->title );
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_date( object $question, array $value ) {
        $survey     = $this->getResponse()->getSurvey();
        $setting    = $question->getSetting();
        $save_h     = $setting['date_setting']['save_h'] ?? 0;

        if( $question->isRequired() && ( empty( $value['date'] ) || ( $save_h && empty( $value['time'] ) ) ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value['date'] ) ) {
            $datetime   = strtotime( $value['date'] . ( !empty( $value['time'] ) ? ' ' . $value['time'] : '' ) );
            $date_from  = $setting['date_setting']['date_from'] ?? false;

            if( $date_from ) {
                switch( $date_from ) {
                    case 'today': $from = strtotime( 'today' ); break;
                    case 'tomorrow': $from = strtotime( 'tomorrow' ); break;
                    case 'upto':
                        if( isset( $setting['date_setting']['date_from2'] ) )
                        $from = strtotime( $setting['date_setting']['date_from2'] );
                    break;
                }
            }

            $date_to    = $setting['date_setting']['date_to'] ?? false;

            if( $date_to ) {
                switch( $date_to ) {
                    case 'tomorrow': $to = strtotime( 'tomorrow' ); break;
                    case 'tweek': $to = strtotime( 'monday next week' ); break;
                    case 'tmonth': $to = strtotime( 'first day of next month' ); break;
                    case 'tyear': $to = strtotime( '1 january next year' ); break;
                    case 'upto':
                        if( isset( $setting['date_setting']['date_to2'] ) )
                        $to = strtotime( $setting['date_setting']['date_to2'] );
                    break;
                }
            }

            if( ( isset( $from ) && $datetime < $from ) || ( isset( $to ) && $datetime > $to ) )
            throw new \Exception( t( 'The selected date is not in range' ) );

            $points     = 0;
            $type       = $setting['date_setting']['type'] ?? 'max';
            $min        = $setting['date_setting']['min'] ?? 0;
            $str_req    = $setting['date_setting']['format'] ?? 'd/m/y';
            if( $save_h ) {
                if( isset( $setting['date_setting']['hour_f'] ) && $setting['date_setting']['hour_f'] == 12 )
                    $str_req .= ', h:i a';
                else $str_req .= ', H:i';
            }

            array_map( function( $v ) use ( &$points, $type, $datetime ) {
                $range = json_decode( $v->value, true );
                if( !isset( $range['from'] ) || !isset( $range['to'] ) ) return ;
                if( $datetime >= (int) $range['from'] && (int) $range['to'] >= $datetime ) {
                    if( $type == 'max' ) {
                        if( $v->points > $points )
                        $points = $v->points;
                    } else {
                        $points += $v->points;
                    }
                }
            }, $question->getAnswerConditions()->fetch( -1 ) );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['date_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'date' => $datetime, 'points' => $points ];
            $this->strValues[$question->getId()] = date( $str_req, $datetime );
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    
    public function validation_imagec( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();
        $value  = isset( $value['value'] ) ? array_flip( [ $value['value'] ] ) : $value;

        if( $question->isRequired() && empty( $value ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value ) ) {
            $options    = $question->getOptions()->fetch( -1 );
            $values     = array_intersect_key( $options, $value );
            $count      = count( $values );

            if( $question->isRequired() && $count == 0 ) {
                throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
            } else if( $count == 0 ) {
                return true;
            }

            $setting    = $question->getSetting();
            $from       = $setting['images_setting']['from'] ?? 0;
            $to         = $setting['images_setting']['to'] ?? 100;

            if( $count < (int) $from || $count > (int) $to )
            throw new \Exception( t( 'This input is not valid' ) );  

            $points     = 0;
            $type       = $setting['images_setting']['points'] ?? 'max';
            $min        = $setting['images_setting']['min'] ?? 0;

            array_map( function( $v ) use ( &$points, $type ) {
                if( $type == 'max' ) {
                    if( $v->points > $points )
                    $points = $v->points;
                } else {
                    $points += $v->points;
                }
            }, $values );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['images_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_group' => array_keys( $values ), 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_slider( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && !is_numeric( $value['value'] ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value['value'] ) ) {
            $value      = (int) $value['value'];
            $setting    = $question->getSetting();
            $from       = $setting['slider_setting']['from'] ?? 0;
            $to         = $setting['slider_setting']['to'] ?? 100;

            if( (int) $value < (int) $from || (int) $value > (int) $to )
            throw new \Exception( t( 'This input is not valid' ) );  

            $points     = 0;
            $type       = $setting['slider_setting']['points'] ?? 'max';
            $min        = $setting['slider_setting']['min'] ?? 0;

            array_map( function( $v ) use ( &$points, $value ) {
                $range = json_decode( $v->value, true );
                if( !isset( $range['from'] ) || !isset( $range['to'] ) ) return ;
                if( (int) $range['from'] <= $value && (int) $range['to'] >= $value ) {
                    if( $points < $v->points ) {
                        $points = $v->points;
                    }
                }
            }, $question->getAnswerConditions()->fetch( -1 ) );

            if( (int) $min > $points )
            throw new \Exception( ( $setting['slider_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_group' => [ $value ], 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_srating( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $value['value'] ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value['value'] ) ) {
            $setting    = $question->getSetting();
            $points     = (int) ( $setting['srating_setting']['points'] ?? 0 );
            $stars      = (int) ( $setting['srating_setting']['stars'] ?? 10 );

            if( $stars < 1 || $stars > 10 || (int) $value['value'] > $stars || (int) $value['value'] < 1 )
            throw new \Exception( t( 'This input is not valid' ) );

            $this->inputs[$question->getId()] = [ 'int_group' => [ (int) $value['value'] ], 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_ranking( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();

        if( $question->isRequired() && empty( $value ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value ) ) {
            asort( $value );

            if( current( $value ) != 1 )
            throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

            $options    = $question->getOptions()->fetch( -1 );
            $intersect  = array_intersect_key( $options, $value );

            if( count( $intersect ) !== count( $options ) )
            throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

            $i      = 1;
            $points = 0;

            foreach( $value as $opt_id => $pos ) {
                if( $i != $pos )
                throw new \Exception( t( 'Invalid order' ) );
                $setting = ( !empty( $options[$opt_id]->setting ) ? json_decode( $options[$opt_id]->setting, true ) : [] );
                if( isset( $setting['sign'] ) && strlen( $setting['sign'] ) == 2 ) {
                    if( ( $setting['sign'][0] == '=' && $setting['sign'][1] == $pos ) ||
                    ( $setting['sign'][0] == '<' && $setting['sign'][1] >= $pos ) )
                    $points += $options[$opt_id]->points;
                }
                $i++;
            }

            $setting    = $question->getSetting();
            $min        = $setting['ranking_setting']['min'] ?? 0;

            if( (int) $min > $points )
            throw new \Exception( ( $setting['ranking_setting']['error'] ?? t( 'The answer to this question is invalid' ) ) );  

            $this->inputs[$question->getId()] = [ 'int_cascade' => $value, 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_file( object $question, array $value ) {
        $response   = $this->getResponse();
        $survey     = $response->getSurvey();

        $attachments= $question->getAttachments( $response->getId() );
        $files      = [];
        foreach( $attachments->fetch( -1 ) as $id => $file ) {
            $info   = ( !empty( $file->info ) ? json_decode( $file->info, true ) : [] );
            $name   = $ext  = $media = '';
            if( isset( $info['name'] ) ) {
                $name   = $info['name'];
                $ext    = pathinfo( $info['name'], PATHINFO_EXTENSION );
            }
            if( !empty( $info['media'] ) )
            $media      = $info['media'];
            $files[$id] = [ 'attachment' => $id, 'media' => $media, 'name' => $name, 'extension' => $ext ];
        }

        if( !empty( $value['deleteFiles'] ) && is_array( $value['deleteFiles'] ) ) {
            foreach( $value['deleteFiles'] as $attachment ) {
                unset( $files[$attachment] );
                $response->deleteAttachment( (int) $attachment );
            }
        }

        $setting    = $question->getSetting();
        $is_link    = !empty( $setting['file_setting']['ulink'] ) && !empty( $value['usee'] ) ?? false;

        $form       = new \markup\front_end\form_fields;
        $uploads    = $form->getFileRequests()['data[' . $question->getId() . ']'] ?? [];

        if( $question->isRequired() && ( ( $is_link && ( empty( $value['link'] ) || !filter_var( $value['link'], FILTER_VALIDATE_URL ) ) ) || ( !$is_link && ( empty( $uploads['file']['name'] ) && empty( $files ) ) ) ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        $points     = (int) ( $setting['file_setting']['points'] ?? 0 );

        if( $is_link ) {

            if( !empty( $value['link'] ) )
            $this->inputs[$question->getId()] = [ 'text' => $value['link'], 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;

            // remove all previous attachments
            if( !empty( $files ) )
            $response->deleteAttachments( $question->getId() );

            return true;

        }

        if( empty( $uploads['file']['error'] ) ) {

            $ext = array_intersect_key( [ 
                'pdf' => 'PDF', 
                'doc' => 'DOC/DOCX', 
                'png' => 'PNG', 
                'jpg' => 'JPG/JPEG', 
                'gif' => 'GIF' 
            ], $setting['file_setting']['extension'] ?? [] );
                
            if( isset( $ext['doc'] ) )
            $ext['docx'] = '';

            $extension = pathinfo( $uploads['file']['name'], PATHINFO_EXTENSION );

            if( !isset( $ext[strtolower( $extension )] ) )
            throw new \Exception( $survey->getText( 'fextension', t( 'File extension is not allowed' ) ) );

            if( $uploads['file']['size'] > ( ( $max_size = (int) filters()->do_filter( 'max_default_upload_size', MAX_SIZE_FILE_TYPE ) ) * 1024 * 1024 ) )
            throw new \Exception( sprintf( $survey->getText( 'ftoobig', t( 'This file is too big. Maximum allowed size: %s Mb' ) ), $max_size ) );

            if( !empty( $uploads['file']['error'] ) )
            throw new \Exception( sprintf( t( 'Error code: %s' ), $uploads['file']['error'] ) );

            // remove all previous attachments
            if( !empty( $files ) )
            $response->deleteAttachments( $question->getId() );

            $files = [];

            foreach( $uploads as $file ) {
                $media  = media( [ $file ] )
                        ->setType( 3 )
                        ->setTypeId( $question->getSurveyId() )
                        ->setOwnerId( $survey->getUserId() )
                        ->getUploadId();

                if( !empty( $media ) ) {
                    $attachment_id          = $response->attachFile( $question->getId(), key( $media ), cms_json_encode( [ 'name' => $file['name'] ] ) );
                    if( $attachment_id )
                    $files[$attachment_id]  = [ 'attachment' => $attachment_id, 'media' => key( $media ), 'name' => $file['name'], 'extension' => pathinfo( $file['name'], PATHINFO_EXTENSION ) ];
                }
            }

            if( $question->isRequired() && empty( $files ) )
            throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
            
            $this->inputs[$question->getId()] = [ 'attachments' => $files, 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;

        } else {

            $this->inputs[$question->getId()] = [ 'attachments' => $files, 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_email( object $question, array $value ) {
        $survey     = $this->getResponse()->getSurvey();
        $text       = $value['value'] ?? '';
        $text       = trim( $text );

        if( $question->isRequired() && empty( $text ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $text ) ) {
            if( !filter_var( $text, FILTER_VALIDATE_EMAIL ) )
            throw new \Exception( $survey->getText( 'iemail', t( 'This is not a valid email address' ) ) );  

            $setting    = $question->getSetting();
            $points     = (int) ( $setting['email_setting']['points'] ?? 0 );

            $this->inputs[$question->getId()] = [ 'text' => esc_html( $text ), 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_net_prom( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();
        $value  = $value['value'] ?? '';
        $value  = (int) $value;

        if( $question->isRequired() && empty( $value ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( !empty( $value ) ) {
            if( $value < 1 || $value > 10 )
            throw new \Exception( t( 'Unexpected' ) );  

            $setting    = $question->getSetting();
            $setting    = $setting['net_prom_setting'] ?? [];
            $points     = (int) ( $setting['points'] ?? 0 );

            $this->inputs[$question->getId()] = [ 'int_group' => [ $value ], 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_checkbox( object $question, array $value ) {
        $setting    = $question->getSetting();

        if( $question->isRequired() && empty( $value['value'] ) )
        throw new \Exception( ( !empty( $setting['checkbox_setting']['terms'] ) ? esc_html( $setting['checkbox_setting']['error'] ) : t( 'You must agree to the terms to complete this survey' ) ) );

        $points = 0;
        $bool   = 0;

        $setting    = $question->getSetting();

        if( !empty( $value['value'] ) ) {
            $points     = (int) ( $setting['checkbox_setting']['points'] ?? 0 );
            $bool       = 1;
        }

        $this->inputs[$question->getId()] = [ 'int_group' => [ $bool ], 'points' => $points ];
        $this->points += $points;
        if( empty( $setting['hide_points'] ) )
        $this->vpoints += $points;

        return true;
    }

    public function validation_matrix_mc( object $question, array $value ) {
        $survey     = $this->getResponse()->getSurvey();
        $labels     = $question->getLabels()->fetch( -1 );
        $columns    = $question->getLabels( 2 )->fetch( -1 );
        $val_labels = array_intersect_key( $labels, $value );

        if( $question->isRequired() && count( $labels ) !== count( $val_labels ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
        
        if( count( $val_labels ) ) {
            $cascades   = [];
            $setting    = $question->getSetting();
            $points     = (int) ( $setting['matrix_mc_setting']['points'] ?? 0 );

            foreach( $value as $lab_id => $col_id ) {
                $column = $columns[$col_id] ?? NULL;

                if( empty( $val_labels[$lab_id] ) || !$column ) {
                    // invalid value
                    if( $question->isRequired() )
                    throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
                } else {
                    // add value
                    $cascades[$lab_id] = $col_id;
                }
            }

            $this->inputs[$question->getId()] = [ 'int_cascade' => $cascades, 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_matrix_rs( object $question, array $value ) {
        $survey     = $this->getResponse()->getSurvey();
        $labels     = $question->getLabels()->fetch( -1 );
        $val_labels = array_intersect_key( $labels, $value );

        if( $question->isRequired() && count( $labels ) !== count( $val_labels ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        if( count( $val_labels ) ) {
            $cascades   = [];
            $setting    = $question->getSetting();
            $points     = (int) ( $setting['matrix_rs_setting']['points'] ?? 0 );

            foreach( $value as $lab_id => $value ) {
                if( empty( $val_labels[$lab_id] ) || ( $value > 5 || $value < 1 ) ) {
                    // invalid value
                    if( $question->isRequired() )
                    throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
                } else {
                    // add value
                    $cascades[$lab_id] = $value;
                }
            }

            $this->inputs[$question->getId()] = [ 'int_cascade' => $cascades, 'points' => $points ];
            $this->points += $points;
            if( empty( $setting['hide_points'] ) )
            $this->vpoints += $points;
        }

        return true;
    }

    public function validation_matrix_dd( object $question, array $value ) {
        $survey     = $this->getResponse()->getSurvey();
        $labels     = $question->getLabels()->fetch( -1 );
        $val_labels = array_intersect_key( $labels, $value );

        if( $question->isRequired() && count( $val_labels ) !== count( $value ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        $columns    = $question->getLabels( 2 );
        $col_values = [];

        foreach( $columns->fetch( -1 ) as $column ) {
            $col_values[$column->id]    = $columns
                                        ->setObject( $column )
                                        ->getOptions()
                                        ->fetch( -1 );
        }

        $cascades   = [];
        $setting    = $question->getSetting();
        $points     = (int) ( $setting['matrix_dd_setting']['points'] ?? 0 );

        foreach( $value as $lab_id => $val_cols ) {
            $val_columns = array_intersect_key( $val_cols, $col_values );

            if( $question->isRequired() && count( $val_labels ) !== count( $value ) )
            throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

            $cascades[$lab_id] = [];

            foreach( $val_columns as $col_id => $column ) {
                $fvalue = $col_values[$col_id][$val_cols[$col_id]] ?? NULL;

                if( empty( $val_cols[$col_id] ) || !$fvalue ) {
                    // invalid value
                    if( $question->isRequired() ) {
                        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );
                    }
                } else {
                    // add value
                    $cascades[$lab_id][$col_id] = $fvalue->id;
                }
            }
        }

        $this->inputs[$question->getId()] = [ 'int_cascade' => $cascades, 'points' => $points ];
        $this->points += $points;
        if( empty( $setting['hide_points'] ) )
        $this->vpoints += $points;

        return true;
    }

    public function validation_ex_textfield( object $question, array $value ) {
        $survey = $this->getResponse()->getSurvey();
        $text   = $value['text'] ?? '';

        if( $question->isRequired() && empty( $text ) )
        throw new \Exception( $survey->getText( 'rfield', t( 'This field is required' ) ) );

        $points     = 0;
        $setting    = $question->getSetting();
        $setting    = $setting['ex_textfield_setting'] ?? [];
        $response   = $this->getResponse();

        if( !empty( $text ) ) {
            $conn   = new \util\connector( $setting['url'] );
            $conn   ->setMethod( 'POST' );
            $conn   ->setPostFields( [
                'value'     => $text,
                'points'    => $response->getResponsePoints()
            ] );
            if( !$conn->Open() || !( $content = $conn->getContentJson() ) )
            throw new \Exception( t( 'Error! Please contact us' ) );

            if( !empty( $content['error'] ) )
            throw new \Exception( esc_html( $content['error'] ) );

            if( !empty( $content['points'] ) )
            $points = (int) $content['points'];

            if( !empty( $content['add_variable'] ) && is_array( $content['add_variable'] ) ) {
                foreach( $content['add_variable'] as $var_id => $var_value ) {
                    $response->addVariable( $var_id, $var_value );
                }
            }
        }

        $this->inputs[$question->getId()] = [ 'text' => esc_html( $text ), 'points' => $points ];
        $this->strValues[$question->getId()] = esc_html( $text );
        $this->points += $points;
        if( empty( $setting['hide_points'] ) )
        $this->vpoints += $points;

        return true;
    }

    private function mF_multi( $form, $question ) {
        $fields = [ 'multi_p_setting' => [ 'type' => 'repeater', 'label' => t( 'Answer options' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'label' => t( 'Option' ) ],
                'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'classes' => 'wa2', 'value' => 0 ],
            ], 'add_button' => t( 'Add option' )
        ] ];

        $fields['multi_setting'] = [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ]
        ] ];

        if( !$this->type ) {
            $fields['multi_setting']['when'] = [ '=', 'data[type]', 'multi' ];
            $fields['multi_p_setting']['when'] = [ '=', 'data[type]', 'multi' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $options= $question->getOptions();
            $values['multi_p_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title, 'points' => $v->points ];
            }, $options->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_checkboxes( $form, $question ) {
        $fields = [ 'checkboxes_p_setting' => [ 'type' => 'repeater', 'label' => t( 'Answer options' ), 'fields' => [
            'title'     => [ 'type' => 'text', 'label' => t( 'Option' ) ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'classes' => 'wa2', 'value' => 0 ],
            ], 'add_button' => t( 'Add option' ) ]
        ];

        $fields['checkboxes_setting'] = [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'from'      => [ 'type' => 'number', 'label' => t( 'Min selections' ), 'min' => 1, 'value' => 1 ],
                'to'        => [ 'type' => 'number', 'label' => t( 'Max selections' ), 'min' => 1, 'value' => 1 ]
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'type'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ]
        ] ];

        if( !$this->type ) {
            $fields['checkboxes_setting']['when'] = [ '=', 'data[type]', 'checkboxes' ];
            $fields['checkboxes_p_setting']['when'] = [ '=', 'data[type]', 'checkboxes' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $options= $question->getOptions();
            $values['checkboxes_p_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title, 'points' => $v->points ];
            }, $options->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_textfield( $form, $question ) {
        $fields = [ 'text_setting' => [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'format'=> [ 'type' => 'select', 'label' => t( 'Format' ), 'options' => [ '' => t( 'String' ), 'number' => t( 'Number' ) ] ],
                'from'  => [ 'type' => 'number', 'label' => t( 'Min length' ), 'value' => 0 ],
                'to'    => [ 'type' => 'number', 'label' => t( 'Max length' ), 'value' => 255 ],
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Find words/phrases' ), 'fields' => [
                'cond' => [ 'type' => 'repeater', 'fields' => [
                    'word'      => [ 'type' => 'text', 'label' => t( 'Word/phrase' ) ],
                    'find'      => [ 'type' => 'select', 'label' => t( 'Find' ), 'options' => [ '' => t( 'Anywhere' ), 'exact' => t( 'Exact' ) ] ],
                    'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'value' => 0, 'classes' => 'wa2' ]
                ], 'add_button' => t( 'Add word/phrase' ) ], 'grouped' => false ], 'grouped' => false ],
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'type'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ] ], 'grouped' => false ]
        ] ] ];

        if( !$this->type ) {
            $fields['text_setting']['when']     = [ '=', 'data[type]', 'text' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $words  = $question->getAnswerConditions();
            $values['text_setting']['cond'] = array_map( function( $v ) {
                $word = json_decode( $v->value, true );
                if( isset( $word['word'] ) && isset( $word['find'] ) )
                return [ 'word' => $word['word'], 'find' => ( $word['find'] ?? '' ), 'points' => $v->points ];
            }, $words->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_textarea( $form, $question ) {
        $fields = [ 'textarea_setting' => [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'from'  => [ 'type' => 'number', 'label' => t( 'Min length' ), 'value' => 0 ],
                'to'    => [ 'type' => 'number', 'label' => t( 'Max length' ), 'value' => 5000 ],
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Find words/phrases' ), 'fields' => [
                'cond' => [ 'type' => 'repeater', 'fields' => [
                    'word'      => [ 'type' => 'text', 'label' => t( 'Word/phrase' ) ],
                    'find'      => [ 'type' => 'select', 'label' => t( 'Find' ), 'options' => [ '' => t( 'Anywhere' ), 'exact' => t( 'Exact' ) ] ],
                    'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'value' => 0, 'classes' => 'wa2' ]
                ], 'add_button' => t( 'Add word/phrase' ) ], 'grouped' => false ], 'grouped' => false ],
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'type'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ] ], 'grouped' => false ]
        ] ] ];

        if( !$this->type ) {
            $fields['textarea_setting']['when']     = [ '=', 'data[type]', 'textarea' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $words  = $question->getAnswerConditions();
            $values['textarea_setting']['cond'] = array_map( function( $v ) {
                $word = json_decode( $v->value, true );
                if( isset( $word['word'] ) && isset( $word['find'] ) )
                return [ 'word' => $word['word'], 'find' => ( $word['find'] ?? '' ), 'points' => $v->points ];
            }, $words->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_dropdown( $form, $question ) {
        $fields = [ 'dropdown_p_setting' => [ 'type' => 'repeater', 'label' => t( 'Answer options' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'label' => t( 'Option' ) ],
                'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'classes' => 'wa2', 'value' => 0 ],
            ], 'add_button' => t( 'Add option' ) ]
        ];

        $fields['dropdown_setting'] = [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ]
        ] ];

        if( !$this->type ) {
            $fields['dropdown_setting']['when'] = [ '=', 'data[type]', 'dropdown' ];
            $fields['dropdown_p_setting']['when'] = [ '=', 'data[type]', 'dropdown' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $values['dropdown_p_setting'] = array_map( function( $v ) {
                return [ 'title' => esc_html( $v->title ), 'points' => $v->points ];
            }, $question->getOptions()->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_date( $form, $question ) {
        $fields = [ 'date_setting' => [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'format'    => [ 'type' => 'select', 'label' => t( 'Date format' ), 'options' => [ 'm/d/y' => t( 'month/day/year' ), 'd/m/y' => t( 'day/month/year' ) ] ],
                'save_h'    => [ 'type' => 'select', 'label' => t( 'Ask for time' ), 'options' => [ 1 => t( 'Yes' ), 0 => t( 'No' ) ], 'value' => 1 ],
                'hour_f'    => [ 'type' => 'select', 'label' => t( 'Hour format' ), 'options' => [ 24 => t( '24-hour clock' ), 12 => t( '12-hour clock' ) ], 'value' => 24, 'when' => [ '=', 'data[date_setting][save_h]', 1 ] ]
            ], 'grouped' => false ],
            [ 'type' => 'inline-group', 'fields' => [
                'date_from'     => [ 'type' => 'select', 'label' => t( 'Date from' ), 'options' => [ 'any' => t( 'Anytime' ), 'today' => t( 'Today' ), 'tomorrow' => t( 'Tomorrow' ), 'upto' => t( 'Select' ) ] ],
                'date_from2'    => [ 'type' => 'text', 'label' => t( 'Date from' ), 'input_type' => 'date', 'when' => [ '=', 'data[date_setting][date_from]', 'upto' ] ],
                'date_to'       => [ 'type' => 'select', 'label' => t( 'Date to' ), 'options' => [ 'any' => t( 'Anytime' ), 'tomorrow' => t( 'Tomorrow' ), 'tweek' => t( 'This week' ), 'tmonth' => t( 'This month' ), 'tyear' => t( 'This year' ), 'upto' => t( 'Select' ) ] ],
                'date_to2'      => [ 'type' => 'text', 'label' => t( 'Date from' ), 'input_type' => 'date', 'when' => [ '=', 'data[date_setting][date_to]', 'upto' ] ]
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Ranges' ), 'fields' => [
            'cond' => [ 'type' => 'repeater', 'fields' => [
                'from'      => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'From' ) ],
                'to'        => [ 'type' => 'text', 'input_type' => 'datetime-local', 'label' => t( 'To' ) ],
                'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'value' => 0 ]
            ], 'add_button' => t( 'Add range' ) ] ], 'grouped' => false ],
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'type'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ] 
        ] ] ];

        if( !$this->type ) {
            $fields['date_setting']['when'] = [ '=', 'data[type]', 'date' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $values['date_setting']['cond'] = array_map( function( $v ) {
                $range = json_decode( $v->value, true );
                if( isset( $range['from'] ) && isset( $range['to'] ) )
                return [ 'from' => date( 'Y-m-d\TH:i', $range['from'] ), 'to' => date( 'Y-m-d\TH:i', $range['to'] ), 'points' => $v->points ];
            }, $question->getAnswerConditions()->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_imagec( $form, $question ) {
        $fields = [ 'images_p_setting' => [ 'type' => 'repeater', 'label' => t( 'Answer options' ), 'fields' => [
            'image'     => [ 'type' => 'image', 'label' => t( 'Image' ), 'category' => 'question', 'classes' => 'wa' ],
            'hasImages' => [ 'type' => 'hidden' ],
            'title'     => [ 'type' => 'text', 'label' => t( 'Name' ), 'placeholder' => t( 'Name' ) ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'classes' => 'wa2', 'value' => 0 ],
        ], 'add_button' => t( 'Add image' ) ] ];

        $fields[ 'images_setting' ] = [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'from'      => [ 'type' => 'number', 'label' => t( 'Min selections' ), 'min' => 1, 'value' => 1 ],
                'to'        => [ 'type' => 'number', 'label' => t( 'Max selections' ), 'min' => 1, 'value' => 1 ]
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [ [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'points'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ],
        ] ];

        if( !$this->type ) {
            $fields['images_setting']['when'] = [ '=', 'data[type]', 'imagec' ];
            $fields['images_p_setting']['when'] = [ '=', 'data[type]', 'imagec' ];
        }

        if( $question ) {
            $options    = $question->getOptions();
            $values     = $question->getSetting();
            $values['images_p_setting'] = array_map( function( $v ) use ( $options ) {
                $options->setObject( $v );
                $images = [];
                foreach( ( $media = $options->getMedia() )->visible()->fetch( 1 ) as $mediaFile ) {
                    $media->setObject( $mediaFile );
                    $images[$media->getId()] = esc_html( $media->getURL() );
                }
                return [ 'image' => $images, 'hasImages' => count( $images ), 'title' => $options->getTitle(), 'points' => $options->getPoints() ];
            }, $options->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_matrix_mc( $form, $question ) {
        $fields = [ 'matrix_mc_l_setting' => [ 'type' => 'repeater', 'label' => t( 'Labels' ), 'fields' => [
            'title'     => [ 'type' => 'text', 'placeholder' => t( 'Name' ) ]
        ], 'add_button' => t( 'Add label' ) ] ];

        $fields['matrix_mc_c_setting'] = [ 'type' => 'repeater', 'label' => t( 'Columns' ), 'fields' => [
            'title' => [ 'type' => 'text', 'placeholder' => t( 'Column name' ) ],
        ], 'add_button' => t( 'Add column' ) ];

        $fields['matrix_mc_setting'] = [ 'type' => 'group', 'fields' => [
            'points' => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle_l' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle labels' ), 'title' => t( 'Shuffle labels for each respondent' ) ],
                'shuffle_c' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle columns' ), 'title' => t( 'Shuffle columns for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ] 
        ] ];

        if( !$this->type ) {
            $fields['matrix_mc_setting']['when'] = [ '=', 'data[type]', 'matrix_mc' ];
            $fields['matrix_mc_l_setting']['when'] = [ '=', 'data[type]', 'matrix_mc' ];
            $fields['matrix_mc_c_setting']['when'] = [ '=', 'data[type]', 'matrix_mc' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $labels = $question->getLabels();
            $values['matrix_mc_l_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title ];
            }, $labels->fetch( -1 ) );
            $cols  = $question->getLabels( 2 );
            $values['matrix_mc_c_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title ];
            }, $cols->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_matrix_rs( $form, $question ) {
        $fields = [ 'matrix_rs_l_setting' => [ 'type' => 'repeater', 'label' => t( 'Labels' ), 'fields' => [
            'title'     => [ 'type' => 'text', 'placeholder' => t( 'Name' ) ]
        ], 'add_button' => t( 'Add label' ) ] ];

        $fields['matrix_rs_setting'] = [ 'type' => 'group', 'fields' => [
            'points' => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle_l' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle labels' ), 'title' => t( 'Shuffle labels for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ] 
        ] ];

        if( !$this->type ) {
            $fields['matrix_rs_setting']['when'] = [ '=', 'data[type]', 'matrix_rs' ];
            $fields['matrix_rs_l_setting']['when'] = [ '=', 'data[type]', 'matrix_rs' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $labels = $question->getLabels();
            $values['matrix_rs_l_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title ];
            }, $labels->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_matrix_dd( $form, $question ) {
        $fields = [ 'matrix_dd_l_setting' => [ 'type' => 'repeater', 'label' => t( 'Labels' ), 'fields' => [
            'title'     => [ 'type' => 'text', 'placeholder' => t( 'New label' ) ]
        ], 'add_button' => t( 'Add label' ) ] ];

        $fields['matrix_dd_c_setting'] = [ 'type' => 'repeater', 'label' => t( 'Columns' ), 'fields' => [
            'columns' => [ 'type' => 'group', 'fields' => [
            'title' => [ 'type' => 'text', 'placeholder' => t( 'Column name' ) ],
            'options' => [ 'type' => 'repeater', 'label' => t( 'Options' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'placeholder' => t( 'Option name' ) ]
            ], 'add_button' => t( 'Add option'), 'classes' => 'fl_bg' ] ], 'grouped' => false ]
        ], 'add_button' => t( 'Add column' ) ];

        $fields['matrix_dd_setting'] = [ 'type' => 'group', 'fields' => [
            'points' => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle_l' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle labels' ), 'title' => t( 'Shuffle labels for each respondent' ) ],
                'shuffle_c' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle columns' ), 'title' => t( 'Shuffle columns for each respondent' ) ],
                'shuffle_o' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ],
            ], 'grouped' => false ] ], 'grouped' => false ] 
        ] ];

        if( !$this->type ) {
            $fields['matrix_dd_setting']['when'] = [ '=', 'data[type]', 'matrix_dd' ];
            $fields['matrix_dd_l_setting']['when'] = [ '=', 'data[type]', 'matrix_dd' ];
            $fields['matrix_dd_c_setting']['when'] = [ '=', 'data[type]', 'matrix_dd' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $labels = $question->getLabels();
            $values['matrix_dd_l_setting'] = array_map( function( $v ) {
                return [ 'title' => $v->title ];
            }, $labels->fetch( -1 ) );
            $cols  = $question->getLabels( 2 );
            $values['matrix_dd_c_setting'] = array_map( function( $v ) use ( $cols, $values ) {
                $cols   ->setObject( $v );
                $opts   = $cols->getOptions();
                return [ 'title' => $v->title, 'options' => array_map( function( $vo ) {
                    return [ 'title' => $vo->title ];
                }, $opts->fetch( -1 ) ) ];
            }, $cols->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_email( $form, $question ) {
        $fields['email_setting'] = [ 'type' => 'group', 'fields' => [
            'points' => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ]
        ] ];

        if( !$this->type ) {
            $fields['email_setting']['when'] = [ '=', 'data[type]', 'email' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_net_prom( $form, $question ) {
        $fields['net_prom_setting'] = [ 'type' => 'group', 'fields' => [
            'points' => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ]
        ] ];

        if( !$this->type ) {
            $fields['net_prom_setting']['when'] = [ '=', 'data[type]', 'net_prom' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            if( $values )
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_file( $form, $question ) {
        $fields = [ 'file_setting' => [ 'type' => 'group', 'fields' => [
            'extension' => [ 'type' => 'checkboxes', 'label' => t( 'Extensions accepted' ), 'options' => [ 'pdf' => 'PDF', 'doc' => 'DOC/DOCX', 'png' => 'PNG', 'jpg' => 'JPG/JPEG', 'gif' => 'GIF' ] ],
            'ulink'     => [ 'type' => 'checkbox', 'label' => t( 'Request external links' ), 'title' => t( 'Activate option for external links' ), 'description' => t( "Instead of files, you can request external links. We don't check or download external links." ) ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if a valid file/link is provided' ) ]
        ] ] ];
        
        if( !$this->type ) {
            $fields['file_setting']['when'] = [ '=', 'data[type]', 'file' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_contact( $form, $question ) {
        $fields = [ 'contact_setting' => [ 'type' => 'group', 'fields' => [
            'info'      => [ 'type' => 'checkboxes', 'label' => t( 'Reqest information' ), 'options' => [ 'name' => t( 'First and last name' ), 'company' => t( 'Company' ), 'address' => t( 'Address' ), 'address2' => t( 'Address 2' ), 'city' => t( 'City/Town' ), 'state' => t( 'State/Province' ), 'zip' => t( 'ZIP/Postal Code' ), 'country' => t( 'Country' ), 'email' => t( 'Email' ), 'phone' => t( 'Phone' ) ], 'value' => [] ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ]
        ] ] ];
        
        if( !$this->type ) {
            $fields['contact_setting']['when'] = [ '=', 'data[type]', 'contact' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_slider( $form, $question ) {
        $fields = [ 'slider_setting' => [ 'type' => 'group', 'fields' => [
            [ 'type' => 'inline-group', 'fields' => [
                'from'      => [ 'type' => 'number', 'label' => t( 'Min selections' ), 'min' => 1, 'value' => 1 ],
                'to'        => [ 'type' => 'number', 'label' => t( 'Max selections' ), 'min' => 2, 'value' => 100 ]
            ], 'grouped' => false ],
            [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Ranges' ), 'fields' => [
            'cond' => [ 'type' => 'repeater', 'fields' => [
                'from'      => [ 'type' => 'number', 'label' => t( 'From' ) ],
                'to'        => [ 'type' => 'number', 'label' => t( 'To' ) ],
                'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'value' => 0 ]
            ], 'add_button' => t( 'Add range' ) ] ], 'grouped' => false ],
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Points' ), 'fields' => [
                'points'    => [ 'type' => 'select', 'label' => t( 'How to calculate points' ), 'options' => [ 
                    'acc' => t( 'Accumulate from each answer' ), 
                    'max' => t( 'Use the maximum value' )
                ] ]
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ] ], 'grouped' => false ] 
        ] ] ];

        if( !$this->type ) {
            $fields['slider_setting']['when'] = [ '=', 'data[type]', 'slider' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $values['slider_setting']['cond'] = array_map( function( $v ) {
                $range = json_decode( $v->value, true );
                if( isset( $range['from'] ) && isset( $range['to'] ) )
                return [ 'from' => (int) $range['from'], 'to' => (int) $range['to'], 'points' => $v->points ];
            }, $question->getAnswerConditions()->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_srating( $form, $question ) {
        $fields = [ 'srating_setting' => [ 'type' => 'group', 'fields' => [
            'stars'     => [ 'type' => 'select', 'label' => t( 'Format (stars)' ), 'options' => array_combine( range( 2, 10 ), range( 2, 10 ) ), 'value' => 5 ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if information is provided' ) ]
        ] ] ]; 

        if( !$this->type ) {
            $fields['srating_setting']['when'] = [ '=', 'data[type]', 'srating' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_ranking( $form, $question ) {
        $pos    = [];
        foreach( range( 1, 10 ) as $p ) {
            $pos['=' . $p] = '= ' . $p;
            if( $p > 1 )
            $pos['<' . $p] = '<= ' . $p;
        }

        $fields = [ 'ranking_p_setting' => [ 'type' => 'repeater', 'label' => t( 'Answer options' ), 'fields' => [
                'title'     => [ 'type' => 'text', 'label' => t( 'Option' ) ],
                'points'    => [ 'type' => 'number', 'label' => t( 'Points for' ), 'classes' => 'wa2', 'value' => 0 ],
                'sign'      => [ 'type' => 'select', 'label' => t( 'position' ), 'classes' => 'wa', 'options' => $pos ],
            ], 'add_button' => t( 'Add option' ) ]
        ];

        $fields['ranking_setting'] = [ 'type' => 'dropdown', 'label' => t( 'More' ), 'fields' => [
            [ 'label' => t( 'Conditions' ), 'fields' => [
                'min'   => [ 'type' => 'number', 'label' => t( 'The minimum number of points to advance' ), 'min' => 0 ],
                'error' => [ 'type' => 'text', 'label' => t( 'Error message if there are not enough points' ) ],
            ], 'grouped' => false ],
            [ 'label' => t( 'Other' ), 'fields' => [
                'shuffle' => [ 'type' => 'checkbox', 'label' => t( 'Shuffle answer options' ), 'title' => t( 'Shuffle answer options for each respondent' ) ]
            ], 'grouped' => false ]
        ] ];

        if( !$this->type ) {
            $fields['ranking_setting']['when'] = [ '=', 'data[type]', 'ranking' ];
            $fields['ranking_p_setting']['when'] = [ '=', 'data[type]', 'ranking' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $values['ranking_p_setting'] = array_map( function( $v ) {
                $setting = json_decode( $v->setting, true );
                return [ 'title' => $v->title, 'points' => $v->points, 'sign' => ( $setting['sign'] ?? '=1' ) ];
            }, $question->getOptions()->fetch( -1 ) );
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_checkbox( $form, $question ) {
        $fields = [ 'checkbox_setting' => [ 'type' => 'group', 'fields' => [
            'label'     => [ 'type' => 'text', 'label' => t( 'Checkbox label' ) ],
            'terms'     => [ 'type' => 'textarea', 'label' => t( 'Terms that must be accepted (if any)' ) ],
            'error'     => [ 'type' => 'text', 'label' => t( 'Error message if terms are not accepted' ) ],
            'points'    => [ 'type' => 'number', 'label' => t( 'Points' ), 'description' => t( 'Points if terms are accepted' ) ]
        ] ] ];

        if( !$this->type ) {
            $fields['checkbox_setting']['when'] = [ '=', 'data[type]', 'checkbox' ];
        }

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function mF_ex_textfield( $form, $question ) {
        $fields['ex_textfield_setting'] = [ 'type' => 'group', 'fields' => [
            'url' => [ 'type' => 'text', 'label' => t( 'URL' ), 'placeholder' => 'https://' ],
        ] ];

        if( !$this->type )
        $fields['ex_textfield_setting']['when'] = [ '=', 'data[type]', 'ex_textfield' ];

        if( $question ) {
            $values = $question->getSetting();
            $form->setValues( $values );
        }

        $form->addFieldsAfter( 'type', $fields );
    }

    private function cD_multi( array $data, string $type ) : void {
        if( !empty( $data['multi_setting'] ) )
        $this->setting['multi_setting'] = $data['multi_setting'];
    }

    private function cD_checkboxes( array $data, string $type ) : void {
        if( !empty( $data['checkboxes_setting'] ) )
        $this->setting['checkboxes_setting'] = $data['checkboxes_setting'];
    }

    private function cD_textfield( array $data, string $type ) : void {
        if( !empty( $data['text_setting'] ) ) {
            unset( $data['text_setting']['cond'] );
            $this->setting['text_setting'] = $data['text_setting'];
        }
    }

    private function cD_textarea( array $data, string $type ) : void {
        if( !empty( $data['textarea_setting'] ) ) {
            unset( $data['textarea_setting']['cond'] );
            $this->setting['textarea_setting'] = $data['textarea_setting'];
        }
    }

    private function cD_dropdown( array $data, string $type ) : void {
        if( !empty( $data['dropdown_setting'] ) )
        $this->setting['dropdown_setting'] = $data['dropdown_setting'];
    }

    private function cD_imagec( array $data, string $type ) : void {
        if( !empty( $data['images_setting'] ) )
        $this->setting['images_setting'] = $data['images_setting'];
    }

    private function cD_date( array $data, string $type ) : void {
        if( !empty( $data['date_setting'] ) )
        $this->setting['date_setting']  = $data['date_setting'];
        if( !empty( $data['date_setting2'] ) )
        $this->setting['date_setting2'] = $data['date_setting2'];
    }

    private function cD_contact( array $data, string $type ) : void {
        if( !empty( $data['contact_setting'] ) )
        $this->setting['contact_setting'] = $data['contact_setting'];
    }

    private function cD_slider( array $data, string $type ) : void {
        if( !empty( $data['slider_setting'] ) )
        $this->setting['slider_setting'] = $data['slider_setting'];
    }

    private function cD_matrix_mc( array $data, string $type ) : void {
        if( !empty( $data['matrix_mc_setting'] ) )
        $this->setting['matrix_mc_setting'] = $data['matrix_mc_setting'];
    }

    private function cD_matrix_rs( array $data, string $type ) : void {
        if( !empty( $data['matrix_rs_setting'] ) )
        $this->setting['matrix_rs_setting'] = $data['matrix_rs_setting'];
    }

    private function cD_matrix_dd( array $data, string $type ) : void {
        if( !empty( $data['matrix_dd_setting'] ) )
        $this->setting['matrix_dd_setting'] = $data['matrix_dd_setting'];
    }

    private function cD_srating( array $data, string $type ) : void {
        if( !empty( $data['srating_setting'] ) )
        $this->setting['srating_setting'] = $data['srating_setting'];
    }

    private function cD_ranking( array $data, string $type ) : void {
        if( !empty( $data['ranking_setting'] ) )
        $this->setting['ranking_setting'] = $data['ranking_setting'];
        if( !empty( $data['ranking_p_setting'] ) )
        $this->setting['ranking_p_setting'] = $data['ranking_p_setting'];
    }

    private function cD_email( array $data, string $type ) : void {
        if( !empty( $data['email_setting'] ) )
        $this->setting['email_setting'] = $data['email_setting'];
    }

    private function cD_file( array $data, string $type ) : void {
        if( !empty( $data['file_setting'] ) )
        $this->setting['file_setting'] = $data['file_setting'];
    }

    private function cD_net_prom( array $data, string $type ) : void {
        if( !empty( $data['net_prom_setting'] ) )
        $this->setting['net_prom_setting'] = $data['net_prom_setting'];
    }

    private function cD_checkbox( array $data, string $type ) : void {
        if( !empty( $data['checkbox_setting'] ) )
        $this->setting['checkbox_setting'] = $data['checkbox_setting'];
    }

    private function cD_ex_textfield( array $data, string $type ) : void {
        if( !empty( $data['ex_textfield_setting'] ) )
        $this->setting['ex_textfield_setting'] = $data['ex_textfield_setting'];
    }

    private function aU_multi( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['multi_p_setting'] ) ) {
            return true;
        }

        $options    = $data['multi_p_setting'];
        array_pop( $options );

        if( $type == 'edit' ) {
            $c_options  = $question->getOptions()->fetch( -1 );
            $deleted    = array_diff_key( $c_options, $options );

            foreach( $options as $opt_id => $option ) {
                $cOption = $c_options[$opt_id] ?? NULL;

                if( !$cOption ) {
                    // new option
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] );
                } else {
                    // old option
                    $points = $option['points'] ?? 0;
                    if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) || $points != $cOption->points ) {
                        // update old option
                        me()->form_actions()->edit_option( $cOption->id, 0, $question->getId(), [ 'points' => $points, 'title' => $option['title'] ] );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $option ) {
                    me()->form_actions()->delete_option( $option->id, 0, $question->getId() );
                }
            }

            return ;
        }

        foreach( $options as $option ) {
            if( isset( $option['points'] ) && !empty( $option['title'] ) )
            me()->form_actions()->add_option( 0, $question, [ 'points' => $option['points'], 'title' => $option['title'] ] );
        }
    }

    private function aU_checkboxes( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['checkboxes_p_setting'] ) ) {
            return true;
        }

        $options    = $data['checkboxes_p_setting'];
        array_pop( $options );

        if( $type == 'edit' ) {
            $c_options  = $question->getOptions()->fetch( -1 );
            $deleted    = array_diff_key( $c_options, $options );

            foreach( $options as $opt_id => $option ) {
                $cOption = $c_options[$opt_id] ?? NULL;

                if( !$cOption ) {
                    // new option
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] );
                } else {
                    // old option
                    $points = $option['points'] ?? 0;
                    if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) || $points != $cOption->points ) {
                        // update old option
                        me()->form_actions()->edit_option( $cOption->id, 0, $question->getId(), [ 'points' => $points, 'title' => $option['title'] ] );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $option ) {
                    me()->form_actions()->delete_option( $option->id, 0, $question->getId() );
                }
            }

            return ;
        }

        foreach( $options as $option_id => $option ) {
            if( isset( $option['points'] ) && !empty( $option['title'] ) )
            me()->form_actions()->add_option( 0, $question, [ 'points' => $option['points'], 'title' => $option['title'] ] );
        }
    }

    private function aU_textfield( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['text_setting']['cond'] ) ) {
            return true;
        }

        $conditions = $data['text_setting']['cond'];
        array_pop( $conditions );

        if( $type == 'edit' ) {
            $c_conditions   = $question->getAnswerConditions()->fetch( -1 );
            $deleted        = array_diff_key( $c_conditions, $conditions );

            foreach( $conditions as $cnd_id => $condition ) {
                $cCondition = $c_conditions[$cnd_id] ?? NULL;
                $points     = $condition['points'] ?? 0;
                if( !$cCondition ) {
                    // new option
                    me()->form_actions()->add_condition( $question->getId(), cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), $points );
                } else {
                    // old option
                    $value = json_decode( $cCondition->value );
                    if( isset( $condition['word'] ) && isset( $condition['find'] ) && ( !isset( $value->word ) || !isset( $value->find ) || $condition['word'] != $value->word || ( $condition['find'] != $value->find ) || $points != $cCondition->points ) ) {
                        // update old option
                        me()->form_actions()->edit_condition( $cCondition->id, $question->getId(), cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), $points );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $condition ) {
                    me()->form_actions()->delete_condition( $condition->id );
                }
            }

            return ;
        }

        foreach( $conditions as $condition ) {
            if( isset( $condition['word'] ) && isset( $condition['find'] ) && isset( $condition['points'] ) )
            me()->form_actions()->add_condition( $question, cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), $condition['points'] );
        }
    }

    private function aU_textarea( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['textarea_setting']['cond'] ) ) {
            return true;
        }

        $conditions = $data['textarea_setting']['cond'];
        array_pop( $conditions );

        if( $type == 'edit' ) {
            $c_conditions   = $question->getAnswerConditions()->fetch( -1 );
            $deleted        = array_diff_key( $c_conditions, $conditions );

            foreach( $conditions as $cnd_id => $condition ) {
                $cCondition = $c_conditions[$cnd_id] ?? NULL;
                $points     = $condition['points'] ?? 0;
                if( !$cCondition ) {
                    // new option
                    me()->form_actions()->add_condition( $question->getId(), cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), $points );
                } else {
                    // old option
                    $value = json_decode( $cCondition->value );
                    if( isset( $condition['word'] ) && isset( $condition['find'] ) && ( !isset( $value->word ) || !isset( $value->find ) || $condition['word'] != $value->word || ( $condition['find'] != $value->find ) || $points != $cCondition->points ) ) {
                        // update old option
                        me()->form_actions()->edit_condition( $cCondition->id, $question->getId(), cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), $points );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $condition ) {
                    me()->form_actions()->delete_condition( $condition->id );
                }
            }

            return ;
        }

        foreach( $conditions as $condition ) {
            if( isset( $condition['word'] ) && isset( $condition['find'] ) && isset( $condition['points'] ) )
            me()->form_actions()->add_condition( $question, cms_json_encode( [ 'word' => $condition['word'], 'find' => $condition['find'] ] ), (int) $condition['points'] );
        }
    }

    private function aU_dropdown( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['dropdown_p_setting'] ) ) {
            return true;
        }

        $options    = $data['dropdown_p_setting'];
        array_pop( $options );

        if( $type == 'edit' ) {
            $c_options  = $question->getOptions()->fetch( -1 );
            $deleted    = array_diff_key( $c_options, $options );

            foreach( $options as $opt_id => $option ) {
                $cOption = $c_options[$opt_id] ?? NULL;

                if( !$cOption ) {
                    // new option
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] );
                } else {
                    // old option
                    $points = $option['points'] ?? 0;
                    if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) || $points != $cOption->points ) {
                        // update old option
                        me()->form_actions()->edit_option( $cOption->id, 0, $question->getId(), [ 'points' => $points, 'title' => $option['title'] ] );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $option ) {
                    me()->form_actions()->delete_option( $option->id, 0, $question->getId() );
                }
            }

            return ;
        }

        foreach( $options as $option ) {
            if( isset( $option['points'] ) && !empty( $option['title'] ) )
            me()->form_actions()->add_option( 0, $question, [ 'points' => $option['points'], 'title' => $option['title'] ] );
        }
    }

    private function aU_date( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['date_setting']['cond'] ) ) {
            return true;
        }

        $conditions = $data['date_setting']['cond'];
        array_pop( $conditions );

        if( $type == 'edit' ) {
            $c_conditions   = $question->getAnswerConditions()->fetch( -1 );
            $deleted        = array_diff_key( $c_conditions, $conditions );

            foreach( $conditions as $cnd_id => $condition ) {
                $cCondition = $c_conditions[$cnd_id] ?? NULL;
                $points     = $condition['points'] ?? 0;
                if( !$cCondition ) {
                    // new option
                    me()->form_actions()->add_condition( $question->getId(), cms_json_encode( [ 'from' => strtotime( $condition['from'] ), 'to' => strtotime( $condition['to'] ) ] ), $points );
                } else {
                    // old option
                    $value = json_decode( $cCondition->value );
                    if( isset( $condition['from'] ) && isset( $condition['to'] ) && ( !isset( $value->from ) || !isset( $value->to ) || ( $condition['from'] != $value->from ) || ( $condition['to'] != $value->to ) || $points != $cCondition->points ) ) {
                        // update old option
                        me()->form_actions()->edit_condition( $cCondition->id, $question->getId(), cms_json_encode( [ 'from' => strtotime( $condition['from'] ), 'to' => strtotime( $condition['to'] ) ] ), $points );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $conditions ) {
                    me()->form_actions()->delete_condition( $condition->id );
                }
            }

            return ;
        }

        foreach( $conditions as $condition ) {
            if( isset( $condition['from'] ) && isset( $condition['to'] ) && isset( $condition['points'] ) )
            me()->form_actions()->add_condition( $question, cms_json_encode( [ 'from' => strtotime( $condition['from'] ), 'to' => strtotime( $condition['to'] ) ] ), $condition['points'] );
        }
    }

    private function aU_imagec( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['images_p_setting'] ) )
        return true;

        $inputs         = $data['images_p_setting'] ?? [];
        array_pop( $inputs );

        if( $type == 'add' ) {

            $question       = questions( $question );
            if( !$question->getObject() || $question->getSurveyId() !== $survey->getId() )
            return true;

            $form           = new \markup\front_end\form_fields;
            $uploads        = $form->getFileRequestsArray();
            $uploads        = $uploads['data']['images_p_setting'] ?? [];

            foreach( $inputs as $option_id => $option ) {
                if( ( $oid = me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] ) ) ) {
                    if( !empty( $uploads[$option_id]['image'] ) ) {
                        // upload the new media files
                        media( $uploads[$option_id]['image'] )
                        ->setOwnerId( $survey->getUserId() )
                        ->isImage()
                        ->imageSize( '300x300' )
                        ->setType( 2 )
                        ->setTypeId( $oid )
                        ->getUploadId();
                    }
                }
            }

        } else if( $type == 'edit' ) {

            $options        = $question->getOptions()->select( [ 'id' ] );
            $old_options    = $options->fetch( -1 );
            $del_options    = array_diff_key( $old_options, $inputs );
            $form           = new \markup\front_end\form_fields;
            $uploads        = $form->getFileRequestsArray();
            $uploads        = $uploads['data']['images_p_setting'] ?? [];

            foreach( $inputs as $option_id => $option ) {
                // Option updated
                if( isset( $old_options[$option_id] ) ) {
                    $options->setObject( $old_options[$option_id] );
                    $opt_id = $options->getId();

                    if( !empty( $option['title'] ) )
                    if( ( me()->form_actions()->edit_option( $opt_id, 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] ) ) ) {
                        $media  = $options->getMedia()
                        ->select( [ 'id' ] );
                        $mFiles = $media->fetch( -1 );

                        if( !empty( $uploads[$opt_id]['image'] ) ) {
                            // remove old media
                            me()->form_actions()->delete_media( 2, $opt_id );

                            // upload the new media files
                            media( $uploads[$opt_id]['image'] )
                            ->setOwnerId( $survey->getUserId() )
                            ->isImage()
                            ->imageSize( '300x300' )
                            ->setType( 2 )
                            ->setTypeId( $opt_id )
                            ->getUploadId();
                        } else if( empty( $option['image'] ) ) {
                            // remove old media
                            me()->form_actions()->delete_media( 2, $opt_id );
                        }
                    }

                // Option added
                } else {
                    if( ( $oid = me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'] ] ) ) ) {
                        if( !empty( $uploads[$option_id]['image'] ) ) {
                            // upload the new media files
                            media( $uploads[$option_id]['image'] )
                            ->setOwnerId( $survey->getUserId() )
                            ->isImage()
                            ->imageSize( '300x300' )
                            ->setType( 2 )
                            ->setTypeId( $oid )
                            ->getUploadId();
                        }
                    }
                }
            }

            // Delete options
            foreach( $del_options as $option_id => $more ) {
                // remove old media
                me()->form_actions()->delete_media( 2, $option_id );

                // remove option
                me()->form_actions()->delete_option( $option_id, 0, $question->getId() );
            }
        }
    }

    private function aU_slider( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['slider_setting']['cond'] ) ) {
            return true;
        }

        $conditions = $data['slider_setting']['cond'];
        array_pop( $conditions );

        if( $type == 'edit' ) {
            $c_conditions   = $question->getAnswerConditions()->fetch( -1 );
            $deleted        = array_diff_key( $c_conditions, $conditions );

            foreach( $conditions as $cnd_id => $condition ) {
                $cCondition = $c_conditions[$cnd_id] ?? NULL;
                $points     = $condition['points'] ?? 0;
                if( !$cCondition ) {
                    // new option
                    me()->form_actions()->add_condition( $question->getId(), cms_json_encode( [ 'from' => $condition['from'], 'to' => $condition['to'] ] ), $points );
                } else {
                    // old option
                    $value = json_decode( $cCondition->value );
                    if( isset( $condition['from'] ) && isset( $condition['to'] ) && ( !isset( $value->from ) || !isset( $value->to ) || ( $condition['from'] != $value->from ) || ( $condition['to'] != $value->to ) || $points != $cCondition->points ) ) {
                        // update old option
                        me()->form_actions()->edit_condition( $cCondition->id, $question->getId(), cms_json_encode( [ 'from' => $condition['from'], 'to' => $condition['to'] ] ), $points );
                    }
                }
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $condition ) {
                    me()->form_actions()->delete_condition( $condition->id );
                }
            }

            return ;
        }

        foreach( $conditions as $condition ) {
            if( isset( $condition['from'] ) && isset( $condition['to'] ) && isset( $condition['points'] ) )
            me()->form_actions()->add_condition( $question, cms_json_encode( [ 'from' => $condition['from'], 'to' => $condition['to'] ] ), $condition['points'] );
        }
    }

    private function aU_matrix_mc( array $data, $question, object $survey, string $type ) {
        if( isset( $data['matrix_mc_l_setting'] ) ) {
            $options    = $data['matrix_mc_l_setting'];
            array_pop( $options );

            if( $type == 'edit' ) {
                $c_options  = $question->getLabels()->fetch( -1 );
                $deleted    = array_diff_key( $c_options, $options );

                foreach( $options as $opt_id => $option ) {
                    $cOption = $c_options[$opt_id] ?? NULL;

                    if( !$cOption ) {
                        // new option
                        if( !empty( $option['title'] ) )
                        me()->form_actions()->add_option( 1, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                    } else {
                        // old option
                        if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) ) {
                            // update old option
                            me()->form_actions()->edit_option( $cOption->id, 1, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                        }
                    }
                }

                if( !empty( $deleted ) ) {
                    foreach( $deleted as $option ) {
                        me()->form_actions()->delete_option( $option->id, 1, $question->getId() );
                    }
                }
            } else {
                foreach( $options as $option ) {
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 1, $question, [ 'points' => 0, 'title' => $option['title'] ] );
                }
            }
        }

        if( isset( $data['matrix_mc_c_setting'] ) ) {
            $options    = $data['matrix_mc_c_setting'];
            array_pop( $options );

            if( $type == 'edit' ) {
                $c_options  = $question->getLabels( 2 )->fetch( -1 );
                $deleted    = array_diff_key( $c_options, $options );

                foreach( $options as $opt_id => $option ) {
                    $cOption = $c_options[$opt_id] ?? NULL;

                    if( !$cOption ) {
                        // new option
                        if( !empty( $option['title'] ) )
                        me()->form_actions()->add_option( 2, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                    } else {
                        // old option
                        if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) ) {
                            // update old option
                            me()->form_actions()->edit_option( $cOption->id, 2, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                        }
                    }
                }

                if( !empty( $deleted ) ) {
                    foreach( $deleted as $option ) {
                        me()->form_actions()->delete_option( $option->id, 2, $question->getId() );
                    }
                }
            } else {
                foreach( $options as $option ) {
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 2, $question, [ 'points' => 0, 'title' => $option['title'] ] );
                }
            }
        }
    }

    private function aU_matrix_rs( array $data, $question, object $survey, string $type ) {
        if( isset( $data['matrix_rs_l_setting'] ) ) {
            $options    = $data['matrix_rs_l_setting'];
            array_pop( $options );

            if( $type == 'edit' ) {
                $c_options  = $question->getLabels()->fetch( -1 );
                $deleted    = array_diff_key( $c_options, $options );

                foreach( $options as $opt_id => $option ) {
                    $cOption = $c_options[$opt_id] ?? NULL;

                    if( !$cOption ) {
                        // new option
                        if( !empty( $option['title'] ) )
                        me()->form_actions()->add_option( 1, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                    } else {
                        // old option
                        if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) ) {
                            // update old option
                            me()->form_actions()->edit_option( $cOption->id, 1, $question->getId(), [ 'points' => 0, 'title' => $option['title'] ] );
                        }
                    }
                }

                if( !empty( $deleted ) ) {
                    foreach( $deleted as $option ) {
                        me()->form_actions()->delete_option( $option->id, 1, $question->getId() );
                    }
                }

                return ;
            }

            foreach( $options as $option ) {
                if( !empty( $option['title'] ) )
                me()->form_actions()->add_option( 1, $question, [ 'points' => 0, 'title' => $option['title'] ] );
            }
        }
    }

    private function aU_matrix_dd( array $data, $question, object $survey, string $type ) {
        if( isset( $data['matrix_dd_l_setting'] ) ) {
            $ilabels = $data['matrix_dd_l_setting'];
            array_pop( $ilabels );

            // edit question
            if( $type == 'edit' ) {
                $labelsObj  = $question->getLabels();
                $labels     = $labelsObj->fetch( -1 );
                $deletedLab = array_diff_key( $labels, $ilabels );

                foreach( $ilabels as $lab_id => $label ) {
                    $cLabel = $labels[$lab_id] ?? NULL;

                    if( !$cLabel ) {
                        // new label
                        if( !empty( $option['title'] ) )
                        me()->form_actions()->add_option( 1, $question->getId(), [ 'points' => 0, 'title' => $label['title'] ] );
                    } else {
                        // update old label
                        if( ( !empty( $label['title'] ) && $label['title'] != $cLabel->title ) )
                        me()->form_actions()->edit_option( $cLabel->id, 1, $question->getId(), [ 'points' => 0, 'title' => $label['title'] ] );
                    }
                }

                // delete label
                if( !empty( $deletedLab ) ) {
                    foreach( $deletedLab as $label ) {
                        me()->form_actions()->delete_option( $label->id, 1, $question->getId() );
                    }
                }
            } else {
                // new question
                foreach( $ilabels as $label ) {
                    if( !empty( $label['title'] ) )
                    me()->form_actions()->add_option( 1, $question, [ 'points' => 0, 'title' => $label['title'] ] );
                }
            }
        }

        if( isset( $data['matrix_dd_c_setting'] ) ) {
            $icolumns   = $data['matrix_dd_c_setting'];
            array_pop( $icolumns );

            // edit question
            if( $type == 'edit' ) {
                $columnsObj = $question->getLabels( 2 );
                $columns    = $columnsObj->fetch( -1 );
                $deletedCol = array_diff_key( $columns, $icolumns );

                foreach( $icolumns as $col_id => $column ) {
                    $cColumn = $columns[$col_id] ?? NULL;

                    if( !$cColumn ) {
                        // new column
                        if( !empty( $column['title'] ) ) {
                            $colId = me()->form_actions()->add_option( 2, $question->getId(), [ 'points' => 0, 'title' => $column['title'] ] );

                            if( isset( $column['options'] ) ) {
                                array_pop( $column['options'] );

                                if( !empty( $column['options'] ) ) {
                                    foreach( $column['options'] as $opt ) {
                                        if( !empty( $opt['title'] ) )
                                        me()->form_actions()->add_option( 3, $colId, [ 'points' => 0, 'title' => $opt['title'] ] );
                                    }
                                }
                            }
                        }
                    } else {
                        // update old column
                        if( ( !empty( $column['title'] ) && $column['title'] != $cColumn->title ) ) {
                            me()->form_actions()->edit_option( $cColumn->id, 2, $question->getId(), [ 'points' => 0, 'title' => $column['title'] ] );
                        }

                        $ioptions   = $column['options'];
                        array_pop( $ioptions );

                        $options    = $columnsObj->setObject( $cColumn )->getOptions()->fetch( -1 );
                        $deletedOpt = array_diff_key( $options, $ioptions );

                        if( !empty( $ioptions ) ) {
                            foreach( $ioptions as $opt_id => $opt ) {
                                $cOption = $options[$opt_id] ?? NULL;
                                if( !$cOption ) {
                                    // new option
                                    if( !empty( $opt['title'] ) )
                                    me()->form_actions()->add_option( 3, $cColumn->id, [ 'points' => 0, 'title' => $opt['title'] ] );
                                } else {
                                    // update old option
                                    if( ( !empty( $opt['title'] ) && $opt['title'] != $cOption->title ) )
                                    me()->form_actions()->edit_option( $cOption->id, 3, $cColumn->id, [ 'points' => 0, 'title' => $opt['title'] ] );
                                }
                            }
                        }

                        // delete option
                        if( !empty( $deletedOpt ) ) {
                            foreach( $deletedOpt as $option ) {
                                me()->form_actions()->delete_option( $option->id, 3, $cColumn->id );
                            }
                        }
                    }
                }

                // delete column
                if( !empty( $deletedCol ) ) {
                    foreach( $deletedCol as $column ) {
                        me()->form_actions()->delete_option( $column->id, 2, $question->getId() );
                    }
                }
            } else {
                // new question
                foreach( $icolumns as $column ) {
                    if( !empty( $column['title'] ) ) {
                        $colId = me()->form_actions()->add_option( 2, $question, [ 'points' => 0, 'title' => $column['title'] ] );

                        if( isset( $column['options'] ) ) {
                            array_pop( $column['options'] );

                            if( !empty( $column['options'] ) ) {
                                foreach( $column['options'] as $opt ) {
                                    if( !empty( $opt['title'] ) )
                                    me()->form_actions()->add_option( 3, $colId, [ 'points' => 0, 'title' => $opt['title'] ] );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function aU_ranking( array $data, $question, object $survey, string $type ) {
        if( !isset( $data['ranking_p_setting'] ) )
        return true;

        $options    = $data['ranking_p_setting'];
        array_pop( $options );

        if( $type == 'edit' ) {
            $c_options  = $question->getOptions()->fetch( -1 );
            $deleted    = array_diff_key( $c_options, $options );
            $pos        = 1;

            foreach( $options as $opt_id => $option ) {
                $cOption = $c_options[$opt_id] ?? NULL;

                if( !$cOption ) {
                    // new option
                    if( !empty( $option['title'] ) )
                    me()->form_actions()->add_option( 0, $question->getId(), [ 'points' => ( $option['points'] ?? 0 ), 'title' => $option['title'], 'position' => $pos, 'setting' => cms_json_encode( [ 'sign' => $option['sign'] ?? '=' ] ) ] );
                } else {
                    // old option
                    $points     = $option['points'] ?? 0;
                    $setting    = cms_json_encode( [ 'sign' => $option['sign'] ?? '=' ] );
                    if( ( !empty( $option['title'] ) && $option['title'] != $cOption->title ) || $points != $cOption->points || $setting != $cOption->setting ) {
                        // update old option
                        me()->form_actions()->edit_option( $cOption->id, 0, $question->getId(), [ 'points' => $points, 'title' => $option['title'], 'position' => $pos, 'setting' => $setting ] );
                    }
                }
                $pos++;
            }

            if( !empty( $deleted ) ) {
                foreach( $deleted as $option ) {
                    me()->form_actions()->delete_option( $option->id, 0, $question->getId() );
                }
            }

            return ;
        }

        $pos = 1;
        foreach( $options as $option ) {
            if( isset( $option['points'] ) && !empty( $option['title'] ) ) {
                me()->form_actions()->add_option( 0, $question, [ 'points' => $option['points'], 'title' => $option['title'], 'position' => $pos, 'setting' => '' ] );
                $pos++;
            }
        }
    }

    private function aU_ex_textfield( array $data, $question, object $survey, string $type ) {
        if( empty( $data['ex_textfield_setting']['url'] ) || !filter_var( $data['ex_textfield_setting']['url'], FILTER_VALIDATE_URL ) )
        throw new \Exception( t( 'Please provide a valid URL' ) );
    }

    public function checkData( array $data, string $type ) {
        if( !$this->type || !isset( $this->type['checkData'] ) || !is_callable( $this->type['checkData'] ) ) {
            return true;
        }

        return call_user_func( $this->type['checkData'], $data, $type );
    }

    public function afterUpdate( $question, object $survey, array $data, string $type ) {
        if( !$this->type || !isset( $this->type['afterUpdate'] ) || !is_callable( $this->type['afterUpdate'] ) ) {
            return true;
        }

        return call_user_func( $this->type['afterUpdate'], $data, $question, $survey, $type );
    }

    public function setting() {
        if( !$this->setting ) {
            return '';
        }

        return cms_json_encode( $this->setting, JSON_UNESCAPED_UNICODE );
    }
    
    public function modifySubmitForm( $form, $question = NULL ) {
        if( !$this->type ) {
            foreach( $this->types as $type ) {
                if( isset( $type['modifyForm'] ) && is_callable( $type['modifyForm'] ) ) {
                    call_user_func( $type['modifyForm'], $form, $question );
                }
            }
        } else {
            if( isset( $this->type['modifyForm'] ) && is_callable( $this->type['modifyForm'] ) ) {
                call_user_func( $this->type['modifyForm'], $form, $question );
            }
        }

        return $this;
    }

}