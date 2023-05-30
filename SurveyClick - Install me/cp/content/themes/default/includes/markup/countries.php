<?php

namespace admin\markup;

class countries {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        $this->countries();
    }

    private function countries() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ), 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' ) ], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="countries_list" class="form list_form countries_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $af_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';
        $af_markup .= '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'add-country' ] ) ) . '\'>' . t( 'Add country' ) . '</a>';

        $receipts = new \admin\markup\table( [
            ''          => 'w60',
            t( 'Name' ) => '', 
        ] );
        
        $receipts
        ->title( t( 'Countries' ) )
        ->afterTitle( $af_markup )
        ->placeholder( true )
        ->add( '{ico}', 'wa df sav2' )
        ->add( '{name}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $receipts->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "countries",
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