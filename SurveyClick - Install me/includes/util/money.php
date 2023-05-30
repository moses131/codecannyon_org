<?php

namespace util;

class money {

    var $amount;
    var $currency;
    const currencies = [
        'AED' => [ 'name' => 'United Arab Emirates Dirham', 'symbol' => 'AED' ],
        'ARS' => [ 'name' => 'Argentine Peso', 'symbol' => 'ARS' ],
        'AUD' => [ 'name' => 'Australian Dollar', 'symbol' => 'AUD$' ],
        'BGN' => [ 'name' => 'Bulgarian Lev', 'symbol' => 'BGN' ],
        'BND' => [ 'name' => 'Brunei Dollar', 'symbol' => 'BND' ],
        'BOB' => [ 'name' => 'Bolivian Boliviano', 'symbol' => 'BOB' ],
        'BRL' => [ 'name' => 'Brazilian Real', 'symbol' => 'BRL' ],
        'CAD' => [ 'name' => 'Canadian Dollar', 'symbol' => 'CAD$' ],
        'CHF' => [ 'name' => 'Swiss Franc', 'symbol' => 'CHF' ],
        'CLP' => [ 'name' => 'Chilean Peso', 'symbol' => 'CLP' ],
        'CNY' => [ 'name' => 'Chinese Yuan Renminbi', 'symbol' => 'CNY' ],
        'COP' => [ 'name' => 'Colombian Peso', 'symbol' => 'COP' ],
        'CZK' => [ 'name' => 'Czech Republic Koruna', 'symbol' => 'CZK' ],
        'DKK' => [ 'name' => 'Danish Krone', 'symbol' => 'DKK' ],
        'EGP' => [ 'name' => 'Egyptian Pound', 'symbol' => 'EGP' ],
        'EUR' => [ 'name' => 'Euro', 'symbol' => '€' ],
        'FJD' => [ 'name' => 'Fijian Dollar', 'symbol' => 'FJD' ],
        'GBP' => [ 'name' => 'British Pound Sterling', 'symbol' => '£' ],
        'HKD' => [ 'name' => 'Hong Kong Dollar', 'symbol' => 'HKD' ],
        'HRK' => [ 'name' => 'Croatian Kuna', 'symbol' => 'HRK' ],
        'HUF' => [ 'name' => 'Hungarian Forint', 'symbol' => 'HUF' ],
        'IDR' => [ 'name' => 'Indonesian Rupiah', 'symbol' => 'IDR' ],
        'ILS' => [ 'name' => 'Israeli New Sheqel', 'symbol' => 'ILS' ],
        'INR' => [ 'name' => 'Indian Rupee', 'symbol' => 'INR' ],
        'JPY' => [ 'name' => 'Japanese Yen', 'symbol' => 'JPY' ],
        'KES' => [ 'name' => 'Kenyan Shilling', 'symbol' => 'KES' ],
        'KRW' => [ 'name' => 'South Korean Won', 'symbol' => 'KRW' ],
        'LTL' => [ 'name' => 'Lithuanian Litas', 'symbol' => 'LTL' ],
        'MAD' => [ 'name' => 'Moroccan Dirham', 'symbol' => 'MAD' ],
        'MXN' => [ 'name' => 'Mexican Peso', 'symbol' => 'MXN' ],
        'MYR' => [ 'name' => 'Malaysian Ringgit', 'symbol' => 'MYR' ],
        'NOK' => [ 'name' => 'Norwegian Krone', 'symbol' => 'NOK' ],
        'NZD' => [ 'name' => 'New Zealand Dollar', 'symbol' => 'NZD' ],
        'PEN' => [ 'name' => 'Peruvian Nuevo Sol', 'symbol' => 'PEN' ],
        'PHP' => [ 'name' => 'Philippine Peso', 'symbol' => 'PHP' ],
        'PKR' => [ 'name' => 'Pakistani Rupee', 'symbol' => 'PKR' ],
        'PLN' => [ 'name' => 'Polish Zloty', 'symbol' => 'PLN' ],
        'RON' => [ 'name' => 'Romanian Leu', 'symbol' => 'RON' ],
        'RSD' => [ 'name' => 'Serbian Dinar', 'symbol' => 'RSD' ],
        'RUB' => [ 'name' => 'Russian Ruble', 'symbol' => 'RUB' ],
        'SAR' => [ 'name' => 'Saudi Riyal', 'symbol' => 'SAR' ],
        'SEK' => [ 'name' => 'Swedish Krona', 'symbol' => 'SEK' ],
        'SGD' => [ 'name' => 'Singapore Dollar', 'symbol' => 'SGD' ],
        'THB' => [ 'name' => 'Thai Baht', 'symbol' => 'THB' ],
        'TRY' => [ 'name' => 'Turkish Lira', 'symbol' => 'TRY' ],
        'TWD' => [ 'name' => 'New Taiwan Dollar', 'symbol' => 'TWD' ],
        'UAH' => [ 'name' => 'Ukrainian Hryvnia', 'symbol' => 'UAH' ],
        'USD' => [ 'name' => 'US Dollar', 'symbol' => '$' ],
        'VEF' => [ 'name' => 'Venezuelan Bolí­var Fuerte', 'symbol' => 'VEF' ],
        'VND' => [ 'name' => 'Vietnamese Dong', 'symbol' => 'VND' ],
        'ZAR' => [ 'name' => 'South African Rand', 'symbol' => 'ZAR' ]
    ];

    function __construct( float $amount, string $currency ) {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function format( int $decimals = 2 ) {
        $country = $GLOBALS['site']->user_country->currentCountry;
        return str_replace( [ '%amount', '%symbol' ], [ number_format( $this->amount, $decimals, $country['mseparator'][0], $country['mseparator'][1] ), self::currencies[$this->currency]['symbol'] ], $country['mformat'] );
    }

}