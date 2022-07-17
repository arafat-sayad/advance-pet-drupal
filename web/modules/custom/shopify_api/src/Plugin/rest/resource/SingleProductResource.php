<?php

namespace Drupal\shopify_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "single_product_resource",
 *   label = @Translation("Single Product Resource"),
 *   uri_paths = {
 *     "canonical" = "/product/{id}",
 *   }
 * )
 */
class SingleProductResource extends ResourceBase {

  private $base_url = 'https://1254-56.myshopify.com/admin/api/2022-07';
  private $access_token = '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';
  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get($id) {
    try{
      $client = new Client(['headers' => ['X-Shopify-Access-Token' => $this->access_token]]);
      $response = $client->get($this->base_url.'/products/'.$id.'.json');
      $result = json_decode($response->getBody(), TRUE);
      return new ResourceResponse($result,200);
    } catch(\Exception $e){
      \Drupal::logger('custom-rest')->error($e->getMessage());
      $response = 'couldn\'t fetch product list';
      return new ResourceResponse($response,404);
    }
  }
}
