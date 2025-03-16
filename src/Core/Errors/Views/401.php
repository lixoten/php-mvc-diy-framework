<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
//Debug::p($message);
///////////////////
// Unauthenticated - 401
///////////////////
?>
<h1>Error Page: View 401</h1>
<h4><?= $message ?></h4>
<p><?= '401 BOOOOO LINE: ' . $data['line'] ?></p>