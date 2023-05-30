<?php

namespace admin\markup;

class invoices {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        if( is_callable( [ $this, 'invoices_' . me()->viewAs ] ) )
        $this->{ 'invoices_' . me()->viewAs }();
    }

    private function invoices_surveyor() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="invoices_list" class="form list_form invoices_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            t( 'Number' )       => '', 
            t( 'Amount' )       => '',
            t( 'Date' )         => '', 
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'Invoices' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{number}', '' )
        ->add( '{amount}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "invoices_surveyor",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function invoices_admin() {
        return $this->invoices_owner();
    }

    private function invoices_owner() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup .= '<form id="invoices_list" class="form list_form invoices_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            t( 'Number' )       => '', 
            t( 'User' )         => '', 
            t( 'Amount' )       => '',
            t( 'Date' )         => '', 
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'Invoices' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{number}', '' )
        ->add( '{user}' )
        ->add( '{amount}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "invoices_owner",
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