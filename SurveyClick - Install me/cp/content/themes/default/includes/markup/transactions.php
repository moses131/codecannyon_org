<?php

namespace admin\markup;

class transactions {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        if( is_callable( [ $this, 'transactions_' . me()->viewAs ] ) )
        $this->{ 'transactions_' . me()->viewAs }();
    }

    private function transactions_respondent() {
        $markup = '<div class="filters">';

        $viewo      = [];
        $viewo[4]   = t( 'Withdrawn' );
        $viewo[6]   = t( 'Commission' );

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Completed'), 1 => t( 'Pending'), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ) ],
            'view'      => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'View' ), 'before_label' => '<i class="far fa-eye"></i>', 'fields' => [
                'view' => [ 'type' => 'checkboxes', 'options' => $viewo, 'value' => $viewo ],
            ], 'grouped' => false ] ], 'grouped' => false ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'date' => t( 'Date &darr;' ), 'date_desc' => t( 'Date &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="transactions_list" class="form list_form transactions_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $transactions = new \admin\markup\table( [
            t( 'Amount' )   => '',
            t( 'Type' )     => '',
            t( 'Survey' )   => 'tl w200p',
            t( 'Status' )   => '',
            t( 'Date' )     => '',
            ''              => '' 
        ] );
        
        $transactions
        ->title( t( 'Transactions' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{amount}' )
        ->add( '{type}' )
        ->add( '{survey}', 'tl w200p' )
        ->add( '{status}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $transactions->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "transactions_respondent",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function transactions_surveyor() {
        $markup = '<div class="filters">';

        $viewo      = [];
        $viewo[1]   = t( 'Deposit' );
        $viewo[2]   = t( 'Returned budget' );
        $viewo[3]   = t( 'Survey budget' );
        $viewo[5]   = t( 'Subscription' );
        $viewo[7]   = t( 'Voucher' );

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Completed' ), 1 => t( 'Pending'), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ) ],
            'view'      => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'View' ), 'before_label' => '<i class="far fa-eye"></i>', 'fields' => [
                'view' => [ 'type' => 'checkboxes', 'options' => $viewo, 'value' => $viewo ],
            ], 'grouped' => false ] ], 'grouped' => false ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'date' => t( 'Date &darr;' ), 'date_desc' => t( 'Date &uarr;' )], 'value' => '', 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="transactions_list" class="form list_form transactions_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $transactions = new \admin\markup\table( [
            t( 'Amount' )   => '',
            t( 'Type' )     => '',
            t( 'Survey' )   => 'tl w200p',
            t( 'Status' )   => '',
            t( 'Date' )     => '',
            ''              => '' 
        ] );
        
        $transactions
        ->title( t( 'Transactions' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{amount}' )
        ->add( '{type}' )
        ->add( '{survey}', 'tl w200p' )
        ->add( '{status}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $transactions->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "transactions_surveyor",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
    }

    private function transactions_admin() {
        return $this->transactions_owner();
    }

    private function transactions_owner() {
        $markup = '<div class="filters">';

        $viewo      = [];
        $viewo[1]   = t( 'Deposit' );
        $viewo[2]   = t( 'Returned budget' );
        $viewo[3]   = t( 'Survey budget' );
        $viewo[5]   = t( 'Subscription' );
        $viewo[6]   = t( 'Commission' );
        $viewo[7]   = t( 'Voucher' );
        $viewo[8]   = t( 'Website commission' );

        $form = new \markup\front_end\form_fields( [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Completed' ), 1 => t( 'Pending'), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ) ],
            'view'      => [ 'type' => 'dropdown', 'fields' => [ [ 'label' => t( 'View' ), 'before_label' => '<i class="far fa-eye"></i>', 'fields' => [
                'view' => [ 'type' => 'checkboxes', 'options' => $viewo, 'value' => $viewo ],
            ], 'grouped' => false ] ], 'grouped' => false ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'date' => t( 'Date &darr;' ), 'date_desc' => t( 'Date &uarr;' )], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="transactions_list" class="form list_form transactions_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';

        $markup .= '</div>';

        $transactions = new \admin\markup\table( [
            t( 'Amount' )   => '',
            t( 'Type' )     => '',
            t( 'User' )     => '',
            t( 'Survey' )   => 'tl w200p',
            t( 'Status' )   => '',
            t( 'Date' )     => '',
            ''              => '' 
        ] );
        
        $transactions
        ->title( t( 'Transactions' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{amount}' )
        ->add( '{type}' )
        ->add( '{user}' )
        ->add( '{survey}', 'tl w200p' )
        ->add( '{status}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $transactions->markup( $uqid );

        $this->markup       = $markup;
        $this->callbacks[]  = '{
            "callback": "cms_populate_table",
            "table": "transactions_owner",
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