<?php
/* Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp */
class ProductSdk {
  private $baseUrl;
  private $token;
  function __construct($baseUrl, $token=null) { $this->baseUrl=$baseUrl; $this->token=$token; }
  function getProduct($id) { return $this->req("GET", "/product/".$id, null); }
  function createProduct($p) { return $this->req("POST", "/product", $p); }
  function subscribeWebhook($url, $secret) { return $this->req("POST", "/product/webhook/subscribe", ["url"=>$url,"secret"=>$secret]); }
  private function req($method, $path, $body) {
    $opts = ["http" => ["method"=>$method, "header"=>"Content-Type: application/json
".($this->token?"Authorization: Bearer ".$this->token."
":""), "content"=>$body?json_encode($body):""]];
    $context = stream_context_create($opts);
    $res = file_get_contents($this->baseUrl.$path, false, $context);
    return json_decode($res, true);
  }
}
?>
