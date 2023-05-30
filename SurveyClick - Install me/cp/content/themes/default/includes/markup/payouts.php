<?php

namespace admin\markup;

class payouts {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        if( is_callable( [ $this, 'payouts_' . me()->viewAs ] ) )
        $this->{ 'payouts_' . me()->viewAs }();
    }

    private function payouts_respondent() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Completed'), 1 => t( 'Pending'), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'date' => t( 'Date &darr;' ), 'date_desc' => t( 'Date &uarr;' )], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="payouts_list" class="form list_form payouts_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $payouts = new \admin\markup\table( [
            t( 'Amount' )   => '',
            t( 'Method' )   => 'tl w200p',
            t( 'Status' )   => '',
            t( 'Date' )     => '',
            ''              => '' 
        ] );
        
        $payouts
        ->title( t( 'Payouts' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{amount}' )
        ->add( '{method}', 'tl w200p' )
        ->add( '{status}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $payouts->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "payouts_respondent",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function payouts_admin() {
        return $this->payouts_owner();
    }

    private function payouts_owner() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Completed'), 1 => t( 'Pending'), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'date' => t( 'Date &darr;' ), 'date_desc' => t( 'Date &uarr;' )], 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="payouts_list" class="form list_form payouts_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $payouts = new \admin\markup\table( [
            t( 'Amount' )   => '',
            t( 'Method' )   => 'tl w200p',
            t( 'Status' )   => '',
            t( 'Date' )     => '',
            ''              => '' 
        ] );
        
        $payouts
        ->title( t( 'Payouts' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{amount}' )
        ->add( '{method}', 'tl w200p' )
        ->add( '{status}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $payouts->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "payouts_owner",
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