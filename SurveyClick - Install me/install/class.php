<?php

class InitInstall {

    const PHPMin        = '7.3';
    const MySQLMin      = '8.0';
    const MariaDBMin    = '10.4';
    private $cannext    = true;
    private $requirements;
    private $installed;
    private $lastError;

    private function details() {
        if( !empty( $this->requirements ) ) {
            return ;
        }

        if( file_exists( 'db/sql.sql' ) )
            $this->requirements[] = [ 'SQL file <strong>in place</strong>', true, true ];
        else
            $this->requirements[] = [ 'SQL file <strong>missing</strong>', false, false ];

        if( file_exists( '../.htaccess' ) )
            $this->requirements[] = [ '.htaccess file <strong>in place</strong>', true, true ];
        else
            $this->requirements[] = [ '.htaccess file <strong>missing</strong>', false, false ];

        if( !is_writable( '../config.php' ) )
            $this->requirements[] = [ 'config.php is <strong>not writable</strong>', false, false ];

        $not_readable = [];
        if( !is_readable( '../' . MAIL_TMPS_DIR ) )
            $not_readable[] = MAIL_TMPS_DIR;
        if( !is_readable( '../' . THEMES_DIR ) )
            $not_readable[] = THEMES_DIR;
        if( !is_readable( '../' . TEMP_DIR ) )
            $not_readable[] = TEMP_DIR;
        if( !is_readable( '../' . PLUGINS_DIR ) )
            $not_readable[] = PLUGINS_DIR;
        if( !is_readable( '../' . UPLOADS_DIR ) )
            $not_readable[] = UPLOADS_DIR;

            
        if( !empty( $not_readable ) )
            $this->requirements[] = [ 'The following directories are not readable: <strong>' . implode( ', ', $not_readable ) . '</strong>', false, true ];

        if( version_compare( PHP_VERSION, self::PHPMin ) >= 0 )
            $this->requirements[] = [ 'PHP version: <strong>' . phpversion() . '</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP version: <strong>' . phpversion() . '</strong>. Requires >= ' . PHP_VERSION_ID . ' - ' . self::PHPMin, false, false ];

        if( extension_loaded( 'mysqli' ) )
            $this->requirements[] = [ 'PHP: MySQLi extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: MySQLi extension <strong>not installed</strong>', false, false ];

        if( extension_loaded( 'json' ) )
            $this->requirements[] = [ 'PHP: json extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: json extension <strong>not installed</strong>', false, false ];

        if( extension_loaded( 'zip' ) )
            $this->requirements[] = [ 'PHP: zip extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: zip extension <strong>not installed</strong>. You will not be able to install themes & plugins', false, true ];

        if( extension_loaded( 'mbstring' ) )
            $this->requirements[] = [ 'PHP: mbstring extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: mbstring extension <strong>not installed</strong>. Your website will not properly handle UTF-8 texts', false, true ];

        if( extension_loaded( 'gettext' ) )
            $this->requirements[] = [ 'PHP: gettext extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: gettext extension <strong>not installed</strong>. You will face transation related issues', false, true ];

        if( extension_loaded( 'intl' ) )
            $this->requirements[] = [ 'PHP: intl extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: intl extension <strong>not installed</strong>. You will face transation related issues and more', false, true ];

        if( extension_loaded( 'gd' ) )
            $this->requirements[] = [ 'PHP: gd extension <strong>installed</strong>', true, true ];
        else 
            $this->requirements[] = [ 'PHP: gd extension <strong>not installed</strong>. You will not be able to upload images properly', false, true ];
    }

    public function getDetails() {
        $this->details();

        $markup = '<div class="details-list">';
        foreach( $this->requirements as $detail ) {
            $markup .= '<li' . ( $detail[1] ? '><i class="fas fa-check"></i> ' . $detail[0] : ' class="e"><i class="fas fa-times"></i> ' . $detail[0] ) . '</li>';
            if( isset( $detail[2] ) && !$detail[2] && $this->cannext ) $this->cannext = false;
        }
        $markup .= '</ul>';

        return $markup;
    }

    public function getCurrencies() {
        return [ 'USD' => '$', 'EUR' => '€', 'AED' => 'د.إ', 'AUD' => '$', 'BGN' => 'лв', 'BRL' => 'R$', 'CAD' => '$', 'CHF' => 'Fr', 'CZK' => 'Kč', 'CNY' => '¥', 'HKD' => '$', 'HUF' => 'Ft', 'ILS' => '₪', 'JPY' => '¥', 'MYR' => 'RM', 'MXN' => '$', 'TWD' => 'NT$', 'NZD' => '$', 'NOK' => 'kr.', 'PLN' => 'zł', 'GBP' => '£', 'RUB' => '₽', 'SGD' => '$', 'SEK' => 'kr.', 'THB' => '฿' ];
    }

    public function canGoNext() {
        return $this->cannext;
    }

    public function install( &$step, $data ) {
        // check step 1
        $this->details();
        foreach( $this->requirements as $detail ) {
            if( isset( $detail[2] ) && !$detail[2] ) {
                $step = 1;
                return ;
            }
        }

        // check step 2
        if( !isset( $data['db']['password']  ) )
        $data['db']['password'] = '';

        if( !isset( $data['db']['host'] ) || !isset( $data['db']['user'] ) || !isset( $data['db']['name'] ) ) {
            $step = 2;
            $this->lastError = [
                'message'   => "Unable to connect to database, please update the correct credentials and try again. Error code: 0"
            ];
            return ;
        }

        try {
            $db = @new mysqli( $data['db']['host'], $data['db']['user'], $data['db']['password'], $data['db']['name'] );

            // For older PHP versions
            if( $db->connect_errno ) {
                $step = 2;
                $this->lastError = [
                    'message'   => "Unable to connect to database, please update the correct credentials and try again. Error code: 1"
                ];
                return ;
            }

            if( substr_count( $db->get_server_info(), 'MariaDB' ) ) {
                preg_match( '/([0-9\.]+)-MariaDB/', $db->get_server_info(), $version );
                if( version_compare( $version[1], self::MariaDBMin ) < 0 ) {
                    $step = 2;
                    $this->lastError = [
                        'message'   => "Sorry, but you need at least MariaDB " . self::MariaDBMin . " to install the script"
                    ];
                    return ;
                }
            } else if( version_compare( $db->server_info, self::MySQLMin ) < 0 ) {
                $step = 2;
                $this->lastError = [
                    'message'   => "Sorry, but you need at least MySQL " . self::MySQLMin . " to install the script"
                ];
                return ;
            }
        }

        catch( \Exception $e ) {
            $step = 2;
            $this->lastError = [
                'message'   => 'MySQL error: ' . $e->getMessage()
            ];
            return ;
        }

        // check step 3
        $currencies = $this->getCurrencies();
        if( !isset( $data['server']['currency'] ) || !isset( $currencies[$data['server']['currency']] ) ) {
            $step = 3;
            $this->lastError = [
                'message'   => "Invalid currency"
            ];
            return ;
        }

        if( !isset( $data['server']['tz'] ) || !in_array( $data['server']['tz'], timezone_identifiers_list() ) ) {
            $step = 3;
            $this->lastError = [
                'message'   => "Invalid timezone"
            ];
            return ;
        }

        if( !isset( $data['server']['language'] ) || !in_array( $data['server']['tz'], timezone_identifiers_list() ) ) {
            $step = 3;
            $this->lastError = [
                'message'   => "Invalid language"
            ];
            return ;
        }
        
        $replacements = [
            'DB_NAME'       => $data['db']['name'],
            'DB_USER'       => $data['db']['user'],
            'DB_PASSWORD'   => $data['db']['password'],
            'DB_HOST'       => $data['db']['host'],
            'DB_TABLE_PREFIX'   => trim( $data['db']['prefix'] ),
            'PAYMENT_CURRENCY'  => $data['server']['currency'],
            'PAYMENT_SYMBOL'    => $currencies[$data['server']['currency']],
            'MAX_SIZE_FILE_TYPE'=> $data['server']['max_file_size'] ?? 5,
            'DEFAULT_TIMEZONE'  => $data['server']['tz'],
            'DEFAULT_LANGUAGE'  => $data['server']['language']
        ];

        $dirname        = str_replace( '\\', '/', dirname( __DIR__ ) );
        $accounts_url   = empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' || $_SERVER['SERVER_PORT'] != 443 ? 'http://' : 'https://';
        $accounts_url   .= $_SERVER['SERVER_NAME'];
        if( $_SERVER['SERVER_PORT'] != 80 )
        $accounts_url   .= ':' . $_SERVER['SERVER_PORT'];
        $accounts_url   .= dirname( dirname( $_SERVER['PHP_SELF'] ) );

        if( !empty( $data['server']['accounts_url'] ) )
        $replacements['ADMIN_LOC']  = trim( $data['server']['accounts_url'] );
        
        // dump sql file
        $sqlFile    = file_get_contents( 'db/sql.sql' );
        $sqlFile    = str_replace( [ '{{prefix}}', '{{account_URL}}' ], [ $data['db']['prefix'], $accounts_url ], $sqlFile );

        try {
            $imported = @$db->multi_query( $sqlFile );
            if( !$imported ) {
                $step   = 'error';
                $this->lastError = [
                    'message'   => "SQL file can't be uploaded"
                ];
                return ;
            }
        }

        catch( \Exception $e ) {
            $step   = 'error';
            $this->lastError = [
                'message'   => "MySQL: " . $e->getMessage()
            ];
            return ;
        }

        while( $db->next_result() ) {}

        $query  = 'INSERT INTO ' . $data['db']['prefix'] . 'users';
        $query  .= ' (name, email, password, perm, surveyor) VALUES (?, ?, MD5(?), 9, 1)';
        $stmt   = $db->stmt_init();
        $stmt   ->prepare( $query );
        $stmt   ->bind_param( 'sss', $data['owner']['user'], $data['owner']['email'], $data['owner']['password'] );
        $stmt   ->execute();
        $stmt   ->close();
        $db     ->close();

        // update config file
        $configFile = file_get_contents( '../config.php' );

        foreach( $replacements as $key => $value )
        $configFile = preg_replace( '/(define)\(\s?([\'"])(' . $key . ')[\'"]\s?\,\s?([\'"])?(.*?)[\'"]?\s?\)/i', '$1( $2$3$2, ' . ( is_numeric( $value ) ? $value : '$4' . $value . '$4' ) . ' )', $configFile );

        file_put_contents( '../config.php', $configFile );

        // no errors
        $step = 'installed';

        return ;
    }

    public function message() {
        if( $this->lastError ) {
            return '<div class="msg ' . ( $this->lastError['type'] ?? 'error' ) . '">' . $this->lastError['message'] . '</div>';
        }
    }

    public function clear() {
        if( $this->installed ) {

        }
    }

    public function saveInputs( array $inputs = NULL, string $current = NULL ) {
        $inputs = $inputs ?? $_POST;
        foreach( $inputs as $key => $value ) {
            if( is_array( $value ) )  $this->saveInputs( $value, ( $current ? $current . '[' . $key . ']' : $key ) );
            else
            echo '<input type="hidden" name="' . ( $current ? $current . '[' . $key . ']' : $key ) . '" value="' . $value . '" />' . "\n";
        }
    }

}