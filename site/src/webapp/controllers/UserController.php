<?php

namespace ttm4135\webapp\controllers;

use ttm4135\webapp\models\User;
use ttm4135\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::guest()) {
            $this->render('newUserForm.twig', []);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    // Does string contain letters?
    function hasCapLetters($string)
    {
        return (!ctype_upper($string) && !ctype_lower($string));
    }

    // Does string contain numbers?
    function hasNumbers($string)
    {
        return preg_match('/\d/', $string);
    }

    // Does string contain special characters?
    function hasSpecialChars($string)
    {
        return preg_match('/[^a-zA-Z\d]/', $string);
    }

    function create()
    {
        $request = $this->app->request;
        $username = $request->post('username');

        $password = $request->post('password');
        $passwordConf = $request->post('passwordConf');


        if (!$passwordConf === $password) {
            $this->app->flashNow('error', 'The password does not meet all the requirements');
            $this->render('newUserForm.twig', []);
        } else if (!$this->hasCapLetters($password) || !$this->hasNumbers($password) || !$this->hasSpecialChars($password)) {
            $this->app->flashNow('error', 'The password does not meet all the requirements');
            $this->render('newUserForm.twig', []);
        } else if (preg_match('/[^a-z0-9 _]+$/i', $username)) {
            $this->app->flashNow('error', 'Username may only contain ASCII letters and digits, 
            with hyphens, underscores and spaces as internal separators');
            $this->render('newUserForm.twig', []);
        } else if ((bool)User::findByUser($username)) {
            $this->app->flashNow('error', 'The given username is already in use');
            $this->render('newUserForm.twig', []);
        } else {
            $user = User::makeEmpty();
            $user->setUsername($username);
            $user->setPassword($password);

            if ($request->post('email')) {
                $email = $request->post('email');
                $user->setEmail($email);
            }

            $user->save();
            $this->app->flash('info', 'Thanks for creating a user. You may now log in.');
            $this->app->redirect('/login');
        }

    }

    function delete($tuserid)
    {
        if (Auth::userAccess($tuserid)) {
            $user = User::findById($tuserid);
            $user->delete();
            $this->app->flash('info', 'User ' . $user->getUsername() . '  with id ' . $tuserid . ' has been deleted.');
            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function deleteMultiple()
    {
        if (Auth::isAdmin()) {
            $request = $this->app->request;
            $userlist = $request->post('userlist');
            $deleted = [];

            if ($userlist == NULL) {
                $this->app->flash('info', 'No user to be deleted.');
            } else {
                foreach ($userlist as $duserid) {
                    $user = User::findById($duserid);
                    if ($user->delete() == 1) { //1 row affect by delete, as expect..
                        $deleted[] = $user->getId();
                    }
                }
                $this->app->flash('info', 'Users with IDs  ' . implode(',', $deleted) . ' have been deleted.');
            }

            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }


    function show($tuserid)
    {
        if (Auth::userAccess($tuserid)) {
            $user = User::findById($tuserid);
            $this->render('showuser.twig', [
                'user' => $user
            ]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function newuser()
    {

        $user = User::makeEmpty();

        if (Auth::isAdmin()) {


            $request = $this->app->request;

            $username = $request->post('username');
            $password = $request->post('password');
            $email = $request->post('email');

            $isAdmin = ($request->post('isAdmin') != null);


            $user->setUsername($username);
            $user->setPassword($password);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $this->app->redirect('/admin');


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function edit($tuserid)
    {

        $user = User::findById($tuserid);

        if (!$user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        } elseif (Auth::userAccess($tuserid)) {


            $request = $this->app->request;

            $username = $request->post('username');
            $password = $request->post('password');
            $email = $request->post('email');

            $isAdmin = ($request->post('isAdmin') != null);


            $user->setUsername($username);
            $user->setPassword($password);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $user = User::findById($tuserid);

            $this->render('showuser.twig', ['user' => $user]);


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

}
