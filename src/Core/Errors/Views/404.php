<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
//Debug::p($message);
//Debug::p($data);
///////////////////
// Not Found - 404
///////////////////
?>
<h1>Error Page: View 404</h1>
<h4><?= $message ?></h4>
<p><?= '404 BOOOOO LINE: ' . $data['line']  . ' -- ' . $data['file'] ?></p>