<?php

use ttm4135\webapp\Auth;

require_once __DIR__ . '/../vendor/autoload.php';

$templatedir = __DIR__ . '/webapp/templates/';
$app = new \Slim\Slim([
    'debug' => true,
    'templates.path' => $templatedir,
    'view' => new \Slim\Views\Twig($templatedir
    )
]);
$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);
$view->parserOptions = array(
    'debug' => true
);


try {
    $options = [
        PDO::ATTR_EMULATE_PREPARES => false, // turn off emulation mode for "real" prepared statements
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
    ];

    // Create (connect to) SQLite database in file
    $app->db = new PDO('sqlite:../app.db', '', '', $options);
    // Set errormode to exceptions
    $app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}


$ns = 'ttm4135\\webapp\\controllers\\';

$isAdmin = function () {
    if (!Auth::isAdmin()) {
        $app = \Slim\Slim::getInstance();
        $username = Auth::user()->getUserName();
        $app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
        $app->redirect('/');
    }
};


/// app->(GET/POST) (URL, $ns . CONTROLLER);    // description..   <who has access>

$app->get('/', $ns . 'HomeController:index');             //front page            <all site visitors>

$app->get('/admin', $ns . 'AdminController:index');        //admin overview        <staff and group members>

$app->get('/login', $ns . 'LoginController:index');        //login form            <all site visitors>
$app->post('/login', $ns . 'LoginController:login');       //login action          <all site visitors>
$app->post('/logout', $ns . 'LoginController:logout');  //logs out    <all users>
$app->get('/logout', $ns . 'LoginController:logout');  //logs out    <all users>
$app->get('/register', $ns . 'UserController:index');     //registration form     <all visitors with valid personal cert>
$app->post('/register', $ns . 'UserController:create');    //registration action   <all visitors with valid personal cert>

$app->group('/admin', $isAdmin, function () use ($app) {
    $ns = 'ttm4135\\webapp\\controllers\\';
    $app->get('/', $ns . 'AdminController:index');
    $app->get('/delete/:userid', $ns . 'UserController:delete');     //delete user userid        <staff and group members>
    $app->post('/deleteMultiple', $ns . 'UserController:deleteMultiple'); //delete user userid        <staff and group members>
    $app->get('/edit/:userid', $ns . 'UserController:show');       //add user userid          <staff and group members>
    $app->post('/edit/:userid', $ns . 'UserController:edit');      //add user userid          <staff and group members>
    $app->get('/create', $ns . 'AdminController:create');      //add user userid          <staff and group members>
    $app->post('/create', $ns . 'UserController:newuser');        //add user userid          <staff and group members>  //TODO FIX
});


return $app;
