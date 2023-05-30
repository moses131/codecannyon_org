<?php

namespace user;

class user_actions extends \util\db {

    private $user;
    private $user_obj;

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
    }

    public function setUser( int $user ) {
        $users = users( $user );
        if( $users->getObject() ) {
            $this->user         = $users->getId();
            $this->user_obj     = $users;
        }
        return $this;
    }

    public function logout() {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-logout', true, $this->user ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'sessions' );
            $query .= ' WHERE user = ? AND session = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $this->user, $_COOKIE['user_session'] );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                global $me;
                $me = NULL;
                setcookie( 'user_session', '', [
                    'expires'   => time(),
                    'path'      => strstr( str_replace( str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] ), '', str_replace( '\\', '/', __DIR__ ) ), INCLUDES_DIR, true ),
                    'secure'    => true,
                    'httponly'  => true,
                    'samesite'  => 'Strict'
                ] );

                actions()->do_action( 'after-logout', $this->user );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deposit( float $amount, array $details = [], int $status = 2 ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-deposit', true, $this->user, $amount, $details, $status ) ) { return ; }
        else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'transactions' );
            $query .= ' (user, type, amount, details, status, transactionId) VALUES (?, 1, ?, ?, ?, ?)';

            $details2 = cms_json_encode( $details );

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'idsis', $this->user, $amount, $details2, $status, $details['id'] );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-deposit', $this->user_obj, $amount, $details, $status );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function approveOrder( int $id, string $token, array $details = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !filters()->do_filter( 'custom-error-appove-order', true, $this->user, $id, $token, $details ) ) { return ; }
        else {
            $query = 'SELECT plan, addm FROM ';
            $query .= $this->table( 'subscriptions' ); 
            $query .= ' WHERE id = ?';
    
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $stmt->execute();
            $stmt->bind_result( $plan, $months );
            $stmt->fetch();

            $query = 'UPDATE ';
            $query .= $this->table( 'subscriptions' );
            $query .= ' SET expiration = DATE_ADD(NOW(), INTERVAL +addm MONTH), addm = NULL, paid = 1, token = NULL WHERE id = ? AND token = ?';
            
            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $id, $token );
        
            if( $stmt->execute() ) {
                actions()->do_action( 'after:upgrade', 'payment', 'upgrade', $this->user_obj, $months, $plan, $details );
                
                $query = 'INSERT INTO ';
                $query .= $this->table( 'transactions' );
                $query .= ' (user, type, amount, details, status, transactionId) VALUES (?, 5, ?, ?, 2, ?)';
    
                $details2 = cms_json_encode( $details );
    
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'idss', $this->user, $details['total'], $details2, $details['id'] );
                $stmt->execute();
                $stmt->close();

                return true;
            }

            $stmt->close();
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function extendOrder( int $id, string $token, array $details = [] ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-extend-order', true, $this->user, $id, $token, $details ) ) { return ; }
        else {
            $query = 'SELECT plan, addm FROM ';
            $query .= $this->table( 'subscriptions' ); 
            $query .= ' WHERE id = ?';
    
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $stmt->execute();
            $stmt->bind_result( $plan, $months );
            $stmt->fetch();

            $query = 'UPDATE ';
            $query .= $this->table( 'subscriptions' );
            $query .= ' SET expiration = DATE_ADD(expiration, INTERVAL +addm MONTH), rcount = rcount + addm, addm = NULL, token = NULL, info = NULL WHERE id = ? AND token = ?';
            
            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $id, $token );
        
            if( $stmt->execute() ) {
                actions()->do_action( 'after:upgrade', 'payment', 'extend', $this->user_obj, $months, $plan, $details );

                $query = 'INSERT INTO ';
                $query .= $this->table( 'transactions' );
                $query .= ' (user, type, amount, details, status, transactionId) VALUES (?, 5, ?, ?, 2, ?)';
    
                $details2 = cms_json_encode( $details );
    
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'idss', $this->user, $details['total'], $details2, $details['id'] );
                $stmt->execute();
                $stmt->close();

                return true;
            }

            $stmt->close();
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function updateAlert( int $alert, int $type ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-update-alert', true, $this->user, $alert, $type ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'alerts' );
            $query .= ' SET open = ? WHERE id = ? AND user = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iii', $type, $alert, $this->user );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function saveOption( string $name, string $value ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-save-option', true, $this->user, $name, $value ) ) { return ; }
        else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'user_options' );
            $query .= ' (user, name, content) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content)';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iss', $this->user, $name, $value );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-saved-option', $this->user_obj, $name, $value );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deleteOption( string $name ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-option', true, $this->user, $name ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'user_options' );
            $query .= ' WHERE user = ? AND name = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'is', $this->user, $name );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-deleted-option', $this->user_obj, $name );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function addIntent( int $type = 1, string $text = '', int $status = 0 ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-add-intent', true, $this->user, $type, $text, $status ) ) { return ; }
        else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'user_intents' );
            $query .= ' (user, type, text, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE text = VALUES(text), status = VALUES(status)';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iisi', $this->user, $type, $text, $status );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-intent-added', $this->user_obj, $type, $text, $status );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deleteIntent( int $type ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-intent', true, $this->user, $type ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'user_intents' );
            $query .= ' WHERE user = ? AND type = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->user, $type );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-intent-deleted', $this->user_obj, $type );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function addFavorite( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-add-favorite', true, $this->user, $id ) ) { return ; }
        else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'favorites' );
            $query .= ' (user, survey) VALUES (?, ?)';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->user, $id );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {

                actions()->do_action( 'after-added-favorite', $this->user_obj, $id );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deleteFavorite( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-favorite', true, $this->user, $id ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'favorites' );
            $query .= ' WHERE user = ? AND survey = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->user, $id );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-delete-favorite', $this->user_obj, $id );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function addSaved( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-add-saved', true, $this->user, $id ) ) { return ; }
        else {
            $collector  = paidSurveys( $id );
            $collector  ->setUserOptions();

            if( !$collector->getObject() )
            throw new \Exception( t( 'Unexpected' ) );

            $query = 'INSERT INTO ';
            $query .= $this->table( 'saved' );
            $query .= ' (user, survey, collector) VALUES (?, ?, ?)';

            $survey         = $collector->getSurveyObject();
            $collector_id   = $collector->getId();
            $survey_id      = $survey->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iii', $this->user, $survey_id, $collector_id );
            $e = $stmt->execute();
            $stmt->close();

            if( $e ) {
                actions()->do_action( 'after-added-saved', $this->user_obj, $id );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deleteSaved( int $id ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-saved', true, $this->user, $id ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'saved' );
            $query .= ' WHERE user = ? AND survey = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->user, $id );
            $e = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-delete-saved', $this->user_obj, $id );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function checkVerificationCode( bool $insert = false, int $type = 1 ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $query = 'SELECT code, checks FROM ';
        $query .= $this->table( 'verif_codes' ); 
        $query .= ' WHERE user = ? AND type = ? AND expiration > NOW()';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $type );
        $stmt->execute();
        $stmt->bind_result( $code, $checks );
        $stmt->fetch();
        $stmt->close();

        if( $code === NULL ) {
            if( $insert )
            return $this->insertVerificationCode( $type );
            return false;
        }

        return [ 'code' => $code, 'checks' => $checks ];
    }

    public function insertVerificationCode( int $type = 1 ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        $stmt = $this->db->stmt_init();

        $query = 'SELECT code, checks, (UNIX_TIMESTAMP(expiration) - UNIX_TIMESTAMP()) as expiration FROM ';
        $query .= $this->table( 'verif_codes' );
        $query .= ' WHERE user = ? AND type = ?';

        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $type );
        $stmt->execute();
        $stmt->bind_result( $code, $checks, $expiration );
        $stmt->fetch();

        if( $expiration > ( 3 * 60 ) )
        return [ 'code' => $code, 'checks' => $checks ];

        $query = 'INSERT INTO ';
        $query .= $this->table( 'verif_codes' );
        $query .= ' (user, type, code, expiration) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL +? MINUTE))';
        $query .= ' ON DUPLICATE KEY UPDATE code = VALUES(code), checks = 0, expiration = VALUES(expiration)';

        $code   = rand( 100000, 999999 );
        $tsvlt  = filters()->do_filter( 'verification-code-life-time', 30, $type );

        $stmt->prepare( $query );
        $stmt->bind_param( 'iiii', $this->user, $type, $code, $tsvlt );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after:user:verification-code-inserted', $this->user_obj, $code, $type );
            return [ 'code' => $code, 'checks' => 0 ];
        }

        return false;
    }

    public function verifyCode( array $data, int $type = 1 ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !isset( $data['code'] ) ) {
            throw new \Exception( t( 'Invalid code' ) );
        } else if( !filters()->do_filter( 'custom-error-verification-code', true, $this->user, $data, $type ) ) { return ; }

        if( ( $exists = $this->checkVerificationCode( false, $type ) ) ) {

            $limit = filters()->do_filter( 'verification-code-checks-limit', 5, $type );

            if( $exists['checks'] >= $limit ) {
                throw new \Exception( t( 'Looks like you inserted the wrong verification code for multiple times, please try again later.' ) );
            } else if( $exists['code'] != $data['code'] ) {
                $query = 'UPDATE ';
                $query .= $this->table( 'verif_codes' );
                $query .= ' SET checks = checks + 1 WHERE user = ? AND type = ?';
                
                $stmt = $this->db->stmt_init();
                $stmt->prepare( $query );
                $stmt->bind_param( 'ii', $this->user, $type );
                $stmt->execute();
                $stmt->close();

                throw new \Exception( t( 'Invalid code' ) );
            }

            $query = 'DELETE FROM ';
            $query .= $this->table( 'verif_codes' );
            $query .= ' WHERE user = ? AND type = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $this->user, $type );
            $stmt->execute();
            $stmt->close();

            return true;

        } else if( $this->insertVerificationCode( $type ) ) {
            throw new \Exception( t( 'Your verification code has expired, but we sent another one right now. Please check your inbox.' ) );
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function confirmSession() {
        if( !$this->user )
        return false;

        $query = 'UPDATE ';
        $query .= $this->table( 'sessions' );
        $query .= ' SET conf = 1 WHERE id = ?';

        $ses_id = $this->user_obj->getSessionId();

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $ses_id );
        $e = $stmt->execute();
        $stmt->close();

        return $e;
    }

    public function confirmEmail() {
        if( !$this->user )
        return false;

        $query = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET valid = 1 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $this->user );
        $e = $stmt->execute();
        $stmt->close();

        return $e;
    }

    public function applyVoucher( int $id, float $amount ) {
        $query = 'SELECT apply_voucher(?, ?, ?)';
        
        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'iid', $this->user, $id, $amount );
        $stmt->execute();
        $stmt->bind_result( $applied );
        $stmt->fetch();
        $stmt->close();

        if( $applied ) {
            actions()->do_action( 'voucher-applied', $this->user_obj, $id, $amount );
            return true;
        }

        return false;
    }

    public function approveTeamInvitation( int $team_id ) {
        $query = 'UPDATE ';
        $query .= $this->table( 'teams_members' );
        $query .= ' SET approved = 1 WHERE user = ? AND team = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $team_id );
        $e = $stmt->execute();
        echo $stmt->error;
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-team-invitation-approved', $this->user_obj, $team_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function rejectTeamInvitation( int $team_id ) {
        $query = 'DELETE FROM ';
        $query .= $this->table( 'teams_members' );
        $query .= ' WHERE user = ? AND team = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $this->user, $team_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-team-invitation-reject', $this->user_obj, $team_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function changeTeam( int $team_id ) {
        $query = 'UPDATE ';
        $query .= $this->table( 'users' );
        $query .= ' SET team = ? WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'ii', $team_id, $this->user );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-team-changed', $this->user_obj, $team_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function approveResponse( int $response_id ) {
        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET status = 3 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $response_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-response-approved', $this->user_obj, $response_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function rejectResponse( int $response_id ) {
        $query = 'UPDATE ';
        $query .= $this->table( 'results' );
        $query .= ' SET status = 0 WHERE id = ?';

        $stmt = $this->db->stmt_init();
        $stmt->prepare( $query );
        $stmt->bind_param( 'i', $response_id );
        $e = $stmt->execute();
        $stmt->close();

        if( $e ) {
            actions()->do_action( 'after-response-approved', $this->user_obj, $response_id );
            return true;
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function deleteFiles( array $files ) {
        foreach( $files as $file ) {
            if( is_array( $file ) ) {
                $this->deleteFiles( $file );
            } else {
                unlink( implode( '/', [ DIR, $file ] ) );
            }
        }
    }

    public function delete_category( object $category ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-delete-category', true, $this->user, $category ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'categories' );
            $query .= ' WHERE id = ? OR parent = ?';

            $id = $category->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'ii', $id, $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-category-deleted', $category );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_page( object $page ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-page', true, $this->user, $page ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'pages' );
            $query .= ' WHERE id = ?';

            $id = $page->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-page-deleted', $page );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_voucher( object $voucher ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-voucher', true, $this->user, $voucher ) ) { return ; }
        else {
            $query = 'DELETE FROM v, uv USING ';
            $query .= $this->table( 'vouchers' );
            $query .= ' AS v LEFT JOIN ';
            $query .= $this->table( 'user_vouchers' );
            $query .= ' AS uv ON uv.voucher_id = v.id ';
            $query .= ' WHERE v.id = ?';

            $id = $voucher->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-voucher-deleted', $voucher );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_country( object $country ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-country', true, $this->user, $country ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'countries' );
            $query .= ' WHERE id = ?';

            $id = $country->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-country-deleted', $id );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function hide_plan( object $plan ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-hide-plan', true, $this->user, $plan ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'plans' );
            $query .= ' SET visible = 0 WHERE id = ?';

            $id = $plan->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-hiding-plan', $plan );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function unhide_plan( object $plan ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-hide-plan', true, $this->user, $plan ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'plans' );
            $query .= ' SET visible = 2 WHERE id = ?';

            $id = $plan->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-unhide-plan', $plan );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_plan( object $plan ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-plan', true, $this->user, $plan ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'plans' );
            $query .= ' WHERE id = ?';

            $id = $plan->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-plan-deleted', $plan );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_plan_offer( object $offer ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-delete-plan-offer', true, $this->user, $offer ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'plan_offers' );
            $query .= ' WHERE id = ?';

            $id = $offer->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-plan-offer-deleted', $offer );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function delete_alert( object $alert ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) ); 
       
        if( $alert->getUserId() != $this->user || !$this->user_obj->isOwner() )
        throw new \Exception( t( 'Unexpected' ) );
        
        if( !filters()->do_filter( 'custom-error-delete-alert', true, $this->user, $alert ) ) { return ; }
        else {
            $query = 'DELETE FROM ';
            $query .= $this->table( 'alerts' );
            $query .= ' WHERE id = ?';

            $id = $alert->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-alert-deleted', $alert );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function approve_withdraw( object $transaction ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !$this->user_obj->isAdmin() )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-approve-withdraw', true, $this->user, $transaction ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'transactions' );
            $query .= ' SET status = 2 WHERE id = ? AND type = 4';

            $id = $transaction->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-withdraw-approve', $transaction );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function cancel_withdraw( object $transaction ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );

        if( !( $transaction->getUserId() == $this->user || $this->user_obj->isAdmin() ) )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-cancel-withdraw', true, $this->user, $transaction ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'transactions' );
            $query .= ' SET status = 0 WHERE id = ? AND type = 4';

            $id = $transaction->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-withdraw-canceled', $transaction );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function save_survey_report( object $report ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
       
        if( $report->getUserId() != $this->user )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-save-survey-report', true, $this->user, $report ) ) { return ; }
        else {
            $query = 'UPDATE ';
            $query .= $this->table( 'saved_reports' );
            $query .= ' SET temp_pos = NULL WHERE id = ?';

            $id = $report->getId();

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'i', $id );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-survey-report-saved', $report );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function add_chat_message( string $message, int $team ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
        
        if( $this->user_obj->getTeamId() != $team )
        throw new \Exception( t( 'Unexpected' ) );

        if( !filters()->do_filter( 'custom-error-add-chat-message', true, $this->user, $message, $team ) ) { return ; }
        else {
            $query = 'INSERT INTO ';
            $query .= $this->table( 'teams_chat' );
            $query .= ' (team, user, text) VALUES (?, ?, ?)';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'iis', $team, $this->user, $message );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after-added-chat-message', $this->user_obj, $message, $team );
                return true;
            }                        
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

    public function switch_language( string $lang ) {
        if( !$this->user )
        throw new \Exception( t( 'Not logged' ) );
                
        if( !filters()->do_filter( 'custom-error-switch-language', true, $this->user, $lang ) ) { return ; }
        else {
            $old_language   = $this->user_obj->getLanguageId(); 
            $languages      = getLanguages();

            if( $old_language == $lang )
            return true;

            if( !isset( $languages[$lang] ) )
            throw new \Exception( t( 'Unexpected' ) );

            $query = 'UPDATE ';
            $query .= $this->table( 'users' );
            $query .= ' SET lang = ? WHERE id = ?';

            $stmt = $this->db->stmt_init();
            $stmt->prepare( $query );
            $stmt->bind_param( 'si', $lang, $this->user );
            $e  = $stmt->execute();
            $stmt->close();
        
            if( $e ) {
                actions()->do_action( 'after:language-switch', $this->user_obj, $lang, $old_language );
                return true;
            }
        }

        throw new \Exception( t( 'Unexpected' ) );
    }

}