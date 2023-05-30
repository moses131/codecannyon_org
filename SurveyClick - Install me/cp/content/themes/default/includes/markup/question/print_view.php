<?php

namespace admin\markup\question;

class print_view {

    private $question;

    function __construct( \query\survey\questions $question ) {
        $this->question = $question;
    }

    private function answers( $value ) {
        switch( $this->question->getType() ) {
            case 'net_prom':
                $value = $value['int_group'][0] ?? NULL;
                if( !$value ) return ;
                $markup = '
                <div>';
                if( $value <= 6 ) {
                    $markup .= t( 'Detractor' ) . ' (' . $value . ')';
                } else if( $value <= 8 ) {
                    $markup .= t( 'Passive' ) . ' (' . $value . ')';
                } else {
                    $markup .= t( 'Promoter' ) . ' (' . $value . ')';
                }
                $markup .= '</div>';
                return $markup;
            break;
            
            case 'srating':
                $value = $value['int_group'][0] ?? NULL;
                if( !$value ) return ;

                $setting    = $this->question->getSetting();
                $stars      = $setting['srating_setting']['stars'] ?? 10;
                $stars      = (int) $stars > 10 || (int) $stars < 1 ? 10 : (int) $stars;
                $rest       = $stars - $value;
                $markup     = '<div class="rating">';
                $markup     .= str_repeat( '<i class="fas fa-star gold"></i>', $value );
                if( $rest ) {
                    $markup .= str_repeat( '<i class="fas fa-star"></i>', $rest );
                }
                $markup     .= '</div>';
                return $markup;
            break;

            case 'checkbox':
                $value = $value['int_group'][0] ?? NULL;
                if( $value === NULL ) return ;
                $markup     = '<div>';
                $markup     .= ( $value ? t( 'Yes' ) : t( 'No' ) );
                $markup     .= '</div>';
                return $markup;
            break;

            case 'slider':
                $value = $value['int_group'][0] ?? NULL;
                if( $value === NULL ) return ;
                $markup     = '<div>';
                $markup     .= (int) $value;
                $markup     .= '</div>';
                return $markup;
            break;

            case 'matrix_rs':
                $value = $value['int_cascade'] ?? NULL;
                if( empty( $value ) ) return ;
                $labels = $this->question->getLabels( 1 )->fetch( -1 );
                $markup = '<div>';
                foreach( $value as $optId => $rating ) {
                    if( isset( $labels[$optId] ) ) {
                        $markup     .= '<div class="mb10">' . esc_html( $labels[$optId]->title ) . '</div>';
                        $stars      = 5;
                        $rest       = $stars - $rating;
                        $markup     .= '<div class="rating mb10">';
                        $markup     .= str_repeat( '<i class="fas fa-star gold"></i>', $rating );
                        if( $rest ) {
                            $markup .= str_repeat( '<i class="fas fa-star"></i>', $rest );
                        }
                        $markup     .= '</div>';
                    }
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'matrix_mc':
                $value = $value['int_cascade'] ?? NULL;
                if( empty( $value ) ) return ;
                $labels = $this->question->getLabels( 1 )->fetch( -1 );
                $columns= $this->question->getLabels( 2 )->fetch( -1 );
                $markup = '<div class="alv">';
                foreach( $value as $labId => $optId ) {
                    if( isset( $labels[$labId] ) ) {
                        $markup .= '<div><h3>' . esc_html( $labels[$labId]->title ) . '</h3>';
                        if( isset( $columns[$optId] ) ) {
                            $markup .= '<div><h3 class="a"><span>' . esc_html( $columns[$optId]->title ) . '</span></h3></div>';
                        }
                        $markup .= '</div>';
                    }
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'matrix_dd':
                $value = $value['int_cascade'] ?? NULL;
                if( empty( $value ) ) return ;
                $labels = $this->question->getLabels( 1 )->fetch( -1 );
                $columns= $this->question->getLabels( 2 );
                $cols   = $columns->fetch( -1 );
                $options= [];
                $markup = '<div class="alv">';
                foreach( $value as $labId => $vals ) {
                    if( isset( $labels[$labId] ) ) {
                        $markup .= '<div><h3>' . esc_html( $labels[$labId]->title ) . '</h3>';
                        foreach( $vals as $colId => $optId ) {
                            if( isset( $cols[$colId] ) ) {
                                $columns->setObject( $cols[$colId] );
                                $markup .= '<div><h3>' . esc_html( $columns->getTitle() ) . '</h3>';
                                if( !isset( $options[$colId] ) )
                                $options[$colId] = $columns->getOptions()->fetch( -1 );
                                if( isset( $options[$colId][$optId] ) ) {
                                    $markup .= '<div><h3 class="a"><span>' . esc_html( $options[$colId][$optId]->title ) . '</span></h3></div>';
                                }
                                $markup .= '</div>';
                            }
                        }
                        $markup .= '</div>';
                    }
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                $value = $value['int_group'] ?? NULL;
                if( !$value ) return ;
                $options    = $this->question->getOptions()->fetch( -1 );
                $markup     = '<div>';
                foreach( $value as $val ) {
                    if( isset( $options[$val] ) ) {
                        $markup .= '<div>' . esc_html( $options[$val]->title ) . '</div>';
                    }
                }
                $markup     .= '</div>';
                return $markup;
            break;

            case 'date':
                $value = $value['date'] ?? NULL;
                if( !$value ) return ;
                $markup = '
                <div>';
                $markup .= custom_time( $value, 4 );
                $markup .= '</div>';
                return $markup;
            break;

            case 'text':
            case 'textarea':
            case 'contact':
                if( !isset( $value['text'] ) ) return ;
                $markup = '
                <div>';
                $markup .= esc_html( $value['text'] );
                $markup .= '</div>';
                return $markup;
            break;

            case 'ranking':
                $value = $value['int_cascade'] ?? NULL;
                if( !$value ) return ;
                asort( $value );
                $options    = $this->question->getOptions()->fetch( -1 );
                $markup     = '<div>';
                foreach( $value as $opt_id => $pos ) {
                    if( isset( $options[$opt_id] ) ) {
                        $markup .= '<div>' . $pos . '. ' . esc_html( $options[$opt_id]->title ) . '</div>';
                    }
                }
                $markup     .= '</div>';
                return $markup;
            break;

            case 'file':
                if( !( empty( $value['attachments'] ) || empty( $value['text'] ) ) ) return ;
                $markup = '';
                if( !empty( $value['text'] ) ) {
                    $markup     .= '<div>';
                    $markup     .= '<div>' . esc_url( $value['text'] ) . '</div>';
                    $markup     .= '</div>';
                } else if( !empty( $value['attachments'] ) ) {
                    $markup     .= '<div>';
                    foreach( $value['attachments'] as $attachment ) {
                        $URL    = mediaLinks( (int) $attachment['media'] )->getItemURL();
                        $markup .= '<div>' . esc_url( $attachment['name'] ) . '</div>';
                        $markup .= '<div><i>URL: ' . esc_url( $URL ) . '</i></div>';
                    }
                    $markup     .= '</div>';
                }
                return $markup;
            break;

            default:
            return filters()->do_filter( 'question:responses:export:view', false, $this->question, $value );
        }
    }

    public function markup( $value, $attrs ) {
        $answer = $this->answers( $value );
        if( !$answer && empty( $attrs[0] ) ) return ;
        $markup = '
        <div class="item">
        <h2 class="ced">
            <span spellcheck="false">' . esc_html( $this->question->getTitle() ) . '</span>
        </h2>
        <div class="itemc">
        <div class="answer">';
        $markup .= ( !empty( $value ) && $answer ? $answer : '-' );
        $markup .= '
        </div>
        </div>
        </div>';

        return $markup;
    }
    
}