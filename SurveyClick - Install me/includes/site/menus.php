<?php

namespace site;

class menus extends \markup\front_end\nav {

    private $menus;

    function __construct() {
        global $menus;
        $menus = $this;
    }

    public function create( string $menu_id, string $name, string $description, array $links = [], string $lang = NULL ) {
        $this->menus[$menu_id][$lang ?? 'default'] = [
            'name'  => $name,
            'desc'  => $description,
            'links' => $links
        ];

        return filters()->add_filter( 'menu-links', [
            'link' => [
                'name'  => t( 'Link' ),
                'form'  => function( object $form, array $the_link = [], string $id = NULL ) {
                    if( !$id )
                    $id = uniqid();

                    $form->addFields( [
                        'links'     => [ 'type' => 'group', 'fields' => [
                        $id         => [ 'type' => 'group', 'fields' => [
                        'label'     => [ 'type' => 'text', 'label' => t( 'Label' ), 'required' => 'required' ],
                        'url'       => [ 'type' => 'text', 'label' => t( 'Link' ) ],
                        [
                            'type' => 'inline-group', 'grouped' => false, 'fields' => [
                                'target'    => [ 'type' => 'select',  'label' => t( 'Open' ), 'options' => [ '' => 'Self', '_blank' => t( 'New tab' ) ] ],
                                'class'     => [ 'type' => 'text',  'label' => t( 'Classes' ) ],
                                'id'        => [ 'type' => 'text',  'label' => t( 'ID' ) ]
                            ]
                        ],
                        'type'      => [ 'type' => 'hidden' ],
                        'parent_id' => [ 'type' => 'hidden' ]
                    ] ] ] ] ] );

                    $form->setValues( [
                        'links'     => [
                        $id         => [
                        'label'     => ( $the_link['label'] ?? '' ),
                        'url'       => ( $the_link['url'] ?? '' ),
                        'target'    => ( $the_link['target'] ?? '' ),
                        'class'     => ( $the_link['class'] ?? '' ),
                        'id'        => ( $the_link['id'] ?? '' ),
                        'type'      => 'link',
                        'parent_id' => ( $the_link['parent_id'] ?? '' )
                        ] ]
                    ] );

                    return $id;
                }
            ]
        ] );
    }

    public function getMenu( string $menu_id, string $classes = '', string $sclasses = '', string $lang = NULL ) {
        if( !isset( $this->menus[$menu_id] ) )
        return ;

        $menu = $this->getMenuInfo( $menu_id, $lang );

        return $this->buildMenu( $menu['links'], $classes, $sclasses );
    }

    private function buildMenu( array $links, string $class = '', string $sclasses = '' ) {
        $this->nav = $links;
        return $this->markup( $class, $sclasses );
    }

    public function getMenus( string $lang = NULL ) {
        $menus      = $this->menus;
        $exp_menus  = [];
        $lang       = $lang ?? getLanguage()['locale_e'];

        foreach( $menus as $menu_id => $menu ) {
            if( isset( $menu[$lang] ) )
            $exp_menus[$menu_id] = $menu[$lang];
            else if( isset( $menu['default'] ) )
            $exp_menus[$menu_id] = $menu['default'];
        }

        return $exp_menus;
    }

    public function getMenuInfo( string $menu_id, string $lang = NULL ) {
        if( !isset( $this->menus[$menu_id] ) )
        return ;

        $lang   = $lang ?? getLanguage()['locale_e'];
        $option = get_option( $menu_id . ':' . $lang, false );

        if( $option )
        return json_decode( $option, true );
        else if( isset( $this->menus[$menu_id][$lang] ) )
        return $this->menus[$menu_id][$lang];
        else if( isset( $this->menus[$menu_id]['default'] ) )
        return $this->menus[$menu_id]['default'];

        return ;
    }

    public function getMenuLinks( string $menu_id, string $lang = NULL ) {
        $info = $this->getMenuInfo( $menu_id, $lang );
        if( !$info )
        return ;

        return $this->linksTree( ( $info['links'] ?? [] ) );
    }

    private function menuLinksEdit( array $links ) {
        $markup = '';
        $types  = $this->getLinkTypes();

        foreach( $links as $link_id => $link ) {
            $markup .= '
            <li class="form_line form_dropdown" id="' . $link_id . '">
                <div>
                    <span>' . esc_html( $link['label'] ) . '</span>
                    <i class="fas fa-angle-down"></i>
                </div>
                <div>';
                    $form = new \markup\front_end\form_fields;
                    if( isset( $types[$link['type']] ) )
                    call_user_func( $types[$link['type']]['form'], $form, $link, $link_id );

                    $fields = $form->build();            
                    $markup .= $fields;
                    $markup .= '
                    <div class="df mt25">
                        <a href="#" class="mla btn delcli">' . t( 'Delete' ) . '</a>
                    </div>
                </div>
                <ul class="sortable">' . ( !empty( $link['childs'] ) ? $this->menuLinksEdit( $link['childs'] ): '' ) . '</ul>
            </li>';
        }

        return $markup;
    }

    public function getMenuLinksEdit( string $menu_id, string $lang = NULL ) {
        $links = $this->getMenuLinks( $menu_id, $lang );
        if( !$links )
        return ;

        return $this->menuLinksEdit( $links );
    }

    /** DEV */

    public function getLinkTypes() {
        return filters()->do_filter( 'menu-links', [] );
    }

}