<?php

namespace user;

class visitor_form_actions extends \util\db {

    public function login( array $data, int $admin_only = 0 ) {
        $data = filters()->do_filter( 'login-form-sanitize-data', $data );

        if( ( $errors = filters()->do_filter( 'custom-error-login', false ) ) )
        throw new \Exception( $errors );

        $userec = ( $recaptcha_key = get_option( 'recaptcha_key' ) ) && !empty( $recaptcha_key );

        if( $userec ) {
            if( !isset( $data['ca_code'] ) )
            throw new \Exception( t( "Unexpected. Please reload the page and try again." ) );

            $conn = new \util\connector( 'https://www.google.com/recaptcha/api/siteverify?secret=' . esc_html( get_option( 'recaptcha_secret' ) ) . '&response=' . esc_html( $data['ca_code'] ) );

            if( !( $response = $conn->Open() ) || ( $response = json_decode( $response ) ) && ( !empty( $response->success ) || $response->score < 0.5 ) )
            throw new \Exception( t( "Unexpected. Please reload the page and try again." ) );
        }

        $query  = 'SELECT id, twosv FROM ';
        $query .= $this->table( 'users' );
        $query .= ' WHERE (name = "' . $this->dbp( $data['email'] ) . '" OR email = "' . $this->dbp( $data['email'] ) . '") AND password = MD5(?) AND perm >= ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['password'], $admin_only );
        $stmt->execute();
        $stmt->bind_result( $user_id, $twosteps );
        $stmt->fetch();

        if( $user_id !== NULL ) {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'sessions' );
            $query .= ' (user, session, expiration, conf) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 4320 MINUTE), ?)';

            $session = md5( uniqid( rand(), true ) );
            $confirm = !$twosteps;

            $stmt->prepare( $query );
            $stmt->bind_param( 'isi', $user_id, $session, $confirm );
            $stmt->execute();

            setcookie( 'user_session', $session, [
                'expires'   => strtotime( '+90 days' ),
                'path'      => strstr( str_replace( str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] ), '', str_replace( '\\', '/', __DIR__ ) ), INCLUDES_DIR, true )
            ] );

            global $me;
            $me = users( $user_id );
            $me->getObject();

            if( $confirm ) {
                $me->updateLastAction();
            }

            $stmt->close();

            actions()->do_action( 'after:user:login', $me );

            return (object) [ 'session' => $session, 'twosteps' => $twosteps ];
        }

        $stmt->close();

        throw new \Exception( t( 'Invalid credentials' ) );
    }

    public function register( array $data, int $admin_only = 0 ) {
        $data = filters()->do_filter( 'register-form-sanitize-data', $data );

        if( !isset( $data['agreed' ] ) ) {
            throw new \Exception( t( 'You must agree with our terms and conditions in order to register' ) );
        } else if( !isset( $data['username'] ) || !preg_match( '/^[a-zA-Z][a-z0-9-_]{2,19}$/i', $data['username'] ) ) {
            throw new \Exception( t( 'Invalid username. It should start with a letter and contain between 3-20 letters, numbers, underscores or hyphens.' ) );
        } else if( !isset( $data['password'] ) || !isset( $data['password2'] ) || !\util\etc::check_password( $data['password'] ) ) {
            throw new \Exception( t( 'Invalid or weak password. It should contain at least 6 characters.' ) );
        } else if( $data['password'] !== $data['password2']  ) {
            throw new \Exception( t( "Passwords do not match" ) );
        } else if( !isset( $data['country'] ) || !( ( $countries = new \query\countries( $data['country'] ) ) && $countries->getObject() ) ) {
            throw new \Exception( t( 'Unexpected' )  );
        } else if( ( $errors = filters()->do_filter( 'custom-error-register', false ) ) ) {
            throw new \Exception( $errors );
        }

        $userec = ( $recaptcha_key = get_option( 'recaptcha_key' ) ) && !empty( $recaptcha_key );

        if( $userec ) {
            if( !isset( $data['ca_code'] ) )
            throw new \Exception( t( "Unexpected. Please reload the page and try again" ) );

            $conn = new \util\connector( 'https://www.google.com/recaptcha/api/siteverify?secret=' . esc_html( get_option( 'recaptcha_secret' ) ) . '&response=' . esc_html( $data['ca_code'] ) );
            
            if( !( $response = $conn->Open() ) || ( $response = json_decode( $response ) ) && ( !empty( $response->success ) || $response->score < 0.5 ) )
            throw new \Exception( t( "Unexpected. Please reload the page and try again" ) );
        }

        $surveyor   = isset( $data['surveyor'] ) ?: 0;
        $publisher  = 0;

        if( isset( $data['publisher'] ) ) {
            if( (bool) get_option( 'aapprove_pub', false ) ) {
                $publisher = 1;
            } else 
            actions()->add_action( 'after-registration', function( $action, $user ) {
                $user->actions()->addIntent( 1, '' );
            } );
        }

        $country    = $countries->getIso3166();
        $timezones  = $countries->getTimezones();
        $timezone   = key( $timezones );
        $hour_format= $countries->getHourFormat();
        $date_format= $countries->getDateFormat();
        $first_day  = $countries->getFirstDay();

        if( !empty( $_COOKIE['ref'] ) ){
            $inviter= users()
                    ->setId( (int) $_COOKIE['ref'] );
            if( $inviter->getObject() )
            $refId  = $inviter->getId();
        }

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'users' );
        $query .= ' (name, email, password, country, surveyor, lang, f_hour, f_date, tz, fdweek, refid) VALUES (?, ?, MD5(?), ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'sssiiisssii', $data['username'], $data['email'], $data['password'], $country, $surveyor, site()->user_language->current, $hour_format, $date_format, $timezone, $first_day, $refId );
        $e  = $stmt->execute();
        $u  = $stmt->insert_id;

        if( $e ) {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'sessions' );
            $query .= ' (user, session, expiration) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 4320 MINUTE))';

            $session = md5( uniqid( rand(), true ) );

            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $u, $session );
            $stmt->execute();

            $path   = strstr( str_replace( str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] ), '', str_replace( '\\', '/', __DIR__ ) ), INCLUDES_DIR, true );

            setcookie( 'user_session', $session, [
                'expires'   => strtotime( '+90 days' ),
                'path'      => $path,
                // 'secure'    => true,
                // 'httponly'  => true,
                // 'samesite'  => 'Strict'
            ] );

            global $me;
            $me = users( $u );
            $me ->getObject();
            $me ->updateLastAction();
            $me ->actions()
                ->insertVerificationCode( 2 );

            actions()->do_action( 'after:user:register', $me );

            return (object) [ 'session' => $session, 'verif' => $me->isVerified() ];
        }

        $stmt->close();

        throw new \Exception( t( 'Username or email address already exists' ) );
    }

    public function reset_password( array $data ) {
        $data = filters()->do_filter( 'reset-password-form-sanitize-data', $data );

        if( !isset( $data['password'] ) || !isset( $data['password2'] ) || !\util\etc::check_password( $data['password'] ) ) {
            throw new \Exception( 'Invalid or weak password. It should contain at least 6 characters.' );
        } else if( $data['password'] !== $data['password2']  ) {
            throw new \Exception( "Passwords do not match" );
        } else if( !isset( $data['email'] ) || !isset( $data['code'] ) || ( ( $users = users() ) && ( !$users->setIdByEmail( $data['email'] ) || !$users->getObject() ) ) || !$users->actions()->verifyCode( [ 'code' => $data['code'] ], 3 ) ) {
            throw new \Exception( "Invalid code" );
        } else if( ( $errors = filters()->do_filter( 'custom-error-reset-password', false ) ) ) {
            throw new \Exception( $errors );
        }

        $query  = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET password = MD5(?) WHERE id = ?';

        $user_id = $users->getId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'si', $data['password'], $user_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-password-reset', $users, $data );

            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function start_survey( array $data, object $collector, object $survey ) {
        $data = filters()->do_filter( 'start-survey-form-sanitize-data', $data );

        if( !isset( $data['agree'] ) ) {
            throw new \Exception( t( 'To start this survey you must agree to its terms' ) );
        } else if( $collector->getResponse() ) {
            throw new \Exception( t( 'Error' ) );
        } else if( $collector->getType() == 1 ) {
            if( $survey->getBudget() < $collector->getCPA() ) {
                throw new \Exception( t( 'This survey is not available at this time, please try again later' ) );
            } else if( !me() || !$collector->setUserOptions( false ) ) {
                throw new \Exception( t( 'You must be logged in and have a completed profile' ) );
            } else if( !$collector->checkOptions() ) {
                throw new \Exception( t( 'Sorry, you are not eligible to take this survey' ) );
            }
        } else if( $collector->getType() == 0 ) {
            $settings   = $collector->getSetting();
            $trackingId = $data['trackId'] ?? false;

            // check password
            $password   = $settings['password'] ?? false;
            if( $password ) {
                $upass  = $data['password'] ?? false;
                if( $password !== $upass ) {
                    throw new \Exception( t( 'Wrong password' ) );
                }
            }

            // check encryption
            $enckey     = $settings['enckey'] ?? false;
            $allowe     = $settings['allowe'] ?? false;
            if( $enckey ) {
                if( $allowe && empty( $trackingId ) ) {
                    // go through
                } else if( !$trackingId || empty( $data['encKey'] ) || $data['encKey'] != md5( $enckey . $trackingId ) ) {
                    throw new \Exception( t( 'Unexpected' ) );
                }
            }
        }

        if( me() ) $user= me()->getId();
        else $visitor   = \util\etc::userIP();

        $survey_id      = $survey->getId();
        $collector_id   = $collector->getId();
        $commission     = ( $collector->getCPA() > $survey->getRealBudget() ? $survey->getRealBudget() : $collector->getCPA() );
        $commission_b   = $collector->getCPA() - $commission;
        $commission_p   = $collector->getCPA() * ( (double) get_option( 'comm_cpa', 0 ) / 100 );
        $lpoints        = $collector->getLoyaltyPoints();
        $country        = ( me() ? me()->getCountryId() : \util\etc::userCountry( $visitor ) );
        $expiration     = $survey->meta()->get( 'rtime', RESPONSE_TIME_LIMIT );

        $query  = 'INSERT INTO ';
        $query .= $this->table( 'results' );
        $query .= ' (user, visitor, survey, status, commission, commission_bonus, commission_p, lpoints, collector, country, exp) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'isiddddisi', $user, $visitor, $survey_id, $commission, $commission_b, $commission_p, $lpoints, $collector_id, $country, $expiration );
        $e  = $stmt->execute();
        $r  = $stmt->insert_id;

        if( $e ) {
            // Hook
            actions()->do_action( 'after-survey-start', $survey, $collector, $data );

            // Insert tracking id into a temporary variable
            if( !empty( $trackingId ) ) {
                $query = 'INSERT INTO ';
                $query .= $this->table( 'result_vars' );
                $query .= ' (result, type, var, value) VALUES (?, 1, "Track Id", ?)';
                
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'is', $r, $trackingId );
                if( $stmt->execute() ) {
                    // Hook
                    actions()->do_action( 'after-survey-variable-added', $survey, $r, 1, "Track Id", $trackingId );
                }
            }

            $stmt->close();

            return $r;
        }

        $stmt->close();

        throw new \Exception( t( 'Unexpected' ) );
    }

}