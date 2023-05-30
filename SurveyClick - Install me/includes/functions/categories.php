<?php

$item->object = categories();
if( isset( $item->params[0] ) ) {
    $item->object->setSlug( strtok( implode( $item->params, '/' ), '.' ), site()->user_country->current );
}
$item->object->getObject();


function category() {
    global $item;
    return $item->object;
}

function exists() {
    global $item;
    $info = $item->object->getObject();
    return !empty( $info ) ?: 0;
}

function content() {
    return category()->getBBText();
}

function theme_surveys2() {

}