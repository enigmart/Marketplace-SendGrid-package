<?php

$app->post('/api/SendGrid/deleteSpamReports', function ($request, $response, $args) {
    $settings =  $this->settings;
    
    $data = $request->getBody();

    if($data=='') {
        $post_data = $request->getParsedBody();
    } else {
        $toJson = $this->toJson;
        $data = $toJson->normalizeJson($data); 
        $data = str_replace('\"', '"', $data);
        $post_data = json_decode($data, true);
    }
    
    $error = [];
    if(empty($post_data['args']['api_key'])) {
        $error[] = 'api_key cannot be empty';
    }
    
    if(!empty($error)) {
        $result['callback'] = 'error';
        $result['contextWrites']['to'] = implode(',', $error);
        return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($result);
    }
    
    
    $apiKey = $post_data['args']['api_key'];
    $query = [];
    if(!empty($post_data['args']['delete_all'])) {
        $query['delete_all'] = (bool) $post_data['args']['delete_all'];
    }
    if(!empty($post_data['args']['emails'])) {
        $query['emails'] = explode(',', $post_data['args']['emails']);
    }
    
    $sg = new \SendGrid($apiKey);
    
    $resp = $sg->client->suppression()->spam_reports()->delete($query);
    $body = json_decode($resp->body());
    
    if($resp->statusCode() == '204') {

        $result['callback'] = 'success';
        $result['contextWrites']['to'] = "deleted";

    } else {
        $result['callback'] = 'error';
        $result['contextWrites']['to'] = !is_string($body) ? $body : json_decode($body);
    }

    return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($result);
});

