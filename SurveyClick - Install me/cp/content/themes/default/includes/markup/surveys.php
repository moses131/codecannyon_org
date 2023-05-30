<?php

namespace admin\markup;

class surveys {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        if( is_callable( [ $this, 'surveys_' . me()->viewAs ] ) )
        $this->{ 'surveys_' . me()->viewAs }();
    }

    private function surveys_respondent() {
        $markup = '<div class="filters">';

        $categories     = categories()->fetch( -1 );
        $ccategories    = array_intersect_key( $categories, paidSurveys()->selectDistinctCategory()->fetch( -1 ) );

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'category'  => [ 'type' => 'select', 'after_label' => '<i class="fas fa-tag"></i>', 'options' => array_map( function( $v ) { return esc_html( $v->name ); }, $ccategories ), 'value' => '', 'placeholder' => t( 'Category' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ), 'cpa' => t( 'Commission &darr;' ), 'cpa_desc' => t( 'Commission &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form surveys_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $surveys = new \admin\markup\table( [
            t( 'Survey' )       => 'tl w150p',
            t( 'Category' )     => '',
            t( 'Commission' )   => 'tc',
            t( '<i class="fas fa-star cl3"></i>' ) => 'tc', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );

        $surveys
        ->title( t( 'Find surveys' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{survey}', 'w150p' )
        ->add( '{category}', '' )
        ->add( '{commission}', 'tc' )
        ->add( '{stars}', 'tc' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $surveys->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "surveys_respondent",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function surveys_surveyor() {
        $markup = '<div class="filters">';

        $categories     = categories()->fetch( -1 );
        $mycategories   = array_intersect_key( $categories, me()->getSurveys()->selectDistinctCategory()->fetch( -1 ) );
        $statuses       = [ 5 => t( 'Finished' ), 4 => t( 'Live' ), 3 => t( 'Paused' ), 2 => t( 'Waiting approval' ), 1 => t( 'Require setup' ), 0 => t( 'Rejected' ) ];
        $mystatuses     = array_intersect_key( $statuses, me()->getSurveys()->selectDistinctStatus()->fetch( -1 ) );

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'View' ), 'before_label' => '<i class="fas fa-user-check"></i>', 'fields' => [
                'view' => [ 'type' => 'radio', 'options' => ( [ 'all' => t( 'All' ), 'p' => t( 'My surveys' ) ] + array_map( function( $v ) {
                    return esc_html( $v->name );
                }, me()->myTeams()->fetch( -1 ) ) ), 'value' => 'all' ]
            ], 'grouped' => false ] ], 'grouped' => false ],
            'category'  => [ 'type' => 'select', 'after_label' => '<i class="fas fa-tag"></i>', 'options' => array_map( function( $v ) { return esc_html( $v->name ); }, $mycategories ), 'value' => '', 'placeholder' => t( 'Category' ) ],
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-on"></i>', 'options' => $mystatuses, 'value' => '', 'placeholder' => t( 'Status' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ), 'lc' => t( 'Last response &darr;' ), 'lc_desc' => t( 'Last response &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form options_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $surveys = new \admin\markup\table( [
            '<div class="w80"></div>'   => 'wa', 
            t( 'Name' )                 => 'tl w150p', 
            '<div></div>'               => 'tc', 
            t( 'Category' )             => '', 
            t( 'Budget' )               => '', 
            ''                          => '' 
        ] );
        
        $surveys
        ->title( t( 'My surveys' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa df sav' )
        ->add( '{name}', 'tl w150p' )
        ->add( '{status}', 'tc' )
        ->add( '{category}' )
        ->add( '{budget}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $surveys->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "surveys_surveyor",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function surveys_moderator() {
        $markup = '<div class="filters">';

        $categories     = categories()->fetch( -1 );
        $statuses       = [ 5 => t( 'Finished' ), 4 => t( 'Live' ), 3 => t( 'Paused' ), 2 => t( 'Waiting approval' ), 1 => t( 'Require setup' ), 0 => t( 'Rejected' ), -1 => t( 'Pending deletion' ) ];

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form surveys_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $surveys = new \admin\markup\table( [
            '<div class="w80"></div>'   => 'wa',
            t( 'Name' )                 => 'tl w200p',
            '<div></div>'               => 'tc',
            t( 'User' )                 => '',
            t( 'Category' )             => '',
            ''                          => ''
        ] );
        
        $surveys
        ->title( t( 'Surveys' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa df sav', 'image_bg' )
        ->add( '{name}', 'tl w200p' )
        ->add( '{status}', 'tc' )
        ->add( '{user}' )
        ->add( '{category}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $surveys->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "surveys_moderator",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function surveys_admin() {
        return $this->surveys_owner();
    }

    private function surveys_owner() {
        $markup = '<div class="filters">';

        $categories     = categories()->fetch( -1 );
        $statuses       = [ 5 => t( 'Finished' ), 4 => t( 'Live' ), 3 => t( 'Paused' ), 2 => t( 'Waiting approval' ), 1 => t( 'Require setup' ), 0 => t( 'Rejected' ), -1 => t( 'Pending deletion' ) ];

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'category'  => [ 'type' => 'select', 'after_label' => '<i class="fas fa-tag"></i>', 'options' => array_map( function( $v ) { return esc_html( $v->name ); }, $categories ), 'value' => '', 'placeholder' => t( 'Category' ) ],
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-on"></i>', 'options' => $statuses, 'value' => '', 'placeholder' => t( 'Status' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form surveys_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $surveys = new \admin\markup\table( [
            '<div class="w80"></div>'   => 'wa', 
            t( 'Name' )                 => 'tl w200p',
            '<div></div>'               => 'tc', 
            t( 'User' )                 => '', 
            t( 'Category' )             => '', 
            t( 'Budget' )               => '',
            t( 'Not billed' )           => '',
            ''                          => '' 
        ] );
        
        $surveys
        ->title( t( 'Surveys' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa df sav', 'image_bg' )
        ->add( '{name}', 'tl w200p' )
        ->add( '{status}', 'tc' )
        ->add( '{user}' )
        ->add( '{category}' )
        ->add( '{budget}' )
        ->add( '{spent}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $surveys->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "surveys_owner",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    public function markup() {
        return $this->markup;
    }

    public function callbacks() {
        return $this->callbacks;
    }

    public function result( array $result ) {
        return $result + $this->result;
    }
    
}