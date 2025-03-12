<?php

declare(strict_types=1);

//print "hiiiiiiiiiiiiiiiiiiiiiiiiiiiii";
$home = '<li><a href="/">Home</a></li>';
$about = '<li><a href="/about">About</a></li>';
$testy = '<li><a href="/testy">Testy</a></li>';
$test = '<li><a href="/test">Test</a></li>';
$post = '<li><a href="/posts">Posts</a></li>';
$user = '<li><a href="/users">Users</a></li>';
$dash = '<li><a href="/admin/dashboard">Dash</a></li>';
$signup = '<li><a href="/signup/new">Signup</a></li>';
$login = '<li><a href="/login">Login</a></li>';
$logout = '<li><a href="/logout">Logout</a></li>';
$useLog = "";
$profile = "";

if (isset($_SESSION['user_id'])) {
    $useLog = $logout;
    $profile = '<li><a href="/admin/profile/index">Profile</a></li>';
} else {
    $useLog = $login;
}

echo "
    <nav>
        <ul>
            $home
            $about
            $testy
            $test
            $post
            $user
            $dash
            $signup
            <div class=\"last-items-container\">
                $profile
                $useLog
            </div>
        </ul>
    </nav>
";
