<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
//Debug::p($message);
///////////////////
// Server Error - 500
///////////////////
?>
<h1>Error Page: View 500</h1>
<h4><?= $message ?></h4>
<p><?= '500 BOOOOO LINE: ' . $data['line']  . ' -- ' . $data['file'] ?></p>