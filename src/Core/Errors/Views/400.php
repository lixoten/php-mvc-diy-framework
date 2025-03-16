<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
//Debug::p($message);
///////////////////
// BadRequest - 400
///////////////////
?>
<h1>Error Page: View 400</h1>
<h4><?= $message ?></h4>
<p><?= '400 BOOOOO LINE: ' . $data['line'] ?></p>