require_once( __DIR__ .'Models.php');
require_once( __DIR__ .'KnownUser.php');

$configText = file_get_contents('integrationconfig.json');
$customerID = "cocotest";
$secretKey = "9520b587-9c01-49d3-8f03-dbece100d1e2daf090b7-603b-40a6-b0eb-0e3274068886"; 

$queueittoken = isset( $_GET["queueittoken"] )? $_GET["queueittoken"] :'';

try
{
    $fullUrl = getFullRequestUri();
    $currentUrlWithoutQueueitToken = preg_replace("/([\\?&])("."queueittoken"."=[^&]*)/i", "", $fullUrl);

    //Verify if the user has been through the queue
    $result = QueueIT\KnownUserV3\SDK\KnownUser::validateRequestByIntegrationConfig(
       $currentUrlWithoutQueueitToken, $queueittoken, $configText, $customerID, $secretKey);
		
    if($result->doRedirect())
    {
        //Adding no cache headers to prevent browsers to cache requests
        header("Expires:Fri, 01 Jan 1990 00:00:00 GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        //end
    
        if(!$result->isAjaxResult)
        {
            //Send the user to the queue - either because hash was missing or because is was invalid
            header('Location: ' . $result->redirectUrl);		            
        }
        else
        {
            header('HTTP/1.0: 200');
            header($result->getAjaxQueueRedirectHeaderKey() . ': '. $result->getAjaxRedirectUrl());            
        }
		
        die();
    }
    if(!empty($queueittoken) && !empty($result->actionType))
    {        
	//Request can continue - we remove queueittoken form querystring parameter to avoid sharing of user specific token
        header('Location: ' . $currentUrlWithoutQueueitToken);
	die();
    }
}
catch(\Exception $e)
{
    // There was an error validating the request
    // Use your own logging framework to log the error
    // This was a configuration error, so we let the user continue
}
