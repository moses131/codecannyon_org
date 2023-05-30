<?php

namespace site;

class invoicing extends \util\db {

    private $user;
    private $user_obj;
    private $temp_invoice;
    private $invoice;
    private $invoiceId;
    private $type;
    private $options = [];

    function __construct( $user ) {
        parent::__construct();

        if( gettype( $user ) == 'object' ) {
            $this->user_obj     = $user;
            $this->user         = $this->user_obj->getId();
        } else if( $user == 0 ) {
            $this->user_obj     = me();
            $this->user         = $this->user_obj->getId();
        } else {
            $this->setUser( $user );
        }

        $options        = get_option( 'invoicing_settings', false );
        if( $options )
        $this->options  = json_decode( $options, true );
    }

    public function setUser( int $user ) {
        $users = users( $user );
        if( $users->getObject() ) {
            $this->user         = $users->getId();
            $this->user_obj     = $users;
        }
        return $this;
    }

    public function setInvoice( int $id ) {
        $query  = 'SELECT id, user, summary, bill_to, tax, total FROM ';
        $query  .= $this->table( 'invoices' );

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->execute();
        $stmt   ->bind_result( $id, $user, $summary, $bill_to, $tax, $total );
        $stmt   ->fetch();

        $this->temp_invoice = [
            'id'        => $id,
            'summary'   => json_decode( $summary ),
            'bill_to'   => json_decode( $bill_to ),
            'taxes'     => $tax,
            'total'     => $total
        ];

        return $this;
    }

    public function newInvoice( array $summary ) {
        $temp       = [];
        $total      = 0;
        $subtotal   = 0;
        $taxes      = 0;
        $tax        = $this->options['tax'] ?? 0;

        $temp['summary'] = array_map( function( $v ) use ( $tax, &$total, &$subtotal, &$taxes ) {
            // Name, quantity, total price
            $tax            = ( $v[2] * ( $tax / 100 ) );
            $withoutTaxes   = $v[2] - $tax;
            $withTaxes      = $v[2];
            $taxes          += $tax;
            $total          += $withTaxes;
            $subtotal       += $withoutTaxes;
            return [ 'name' => $this->getName( $v[0] ), 'quantity' => $v[1], 'up_without_taxes' => round( $withoutTaxes / $v[1], 2 ), 'up_with_taxes' => round( $withTaxes / $v[1], 2 ), 'without_taxes' => round( $withoutTaxes, 2 ), 'tax' => round( $tax, 2 ), 'with_taxes' => round( $withTaxes, 2 ) ];
        }, $summary );

        $temp['taxes']      = $taxes;
        $temp['total']      = $total;
        $temp['subtotal']   = $subtotal;
        $this->temp_invoice = $temp;

        return $this;
    }

    public function setType( string $type ) {
        $this->type = $type;
        return $this;
    }

    public function setDate( int $date ) {
        $this->temp_invoice['date'] = date( 'Y-m-d H:i:s', $date );
        return $this;
    }

    public function setInvoiceTo( array $invoice_to ) {
        $this->temp_invoice['bill_to'] = $invoice_to;
        return $this;
    }

    public function setInvoiceToUser() {
        $this->temp_invoice['bill_to'] = [
            'name'      => me()->getFullName(),
            'address'   => me()->getAddress()
        ];
        return $this;
    }

    public function createInvoice( bool $paid = false ) {
        if( !isset( $this->temp_invoice['bill_to'] ) )
        $this->setInvoiceToUser();
        if( !isset( $this->temp_invoice['date'] ) )
        $this->temp_invoice['date'] = time();

        $tax    = $this->options['tax'] ?? '';

        $query  = 'SELECT MAX(id) FROM ';
        $query  .= $this->table( 'invoices' );

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->execute();
        $stmt   ->bind_result( $lastInvoice );
        $stmt   ->fetch();

        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'invoices' );
        $query  .= ' (id, number, user, summary, bill_to, tax, subtotal, total, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?))';

        $newId  = (int) $lastInvoice + 1;
        $summary= cms_json_encode( $this->temp_invoice['summary'] );
        $bill_to= cms_json_encode( $this->temp_invoice['bill_to'] );
        $number = $this->getPrefix( 'invoice' ) . $newId;

        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'isissddds', $newId, $number, $this->user, $summary, $bill_to, $this->temp_invoice['taxes'], $this->temp_invoice['subtotal'], $this->temp_invoice['total'], $this->temp_invoice['date'] );
        $e      = $stmt->execute();
        $id     = $stmt->insert_id;
        $stmt   ->close();

        if( $e )
        $this->temp_invoice['id'] = $id;

        if( $paid ) 
        $this->paidInvoice();

        return $e;
    }

    public function paidInvoice() {
        if( !isset( $this->temp_invoice['id'] ) )
        return false;

        $query  = 'SELECT MAX(id) FROM ';
        $query  .= $this->table( 'receipts' );

        $stmt   = $this->db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->execute();
        $stmt   ->bind_result( $lastReceipt );
        $stmt   ->fetch();

        $query  = 'INSERT INTO ';
        $query  .= $this->table( 'receipts' );
        $query  .= ' (id, invoice, number, user, summary, bill_to, tax, subtotal, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $newId  = (int) $lastReceipt + 1;
        $summary= cms_json_encode( $this->temp_invoice['summary'] );
        $bill_to= cms_json_encode( $this->temp_invoice['bill_to'] );
        $number = $this->getPrefix( 'receipt' ) . $newId;

        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'iisissddd', $newId, $this->temp_invoice['id'], $number, $this->user, $summary, $bill_to, $this->temp_invoice['taxes'], $this->temp_invoice['subtotal'], $this->temp_invoice['total'] );
        $e      = $stmt->execute();
        $id     = $stmt->insert_id;
        $stmt   ->close();

        return $e;
    }

    private function getName( string $name ) {
        return str_replace( [ '%survey_label%' ], [ $this->options['s_label'] ?? t( 'Survey' ) ], $name );
    }

    private function getPrefix( string $useFor ) {
        if( !$this->type ) return '';

        switch( $this->type ) {
            case 'plan':
                switch( $useFor ) {
                    case 'invoice':
                        return ( $this->options['plans_i'] ?? '' );
                    break;

                    case 'receipt':
                        return ( $this->options['plans_r'] ?? '' );
                    break;
                }
            break;

            case 'survey':
                switch( $useFor ) {
                    case 'invoice':
                        return ( $this->options['surveys_i'] ?? '' );
                    break;

                    case 'receipt':
                        return ( $this->options['surveys_r'] ?? '' );
                    break;
                }
            break;

            default:
        }
    }
    
}