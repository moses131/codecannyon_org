<?php

class view_receipt {

    private $receipt;
    private $options;

    function __construct( int $id ) {
        $receipt =  new \query\receipts( $id );
        if( $receipt->getObject() )
        $this->receipt  = $receipt;
        $this->options  = json_decode( get_option( 'invoicing_settings' ), true );
    }

    private function header() {
        $header = "<!DOCTYPE html>\n<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
        $header .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" />\n";
        $header .= "<title>" . t( 'View receipt' ) . "</title>\n";
        $header .= "<meta property=\"og:title\" content=\"" . t( 'View receipt' ) . "\" />\n";
        $header .= "<meta name=\"robots\" content=\"noindex, nofollow\" />\n";
        $header .= "<link href=\"//fonts.googleapis.com/css?family=Quicksand:500,700\" rel=\"stylesheet\">";
        $header .= "<link href=\"" . admin_url( 'export/assets/css/view_invoice.css', true ) . "\" media=\"all\" rel=\"stylesheet\" />\n";
        $header .= "</head>\n";
        $header .= "<body>\n";
        return $header;
    }

    private function footer() {
        return "\n</body>\n</html>";
    }

    public function view() {
        $markup = $this->header();

        $markup .= '
        <div class="container">
            <h2>' . t( 'Receipt' ) . '</h2>
            <div class="row mt">
                <div class="from">
                    <h3>' . t( 'From' ) . '</h3>';
                    if( isset( $this->options['c_name'] ) )
                    $markup .= '<strong>' . esc_html( $this->options['c_name'] ) . '</strong><br />';
                    if( isset( $this->options['c_address'] ) )
                    $markup .= nl2br( esc_html( $this->options['c_address'] ) );
                    $markup .= '
                </div>
                <div class="invoice wa">
                    <ul class="invoice-info">
                        <li>
                            <span>' . t( 'Receipt #' ) . '</span>
                            <span>' . $this->receipt->getNumber() . '</span>
                        </li>
                        <li>
                            <span>' . t( 'Receipt date' ) . '</span>
                            <span>' . custom_time( $this->receipt->getDate(), 2, 'm/d/Y' ) . '</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row mt">
                <div>
                    <h3>' . t( 'Bill to' ) . '</h3>';
                    $billTo = $this->receipt->getBillToJson();
                    if( isset( $billTo['name'] ) ) {
                        $markup .= '<strong>' . esc_html( $billTo['name'] ) . '</strong><br />';
                        unset( $billTo['name'] );
                    }
                    foreach( $billTo as $bt ) {
                        $markup .= nl2br( esc_html( $bt ) ) . '<br />';
                    }
                    $markup .= '
                </div>
            </div>

            <ul class="items mt">
                <li class="fl">
                    <span class="tl">' . t( 'Name' ) . '</span>
                    <span class="tc">' . t( 'Qty' ) . '</span>
                    <span class="tc">' . t( 'Unit price' ) . '</span>
                    <span class="tr">' . t( 'Amount' ) . '</span>
                </li>';
                foreach( $this->receipt->getSummaryJson() as $item ) {
                    $markup .= '
                    <li>
                        <span class="tl">' . esc_html( $item['name'] ) . '</span>
                        <span class="tc">' . esc_html( $item['quantity'] ) . '</span>
                        <span class="tc">' . cms_money_format( (double) $item['up_without_taxes'] ) . '</span>
                        <span class="tr">' . cms_money_format( (double) $item['without_taxes'] ) . '</span>
                    </li>';
                }
            $markup .= '
            </ul>

            <div class="final">
                <ul class="mt">
                    <li>
                        <span>' . t( 'Subtotal' ) . ':</span>
                        <span>' . $this->receipt->getSubtotalF() . '</span>
                    </li>';
                    if( !empty( $this->receipt->getTaxes() ) ) {
                        $markup .= '
                        <li>
                            <span>' . ( !empty( $this->options['tax_label'] ) ? esc_html( $this->options['tax_label'] ) : t( 'TAX' ) ) . ':</span>
                            <span>' . $this->receipt->getTaxesF() . '</span>
                        </li>';
                    }
                    $markup .= '
                    <li class="big">
                        <span>' . t( 'Total' ) . ':</span>
                        <span>' . $this->receipt->getTotalF() . '</span>
                    </li>
                </ul>
            </div>
        </div>';

        $markup .= $this->footer();

        return $markup;
    }

}