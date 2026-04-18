<?php declare(strict_types=1);

require_once '../../config/bootstrap.php';

if ( ! isValidRequest($_SERVER['REQUEST_URI']) )
{
    return false;
}
if ( preg_match('/^\/scripts/', $_SERVER['REQUEST_URI']) )
{
////    require_once __DIR__ . '.../../config/rpc_bootstrap.php';

//    return true;
}

require_once __DIR__ . '/../routes.php';


function isValidRequest(string $requestUri) : bool
{
    if ( preg_match('/^\/cgi-bin/', $requestUri) )
    {
        return false;
    }
    if ( preg_match('/\.(png|jpg|jpeg|gif|woff|ttf|map|ico)$/', $requestUri) )
    {
        return false;
    }

    return true;
}










