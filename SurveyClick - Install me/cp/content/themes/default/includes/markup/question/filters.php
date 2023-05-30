<?php

namespace admin\markup\question;

class filters {

    private $question;

    function __construct( \query\survey\questions $question ) {
        $this->question = $question;
    }

    private function options( $value ) {
        switch( $this->question->getType() ) {
            case 'net_prom':
                $markup = '
                <div class="chbxes">
                <div>';
                    foreach( [ 3 => t( 'Promoters' ), 2 => t( 'Passives' ), 1 => t( 'Detractors' ) ] as $k => $name ) {
                        $markup .= '
                        <div>
                            <input type="checkbox" name="data[q][net_prom][' . $this->question->getId() . '][' . $k . ']" id="data[q][net_prom][' . $this->question->getId() . '][' . $k . ']" value="' . $k . '"' . ( $value && is_array( $value ) && array_search( $k, $value ) !== false ? ' checked' : '' ) . '>
                            <label for="data[q][net_prom][' . $this->question->getId() . '][' . $k . ']">' . $name . '</label>
                        </div>';
                    }
                $markup .= '
                </div>
                </div>';
                return $markup;
            break;
            
            case 'srating':
                $setting    = $this->question->getSetting();
                $stars      = $setting['stars'] ?? 10;
                $markup = '
                <div class="chbxes">
                <div>';
                    for( $i = $stars; $i >= 1; $i-- ) {
                        $markup .= '
                        <div>
                            <input type="checkbox" name="data[q][srating][' . $this->question->getId() . '][' . $i . ']" id="data[q][srating][' . $this->question->getId() . '][' . $i . ']" value="' . $i . '"' . ( $value && is_array( $value ) && array_search( $i, $value ) !== false ?  ' checked' : '' ) . ' />
                            <label for="data[q][srating][' . $this->question->getId() . '][' . $i . ']">' . $i . ' <i class="fas fa-star"></i></label>
                        </div>';
                    }
                $markup .= '
                </div>
                </div>';
                return $markup;
            break;

            case 'checkbox':
                return '
                <div class="chbxes">
                    <div>
                        <div><input type="radio" name="data[q][checkbox][' . $this->question->getId() . ']" id="data[q][checkbox][' . $this->question->getId() . '][1]" value="1"' . ( isset( $value ) && $value == 1 ? ' checked' : '' ) . ' /><label for="data[q][checkbox][' . $this->question->getId() . '][1]">' . t( 'Yes' ) . '</label></div>
                        <div><input type="radio" name="data[q][checkbox][' . $this->question->getId() . ']" id="data[q][checkbox][' . $this->question->getId() . '][0]" value="0"' . ( isset( $value ) && $value == 0 ? ' checked' : '' ) . ' /><label for="data[q][checkbox][' . $this->question->getId() . '][0]">' . t( 'No' ) . '</label></div>
                    </div>
                </div>';
            break;

            case 'slider':
                $setting    = $this->question->getSetting();
                $min        = $setting['slider_setting']['from'] ?? 1;
                $max        = $setting['slider_setting']['to'] ?? 100;
                return '
                <div class="form_group">
                    <div class="form_line">
                        <label for="data[q][slider][' . $this->question->getId() . '][min]">' . t( 'Min' ) . '</label>
                        <input type="number" name="data[q][slider][' . $this->question->getId() . '][min]" id="data[q][slider][' . $this->question->getId() . '][min]" min="' . $min . '" max="' . ($max-1) . '" value="' . ( isset( $value['min'] ) ? (int) $value['min'] : '' ) . '">
                    </div>
                    <div class="form_line">
                        <label for="data[q][slider][' . $this->question->getId() . '][max]">' . t( 'Max' ) . '</label>
                        <input type="number" name="data[q][slider][' . $this->question->getId() . '][max]" id="data[q][slider][' . $this->question->getId() . '][max]" min="' . $min . '" max="' . $max . '" value="' . ( isset( $value['max'] ) ? (int) $value['max'] : '' ) . '">
                    </div>
                </div>';
            break;

            case 'matrix_rs':
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $markup     = '<div class="form_lines">';
                foreach( $fLabels as $label ) {
                    $markup .= '
                    <div class="form_line">
                    <label>' . esc_html( $label->title ) . '</label>';
                    $markup .= '<div class="form_line fl_bg">
                    <div class="chbxes">
                    <div>';
                    for( $i = 5; $i >= 1; $i-- ) {
                        $markup .= '
                        <div>
                            <input type="checkbox" name="data[q][matrix_rs][' . $this->question->getId() . '][' . $label->id . '][' . $i . ']" id="data[q][matrix_rs][' . $this->question->getId() . '][' . $label->id . '][' . $i . ']" value="' . $i . '"' . ( isset( $value[$label->id] ) && is_array( $value[$label->id] ) && array_search( $i, $value[$label->id] ) !== false ? ' checked' : '' ) . ' />
                            <label for="data[q][matrix_rs][' . $this->question->getId() . '][' . $label->id . '][' . $i . ']">' . $i . ' <i class="fas fa-star"></i></label>
                        </div>';
                    }
                    $markup .= '</div>
                    </div>
                    </div>
                    </div>';
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'matrix_mc':
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->question->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );
                $markup     = '<div class="form_lines">';
                foreach( $fLabels as $label ) {
                    $markup .= '
                    <div class="form_line">
                    <label>' . esc_html( $label->title ) . '</label>';
                    $markup .= '<div class="form_line fl_bg">
                    <div class="chbxes">
                    <div>';
                    foreach( $fColumns as $column ) {
                        $markup .= '
                        <div>
                            <input type="checkbox" name="data[q][matrix_mc][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . ']" id="data[q][matrix_mc][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . ']" value="' . $column->id . '"' . ( isset( $value[$label->id] ) && is_array( $value[$label->id] ) && array_search( $column->id, $value[$label->id] ) !== false ? ' checked' : '' ) . ' />
                            <label for="data[q][matrix_mc][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . ']">' . esc_html( $column->title ) . '</label>
                        </div>';
                    }
                    $markup .= '</div>
                    </div>
                    </div>
                    </div>';
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'matrix_dd':
                $labels     = $this->question->getLabels( 1 );
                $fLabels    = $labels->fetch( -1 );
                $columns    = $this->question->getLabels( 2 );
                $fColumns   = $columns->fetch( -1 );
                $markup     = '<div class="form_lines">';
                foreach( $fLabels as $label ) {
                    $markup .= '
                    <div class="form_line">
                    <label>' . esc_html( $label->title ) . '</label>';
                    $markup .= '<div class="form_lines fl_bg">';

                    foreach( $fColumns as $column ) {
                        $columns    ->setObject( $column );
                        $options    = $columns->getOptions();

                        $markup .= '
                        <div class="form_line">
                        <label>' . esc_html( $columns->getTitle() ) . '</label>';

                        $markup .= '
                        <div class="chbxes">
                        <div>';

                        foreach( $options->fetch( -1 ) as $option ) {
                            $markup .= '
                            <div>
                                <input type="checkbox" name="data[q][matrix_dd][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . '][' . $option->id . ']" id="data[q][matrix_dd][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . '][' . $option->id . ']" value="' . $option->id . '"' . ( isset( $value[$label->id][$column->id] ) && is_array( $value[$label->id][$column->id] ) && array_search( $option->id, $value[$label->id][$column->id] ) !== false ? ' checked' : '' ) . ' />
                                <label for="data[q][matrix_dd][' . $this->question->getId() . '][' . $label->id . '][' . $column->id . '][' . $option->id . ']">' . esc_html( $option->title ) . '</label>
                            </div>';
                        }

                        $markup .= '
                        </div>
                        </div>
                        </div>';
                    }

                    $markup .= '</div>
                    </div>';
                }
                $markup .= '</div>';
                return $markup;
            break;

            case 'multi':
            case 'checkboxes':
            case 'dropdown':
            case 'imagec':
                $options    = $this->question->getOptions();
                $fOptions   = $options->fetch( -1 );
                $markup     = '<div class="form_line">
                <div class="chbxes">
                <div>';
                foreach( $fOptions as $option ) {
                    $markup .= '
                    <div>
                        <input type="checkbox" name="data[q][multi][' . $this->question->getId() . '][' . $option->id . ']" id="data[q][multi][' . $this->question->getId() . '][' . $option->id . ']" value="' . $option->id . '"' . ( $value && is_array( $value ) && array_search( $option->id, $value ) ? ' checked' : '' ) . ' />
                        <label for="data[q][multi][' . $this->question->getId() . '][' . $option->id . ']">' . esc_html( $option->title ) . '</label>
                    </div>';
                }
                $markup .= '</div>
                </div>
                </div>';
                return $markup;
            break;

            case 'date':
                return '
                <div class="form_group">
                    <div class="form_line">
                        <label for="data[q][date][' . $this->question->getId() . '][from]">' . t( 'From ' ) . '</label>
                        <input type="datetime-local" name="data[q][date][' . $this->question->getId() . '][from]" id="data[q][date][' . $this->question->getId() . '][from]">
                    </div>
                    <div class="form_line">
                        <label for="data[q][date][' . $this->question->getId() . '][to]">' . t( 'To' ) . '</label>
                        <input type="datetime-local" name="data[q][date][' . $this->question->getId() . '][to]" id="data[q][date][' . $this->question->getId() . '][to]">
                    </div>
                </div>';
            break;

            case 'text':
            case 'textarea':
            case 'contact':
                return '
                <div class="form_group">
                    <div class="form_line">
                        <input type="text" name="data[q][text][' . $this->question->getId() . '][str]" placeholder="' . t( 'Word/phrase' ) . '" value="' . ( isset( $value['str'] ) ? esc_html( $value['str'] ) : '' ) . '" />
                    </div>
                    <div class="form_line">
                        <select name="data[q][text][' . $this->question->getId() . '][type]">
                            <option value="e"' . ( isset( $value['type'] ) && $value['type'] == 'e' ? ' selected' : '' ) . '>' . t( 'Exact word/phrase' ) . '</option>
                            <option value="m"' . ( isset( $value['type'] ) && $value['type'] == 'm' ? ' selected' : '' ) . '>' . t( 'Find anywhere' ) . '</option>
                        </select>
                    </div>
                </div>';
            break;

            case 'ranking':
                $options    = $this->question->getOptions();
                $fOptions   = $options->fetch( -1 );
                $markup     = '<div class="form_line">
                <div class="chbxes">
                <div class="rfilt">';
                foreach( $fOptions as $option ) {
                    $markup .= '
                    <div>
                        <select name="data[q][opr][' . $this->question->getId() . '][' . $option->id . ']" id="data[q][opr][' . $this->question->getId() . '][' . $option->id . ']">
                            <option value=""></option>';
                            for( $i = 1; $i <= 10; $i++ ) {
                                if( $i == 1 )
                                    $markup .= '<option value="=|1">=1</option>';
                                else {
                                    $markup .= '<option value="=|' . $i . '">=' . $i . '</option>';
                                    $markup .= '<option value="<=|' . $i . '"><=' . $i . '</option>';
                                }
                            }
                        $markup .= '
                        </select>
                        <label for="data[q][opr][' . $this->question->getId() . '][' . $option->id . ']">' . esc_html( $option->title ) . '</label>
                    </div>';
                }
                $markup .= '</div>
                </div>
                </div>';
                return $markup;
            break;

            default:
                $filter = filters()->do_filter( 'question:filters', false, $this->question );
                if( $filter )
                return $filter;
                return '<div class="msg info mb0">' . t( 'This type is not supported for filtering results' ) . '</div>';
        }
    }

    public function markup( $value ) {
        $markup = '
        <div data-qid="' . $this->question->getId() . '">
            <a href="#" class="viewn">
                <i class="fas fa-chevron-right"></i>
                <span>' . esc_html( $this->question->getTitle() ) . '</span>
            </a>
            <div class="mt15">
                ' . $this->options( $value ) . '
            </div>
        </div>';

        return $markup;
    }
    
}