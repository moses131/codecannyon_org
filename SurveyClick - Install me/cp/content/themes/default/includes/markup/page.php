<?php

namespace admin\markup;

class page {

    private $markup;
    private $callbacks;
    private $result = [];

    function __construct( string $type = '' ) {
        $id     = $_POST['options']['id'] ?? NULL;
        $type   = $_POST['options']['type'] ?? 'website';
        
        if( $id == 'new' ) {
            $this->page( $type );
        } else if( is_numeric( $id ) ) {
            $page = pages( $id );
            if( $page->getObject() )
            $this->page( $page->getType(), $page );
        }
    }

    private function page( string $type, object $page = NULL ) {
        $uqid       = 'blocks_' . uniqid();
        $builder    = new \dev\builder\blocks;

        try {
            if( $page ) {
            $builder->setPage( $page );
            $builder->dev()
                    ->checkType();
            } else
            $builder->setType( $type );
        }

        catch( \Exception $e ) {
            return false;
        }

        if( $page )
        $markup = '
        <form id="add_block" class="form add_block_form h100" data-ajax="' . ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'edit-page', 'page' => $page->getId() ] ) . '">';
        else
        $markup = '
        <form id="add_block" class="form add_block_form h100" data-ajax="' . ajax()->get_call_url( 'website-form-actions', [ 'action2' => 'add-page', 'type' => $type ] ) . '">';
        $markup .= '
        <div class="table-col">
            <div class="table t2">
                <ul class="blocks sortable ' . $uqid . '">';
                $markup .= $builder->viewBlocks();
                $markup .= '
                </ul>
                <ul class="blocks b2">
                    <li class="new">
                        <div class="head">
                            <a href="#">
                                <span><i class="fas fa-plus"></i></span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>';

        $fields = [
            'title'     => [ 'type' => 'text', 'label' => t( 'Page title' ), 'position' => 1, 'required' => 'required' ],
            'slug'      => [ 'type' => 'text', 'label' => t( 'Slug' ), 'description' => ( $page ? site_url( '<span>' . esc_html( $page->getSlug() ) . '</span>' ) : site_url( '<span>' . t( 'Your+Slug' ) . '</span>' ) ), 'position' => 2, 'required' => 'required' ],
            'lang'      => [ 'type' => 'select', 'label' => t( 'Language' ), 'position' => 3, 'options' => array_map( function( $language ) {
                return esc_html( $language['name_en'] );
              }, getLanguages() ) ],
            'template'  => [ 'type' => 'select', 'label' => t( 'Template' ), 'position' => 4, 'options' => $builder->dev()->getTemplates() ],
            'thumb'     => [ 'type' => 'image', 'label' => t( 'Thumbnail' ), 'position' => 5, 'category' => 'post-thumb' ],
            'meta'      => [ 'type' => 'dropdown', 'label' => t( 'Meta tags' ), 'grouped' => false, 'fields' => [ 'meta' => [
                'label' => t( 'Custom meta tags' ),
                'fields' => [
                    'title' => [ 'type' => 'text', 'label' => t( 'Title' ) ],
                    'keys'  => [ 'type' => 'text', 'label' => t( 'Keywords' ) ],
                    'mdesc' => [ 'type' => 'textarea', 'label' => t( 'Description' ) ]
                ]
            ] ], 'position' => 199 ]
        ];

        $builder->dev()
                ->manageFields( $fields );

        $form   = new \markup\front_end\form_fields( $fields );
        $form   ->changeInputName( 'page' );
        $values = [];

        if( $page ) {
            $values   = [
                'title'     => $page->getTitle(),
                'slug'      => $page->getSlug(),
                'lang'      => $page->getLanguageId(),
                'template'  => $page->getTemplate(),
                'meta'      => [
                    'title' => $page->getMetaTitle(),
                    'keys'  => $page->getMetaKeywords(),
                    'mdesc' => $page->getMetaDesc()
                ]
            ];

            if( isset( $fields['thumb'] ) && ( $thumbs = $page->getThumbnails() ) && !empty( $thumbs ) )
            $values['thumb'] = $thumbs;
        } else
            $values = [
                'template'  => $builder->dev()->getDefaultTemplate()
            ];

        $builder->dev()
                ->manageValues( $values );
        $form   ->setValues( $values );

        $fields = $form->build();

        $markup .= '
            <div class="table t2">
                <div class="fx">
                    <button class="btn goldbtn">' . t( 'Save' ) . '</button>
                    <div>';
                        if( $page )
                        $markup .= '<a href="' . $page->getPermalink() . '" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
                    $markup .= '
                    </div>
                </div>
                <div' . $form->formAttributes() . '>';    
                $markup .= $fields;
                $markup .= '
                </div>
            </div>
        </form>';

        $this->markup = $markup;
        $this->result['load_scripts'] = [ site_url( SCRIPTS_DIR . '/blocks_builder.js' ) => '{
            "callback": "init_blocks",
            "type": "' . $type . '",
            "container": ".' . $uqid . '"
        }',
        admin_url( 'assets/js/jquery-ui.js', true ) => '{
            "callback": "initPageSort"
        }' ];
        $this->result['menu_link'] = $builder->getType() . '_add';
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