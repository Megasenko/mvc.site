<?php

class ControllerMain extends Controller
{
    function indexAction()
    {
        $this->view->generate('main_view.php', 'template_view.php');
    }
}