<?php

declare(strict_types=1);

use App\Helpers\UiHelper;

/**
 * page template
 *
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= $title ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>

<p>This is just an empty page, an example of an page Action</p>
<ul> xxxxxxx
    <li>Requires a New Action in the Controller.
        <ul>
            <li> `public function placeHolderAction(`</li>
        </ul>
    </li>
    <li>Require a New view file in Views.
         <ul>
            <li>'\src\App\Features\Testy\Views\placeholder.php'</li>
        </ul>
    </li>
    <li>in 'Url.php' url enum. It needs a new Case + code
        <ul>
            <li>case CORE_TESTY_PLACEHOLDER;</li>
            <li><pre>case CORE_TESTY_PLACEHOLDER;
            </pre></li>
        </ul>
    </li>
    <li>
        <ul>
            <li>xxx</li>
        </ul>
    </li>
</ul>
