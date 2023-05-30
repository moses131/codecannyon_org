<?php

class cms_content {

    private $sections;

    function __construct() {
        $this->sections = [
            'index'         => [ $this, 'index' ],
            'surveys'       => [ $this, 'surveys' ],
            'favorites'     => [ $this, 'favorites' ],
            'saved'         => [ $this, 'saved' ],
            'payouts'       => [ $this, 'payouts' ],
            'reportings'    => [ $this, 'reportings' ],
            'transactions'  => [ $this, 'transactions' ],
            'my-responses'  => [ $this, 'my_responses' ]
        ];

        if( me()->isModerator() )
        $this->sections = $this->sections + [
            'kyc'           => [ $this, 'kyc' ],
        ];

        if( me()->isAdmin() )
        $this->sections = $this->sections + [
            'pages'         => [ $this, 'pages' ],
            'page'          => [ $this, 'page' ],
            'users'         => [ $this, 'users' ],
            'subscriptions' => [ $this, 'subscriptions' ],
            'teams'         => [ $this, 'teams' ],
            'shop-categories' => [ $this, 'shop_categories' ],
            'shop-items'    => [ $this, 'shop_items' ],
            'shop-orders'   => [ $this, 'shop_orders' ],
            'vouchers'      => [ $this, 'vouchers' ],
            'menus'         => [ $this, 'menus' ],
            'categories'    => [ $this, 'categories' ],
            'countries'     => [ $this, 'countries' ],
            'actions'       => [ $this, 'admin_actions' ]
        ];

        if( me()->isAdmin() || me()->isSurveyor() )
        $this->sections = $this->sections + [
            'survey'        => [ $this, 'survey' ],
            'invoices'      => [ $this, 'invoices' ],
            'receipts'      => [ $this, 'receipts' ],
            'manage-subscriptions' => [ $this, 'manage_subscriptions' ]
        ];

        if( me()->isSurveyor() )
        $this->sections = $this->sections + [
            'pending-responses' => [ $this, 'pending_responses' ],
            'upgrade'           => [ $this, 'upgrade' ]
        ];

        if( me()->myTeam() )
        $this->sections = $this->sections + [
            'myteam'        => [ $this, 'myteam' ]
        ];

        if( me()->isOwner() )
        $this->sections = $this->sections + [
            'themes'        => [ $this, 'themes' ],
            'plugins'       => [ $this, 'plugins' ]
        ];

        if( me()->viewAs == 'respondent' )
        $this->sections = $this->sections + [
            'shop'          => [ $this, 'shop' ],
            'orders'        => [ $this, 'shop_my_orders' ]
        ];
    }

    public function getSection( string $section ) {
        $path   = explode( '/', $section );
        $page   = array_shift( $path );

        if( isset( $this->sections[$page] ) && is_callable( $this->sections[$page] ) && ( $result = call_user_func( $this->sections[$page], ...$path ) ) ) {
            if( !empty( $result['content'] ) || !empty( $result['callback'] ) )
            return $result;
        }
        return $this->noResults();
    }

    public function getSectionJson( string $section ) {
        return cms_json_encode( $this->getSection( $section ) );
    }

    private function noResults() {
        $result = [];
        ob_start();
        require_once admin_dir( '404.php' );
        $result['content'] = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }

    private function index() {
        $dashboard              = new \admin\markup\dashboard;
        $result['content']      = $dashboard->markup();
        $result['callbacks']    = $dashboard->callbacks();
        $result['menu_link']    = 'dashboard';
        $result                 = $dashboard->result( $result );

        return $result;
    }

    private function surveys() {
        $surveys                = new \admin\markup\surveys;
        $result['content']      = $surveys->markup();
        $result['callbacks']    = $surveys->callbacks();
        $result['menu_link']    = 'surveys';
        $result                 = $surveys->result( $result );

        return $result;
    }

    private function survey() {
        $surveys                = new \admin\markup\survey;
        $result['content']      = $surveys->markup();
        $result['callbacks']    = $surveys->callbacks();
        $result                 = $surveys->result( $result );

        return $result;
    }

    private function invoices() {
        $surveys                = new \admin\markup\invoices;
        $result['content']      = $surveys->markup();
        $result['callbacks']    = $surveys->callbacks();
        $result['menu_link']    = 'invoices';
        $result                 = $surveys->result( $result );

        return $result;
    }

    private function receipts() {
        $surveys                = new \admin\markup\receipts;
        $result['content']      = $surveys->markup();
        $result['callbacks']    = $surveys->callbacks();
        $result['menu_link']    = 'receipts';
        $result                 = $surveys->result( $result );

        return $result;
    }

    private function manage_subscriptions() {
        $items = new \admin\markup\table( [
            '<div class="w80"></div>'   => 'wa',
            t( 'Name' )                 => 'tl w150p',
            ''                          => '' 
        ] );
        
        $items
        ->title( t( 'Manage subscriptions' ) )
        ->placeholder( true )
        ->add( '{count}', 'wa sav' )
        ->add( '{name}', 'w150p' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup = $items->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "manage_subscriptions",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'manage_subscriptions';

        return $result;
    }

    private function countries() {
        $surveys                = new \admin\markup\countries;
        $result['content']      = $surveys->markup();
        $result['callbacks']    = $surveys->callbacks();
        $result['menu_link']    = 'ws_countries';
        $result                 = $surveys->result( $result );

        return $result;
    }

    private function favorites() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'value' => '', 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="favorites_list" class="form list_form favorites_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $favorites = new \admin\markup\table( [
            '<div class="w80"></div>'   => 'wa', 
            t( 'Name' )                 => 'tl w150p', 
            t( 'Category' )             => '', 
            t( 'Status' )               => '', 
            t( 'Budget' )               => '', 
            ''                          => '' 
        ] );
        
        $favorites
        ->title( t( 'Favorites' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa df sav' )
        ->add( '{name}', 'tl w150p' )
        ->add( '{category}' )
        ->add( '{status}' )
        ->add( '{budget}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $favorites->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "favorites",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'favorites';

        return $result;
    }

    private function saved() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'value' => '', 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) )
        $form->setValues( $_POST['options'] );

        $fields = $form->build();
        $markup .= '<form id="saved_surveys_list" class="form list_form saved_surveys_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $saved = new \admin\markup\table( [
            t( 'Survey' )       => 'tl w150p',
            t( 'Category' )     => '',
            t( 'Commission' )   => 'tc',
            t( '<i class="fas fa-star cl3"></i>' ) => 'tc', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $saved
        ->title( t( 'Saved' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{survey}', 'w150p' )
        ->add( '{category}', '' )
        ->add( '{commission}', 'tc' )
        ->add( '{stars}', 'tc' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $saved->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "saved",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'saved';

        return $result;
    }

    private function payouts() {
        $payouts                = new \admin\markup\payouts;
        $result['content']      = $payouts->markup();
        $result['callbacks']    = $payouts->callbacks();
        $result['menu_link']    = 'payouts';
        $result                 = $payouts->result( $result );

        return $result;
    }

    private function transactions() {
        $transactions           = new \admin\markup\transactions;
        $result['content']      = $transactions->markup();
        $result['callbacks']    = $transactions->callbacks();
        $result['menu_link']    = 'transactions';
        $result                 = $transactions->result( $result );

        return $result;
    }

    private function reportings( ...$attrs ) {
        $reportings             = new \admin\markup\reportings;
        $result['content']      = $reportings->markup();
        $result['callbacks']    = $reportings->callbacks();
        $result                 = $reportings->result( $result );
        
        return $result;
    }

    private function users() {
        $markup = '<div class="filters">';

        $viewo          = [];
        $viewo['0']     = t( 'All' );
        $viewo['sur']   = t( 'Surveyors' );
        $viewo['team']  = t( 'Team members' );
        $viewo['ban']   = t( 'Banned' );

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            [ 'type' => 'dropdown', 'grouped' => false, 'fields' => [ 'view' => [ 'grouped' => false, 'before_label' => '<i class="fas fa-user-check"></i>', 'label' => t( 'View' ), 'fields' => [
                'view' => [ 'type' => 'radio', 'options' => $viewo, 'view' => '0' ],
            ] ] ] ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ), 'last_act' => t( 'Last action &darr;' ), 'last_act_desc' => t( 'Last action &uarr;' ) ], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form users_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            '<div class="w60"></div>'   => 'wa', 
            t( 'Name' )                 => 'tl w150p', 
            t( 'ID' )                   => '', 
            t( 'Info' )                 => '', 
            t( 'Balance' )              => '', 
            t( 'Language' )             => '', 
            t( 'Country' )              => '', 
            ''                          => '' 
        ] );

        $at_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';
        $at_markup .= '<a href="#" class="btn mla" data-popup="manage-new" data-options=\'' . cms_json_encode( [ 'action' => 'add-user' ] ) .'\'>' . t( 'Add user' ) . '</a>';
        
        $users
        ->title( t( 'Users' ) )
        ->afterTitle( $at_markup )
        ->placeholder( true )
        ->add( '{avatar}', 'wa sav sav2' )
        ->add( '{name}', 'tl w150p' )
        ->add( '{id}' )
        ->add( '{info}' )
        ->add( '{balance}' )
        ->add( '{language}' )
        ->add( '{country}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "users",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'users';

        return $result;
    }

    private function categories() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $builder    = new \dev\builder\categories;
        if( $options && isset( $options['type'] ) )
        $builder    ->setType( $options['type'] );

        try {
            $builder->checkType();
        }

        catch( \Exception $e ) {
            return ;
        }

        if( !$builder->useCategories() ) return ;

        $markup     = '<div class="filters">';
        $filters    = [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $builder    ->manageFilters( $filters );
        $filters['type'] = [ 'type' => 'hidden', 'value' => $options['type'] ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="categories_list" class="form list_form categories_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $at_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';
        $at_markup .= '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . cms_json_encode( [ 'action' => 'add-category', 'type' => $options['type'] ] ) .'\'>' . t( 'Add category' ) . '</a>';

        $categories = new \admin\markup\table( $builder->getHeader() );
        
        $categories
        ->title( $builder->getTitle() )
        ->afterTitle( $at_markup )
        ->placeholder( true );

        $builder->getItems( $categories );

        $categories
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $categories->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "categories",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';

        $result['menu_link'] = $builder->getType() . '_cats';

        return $result;
    }

    private function pages() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $builder    = new \dev\builder\pages;
        if( $options && isset( $options['type'] ) )
        $builder    ->setType( $options['type'] );

        try {
            $builder->checkType();
        }

        catch( \Exception $e ) {
            return false;
        }

        $markup     = '<div class="filters">';
        $filters    = [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $builder    ->manageFilters( $filters );
        $filters['type'] = [ 'type' => 'hidden', 'value' => $options['type'] ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="pages_list" class="form list_form pages_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $at_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';
        $at_markup .= '<a href="' . admin_url( 'page/new/' . $options['type'] ) . '" class="btn mla" data-to="page" data-options=\'' . cms_json_encode( [ 'id' => 'new', 'type' => $options['type'] ] ) .'\'>' . $builder->addButton() . '</a>';

        $pages  = new \admin\markup\table( $builder->getHeader() );
        
        $pages
        ->title( $builder->getTitle() )
        ->afterTitle( $at_markup )
        ->placeholder( true );

        $builder->getItems( $pages );

        $pages
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $pages->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "pages",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = $builder->getType() . '_view';

        return $result;
    }

    private function page() {
        $page                   = new \admin\markup\page;
        $result['content']      = $page->markup();
        $result['callbacks']    = $page->callbacks();
        $result                 = $page->result( $result );

        return $result;
    }

    private function menus() {
        $options    = $_POST['options'] ?? [];

        $langs      = getLanguages();
        $currLang   = isset( $options['lang'] ) && isset( $langs[$options['lang']] ) ? $options['lang'] : getLanguage()['locale_e'];
        $menus      = menus()->getMenus( $currLang );
        $menuId     = isset( $options['menu'] ) &&  isset( $menus[$options['menu']] ) ? $options['menu'] : key( $menus );
        $menu       = $menus[$menuId];

        $markup     = '
        <div class="df t1-1">';
        $markup .= '
        <div>
            <div class="table t2 ns">
                <h2>' . t( 'Edit menus' ) . '</h2>';
                if( !empty( $menu['desc'] ) )
                $markup .= '<div class="td">' . esc_html( $menu['desc'] ) . '</div>';
                $markup .= '
                <div class="tr">
                    <div class="form_group">
                        <div class="form_line">
                            <select data-roc="' . admin_url( 'menus/menu/%R/lang/' . $currLang . '/' ) . '">';
                            array_walk( $menus, function( $menu, $id ) use ( $menuId, &$markup ) {
                                $markup .= '<option value="' . esc_html( $id ) . '"' . ( $id == $menuId ? ' selected' : '' ) . '>' . esc_html( $menu['name'] ) . '</option>';
                            } );
                            $markup .= '
                            </select>
                        </div>
                        <div class="form_line wa">
                            <select data-roc="' . admin_url( 'menus/menu/' . $menuId . '/lang/%R/' ) . '">';
                            array_walk( $langs, function( $info, $iso ) use ( $currLang, &$markup ) {
                                $markup .= '<option value="' . $iso . '"' . ( $currLang == $iso ? ' selected' : '' ) . '>' . $info['name_en'] . '</option>';
                            } );
                            $markup .= '
                            </select>
                        </div>
                    </div>
                </div>
                <div class="td">
                    <div>
                        <a href="#" class="btn" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'add-item-menu' ] ) ) . '\'>' . t( 'Add new' ) . '</a>
                    </div>
                </div>
            </div>

            <div class="table">
                <div class="tr nb np">
                    <div>
                        <form data-ajax="' . ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'save-menu' ] ) . '">
                            <ul id="menu" class="sortable">';
                            $markup .= menus()->getMenuLinksEdit( $menuId, $currLang );
                            $markup .= '
                            </ul>
                            <input type="hidden" name="data[menu]" value="' . $menuId . '" />
                            <input type="hidden" name="data[lang]" value="' . $currLang . '" />
                            <div class="df">
                                <button>' . t( 'Save' ) . '</button>
                                <a href="#" data-popup="website-options" data-options=\'' . ( cms_json_encode( [ 'action' => 'reset-menu', 'menu' => $menuId, 'lang' => $currLang ] ) ) . '\' class="mla asc">' . t( 'Reset menu' ) . '</a>
                            </div>
                        <form>
                    </div>
                </div>
            </div>
        </div>
        </div>';

        $result['content']  = $markup;
        $result['load_scripts'] = [ admin_url( 'assets/js/jquery-ui.js', true ) => '{
            "callback": "initSort"
        }' ];
        $result['menu_link'] = 'ws_menus';

        return $result;
    }

    private function themes() {
        $themes = new \admin\markup\table( [
            '<div class="w80"></div>'  
                            => 'wa', 
            t( 'Name' )     => 'tl w150p', 
            t( 'Version' )  => '', 
            t( 'Author' )   => '',
            ''              => '' 
        ] );
        
        $themes
        ->title( t( 'Themes' ) )
        ->afterTitle( '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'install-theme' ] ) ) . '\'>' . t( 'Install theme' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa sav' )
        ->add( '{name}', 'tl w150p' )
        ->add( '{version}' )
        ->add( '{author}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup = $themes->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "themes",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'ws_viewthemes';

        return $result;
    }

    private function plugins() {
        $plugins = new \admin\markup\table( [
            '<div class="w80"></div>'  
                            => 'wa', 
            t( 'Name' )     => 'tl w150p', 
            t( 'Version' )  => '', 
            t( 'Author' )   => '',
            ''              => '' 
        ] );
        
        $plugins
        ->title( t( 'Plugins' ) )
        ->afterTitle( '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'install-plugin' ] ) ) . '\'>' . t( 'Install plugin' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa sav' )
        ->add( '{name}', 'tl w150p' )
        ->add( '{version}' )
        ->add( '{author}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup = $plugins->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "plugins",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'ws_viewplgs';

        return $result;
    }

    private function vouchers() {
        $markup = '<div class="filters">';

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 1 => t( 'Available'), 0 => t( 'Disabled' ) ], 'placeholder' => t( 'Status' ) ],
            'exp'       => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 1 => t( 'Active'), 0 => t( 'Expired' ) ], 'placeholder' => t( 'Expiration' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup .= '<form id="pages_list" class="form list_form pages_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $vouchers = new \admin\markup\table( [
            t( 'Code' )         => 'tl w150p', 
            t( 'Applying' )     => '',
            t( 'User' )         => '', 
            t( 'Amount' )       => '',
            t( 'Limit' )        => '',
            t( 'Status' )       => '',
            t( 'Expiration' )   => '',
            ''                  => '' 
        ] );

        $at_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';
        $at_markup .= '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . ( cms_json_encode( [ 'action' => 'add-voucher' ] ) ) . '\'>' . t( 'Add voucher' ) . '</a>';
        
        $vouchers
        ->title( t( 'Vouchers' ) )
        ->afterTitle( $at_markup )
        ->placeholder( true )
        ->add( '{code}', 'tl w150p' )
        ->add( '{applying}' )
        ->add( '{user}' )
        ->add( '{amount}' )
        ->add( '{limit}' )
        ->add( '{status}' )
        ->add( '{expiration}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $vouchers->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "vouchers",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'ws_viewvouchers';

        return $result;
    }

    private function myTeam() {
        $myTeam = me()->myTeam();
        $markup = '
        <div class="df t1 fp">';
        $markup .= '
        <div class="table t2 dfc ns wa mb0 pra5 chat">            
            <div class="tr chat_actions">
                <form class="form w100p" enctype="multipart/form-data">
                    <div class="form_group">
                        <div class="form_line">
                            <input type="text" name="message" placeholder="' . t( 'Write here' ) . '" required />
                            <input type="hidden" name="team_id" value="' . me()->getTeamId() . '" />
                        </div>
                        <div class="form_line wa">
                            <button><i class="fas fa-arrow-down"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="oa w100p"></div>
            <div class="pagination">
                <a href="#" class="ma">' . t( 'Load more' ) . '</a>
            </div>
        </div>

        <div class="table t2 dfc ns wa mb0 pra5 mt20">
            <h2>' . t( 'Latest news' ) . '</h2>
            <div class="oa w100p">';
            $aReader = new \markup\back_end\team_news;
            foreach( $myTeam->actions()->fetch( 50 ) as $action ) {
                if( !( $alertContent = $aReader->readAlert( $action->text ) ) ) continue;
                $markup .= '
                <div class="td">
                    <div class="w100p">' . $alertContent . '</div>
                </div>';
            }
        $markup .= '
        </div>
        </div>';

        $result['content']  = $markup;        
        $result['callback'] = '{
            "callback": "init_team_chat",
            "el": ".chat",
            "msgs": "> .tr + div",
            "write": "> .chat_actions > form"
        }';
        $result['menu_link'] = 'team_room';

        return $result;
    }

    private function pending_responses() {
        $users = new \admin\markup\table( [
            t( 'Name' )     => 'tl w150p',
            t( 'Survey' )   => '',
            t( 'Country' )  => '',
            t( 'Duration' ) => 'tar',
            t( 'Date' )     => 'tar',
            ''              => '' 
        ] );
        
        $users
        ->title( t( 'Pending responses' ) )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{survey}', '' )
        ->add( '{country}', 'ico' )
        ->add( '{duration}', 'tar' )
        ->add( '{date}', 'tar' )
        ->add( '{options}', 'df' )
        ->save( 'template' );

        $uqid = 'table_' . uniqid();
        $markup = $users->markup( $uqid );

        $result['content'] = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "pending_responses",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'pending_responses';

        return $result;
    }

    private function subscriptions() {
        $markup = '<div class="filters">';

        $plans  = new \query\plans\plans;
        $plans  = $plans->fetch( -1 );

        $form = new \markup\front_end\form_fields( [
            'plan'      => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-on"></i>', 'options' => ( [ '' => t( 'Any' ) ] + array_map( function( $v ) {
                return esc_html( $v->name );
            }, $plans ) ), 'placeholder' => t( 'Plan' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ), 'lr' => t( 'Last renew &darr;' ), 'lr_desc' => t( 'Last renew &uarr;' ), 'expiration' => t( 'Expiration &darr;' ), 'expiration_desc' => t( 'Expiration &uarr;' ) ], 'value' => '', 'placeholder' => t( 'Order by' ) ]
        ] );

        if( isset( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form options_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            t( 'Plan' )         => 'tl w150p',
            t( 'User' )         => '',
            t( 'Expiration' )   => '',
            t( 'Auto-renew' )   => '',
            t( 'Last renew' )   => '',
            t( 'Date' )         => '',
            ''                  => '' 
        ] );

        $at_markup = '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' ;
        $at_markup .= '<a href="#" class="btn mla" data-popup="website-actions" data-data=\'' . cms_json_encode( [ 'action' => 'add-subscription' ] ) .'\'>' . t( 'Add subscription' ) . '</a>';
        
        $users
        ->title( t( 'Subscriptions' ) )
        ->afterTitle( $at_markup )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{user}', '' )
        ->add( '{expiration}', '' )
        ->add( '{autorenew}', '' )
        ->add( '{lastrenew}', '' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );

        $uqid   = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "subscriptions",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'subscriptions';

        return $result;
    }

    private function teams() {
        $markup = '<div class="filters">';

        $viewo          = [];
        $viewo['0']     = t( 'All' );
        $viewo['sur']   = t( 'Surveyors' );
        $viewo['team']  = t( 'Team members' );
        $viewo['ban']   = t( 'Banned' );

        $form = new \markup\front_end\form_fields( [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ) ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' ) ], 'placeholder' => t( 'Order by' ) ],
        ] );

        if( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
            $form->setValues( $_POST['options'] );
        }

        $fields = $form->build();
        $markup .= '<form id="surveys_list" class="form list_form users_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            t( 'Name' )         => 'tl w150p', 
            t( 'Owner' )        => '', 
            t( 'Members' )      => '',
            t( 'Date' )         => '', 
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'Teams' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{name}', 'tl w150p' )
        ->add( '{owner}' )
        ->add( '{members}' )
        ->add( '{date}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "teams",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'teams';

        return $result;
    }

    private function admin_actions() {
        $users = new \admin\markup\table( [
            t( 'Action' )   => 'tl', 
            ''              => '' 
        ] );
        
        $users
        ->title( t( 'Admin actions' ) )
        ->placeholder( true )
        ->add( '{action}' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup = $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "admin_actions",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'actions';

        return $result;
    }

    private function kyc() {
        $users = new \admin\markup\table( [
            t( 'User' )         => 'tl w150p', 
            t( 'Date' )         => '', 
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'KYC Verification' ) )
        ->placeholder( true )
        ->add( '{user}', 'w150p' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup = $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "kyc",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'users_kyc';

        return $result;
    }


    private function upgrade() {
        $pricing    = new \admin\markup\pricing_table;
        $markup     = '<div class="mb40">';
        $markup     .= $pricing->markup();
        $markup     .= '</div>';

        $result['content']  = $markup;
        $result['menu_link']  = 'upgrade';

        return $result;
    }

    private function shop_categories() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $markup     = '<div class="filters">';
        $filters    = [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="shop_categories_list" class="form list_form shop_categories_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '<a href="#" class="btn mla" data-popup="manage-shop" data-data=\'' . cms_json_encode( [ 'action' => 'add-category' ] ) .'\'>' . t( 'Add category' ) . '</a>';
        $markup .= '</div>';

        $categories = new \admin\markup\table( [
            t( 'Name' )         => 'tl w150p',
            t( 'Country' )      => '', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $categories
        ->title( t( 'Shop categories' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{name}', 'w150p' )
        ->add( '{country}', '' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $categories->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "shop_categories",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'ls_categories';

        return $result;
    }

    private function shop_items() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $markup     = '<div class="filters">';
        $filters    = [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="shop_items_list" class="form list_form shop_items_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '<a href="#" class="btn mla" data-popup="manage-shop" data-data=\'' . cms_json_encode( [ 'action' => 'add-item' ] ) .'\'>' . t( 'Add item' ) . '</a>';
        $markup .= '</div>';

        $items = new \admin\markup\table( [
            '<div class="w60"></div>'   => 'wa',
            t( 'Name' )         => 'tl w150p',
            t( 'Price' )        => '',
            t( 'Stock' )        => '', 
            t( 'Purchases' )    => '', 
            t( 'Country' )      => '', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $items
        ->title( t( 'Shop items' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{image}', 'wa sav sav2' )
        ->add( '{name}', 'w150p' )
        ->add( '{price}', '' )
        ->add( '{stock}', '' )
        ->add( '{purchases}', '' )
        ->add( '{country}', '' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $items->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "shop_items",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'ls_items';

        return $result;
    }

    private function shop_orders() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $markup     = '<div class="filters">';
        $filters    = [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Approved' ), 1 => t( 'Pending' ), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="shop_orders_list" class="form list_form shop_orders_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            '#'                 => '',
            t( 'User' )         => 'tl w150p',
            '<div></div>'       => 'tc',
            t( 'Amount' )       => '', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'Shop orders' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{id}' )
        ->add( '{user}', 'w150p' )
        ->add( '{status}', 'tc' )
        ->add( '{amount}', '' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "shop_orders",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = ( isset( $options['status'] ) && $options['status'] == 1 ? 'ls_pending' : 'ls_orders' );

        return $result;
    }

    private function my_responses() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $markup     = '<div class="filters">';
        $filters    = [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 3 => t( 'Finished' ), 1 => t( 'Pending' ), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="my_responses_list" class="form list_form my_responses_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $myresults = new \admin\markup\table( [
            t( 'Survey' )       => 'tl w150p',
            '<div></div>'       => 'tc',
            t( 'Commission' )   => 'tc',
            t( '<i class="fas fa-star cl3"></i>' ) => 'tc', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $myresults
        ->title( t( 'My responses' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{survey}', 'w150p' )
        ->add( '{status}', 'tc' )
        ->add( '{commission}', 'tc' )
        ->add( '{stars}', 'tc' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $myresults->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "my_responses",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = ( isset( $options['status'] ) && $options['status'] == 1 ? 'awaiting_completion' : 'answers' );

        return $result;
    }

    private function shop() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $categories = my_shop()->categories->fetch( -1 );
        $markup     = '<div class="filters">';
        $filters    = [
            'search'    => [ 'type' => 'text', 'after_label' => '<i class="fas fa-search"></i>', 'autocomplete' => 'off', 'placeholder' => t( 'Search' ), 'position' => 1 ],
            'category'  => [ 'type' => 'select', 'after_label' => '<i class="fas fa-tag"></i>', 'options' => array_map( function( $v ) { return esc_html( $v->name ); }, $categories ), 'value' => '', 'placeholder' => t( 'Category' ), 'position' => 2 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'name' => t( 'Name &darr;' ), 'name_desc' => t( 'Name &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 3 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $count  = shop()->cartCount();

        $fields = $form->build();
        $markup .= '<form id="shop_items_list" class="form list_form shop_items_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $at_markup = '
        <div class="shop-cart mla">
            <span class="bal">' . sprintf( t( '<span>You have</span><span>%s</span>' ), '<i class="fas fa-star cl3"></i> ' . me()->getLoyaltyPoints() ) . '</span>
            <a href="#" class="btn" data-popup="shop-cart" data-shop-cart="' . $count . '">
                <i class="fas fa-shopping-cart"></i>
                <span>' . t( 'Cart' ) . '</span>
                <span data-count-attr="' . $count . '"><span data-count>' . $count . '</span></span>
            </a>
        </div>
        
        <a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>';

        $items = new \admin\markup\table( [
            '<div class="w60"></div>'   
                                => 'wa',
            t( 'Name' )         => 'tl w150p',
            t( 'Price' ) . ' <i class="fas fa-star cl3"></i>' 
                                => 'tc',
            ''                  => '' 
        ] );

        $items
        ->title( t( 'Shop' ) )
        ->afterTitle( $at_markup )
        ->placeholder( true )
        ->add( '{image}', 'wa sav sav2' )
        ->add( '{name}', 'w150p' )
        ->add( '{price}', 'tc' )
        ->add( '{options}', 'df' )
        ->save( 'template' );

        $uqid   = 'table_' . uniqid();
        $markup .= $items->markup( $uqid );

        $result['content']  = $markup;

        $result['load_scripts'] = [ esc_url( site_url( [ SCRIPTS_DIR, 'shop.js' ] ) ) => '{
            "callback": "init_shop",
            "Items": ".' . $uqid . '"
        }' ];

        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "shop",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';

        $result['menu_link'] = ( isset( $options['category'] ) ? 'shop_cat_' . (int) $options['category'] : 'lp_shop_all' );

        return $result;
    }

    private function shop_my_orders() {
        $options    = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? $_POST['options'] : NULL;
        $markup     = '<div class="filters">';
        $filters    = [
            'status'    => [ 'type' => 'select', 'after_label' => '<i class="fas fa-toggle-off"></i>', 'options' => [ '' => t( 'All' ), 2 => t( 'Approved' ), 1 => t( 'Pending' ), 0 => t( 'Canceled' ) ], 'placeholder' => t( 'Status' ), 'position' => 1 ],
            'orderby'   => [ 'type' => 'select', 'after_label' => '<i class="fas fa-sort-amount-down"></i>', 'options' => [ 'id' => t( 'Date &darr;' ), 'id_desc' => t( 'Date &uarr;' )], 'placeholder' => t( 'Order by' ), 'position' => 2 ]
        ];
        $form       = new \markup\front_end\form_fields( $filters );

        if( $options )
        $form->setValues( $options );

        $fields = $form->build();
        $markup .= '<form id="shop_orders_list" class="form list_form shop_my_orders_list_form"' . $form->formAttributes() . '>';
        $markup .= $fields;
        $markup .= '</form>';
        $markup .= '</div>';

        $users = new \admin\markup\table( [
            '#'                 => '',
            '<div></div>'       => '',
            t( 'Amount' )       => '', 
            t( 'Date' )         => '',
            ''                  => '' 
        ] );
        
        $users
        ->title( t( 'My orders' ) )
        ->afterTitle( '<a href="#" class="show_filters btn"><i class="fas fa-arrow-up"></i> ' . t( 'Filters' ) . '</a>' )
        ->placeholder( true )
        ->add( '{id}' )
        ->add( '{status}', '' )
        ->add( '{amount}', '' )
        ->add( '{date}', '' )
        ->add( '{options}', 'df' )
        ->save( 'template' );
        
        $uqid   = 'table_' . uniqid();
        $markup .= $users->markup( $uqid );

        $result['content']  = $markup;
        $result['callback'] = '{
            "callback": "cms_populate_table",
            "table": "shop_my_orders",
            "options": "' . \util\etc::buildFilterOptions() . '",
            "class": "' . $uqid . '"
        }';
        $result['menu_link'] = 'orders';

        return $result;
    }

}