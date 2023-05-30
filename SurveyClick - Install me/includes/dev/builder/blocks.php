<?php

namespace dev\builder;

class blocks extends \util\db {

    private $type;
    private $page;
    private $dev;
    private $content    = [];
    private $currentBlock;
    private $currentBlockId;

    function __construct() {
        $this->dev = new pages;
    }

    public function setType( string $type ) {
        $this->type = $type;
        $this->dev  ->setType( $this->type )
                    ->checkType();
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setPage( object $page, array $content = NULL ) {
        if( !$page->getObject() )
        throw new \Exception( t( 'Invalid page' ) );

        $this->page = $page;
        $this->type = $page->getType();
        $this->dev  ->setType( $this->type )
                    ->setObject( $page );

        if( $content ) $this->content = $content;
        else $this->setContent( $page->getText() );

        return $this;
    }

    public function setContent( string $content ) {
        $this->content  = json_decode( $content, true );
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setCurrentBlock( array $block, string $blockId ) {
        $this->currentBlock     = $block;
        $this->currentBlockId   = $blockId;
        return $this;
    }

    public function getCurrentBlock() {
        return $this->currentBlock;
    }

    public function getCurrentBlockId() {
        return $this->currentBlockId;
    }

    public function dev() {
        return $this->dev;
    }

    public function getBlock( string $id = NULL, array $blockData = [] ) {
        $blockId    = $id ?? uniqid();
        $markup     = '
        <li' . ( !$id ? ' class="open"' : '' ) . '>
            <div class="head">
                <a href="#"><span contenteditable="true" spellcheck="false">' . ( !empty( $blockData['_label'] ) ? esc_html( $blockData['_label'] ) : esc_html( $this->currentBlock['label'] ) ) . '</span><i class="fas fa-chevron-down"></i></a>
                <div>
                    <a href="#" class="move"><i class="fas fa-arrows-alt-v"></i></a>
                </div>
                <div class="more">
                    <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                </div>
            </div>';

            $fields     = call_user_func( $this->currentBlock['options'], $blockId );
            $form       = new \markup\front_end\form_fields( $fields );
            if( !empty( $blockData ) )
            $form       ->setValues( [ $blockId => $blockData ] );
            $fields     = $form->build();
            $markup    .= '<div class="content"' . $form->formAttributes() . '>';
            $markup    .= $fields;
            $markup    .= '
            </div>
        </li>';

        return $markup;
    }

    public function viewBlocks() {
        $blocks = developer()->blocksForType( $this->type );
        $markup = '';

        if( empty( $this->content ) ) {
            if( isset( $blocks['text'] ) ) {
                $this->currentBlock     = $blocks['text'];
                $this->currentBlockId   = 'text';
                $markup .= $this->getBlock();
            }
        } else {
            foreach( $this->content as $blockId => $blockData ) {
                $type = $blockData['_type'] ?? NULL;
                if( $type && isset( $blocks[$type] ) ) {
                    $this->currentBlock     = $blocks[$type];
                    $this->currentBlockId   = $blockId;
                    $markup .= $this->getBlock( $blockId, $blockData );
                }
            }
        }

        return $markup;
    }

    public function buildBlocks( array $blocks ) {
        $dev_blocks = developer()->blocksForType( $this->type );
        $saveBlocks = [];

        foreach( $blocks as $blockId => $block ) {
            if( isset( $block['_type'] ) ) {
                $the_block = $dev_blocks[$block['_type']] ?? NULL;
                if( $the_block ) {
                    $fields     = call_user_func( $the_block['options'], $blockId );
                    $form       = new \markup\front_end\form_fields( $fields );
                    if( isset( $this->content[$blockId] ) )
                    $form       ->setValues( [ $blockId => $this->content[$blockId] ] );
                    $form       ->build();
                    $media      = $form->uploadFiles( [ $blockId => $block ] );
                    $saveBlocks += $form->getValuesArray();
                }
            }
        }

        // Deleted blocks
        $del_blocks = array_diff_key( $this->content, $saveBlocks );

        if( !empty( $del_blocks ) ) {
            foreach( $del_blocks as $blockId => $block ) {
                if( isset( $block['_type'] ) ) {
                    $the_block = $dev_blocks[$block['_type']] ?? NULL;
                    if( $the_block ) {
                        $fields     = call_user_func( $the_block['options'], $blockId );
                        $form       = new \markup\front_end\form_fields( $fields );
                        $form       ->setValues( [ $blockId => $block ] );
                        $form       ->build();
                        $form       ->uploadFiles( [ $blockId => [] ]);
                    }
                }
            }
        }

        return cms_json_encode( $saveBlocks );
    }

    public function render() {
        $blocks = developer()->blocksForType( $this->type );
        $markup = '';

        foreach( $this->content as $blockId => $blockData ) {
            $type = $blockData['_type'] ?? NULL;
            if( $type && isset( $blocks[$type] ) ) {
                $this->currentBlock     = $blocks[$type];
                $this->currentBlockId   = $blockId;
                $markup .= call_user_func( $this->currentBlock['render'], $blockData );
            }
        }

        return $markup;
    }

}