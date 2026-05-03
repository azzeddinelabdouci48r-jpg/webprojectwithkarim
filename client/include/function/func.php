 <?php

function getTitle(){
    global $pageTitle;
    return isset($pageTitle) ? $pageTitle : 'Default Title';
}