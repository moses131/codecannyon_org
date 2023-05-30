<?php

ajax()->add_call( 'load', function() {
    if( !me() )
    return cms_json_encode( [ 'redirect' => admin_url( 'login' ), 'timeout' => 0 ] );

    $content    = new cms_content;
    return $content->getSectionJson( $_POST['type'] );
});

ajax()->add_call( 'populate-table', function() {
    if( !isset( $_GET['table'] ) ) {
        // that's for sure an error
        return ;
    }

    switch( $_GET['table'] ) {
        case 'surveys_respondent':
            $result['list'] = [];
            $collectors     = paidSurveys();
            $saved          = me()->getSaved();
            $results        = me()->getResults();
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $collectors ->setPage( (int) $_POST['page'] );

            if( isset( $options['category'] ) )
            $collectors ->setCategoryId( $options['category'] );

            if( isset( $options['search'] ) ) {
                $collectors ->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $collectors->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $collectors ->orderBy( $options['orderby'] );

            if( isset( $options['available'] ) ) {
                if( !$collectors->setUserOptions() ) {
                    $result['fallback']     = '<div class="msg mb0 error">' . t( 'Please complete your profile' ) . '</div>';
                    $result['show_popup']   = [ 'action' => 'user-options', 'options' => [ 'action' => 'edit-profile' ] ];     
                }
            } else $collectors->setUserOptions();

            if( !isset( $result['fallback'] ) ) {
                foreach( $collectors->fetch() as $collector ) {
                    $collectors ->setObject( $collector );
                    $survey     = $collectors->getSurveyObject();
                    $response   = $results->isResponsed( $survey->getId() );
                    $category   = '-';

                    if( $response ) {
                        $cpa        = ( $response->commission + $response->commission_bonus - $response->commission_p );
                        $cpaf       = cms_money_format( $cpa );
                        $lpoints    = $response->lpoints;
                    } else {
                        $cpa        = $collectors->getCPA2();
                        $cpaf       = $collectors->getCPAF2();
                        $lpoints    = $collectors->getLoyaltyPoints();
                    }

                    if( ( $categories = $survey->getCategory() ) && $categories->getObject() )
                    $category = esc_html( $categories->getName() );

                    $srv    = [
                        'survey'        => esc_html( $survey->getName() ),
                        'category'      => $category,
                        'commission'    => $cpaf,
                        'stars'         => $lpoints,
                        'date'          => custom_time( $collectors->getDate(), 2 )
                    ];
                    
                    $options = '<ul class="btnset top mla">';

                    if( $saved->isSaved( $survey->getId() ) )
                        $options .= '<li><a href="#" data-ajax="remove-saved" data-data=\'' . cms_json_encode( [ 'id' => $collectors->getId(), 'survey' => $survey->getId() ] ) . '\'><i class="fas fa-calendar-check"></i></a></li>';
                    else
                        $options .= '<li><a href="#" data-ajax="add-saved" data-data=\'' . cms_json_encode( [ 'id' => $collectors->getId(), 'survey' => $survey->getId() ] ) . '\'><i class="fas fa-calendar"></i></a></li>';

                    if( $response ) {
                        switch( $response->status ) {
                            case 3: $options .= '<li class="active disabled"><a href="#"><i class="fas fa-check"></i><span>' . t( 'Respond' ) . '</span></a></li>'; break;
                            case 2: $options .= '<li class="active disabled"><a href="#"><i class="fas fa-hourglass-half"></i><span>' . t( 'Pending' ) . '</span></a></li>'; break;
                            case 1: $options .= '<li><a href="' . esc_url( $collectors->getCollectorPermalink() ) . '" target="_blank"><span>' . t( 'Continue' ) . '</span></a></li>'; break;
                        }
                    } else $options .= '<li><a href="' . esc_url( $collectors->getCollectorPermalink() ) . '" target="_blank"><span>' . t( 'Respond' ) . '</span></a></li>';
                    
                    $options .= '</ul>';

                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                if( !isset( $result['fallback'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No surveys" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $collectors->pagination() )
                $result['pagination']   = $collectors->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'surveys_surveyor':
            $result['list'] = [];
            $teams          = [];
            $user_surveys   = me()->getSurveys();
            $favorites      = me()->getFavorites();
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $user_surveys   ->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) )
            $user_surveys   ->setStatus( $options['status'] );

            if( isset( $options['category'] ) )
            $user_surveys   ->setCategoryId( $options['category'] );

            if( isset( $options['view'] ) ) {
                if( is_numeric( $options['view'] ) )
                    $user_surveys->setTeamId( $options['view'] );
                else if( $options['view'] == 'p' )
                    $user_surveys->setNullTeamId();
            }

            if( isset( $options['search'] ) ) {
                $user_surveys   ->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $user_surveys->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $user_surveys   ->orderBy( $options['orderby'] );
            
            foreach( $user_surveys->fetch() as $user_survey ) {
                $user_surveys   ->setObject( $user_survey );
                $surveys        = $user_surveys->getSurveyObject();

                if( !me()->selectSurvey( $surveys->getId() ) )
                continue;
                
                $isOwner        = !$user_survey->us_team;
                $canEdit        = me()->manageSurvey( 'edit-survey' );
                $category       = '-';

                if( ( $categories = $surveys->getCategory() ) && $categories->getObject() )
                 $category = esc_html( $categories->getName() );

                $avatar = $surveys->getAvatarMarkup( 60 );
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();
                $srv    = [
                    'name'      => '<a href="' . $URL . '" data-to="survey" data-options=\'' . $jsURL . '\'>' . esc_html( $surveys->getName() ) . '</a>',
                    'image'     => ( filter_var( $avatar, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $avatar ). '" alt="" />' : $avatar ),
                    'category'  => $category,
                    'status'    => $surveys->getStatusMarkup(),
                    'budget'    => '-',
                ];

                if( $isOwner ) 
                $srv['budget'] = $surveys->getBudgetF() . ' <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'budget', 'survey' => $surveys->getId() ] ) ) . '\'><i class="fas fa-pencil-alt"></i></a>';

                $options = '<ul class="btnset top mla">';

                if( $surveys->getStatus() == 1 && ( $isOwner || me()->manageSurvey( 'manage-question' ) ) )
                $options .= '<li><a href="#" data-popup="add-survey-step2" data-data=\'' . ( cms_json_encode( [ 'survey' => $surveys->getId() ] ) ) . '\'><i class="fas fa-wrench"></i></a></li>';
                
                $options .= '<li>';

                if( $favorites->isFavorite( $surveys->getId() ) )
                    $options .= '<a href="#" data-ajax="remove-favorite" data-data=\'' . cms_json_encode( [ 'id' => $surveys->getId() ] ) . '\'><i class="fas fa-heart"></i></a>';
                else
                    $options .= '<a href="#" data-ajax="add-favorite" data-data=\'' . cms_json_encode( [ 'id' => $surveys->getId() ] ) . '\'><i class="far fa-heart"></i></a>';

                $options .= '</li>
                <li class="vopts">
                    <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                </li>';

                $options .= '</ul>

                <div class="dd-o">
                    <ul>';
                        if( $isOwner || $canEdit )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>';
                        if( $isOwner || me()->manageSurvey( 'manage-question' ) )
                        $options .= '
                        <li class="df">
                            <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'questions', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Questions' ) . '</a>
                            <a href="#" class="wa" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId() ] ) ) . '\'>
                                <i class="fas fa-plus"></i>
                            </a>
                        </li>';
                        if( $isOwner || me()->manageSurvey( 'manage-collector' ) )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'collectors', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Collectors (links)' ) . '</a></li>';
                        if( $isOwner || me()->manageSurvey( 'view-result' ) )
                        $options .= '<li><a href="' . admin_url( 'survey/' . $surveys->getId() . '/responses' ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'responses', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Responses' ) . '</a></li>';
                        if( $isOwner )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'collaborators', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Collaborators' ) . '</a></li>';
                        $options .= '
                    </ul>
                </div>';
                
                $srv['options']     = $options;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No surveys" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $user_surveys->pagination() )
                $result['pagination']   = $user_surveys->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'surveys_moderator':
            if( !me()->isModerator() ) return ;

            $result['list'] = [];
            $surveys        = surveys();
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $surveys->setPage( (int) $_POST['page'] );
            $surveys->setStatus( 2 );

            if( isset( $options['search'] ) ) {
                $surveys   ->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $surveys->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $surveys->orderBy( $options['orderby'] );

            foreach( $surveys->fetch() as $survey ) {
                $surveys    ->setObject( $survey );
                $category   = '-';

                if( ( $categories = $surveys->getCategory() ) && $categories->getObject() )
                $category = esc_html( $categories->getName() );

                $avatar = $surveys->getAvatarMarkup( 60 );
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();

                $srv        = [
                    'name'      => esc_html( $surveys->getName() ),
                    'image'     => ( filter_var( $avatar, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $avatar ). '" alt="" />' : $avatar ),
                    'user'      => ( ( $user = $surveys->getUser() )->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                    'category'  => $category,
                    'status'    => $surveys->getStatusMarkup()
                ];
                $options = '
                <ul class="btnset top mla">';
                    if( $surveys->getStatus() == 2 )
                    $options .= '<li><a href="#" data-ajax="admin-manage-surveys3" data-data=\'' . cms_json_encode( [ 'action' => 'approve', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                    $options .= '
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">';
                        if( $surveys->getStatus() == 2 )
                        $options .= '<li><a href="#" data-ajax="admin-manage-surveys3" data-data=\'' . cms_json_encode( [ 'action' => 'reject', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Reject' ) . '</a></li>';
                        $options .= '
                        <li><a href="#" data-popup="admin-manage-surveys" data-data=\'' . cms_json_encode( [ 'action' => 'change-status', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Change status' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options']     = $options;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No surveys" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $surveys->pagination() )
                $result['pagination']   = $surveys->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'surveys_owner':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $surveys        = surveys();
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $surveys->setPage( (int) $_POST['page'] );

            if( isset( $options['category'] ) )
            $surveys->setCategoryId( (int) $options['category'] );

            if( isset( $options['status'] ) )
            $surveys->setStatus( (int) $options['status'] );

            if( isset( $options['search'] ) ) {
                $surveys   ->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $surveys->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $surveys->orderBy( $options['orderby'] );

            foreach( $surveys->fetch() as $survey ) {
                $surveys    ->setObject( $survey );
                $category   = '-';

                if( ( $categories = $surveys->getCategory() ) && $categories->getObject() )
                $category = esc_html( $categories->getName() );

                $avatar = $surveys->getAvatarMarkup( 60 );
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();

                $srv        = [
                    'name'      => '<a href="' . $URL . '" data-to="survey" data-options=\'' . $jsURL . '\'>' . esc_html( $surveys->getName() ) . '</a>',
                    'image'     => ( filter_var( $avatar, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $avatar ). '" alt="" />' : $avatar ),
                    'user'      => ( ( $user = $surveys->getUser() )->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                    'category'  => $category,
                    'status'    => $surveys->getStatusMarkup(),
                    'budget'    => $surveys->getBudgetF(),
                    'spent'     => $surveys->getBudgetSpentF()
                ];
                $options = '
                <ul class="btnset top mla">';
                    if( $surveys->getStatus() == 2 )
                    $options .= '<li><a href="#" data-ajax="admin-manage-surveys3" data-data=\'' . cms_json_encode( [ 'action' => 'approve', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                    $options .= '
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">';
                        if( $surveys->getStatus() == 2 )
                        $options .= '<li><a href="#" data-ajax="admin-manage-surveys3" data-data=\'' . cms_json_encode( [ 'action' => 'reject', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Reject' ) . '</a></li>';
                        $options .= '
                        <li><a href="#" data-popup="admin-manage-surveys" data-data=\'' . cms_json_encode( [ 'action' => 'change-status', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Change status' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options']     = $options;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No surveys" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $surveys->pagination() )
                $result['pagination']   = $surveys->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'survey_responses':
            $options = \util\etc::formFilterOptions();

            if( empty( $options['id'] ) )
                return ;

            $surveys = surveys();
            $surveys ->setId( (int) $options['id'] );
            $result['list'] = [];
            if( empty( $options['report'] ) ) {
                if( !empty( $options['advanced'] ) ) {
                    parse_str( $options['advanced'], $opt );
                    print_r($opts);
                    $results    = new \survey\results_advanced;
                    $results    ->setSurveyId( $surveys->getId() );
                    $results    ->filtersFromArray( $opt );
                }
            } else {
                $report     = new \query\survey\saved_reports;
                $report     ->setId( (int) $options['report'] );
                if( $report->getObject() && me()->getId() == $report->getUserId() )
                    $results = $report->getResults();
            }

            if( !isset( $results ) ) {
                $results    = $surveys->getResults();
                $results    ->setCountry();
            }

            if( isset( $_POST['page'] ) )
            $results    ->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) )
            $results    ->setStatus( $options['status'] );

            if( isset( $options['orderby'] ) )
            $results    ->orderBy( $options['orderby'] );

            $hasLabels      = $surveys->getLabels()->count();
            $not_approved   = isset( $options['status'] ) && $options['status'] == 2;

            foreach( $results->fetch() as $r ) {
                $results->setObject( $r );

                // Rejected response
                if( $results->getStatus() == 0 ) {
                    $durat  = $results->getStatusMarkup();
                // Response in progress
                } else if( $results->getStatus() == 1 ) {
                    $durat  = $results->getStatusMarkup( '<div>' . $results->getDuration() . '</div>' );
                // Finished
                } else {
                    $durat  = $results->getStatusMarkup( '<div>' . $results->getDuration() . '</div>' );
                }
                
                $country= $results->getCountryIso3166();
                $labels = '';

                if( $results->getComment() )
                $labels .= '<div class="lop"><i class="fas fa-comment-alt"></i></div>';

                if( $hasLabels ) {
                    $labels .= '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'labels', 'result' => $results->getId() ] ) . '\'><div class="llst">';
                    foreach( $results->getLabels()->fetch( -1 ) as $label ) {
                        if( $label->id ) {
                            $labels .= '<div class="sav" id="lab-' . $label->id . '"><i class="avt-' . esc_html( $label->color ) . '"></i></div>';
                        }
                    }
                    $labels .= '<div class="e"><i></i></div>';
                    $labels .= '</div></a>';
                }

                $srv    = [
                    'name'      => '<div class="dfac"><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $results->getId() ] ) . '\'>#' . $results->getId() . '</a>' . $labels . '</div>',
                    'country'   => ( $country ? '<img src="' . site_url( 'assets/flags/' . $country . '.svg' ) . '"><span>' . $country . '</span>' : '-' ),
                    'duration'  => $durat,
                    'date'      => custom_time( $results->getDate() )[0],
                ];

                $opts = '
                <ul class="btnset top mla">';
                    if( $results->getStatus() == 1 ) {
                        if( $results->isSelfResponse() )
                        $opts .= '<li><a href="' . site_url( 's/' . $results->getId() ) . '" target="_blank">' . t( 'Complete' ) . '</a></li>';
                    } else if( $results->getStatus() == 2 )
                    $opts .= '<li class="approve"><a href="#" data-ajax="user-options3" data-data=\'' . cms_json_encode( [ 'action' => 'approve-response', 'response' => $results->getId(), 'location' => ( $not_approved ? 'table-pending' : 'table' ) ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                    $opts .= '
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $results->getId() ] ) . '\'>' . t( 'View' ) . '</a></li>
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'export', 'result' => $results->getId() ] ) . '\'>' . t( 'Export' ) . '</a></li>';
                        if( $results->getStatus() == 2 )
                        $opts .= '<li><a href="#" data-ajax="user-options3" data-data=\'' . cms_json_encode( [ 'action' => 'reject-response', 'response' => $results->getId(), 'location' => ( $not_approved ? 'table-pending' : 'table' ) ] ) . '\'>' . t( 'Reject' ) . '</a></li>';
                        $opts .= '
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'delete', 'result' => $results->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options']     = $opts;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No responses" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $results->pagination() )
                $result['pagination']   = $results->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'survey_label_responses':
            $options = \util\etc::formFilterOptions();

            if( empty( $options['id'] ) || empty( $options['label'] ) )
            return ;

            $surveys = surveys();
            $surveys ->setId( (int) $options['id'] );
            
            if( !$surveys->getObject() )
            return ;

            $labels = new \query\survey\labels;
            $labels ->setId( (int) $options['label'] );

            if( !$labels->getObject() || $surveys->getId() !== $labels->getSurveyId() )
            return ;

            $list['list']   = [];

            $results    = $labels->getResults();
            $results    ->setCountry();

            if( isset( $_POST['page'] ) )
            $results->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) )
            $results->setStatus( $options['status'] );

            if( isset( $options['checked'] ) )
            $results->setChecked( (bool) $options['checked'] );

            if( isset( $options['orderby'] ) )
            $results->orderBy( $options['orderby'] );

            foreach( $results->fetch() as $r ) {
                // Result
                $results    ->setObject( $r );
                $result     = $results->getResultObject();
                // Rejected response
                if( $result->getStatus() == 0 ) {
                    $durat  = $result->getStatusMarkup();
                // Response in progress
                } else if( $result->getStatus() == 1 ) {
                    $spent  = time() - strtotime( $result->getDate() );
                    $durat  = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );
                    $durat  = $result->getStatusMarkup( '<div>' . $durat . '</div>' );
                // Finished
                } else {
                    $spent  = strtotime( $result->getFinishDate() ) - strtotime( $result->getDate() );
                    $durat  = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );
                    $durat  = $result->getStatusMarkup( '<div>' . $durat . '</div>' );
                }
                
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();
                $country= $result->getCountryIso3166();
                $labels = '';

                if( $result->getComment() )
                $labels .= '<div class="lop"><i class="fas fa-comment-alt"></i></div>';
                $labels .= '<a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'labels', 'result' => $result->getId() ] ) . '\'><div class="llst">';
                foreach( $result->getLabels()->fetch( -1 ) as $label ) {
                    if( $label->id ) {
                        $labels .= '<div class="sav" id="lab-' . $label->id . '"><i class="avt-' . esc_html( $label->color ) . '"></i></div>';
                    }
                }
                $labels .= '<div class="e"><i></i></div>';
                $labels .= '</div></a>';

                $srv    = [
                    'name'      => '
                    <div class="dfac">
                        <label class="lch">
                            <input name="result-' . $result->getId() . '" type="checkbox" value="' . $results->getId() . '"' . ( $results->getChecked() ? ' checked' : '' ) . ' />
                            <i class="fas fa-check"></i>
                        </label>
                        <a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $result->getId() ] ) . '\'>#' . $result->getId() . '</a>' . $labels . '
                    </div>',
                    'country'   => ( $country ? '<img src="' . site_url( 'assets/flags/' . $country . '.svg' ) . '"><span>' . $country . '</span>' : '-' ),
                    'duration'  => $durat,
                    'date'      => custom_time( $result->getDate() )[0],
                ];

                $opts = '
                <ul class="btnset top mla">';
                    if( $result->getStatus() == 2 )
                    $opts .= '<li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'approve', 'result' => $result->getId() ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                    $opts .= '
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $result->getId() ] ) . '\'>' . t( 'View' ) . '</a></li>
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'export', 'result' => $result->getId() ] ) . '\'>' . t( 'Export' ) . '</a></li>
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'delete', 'result' => $result->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options'] = $opts;
                $list['list'][] = $srv;
            }

            if( empty( $list['list'] ) )
                $list['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No responses" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $results->pagination() )
                $list['pagination']   = $results->pagination()->markup( 1 );

            return cms_json_encode( $list );
        break;

        case 'survey_reports':
            $result['list'] = [];
            $teams          = [];
            $surveys        = surveys();
            $options        = \util\etc::formFilterOptions();

            if( empty( $options['id'] ) )
            return ;

            if( isset( $_POST['firstLoad'] ) ) {
                if( isset( $options['report'] ) ) {
                    $before = page_alert( t( 'Currently, you only see the answers from a report' ) );
                    $result['before'] = $before;
                }
            }

            $surveys    ->setId( (int) $options['id'] );
            $results    = $surveys->reports();
            $results    ->setSaved();
            
            if( isset( $_POST['page'] ) )
            $results->setPage( (int) $_POST['page'] );

            if( isset( $options['survey'] ) )
            $results->setSurveyId( $options['status'] );

            if( isset( $options['search'] ) ) {
                $results   ->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $results    ->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $results->orderBy( $options['orderby'] );

            foreach( $results->fetch() as $r ) {
                $results->setObject( $r );                
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();
                $srv    = [
                    'name'      => '<a href="' . admin_url( 'survey/' . $surveys->getId() . '/report/' . $results->getId() ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'report', 'id' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . esc_html( $results->getTitle() ) . '</a>',
                    'date'      => custom_time( $results->getDate() )[0],
                ];

                $opts = '
                <ul class="btnset top mla">';
                    $opts .= '
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="' . admin_url( 'survey/' . $surveys->getId() . '/report/' . $results->getId() ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'report', 'id' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'View report' ) . '</a></li>
                        <li><a href="' . admin_url( 'survey/' . $surveys->getId() . '/responses/report/' . $results->getId() ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'responses', 'id' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'View responses' ) . '</a></li>
                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'export-report', 'survey' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'Export' ) . '</a></li>
                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'edit-report', 'survey' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'Edit' ) . '</a></li>
                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'share-report', 'survey' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'Share' ) . '</a></li>
                        <li><a href="#" data-popup="manage-survey" data-options=\'' . cms_json_encode( [ 'action' => 'delete-report', 'survey' => $surveys->getId(), 'report' => $results->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options']     = $opts;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No reports" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $results->pagination() )
                $result['pagination']   = $results->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'payouts_respondent':
            $result['list'] = [];

            $surveys        = surveys();
            $transactions   = me()->getTransactions( 4 );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $transactions->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) && in_array( $options['status'], [ 0, 1, 2 ] ) )
            $transactions->setStatus( $options['status'] );

            if( isset( $options['orderby'] ) )
            $transactions->orderBy( $options['orderby'] );

            foreach( $transactions->fetch() as $transaction ) {
                $transactions   ->setObject( $transaction );
                $to             = '-';

                if( ( $details = $transactions->getDetailsJD() ) ) {
                    if( isset( $details->Method ) )
                    $to = esc_html( $details->Method );
                }

                $trs = [
                    'amount'    => $transactions->getAmountF(),
                    'method'    => $to,
                    'status'    => $transactions->getStatusMarkup(),
                    'date'      => custom_time( $transactions->getDate(), 2 )
                ];

                $options = '
                <ul class="btnset mla">';

                $options .= '
                    <li><a href="#" data-popup="user-actions" data-data=\'' . cms_json_encode( [ 'action' => 'withdraw-info', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Details' ) . '</a></li>';
                    if( $transactions->getStatus() == 1 )
                    $options .= '<li><a href="#" data-ajax="user-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'cancel-withdraw', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Cancel' ) . '</a></li>';
                $options .= '
                </ul>';

                $trs['options'] = $options;
                $result['list'][] = $trs;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No payouts" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $transactions->pagination() )
                $result['pagination']   = $transactions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'payouts_owner':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];

            $surveys        = surveys();
            $transactions   = new \query\transactions;
            $transactions   ->setTypeId( 4 );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $transactions->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) && in_array( $options['status'], [ 0, 1, 2 ] ) )
            $transactions->setStatus( $options['status'] );

            if( isset( $options['orderby'] ) )
            $transactions->orderBy( $options['orderby'] );

            foreach( $transactions->fetch() as $transaction ) {
                $transactions   ->setObject( $transaction );
                $to             = '-';

                if( ( $details = $transactions->getDetailsJD() ) ) {
                    if( isset( $details->Method ) )
                    $to = esc_html( $details->Method );
                }

                $trs = [
                    'amount'    => $transactions->getAmountF(),
                    'method'    => $to,
                    'status'    => $transactions->getStatusMarkup(),
                    'date'      => custom_time( $transactions->getDate(), 2 )
                ];

                $options = '
                <ul class="btnset mla">
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="#" data-popup="user-actions" data-data=\'' . cms_json_encode( [ 'action' => 'withdraw-info', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Details' ) . '</a></li>';
                        if( $transactions->getStatus() == 1 ) {
                            $options .= '<li><a href="#" data-ajax="user-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'approve-withdraw', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                            $options .= '<li><a href="#" data-ajax="user-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'cancel-withdraw', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Cancel' ) . '</a></li>';
                        }
                    $options .= '
                    </ul>
                </div>';

                $trs['options'] = $options;
                $result['list'][] = $trs;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No payouts" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $transactions->pagination() )
                $result['pagination']   = $transactions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'transactions_respondent':
        case 'transactions_surveyor':
            $result['list'] = [];
            $surveys        = surveys();
            $transactions   = me()->getTransactions();
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $transactions->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) && in_array( $options['status'], [ 0, 1, 2 ] ) )
            $transactions->setStatus( $options['status'] );

            if( $_GET['table'] == 'transactions_surveyor' ) {
                $viewids = [ 1, 2, 3, 5, 7 ];
                if( isset( $options['view'] ) )
                $viewids = array_intersect( $viewids, $options['view'] );
                $transactions->setTypeIdIN( $viewids );
            } else {
                $viewids = [ 4, 6 ];
                if( isset( $options['view'] ) )
                $viewids = array_intersect( $viewids, $options['view'] );
                $transactions->setTypeIdIN( $viewids );
            }

            if( isset( $options['orderby'] ) )
            $transactions->orderBy( $options['orderby'] );

            foreach( $transactions->fetch() as $transaction ) {
                $transactions   ->setObject( $transaction );
                $survey         = '-';
                if( $transactions->getSurveyId() ) {
                    $surveys    ->setId( $transactions->getSurveyId() )
                                ->resetInfo();

                    if( $surveys->getObject() )
                    $survey = esc_html( $surveys->getName() );
                }

                $amount             = $transactions->getAmountF();
                $transaction_str    = get_transaction_str( $transactions->getTypeId(), $transactions->getAmount() );
                
                if( $transaction_str['sign'] == '+' )
                    $amount = '<span class="plus">' . $amount . '</span>';
                else if( $transaction_str['sign'] == '-' ) 
                    $amount = '<span class="minus">' . $amount . '</span>';
                
                $trs = [
                    'amount'    => $amount,
                    'type'      => $transaction_str['title'],
                    'survey'    => $survey,
                    'status'    => $transactions->getStatusMarkup(),
                    'date'      => custom_time( $transactions->getDate(), 2 )
                ];

                $options = '
                <ul class="btnset mla">
                    <li><a href="#" data-popup="user-actions" data-data=\'' . cms_json_encode( [ 'action' => 'withdraw-info', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Details' ) . '</a></li>';
                    if( $transactions->getStatus() == 1 )
                    $options .= '<li><a href="#" data-ajax="user-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'cancel-withdraw', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Cancel' ) . '</a></li>';
                    $options .= '
                </ul>';

                $trs['options'] = $options;
                $result['list'][] = $trs;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No transactions" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $transactions->pagination() )
                $result['pagination']   = $transactions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'transactions_owner':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $surveys        = surveys();
            $transactions   = new \query\transactions;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $transactions->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) && in_array( $options['status'], [ 0, 1, 2 ] ) )
            $transactions->setStatus( $options['status'] );

            $viewids = [ 1, 2, 3, 5, 6, 7, 8 ];

            if( isset( $options['view'] ) )
            $viewids = array_intersect( $viewids, $options['view'] );

            $transactions->setTypeIdIN( $viewids );

            if( isset( $options['orderby'] ) )
            $transactions->orderBy( $options['orderby'] );

            foreach( $transactions->fetch() as $transaction ) {
                $transactions   ->setObject( $transaction );
                $survey         = '-';
                if( $transactions->getSurveyId() ) {
                    $surveys    ->setId( $transactions->getSurveyId() )
                                ->resetInfo();

                    if( $surveys->getObject() )
                    $survey     = esc_html( $surveys->getName() );
                }

                $transaction_str= get_transaction_str( $transactions->getTypeId(), $transactions->getAmount() );
                
                $trs = [
                    'amount'    => '<strong>' . $transactions->getAmountF() . '</strong>',
                    'type'      => $transaction_str['title'],
                    'user'      => ( ( $user = $transactions->getUser() )->getObject() ? $user->getName() : '-' ),
                    'survey'    => $survey,
                    'status'    => $transactions->getStatusMarkup(),
                    'date'      => custom_time( $transactions->getDate(), 2 )
                ];

                $options = '
                <ul class="btnset top mla">
                    <li><a href="#" data-popup="user-actions" data-data=\'' . cms_json_encode( [ 'action' => 'withdraw-info', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Details' ) . '</a></li>';
                    if( $transactions->getStatus() == 1 )
                    $options .= '<li><a href="#" data-ajax="user-actions2" data-data=\'' . cms_json_encode( [ 'action' => 'cancel-withdraw', 'withdraw' => $transactions->getId() ] ) . '\'>' . t( 'Cancel' ) . '</a></li>';
                    $options .= '
                </ul>';

                $trs['options'] = $options;
                $result['list'][] = $trs;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No transactions" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $transactions->pagination() )
                $result['pagination']   = $transactions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'users':
            if( !me()->isAdmin() ) return ;

            $result['list']  = [];
            $users          = users();     
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $users->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $users->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $users->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['view'] ) ) {
                switch( $options['view'] ) {
                    case 'sur': $users->setIsSurveyor(); break;
                    case 'team': $users->setHasPerm( 0, '>' ); break;
                    case 'ban': $users->setIsBanned(); break;
                }
            }

            if( isset( $options['orderby'] ) )
            $users->orderBy( $options['orderby'] );

            foreach( $users->fetch() as $user ) {
                $users  ->setObject( $user );
                $avatar = $users->getAvatarMarkup( 160 );
                $usr    = [
                    'name'      => '<a href="#">' . esc_html( $users->getDisplayName() ) . '</a>',
                    'id'        => $users->getId(),
                    'info'      => $users->getInfoListMarkup(),
                    'balance'   => $users->getBalanceF(),
                    'avatar'    => ( filter_var( $avatar, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $avatar ). '" alt="" />' : $avatar ),
                    'language'  => $users->getLanguage( 'short' ),
                    'country'   => '-'
                ];

                if( ( $country = $users->getCountry() )->getObject() )
                $usr['country'] = esc_html( $country->getName() );

                $options = '
                <ul class="btnset top mla">
                    <li><a href="#" data-popup="manage-users" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit', 'id' => $users->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    <li class="vopts">
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>';

                $options .= '
                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="#" data-popup="manage-users" data-data=\'' . cms_json_encode( [ 'action' => 'change-password', 'id' => $users->getId() ] ) . '\'>' . t( 'Change password' ) . '</a></li>
                        <li><a href="#" data-popup="manage-users" data-data=\'' . cms_json_encode( [ 'action' => 'ban', 'id' => $users->getId() ] ) . '\'>' . ( $users->isBanned() ? t( 'Unban' ) : t( 'Ban' ) ) . '</a></li>
                        <li><a href="#" data-popup="manage-users" data-data=\'' . cms_json_encode( [ 'action' => 'send-alert', 'id' => $users->getId() ] ) . '\'>' . t( 'Send alert' ) . '</a></li>
                        <li><a href="#" data-popup="manage-users" data-data=\'' . cms_json_encode( [ 'action' => 'user-balance', 'id' => $users->getId() ] ) . '\'>' . t( 'Balance' ) . '</a></li>
                        <li><a href="#" data-popup="manage-users" data-data=\'' . cms_json_encode( [ 'action' => 'info-user', 'id' => $users->getId() ] ) . '\'>' . t( 'Info' ) . '</a></li>';
                        if( me()->isOwner() ) {
                            $options .= '<li><a href="' . admin_url( 'actions/to_user/' . $users->getId() ) . '" data-to="actions" data-options=\'' . cms_json_encode( [ 'to_user' => $users->getId() ] ) . '\'>' . t( 'Actions history' ) . '</a></li>';
                            if( $users->isModerator() )
                            $options .= '<li><a href="' . admin_url( 'actions/by_user/' . $users->getId() ) . '" data-to="actions" data-options=\'' . cms_json_encode( [ 'by_user' => $users->getId() ] ) . '\'>' . t( 'Actions done' ) . '</a></li>';
                        }
                        $options .= '
                    </ul>
                </div>';

                $usr['options'] = $options;
                $result['list'][] = $usr;
            }

            if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $users->pagination() )
            $result['pagination'] = $users->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'categories':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $options        = \util\etc::formFilterOptions();

            $builder    = new \dev\builder\categories;
            if( isset( $options['type'] ) )
            $builder    ->setType( $options['type'] );
    
            try {
                $builder->checkType();
            }
    
            catch( \Exception $e ) {
                return false;
            }

            $categories = $builder->getCategoriesObject();
            $builder    ->filters( $options );
            $categories ->setType( $options['type'] );

            if( isset( $_POST['page'] ) )
            $categories ->setPage( (int) $_POST['page'] );

            $builder->getHeader();

            foreach( $categories->fetch() as $category ) {
                $categories         ->setObject( $category );
                $result['list'][]   = $builder->getItem( $categories );
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No categories" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $categories->pagination() )
                $result['pagination']   = $categories->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'pages':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $options        = \util\etc::formFilterOptions();

            $builder    = new \dev\builder\pages;
            if( isset( $options['type'] ) )
            $builder    ->setType( $options['type'] );
    
            try {
                $builder->checkType();
            }
    
            catch( \Exception $e ) {
                return false;
            }

            $pages      = $builder->getPagesObject();
            $builder    ->filters( $options );
            $pages      ->setType( $options['type'] );

            if( isset( $_POST['page'] ) )
            $pages->setPage( (int) $_POST['page'] );

            $builder->getHeader();

            foreach( $pages->fetch() as $page ) {
                $pages              ->setObject( $page );
                $result['list'][]   = $builder->getItem( $pages );
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No pages" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $pages->pagination() )
                $result['pagination']   = $pages->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'themes':
            if( !me()->isOwner() ) return ;

            $result['list']  = [];

            $cTheme = get_option( 'theme_name' );
            $themes = new \site\themes;
            $themes ->getThemes();

            if( isset( $_POST['page'] ) )
            $themes->setPage( (int) $_POST['page'] );

            if( $themes->count() ) {
                foreach( $themes->fetch() as $tid => $theme ) {
                    $themes->readTheme( $tid );

                    $srv    = [
                        'name'      => '<a href="' . esc_url( $themes->getListLink() ) . '">' . esc_html( $themes->getName() ) . '</a>',
                        'image'     => ( filter_var( $themes->getPreviewMarkup(), FILTER_VALIDATE_URL ) ? '<div class="ibg" style="background-image:url(\'' . esc_html( $themes->getPreviewMarkup() ) . '\');" alt="" />' : $themes->getPreviewMarkup() ),
                        'author'    => ( ( $author = $themes->getAuthor() ) ? esc_html( $author ) : '-' ),
                        'version'   => ( ( $version = $themes->getVersion() ) ? esc_html( $version ) : '1.00' )
                    ];

                    $options    = '
                    <ul class="btnset mla">
                        <li class="activate' . ( $themes->isActivated() ? ' hidden' : '' ) . '">
                            <a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'activate-theme', 'theme' => $tid ] ) ) . '\'>' . t( 'Activate' ) . '</a>
                        </li>
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'theme-info', 'theme' => $themes->getId() ] ) ) . '\'>' . t( 'Info' ) . '</a></li>';
                            if( $themes->getId() != $cTheme )
                            $options .= '<li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-theme', 'theme' => $themes->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>';
                            $options .= '
                        </ul>
                    </div>';

                    $options .= '</ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }

                if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $themes->pagination() )
                $result['pagination'] = $themes->pagination()->markup( 1 );
            }

            return cms_json_encode( $result );
        break;

        case 'plugins':
            if( !me()->isOwner() ) return ;

            $result['list']  = [];

            $plugins    = new \site\plugins;
            $plugins    ->getPlugins();

            if( isset( $_POST['page'] ) )
            $plugins->setPage( (int) $_POST['page'] );

            if( $plugins->count() ) {
                foreach( $plugins->fetch() as $pid => $info ) {
                    $plugins->setObject( $info );
                    $srv        = [
                        'name'      => '<a href="' . esc_url( $plugins->getListLink() ) . '">' . esc_html( $plugins->getName() ) . '</a>',
                        'image'     => ( filter_var( $plugins->getPreviewMarkup(), FILTER_VALIDATE_URL ) ? '<div class="ibg" style="background-image:url(\'' . esc_html( $plugins->getPreviewMarkup() ) . '\');" alt="" />' : $plugins->getPreviewMarkup() ),
                        'author'    => ( ( $author = $plugins->getAuthor() ) ? esc_html( $author ) : '-' ),
                        'version'   => ( ( $version = $plugins->getVersion() ) ? esc_html( $version ) : '1.00' )
                    ];

                    $options = '<ul class="btnset top mla">';

                    if( $plugins->isActivated() )
                        $options .= '<li><a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'deactivate-plugin', 'plugin' => $plugins->getId() ] ) ) . '\'>' . t( 'Deactivate' ) . '</a></li>';
                    else
                        $options .= '<li><a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'activate-plugin', 'plugin' => $plugins->getId() ] ) ) . '\'>' . t( 'Activate' ) . '</a></li>';
                    
                    $options .= '
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'plugin-info', 'plugin' => esc_html( $plugins->getId() ) ] ) ) . '\'>' . t( 'Info' ) . '</a></li>
                            <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-plugin', 'plugin' => esc_html( $plugins->getId() ) ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';

                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }

                if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $plugins->pagination() )
                $result['pagination'] = $plugins->pagination()->markup( 1 );
            } else 
            $result['fallback'] = '<div class="msg mb0 info2">' . t( "Oops! No plugins" ) . '</div>';

            return cms_json_encode( $result );
        break;

        case 'subscriptions':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $subscriptions  = new \query\subscriptions;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $subscriptions->setPage( (int) $_POST['page'] );

            if( !empty( $options['plan'] ) )
            $subscriptions->setPlanId( $options['plan'] ); 

            if( isset( $options['orderby'] ) )
            $subscriptions->orderBy( $options['orderby'] );

            if( $subscriptions->count() ) {
                foreach( $subscriptions->fetch() as $sid => $subscription ) {
                    $subscriptions  ->setObject( $subscription );
                    $expiration     = custom_time( $subscriptions->getExpiration() );
                    $srv    = [
                        'name'      => '<strong>' . esc_html( $subscriptions->getName() ) . '</strong>',
                        'user'      => ( ( $user = $subscriptions->getUser() )->getObject() ? $user->getName() : '-' ),
                        'expiration'=> ( strtotime( $subscriptions->getExpiration() ) > time() ? '<strong title="' . $expiration[0] . '">' . $expiration[1] . '</strong>' : '<strong>' . t( 'Expired' ) . '</strong>' ),
                        'autorenew' => '<strong>' . ( $subscriptions->getAutoRenew() ? t( 'Yes' ) : t( 'No' ) ) . '</strong>',
                        'lastrenew' => custom_time( $subscriptions->getLastRenew(), 2 ),
                        'date'      => custom_time( $subscriptions->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-subscription', 'subscription' => $subscriptions->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No subscriptions" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $subscriptions->pagination() )
                $result['pagination']   = $subscriptions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'teams':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $teams          = new \query\team\teams;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $teams->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $teams->search( $options['search'] );
                if( !isset( $options['orderby'] ) ) {
                    $teams->orderBy( 'relevance_desc' ); 
                }
            }

            if( isset( $options['orderby'] ) )
            $teams->orderBy( $options['orderby'] );

            if( $teams->count() ) {
                foreach( $teams->fetch() as $sid => $team ) {
                    $teams  ->setObject( $team );
                    $owner  = $teams->getUser();
                    $srv    = [
                        'name'      => '<strong>' . esc_html( $teams->getName() ) . '</strong>',
                        'owner'     => ( $owner->getObject() ? esc_html( $owner->getDisplayName() ) : '-' ),
                        'members'   => $teams->members()->setApproved()->count(),
                        'date'      => custom_time( $teams->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="#" data-popup="manage-teams" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit', 'id' => $teams->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No teams" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $teams->pagination() )
                $result['pagination']   = $teams->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'shop_categories':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $categories     = new \query\shop\categories;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $categories->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $categories->search( $options['search'] );
                if( !isset( $options['orderby'] ) ) {
                    $categories->orderBy( 'relevance_desc' ); 
                }
            }

            if( isset( $options['orderby'] ) )
            $categories->orderBy( $options['orderby'] );

            if( $categories->count() ) {
                foreach( $categories->fetch() as $sid => $team ) {
                    $categories ->setObject( $team );
                    $country    = $categories->getCountry();
                    if( $country )
                    $country= getCountry( $country );

                    $srv    = [
                        'name'      => '<strong>' . esc_html( $categories->getName() ) . '</strong>',
                        'country'   => $country ? $country->name : t( 'All' ),
                        'date'      => custom_time( $categories->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="#" data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-category', 'id' => $categories->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No categories" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $categories->pagination() )
                $result['pagination']   = $categories->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'shop_items':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $items          = new \query\shop\items;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $items->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $items->search( $options['search'] );
                if( !isset( $options['orderby'] ) ) {
                    $items->orderBy( 'relevance_desc' ); 
                }
            }

            if( isset( $options['orderby'] ) )
            $items->orderBy( $options['orderby'] );

            if( $items->count() ) {
                foreach( $items->fetch() as $sid => $team ) {
                    $items      ->setObject( $team );
                    $image      = $items->getMediaMarkup();
                    $country    = $items->getCountry();
                    if( $country )
                    $country    = getCountry( $country );

                    $srv    = [
                        'image'     => ( filter_var( $image, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $image ). '" alt="" />' : $image ),
                        'name'      => '<strong>' . esc_html( $items->getName() ) . '</strong>',
                        'price'     => $items->getPrice(),
                        'stock'     => ( $items->getStock() ? $items->getStock() : '-' ),
                        'purchases' => $items->getPurchases(),
                        'country'   => $country ? $country->name : t( 'All' ),
                        'date'      => custom_time( $items->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="#" data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-item', 'id' => $items->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No items" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $items->pagination() )
                $result['pagination']   = $items->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'shop_orders':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $orders         = new \query\shop\orders;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $orders->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) )
            $orders->setStatus( $options['status'] );

            if( isset( $options['orderby'] ) )
            $orders->orderBy( $options['orderby'] );

            if( $orders->count() ) {
                foreach( $orders->fetch() as $sid => $order ) {
                    $orders ->setObject( $order );
                    $user   = $orders->getUser();
                    $srv    = [
                        'id'        => $orders->getId(),
                        'user'      => ( $user->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                        'status'    => $orders->getStatusMarkup(),
                        'amount'    => $orders->getTotal(),
                        'date'      => custom_time( $orders->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li>
                            <a href="#" data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'view-order', 'id' => $orders->getId() ] ) ) . '\'>' . t( 'View' ) . '</a>
                        </li>
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'change-order-status', 'id' => $orders->getId() ] ) ) . '\'>' . t( 'Change status' ) . '</a></li>
                        </ul>
                    </div>';

                    $options .= '</ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No orders" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $orders->pagination() )
                $result['pagination']   = $orders->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'shop':
            if( me()->viewAs !== 'respondent' ) return ;

            $result['list'] = [];
            $options    = \util\etc::formFilterOptions();

            if( !empty( $options['category'] ) ) {
                $items  = new \query\shop\category_with_items;
                $items  ->setCategoryId( $options['category'] );
            } else
                $items  = new \query\shop\items;

            if( isset( $_POST['page'] ) )
            $items->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $items->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $items->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $items->orderBy( $options['orderby'] );

            if( $items->count() ) {
                foreach( $items->fetch() as $sid => $team ) {
                    $items      ->setObject( $team );
                    $image      = $items->getMediaMarkup();
                    $country    = $items->getCountry();
                    if( $country )
                    $country    = getCountry( $country );

                    $srv        = [
                        'image'     => ( filter_var( $image, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $image ). '" alt="" />' : $image ),
                        'name'      => '<strong>' . esc_html( $items->getName() ) . '</strong>',
                        'price'     => $items->getPrice()
                    ];

                    $options    = '<ul data-item="' . $items->getId() . '" class="btnset opts top mla">';

                    if( shop()->cartHasItem( $items->getId() ) ) {
                        $options .= '
                        <li>
                            <a href="#" data-remove-item>
                                <i class="fas fa-times"></i>
                            </a>
                        </li>';
                    } else {
                        $options .= '
                        <li>
                            <a href="#" data-add-item>
                                <i class="fas fa-cart-plus"></i>
                            </a>
                        </li>';
                    }
                    
                    $options    .= '
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-popup="manage-shop" data-data=\'' . ( cms_json_encode( [ 'action' => 'shop-details', 'id' => $items->getId() ] ) ) . '\'>' . t( 'Details' ) . '</a></li>
                        </ul>
                    </div>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No items" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $items->pagination() )
                $result['pagination']   = $items->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'shop_my_orders':
            $result['list'] = [];
            $orders         = new \query\shop\orders;
            $orders         ->setUserId( me()->getId() );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $orders->setPage( (int) $_POST['page'] );

            if( isset( $options['status'] ) )
            $orders->setStatus( $options['status'] );

            if( isset( $options['orderby'] ) )
            $orders->orderBy( $options['orderby'] );

            if( $orders->count() ) {
                foreach( $orders->fetch() as $sid => $order ) {
                    $orders ->setObject( $order );
                    $srv    = [
                        'id'        => $orders->getId(),
                        'status'    => $orders->getStatusMarkup(),
                        'amount'    => $orders->getTotal(),
                        'date'      => custom_time( $orders->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="#" data-popup="view-order" data-data=\'' . ( cms_json_encode( [ 'id' => $orders->getId() ] ) ) . '\'>' . t( 'View' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No orders" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $orders->pagination() )
                $result['pagination']   = $orders->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'invoices_surveyor':
            $result['list'] = [];
            $invoices       = new \query\invoices;
            $invoices       ->setUserId( me()->getId() );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $invoices->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) )
            $invoices->search( $options['search'] );

            if( isset( $options['orderby'] ) )
            $invoices->orderBy( $options['orderby'] );

            if( $invoices->count() ) {
                foreach( $invoices->fetch() as $sid => $invoice ) {
                    $invoices->setObject( $invoice );
                    $user   = $invoices->getUser();
                    $srv    = [
                        'number'    => '<strong>' . esc_html( $invoices->getNumber() ) . '</strong>',
                        'user'      => ( $user->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                        'amount'    => $invoices->getTotalF(),
                        'date'      => custom_time( $invoices->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="' . admin_url( 'view/invoice/' . $invoices->getId() ) . '" target="_blank">' . t( 'View' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No invoices" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $invoices->pagination() )
                $result['pagination']   = $invoices->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'invoices_owner':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $invoices       = new \query\invoices;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $invoices->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) )
            $invoices->search( $options['search'] );

            if( isset( $options['orderby'] ) )
            $invoices->orderBy( $options['orderby'] );

            if( $invoices->count() ) {
                foreach( $invoices->fetch() as $sid => $invoice ) {
                    $invoices->setObject( $invoice );
                    $user   = $invoices->getUser();
                    $srv    = [
                        'number'    => '<strong>' . esc_html( $invoices->getNumber() ) . '</strong>',
                        'user'      => ( $user->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                        'amount'    => $invoices->getTotalF(),
                        'date'      => custom_time( $invoices->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="' . admin_url( 'view/invoice/' . $invoices->getId() ) . '" target="_blank">' . t( 'View' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No invoices" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $invoices->pagination() )
                $result['pagination']   = $invoices->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'manage_subscriptions':
            if( !me()->isAdmin() ) return ;

            $result     = [ 'list' => [] ];
            $total      = subscriptions();
            $active     = subscriptions()
                        ->setNotExpired();
            $autorenew  = subscriptions()
                        ->setExpiration( 24, 'HOUR' )
                        ->setAutorenew();
            $expires    = subscriptions()
                        ->setExpiration( 7 )
                        ->setNotExpired();
            $expired    = subscriptions()
                        ->setExpired();

            $result['list'][]   = [
                'count'     => '<div class="avt avt-P"><span>' . (int) $total->count() . '</span></div>',
                'name'      => t( 'Total subscriptions' ),
                'options'   => ''
            ];

            $result['list'][]   = [
                'count'     => '<div class="avt avt-A2"><span>' . (int) $active->count() . '</span></div>',
                'name'      => t( 'Active subscriptions' ),
                'options'   => ''
            ];

            $result['list'][]   = [
                'count'     => '<div class="avt avt-H"><span>' . (int) $autorenew->count() . '</span></div>',
                'name'      => t( 'Ready for auto-renew' ),
                'options'   => '
                <ul class="btnset mla">
                    <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'autorenew-subscriptions' ] ) ) . '\'>' . t( 'Auto-renew' ) . '</a></li>
                </ul>'
            ];

            $result['list'][]   = [
                'count'     => '<div class="avt avt-X"><span>' . (int) $expires->count() . '</span></div>',
                'name'      => t( 'Will expire in the next 7 days' ),
                'options'   => '
                <ul class="btnset mla">
                    <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'subscription-actions', 'type' => 'will_expire' ] ) ) . '\'>' . t( 'Notify' ) . '</a></li>
                </ul>'
            ];

            $result['list'][]   = [
                'count'     => '<div class="avt avt-O"><span>' . (int) $expired->count() . '</span></div>',
                'name'      => t( 'Have expired' ),
                'options'   => '
                <ul class="btnset mla">
                <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'remove-subscriptions' ] ) ) . '\'>' . t( 'Remove' ) . '</a></li>
                <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'subscription-actions', 'type' => 'expired' ] ) ) . '\'>' . t( 'Notify' ) . '</a></li>
                </ul>'
            ];

            return cms_json_encode( $result );
        break;

        case 'receipts_surveyor':
            $result['list'] = [];
            $receipts       = new \query\receipts;
            $receipts       ->setUserId( me()->getId() );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $receipts->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) )
            $receipts->search( $options['search'] );

            if( isset( $options['orderby'] ) )
            $receipts->orderBy( $options['orderby'] );

            if( $receipts->count() ) {
                foreach( $receipts->fetch() as $sid => $receipt ) {
                    $receipts->setObject( $receipt );
                    $user   = $receipts->getUser();
                    $srv    = [
                        'number'    => '<strong>' . esc_html( $receipts->getNumber() ) . '</strong>',
                        'user'      => ( $user->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                        'amount'    => $receipts->getTotalF(),
                        'date'      => custom_time( $receipts->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="' . admin_url( 'view/receipt/' . $receipts->getId() ) . '" target="_blank">' . t( 'View' ) . '</a></li>
                    </ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No receipts" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $receipts->pagination() )
                $result['pagination']   = $receipts->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'receipts_owner':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $receipts       = new \query\receipts;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $receipts->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) )
            $receipts->search( $options['search'] );

            if( isset( $options['orderby'] ) )
            $receipts->orderBy( $options['orderby'] );

            if( $receipts->count() ) {
                foreach( $receipts->fetch() as $sid => $receipt ) {
                    $receipts->setObject( $receipt );
                    $user   = $receipts->getUser();
                    $srv    = [
                        'number'    => '<strong>' . esc_html( $receipts->getNumber() ) . '</strong>',
                        'user'      => ( $user->getObject() ? esc_html( $user->getDisplayName() ) : '-' ),
                        'amount'    => $receipts->getTotalF(),
                        'date'      => custom_time( $receipts->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li><a href="' . admin_url( 'view/receipt/' . $receipts->getId() ) . '" target="_blank">' . t( 'View' ) . '</a></li>
                    </ul>';

                    $options .= '</ul>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No receipts" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $receipts->pagination() )
                $result['pagination']   = $receipts->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'countries':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $countries      = new \query\countries;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $countries->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) )
            $countries->search( $options['search'] );

            if( isset( $options['orderby'] ) )
            $countries->orderBy( $options['orderby'] );

            if( $countries->count() ) {
                foreach( $countries->fetch() as $cid => $country ) {
                    $countries  ->setObject( $country );
                    $srv        = [
                        'ico'   => '<img src="' . assets_url( 'flags/' . $countries->getIso3166() . '.svg' ) . '" alt="" />',
                        'name'  => '<strong>' . esc_html( $countries->getName() ) . '</strong>',
                    ];

                    $options = '
                    <ul class="btnset mla">
                        <li>
                            <a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-country', 'country' => $countries->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a>
                        </li>
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-country', 'country' => $countries->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No countries" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $countries->pagination() )
                $result['pagination']   = $countries->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'vouchers':
            if( !me()->isAdmin() ) return ;

            $result['list'] = [];
            $vouchers       = new \query\vouchers;
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $vouchers->setPage( (int) $_POST['page'] );

            if( isset( $options['exp'] ) ) {
                if( $options['exp'] == 0 )
                    $vouchers->setExpired();
                else if( $options['exp'] == 1 )
                    $vouchers->setNotExpired();
            }

            if( isset( $options['status'] ) ) {
                if( $options['status'] == 0 )
                    $vouchers->setStatus( 0 );
                else if( $options['status'] == 1 )
                    $vouchers->setStatus( 1 );
            }

            if( isset( $options['search'] ) ) {
                $vouchers->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $vouchers->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $vouchers->orderBy( $options['orderby'] );

            if( $vouchers->count() ) {
                foreach( $vouchers->fetch() as $vid => $voucher ) {
                    $vouchers->setObject( $voucher );

                    $srv        = [
                        'code'          => '<a href="#">' . esc_html( $vouchers->getCode() ) . '</a>',
                        'applying'      => ( $vouchers->getType() == 0 ? t( 'Free' ) : t( 'Deposit' ) ),
                        'user'          => ( $vouchers->getUserId() ? ( ( $user = $vouchers->getUser() )->getObject() ? $user->getDisplayName() : '-' ) : t( 'All') ),
                        'amount'        => ( $vouchers->getAmountType() == 0 ? cms_money_format( $vouchers->getAmount() ) : '20%' ),
                        'limit'         => ( $vouchers->getLimit() ? $vouchers->getLimit() : t( 'Unl.' ) ),
                        'status'        => ( $vouchers->getStatus() == 1 ? t( 'Available' ) : t( 'Disabled' ) ),
                        'expiration'    => ( $vouchers->getExpiration() ? custom_time( $vouchers->getExpiration() )[0] : t( 'Never' ) )
                    ];

                    $options = '
                    <ul class="btnset top mla">
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>
                    
                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'edit-voucher', 'voucher' => $vouchers->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>
                            <li><a href="#" data-ajax="website-actions2" data-data=\'' . ( cms_json_encode( [ 'action' => 'delete-voucher', 'voucher' => $vouchers->getId() ] ) ) . '\'>' . t( 'Delete' ) . '</a></li>
                        </ul>
                    </div>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No vouchers" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $vouchers->pagination() )
                $result['pagination']   = $vouchers->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'favorites':
            $result['list']  = [];

            $favorites  = me()->getFavorites();
            $options    = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $favorites->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $favorites->search( $options['search'] );
                if( !isset( $options['orderby'] ) ) {
                    $favorites->orderBy( 'relevance_desc' ); 
                }
            }

            if( isset( $options['orderby'] ) )
            $favorites->orderBy( $options['orderby'] );

            foreach( $favorites->fetch() as $favorite ) {
                $surveys        = $favorites->getSurvey( $favorite );
                if( !$surveys->getId() ) 
                continue;
                $isOwner        = $surveys->getUserId() == me()->getId();
                $canEdit        = me()->manageSurvey( 'edit-survey', $surveys->getId() );
                $category       = '-';

                if( ( $categories = $surveys->getCategory() ) && $categories->getObject() ) {
                    $category = esc_html( $categories->getName() );
                }

                $avatar = $surveys->getAvatarMarkup( 60 );
                $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $surveys->getId() ] );
                $URL    = admin_url( 'survey/' . $bURL->build() );
                $jsURL  = $bURL->getValuesJson();
                $srv    = [
                    'name'      => '<a href="' . $URL . '" data-to="survey" data-options=\'' . $jsURL . '\'>' . esc_html( $surveys->getName() ) . '</a>',
                    'image'     => ( filter_var( $avatar, FILTER_VALIDATE_URL ) ? '<img src="' . esc_html( $avatar ). '" alt="" />' : $avatar ),
                    'category'  => $category,
                    'status'    => $surveys->getStatusMarkup(),
                    'budget'    => '-',
                ];

                if( $isOwner ) $srv['budget'] = $surveys->getBudgetF() . ' <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'budget', 'survey' => $surveys->getId() ] ) ) . '\'><i class="fas fa-pencil-alt"></i></a>';

                $options = '<ul class="btnset top mla">';
                if( $surveys->getStatus() == 1 && ( $isOwner || me()->manageSurvey( 'manage-question' ) ) )
                $options .= '<li><a href="#" data-popup="add-survey-step2" data-data=\'' . ( cms_json_encode( [ 'survey' => $surveys->getId() ] ) ) . '\'><i class="fas fa-wrench"></i></a></li>';
                $options .= '<li>';
                if( $favorites->isFavorite( $surveys->getId() ) )
                    $options .= '<a href="#" data-ajax="remove-favorite" data-data=\'' . cms_json_encode( [ 'id' => $surveys->getId() ] ) . '\'><i class="fas fa-heart"></i></a>';
                else
                    $options .= '<a href="#" data-ajax="add-favorite" data-data=\'' . cms_json_encode( [ 'id' => $surveys->getId() ] ) . '\'><i class="far fa-heart"></i></a>';
                $options .= '</li>
                <li class="vopts">
                    <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                </li>
                </ul>

                <div class="dd-o">
                    <ul class="btnset">';
                        if( $isOwner || $canEdit )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'edit', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Edit' ) . '</a></li>';
                        if( $isOwner || me()->manageSurvey( 'manage-question' ) )
                        $options .= '
                        <li class="df">
                            <a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'questions', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Questions' ) . '</a>
                            <a href="#" class="wa" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-question', 'survey' => $surveys->getId() ] ) ) . '\'>
                                <i class="fas fa-plus"></i>
                            </a>
                        </li>';
                        if( $isOwner || me()->manageSurvey( 'manage-collector' ) )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'collectors', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Collectors (links)' ) . '</a></li>';
                        if( $isOwner || me()->manageSurvey( 'view-result' ) )
                        $options .= '<li><a href="' . admin_url( 'survey/' . $surveys->getId() . '/responses' ) . '" data-to="survey" data-options=\'' . cms_json_encode( [ 'action' => 'responses', 'id' => $surveys->getId() ] ) . '\'>' . t( 'Responses' ) . '</a></li>';
                        if( $isOwner )
                        $options .= '<li><a href="#" data-popup="manage-survey" data-options=\'' . ( cms_json_encode( [ 'action' => 'collaborators', 'survey' => $surveys->getId() ] ) ) . '\'>' . t( 'Collaborators' ) . '</a></li>';
                        $options .= '
                    </ul>
                </div>';
                
                $srv['options']     = $options;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No favorites" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $favorites->pagination() )
                $result['pagination']   = $favorites->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'saved':
            $result['list']  = [];

            $saved      = me()->getSaved();
            $results    = me()->getResults();
            $options    = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $saved->setPage( (int) $_POST['page'] );

            if( isset( $options['search'] ) ) {
                $saved->search( $options['search'] );
                if( !isset( $options['orderby'] ) )
                $saved->orderBy( 'relevance_desc' ); 
            }

            if( isset( $options['orderby'] ) )
            $saved->orderBy( $options['orderby'] );

            $collectors = collectors();

            foreach( $saved->fetch() as $saved_survey ) {
                $saved      ->setObject( $saved_survey );
                $survey     = $saved->getSurveyObject();
                if( !$survey->getId() ) 
                continue;
                $response   = $results->isResponsed( $survey->getId() );
                $collector  = NULL;
                $category   = '-';

                if( !$response || $response->status == 1 ) {
                    $collectors = $saved->getCollector();
                    if( $collectors->getObject() )
                    $collector = true;
                }

                if( $response ) {
                    $cpa        = ( $response->commission + $response->commission_bonus );
                    $cpaf       = cms_money_format( $cpa );
                    $lpoints    = $response->lpoints;
                } else {
                    if( $collector ) {
                        $cpa        = $collectors->getCPA();
                        $cpaf       = $collectors->getCPAF();
                        $lpoints    = $collectors->getLoyaltyPoints();
                    } else {
                        list( $cpa, $cpaf, $lpoints ) = [ '', '', '' ];
                    }
                }

                if( ( $categories = $survey->getCategory() ) && $categories->getObject() )
                $category = esc_html( $categories->getName() );

                $srv        = [
                    'survey'        => esc_html( $survey->getName() ),
                    'category'      => $category,
                    'commission'    => $cpaf,
                    'stars'         => $lpoints,
                    'date'          => custom_time( $saved_survey->sv_date, 2 )
                ];
                
                $options = '<ul class="btnset mla">';
                $options .= '<li><a href="#" data-ajax="remove-saved" data-data=\'' . cms_json_encode( [ 'id' => $survey->getId() ] ) . '\'><i class="fas fa-calendar-check"></i></a></li>';

                if( $response ) {
                    switch( $response->status ) {
                        case 3: $options .= '<li class="active disabled"><a href="#"><i class="fas fa-check"></i><span>' . t( 'Respond' ) . '</span></a></li>'; break;
                        case 2: $options .= '<li class="active disabled"><a href="#"><i class="fas fa-hourglass-half"></i><span>' . t( 'Pending' ) . '</span></a></li>'; break;
                        case 1: 
                            if( $collector )
                            $options .= '<li><a href="' . esc_url( $collectors->getPermalink() ) . '" target="_blank"><span>' . t( 'Continue' ) . '</span></a></li>'; break;
                    }
                } else if( $collector ) $options .= '<li><a href="' . esc_url( $collectors->getPermalink() ) . '" target="_blank"><span>' . t( 'Respond' ) . '</span></a></li>';
                
                $options .= '</ul>';

                $srv['options'] = $options;
                $result['list'][] = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No saved surveys" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $saved->pagination() )
                $result['pagination']   = $saved->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'pending_responses':
            $options    = \util\etc::formFilterOptions();

            $results    = me()->getSurveyResponses()
                        ->setStatus( 2 )
                        ->setCountry();

            $result['list'] = [];

            if( isset( $_POST['page'] ) )
            $results->setPage( (int) $_POST['page'] );

            foreach( $results->fetch() as $r ) {
                $response = $results->getResponseObject( $r );

                $spent  = strtotime( $response->getFinishDate() ) - strtotime( $response->getDate() );
                $durat  = $spent > 59 ? sprintf( t( '%s m' ), ceil( $spent / 60 ) ) : sprintf( t( '%s s' ), $spent );
                $durat  = $response->getStatusMarkup( '<div>' . $durat . '</div>' );
                $country= $response->getCountryIso3166();
                $slink  = '';
                $survey = $response->getSurvey();

                if( $survey->getObject() ) {
                    $bURL   = URLBP( [ 'id' => 'dir' ], [ 'id' => $survey->getId() ] );
                    $URL    = admin_url( 'survey/' . $bURL->build() );
                    $jsURL  = $bURL->getValuesJson();
                    $slink  = '<a href="' . $URL . '" data-to="survey" data-options=\'' . $jsURL . '\'>' . esc_html( $survey->getName() ) . '</a>';
                }

                $srv    = [
                    'name'      => '<div class="dfac"><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $response->getId() ] ) . '\'>#' . $response->getId() . '</a></div>',
                    'survey'    => $slink,
                    'country'   => ( $country ? '<img src="' . site_url( 'assets/flags/' . $country . '.svg' ) . '"><span>' . $country . '</span>' : '-' ),
                    'duration'  => $durat,
                    'date'      => custom_time( $response->getDate() )[0],
                ];

                $opts = '
                <ul class="btnset top mla">';
                    if( $response->getStatus() == 1 ) {
                        if( $response->isSelfResponse() )
                        $opts .= '<li><a href="' . site_url( 's/' . $response->getId() ) . '" target="_blank">' . t( 'Complete' ) . '</a></li>';
                    } else if( $response->getStatus() == 2 )
                    $opts .= '<li class="approve"><a href="#" data-ajax="user-options3" data-data=\'' . cms_json_encode( [ 'action' => 'approve-response', 'response' => $response->getId(), 'location' => 'table-pending' ] ) . '\'>' . t( 'Approve' ) . '</a></li>';
                    $opts .= '
                    <li>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </li>
                </ul>
                
                <div class="dd-o">
                    <ul class="btnset">
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'view', 'result' => $response->getId() ] ) . '\'>' . t( 'View' ) . '</a></li>
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'export', 'result' => $response->getId() ] ) . '\'>' . t( 'Export' ) . '</a></li>';
                        if( $response->getStatus() == 2 )
                        $opts .= '<li><a href="#" data-ajax="user-options3" data-data=\'' . cms_json_encode( [ 'action' => 'reject-response', 'response' => $response->getId(), 'location' => 'table-pending' ] ) . '\'>' . t( 'Reject' ) . '</a></li>';
                        $opts .= '
                        <li><a href="#" data-popup="manage-result" data-options=\'' . cms_json_encode( [ 'action' => 'delete', 'result' => $response->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                    </ul>
                </div>';

                $srv['options']     = $opts;
                $result['list'][]   = $srv;
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No responses" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $results->pagination() )
                $result['pagination']   = $results->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'admin_actions':
            if( !me()->isOwner() ) return ;

            $result['list'] = [];
            $actions        = new \query\admin\actions();     
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $actions->setPage( (int) $_POST['page'] );

            if( isset( $options['by_user'] ) )
            $actions->setByUserId( (int) $options['by_user'] );

            if( isset( $options['to_user'] ) )
            $actions->setToUserId( (int) $options['to_user'] );

            $amarkup = new markup\back_end\admin_actions;

            foreach( $actions->fetch() as $action ) {
                $actions    ->setObject( $action );
                $usr        = [
                    'action'    => $amarkup->read( $actions->getContent(), $actions->getByUserId(), $actions->getToUserId() )
                ];
                
                $usr['options'] = '
                <ul class="btnset mla">
                    <li><a href="#" data-ajax="manage-new3" data-data=\'' . cms_json_encode( [ 'action' => 'remove-admin-action', 'id' => $actions->getId() ] ) . '\'>' . t( 'Delete' ) . '</a></li>
                </ul>';
                $result['list'][] = $usr;
            }

            if( empty( $result['list'] ) )
                if( !isset( $result['fallback'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No actions" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $actions->pagination() )
                $result['pagination']   = $actions->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'kyc':
            if( !me()->isModerator() ) return ;

            $result['list'] = [];
            $intents        = new \query\user_intents;
            $intents        ->setTypeId( 1 );
            $options        = \util\etc::formFilterOptions();

            if( isset( $_POST['page'] ) )
            $intents->setPage( (int) $_POST['page'] );

            if( isset( $options['orderby'] ) )
            $intents->orderBy( $options['orderby'] );


            if( $intents->count() ) {
                foreach( $intents->fetch() as $id => $intent ) {
                    $intents    ->setObject( $intent );
                    $srv        = [
                        'user'      => ( ( $user = $intents->getUser() )->getObject() ? $user->getName() : '-' ),
                        'date'      => custom_time( $intents->getDate(), 2 ),
                    ];

                    $options = '
                    <ul class="btnset top mla">
                        <li><a href="#" data-popup="manage-kyc" data-data=\'' . ( cms_json_encode( [ 'action' => 'view', 'id' => $intents->getId() ] ) ) . '\'>' . t( 'View' ) . '</a></li>
                        <li class="vopts">
                            <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                        </li>
                    </ul>

                    <div class="dd-o">
                        <ul class="btnset">
                            <li><a href="#" data-ajax="manage-kyc3" data-data=\'' . ( cms_json_encode( [ 'action' => 'approve', 'id' => $intents->getId() ] ) ) . '\'>' . t( 'Approve' ) . '</a></li>
                            <li><a href="#" data-ajax="manage-kyc3" data-data=\'' . ( cms_json_encode( [ 'action' => 'reject', 'id' => $intents->getId() ] ) ) . '\'>' . t( 'Reject' ) . '</a></li>
                        </ul>
                    </div>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No intents" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $intents->pagination() )
                $result['pagination']   = $intents->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        case 'my_responses':
            $result['list'] = [];
            $myresults      = me()->getResults();
            $options        = \util\etc::formFilterOptions();

            if( isset( $options['status'] ) )
            $myresults->setStatus( (int) $options['status'] );
            
            if( isset( $_POST['page'] ) )
            $myresults->setPage( (int) $_POST['page'] );

            if( isset( $options['orderby'] ) )
            $myresults->orderBy( $options['orderby'] );

            if( $myresults->count() ) {
                foreach( $myresults->fetch() as $id => $myresult ) {
                    $myresults  ->setObject( $myresult );
                    $survey     = $myresults->getSurvey();
                    $srv        = [
                        'survey'        => ( $survey->getObject() ? esc_html( $survey->getName() ) : '-' ),
                        'status'        => $myresults->getStatusMarkup(),
                        'commission'    => $myresults->getCommissionF(),
                        'stars'         => $myresults->getLoyaltyStars(),
                        'date'          => custom_time( $myresults->getDate(), 2 )
                    ];

                    if( $myresults->getStatus() == 1 ) {
                        $collector  = $myresults->getCollector();
                        if( $collector->getObject() ) {
                            $options = '
                            <ul class="btnset mla">
                                <li><a href="' . esc_url( $collector->getPermalink() ) . '">' . t( 'Continue' ) . '</a></li>
                            </ul>';
                        }
                    } else 
                        $options = '<div class="mla">' . $myresults->getDuration() . '</div>';
                    
                    $srv['options'] = $options;
                    $result['list'][] = $srv;
                }
            }

            if( empty( $result['list'] ) )
                $result['fallback']     = '<div class="msg mb0 info2">' . t( "Oops! No results" ) . '</div>';
            else if( isset( $_POST['check_pagination'] ) && $_POST['check_pagination'] !== 'false' && $myresults->pagination() )
                $result['pagination']   = $myresults->pagination()->markup( 1 );

            return cms_json_encode( $result );
        break;

        default:
        // that's for sure an error
        break;
    }
});

ajax()->add_call( 'populate-boxes', function() {
    if( !isset( $_POST['load'] ) ) {
        // that's for sure an error
        return ;
    }

    $result = [];
    $box    = $_POST['load'];
    $res    = filters()->do_filter( 'get_box', $box );

    if( is_array( $res ) ) {
        $result = $result + $res;
    } else {
        $result[$box] = $res;
    }

    return cms_json_encode( $result );
});

ajax()->add_call( 'populate-charts', function() {
    if( !isset( $_GET['chart'] ) ) {
        // that's for sure an error
        return ;
    }

    switch( $_GET['chart'] ) {
        case 'reportings_earnings':
            $results    = [];
            // head
            $results[]  = [ '', t( 'Eargnins' ), t( 'Responses' ) ];

            // stats
            $stats = me()->getEarningsStats();
            if( $stats ) {
                list( $year, $month ) = [date( 'Y' ), date( 'm' )];

                $stats = $stats->setStatus( 3 );

                if( isset( $_POST['options'] ) ) {
                    parse_str( $_POST['options'], $filters );
                    if( isset( $filters['data']['year'] ) ) {
                        $year = $filters['data']['year'];
                    }

                    if( isset( $filters['data']['month'] ) ) {
                        $month = $filters['data']['month'];
                    }
                }

                $results += array_map( function( $v ) {
                    return [ $v->date, $v->sum, $v->responses_done ];
                }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );
            }

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_surveyor':
            $results    = [];
            // head
            $results[]  = [ '', t( 'Commissions' ), t( 'Responses' ) ];

            // stats
            $stats = me()->getMySurveysResponsesStats();
            if( $stats ) {
                list( $year, $month ) = [date( 'Y' ), date( 'm' )];

                $stats = $stats->setStatus( 3 );

                if( isset( $_POST['options'] ) ) {
                    parse_str( $_POST['options'], $filters );
                    if( isset( $filters['data']['year'] ) ) {
                        $year = $filters['data']['year'];
                    }

                    if( isset( $filters['data']['month'] ) ) {
                        $month = $filters['data']['month'];
                    }
                }

                $results += array_map( function( $v ) {
                    return [ $v->date, $v->sum, $v->total ];
                }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );
            }

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_owner':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = stats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', 'Sales', 'Earnings' ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->sales, $v->sum ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_users':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = usersStats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ), t( 'Surveyor' ), t( 'Verified' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total, $v->surveyor, $v->verified ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_commissions':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = stats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ), t( 'Commissions' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total, $v->sum ];
            }, $stats->setTypeId( 6 )->setStatus( 2 )->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_wcommissions':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = stats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ), t( 'Commissions' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total, $v->sum ];
            }, $stats->setTypeId( 8 )->setStatus( 2 )->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_deposits':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = stats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ), t( 'Commissions' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total, $v->sum ];
            }, $stats->setTypeId( 1 )->setStatus( 2 )->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_subscriptions':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats = subscriptionsStats();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_surveys':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats      = surveysStats();
            $options    = \util\etc::formFilterOptions();

            if( isset( $options['category'] ) ) {
                $stats->setCategoryId( (int) $options['category'] );
            }

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', t( 'Total' ) ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_responses':
            list( $year, $month ) = [date( 'Y' ), date( 'm' )];

            $stats      = responsesStats();
            $options    = \util\etc::formFilterOptions();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            $results[]  = [ '', 'Total' ];

            $results += array_map( function( $v ) {
                return [ $v->date, $v->total ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;

        case 'reportings_survey':
            list( $survey, $year, $month ) = [$_POST['data']['survey'] ?? NULL, date( 'Y' ), date( 'm' )];

            $user_survey    = me()->selectSurvey( (int) $survey );
            if( !$user_survey ) return ;

            $survey = surveys( $survey );
            if( !$survey->getObject() )
            return ;

            $viewCommissions = me()->manageSurvey( 'view-advanced-results' );

            $stats  = earningsStats()->setSurveyId( $survey->getId() )->setStatus( 3 )->setIncludeCommissions();

            if( isset( $_POST['options'] ) ) {
                parse_str( $_POST['options'], $filters );
                if( isset( $filters['data']['year'] ) ) {
                    $year = $filters['data']['year'];
                }

                if( isset( $filters['data']['month'] ) ) {
                    $month = $filters['data']['month'];
                }
            }

            $results    = [];
            // head
            if( $viewCommissions ) {
                $results[]  = [ '', t( 'Commissions' ), t( 'Responses' ) ];
            } else {
                $results[]  = [ '', t( 'Responses' ) ];
            }

            $results += array_map( function( $v ) use ( $viewCommissions ) {
                if( $viewCommissions ) return [ $v->date, $v->sum, $v->responses_done ];
                else return [ $v->date, $v->responses_done ];
            }, $stats->reportYearMonth( $year, $month, NULL, true )->autoFillDates() );

            return cms_json_encode( array_values( $results ) );
        break;
    }
});