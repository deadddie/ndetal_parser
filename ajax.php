<?php

/**
 * ndetal.com Parser Ajax handler
 *
 * @author deadie
 */

use Deadie\Ajax\AjaxCombilift;

require_once __DIR__ . '/init.php';

if (isset($_REQUEST) && !empty($_REQUEST['method'])) {
    $request = $_REQUEST;
    $method = $request['method'];
    $result = method_exists(AjaxCombilift::class, $method)
        ? AjaxCombilift::$method($request)
        : AjaxCombilift::errorsOnly(405); // результат из метода, иначе 405 Method Not Allowed
    print json_encode($result);
}
